<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class KhaltiModeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'khalti:mode {mode : test or live}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch between Khalti test and live mode';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->argument('mode');
        
        if (!in_array($mode, ['test', 'live'])) {
            $this->error('Invalid mode. Use "test" or "live".');
            return 1;
        }

        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Update KHALTI_TEST_MODE
        $testMode = $mode === 'test' ? 'true' : 'false';
        $envContent = preg_replace(
            '/KHALTI_TEST_MODE=.*/m',
            "KHALTI_TEST_MODE={$testMode}",
            $envContent
        );

        File::put($envPath, $envContent);

        // Clear config cache
        $this->call('config:clear');

        $this->info("Khalti mode switched to: {$mode}");
        
        if ($mode === 'test') {
            $this->warn('⚠️  You are now in TEST MODE. Payments will use test keys.');
            $this->info('Public Key: ' . config('khalti.public_key'));
        } else {
            $this->warn('🔴 You are now in LIVE MODE. Real payments will be processed!');
            $this->info('Public Key: ' . config('khalti.public_key'));
        }

        return 0;
    }
}
