document.addEventListener("DOMContentLoaded", function () {
  // 1. MAIN DISPLAY MAP
  const map = L.map("map").setView([15.1465, 120.5794], 14);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);

  // 1.1. ADD MARKERS FROM DATABASE
  if (
    typeof establishmentData !== "undefined" &&
    establishmentData.length > 0
  ) {
    establishmentData.forEach((place) => {
      // Create a marker for each establishment
      const marker = L.marker([place.latitude, place.longitude]).addTo(map);

      // Create a nice popup content
      const popupContent = `
                <div style="font-family: sans-serif;">
                    <h6 class="fw-bold mb-1">${place.name}</h6>
                    <p class="small text-muted mb-1"><i class="fas fa-map-marker-alt"></i> ${place.address}</p>
                    <p class="small mb-0">${place.description}</p>
                </div>
            `;

      marker.bindPopup(popupContent);
    });

    // Optional: Automatically zoom map to fit all markers
    const group = new L.featureGroup(
      establishmentData.map((p) => L.marker([p.latitude, p.longitude])),
    );
    map.fitBounds(group.getBounds().pad(0.1));
  }

  // 2. MODAL PICKER MAP LOGIC
  let pickerMap;
  let pickerMarker;
  const modal = document.getElementById("addEstablishmentModal");

  // Initialize picker map only when modal opens (Leaflet needs visible container)
  modal.addEventListener("shown.bs.modal", function () {
    if (!pickerMap) {
      pickerMap = L.map("picker-map").setView([15.1465, 120.5794], 16);
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
        pickerMap,
      );

      pickerMap.on("click", function (e) {
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);

        if (pickerMarker) {
          pickerMarker.setLatLng(e.latlng);
        } else {
          pickerMarker = L.marker(e.latlng).addTo(pickerMap);
        }

        // Update hidden inputs and display text
        document.getElementById("lat_input").value = lat;
        document.getElementById("lng_input").value = lng;
        document.getElementById("display-lat").innerText = lat;
        document.getElementById("display-lng").innerText = lng;
      });
    } else {
      pickerMap.invalidateSize(); // Refreshes map tiles properly
    }
  });

  // 3. FORM SUBMISSION
  document
    .getElementById("addEstablishmentForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();

      if (!document.getElementById("lat_input").value) {
        Swal.fire("Error", "Please pin a location on the map", "error");
        return;
      }

      const formData = new FormData(this);
      // Assuming user_id comes from your session or a global var
      formData.append("action", "add_establishment");

      fetch("./features/handle_establishments.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            Swal.fire("Success", "Establishment added!", "success").then(() =>
              location.reload(),
            );
          } else {
            Swal.fire("Error", data.message, "error");
          }
        });
    });
});
