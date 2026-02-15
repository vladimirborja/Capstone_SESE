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
                                <label class="form-label small fw-bold">ESTABLISHMENT TYPE</label>
                                <select name="type" id="typeSelect" class="form-select" required onchange="toggleOtherInput(this.value)">
                                    <option value="" selected disabled>Select Type</option>
                                    <option value="Restaurant / Cafe">Restaurant / Cafe</option>
                                    <option value="Hotel / Resort">Hotel / Resort</option>
                                    <option value="Mall / Shopping Center">Mall / Shopping Center</option>
                                    <option value="Park / Recreational Area">Park / Recreational Area</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>

                            <div class="mb-3" id="otherTypeDiv" style="display: none;">
                                <label class="form-label small fw-bold">PLEASE SPECIFY TYPE</label>
                                <input type="text" name="other_type_input" id="other_type_input" class="form-control" placeholder="e.g., Veterinary Clinic">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">ADDRESS</label>
                                <input type="text" name="address" class="form-control" required placeholder="Street, City, Province">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">DESCRIPTION</label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>
                            <input type="hidden" name="latitude" id="lat_input">
                            <input type="hidden" name="longitude" id="lng_input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">PIN LOCATION (Click map to set)</label>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>