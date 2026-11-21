<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="project-avatar"><i class="bi bi-pc-display"></i></div>
        <div>
            <h1 class="h3 fw-bold mb-1">Machines</h1>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
            data-bs-toggle="modal" 
            data-bs-target="#machineEditModal"
            data-machine-id="-1"
            data-machine-name=""
            data-machine-board-id=""
            data-machine-hc=""
            data-machine-json=""
            data-machine-comment="">
            <i class="bi bi-plus-square-dotted me-2"></i>Machine
        </button>
    </div>
</div>

<div class="modern-card mb-4">
    <div class="card-header-gradient d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle fs-5 text-primary"></i>
            <span class="fw-semibold">Below is the list of machines associated with the respective boards.</span>
        </div>
    </div>
</div>

<div id="machines_container">
<?= render_machines() ?>
</div>
