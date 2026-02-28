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
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3875.1234567890123!2d120.58912345678901!3d15.145678901234567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b7c123456789%3A0xabcdef1234567890!2sAngeles%20City%2C%20Pampanga%2C%20Philippines!5e0!3m2!1sen!2sus!4v1697041234567!5m2!1sen!2sus"
            width="100%" height="450" style="border:0;"
            allowfullscreen loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="map-iframe">
          </iframe>

         <div class="map-buttons">
          <a href="./signUp.php" class="btn btn-primary fs-5">Pet-Friendly Places</a>
          <a href="./signUp.php" class="btn btn-primary fs-5">Pet Care Services</a>
        </div>
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