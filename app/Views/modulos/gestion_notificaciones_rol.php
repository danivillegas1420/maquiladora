<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Notificaciones por Rol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Gestión de Notificaciones por Rol
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Configure qué tipos de notificaciones debe recibir cada rol. 
                            Solo los usuarios con roles configurados recibirán las notificaciones correspondientes.
                        </div>

                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $rol): ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user-tag me-2"></i>
                                            <?= esc($rol['nombre']) ?>
                                            <?php if (!empty($rol['descripcion'])): ?>
                                                <small class="text-muted">(<?= esc($rol['descripcion']) ?>)</small>
                                            <?php endif; ?>
                                        </h6>
                                        <button class="btn btn-sm btn-outline-primary" onclick="toggleConfiguracion(<?= $rol['id'] ?>)">
                                            <i class="fas fa-cog"></i> Configurar
                                        </button>
                                    </div>

                                    <div id="config-<?= $rol['id'] ?>" class="configuracion-rol" style="display: none;">
                                        <form class="form-notificaciones-rol" data-rol-id="<?= $rol['id'] ?>">
                                            <div class="row">
                                                <?php foreach ($tiposNotificacion as $key => $label): ?>
                                                    <div class="col-md-6 col-lg-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="tipos_notificacion[]" 
                                                                   value="<?= $key ?>"
                                                                   id="tipo_<?= $rol['id'] ?>_<?= $key ?>"
                                                                   <?= in_array($key, $rol['tipos_notificacion']) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="tipo_<?= $rol['id'] ?>_<?= $key ?>">
                                                                <?= $label ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-save"></i> Guardar Configuración
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleConfiguracion(<?= $rol['id'] ?>)">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <?php if (!empty($rol['tipos_notificacion'])): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-bell-slash me-1"></i>
                                                Tipos configurados: 
                                                <?php 
                                                $tipos_configurados = [];
                                                foreach ($rol['tipos_notificacion'] as $tipo) {
                                                    $tipos_configurados[] = $tiposNotificacion[$tipo] ?? $tipo;
                                                }
                                                echo implode(', ', $tipos_configurados);
                                                ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2">
                                            <small class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Este rol no tiene configurado ningún tipo de notificación
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No hay roles configurados para esta maquiladora.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast para notificaciones -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Sistema de Notificaciones</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                Mensaje
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleConfiguracion(rolId) {
            const configDiv = document.getElementById('config-' + rolId);
            const allConfigs = document.querySelectorAll('.configuracion-rol');
            
            // Ocultar todas las demás configuraciones
            allConfigs.forEach(config => {
                if (config.id !== 'config-' + rolId) {
                    config.style.display = 'none';
                }
            });
            
            // Toggle la configuración actual
            configDiv.style.display = configDiv.style.display === 'none' ? 'block' : 'none';
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastHeader = toast.querySelector('.toast-header');
            
            toastMessage.textContent = message;
            
            // Cambiar colores según el tipo
            toastHeader.className = 'toast-header text-white ' + (type === 'success' ? 'bg-success' : 'bg-danger');
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Manejar envío de formularios
        document.querySelectorAll('.form-notificaciones-rol').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const rolId = this.dataset.rolId;
                const formData = new FormData(this);
                const checkboxes = this.querySelectorAll('input[type="checkbox"]:checked');
                const tiposNotificacion = Array.from(checkboxes).map(cb => cb.value);
                
                // Enviar datos como JSON
                fetch('/modulos/guardar_notificaciones_rol', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        rol_id: parseInt(rolId),
                        tipos_notificacion: tiposNotificacion
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Recargar la página para mostrar los cambios
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'Error al guardar la configuración', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error de conexión al guardar la configuración', 'error');
                });
            });
        });
    </script>
</body>
</html>
