window.marker = null;

function initialize() {
  // Get coordinates and marker from data attributes
  var latitude = $('#map').attr('data-latitude');
  var longitude = $('#map').attr('data-longitude');
  var markerImage = $('#map').attr('data-marker'); // The marker image path
  
  // Initialize the Leaflet map
  var map = L.map('map').setView([latitude, longitude], 13); // Set the center of the map and zoom level
  
  // Add tile layer (OpenStreetMap)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Add a custom marker
  var marker = L.marker([latitude, longitude], {
    icon: L.icon({
      iconUrl: markerImage,
      iconSize: [40, 60], // Adjust the size of the marker
      iconAnchor: [20, 60], // Position the marker
    })
  }).addTo(map);
  
  // Optionally, add a popup with the marker's title
  marker.bindPopup('<b>' + $('#map').attr('data-marker-name') + '</b>').openPopup();
}

var mapElement = document.getElementById('map');
if (mapElement != null) {
  initialize();
}
