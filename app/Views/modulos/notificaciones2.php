<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .notification-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .notification-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .notification-card.unread {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .notification-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-bell"></i> Notificaciones
                <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger"><?= $unreadCount ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-muted mb-0">Sistema de notificaciones en tiempo real</p>
        </div>
        <div class="btn-group">
            <a href="<?= base_url('modulo3/notificaciones2/generate-test') ?>" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Generar Pruebas
            </a>
            <?php if ($unreadCount > 0): ?>
                <form action="<?= base_url('modulo3/notificaciones2/mark-all-read') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-check2-all"></i> Marcar Todas Leídas
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Notifications List -->
    <?php if (empty($notifications)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">No hay notificaciones</h5>
                <p class="text-muted">Cuando recibas notificaciones, aparecerán aquí</p>
                <a href="<?= base_url('modulo3/notificaciones2/generate-test') ?>" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Generar Notificaciones de Prueba
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($notifications as $notif):
                $isRead = (int) ($notif['is_leida'] ?? 0) === 1;
                $nivel = $notif['nivel'] ?? 'info';
                $color = $notif['color'] ?? '#6c757d';

                // Icon and color based on level
                $iconMap = [
                    'danger' => ['icon' => 'bi-exclamation-triangle-fill', 'bg' => 'danger'],
                    'warning' => ['icon' => 'bi-exclamation-circle-fill', 'bg' => 'warning'],
                    'success' => ['icon' => 'bi-check-circle-fill', 'bg' => 'success'],
                    'info' => ['icon' => 'bi-info-circle-fill', 'bg' => 'info'],
                ];
                $iconData = $iconMap[$nivel] ?? $iconMap['info'];
                ?>
                <div class="col-12">
                    <div class="card notification-card <?= !$isRead ? 'unread' : '' ?> shadow-sm">
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                <!-- Icon -->
                                <div
                                    class="notification-icon bg-<?= $iconData['bg'] ?> bg-opacity-10 text-<?= $iconData['bg'] ?>">
                                    <i class="bi <?= $iconData['icon'] ?>"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <?= esc($notif['titulo']) ?>
                                                <?php if (!$isRead): ?>
                                                    <span class="badge bg-primary notification-badge">Nuevo</span>
                                                <?php endif; ?>
                                            </h6>
                                            <?php if (!empty($notif['sub'])): ?>
                                                <small class="text-muted d-block mb-1"><?= esc($notif['sub']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-<?= $iconData['bg'] ?> notification-badge text-capitalize">
                                            <?= esc($nivel) ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($notif['mensaje'])): ?>
                                        <p class="mb-2"><?= esc($notif['mensaje']) ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> <?= $notif['time_ago'] ?>
                                        </small>

                                        <div class="btn-group btn-group-sm">
                                            <?php if (!$isRead): ?>
                                                <form action="<?= base_url('modulo3/notificaciones2/mark-read/' . $notif['id']) ?>"
                                                    method="post" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-outline-success" title="Marcar como leída">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="<?= base_url('modulo3/notificaciones2/delete/' . $notif['id']) ?>"
                                                method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn btn-outline-danger eliminar-notif"
                                                    data-id="<?= $notif['id'] ?>"
                                                    data-url="<?= base_url('modulo3/notificaciones2/delete/' . $notif['id']) ?>"
                                                    title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Auto-dismiss success alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Eliminar notificación con SweetAlert2
    document.querySelectorAll('.eliminar-notif').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.dataset.url;
            const id = this.dataset.id;

            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas eliminar esta notificación?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear y enviar formulario
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Agregar CSRF token
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