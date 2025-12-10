<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0">Roles</h1>
        <button type="button" class="btn btn-success" id="btnAbrirAgregar">
            <i class="bi bi-plus-lg"></i> Agregar Rol
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Lista de Roles</strong>
        </div>
        <div class="card-body">
            <table id="tablaRoles" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($roles)): foreach ($roles as $r): ?>
                    <?php $esFijo = (int)($r['es_fijo'] ?? 0) === 1; ?>
                    <tr data-es-fijo="<?= $esFijo ? 1 : 0 ?>">
                        <td><?= esc($r['id']) ?></td>
                        <td class="rol-nombre"><?= esc($r['nombre'] ?? '-') ?></td>
                        <td class="rol-descripcion"><?= esc($r['descripcion'] ?? '-') ?></td>
                        <td>
                            <?php if (!$esFijo): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-rol-editar"
                                        data-id="<?= (int)$r['id'] ?>"
                                        data-nombre="<?= esc($r['nombre'] ?? '', 'attr') ?>"
                                        data-descripcion="<?= esc($r['descripcion'] ?? '', 'attr') ?>"
                                        title="Editar rol">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-rol-eliminar ms-1"
                                        data-id="<?= (int)$r['id'] ?>"
                                        data-nombre="<?= esc($r['nombre'] ?? '', 'attr') ?>"
                                        title="Eliminar rol">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php else: ?>
                                <span class="badge bg-secondary">Rol fijo</span>
                            <?php endif; ?>
                            <button type="button"
                                    class="btn btn-sm btn-outline-info btn-rol-permisos ms-1"
                                    data-id="<?= (int)$r['id'] ?>"
                                    data-nombre="<?= esc($r['nombre'] ?? '', 'attr') ?>"
                                    title="Ver permisos del rol">
                                <i class="bi bi-key"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Agregar Rol -->
    <div class="modal fade" id="modalAgregarRol" tabindex="-1" aria-labelledby="modalAgregarRolLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalAgregarRolLabel">Agregar Rol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAgregarRol">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ar-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="ar-nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="ar-descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="ar-descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnAgregarGuardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Rol -->
    <div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarRolLabel">Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarRol">
                    <div class="modal-body">
                        <input type="hidden" id="er-id" name="id">
                        <div class="mb-3">
                            <label for="er-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="er-nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="er-descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="er-descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnEditarGuardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Permisos del Rol -->
    <div class="modal fade" id="modalPermisosRol" tabindex="-1" aria-labelledby="modalPermisosRolLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center w-100 justify-content-between">
                        <h5 class="modal-title mb-2 mb-md-0" id="modalPermisosRolLabel">
                            Permisos del Rol: <span id="permisos-rol-nombre"></span>
                        </h5>
                        <div class="form-check ms-md-3">
                            <input class="form-check-input" type="checkbox" id="perm_todos">
                            <label class="form-check-label" for="perm_todos">
                                Seleccionar todos
                            </label>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Gestión</h6>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.catalogo_disenos" id="perm_catalogo_disenos">
                                <label class="form-check-label" for="perm_catalogo_disenos">
                                    Catálogo de Diseños
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.pedidos" id="perm_pedidos">
                                <label class="form-check-label" for="perm_pedidos">
                                    Pedidos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.pagos" id="perm_pagos">
                                <label class="form-check-label" for="perm_pagos">
                                    Pagos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.ordenes" id="perm_ordenes">
                                <label class="form-check-label" for="perm_ordenes">
                                    Órdenes en proceso
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.produccion" id="perm_produccion">
                                <label class="form-check-label" for="perm_produccion">
                                    Producción
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.ordenes_clientes" id="perm_ordenes_clientes">
                                <label class="form-check-label" for="perm_ordenes_clientes">
                                    Clientes
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">Muestras e Inspección</h6>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.muestras" id="perm_muestras">
                                <label class="form-check-label" for="perm_muestras">
                                    Muestras
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.inspeccion" id="perm_inspeccion">
                                <label class="form-check-label" for="perm_inspeccion">
                                    Inspección
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.incidencias" id="perm_incidencias">
                                <label class="form-check-label" for="perm_incidencias">
                                    Incidencias
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.wip" id="perm_wip">
                                <label class="form-check-label" for="perm_wip">
                                    WIP
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="text-warning mb-3">Planificación</h6>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.planificacion_materiales" id="perm_planificacion_materiales">
                                <label class="form-check-label" for="perm_planificacion_materiales">
                                    Planificación Materiales
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.desperdicios" id="perm_desperdicios">
                                <label class="form-check-label" for="perm_desperdicios">
                                    Desperdicios
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.proveedores" id="perm_proveedores">
                                <label class="form-check-label" for="perm_proveedores">
                                    Proveedores
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger mb-3">Mantenimiento</h6>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.inv_maquinas" id="perm_inv_maquinas">
                                <label class="form-check-label" for="perm_inv_maquinas">
                                    Inventario Maq.
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.mant_correctivo" id="perm_mant_correctivo">
                                <label class="form-check-label" for="perm_mant_correctivo">
                                    Mant. Correctivo
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.mant_preventivo" id="perm_mant_preventivo">
                                <label class="form-check-label" for="perm_mant_preventivo">
                                    Mant. Preventivo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="text-info mb-3">Logística</h6>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.logistica_preparacion" id="perm_logistica_preparacion">
                                <label class="form-check-label" for="perm_logistica_preparacion">
                                    Prep. Envíos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.logistica_gestion" id="perm_logistica_gestion">
                                <label class="form-check-label" for="perm_logistica_gestion">
                                    Gestión Envíos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.logistica_documentos" id="perm_logistica_documentos">
                                <label class="form-check-label" for="perm_logistica_documentos">
                                    Docs. Embarque
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.inventario_almacen" id="perm_inventario_almacen">
                                <label class="form-check-label" for="perm_inventario_almacen">
                                    Inventario Almacén
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-dark mb-3">Administración</h6>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.notificaciones" id="perm_notificaciones">
                                <label class="form-check-label" for="perm_notificaciones">
                                    Notificaciones
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.reportes" id="perm_reportes">
                                <label class="form-check-label" for="perm_reportes">
                                    Reportes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.roles" id="perm_roles">
                                <label class="form-check-label" for="perm_roles">
                                    Roles
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.usuarios" id="perm_usuarios">
                                <label class="form-check-label" for="perm_usuarios">
                                    Gestión Usuarios
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" type="checkbox" value="menu.maquiladora" id="perm_maquiladora">
                                <label class="form-check-label" for="perm_maquiladora">
                                    Mi Maquiladora
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Notificaciones por Rol -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-purple mb-3">
                                <i class="bi bi-bell"></i> Notificaciones por Rol
                            </h6>
                            <div class="alert alert-info">
                                <small class="mb-0">
                                    <strong>Selecciona qué tipos de notificaciones recibirán los usuarios con este rol.</strong><br>
                                    Las notificaciones no seleccionadas no se mostrarán a los usuarios de este rol.
                                </small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="clientes" id="notif_clientes">
                                        <label class="form-check-label" for="notif_clientes">
                                            <i class="bi bi-people"></i> Clientes (altas, bajas, cambios)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="disenos" id="notif_disenos">
                                        <label class="form-check-label" for="notif_disenos">
                                            <i class="bi bi-palette"></i> Diseños (creación, actualización)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="pedidos" id="notif_pedidos">
                                        <label class="form-check-label" for="notif_pedidos">
                                            <i class="bi bi-cart"></i> Pedidos (nuevos, cambios de estado)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="ordenes_produccion" id="notif_ordenes_produccion">
                                        <label class="form-check-label" for="notif_ordenes_produccion">
                                            <i class="bi bi-gear"></i> Órdenes de Producción
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="mantenimiento" id="notif_mantenimiento">
                                        <label class="form-check-label" for="notif_mantenimiento">
                                            <i class="bi bi-tools"></i> Mantenimiento (programado, correctivo)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="muestras" id="notif_muestras">
                                        <label class="form-check-label" for="notif_muestras">
                                            <i class="bi bi-eye"></i> Muestras (aprobadas, rechazadas)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="inspeccion" id="notif_inspeccion">
                                        <label class="form-check-label" for="notif_inspeccion">
                                            <i class="bi bi-clipboard-check"></i> Inspección (resultados)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="incidencias" id="notif_incidencias">
                                        <label class="form-check-label" for="notif_incidencias">
                                            <i class="bi bi-exclamation-triangle"></i> Incidencias (reportes)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="mrp_materiales" id="notif_mrp_materiales">
                                        <label class="form-check-label" for="notif_mrp_materiales">
                                            <i class="bi bi-box"></i> MRP y Materiales (stock bajo, OC)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input notif-checkbox" type="checkbox" value="sistema" id="notif_sistema">
                                        <label class="form-check-label" for="notif_sistema">
                                            <i class="bi bi-gear-fill"></i> Sistema (usuarios, roles)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notif_todas">
                                        <label class="form-check-label" for="notif_todas">
                                            <strong>Seleccionar todas las notificaciones</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-info" id="btnGuardarPermisos">Guardar Permisos</button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function(){
            const dt = $('#tablaRoles').DataTable({
                language: {
                    sProcessing: 'Procesando...',
                    sLengthMenu: 'Mostrar _MENU_ registros',
                    sZeroRecords: 'No se encontraron resultados',
                    sEmptyTable: 'Ningún dato disponible en esta tabla',
                    sInfo: 'Mostrando _START_ a _END_ de _TOTAL_',
                    sInfoEmpty: 'Mostrando 0 a 0 de 0',
                    sInfoFiltered: '(filtrado de _MAX_)',
                    sSearch: 'Buscar:',
                    sLoadingRecords: 'Cargando...',
                    oPaginate: { sFirst: 'Primero', sLast: 'Último', sNext: 'Siguiente', sPrevious: 'Anterior' }
                }
            });

            // Abrir modal Agregar
            $('#btnAbrirAgregar').on('click', function(){
                $('#ar-nombre').val('');
                $('#ar-descripcion').val('');
                const m = new bootstrap.Modal(document.getElementById('modalAgregarRol'));
                m.show();
            });

            // Guardar nuevo rol
            $('#formAgregarRol').on('submit', function(e){
                e.preventDefault();
                const payload = {
                    nombre: $('#ar-nombre').val().trim(),
                    descripcion: $('#ar-descripcion').val().trim()
                };
                if (!payload.nombre) { 
                    Swal.fire({
                        title: 'Error',
                        text: 'El nombre es obligatorio',
                        icon: 'warning',
                        confirmButtonColor: '#3085d6'
                    }); 
                    return; 
                }
                // Evitar envíos múltiples
                const $btn = $('#btnAgregarGuardar');
                if ($btn.prop('disabled')) return; // ya en curso
                $btn.prop('disabled', true).text('Guardando...');
                $.ajax({
                    url: '<?= base_url('modulo11/roles/agregar') ?>',
                    method: 'POST',
                    data: payload,
                    dataType: 'json'
                })
                    .done(function(r){
                        console.log('Response agregar:', r);
                        if (r && r.success && r.id) {
                            // Añadir fila a la DataTable
                            const accionHtml = '<button type="button" class="btn btn-sm btn-outline-primary btn-rol-editar"'
                                + ' data-id="'+ r.id +'"'
                                + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                + ' data-descripcion="'+ $('<div>').text(payload.descripcion).html() +'"'
                                + ' title="Editar rol">'
                                + ' <i class="bi bi-pencil"></i>'
                                + '</button>'
                                + '<button type="button" class="btn btn-sm btn-outline-danger btn-rol-eliminar ms-1"'
                                + ' data-id="'+ r.id +'"'
                                + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                + ' title="Eliminar rol">'
                                + ' <i class="bi bi-trash"></i>'
                                + '</button>'
                                + '<button type="button" class="btn btn-sm btn-outline-info btn-rol-permisos ms-1"'
                                + ' data-id="'+ r.id +'"'
                                + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                + ' title="Ver permisos del rol">'
                                + ' <i class="bi bi-key"></i>'
                                + '</button>';
                            dt.row.add([r.id, payload.nombre, payload.descripcion, accionHtml]).draw(false);
                            Swal.fire({
                                title: '¡Agregado!',
                                text: 'Rol agregado correctamente',
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalAgregarRol'))?.hide();
                            // Limpiar formulario
                            $('#formAgregarRol')[0].reset();
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: r && r.message ? r.message : 'No se pudo agregar el rol',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    })
                    .fail(function(xhr){
                        let msg = 'Error al agregar el rol';
                        try { const j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e) {}
                        Swal.fire({
                            title: 'Error',
                            text: msg,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    })
                    .always(function(){
                        $btn.prop('disabled', false).text('Guardar');
                    });
            });

            // Eliminar rol
            $(document).on('click', '.btn-rol-eliminar', function(){
                const id = $(this).data('id');
                const nombre = $(this).data('nombre') || '';
                
                Swal.fire({
                    title: "¿Estás seguro?",
                    text: `Eliminarás el rol "${nombre}". ¡No podrás revertir esto!`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, eliminar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= base_url('modulo11/roles/eliminar') ?>',
                            method: 'POST',
                            data: { id: id },
                            dataType: 'json'
                        })
                        .done(function(response){
                        console.log('Response actualizar:', response);
                        if (response && response.success) {
                                Swal.fire({
                                    title: "¡Eliminado!",
                                    text: "El rol ha sido eliminado correctamente.",
                                    icon: "success",
                                    confirmButtonColor: "#3085d6"
                                });
                                // Eliminar fila de la DataTable
                                const $row = $('#tablaRoles tbody tr').filter(function(){ 
                                    return $(this).find('td:first').text().trim() == String(id); 
                                });
                                if ($row.length) {
                                    const dt = $('#tablaRoles').DataTable();
                                    dt.row($row).remove().draw(false);
                                }
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response && response.message ? response.message : 'No se pudo eliminar el rol',
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        })
                        .fail(function(xhr){
                            let msg = 'Error al eliminar el rol';
                            try { 
                                const j = JSON.parse(xhr.responseText); 
                                if (j.message) msg = j.message; 
                            } catch(e) {}
                            Swal.fire({
                                title: 'Error',
                                text: msg,
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                    }
                });
            });

            // Abrir modal con datos
            $(document).on('click', '.btn-rol-editar', function(){
                const id  = $(this).data('id');
                const nom = $(this).data('nombre') || '';
                const des = $(this).data('descripcion') || '';
                $('#er-id').val(id);
                $('#er-nombre').val(nom);
                $('#er-descripcion').val(des);
                const m = new bootstrap.Modal(document.getElementById('modalEditarRol'));
                m.show();
            });

            // Abrir modal de permisos
            $(document).on('click', '.btn-rol-permisos', function(){
                const id = $(this).data('id');
                const nombre = $(this).data('nombre') || '';
                
                // Mostrar nombre del rol en el modal
                $('#permisos-rol-nombre').text(nombre);
                
                // Limpiar checkboxes
                $('.permiso-checkbox').prop('checked', false);
                $('.notif-checkbox').prop('checked', false);
                
                // Si es el rol Cliente, mostrar solo Pedidos Clientes
                if (nombre.toLowerCase() === 'cliente') {
                    // Ocultar todos los permisos
                    $('.permiso-checkbox').closest('.form-check').hide();
                    // Mostrar solo Pedidos Clientes
                    $('.permiso-checkbox[value="menu.pedidos_clientes"]').closest('.form-check').show();
                    // Ocultar "Seleccionar todos"
                    $('#perm_todos').closest('.form-check').hide();
                } else {
                    // Mostrar todos los permisos
                    $('.permiso-checkbox').closest('.form-check').show();
                    // Mostrar "Seleccionar todos"
                    $('#perm_todos').closest('.form-check').show();
                }
                
                // Cargar permisos del rol desde el servidor
                $.ajax({
                    url: '<?= base_url('modulo11/roles/permisos') ?>',
                    method: 'POST',
                    data: { rol_id: id },
                    dataType: 'json'
                })
                .done(function(response){
                    console.log('Response editar:', response);
                    if (response && response.success) {
                        // Marcar los permisos que tiene el rol
                        response.permisos.forEach(function(permiso) {
                            $('.permiso-checkbox[value="' + permiso + '"]').prop('checked', true);
                        });
                        // Marcar las notificaciones que tiene el rol
                        if (response.notificaciones) {
                            response.notificaciones.forEach(function(notificacion) {
                                $('.notif-checkbox[value="' + notificacion + '"]').prop('checked', true);
                            });
                        }
                    }
                })
                .fail(function(xhr){
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al cargar permisos del rol',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                });
                
                // Guardar el ID del rol para uso posterior
                $('#modalPermisosRol').data('rol-id', id);
                
                // Abrir el modal
                const m = new bootstrap.Modal(document.getElementById('modalPermisosRol'));
                m.show();
            });

            // Seleccionar / deseleccionar todos los permisos
            $('#perm_todos').on('change', function(){
                const checked = $(this).is(':checked');
                $('.permiso-checkbox').prop('checked', checked);
            });

            // Mantener sincronizado el checkbox "Seleccionar todos" cuando se cambian individuales
            $(document).on('change', '.permiso-checkbox', function(){
                const total = $('.permiso-checkbox').length;
                const marcados = $('.permiso-checkbox:checked').length;
                $('#perm_todos').prop('checked', total > 0 && marcados === total);
            });

            // Seleccionar / deseleccionar todas las notificaciones
            $('#notif_todas').on('change', function(){
                const checked = $(this).is(':checked');
                $('.notif-checkbox').prop('checked', checked);
            });

            // Mantener sincronizado el checkbox "Seleccionar todas notificaciones" cuando se cambian individuales
            $(document).on('change', '.notif-checkbox', function(){
                const total = $('.notif-checkbox').length;
                const marcados = $('.notif-checkbox:checked').length;
                $('#notif_todas').prop('checked', total > 0 && marcados === total);
            });

            // Guardar permisos del rol
            $('#btnGuardarPermisos').on('click', function(){
                const rolId = $('#modalPermisosRol').data('rol-id');
                const permisosSeleccionados = [];
                const notificacionesSeleccionadas = [];
                
                // Recopilar permisos seleccionados
                $('.permiso-checkbox:checked').each(function(){
                    permisosSeleccionados.push($(this).val());
                });
                
                // Recopilar notificaciones seleccionadas
                $('.notif-checkbox:checked').each(function(){
                    notificacionesSeleccionadas.push($(this).val());
                });
                
                const $btn = $(this);
                if ($btn.prop('disabled')) return;
                
                $btn.prop('disabled', true).text('Guardando...');
                
                $.ajax({
                    url: '<?= base_url('modulo11/roles/guardar_permisos') ?>',
                    method: 'POST',
                    data: {
                        rol_id: rolId,
                        permisos: permisosSeleccionados,
                        notificaciones: notificacionesSeleccionadas
                    },
                    dataType: 'json'
                })
                .done(function(response){
                    console.log('Response permisos:', response);
                    if (response && response.success) {
                        Swal.fire({
                            title: '¡Guardado!',
                            text: 'Permisos guardados correctamente',
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        });
                        bootstrap.Modal.getInstance(document.getElementById('modalPermisosRol')).hide();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response && response.message ? response.message : 'Error al guardar permisos',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                })
                .fail(function(xhr){
                    let msg = 'Error al guardar los permisos';
                    try { 
                        const j = JSON.parse(xhr.responseText); 
                        if (j.message) msg = j.message; 
                    } catch(e) {}
                    Swal.fire({
                        title: 'Error',
                        text: msg,
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                })
                .always(function(){
                    $btn.prop('disabled', false).text('Guardar Permisos');
                });
            });

            // Guardar cambios (AJAX)
            $('#formEditarRol').on('submit', function(e){
                e.preventDefault();
                const payload = {
                    id: $('#er-id').val(),
                    nombre: $('#er-nombre').val().trim(),
                    descripcion: $('#er-descripcion').val().trim()
                };
                if (!payload.nombre) { 
                    Swal.fire({
                        title: 'Error',
                        text: 'El nombre es obligatorio',
                        icon: 'warning',
                        confirmButtonColor: '#3085d6'
                    }); 
                    return; 
                }
                // Evitar envíos múltiples
                const $btn = $('#btnEditarGuardar');
                if ($btn.prop('disabled')) return; // ya en curso
                $btn.prop('disabled', true).text('Guardando...');
                $.ajax({
                    url: '<?= base_url('modulo11/roles/actualizar') ?>',
                    method: 'POST',
                    data: payload,
                    dataType: 'json'
                })
                    .done(function(r){
                        console.log('Response actualizar:', r);
                        if (r && r.success) {
                            // Refrescar fila en DataTable usando API
                            const id = payload.id;
                            const $row = $('#tablaRoles tbody tr').filter(function(){ return $(this).find('td:first').text().trim() == String(id); });
                            if ($row.length) {
                                const accionHtml = '<button type="button" class="btn btn-sm btn-outline-primary btn-rol-editar"'
                                    + ' data-id="'+ id +'"'
                                    + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                    + ' data-descripcion="'+ $('<div>').text(payload.descripcion).html() +'"'
                                    + ' title="Editar rol">'
                                    + ' <i class="bi bi-pencil"></i>'
                                    + '</button>'
                                    + '<button type="button" class="btn btn-sm btn-outline-danger btn-rol-eliminar ms-1"'
                                    + ' data-id="'+ id +'"'
                                    + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                    + ' title="Eliminar rol">'
                                    + ' <i class="bi bi-trash"></i>'
                                    + '</button>'
                                    + '<button type="button" class="btn btn-sm btn-outline-info btn-rol-permisos ms-1"'
                                    + ' data-id="'+ id +'"'
                                    + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                    + ' title="Ver permisos del rol">'
                                    + ' <i class="bi bi-key"></i>'
                                    + '</button>';
                                dt.row($row).data([id, payload.nombre, payload.descripcion, accionHtml]).draw(false);
                            }
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: 'Rol actualizado correctamente',
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalEditarRol'))?.hide();
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: r && r.message ? r.message : 'No se pudo actualizar el rol',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    })
                    .fail(function(xhr){
                        let msg = 'Error al actualizar el rol';
                        try { const j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e) {}
                        Swal.fire({
                            title: 'Error',
                            text: msg,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    })
                    .always(function(){
                        $btn.prop('disabled', false).text('Guardar');
                    });
            });
        });
    </script>

<?= $this->endSection() ?>