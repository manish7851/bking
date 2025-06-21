//  const map = L.map('map').setView([27.9634, 84.6548], 7); // Midpoint between Kathmandu and Pokhara

//   // Add OpenStreetMap tiles
//   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//     attribution: '&copy; OpenStreetMap contributors'
//   }).addTo(map);

//   // Add the routing control
//   L.Routing.control({
//     waypoints: [
//       L.latLng(28.046484, 82.485705), // Kathmandu
//       L.latLng(27.734943, 85.311397),  // Pokhara
//       L.latLng(activeRoute.source_latitude, activeRoute.source_longitude),
//       L.latLng(activeRoute.destination_latitude, activeRoute.destination_longitude) // Midpoint
//     ],
//     routeWhileDragging: false,
//     lineOptions: {
//       styles: [{ color: 'blue', opacity: 0.7, weight: 5 }]
//     },
//     showAlternatives: true,
//     show: false,
//     createMarker: function(i, waypoint, n) {
//       const labels = ['Tulsipur(Startttttt)', 'Pokhara (End)'];
//       return L.marker(waypoint.latLng).bindPopup(labels[i]).openPopup();
//     }
//   }).addTo(map);


// // const busData = {heading: 24, bus_number: "2345", bus_name: "ldjf", speed: 40}
// // // const position = [busData.latitude, busData.longitude];
// // const position = [27.7172, 85.3240];
// // let busMarker;                
// // // Update icon rotation based on heading
// // if (/**busData.heading**/ true) {
// //     const icon = L.divIcon({
// //         className: 'bus-icon-container',
// //         html: `<img src="/bus.svg" alt="Bus" style="transform: rotate(${busData.heading}deg);" />`,
// //         iconSize: [32, 32],
// //         iconAnchor: [16, 16]
// //     });
    
// //     if (busMarker) {
// //         busMarker.setIcon(icon);
// //         busMarker.setLatLng(position);
// //     } else {
// //         busMarker = L.marker(position, {icon: icon}).addTo(map);
// //     }
// // } else {
// //     if (busMarker) {
// //         busMarker.setLatLng(position);
// //     } else {
// //         busMarker = L.marker(position, {icon: busIcon}).addTo(map);
// //     }
// // }

// // busMarker.bindPopup(`
// //     <b>${busData.bus_name}</b><br>
// //     Bus #: ${busData.bus_number}<br>
// //     Speed: ${busData.speed || 0} km/h<br>
// //     Last update: ${new Date(busData.last_tracked_at || Date.now()).toLocaleString()}
// // `);

// // // Center map on bus position
// // map.setView(position, map.getZoom());


// // Replace with your WebSocket server URL
// const socket = new WebSocket('ws://103.90.84.153:8081');

// const output = document.getElementById('output');

// socket.addEventListener('open', () => {
//     console.log('Connected to WebSocket server');
//     // output.textContent = 'Connected. Waiting for messages...\n';
// });

// socket.addEventListener('message', (event) => {
//     console.log('Received:', event.data);

//     try {
//         const message = JSON.parse(event.data);

//         // Check message type and structure
//         if ((message.type='update' || message.type === 'initial_state') && message.data && message.data[0].imei) {
//             const {
//                 imei, lat, lon, speed, course, datetime, lastUpdate
//             } = message.data[0];

//             const formatted = `
//               IMEI: ${imei}
//               Latitude: ${lat}
//               Longitude: ${lon}
//               Speed: ${speed} km/h
//               Heading: ${course}
//               Datetime: ${datetime}
//               Last Update: ${lastUpdate}
//             `;
//             const busData = {heading: 24, bus_number: "2345", bus_name: "ldjf", speed: speed, last_tracked_at: lastUpdate}
//             // const position = [busData.latitude, busData.longitude];
//             const position = [lat, lon];
//             // const position = [27.7172, 85.3240];

//             let busMarker;                
//             // Update icon rotation based on heading
//             if (/**busData.heading**/ true) {
//                 const icon = L.divIcon({
//                     className: 'bus-icon-container',
//                     html: `<img src="/bus.svg" alt="Bus" style="transform: rotate(${course}deg);" />`,
//                     iconSize: [32, 32],
//                     iconAnchor: [16, 16]
//                 });
                
//                 if (busMarker) {
//                     busMarker.setIcon(icon);
//                     busMarker.setLatLng(position);
//                 } else {
//                     busMarker = L.marker(position, {icon: icon}).addTo(map);
//                 }
//             } else {
//                 if (busMarker) {
//                     busMarker.setLatLng(position);
//                 } else {
//                     busMarker = L.marker(position, {icon: busIcon}).addTo(map);
//                 }
//             }
//             map.setView(position, map.getZoom());
//             busMarker.bindPopup(`
//                 <b>${busData.bus_name}</b><br>
//                 Bus #: ${busData.bus_number}<br>
//                 Speed: ${busData.speed || 0} km/h<br>
//                 Last update: ${new Date(busData.last_tracked_at || Date.now()).toLocaleString()}
//             `);

//             console.log(formatted);
//             // output.textContent = formatted;
//         } else {
//             console.warn('Unknown message format:', message);
//         }
//     } catch (err) {
//         console.error('Error parsing message:', err);
//         // output.textContent += `\nError parsing message: ${err.message}`;
//     }
// });

// socket.addEventListener('close', () => {
//     console.log('WebSocket connection closed');
//     // output.textContent += '\nDisconnected from server.';
// });

// socket.addEventListener('error', (err) => {
//     console.error('WebSocket error:', err);
//     // output.textContent += `\nWebSocket error: ${err.message}`;
// });