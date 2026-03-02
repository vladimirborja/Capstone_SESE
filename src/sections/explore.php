<!-- Explore Section -->
<?php
?>
<section id="explore" class="explore-section">
  <div class="container">
    <div class="row g-2">
      <div class="col-12">
        <div class="explore-content-wrapper">
          <div class="explore-card">
            <h2 class="explore-title">Explore</h2>
            <h3 class="explore-subtitle fs-5">Categories:</h3>
            <p class="explore-text">
              <strong>Pet-Friendly Places:</strong> <br>Find cafes, restaurants, parks, hotels, and more that welcome pets.
            </p>
            <p class="explore-text">
              <strong>Pet Care Services:</strong><br> Discover veterinary clinics, grooming shops, and other essential pet services.
            </p>
          </div>
          <div class="explore-image-container">
            <img src="images/explore_section/4.png" alt="Pet owner with pet">
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4 g-3">
      <div class="col-12">
        <h3 class="fw-bold text-primary">Featured Pet-Friendly Establishments</h3>
      </div>
      <?php if (!empty($featured_establishments)): ?>
        <?php foreach ($featured_establishments as $item): ?>
          <div class="col-md-6 col-lg-4">
            <div class="p-3 border rounded-4 bg-white h-100">
              <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
              <p class="small mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
              <p class="small mb-1"><strong>Barangay:</strong> <?php echo htmlspecialchars($item['barangay'] ?? 'Angeles City'); ?></p>
              <p class="small text-muted mb-0"><strong>Rating:</strong> 4.8 ★</p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-light border">Featured establishments will appear here once listings are available.</div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Map Row -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="map-container">
          <div id="landingLeafletMap" class="map-iframe" style="height: 450px; width: 100%;"></div>
        </div>
      </div>
    </div>

    <div class="row mt-4 g-3">
      <div class="col-12">
        <h3 class="fw-bold text-primary">Pet Lover Tips &amp; Info</h3>
      </div>
      <div class="col-md-4">
        <div class="p-3 bg-white border rounded-4 h-100">
          <h6 class="fw-bold">Responsible Pet Ownership</h6>
          <p class="small mb-0 text-muted">Keep vaccinations updated, microchip or tag your pet, and maintain a secure routine for walks and feeding.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 bg-white border rounded-4 h-100">
          <h6 class="fw-bold">If Your Pet Is Lost</h6>
          <p class="small mb-0 text-muted">Post clear photos, include barangay and date, and respond quickly to community messages for better recovery chances.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 bg-white border rounded-4 h-100">
          <h6 class="fw-bold">Finding Pet-Friendly Spots</h6>
          <p class="small mb-0 text-muted">Check policies before visiting, bring essentials, and choose places that openly support pet safety and comfort.</p>
        </div>
      </div>
    </div>

  </div>
</section>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('landingLeafletMap');
    if (!mapEl || typeof L === 'undefined') return;
    const guest = !<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const map = L.map('landingLeafletMap').setView([15.1450, 120.5887], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    fetch('features/handle_establishments.php?action=list_public_establishments')
      .then(function (res) { return res.json(); })
      .then(function (data) {
        const rows = Array.isArray(data.establishments) ? data.establishments : [];
        if (!rows.length) return;
        rows.forEach(function (place) {
          const lat = Number(place.latitude || 0);
          const lng = Number(place.longitude || 0);
          if (!lat || !lng) return;
          const marker = L.marker([lat, lng], {
            icon: (typeof getIconByType === 'function') ? getIconByType(place.type) : undefined
          }).addTo(map);

          let actionsHtml = '';
          if (guest) {
            actionsHtml = `
              <div class="small text-muted mb-2">Log in to contact the owner.</div>
              <a href="signIn.php" class="btn btn-sm btn-primary">Login</a>
              <a href="signUp.php" class="btn btn-sm btn-outline-primary mt-2">Register</a>
            `;
          } else {
            actionsHtml = `
              <a href="mains/profile.php?user_id=${Number(place.ownerId || 0)}" class="btn btn-sm btn-outline-primary">Contact Owner</a>
              <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="btn btn-sm btn-primary mt-2">View Directions</a>
            `;
          }

          marker.bindPopup(`
            <div style="min-width:210px">
              <h6 class="fw-bold text-primary mb-1">${String(place.name || 'Establishment')}</h6>
              <div class="small mb-1">${String(place.type || 'Others')}</div>
              <div class="mb-2"><span class="badge rounded-pill bg-primary">✓ Verified Establishment</span></div>
              <div class="d-grid">${actionsHtml}</div>
            </div>
          `);
        });
      });
  });
</script>