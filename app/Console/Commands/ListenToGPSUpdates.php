<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Log;
use Ratchet\Client\Connector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log as FacadesLog;
use React\EventLoop\Factory;

class ListenToGPSUpdates extends Command
{
    protected $signature = 'gps:listen';
    protected $description = 'Listen to GPS WebSocket and update bus locations';

    public function handle()
    {
        $loop = Factory::create();
        $connector = new Connector($loop);
        $url = 'ws://103.90.84.153:8081';

        $reconnect = function () use (&$reconnect, $connector, $loop, $url) {
            echo "Connecting to WebSocket server...\n";

            $connector($url, [], ['Origin' => 'http://localhost'])->then(
                function (\Ratchet\Client\WebSocket $conn) use ($loop, &$reconnect) {
                    echo "Connected to WebSocket server\n";

                    $conn->on('message', function ($msg) {
                        echo "Received: {$msg}\n";

                        $data = json_decode($msg, true);
                        $busData = $data['data'][0] ?? $data['data'];

                        if (!isset($busData['imei'], $busData['lat'], $busData['lon'])) return;

                        $busInfo = self::getBusInfoByIMEI($busData['imei']);
                        if($busInfo === null) {
                            FacadesLog::warning('No bus found for IMEI', [
                                'imei' => $busData['imei']
                            ]);
                            return;
                        }
                        FacadesLog::info('bus_id for ime', [
                            'imei' => $busData['imei'],
                            'busInfo' => $busInfo->id
                        ]);
                        if (!$busInfo || !$busInfo->tracking_enabled) return;

                        $cacheKey = "last_update_{$busInfo->id}";
                        $now = time();
                        $last = Cache::get($cacheKey, ['time' => 0, 'lat' => null, 'lon' => null]);

                        $timeDiff = $now - $last['time'];
                        $moved = self::getDistance($last['lat'], $last['lon'], $busData['lat'], $busData['lon']);

                        // if ($timeDiff < 60 || $moved < 10) return;

                        Cache::put($cacheKey, ['time' => $now, 'lat' => $busData['lat'], 'lon' => $busData['lon']], now()->addMinutes(5));

                        // Send update
                        $response = Http::post(url('/api/bus/location/update'), [
                            'api_key' => 'public_api_key_for_location_updates',
                            'bus_tracking_id' => $busInfo->current_tracking_id,
                            'bus_id' => $busInfo->id,
                            'latitude' => $busData['lat'],
                            'longitude' => $busData['lon'],
                            'speed' => $busData['speed'] ?? 0,
                            'heading' => $busData['course'] ?? 0,
                            'last_tracked_at' => $busData['lastUpdate'] ?? now(),
                        ]);
                        FacadesLog::info('bus location update', [
                            'response' => $response,
                            'bus_id' => $busInfo->id
                        ]);
                        self::handleZoneTransitions($busInfo, $busData);

                        // echo "Updated location for {$busInfo->bus_number}\n";
                    });

                    $conn->on('close', function ($code = null, $reason = null) use (&$reconnect, $loop) {
                        echo "Connection closed ({$code} - {$reason}) - Reconnecting in 5s...\n";
                        $loop->addTimer(5, fn() => $reconnect());
                    });
                },
                function (\Exception $e) use ($loop, &$reconnect) {
                    echo "WebSocket connection failed: {$e->getMessage()} - Reconnecting in 5s...\n";
                    $loop->addTimer(5, fn() => $reconnect());
                }
            );
        };
        $reconnect();
        $loop->run();
        return 0;
    }

    public static function getBusInfoByIMEI($imei)
    {
        // Stubbed. Replace with actual DB query if needed.
        return \DB::table('buses')
            ->where('imei', $imei)
            ->select('id', 'bus_number', 'tracking_enabled', 'current_tracking_id')
            ->first();
    }

    public static function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2)) return 0;
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
    public static function handleZoneTransitions($busInfo, $busData)
    {
        $zones = \DB::table('alerts')
            ->where('bus_id', $busInfo->id)
            ->get();
        foreach ($zones as $zone) {
            $lat = $zone->latitude ?? 0;
            $lon = $zone->longitude ?? 0;
            $radius = 300;/*$busInfo->source_radius ?? 1000;*/ // Default to 1000 meters
            $transition = self::checkZoneTransitions(
                $zone->id, 
                $busInfo->id, $busData['lat'], $busData['lon'],
                $lat, $lon, $radius
            );
            
            if ($zone->type === 'geofence_exit' && $transition === 'exited') { 
                FacadesLog::info("\n\n\nBus {$busInfo->bus_number} exited zone {$zone->id}");
            }
            if ($zone->type === 'geofence_entry' && $transition === 'entered') {
                FacadesLog::info("\n\n\nBus {$busInfo->bus_number} entered zone {$zone->id}");
            }
        }
    }
    public static function checkZoneTransitions($zoneId, $busId, $lat, $lon, $centerLat, $centerLon, $radiusMeters)
    {
        $cacheKey = "zone_alert_{$zoneId}";
        $wasInside = Cache::get($cacheKey, false); // false means previously outside

        $distance = self::getDistance($lat, $lon, $centerLat, $centerLon);
        FacadesLog::info(message: "\nzoneId:{$zoneId}: {$distance}");
        $isInside = $distance <= $radiusMeters;

        if ($isInside && !$wasInside) {
            // Entered zone
            echo "\n\n {$distance} {$radiusMeters}-{$isInside}-{$wasInside}";
            Cache::put($cacheKey, true, now()->addHours(1));
            return 'entered';
        }

        if (!$isInside && $wasInside) {
            // Exited zone
        echo "\n\n {$distance} {$radiusMeters}-{$isInside}-{$wasInside}";
            Cache::put($cacheKey, false, now()->addHours(1));
            return 'exited';
        }

        return false; // No transition
    }
}
