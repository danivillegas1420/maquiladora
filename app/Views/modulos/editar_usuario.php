<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('modulo11/usuarios') ?>" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h1>Editar Usuario</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Información del Usuario</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('modulo11/editar/' . $id) ?>">
                <!-- Información de Usuario -->
                <h5 class="mb-3 text-primary">Información de Usuario</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label">Nombre de Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" 
                               value="<?= $usuario['usuario'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idMaquiladora" class="form-label">ID Maquiladora</label>
                        <input type="number" class="form-control" id="idMaquiladora" name="idMaquiladora" 
                               value="<?= $usuario['idmaquiladora'] ?? '' ?>" 
                               placeholder="Ingrese el ID de la maquiladora">
                    </div>
                </div>

                <!-- Información de Empleado -->
                <h5 class="mb-3 text-primary mt-4">Información de Empleado</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="noEmpleado" class="form-label">Número de Empleado *</label>
                        <input type="text" class="form-control" id="noEmpleado" name="noEmpleado" 
                               value="<?= $usuario['noEmpleado'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= $usuario['email'] ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?= $usuario['nombre'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido" class="form-label">Apellido *</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" 
                               value="<?= $usuario['apellido'] ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono *</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                               value="<?= $usuario['telefono'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="puesto" class="form-label">Puesto *</label>
                        <select class="form-select" id="puesto" name="puesto" required>
                            <option value="">Seleccionar puesto...</option>
                            <option value="Administrador" <?= $usuario['puesto'] == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                            <option value="Supervisor" <?= $usuario['puesto'] == 'Supervisor' ? 'selected' : '' ?>>Supervisor</option>
                            <option value="Operador" <?= $usuario['puesto'] == 'Operador' ? 'selected' : '' ?>>Operador</option>
                            <option value="Diseñador" <?= $usuario['puesto'] == 'Diseñador' ? 'selected' : '' ?>>Diseñador</option>
                            <option value="Jefe de Producción" <?= $usuario['puesto'] == 'Jefe de Producción' ? 'selected' : '' ?>>Jefe de Producción</option>
                            <option value="Coordinador" <?= $usuario['puesto'] == 'Coordinador' ? 'selected' : '' ?>>Coordinador</option>
                            <option value="Analista" <?= $usuario['puesto'] == 'Analista' ? 'selected' : '' ?>>Analista</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="domicilio" class="form-label">Domicilio *</label>
                        <textarea class="form-control" id="domicilio" name="domicilio" rows="3" required><?= $usuario['domicilio'] ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="activo_usuario" class="form-label">Estatus Usuario *</label>
                        <select class="form-select" id="activo_usuario" name="activo_usuario" required>
                            <option value="1" <?= $usuario['activo'] == 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= $usuario['activo'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="activo_empleado" class="form-label">Estatus Empleado *</label>
                        <select class="form-select" id="activo_empleado" name="activo_empleado" required>
                            <option value="1" <?= $usuario['empleado_activo'] == 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= $usuario['empleado_activo'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                            <option value="2" <?= $usuario['empleado_activo'] == 2 ? 'selected' : '' ?>>Baja de la empresa</option>
                            <option value="3" <?= $usuario['empleado_activo'] == 3 ? 'selected' : '' ?>>En espera</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de Registro</label>
                        <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($usuario['fechaAlta'])) ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Último Acceso</label>
                        <input type="text" class="form-control" value="<?= date('d/m/Y H:i', strtotime($usuario['ultimoAcceso'])) ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Dejar vacío para mantener la actual">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Dejar vacío para mantener la actual">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> Si desea cambiar la contraseña, complete ambos campos. 
                            Si no desea cambiarla, deje ambos campos vacíos.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('modulo11/usuarios') ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Validación de contraseñas (solo si se proporcionan)
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== '' && confirmPassword !== '' && password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Solo validar si se proporcionaron contraseñas
            if (password !== '' || confirmPassword !== '') {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({icon:'warning', title:'Atención', text:'Las contraseñas no coinciden'});
                    return false;
                }
            }
        });
    </script>
<?= $this->endSection() ?>
