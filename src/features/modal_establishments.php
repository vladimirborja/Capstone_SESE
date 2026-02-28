<div class="modal fade" id="addEstablishmentModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <form id="addEstablishmentForm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalLabel">Register New Establishment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">ESTABLISHMENT NAME</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Paw-some Cafe">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">CATEGORY</label>
                                <select name="type" id="typeSelect" class="form-select" required onchange="toggleOtherInput(this.value)">
                                    <option value="" selected disabled>Select Type</option>
                                    <option value="Restaurant / Cafe">Restaurant / Cafe</option>
                                    <option value="Hotel / Resort">Hotel / Resort</option>
                                    <option value="Mall / Shopping Center">Mall / Shopping Center</option>
                                    <option value="Park / Recreational Area">Park / Recreational Area</option>
                                    <option value="Pet Salon">Pet Salon</option>
                                    <option value="Veterinary Clinic">Veterinary Clinic</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>

                            <div class="mb-3" id="otherTypeDiv" style="display: none;">
                                <label class="form-label small fw-bold">PLEASE SPECIFY TYPE</label>
                                <input type="text" name="other_type_input" id="other_type_input" class="form-control" placeholder="e.g., Veterinary Clinic">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">ADDRESS</label>
                                <input type="text" name="address" class="form-control" required placeholder="e.g., #12 Friendship Highway, Angeles City">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">BARANGAY</label>
                                <select name="barangay" id="establishment-barangay-select" class="form-select" required></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">DESCRIPTION</label>
                                <textarea name="description" class="form-control" rows="3" required placeholder="Tell pet owners what makes your place special for them and their pets..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">ESTABLISHMENT POLICIES</label>
                                <textarea name="policies" class="form-control" rows="2" placeholder="e.g., Pets must be leashed, max 2 pets per visit, no aggressive breeds..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">PET TYPES ALLOWED</label>
                                <div class="d-flex flex-wrap gap-3 small">
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="pet_types_allowed[]" value="Dogs" id="petAllowedDog"><label class="form-check-label" for="petAllowedDog">Dogs</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="pet_types_allowed[]" value="Cats" id="petAllowedCat"><label class="form-check-label" for="petAllowedCat">Cats</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="pet_types_allowed[]" value="Birds" id="petAllowedBird"><label class="form-check-label" for="petAllowedBird">Birds</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="pet_types_allowed[]" value="Small Animals" id="petAllowedSmall"><label class="form-check-label" for="petAllowedSmall">Small Animals</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="pet_types_allowed[]" value="All Pets" id="petAllowedAll"><label class="form-check-label" for="petAllowedAll">All Pets</label></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">VENUE SIZE</label>
                                <select name="venue_size" class="form-select">
                                    <option value="">Select Venue Size</option>
                                    <option value="Small">Small</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Large">Large</option>
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">OPERATING HOURS</label>
                                    <input type="text" name="operating_hours" class="form-control" placeholder="e.g., Mon-Sun 8:00 AM - 8:00 PM">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">CONTACT NUMBER</label>
                                    <input type="tel" name="contact_number" class="form-control" placeholder="e.g., 09123456789">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">FACEBOOK LINK (OPTIONAL)</label>
                                <input type="text" name="social_links" class="form-control" placeholder="e.g., https://facebook.com/yourpage">
                            </div>
                            <input type="hidden" name="latitude" id="lat_input">
                            <input type="hidden" name="longitude" id="lng_input">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="estGuidelinesAgree" name="guidelines_agree" required>
                                <label class="form-check-label small" for="estGuidelinesAgree">
                                    I agree to the <a href="../pages/community-guidelines.php" target="_blank" rel="noopener noreferrer">Community Guidelines</a>.
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">PIN LOCATION (Auto-zoom by barangay, drag to refine)</label>
                            <div id="picker-map" style="height: 250px; width: 100%; border-radius: 10px; border: 1px solid #ddd;"></div>
                            <p class="text-muted mt-2" style="font-size: 0.7rem;">Lat: <span id="display-lat">-</span> | Lng: <span id="display-lng">-</span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Establishment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />