<!-- Lost & Found Section -->
<section id="lost-found" class="lost-found-section">
    <div class="container">
        <h2 class="A text-center" style="font-size: 50px;">
            <span style="color: #219AFF;">Lost</span> <span style="color: black;">& Found</span>
        </h2>

        <p class="section-subtitle text-center fs-5">
            Helping Every Pet Find Their Way Home <br>
            A dedicated space to help reunite lost pets with their families and find new homes for pets in need of adoption.
        </p>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-outline-primary lost-prev-btn">❮ Prev</button>
            <button class="btn btn-outline-primary lost-next-btn">Next ❯</button>
        </div>

        <!-- CAROUSEL -->
        <div id="lost_found_carousel" class="owl-carousel owl-theme">
            <?php if (!empty($latest_lost_found)): ?>
                <?php foreach ($latest_lost_found as $pet): ?>
                    <div class="item">
                        <div class="pet-card-vertical">
                            <div class="pet-image-container">
                                <span class="badge-latest">Latest</span>
                                <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'images/lost&found_section/5.png'; ?>" alt="Pet Photo">
                            </div>
                            <div class="pet-details-container">
                                <h3 class="pet-status fs-5">Status: <span><?php echo strtoupper(htmlspecialchars($pet['category'])); ?></span></h3>
                                <div class="pet-info-item"><label>Pet Name:</label>
                                    <p><?php echo htmlspecialchars($pet['pet_name']); ?></p>
                                </div>
                                <div class="pet-info-item"><label>Type/Breed:</label>
                                    <p><?php echo htmlspecialchars($pet['breed'] ?? 'Unknown'); ?></p>
                                </div>
                                <div class="pet-info-item"><label>Last Seen:</label>
                                    <p><?php echo htmlspecialchars($pet['last_seen_location'] ?? 'Angeles City'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>