// Simple Node.js TCP server for GPS data forwarding to Laravel
import { createServer } from 'net';
import axios from 'axios';
import * as dotenv from 'dotenv';
dotenv.config();

const TCP_PORT = 7711; // Port to listen for GPS device
const LARAVEL_API = 'http://103.90.84.153:8081/api/bus/location/update-gps'; // GPS endpoint
const API_KEY = process.env.GPS_TRACKING_API_KEY; // Get from .env file

const server = createServer((socket) => {
    console.log('✅ GPS device connected from:', socket.remoteAddress, socket.remotePort);
    console.log('⏳ Waiting for data...');
    let buffer = '';

    socket.on('data', async (data) => {
        buffer += data.toString();
        let lines = buffer.split('\n');
        buffer = lines.pop(); // Keep incomplete line for next data

        for (let line of lines) {
            line = line.trim();
            if (!line) continue;

            try {
                const gps = JSON.parse(line);

                // Append API key before sending to Laravel
                gps.api_key = API_KEY;

                // Forward to Laravel
                const response = await axios.post(LARAVEL_API, gps);
                console.log('Forwarded GPS:', gps, 'Laravel responded:', response.status);
            } catch (err) {
                console.error('Parse/Forward error:', err.message, 'Line:', line);
            }
        }
    });

    socket.on('end', () => {
        console.log('GPS device disconnected:', socket.remoteAddress, socket.remotePort);
    });

    socket.on('error', (err) => {
        console.error('Socket error:', err.message);
    });
});

server.listen(TCP_PORT, '0.0.0.0', () => {
    console.log(`✅ GPS TCP server listening on 0.0.0.0:${TCP_PORT}`);
});
