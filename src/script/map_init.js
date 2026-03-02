// 1. Declare variables globally so all functions can access them
let mainMap;
let isMapInitialized = false;
let allMarkers = []; // Array to store marker objects and their types

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function escapeAttr(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

function buildTypeIcon(svgContent, color = "#1e88e5") {
  return L.divIcon({
    className: "custom-est-marker",
    iconSize: [40, 40],
    iconAnchor: [20, 39],
    popupAnchor: [0, -34],
    html: `
      <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M20 1.8C12.27 1.8 6 8.07 6 15.8c0 9.73 10.46 20.65 13.04 23.2a1.4 1.4 0 0 0 1.92 0C23.54 36.45 34 25.53 34 15.8 34 8.07 27.73 1.8 20 1.8z" fill="${color}" stroke="#fff" stroke-width="2"/>
        <circle cx="20" cy="16" r="9.2" fill="rgba(255,255,255,.16)"/>
      </svg>
      <div style="position:absolute;left:50%;top:40%;transform:translate(-50%,-50%);display:flex;align-items:center;justify-content:center;">
        ${svgContent}
      </div>
    `,
  });
}

const ICON_RESTAURANT = buildTypeIcon(
  '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 2v8"/><path d="M8 2v8"/><path d="M6 10v12"/><path d="M14 2v7a3 3 0 0 0 6 0V2"/><path d="M17 12v10"/></svg>',
  "#5b8a3c",
);
const ICON_HOTEL = buildTypeIcon(
  '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 20v-6h8v6"/><path d="M8 8h.01"/><path d="M12 8h.01"/><path d="M16 8h.01"/></svg>',
  "#6b7280",
);
const ICON_MALL = buildTypeIcon(
  '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 12H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>',
  "#7c4dff",
);
const ICON_PARK = buildTypeIcon(
  '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l4 5h-8l4-5z"/><path d="M7 12l5-6 5 6H7z"/><path d="M12 12v9"/></svg>',
  "#2f9e44",
);
const ICON_PET_SERVICE = buildTypeIcon(
  '<svg viewBox="0 0 24 24" width="22" height="22" fill="#fff" aria-hidden="true"><path d="M12 13.7c-1.35 0-2.42 1.02-2.42 2.32 0 1.07.93 2.06 2.42 2.06s2.42-.99 2.42-2.06c0-1.3-1.07-2.32-2.42-2.32zM6.18 12.03c1.12 0 2.03-.97 2.03-2.16 0-1.2-.91-2.17-2.03-2.17S4.16 8.67 4.16 9.87c0 1.19.9 2.16 2.02 2.16zm11.64 0c1.12 0 2.02-.97 2.02-2.16 0-1.2-.9-2.17-2.02-2.17s-2.03.97-2.03 2.17c0 1.19.91 2.16 2.03 2.16zM9.28 7.58c1.12 0 2.03-.97 2.03-2.16 0-1.2-.91-2.17-2.03-2.17S7.25 4.22 7.25 5.42c0 1.19.9 2.16 2.03 2.16zm5.44 0c1.12 0 2.03-.97 2.03-2.16 0-1.2-.91-2.17-2.03-2.17s-2.03.97-2.03 2.17c0 1.19.91 2.16 2.03 2.16z"/></svg>',
  "#ec4899",
);
const ICON_OTHER = buildTypeIcon(
  '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>',
  "#1e88e5",
);

function normalizeTypeBucket(type) {
  const normalized = String(type || "").toLowerCase().trim();
  if (normalized.includes("restaurant") || normalized.includes("cafe")) return "restaurant";
  if (normalized.includes("hotel") || normalized.includes("resort")) return "hotel";
  if (normalized.includes("mall") || normalized.includes("shopping")) return "mall";
  if (normalized.includes("park") || normalized.includes("recreational")) return "park";
  if (
    normalized.includes("pet salon") ||
    normalized.includes("veterinary") ||
    normalized.includes("veterinarian") ||
    normalized.includes("pet salons & veterinary clinic")
  ) {
    return "pet_service";
  }
  if (normalized === "others" || normalized === "other") return "others";
  return "others";
}

function getIconByType(type) {
  const bucket = normalizeTypeBucket(type);
  if (bucket === "restaurant") return ICON_RESTAURANT;
  if (bucket === "hotel") return ICON_HOTEL;
  if (bucket === "mall") return ICON_MALL;
  if (bucket === "park") return ICON_PARK;
  if (bucket === "pet_service") return ICON_PET_SERVICE;
  return ICON_OTHER;
}

function getPopupActionState(place) {
  const viewerId = Number(
    typeof CURRENT_USER_ID !== "undefined" ? CURRENT_USER_ID : 0,
  );
  const isLoggedIn = viewerId > 0;
  const ownerId = Number(place.ownerId || place.owner_id || 0);
  const ownerVerified = Number(place.ownerVerified || place.owner_verified || 0) === 1;
  if (!isLoggedIn) return "guest";
  if (ownerVerified && viewerId > 0 && ownerId === viewerId) return "verified_owner";
  if (!ownerVerified || ownerId <= 0) return "claimable";
  return "visitor";
}

function buildPopupVerificationLabel(place, state) {
  if (state === "verified_owner") {
    return `<div class="mb-2"><span class="badge rounded-pill bg-success">✓ Verified by Owner</span></div>`;
  }
  const ownerVerified = Number(place.ownerVerified || place.owner_verified || 0) === 1;
  if (ownerVerified) {
    return `<div class="mb-2"><span class="badge rounded-pill bg-primary">✓ Verified Establishment</span></div>`;
  }
  return "";
}

function buildPopupActions(place) {
  const state = getPopupActionState(place);
  const lat = Number(place.latitude || 0);
  const lng = Number(place.longitude || 0);
  const profileBase =
    typeof PROFILE_BASE_URL !== "undefined"
      ? PROFILE_BASE_URL
      : "profile.php?user_id=";
  const viewerId = Number(
    typeof CURRENT_USER_ID !== "undefined" ? CURRENT_USER_ID : 0,
  );
  const ownerId = Number(place.ownerId || place.owner_id || 0);
  const directionsHref = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
  const directionsBtnStyle =
    "font-size:0.75rem;background:#0d6efd;color:#fff;border:1px solid #0b5ed7;padding:0.35rem 0.5rem;text-decoration:none;";
  const loginUrl = typeof LOGIN_URL !== "undefined" ? LOGIN_URL : "signIn.php";
  const registerUrl = typeof REGISTER_URL !== "undefined" ? REGISTER_URL : "signUp.php";

  if (state === "guest") {
    return `
      <div class="small text-muted mb-2">Log in to contact the owner.</div>
      <a href="${loginUrl}" class="btn btn-sm btn-primary py-1" style="font-size:0.75rem;">Login</a>
      <a href="${registerUrl}" class="btn btn-sm btn-outline-primary py-1 mt-2" style="font-size:0.75rem;">Register</a>
      <a href="${directionsHref}" target="_blank" class="btn btn-sm map-directions-btn py-1 mt-2" style="${directionsBtnStyle}">View Directions</a>
    `;
  }

  if (state === "verified_owner") {
    return `
      <a href="${profileBase}${viewerId}" class="btn btn-sm btn-outline-primary py-1 mt-2" style="font-size:0.75rem;">Manage Establishment</a>
      <a href="${directionsHref}" target="_blank" class="btn btn-sm map-directions-btn py-1 mt-2" style="${directionsBtnStyle}">View Directions</a>
    `;
  }

  if (state === "claimable") {
    const estId = Number(place.id || 0);
    const estName = escapeAttr(place.name || "Establishment");
    if (place.claimPendingByCurrentUser) {
      return `
        <button type="button" class="btn btn-sm btn-outline-warning py-1" style="font-size:0.75rem;" disabled>
          Your claim is under review by admin
        </button>
        <a href="${directionsHref}" target="_blank" class="btn btn-sm map-directions-btn py-1 mt-2" style="${directionsBtnStyle}">View Directions</a>
      `;
    }
    const canClaimHere = !!document.getElementById("ownershipClaimModal");
    if (!canClaimHere) {
      return `
        <button type="button" class="btn btn-sm btn-outline-secondary py-1" style="font-size:0.75rem;" disabled>Owner Not Yet Verified</button>
        <a href="${directionsHref}" target="_blank" class="btn btn-sm map-directions-btn py-1 mt-2" style="${directionsBtnStyle}">View Directions</a>
      `;
    }
    return `
      <button type="button" class="btn btn-sm btn-warning py-1 claim-owner-btn" style="font-size:0.75rem;"
              data-est-id="${estId}" data-est-name="${estName}">
        🏪 Are You the Owner? Verify Now
      </button>
      <a href="${directionsHref}" target="_blank" class="btn btn-sm map-directions-btn py-1 mt-2" style="${directionsBtnStyle}">View Directions</a>
    `;
  }

  return `
    <a href="${profileBase}${ownerId}" class="btn btn-sm btn-outline-primary py-1" style="font-size:0.75rem;">Contact Owner</a>
    <a href="${directionsHref}" target="_blank" class="btn btn-sm map-directions-btn py-1 mt-2" style="${directionsBtnStyle}">View Directions</a>
  `;
}

function openOwnershipClaimModal(establishmentId, establishmentName) {
  const modalEl = document.getElementById("ownershipClaimModal");
  if (!modalEl) return;
  const idInput = document.getElementById("claim_establishment_id");
  const nameLabel = document.getElementById("claimEstablishmentName");
  if (idInput) idInput.value = String(establishmentId || "");
  if (nameLabel) nameLabel.textContent = establishmentName || "Establishment";
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

function buildPopupContent(place) {
  const actionButtons = buildPopupActions(place);
  const verificationLabel = buildPopupVerificationLabel(place, getPopupActionState(place));
  return `
    <div class="p-1" style="min-width: 200px;">
      <h6 class="fw-bold text-primary mb-1 d-flex align-items-center">
        <i class="bi bi-geo-alt-fill me-2"></i> ${escapeHtml(place.name)}
      </h6>
      <span class="badge bg-primary rounded-pill" style="font-size: 0.80rem;">${escapeHtml(place.type)}</span>
      <p class="text-muted mb-2">
        Barangay: ${escapeHtml(place.barangay || "N/A")}
      </p>
      ${verificationLabel}
      <div class="d-grid">
        ${actionButtons}
      </div>
    </div>
  `;
}

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
    if (btn) btn.innerHTML = "View Feed";
    initMainMap(); // Call the initialization function
  } else {
    feed.style.display = "block";
    mapBox.style.display = "none";
    if (btn) btn.innerHTML = "Location";
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
      const marker = L.marker([place.latitude, place.longitude], {
        icon: getIconByType(place.type),
      }).addTo(mainMap);
      marker.bindPopup(buildPopupContent(place));
      allMarkers.push({
        marker: marker,
        id: Number(place.id || 0),
        type: place.type,
        typeBucket: normalizeTypeBucket(place.type),
        barangay: (place.barangay || "").toLowerCase().trim(),
        place: place,
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
  const allOption = document.getElementById("fAll");
  const selectedTypeValues = Array.from(
    document.querySelectorAll(".filter-checkbox:checked"),
  ).map((cb) => cb.value);
  const hasAll = selectedTypeValues.includes("__all__");
  const selectedBuckets = selectedTypeValues
    .filter((value) => value !== "__all__")
    .map((value) => normalizeTypeBucket(value));
  if (allOption && hasAll && selectedBuckets.length > 0) {
    allOption.checked = false;
  } else if (allOption && !hasAll && selectedBuckets.length === 0) {
    allOption.checked = true;
  }
  const selectedBarangay = (
    document.getElementById("mapBarangayFilter")?.value || ""
  )
    .toLowerCase()
    .trim();

  allMarkers.forEach((item) => {
    let isVisible = false;

    if (hasAll || selectedBuckets.length === 0) {
      isVisible = true; // Show all if none selected
    } else {
      isVisible = selectedBuckets.includes(item.typeBucket);
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
  const filterBar = document.getElementById("filterByType");
  if (filterBar) {
    filterBar.style.display = "flex";
    filterBar.classList.remove("hidden");
  }
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

  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".claim-owner-btn");
    if (!btn) return;
    const estId = Number(btn.getAttribute("data-est-id") || 0);
    const estName = btn.getAttribute("data-est-name") || "Establishment";
    openOwnershipClaimModal(estId, estName);
  });

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
            const selectedType =
              document.getElementById("typeSelect")?.value === "Others"
                ? document.getElementById("other_type_input")?.value
                : document.getElementById("typeSelect")?.value;
            pickerMarker = L.marker(e.latlng, {
              icon: getIconByType(selectedType),
            }).addTo(pickerMap);
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
  const claimForm = document.getElementById("ownershipClaimForm");
  const claimRejectSubmitBtn = document.getElementById("confirmRejectOwnershipClaimBtn");
  const typeSelect = document.getElementById("typeSelect");
  const otherTypeInput = document.getElementById("other_type_input");
  const updatePickerIcon = () => {
    if (!pickerMarker) return;
    const selectedType =
      typeSelect?.value === "Others" ? otherTypeInput?.value : typeSelect?.value;
    pickerMarker.setIcon(getIconByType(selectedType));
  };
  if (typeSelect) typeSelect.addEventListener("change", updatePickerIcon);
  if (otherTypeInput) otherTypeInput.addEventListener("input", updatePickerIcon);

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

  if (claimForm) {
    claimForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(claimForm);
      formData.append("action", "submit_ownership_claim");
      fetch(API_BASE_URL, { method: "POST", body: formData })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const modalEl = document.getElementById("ownershipClaimModal");
            if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            const claimEstablishmentId = Number(
              document.getElementById("claim_establishment_id")?.value || 0,
            );
            const markerEntry = allMarkers.find((item) => item.id === claimEstablishmentId);
            if (markerEntry && markerEntry.place) {
              if (data.status === "self_verified") {
                markerEntry.place.ownerVerified = 1;
                markerEntry.place.ownerId = Number(
                  typeof CURRENT_USER_ID !== "undefined" ? CURRENT_USER_ID : 0,
                );
                markerEntry.place.verifiedBy = "self";
              } else {
                markerEntry.place.claimPendingByCurrentUser = true;
              }
              markerEntry.marker.setPopupContent(buildPopupContent(markerEntry.place));
              markerEntry.marker.openPopup();
            }
            claimForm.reset();
            if (data.status === "self_verified") {
              Swal.fire(
                "Verified",
                "Your establishment has been verified successfully.",
                "success",
              );
            } else {
              Swal.fire(
                "Submitted",
                "Your claim is under review by admin.",
                "info",
              );
            }
          } else {
            Swal.fire("Error", data.message || "Unable to submit claim.", "error");
          }
        })
        .catch(() =>
          Swal.fire("Error", "Connection failed while submitting claim.", "error"),
        );
    });
  }

  if (claimRejectSubmitBtn) {
    claimRejectSubmitBtn.addEventListener("click", function () {
      const claimId = document.getElementById("rejectOwnershipClaimId")?.value || "";
      const reasonEl = document.getElementById("rejectOwnershipClaimReason");
      const reason = (reasonEl?.value || "").trim();
      if (!claimId) {
        Swal.fire("Error", "Missing ownership claim ID.", "error");
        return;
      }
      if (!reason) {
        Swal.fire("Required", "Rejection reason is required.", "warning");
        return;
      }
      const fd = new FormData();
      fd.append("action", "reject_ownership_claim");
      fd.append("claim_id", String(claimId));
      fd.append("reason", reason);
      fetch(API_BASE_URL, { method: "POST", body: fd })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const modalEl = document.getElementById("rejectOwnershipClaimModal");
            if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            if (reasonEl) reasonEl.value = "";
            Swal.fire("Rejected", "Ownership claim rejected.", "success").then(() =>
              location.reload(),
            );
          } else {
            Swal.fire("Error", data.message || "Rejection failed.", "error");
          }
        })
        .catch(() =>
          Swal.fire("Error", "Connection failed while rejecting claim.", "error"),
        );
    });
  }
});

function approveOwnershipClaim(claimId) {
  const fd = new FormData();
  fd.append("action", "approve_ownership_claim");
  fd.append("claim_id", String(claimId));
  fetch(API_BASE_URL, { method: "POST", body: fd })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        Swal.fire("Approved", "Ownership claim approved.", "success").then(() =>
          location.reload(),
        );
      } else {
        Swal.fire("Error", data.message || "Approval failed.", "error");
      }
    });
}

function rejectOwnershipClaim(claimId) {
  const hiddenId = document.getElementById("rejectOwnershipClaimId");
  const reasonEl = document.getElementById("rejectOwnershipClaimReason");
  const modalEl = document.getElementById("rejectOwnershipClaimModal");
  if (!hiddenId || !reasonEl || !modalEl) {
    Swal.fire("Error", "Reject modal is not available.", "error");
    return;
  }
  hiddenId.value = String(claimId);
  reasonEl.value = "";
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}
