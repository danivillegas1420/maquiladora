<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    .profile-header {
        background: transparent;
        padding: 1.5rem 0;
        margin-bottom: 1.5rem;
    }
    
    .profile-header h1 {
        color: #333;
        font-weight: 600;
        margin: 0;
        font-size: 1.75rem;
    }
    
    .profile-header .badge {
        background: #667eea;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .profile-photo-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }
    
    .profile-img-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 50%;
        padding: 3px;
        background: #667eea;
    }
    
    .profile-img {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid white;
    }
    
    .info-card {
        background: white;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    
    .info-card-header {
        background: #f8f9fa;
        color: #333;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #667eea;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .info-section {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-section:last-child {
        border-bottom: none;
    }
    
    .info-section h6 {
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-row {
        display: flex;
        align-items: center;
        padding: 0.5rem 0;
    }
    
    .info-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
        background: #f8f9fa;
        color: #667eea;
    }
    
    .info-content {
        flex: 1;
    }
    
    .info-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 0.15rem;
    }
    
    .info-value {
        font-size: 0.95rem;
        color: #333;
        font-weight: 400;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-badge.active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<?php 
// Depuración temporal - verificar sesión
$userId = session()->get('user_id');
$userName = session()->get('user_name');
$logged_in = session()->get('logged_in');
?>
<!-- DEBUG: Sesión activa: <?php var_dump($userId); ?> | <?php var_dump($userName); ?> | <?php var_dump($logged_in); ?> -->
<?php $hasEmpleado = !empty($empleado); ?>

<div class="profile-header d-flex align-items-center">
    <div class="flex-grow-1">
        <h1><i class="bi bi-person-circle me-2"></i>Perfil del Empleado</h1>
    </div>
    <span class="badge me-3">Módulo 1</span>
    <div>
        <?php if ($hasEmpleado): ?>
            <button type="button" class="btn btn-primary" id="btnEditarEmp">
                <i class="bi bi-pencil-square me-2"></i>Editar
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-success" id="btnAgregarEmp">
                <i class="bi bi-plus-circle me-2"></i>Agregar
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row justify-content-center g-4">
    <!-- Foto -->
    <div class="col-md-4 col-lg-3">
        <div class="card profile-photo-card text-center">
            <div class="card-body">
                <?php 
                $defaultAvatar = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22200%22%20height%3D%22200%22%20fill%3D%22%23e9ecef%22%2F%3E%3Ctext%20x%3D%22100%22%20y%3D%22120%22%20font-family%3D%22Arial%22%20font-size%3D%2280%22%20text-anchor%3D%22middle%22%20fill%3D%22%23667eea%22%3E👤%3C%2Ftext%3E%3C%2Fsvg%3E';
                $avatarSrc = $defaultAvatar;
                if (isset($empleado['foto']) && !empty($empleado['foto'])) {
                    $raw = $empleado['foto'];
                    $isBase64 = is_string($raw)
                        && (strlen($raw) % 4 === 0)
                        && preg_match('/^[A-Za-z0-9+\/\r\n=]+$/', $raw);

                    $binary = $isBase64 ? base64_decode($raw, true) : $raw;
                    if ($binary !== false && $binary !== null && $binary !== '') {
                        $mime = 'image/jpeg';
                        try {
                            $finfo = new \finfo(FILEINFO_MIME_TYPE);
                            $detected = $finfo->buffer($binary);
                            if (is_string($detected) && $detected !== '') {
                                $mime = $detected;
                            }
                        } catch (\Throwable $e) {
                            // keep default
                        }
                        $avatarSrc = 'data:' . $mime . ';base64,' . base64_encode($binary);
                    }
                }
                ?>
                <div class="profile-img-wrapper mb-3">
                    <img src="<?= $avatarSrc ?>" alt="Foto" class="profile-img">
                </div>
                <h5 class="mb-1">
                    <?= esc(trim(($empleado['nombre'] ?? '').' '.($empleado['apellido'] ?? ''))) ?: 'Sin nombre' ?>
                </h5>
                <p class="text-muted mb-2 small">
                    <i class="bi bi-briefcase me-1"></i><?= esc($empleado['puesto'] ?? 'Sin puesto') ?>
                </p>
                <p class="text-muted small">
                    <i class="bi bi-building me-1"></i><?= esc($empleado['nombre_maquiladora'] ?? 'No asignada') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Datos -->
    <div class="col-md-8 col-lg-9">
        <div class="card info-card">
            <div class="card-header info-card-header">
                <i class="bi bi-info-circle me-2"></i>Información Completa
            </div>
            <div class="card-body p-0">
                <!-- Datos Personales -->
                <div class="info-section">
                    <h6><i class="bi bi-person-fill me-2"></i>Datos Personales</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Usuario</div>
                                    <div class="info-value"><?= esc($empleado['username'] ?? 'No asignado') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Fecha de Nacimiento</div>
                                    <div class="info-value"><?= esc($empleado['fecha_nac'] ?? 'No registrada') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-card-text"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">CURP</div>
                                    <div class="info-value"><?= esc($empleado['curp'] ?? 'No registrado') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Edad</div>
                                    <div class="info-value"><?= esc($empleado['edad'] ?? 'No calculada') ?> años</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div class="info-section">
                    <h6><i class="bi bi-telephone-fill me-2"></i>Información de Contacto</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Domicilio</div>
                                    <div class="info-value"><?= esc($empleado['domicilio'] ?? 'No registrado') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-phone"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Teléfono</div>
                                    <div class="info-value"><?= esc($empleado['telefono'] ?? 'No registrado') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?= esc($empleado['email'] ?? ($empleado['correo'] ?? 'No registrado')) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Laboral -->
                <div class="info-section">
                    <h6><i class="bi bi-briefcase-fill me-2"></i>Información Laboral</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Empresa</div>
                                    <div class="info-value"><?= esc($empleado['nombre_maquiladora'] ?? 'No asignada') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Puesto</div>
                                    <div class="info-value"><?= esc($empleado['puesto'] ?? 'No asignado') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-hash"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">No. Empleado</div>
                                    <div class="info-value"><?= esc($empleado['noEmpleado'] ?? 'No asignado') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-toggle-on"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Estatus de Usuario</div>
                                    <div class="info-value">
                                        <?php 
                                        $isActive = isset($empleado['usuario_activo']) && ((int)$empleado['usuario_activo'] === 1);
                                        ?>
                                        <span class="status-badge <?= $isActive ? 'active' : 'inactive' ?>">
                                            <i class="bi bi-<?= $isActive ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                            <?= $isActive ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-icon">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Forma de Pago</div>
                                    <div class="info-value"><?= esc($empleado['Forma_pago'] ?? 'No registrada') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar/agregar empleado -->
<style>
    .modal-empleado .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .modal-empleado .modal-header {
        background: #f8f9fa;
        color: #333;
        padding: 1.25rem 1.5rem;
        border-bottom: 2px solid #667eea;
    }
    
    .modal-empleado .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }
    
    .modal-empleado .modal-body {
        padding: 1.5rem;
    }
    
    .photo-upload-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
    }
    
    .photo-preview-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .photo-preview-ring {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        padding: 3px;
        background: #667eea;
        display: inline-block;
    }
    
    #foto-preview {
        width: 144px;
        height: 144px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
    }
    
    .photo-controls {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
    }
    
    .photo-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid white;
    }
    
    .photo-btn:hover {
        transform: scale(1.1);
    }
    
    .photo-btn.upload {
        background: #667eea;
    }
    
    .photo-btn.camera {
        background: #28a745;
    }
    
    .photo-btn.webcam {
        background: #17a2b8;
    }
    
    .form-section {
        background: white;
        border-radius: 8px;
        padding: 1.25rem;
        border: 1px solid #e9ecef;
    }
    
    .modal-empleado .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }
    
    .modal-empleado .form-label i {
        color: #667eea;
        font-size: 0.85rem;
    }
    
    .modal-empleado .form-control {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 0.6rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .modal-empleado .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15);
    }
    
    .modal-empleado .modal-footer {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-top: 1px solid #dee2e6;
    }
    
    .photo-help-text {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 0.75rem;
    }
</style>

<div class="modal fade modal-empleado" id="modalEmpleado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-person-circle me-2"></i>
            <?= $hasEmpleado ? 'Editar datos del empleado' : 'Agregar datos del empleado' ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEmpleado" method="post" action="<?= base_url('modulo1/empleado/guardar') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <!-- Sección de Foto -->
          <div class="photo-upload-section text-center">
            <div class="photo-preview-wrapper">
              <div class="photo-preview-ring">
                <img id="foto-preview" 
                     src="<?= $avatarSrc !== $defaultAvatar ? $avatarSrc : base_url('assets/img/default-avatar.png') ?>" 
                     alt="Foto de perfil">
              </div>
              <div class="photo-controls">
                <label for="emp-foto" class="photo-btn upload" title="Subir archivo">
                  <i class="bi bi-upload"></i>
                  <input type="file" class="d-none" id="emp-foto" name="foto" accept="image/*">
                </label>
                <label for="emp-foto-webcam" class="photo-btn webcam" title="Cámara web" onclick="abrirWebcam(event)">
                  <i class="bi bi-webcam"></i>
                  <input type="file" class="d-none" id="emp-foto-webcam" name="foto" accept="image/*">
                </label>
              </div>
            </div>
            <div class="photo-help-text">
              <i class="bi bi-info-circle me-1"></i>
              Formatos: JPG, PNG, GIF, WEBP • Tamaño máximo: 2MB
            </div>
          </div>

          <!-- Formulario de Datos -->
          <div class="form-section">
            <div class="row g-3">
              <div class="col-md-4" id="grp-noEmpleado">
                <label class="form-label">
                  <i class="bi bi-hash"></i> No. Empleado
                </label>
                <input type="text" class="form-control" id="emp-noEmpleado" name="noEmpleado" placeholder="Automático">
              </div>
              <div class="col-md-4">
                <label class="form-label">
                  <i class="bi bi-person"></i> Nombre <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="emp-nombre" name="nombre" required placeholder="Ingrese el nombre">
              </div>
              <div class="col-md-4">
                <label class="form-label">
                  <i class="bi bi-person"></i> Apellido <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="emp-apellido" name="apellido" required placeholder="Ingrese el apellido">
              </div>
              <div class="col-md-6">
                <label class="form-label">
                  <i class="bi bi-envelope"></i> Email
                </label>
                <input type="email" class="form-control" id="emp-email" name="email" readonly placeholder="email@ejemplo.com">
              </div>
              <div class="col-md-6">
                <label class="form-label">
                  <i class="bi bi-phone"></i> Teléfono
                </label>
                <input type="text" class="form-control" id="emp-telefono" name="telefono" placeholder="Ej: 6441234567">
              </div>
              <div class="col-md-8">
                <label class="form-label">
                  <i class="bi bi-house-door"></i> Domicilio
                </label>
                <input type="text" class="form-control" id="emp-domicilio" name="domicilio" placeholder="Calle, número, colonia">
              </div>
              <div class="col-md-4">
                <label class="form-label">
                  <i class="bi bi-briefcase"></i> Puesto
                </label>
                <input type="text" class="form-control" id="emp-puesto" name="puesto" readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label">
                  <i class="bi bi-calendar-event"></i> Fecha de nacimiento
                </label>
                <input type="date" class="form-control" id="emp-fecha_nac" name="fecha_nac">
              </div>
              <div class="col-md-4">
                <label class="form-label">
                  <i class="bi bi-card-text"></i> CURP
                </label>
                <input type="text" class="form-control" id="emp-curp" name="curp" maxlength="18" placeholder="18 caracteres">
              </div>
              <div class="col-md-4">
                <label class="form-label">
                  <i class="bi bi-credit-card"></i> Forma de Pago
                </label>
                <select class="form-control" id="emp-Forma_pago" name="Forma_pago">
                  <option value="">Seleccionar...</option>
                  <option value="Destajo">Destajo</option>
                  <option value="Por dia">Por día</option>
                  <option value="Por hora">Por hora</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- jQuery first, then SweetAlert2 (Bootstrap ya lo carga el layout) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Esperar a que jQuery y Bootstrap estén cargados
document.addEventListener('DOMContentLoaded', function() {
  // Verificar si jQuery está cargado
  if (typeof jQuery === 'undefined') {
    console.error('jQuery no está cargado');
    return;
  }
  console.log('jQuery versión:', jQuery.fn.jquery);
  
  // Configuración global para evitar conflictos con $ de jQuery
  var $j = jQuery.noConflict();
  
  // Verificar si Bootstrap está cargado
  if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap no está cargado');
  } else {
    console.log('Bootstrap está cargado');
  }
  
  // Mostrar el modal al hacer clic en Editar/Agregar
  $j(document).ready(function() {
    // Verificar si los botones existen
    console.log('Botón Editar encontrado:', $j('#btnEditarEmp').length > 0);
    console.log('Botón Agregar encontrado:', $j('#btnAgregarEmp').length > 0);
    
    $j('#btnEditarEmp, #btnAgregarEmp').on('click', function(e) {
      e.preventDefault();
      console.log('Botón clickeado:', $j(this).attr('id'));
      const mode = $j(this).attr('id') === 'btnEditarEmp' ? 'edit' : 'add';
      console.log('Modo:', mode);
      openModal(mode);
    });
  });
  
  
  // Prefill modal with current empleado (si existe)
  const data = <?php echo json_encode($empleado ?? []); ?>;
  
  // Mostrar vista previa de la imagen al seleccionar archivo
  const fotoInput = document.getElementById('emp-foto');
  const fotoCamInput = document.getElementById('emp-foto-cam');
  const fotoWebcamInput = document.getElementById('emp-foto-webcam');
  
  function setupFotoPreview(input) {
    if (input) {
      input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Validar tamaño (2MB máximo)
          if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'La imagen no debe pesar más de 2MB',
              confirmButtonText: 'Entendido'
            });
            e.target.value = '';
            return;
          }
          
          // Validar tipo de archivo
          const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
          if (!validTypes.includes(file.type)) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Formato de archivo no válido. Use JPG, PNG, GIF o WEBP',
              confirmButtonText: 'Entendido'
            });
            e.target.value = '';
            return;
          }
          
          // Mostrar vista previa
          const reader = new FileReader();
          reader.onload = function(event) {
            document.getElementById('foto-preview').src = event.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }
  }
  
  setupFotoPreview(fotoInput);
  setupFotoPreview(fotoCamInput);
  setupFotoPreview(fotoWebcamInput);
  
  // Función para abrir cámara del móvil
  window.abrirCamaraMovil = function(event) {
    event.preventDefault();
    
    // Detectar si es un dispositivo móvil
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    if (isMobile) {
      // Para móviles, usar la API de MediaDevices si está disponible
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Crear modal para la cámara del móvil
        const mobileCameraModal = document.createElement('div');
        mobileCameraModal.className = 'modal fade';
        mobileCameraModal.id = 'mobileCameraModal';
        mobileCameraModal.innerHTML = `
          <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Cámara del Móvil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center p-0">
                <video id="mobileCameraVideo" style="width: 100%; height: auto; max-height: 70vh; object-fit: cover;"></video>
                <canvas id="mobileCameraCanvas" style="display: none;"></canvas>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="capturarFotoMovil()">
                  <i class="bi bi-camera-fill"></i> Capturar Foto
                </button>
              </div>
            </div>
          </div>
        `;
        
        document.body.appendChild(mobileCameraModal);
        
        const modal = new bootstrap.Modal(mobileCameraModal);
        modal.show();
        
        // Configuración para cámara trasera del móvil
        const video = document.getElementById('mobileCameraVideo');
        const constraints = {
          video: {
            facingMode: 'environment', // Cámara trasera
            width: { ideal: 1920 },
            height: { ideal: 1080 }
          }
        };
        
        navigator.mediaDevices.getUserMedia(constraints)
          .then(function(stream) {
            video.srcObject = stream;
            window.mobileCameraStream = stream;
          })
          .catch(function(err) {
            console.error('Error al acceder a la cámara del móvil:', err);
            // Si falla, intentar con el input file
            fallbackToInputFile();
            modal.hide();
          });
        
        // Limpiar al cerrar el modal
        mobileCameraModal.addEventListener('hidden.bs.modal', function() {
          if (window.mobileCameraStream) {
            window.mobileCameraStream.getTracks().forEach(track => track.stop());
          }
          document.body.removeChild(mobileCameraModal);
        });
        
        // Función para capturar foto del móvil
        window.capturarFotoMovil = function() {
          const video = document.getElementById('mobileCameraVideo');
          const canvas = document.getElementById('mobileCameraCanvas');
          const context = canvas.getContext('2d');
          
          // Configurar canvas con las dimensiones del video
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          
          // Dibujar el frame actual
          context.drawImage(video, 0, 0, canvas.width, canvas.height);
          
          // Convertir a base64 y mostrar en el preview
          const imageData = canvas.toDataURL('image/jpeg', 0.8);
          document.getElementById('foto-preview').src = imageData;
          
          // Crear un archivo para el formulario
          canvas.toBlob(function(blob) {
            const file = new File([blob], 'mobile_photo.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('emp-foto-cam').files = dataTransfer.files;
          }, 'image/jpeg', 0.8);
          
          // Cerrar el modal
          const mobileCameraModal = document.getElementById('mobileCameraModal');
          const modalInstance = bootstrap.Modal.getInstance(mobileCameraModal);
          modalInstance.hide();
        };
      } else {
        // Fallback para móviles que no soportan MediaDevices
        fallbackToInputFile();
      }
    } else {
      // Para desktop, usar el input file normal
      fallbackToInputFile();
    }
    
    function fallbackToInputFile() {
      // Simular clic en el input file
      document.getElementById('emp-foto-cam').click();
    }
  };
  
  // Función para abrir cámara web con MediaDevices API
  window.abrirWebcam = function(event) {
    event.preventDefault();
    
    // Crear modal para la cámara web
    const webcamModal = document.createElement('div');
    webcamModal.className = 'modal fade';
    webcamModal.id = 'webcamModal';
    webcamModal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cámara Web</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <video id="webcamVideo" width="400" height="300" autoplay style="border-radius: 8px;"></video>
            <canvas id="webcamCanvas" width="400" height="300" style="display: none;"></canvas>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="capturarFoto()">Capturar Foto</button>
          </div>
        </div>
      </div>
    `;
    
    document.body.appendChild(webcamModal);
    
    const modal = new bootstrap.Modal(webcamModal);
    modal.show();
    
    // Iniciar la cámara
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('webcamCanvas');
    const context = canvas.getContext('2d');
    
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(function(stream) {
        video.srcObject = stream;
        window.webcamStream = stream;
      })
      .catch(function(err) {
        console.error('Error al acceder a la cámara:', err);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo acceder a la cámara web. Verifique los permisos.',
          confirmButtonText: 'Entendido'
        });
        modal.hide();
      });
    
    // Limpiar al cerrar el modal
    webcamModal.addEventListener('hidden.bs.modal', function() {
      if (window.webcamStream) {
        window.webcamStream.getTracks().forEach(track => track.stop());
      }
      document.body.removeChild(webcamModal);
    });
  };
  
  // Función para capturar la foto
  window.capturarFoto = function() {
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('webcamCanvas');
    const context = canvas.getContext('2d');
    
    // Dibujar el frame actual del video en el canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convertir a base64 y mostrar en el preview
    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('foto-preview').src = imageData;
    
    // Crear un archivo para el formulario
    canvas.toBlob(function(blob) {
      const file = new File([blob], 'webcam_photo.jpg', { type: 'image/jpeg' });
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      document.getElementById('emp-foto-webcam').files = dataTransfer.files;
    }, 'image/jpeg', 0.8);
    
    // Cerrar el modal
    const webcamModal = document.getElementById('webcamModal');
    const modal = bootstrap.Modal.getInstance(webcamModal);
    modal.hide();
  };
  
  const sessEmail  = '<?= esc(session()->get('user_email') ?? session()->get('correo') ?? '') ?>';
  const sessPuesto = '<?= esc((session()->get('primary_role') ?? ((($tmp=session()->get('role_names')) && is_array($tmp) && isset($tmp[0])) ? $tmp[0] : null)) ?? session()->get('user_role') ?? session()->get('status') ?? '') ?>';
  const sessUid    = '<?= esc((string)(session()->get('user_id') ?? '')) ?>';
  let mode = 'edit'; // 'edit' | 'add'
  function fillForm(){
    if (!data || Object.keys(data).length === 0) return;
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
    // Mostrar la foto actual si existe
    if (data.foto) {
      const imgPreview = document.getElementById('foto-preview');
      if (imgPreview) {
        imgPreview.src = 'data:image/jpeg;base64,' + data.foto;
      }
    }
    set('emp-noEmpleado', data.noEmpleado||'');
    set('emp-nombre', data.nombre||'');
    set('emp-apellido', data.apellido||'');
    set('emp-email', data.email || data.correo || sessEmail || '');
    set('emp-telefono', data.telefono||'');
    set('emp-domicilio', data.domicilio||'');
    set('emp-puesto', data.puesto || sessPuesto || '');
    if (data.fecha_nac){
      try { const d = new Date(data.fecha_nac); set('emp-fecha_nac', isNaN(d) ? String(data.fecha_nac).slice(0,10) : d.toISOString().slice(0,10)); } catch(_){ set('emp-fecha_nac', String(data.fecha_nac).slice(0,10)); }
    }
    set('emp-curp', data.curp||'');
    set('emp-Forma_pago', data.Forma_pago||'');
  }

  // Función para abrir el modal
  function openModal(mode = 'edit') {
    console.log('openModal llamado con modo:', mode);
    // Mostrar el modal
    const modal = $j('#modalEmpleado');
    console.log('Modal encontrado:', modal.length > 0);
    
    // Reiniciar el formulario y la vista previa de la imagen
    const form = document.getElementById('formEmpleado');
    if (form) form.reset();
    
    // Restablecer la imagen de vista previa
    const imgPreview = document.getElementById('foto-preview');
    if (imgPreview) {
      imgPreview.src = '<?= base_url('assets/img/default-avatar.png') ?>';
    }
    
    // Si es editar, precargar los datos
    if (mode === 'edit') {
      // No requerir la foto en edición (puede ser opcional)
      const fotoInput = document.getElementById('emp-foto');
      if (fotoInput) fotoInput.required = false;
      
      fillForm();
      
      // Mostrar No. Empleado (solo para editar) como solo lectura
      const grp = document.getElementById('grp-noEmpleado');
      if (grp) grp.style.display = '';
      
      const noEmp = document.getElementById('emp-noEmpleado');
      if (noEmp) noEmp.readOnly = true;
    } else {
      // Limpiar el formulario para agregar nuevo
      const defaults = {
        'emp-noEmpleado': sessUid ? ('EMP0' + sessUid) : '',
        'emp-nombre': '', 
        'emp-apellido': '',
        'emp-email': (sessEmail || ''), 
        'emp-telefono': '', 
        'emp-domicilio': '',
        'emp-puesto': (sessPuesto || ''), 
        'emp-fecha_nac': '', 
        'emp-curp': ''
      };
      
      Object.entries(defaults).forEach(([id, val]) => { 
        const elI = document.getElementById(id); 
        if (elI) elI.value = val; 
      });
      
      // Ocultar No. Empleado (se generará automático en backend) y asignar valor sugerido
      const grp = document.getElementById('grp-noEmpleado');
      if (grp) grp.style.display = 'none';
      
      const noEmp = document.getElementById('emp-noEmpleado');
      if (noEmp) noEmp.readOnly = false;
    }
    
    // Mostrar el modal al final de la función
    console.log('Intentando mostrar modal...');
    try {
      const modalInstance = new bootstrap.Modal(modal[0]);
      modalInstance.show();
      console.log('Modal mostrado exitosamente');
    } catch (error) {
      console.error('Error al mostrar modal:', error);
    }
  }
  
  // Los manejadores de eventos ya están configurados arriba con jQuery

  // Submit via fetch (JSON)
  const form = document.getElementById('formEmpleado');
  if (form){
    const btnSubmit = form.querySelector('button[type="submit"]');
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const url = form.getAttribute('action');
      const fd  = new FormData(form);

      const confirm = await Swal.fire({
        title: '¿Guardar datos del empleado?',
        text: 'Se actualizará tu información de perfil.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d'
      });
      if (!confirm.isConfirmed) return;

      if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Guardando...'; }
      try{
        const res = await fetch(url, { method:'POST', body: fd });
        const json = await res.json();
        if (json && json.success){
          await Swal.fire({ title: 'Guardado', text: (json.updated?'Datos actualizados':'Empleado agregado'), icon: 'success' });
          location.reload();
        } else {
          await Swal.fire({ title: 'No se pudo guardar', text: (json && (json.message||json.error)) || 'Error desconocido', icon: 'error' });
          if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar'; }
        }
      }catch(err){
        await Swal.fire({ title: 'Error', text: 'Error al guardar', icon: 'error' });
        if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar'; }
      }
    });
  }
});
</script>
<?= $this->endSection() ?>
