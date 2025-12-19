<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .badge-dot {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .badge-dot i {
        width: .55rem;
        height: .55rem;
        border-radius: 50%;
        display: inline-block;
    }

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 m-0">Notificaciones</h1>
    <form action="<?= base_url('notificaciones1/leertodas') ?>" method="post" class="m-0">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-primary">
            <i class="bi bi-check2-all me-1"></i> Marcar todas como leídas
        </button>
    </form>
</div>

<?php if (empty($items)): ?>
    <div class="alert alert-light border d-flex align-items-center" role="alert">
        <i class="bi bi-bell me-2"></i>
        No hay notificaciones.
    </div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($items as $n):
            $isLeida = (int) ($n['is_leida'] ?? 0) === 1;
            $color = $n['color'] ?? '#0d6efd';
            $nivel = $n['nivel'] ?? 'info';
            $titulo = $n['titulo'] ?? 'Notificación';
            $sub = $n['sub'] ?? '';
            $msg = $n['mensaje'] ?? '';
            $fecha = $n['created_at'] ?? '';
            ?>
            <div class="list-group-item p-3 <?= $isLeida ? 'bg-white' : 'bg-light' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <div class="badge-dot mb-1">
                            <i style="background: <?= esc($color) ?>;"></i>
                            <span class="badge rounded-pill text-bg-secondary text-capitalize"><?= esc($nivel) ?></span>
                        </div>
                        <h6 class="mb-1"><?= esc($titulo) ?></h6>
                        <?php if ($sub): ?>
                            <div class="text-muted small mb-1"><?= esc($sub) ?></div><?php endif; ?>
                        <?php if ($msg): ?>
                            <div class="mb-1"><?= esc($msg) ?></div><?php endif; ?>
                        <div class="text-muted small"><i class="bi bi-clock me-1"></i><?= esc($fecha) ?></div>
                    </div>

                    <div class="text-nowrap">
                        <?php if (!$isLeida): ?>
                            <form action="<?= base_url('notificaciones1/leer/' . $n['id']) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-success me-1" title="Marcar como leída">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                        <button type="button" class="btn btn-sm btn-outline-danger eliminar-notif" data-id="<?= $n['id'] ?>"
                            data-url="<?= base_url('notificaciones1/eliminar/' . $n['id']) ?>" title="Quitar de mi lista">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
document.querySelectorAll('.eliminar-notif').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas quitar esta notificación de tu lista?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrf = document.querySelector('input[name="<?= csrf_token() ?>"]');
                if (csrf) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = csrf.name;
                    csrfInput.value = csrf.value;
                    form.appendChild(csrfInput);
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>

<?= $this->endSection() ?>