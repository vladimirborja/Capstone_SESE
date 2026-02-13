<div class="messages-container shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0"><i class="fas fa-map-marker-alt me-2"></i> PET-FRIENDLY ESTABLISHMENTS</h5>
        <button class="btn btn-primary btn-sm rounded-pill px-3 py-2" data-bs-toggle="modal" data-bs-target="#addEstablishmentModal">
            <i class="fas fa-plus me-1"></i> Add Establishment
        </button>
    </div>
    
    <div id="map" style="height: 500px; width: 100%; border-radius: 15px; border: 2px solid white;"></div>
</div>

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