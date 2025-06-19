 const map = L.map('map').setView([27.9634, 84.6548], 7); // Midpoint between Kathmandu and Pokhara

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Add the routing control
  L.Routing.control({
    waypoints: [
      L.latLng(27.7172, 85.3240), // Kathmandu
      L.latLng(28.2096, 83.9856)  // Pokhara
    ],
    routeWhileDragging: false,
    lineOptions: {
      styles: [{ color: 'blue', opacity: 0.7, weight: 5 }]
    },
    createMarker: function(i, waypoint, n) {
      const labels = ['Tulsipur(Startttttt)', 'Pokhara (End)'];
      return L.marker(waypoint.latLng).bindPopup(labels[i]).openPopup();
    }
  }).addTo(map);