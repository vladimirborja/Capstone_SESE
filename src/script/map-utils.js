/**
 * Initialize any Leaflet map centered on Angeles City.
 * @param {string} elementId - the HTML div id for the map
 * @returns {L.Map}
 */
function initAngelesMap(elementId) {
  const map = L.map(elementId).setView(ANGELES_CENTER, ANGELES_DEFAULT_ZOOM);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors"
  }).addTo(map);
  return map;
}

/**
 * Auto-zoom to a barangay and drop/move a draggable pin.
 * @param {L.Map} mapInstance
 * @param {string} barangayName
 * @param {L.Marker|null} markerRef
 * @returns {L.Marker}
 */
function zoomToBarangay(mapInstance, barangayName, markerRef = null) {
  const key = (barangayName || "").toLowerCase().trim();
  const coords = ANGELES_BARANGAYS[key];
  if (!coords) return markerRef;

  mapInstance.flyTo(coords, BARANGAY_ZOOM, { animate: true, duration: 1 });

  if (markerRef) {
    markerRef.setLatLng(coords);
  } else {
    markerRef = L.marker(coords, { draggable: true }).addTo(mapInstance);
  }

  markerRef.bindPopup("📍 " + barangayName).openPopup();
  return markerRef;
}

/**
 * Attach drag event to update hidden lat/lng inputs.
 * @param {L.Marker} marker
 * @param {string} latInputId
 * @param {string} lngInputId
 */
function bindMarkerToInputs(marker, latInputId, lngInputId) {
  function updateInputs(latlng) {
    const latEl = document.getElementById(latInputId);
    const lngEl = document.getElementById(lngInputId);
    if (latEl) latEl.value = latlng.lat.toFixed(7);
    if (lngEl) lngEl.value = latlng.lng.toFixed(7);
  }

  marker.on("dragend", function (e) {
    updateInputs(e.target.getLatLng());
  });
  updateInputs(marker.getLatLng());
}
