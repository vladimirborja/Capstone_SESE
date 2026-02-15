// 1. Declare variables globally so all functions can access them
let mainMap;
let isMapInitialized = false;
let allMarkers = []; // Array to store marker objects and their types

// Toggle the 'Specify Others' input field
function toggleOtherInput(value) {
  const otherDiv = document.getElementById("otherTypeDiv");
  const otherInput = document.getElementById("other_type_input");
  if (value === "Others") {
    otherDiv.style.display = "block";
    otherInput.setAttribute("required", "required");
  } else {
    otherDiv.style.display = "none";
    otherInput.removeAttribute("required");
  }
}

// 2. Define this globally so the HTML button's 'onclick' can find it
function toggleMapView() {
  const feed = document.getElementById("feed-container");
  const mapBox = document.getElementById("map-container");
  const btn = document.getElementById("toggleMapBtn");

  if (feed.style.display !== "none") {
    feed.style.display = "none";
    mapBox.style.display = "block";
    btn.innerHTML = "View Feed";
    initMainMap(); // Call the initialization function
  } else {
    feed.style.display = "block";
    mapBox.style.display = "none";
    btn.innerHTML = "Location";
  }
}

function initMainMap() {
  if (isMapInitialized) {
    if (mainMap) mainMap.invalidateSize();
    return;
  }

  mainMap = L.map("map").setView([15.1465, 120.5794], 14);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
    mainMap,
  );

  if (
    typeof establishmentData !== "undefined" &&
    establishmentData.length > 0
  ) {
    establishmentData.forEach((place) => {
      const marker = L.marker([place.latitude, place.longitude]).addTo(mainMap);
      // Create a nice popup content
      const popupContent = `
        <div class="p-1" style="min-width: 200px;">
            <h6 class="fw-bold text-primary mb-1 d-flex align-items-center">
            <i class="bi bi-geo-alt-fill me-2"></i> ${place.name}
            </h6>
            <span class="badge bg-primary rounded-pill" style="font-size: 0.80rem;">${place.type}</span>
            <p class="text-muted">
                ${place.address}
            </p>
            
            <p class="mb-3 text-dark" style="line-height: 1.4;">
            ${place.description}
            </p>

            <div class="d-grid">
                <a href="https://www.google.com/maps/dir/?api=1&destination=${place.latitude},${place.longitude}" 
                    target="_blank" 
                    class="btn btn-sm btn-outline-primary py-1" 
                    style="font-size: 0.75rem;">
                    Get Directions
                </a>
            </div>
        </div>
        `;

      marker.bindPopup(popupContent);
      allMarkers.push({ marker: marker, type: place.type });
    });

    const group = new L.featureGroup(
      establishmentData.map((p) => L.marker([p.latitude, p.longitude])),
    );
    mainMap.fitBounds(group.getBounds().pad(0.1));
  }
  isMapInitialized = true;
}

// Filtering Logic
function filterMapMarkers() {
  const selectedTypes = Array.from(
    document.querySelectorAll(".filter-checkbox:checked"),
  ).map((cb) => cb.value);
  const standardTypes = [
    "Restaurant / Cafe",
    "Hotel / Resort",
    "Mall / Shopping Center",
    "Park / Recreational Area",
  ];

  allMarkers.forEach((item) => {
    let isVisible = false;

    if (selectedTypes.length === 0) {
      isVisible = true; // Show all if none selected
    } else {
      const isOther = !standardTypes.includes(item.type);

      if (selectedTypes.includes(item.type)) {
        isVisible = true;
      } else if (selectedTypes.includes("Others") && isOther) {
        isVisible = true;
      }
    }

    if (isVisible) {
      item.marker.addTo(mainMap);
    } else {
      mainMap.removeLayer(item.marker);
    }
  });
}

function approveEstablishment(id) {
  const formData = new FormData();
  formData.append("action", "approve_establishment");
  formData.append("id", id);

  fetch(API_BASE_URL, { method: "POST", body: formData })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        Swal.fire(
          "Approved!",
          "The establishment is now active.",
          "success",
        ).then(() => location.reload());
      }
    });
}

// 3. Keep DOM-dependent event listeners inside the DOMContentLoaded block
document.addEventListener("DOMContentLoaded", function () {
  if (typeof AUTO_INIT_MAP !== "undefined" && AUTO_INIT_MAP === true) {
    initMainMap();
  }

  // MODAL PICKER MAP LOGIC
  let pickerMap;
  let pickerMarker;
  const modal = document.getElementById("addEstablishmentModal");

  if (modal) {
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

          document.getElementById("lat_input").value = lat;
          document.getElementById("lng_input").value = lng;
          document.getElementById("display-lat").innerText = lat;
          document.getElementById("display-lng").innerText = lng;
        });
      } else {
        pickerMap.invalidateSize();
      }
    });
  }

  // FORM SUBMISSION
  const estForm = document.getElementById("addEstablishmentForm");
  if (estForm) {
    estForm.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!document.getElementById("lat_input").value) {
        Swal.fire("Error", "Please pin a location on the map", "error");
        return;
      }

      const formData = new FormData(this);
      fetch(API_BASE_URL, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const successMsg =
              USER_ROLE === "admin"
                ? "Establishment added!"
                : "Request submitted! Waiting for admin approval.";

            Swal.fire("Success", successMsg, "success").then(() =>
              location.reload(),
            );
          } else {
            Swal.fire("Error", data.message, "error");
          }
        });
    });
  }
});
