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

  if (typeof initAngelesMap === "function") {
    mainMap = initAngelesMap("map");
  } else {
    mainMap = L.map("map").setView([15.1465, 120.5794], 14);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
      mainMap,
    );
  }

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
            <p class="text-muted mb-2">
                Barangay: ${place.barangay || "N/A"}
            </p>

            <div class="d-grid">
                <a href="https://www.google.com/maps/dir/?api=1&destination=${place.latitude},${place.longitude}" 
                    target="_blank" 
                    class="btn btn-sm btn-primary py-1" 
                    style="font-size: 0.75rem;">
                    View Directions
                </a>
            </div>
        </div>
        `;

      marker.bindPopup(popupContent);
      allMarkers.push({
        marker: marker,
        type: place.type,
        barangay: (place.barangay || "").toLowerCase().trim(),
      });
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
  const selectedBarangay = (
    document.getElementById("mapBarangayFilter")?.value || ""
  )
    .toLowerCase()
    .trim();
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

    if (isVisible && selectedBarangay) {
      isVisible = item.barangay === selectedBarangay;
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
      } else {
        Swal.fire("Error", data.message || "Approval failed.", "error");
      }
    });
}

function rejectEstablishment(id) {
  const hiddenId = document.getElementById("rejectEstablishmentId");
  const reasonInput = document.getElementById("rejectReasonInput");
  if (!hiddenId || !reasonInput) return;
  hiddenId.value = id;
  reasonInput.value = "";
  const modalEl = document.getElementById("rejectEstablishmentModal");
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

// 3. Keep DOM-dependent event listeners inside the DOMContentLoaded block
document.addEventListener("DOMContentLoaded", function () {
  if (
    typeof populateBarangayDropdown === "function" &&
    document.getElementById("mapBarangayFilter")
  ) {
    populateBarangayDropdown("mapBarangayFilter");
    const dropdown = document.getElementById("mapBarangayFilter");
    dropdown.addEventListener("change", function () {
      if (this.value && mainMap && typeof zoomToBarangay === "function") {
        zoomToBarangay(mainMap, this.value, null);
      }
      filterMapMarkers();
    });
  }
  if (typeof AUTO_INIT_MAP !== "undefined" && AUTO_INIT_MAP === true) {
    initMainMap();
  }

  // MODAL PICKER MAP LOGIC
  let pickerMap;
  let pickerMarker;
  let pickerMarkerBound = false;
  const modal = document.getElementById("addEstablishmentModal");
  const barangaySelect = document.getElementById("establishment-barangay-select");

  if (typeof populateBarangayDropdown === "function" && barangaySelect) {
    populateBarangayDropdown("establishment-barangay-select");
  }

  function syncLatLngDisplay() {
    const lat = document.getElementById("lat_input")?.value || "-";
    const lng = document.getElementById("lng_input")?.value || "-";
    document.getElementById("display-lat").innerText = lat;
    document.getElementById("display-lng").innerText = lng;
  }

  if (barangaySelect) {
    barangaySelect.addEventListener("change", function () {
      if (!pickerMap || typeof zoomToBarangay !== "function") return;
      pickerMarker = zoomToBarangay(pickerMap, this.value, pickerMarker);
      if (pickerMarker && typeof bindMarkerToInputs === "function") {
        if (!pickerMarkerBound) {
          bindMarkerToInputs(pickerMarker, "lat_input", "lng_input");
          pickerMarkerBound = true;
        } else {
          const ll = pickerMarker.getLatLng();
          document.getElementById("lat_input").value = ll.lat.toFixed(7);
          document.getElementById("lng_input").value = ll.lng.toFixed(7);
        }
        syncLatLngDisplay();
      }
    });
  }

  if (modal) {
    modal.addEventListener("shown.bs.modal", function () {
      if (!pickerMap) {
        if (typeof initAngelesMap === "function") {
          pickerMap = initAngelesMap("picker-map");
        } else {
          pickerMap = L.map("picker-map").setView([15.1465, 120.5794], 16);
          L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
            pickerMap,
          );
        }

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
          syncLatLngDisplay();

          if (pickerMarker && typeof bindMarkerToInputs === "function" && !pickerMarkerBound) {
            bindMarkerToInputs(pickerMarker, "lat_input", "lng_input");
            pickerMarkerBound = true;
          }
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

      if (pickerMarker) {
        const ll = pickerMarker.getLatLng();
        document.getElementById("lat_input").value = ll.lat.toFixed(7);
        document.getElementById("lng_input").value = ll.lng.toFixed(7);
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
              USER_ROLE === "admin" || USER_ROLE === "super_admin"
                ? "Establishment added successfully."
                : "Your establishment has been submitted and is pending admin approval.";

            Swal.fire("Success", successMsg, "success").then(() =>
              location.reload(),
            );
          } else {
            Swal.fire("Error", data.message, "error");
          }
        });
    });
  }

  const rejectSubmitBtn = document.getElementById("confirmRejectEstablishmentBtn");
  if (rejectSubmitBtn) {
    rejectSubmitBtn.addEventListener("click", function () {
      const id = document.getElementById("rejectEstablishmentId").value;
      const reason = document.getElementById("rejectReasonInput").value.trim();
      if (!reason) {
        Swal.fire("Required", "Please enter a rejection reason.", "warning");
        return;
      }

      const formData = new FormData();
      formData.append("action", "reject_establishment");
      formData.append("id", id);
      formData.append("reason", reason);

      fetch(API_BASE_URL, { method: "POST", body: formData })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const modalEl = document.getElementById("rejectEstablishmentModal");
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            Swal.fire("Rejected", "The request has been rejected.", "success").then(
              () => location.reload(),
            );
          } else {
            Swal.fire("Error", data.message || "Rejection failed.", "error");
          }
        });
    });
  }
});
