window.marker = null;

function initialize() {
  // Get coordinates from data attributes
  var latitude = $('#map').attr('data-latitude');
  var longitude = $('#map').attr('data-longitude');

  if (!latitude || !longitude) {
    console.error('Latitude or Longitude is missing!');
    return;
  }

  // Initialize the Leaflet map
  var map = L.map('map').setView([latitude, longitude], 13); // Set the center of the map and zoom level
  
  // Add tile layer (OpenStreetMap)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Add the default marker
  var marker = L.marker([latitude, longitude]).addTo(map).bindPopup('Event Location')
  .openPopup();
}

var mapElement = document.getElementById('map');
if (mapElement != null) {
  initialize();
}
