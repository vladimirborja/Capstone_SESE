<div class="messages-container shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0"><i class="fas fa-map-marker-alt me-2"></i> PET-FRIENDLY ESTABLISHMENTS</h5>

        <div class="d-flex gap-2 align-items-center">
            <select id="mapBarangayFilter" class="form-select form-select-sm" style="max-width: 220px;">
                <option value="">All Barangays</option>
            </select>
            <button class="btn btn-warning btn-sm rounded-pill px-3 py-2" data-bs-toggle="modal" data-bs-target="#pendingModal">
                <i class="fas fa-clock me-1"></i> Pending Requests
            </button>
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true)): ?>
                <button class="btn btn-outline-success btn-sm rounded-pill px-3 py-2" type="button" data-bs-toggle="modal" data-bs-target="#establishmentRecordsModal">
                    <i class="fas fa-table me-1"></i> Establishment Records
                </button>
            <?php endif; ?>
            <button class="btn btn-primary btn-sm rounded-pill px-3 py-2" data-bs-toggle="modal" data-bs-target="#addEstablishmentModal">
                <i class="fas fa-plus me-1"></i> Add Establishment
            </button>
        </div>
        
    </div>
    <div class="p-2 border rounded-3 mb-3 bg-light d-flex flex-wrap gap-3" id="filterByType">
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="__all__" id="fAll" checked onchange="filterMapMarkers()">
            <label class="form-check-label small" for="fAll">All</label>
        </div>
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="Restaurant / Cafe" id="f1" onchange="filterMapMarkers()">
            <label class="form-check-label small" for="f1">Restaurant / Cafe</label>
        </div>
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="Hotel / Resort" id="f2" onchange="filterMapMarkers()">
            <label class="form-check-label small" for="f2">Hotel / Resort</label>
        </div>
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="Mall / Shopping Center" id="f3" onchange="filterMapMarkers()">
            <label class="form-check-label small" for="f3">Mall / Shopping Center</label>
        </div>
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="Park / Recreational Area" id="f4" onchange="filterMapMarkers()">
            <label class="form-check-label small" for="f4">Park / Recreational Area</label>
        </div>
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="Pet Salons & Veterinary Clinic" id="f6" onchange="filterMapMarkers()">
            <label class="form-check-label small" for="f6">Pet Salons & Veterinary Clinic</label>
        </div>
        <div class="form-check">
            <input class="form-check-input filter-checkbox" type="checkbox" value="Others" id="f5" onchange="filterMapMarkers()">
            <label class="form-check-label small" for="f5">Others</label>
        </div>
    </div>
    <div id="map" style="height: 500px; width: 100%; border-radius: 15px; border: 2px solid white;"></div>
</div>

<?php if (isset($adoption_activity)): ?>
<div class="messages-container shadow-sm mt-3">
    <h5 class="fw-bold mb-3"><i class="fas fa-hand-holding-heart me-2"></i> Adoption Activity (Read Only)</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Adopter</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($adoption_activity)): ?>
                    <?php foreach ($adoption_activity as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['pet_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['adopter_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['owner_name'] ?? 'N/A') ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($row['status'] ?? 'pending') ?></td>
                            <td><?= !empty($row['created_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))) : 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No adoption activity yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true)): ?>
    <div class="modal fade" id="establishmentRecordsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Establishment Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Establishment</th>
                                    <th>Category</th>
                                    <th>Barangay</th>
                                    <th>Submitted By</th>
                                    <th>Status</th>
                                    <th>Admin</th>
                                    <th>Date Submitted</th>
                                    <th>Date Actioned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($establishment_records ?? [])): ?>
                                    <?php foreach ($establishment_records as $rec): ?>
                                        <?php $isApproved = strtolower($rec['status']) === 'approved'; ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rec['name']) ?></td>
                                            <td><?= htmlspecialchars($rec['type']) ?></td>
                                            <td><?= htmlspecialchars($rec['barangay'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($rec['submitted_by'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge <?= $isApproved ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= strtoupper(htmlspecialchars($rec['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($rec['actioned_by'] ?? 'N/A') ?></td>
                                            <td><?= !empty($rec['submitted_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($rec['submitted_at']))) : 'N/A' ?></td>
                                            <td><?= !empty($rec['actioned_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($rec['actioned_at']))) : 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">No approved/rejected establishment records yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="pendingModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pending Establishment Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Barangay</th>
                            <th>Submitted By</th>
                            <th>Date Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT e.*, u.full_name AS requester_name 
                                FROM establishments e 
                                LEFT JOIN users u ON e.requester_id = u.user_id
                                WHERE e.status = 'pending'";

                        $pending = $pdo->query($sql)->fetchAll();
                        foreach ($pending as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['type']) ?></td>
                                <td><?= htmlspecialchars($row['barangay'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['requester_name']) ?></td>
                                <td><small><?= !empty($row['created_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))) : 'N/A'; ?></small></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="approveEstablishment(<?= $row['id'] ?>)">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectEstablishment(<?= $row['id'] ?>)">Reject</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pending)): ?>
                            <tr><td colspan="6" class="text-center">No pending requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true)): ?>
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-file-signature me-2"></i>Ownership Claims</h6>
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Establishment</th>
                                <th>Claimant</th>
                                <th>Permit #</th>
                                <th>Contact</th>
                                <th>Message</th>
                                <th>Document</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $claimRows = [];
                            try {
                                $claimRows = $pdo->query(
                                    "SELECT bc.id AS claim_id, bc.permit_number, bc.contact_number, bc.message,
                                            bc.document_path, bc.submitted_at, e.name AS establishment_name,
                                            COALESCE(u.full_name, 'N/A') AS claimant_name
                                     FROM ownership_claims bc
                                     JOIN establishments e ON e.id = bc.establishment_id
                                     LEFT JOIN users u ON u.user_id = bc.claimant_user_id
                                     WHERE bc.status IN ('pending','self_verified','admin_verified','rejected')
                                     ORDER BY bc.submitted_at DESC"
                                )->fetchAll(PDO::FETCH_ASSOC);
                            } catch (Exception $e) {
                                $claimRows = [];
                            }
                            ?>
                            <?php if (!empty($claimRows)): ?>
                                <?php foreach ($claimRows as $claim): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($claim['establishment_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($claim['claimant_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($claim['permit_number'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($claim['contact_number'] ?? 'N/A') ?></td>
                                        <td style="max-width: 220px; white-space: normal;"><?= htmlspecialchars($claim['message'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (!empty($claim['document_path'])): ?>
                                                <a href="<?= htmlspecialchars($claim['document_path']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">View File</a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?= !empty($claim['submitted_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($claim['submitted_at']))) : 'N/A' ?></small></td>
                                        <td>
                                            <?php
                                                $claimStatus = strtolower((string)($claim['status'] ?? 'pending'));
                                                $statusClass = 'bg-warning text-dark';
                                                $statusLabel = 'Pending';
                                                if ($claimStatus === 'self_verified') { $statusClass = 'bg-success'; $statusLabel = 'Self-Verified'; }
                                                elseif ($claimStatus === 'admin_verified') { $statusClass = 'bg-primary'; $statusLabel = 'Admin-Verified'; }
                                                elseif ($claimStatus === 'rejected') { $statusClass = 'bg-danger'; $statusLabel = 'Rejected'; }
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                        </td>
                                        <td>
                                            <?php if (in_array($claimStatus, ['pending', 'self_verified'], true)): ?>
                                                <button class="btn btn-success btn-sm" onclick="approveOwnershipClaim(<?= (int)$claim['claim_id'] ?>)">Admin Verify</button>
                                                <button class="btn btn-danger btn-sm" onclick="rejectOwnershipClaim(<?= (int)$claim['claim_id'] ?>)">Reject</button>
                                            <?php else: ?>
                                                <span class="text-muted small">Finalized</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center">No ownership claims yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectOwnershipClaimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Ownership Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectOwnershipClaimId">
                <label for="rejectOwnershipClaimReason" class="form-label fw-bold">Reason (required)</label>
                <textarea id="rejectOwnershipClaimReason" class="form-control" rows="4" required
                          style="pointer-events:auto; z-index:2; position:relative;"
                          placeholder="Type the reason for rejecting this ownership claim..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectOwnershipClaimBtn">Reject Claim</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectEstablishmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Establishment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectEstablishmentId">
                <label for="rejectReasonInput" class="form-label fw-bold">Reason (required)</label>
                <textarea id="rejectReasonInput" class="form-control" rows="4" placeholder="Type the reason for rejection..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectEstablishmentBtn">Reject</button>
            </div>
        </div>
    </div>
</div>

<?php require_once('modal_establishments.php'); ?>