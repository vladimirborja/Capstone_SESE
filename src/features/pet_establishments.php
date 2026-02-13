<div class="messages-container shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0"><i class="fas fa-map-marker-alt me-2"></i> PET-FRIENDLY ESTABLISHMENTS</h5>
        <div class="d-flex gap-2"><button class="btn btn-warning btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#pendingModal">
            <i class="fas fa-clock me-1"></i> Pending Requests
        </button>
        <button class="btn btn-primary btn-sm rounded-pill px-3 py-2" data-bs-toggle="modal" data-bs-target="#addEstablishmentModal">
            <i class="fas fa-plus me-1"></i> Add Establishment
        </button></div>
        
    </div>
    
    <div id="map" style="height: 500px; width: 100%; border-radius: 15px; border: 2px solid white;"></div>
</div>

<div class="modal fade" id="pendingModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pending Establishment Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Requester</th>
                            <th>Establishment</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT e.*, u.full_name AS requester_name 
                                FROM establishments e 
                                LEFT JOIN users u ON e.requester_id = u.user_id 
                                WHERE e.status = 'pending'
                                AND e.requester_id IS NOT NULL";

                        $pending = $pdo->query($sql)->fetchAll();
                        foreach ($pending as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['requester_name']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><small><?= htmlspecialchars($row['address']) ?></small></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="approveEstablishment(<?= $row['id'] ?>)">Approve</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pending)): ?>
                            <tr><td colspan="3" class="text-center">No pending requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('modal_establishments.php'); ?>