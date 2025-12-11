<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .progress {
        height: 20px;
        background-color: #e9ecef;
        border-radius: 0.375rem;
    }

    .progress-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: bold;
        font-size: 0.75rem;
        transition: width 0.6s ease;
    }

    .status-badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }

    .card-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Control de Bultos</h1>
    <span class="badge bg-primary">Producción</span>
    <div class="ms-auto">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoControl">
            <i class="fas fa-plus me-1"></i> Nuevo Control
        </button>
        <button type="button" class="btn btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#modalPlantillas">
            <i class="fas fa-cog me-1"></i> Plantillas
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header card-header-actions">
        <strong>Listado de Controles de Producción</strong>
    </div>
    <div class="card-body table-responsive">
        <table id="tablaControles" class="table table-striped table-bordered align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Orden Producción</th>
                    <th>Estilo</th>
                    <th>Prenda</th>
                    <th>Cant. Total</th>
                    <th>Progreso General</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nuevo Control -->
<div class="modal fade" id="modalNuevoControl" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Nuevo Control de Bultos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoControl">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Orden de Producción</label>
                            <select class="form-select" name="ordenProduccionId" required>
                                <option value="">Seleccione una orden...</option>
                                <!-- Populate via JS -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Prenda (Plantilla)</label>
                            <select class="form-select" name="tipo_prenda" required>
                                <option value="">Seleccione tipo...</option>
                                <!-- Populate via JS -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estilo</label>
                            <input type="text" class="form-control" name="estilo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cantidad Total</label>
                            <input type="number" class="form-control" name="cantidad_total" required min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarControl">Crear Control</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle/Producción -->
<div class="modal fade" id="modalDetalleControl" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <small class="text-muted">Orden</small>
                        <h5 id="detalleOrden">---</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Estilo</small>
                        <h5 id="detalleEstilo">---</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Estado</small>
                        <div id="detalleEstado">---</div>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-sm btn-outline-primary" id="btnRegistrarProduccion">
                            <i class="fas fa-clipboard-check"></i> Registrar Producción
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Progreso por Operación</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tablaOperaciones">
                            <thead>
                                <tr>
                                    <th>Operación</th>
                                    <th>Tipo</th>
                                    <th>Requeridas</th>
                                    <th>Completadas</th>
                                    <th style="width: 30%;">Progreso</th>
                                    <th style="width: 80px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Operations loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Producción -->
<div class="modal fade" id="modalRegistroProduccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistroProduccion">
                    <input type="hidden" name="controlBultoId" id="regControlId">
                    <div class="mb-3">
                        <label class="form-label">Operación</label>
                        <select class="form-select" name="operacionControlId" id="regOperacion" required>
                            <option value="">Seleccione operación...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Empleado</label>
                        <select class="form-select" name="empleadoId" id="regEmpleado" required>
                            <option value="">Seleccione empleado...</option>
                            <!-- Populate via JS -->
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cantidad Realizada</label>
                            <input type="number" class="form-control" name="cantidad_producida" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha_registro" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Inicio</label>
                            <input type="time" class="form-control" name="hora_inicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Fin</label>
                            <input type="time" class="form-control" name="hora_fin">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarProduccion">Guardar Registro</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Control -->
<div class="modal fade" id="modalEditarControl" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Control</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarControl">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Estilo</label>
                        <input type="text" class="form-control" name="estilo" id="editEstilo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad Total</label>
                        <input type="number" class="form-control" name="cantidad_total" id="editCantidad" required
                            min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="editObservaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarControl">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Plantillas -->
<div class="modal fade" id="modalPlantillas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestión de Plantillas de Operaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="plantillasTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="lista-tab" data-bs-toggle="tab"
                            data-bs-target="#lista-plantillas" type="button" role="tab">Listado</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nueva-tab" data-bs-toggle="tab" data-bs-target="#nueva-plantilla"
                            type="button" role="tab">Nueva Plantilla</button>
                    </li>
                </ul>
                <div class="tab-content" id="plantillasTabContent">
                    <!-- Tab Listado -->
                    <div class="tab-pane fade show active" id="lista-plantillas" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo Prenda</th>
                                        <th>Operaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPlantillas">
                                    <!-- Populate via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Tab Nueva Plantilla -->
                    <div class="tab-pane fade" id="nueva-plantilla" role="tabpanel">
                        <form id="formNuevaPlantilla">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre Plantilla</label>
                                    <input type="text" class="form-control" name="nombre_plantilla" required
                                        placeholder="Ej. Camisa Estándar">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Prenda</label>
                                    <input type="text" class="form-control" name="tipo_prenda" required
                                        placeholder="Ej. CAMISA">
                                </div>

                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Agregar Operaciones</h6>
                                            <p class="text-muted small">Agregue las operaciones en el orden que deben
                                                realizarse.</p>

                                            <div class="row g-2 align-items-end mb-3">
                                                <div class="col-md-5">
                                                    <label class="form-label small">Nombre Operación</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="opNombre" placeholder="Ej. Corte, Armado de Cuello">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Tipo</label>
                                                    <select class="form-select form-select-sm" id="opTipo">
                                                        <option value="1">Componente (Pieza)</option>
                                                        <option value="0">Armado (Ensamble)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Piezas</label>
                                                    <input type="number" class="form-control form-control-sm"
                                                        id="opPiezas" value="1" min="1">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-success w-100"
                                                        id="btnAgregarOp">
                                                        <i class="fas fa-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="table-responsive bg-white border rounded"
                                                style="max-height: 200px; overflow-y: auto;">
                                                <table class="table table-sm table-striped mb-0"
                                                    id="tablaBuilderOperaciones">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 50px;">#</th>
                                                            <th>Operación</th>
                                                            <th>Tipo</th>
                                                            <th>Piezas</th>
                                                            <th style="width: 50px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbodyOpsPlantilla">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden input to store JSON -->
                                <input type="hidden" name="operaciones" id="inputOperacionesJson">

                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary" id="btnGuardarPlantilla">Guardar
                                        Plantilla</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Pasar datos de PHP a JS
    const ordenes = <?= json_encode($ordenes ?? []) ?>;
    const plantillas = <?= json_encode($plantillas ?? []) ?>;
    const empleados = <?= json_encode($empleados ?? []) ?>;
    const empleadoActual = <?= json_encode($empleadoActual ?? null) ?>;

    $(document).ready(function () {
        // Inicializar DataTable
        const tabla = $('#tablaControles').DataTable({
            ajax: {
                url: '<?= base_url('modulo3/api/control-bultos') ?>',
                dataSrc: function (json) {
                    return json.data || [];
                }
            },
            columns: [
                { data: 'id' },
                { data: 'ordenFolio' },
                { data: 'estilo' },
                { data: 'tipo_prenda', defaultContent: 'N/A' },
                { data: 'cantidad_total' },
                {
                    data: null,
                    render: function (data, type, row) {
                        // Calcular progreso real si está disponible, sino 0
                        let progreso = row.progreso_general || 0;
                        return `<div class="progress"><div class="progress-bar bg-info" role="progressbar" style="width: ${progreso}%">${progreso}%</div></div>`;
                    }
                },
                {
                    data: 'estado',
                    render: function (data) {
                        let badge = 'secondary';
                        if (data === 'en_proceso') badge = 'primary';
                        if (data === 'listo_armado') badge = 'warning';
                        if (data === 'completado') badge = 'success';
                        return `<span class="badge bg-${badge} status-badge">${data.replace('_', ' ').toUpperCase()}</span>`;
                    }
                },
                {
                    data: 'created_at',
                    render: function (data) {
                        return data ? new Date(data).toLocaleDateString() : '';
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-info text-white btn-ver" data-id="${data.id}" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                            <a href="<?= base_url('modulo3/control-bultos') ?>/${data.id}/matriz" class="btn btn-sm btn-success" title="Vista Matriz"><i class="fas fa-th"></i></a>
                            <button class="btn btn-sm btn-warning text-white btn-editar" data-id="${data.id}" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data.id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                        `;
                    }
                }
            ],
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });

        // Poblar selects
        const selectOrden = $('select[name="ordenProduccionId"]');
        ordenes.forEach(o => {
            selectOrden.append(`<option value="${o.opId || o.id}" data-cantidad="${o.cantidadPlan || ''}" data-diseno="${o.diseno || ''}">${o.op || o.folio} - ${o.diseno || o.estilo || ''}</option>`);
        });

        // Autocompletar estilo y cantidad_total al seleccionar una orden
        selectOrden.on('change', function () {
            const selected = $(this).find(':selected');
            const diseno = selected.data('diseno');
            const cantidad = selected.data('cantidad');

            if (diseno) {
                $('input[name="estilo"]').val(diseno);
            }
            if (cantidad) {
                $('input[name="cantidad_total"]').val(cantidad);
            }
        });

        const selectPlantilla = $('select[name="tipo_prenda"]');
        // Usamos un Set para tipos únicos o listamos las plantillas disponibles
        plantillas.forEach(p => {
            selectPlantilla.append(`<option value="${p.tipo_prenda}" data-id="${p.id}">${p.nombre_plantilla} (${p.tipo_prenda})</option>`);
        });

        // Manejar cambio en tipo de prenda para setear plantillaId oculto si fuera necesario
        // O simplemente enviamos el tipo y el backend busca la activa.

        // Guardar Nuevo Control
        $('#btnGuardarControl').click(function () {
            const formData = new FormData(document.getElementById('formNuevoControl'));
            // Agregar el ID de la plantilla seleccionada si es necesario
            const selectedOption = selectPlantilla.find(':selected');
            if (selectedOption.data('id')) {
                formData.append('plantillaId', selectedOption.data('id'));
            }

            $.ajax({
                url: '<?= base_url('modulo3/api/control-bultos/crear') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.ok) {
                        $('#modalNuevoControl').modal('hide');
                        tabla.ajax.reload();
                        Swal.fire('Éxito', 'Control creado correctamente', 'success');
                        document.getElementById('formNuevoControl').reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'No se pudo crear el control', 'error');
                }
            });
        });

        // Ver Detalles
        $('#tablaControles tbody').on('click', '.btn-ver', function () {
            const id = $(this).data('id');
            cargarDetalleControl(id);
        });

        // Editar Control
        $('#tablaControles tbody').on('click', '.btn-editar', function () {
            const id = $(this).data('id');

            // Obtener datos del control
            $.get(`<?= base_url('modulo3/api/control-bultos') ?>/${id}`, function (response) {
                if (response.ok) {
                    const data = response.data;
                    $('#editId').val(data.id);
                    $('#editEstilo').val(data.estilo);
                    $('#editCantidad').val(data.cantidad_total);
                    // Si hubiera observaciones en el response, las ponemos
                    // $('#editObservaciones').val(data.observaciones); 
                    $('#modalEditarControl').modal('show');
                } else {
                    Swal.fire('Error', 'No se pudo cargar la información', 'error');
                }
            });
        });

        // Guardar Edición
        $('#btnActualizarControl').click(function () {
            const id = $('#editId').val();
            const formData = new FormData(document.getElementById('formEditarControl'));

            $.ajax({
                url: `<?= base_url('modulo3/api/control-bultos') ?>/${id}/editar`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.ok) {
                        $('#modalEditarControl').modal('hide');
                        tabla.ajax.reload();
                        Swal.fire('Éxito', 'Control actualizado', 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'No se pudo actualizar', 'error');
                }
            });
        });

        // Eliminar Control
        $('#tablaControles tbody').on('click', '.btn-eliminar', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto. Se eliminarán también los registros asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= base_url('modulo3/api/control-bultos') ?>/${id}/eliminar`,
                        type: 'DELETE',
                        success: function (response) {
                            if (response.ok) {
                                tabla.ajax.reload();
                                Swal.fire('Eliminado', 'El control ha sido eliminado.', 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'No se pudo eliminar el control', 'error');
                        }
                    });
                }
            });
        });

        function cargarDetalleControl(id) {
            $.ajax({
                url: `<?= base_url('modulo3/api/control-bultos') ?>/${id}/progreso`,
                type: 'GET',
                success: function (response) {
                    if (response.ok) {
                        const data = response.data;
                        // Actualizar cabecera del modal
                        // Necesitamos info básica del control también, que podría venir en response.data o hacemos otra llamada
                        // Por simplicidad, asumimos que el endpoint progreso devuelve todo o hacemos llamada a detalle

                        // Llamada adicional para detalles básicos si faltan
                        $.get(`<?= base_url('modulo3/api/control-bultos') ?>/${id}`, function (resDetalle) {
                            if (resDetalle.ok) {
                                const control = resDetalle.data;
                                $('#detalleOrden').text(control.ordenFolio);
                                $('#detalleEstilo').text(control.estilo);
                                $('#detalleEstado').html(`<span class="badge bg-secondary">${control.estado}</span>`);
                                $('#regControlId').val(control.id);

                                // Poblar tabla de operaciones
                                const tbody = $('#tablaOperaciones tbody');
                                tbody.empty();
                                const selectOperacion = $('#regOperacion');
                                selectOperacion.empty().append('<option value="">Seleccione operación...</option>');

                                data.operaciones.forEach(op => {
                                    const progreso = op.porcentaje_completado;
                                    const completado = parseFloat(progreso) >= 100;
                                    const disabledAttr = completado ? 'disabled' : '';

                                    const row = `
                                        <tr>
                                            <td>${op.nombre_operacion}</td>
                                            <td>${op.es_componente == 1 ? '<span class="badge bg-info">Componente</span>' : '<span class="badge bg-primary">Armado</span>'}</td>
                                            <td>${op.piezas_requeridas}</td>
                                            <td>${op.piezas_completadas}</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: ${progreso}%" aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">${progreso}%</div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-success btn-op-registrar" data-id="${op.id}" ${disabledAttr} title="Registrar producción para esta operación">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                    tbody.append(row);

                                    // Agregar a select de registro si no está completa
                                    if (!completado) {
                                        selectOperacion.append(`<option value="${op.id}">${op.nombre_operacion}</option>`);
                                    }
                                });

                                $('#modalDetalleControl').modal('show');
                            }
                        });
                    }
                }
            });
        }

        // Abrir modal de registro desde detalle
        $('#btnRegistrarProduccion').click(function () {

            // Poblar empleados
            const selectEmpleado = $('#regEmpleado');
            selectEmpleado.empty().append('<option value="">Seleccione empleado...</option>');
            empleados.forEach(e => {
                selectEmpleado.append(`<option value="${e.id}">${e.nombre} ${e.apellido}</option>`);
            });

            // Si hay empleadoActual en sesión, preseleccionarlo
            if (empleadoActual && empleadoActual.id) {
                selectEmpleado.val(empleadoActual.id);
            }

            // Establecer hora de inicio y fin automáticamente al abrir el modal
            const inputHoraInicio = $('input[name="hora_inicio"]');
            const inputHoraFin = $('input[name="hora_fin"]');
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hh}:${mm}`;
            inputHoraInicio.val(currentTime);
            inputHoraFin.val(currentTime);

            $('#modalDetalleControl').modal('hide');
            $('#modalRegistroProduccion').modal('show');
        });

        // Registrar producción desde el botón por operación en la tabla de detalle
        $('#tablaOperaciones').on('click', '.btn-op-registrar', function () {
            const opId = $(this).data('id');

            // Asegurar que la operación exista en el select y seleccionarla
            const selectOperacion = $('#regOperacion');

            if (!selectOperacion.find(`option[value="${opId}"]`).length) {
                // Como respaldo, agregamos la opción con el texto "Operación seleccionada"
                selectOperacion.append(`<option value="${opId}">Operación seleccionada</option>`);
            }
            selectOperacion.val(opId);

            // Poblar empleados y aplicar empleadoActual si existe
            const selectEmpleado = $('#regEmpleado');

            selectEmpleado.empty().append('<option value="">Seleccione empleado...</option>');
            empleados.forEach(e => {
                selectEmpleado.append(`<option value="${e.id}">${e.nombre} ${e.apellido}</option>`);
            });

            if (empleadoActual && empleadoActual.id) {
                selectEmpleado.val(empleadoActual.id);
            }

            // Establecer hora de inicio y fin automáticamente al abrir el modal
            const inputHoraInicio = $('input[name="hora_inicio"]');
            const inputHoraFin = $('input[name="hora_fin"]');
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hh}:${mm}`;
            inputHoraInicio.val(currentTime);
            inputHoraFin.val(currentTime);

            // Mostrar modal de registro (dejamos abierto el de detalle para que el usuario vea el contexto)
            $('#modalRegistroProduccion').modal('show');
        });

        // Guardar Registro Producción
        $('#btnGuardarProduccion').click(function () {
            const formData = new FormData(document.getElementById('formRegistroProduccion'));

            $.ajax({
                url: '<?= base_url('modulo3/api/control-bultos/registrar-produccion') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.ok) {
                        $('#modalRegistroProduccion').modal('hide');
                        Swal.fire('Éxito', 'Producción registrada', 'success');
                        document.getElementById('formRegistroProduccion').reset();
                        // Recargar detalle
                        const id = $('#regControlId').val();
                        cargarDetalleControl(id);
                    } else {
                        let msg = response.message;
                        if (response.errors) {
                            msg += ': ' + JSON.stringify(response.errors);
                        }
                        if (response.db_error) {
                            msg += ' | DB Error: ' + JSON.stringify(response.db_error);
                        }
                        if (response.debug) {
                            msg += ' | Debug: ' + JSON.stringify(response.debug);
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Error al registrar producción', 'error');
                }
            });
        });
        // --- Lógica de Plantillas (Builder) ---
        let operacionesBuilder = [];
        let plantillaEditId = null; // null = nueva, número = editando existente
        let plantillasCache = [];

        // Cargar plantillas al abrir el modal o tab
        $('#modalPlantillas').on('shown.bs.modal', function () {
            cargarPlantillas();
            resetBuilder();
        });

        $('#lista-tab').on('shown.bs.tab', function () {
            cargarPlantillas();
        });

        // Nota: No reseteamos automáticamente al entrar a la pestaña "Nueva Plantilla"
        // para no borrar los datos cuando venimos desde "Editar".

        function resetBuilder() {
            operacionesBuilder = [];
            plantillaEditId = null;
            renderBuilder();
            $('#formNuevaPlantilla')[0].reset();
            $('#opPiezas').val(1);
        }

        // Agregar Operación al Builder
        $('#btnAgregarOp').click(function () {
            const nombre = $('#opNombre').val().trim();
            const tipo = $('#opTipo').val();
            const piezas = parseInt($('#opPiezas').val());

            if (!nombre) {
                Swal.fire('Atención', 'Escriba un nombre para la operación', 'warning');
                return;
            }
            if (piezas < 1) {
                Swal.fire('Atención', 'La cantidad de piezas debe ser mayor a 0', 'warning');
                return;
            }

            operacionesBuilder.push({
                nombre: nombre,
                es_componente: parseInt(tipo),
                piezas_por_prenda: piezas,
                orden: operacionesBuilder.length + 1
            });

            // Limpiar inputs
            $('#opNombre').val('').focus();
            $('#opPiezas').val(1);

            renderBuilder();
        });

        // Renderizar tabla del builder
        function renderBuilder() {
            const tbody = $('#tablaBuilderOperaciones tbody');
            tbody.empty();

            if (operacionesBuilder.length === 0) {
                tbody.html('<tr id="row-empty"><td colspan="5" class="text-center text-muted py-3">No hay operaciones agregadas</td></tr>');
                return;
            }

            operacionesBuilder.forEach((op, index) => {
                // Asegurar orden consecutivo
                op.orden = index + 1;

                const tipoBadge = op.es_componente == 1
                    ? '<span class="badge bg-info text-dark">Componente</span>'
                    : '<span class="badge bg-primary">Armado</span>';

                tbody.append(`
                    <tr>
                        <td>${op.orden}</td>
                        <td>${op.nombre}</td>
                        <td>${tipoBadge}</td>
                        <td>${op.piezas_por_prenda}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-op" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        // Eliminar operación del builder
        $(document).on('click', '.btn-remove-op', function () {
            const index = $(this).data('index');
            operacionesBuilder.splice(index, 1);
            renderBuilder();
        });

        function cargarPlantillas() {
            $.ajax({
                url: '<?= base_url('modulo3/api/plantillas-operaciones') ?>',
                type: 'GET',
                success: function (response) {
                    if (response.ok) {
                        const tbody = $('#tbodyPlantillas');
                        tbody.empty();
                        plantillasCache = response.data || [];

                        plantillasCache.forEach(p => {
                            // Parsear operaciones para contar o mostrar resumen
                            let opsCount = 0;
                            try {
                                const ops = JSON.parse(p.operaciones);
                                if (Array.isArray(ops)) {
                                    opsCount = ops.length;
                                }
                            } catch (e) { }

                            tbody.append(`
                                <tr>
                                    <td>${p.nombre_plantilla}</td>
                                    <td>${p.tipo_prenda}</td>
                                    <td>${opsCount} operaciones</td>
                                    <td>
                                     <button class="btn btn-sm btn-outline-primary btn-editar-plantilla" data-id="${p.id}"><i class="fas fa-edit"></i></button>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                }
            });
        }

        // Cargar una plantilla existente en el builder para editar
        $(document).on('click', '.btn-editar-plantilla', function () {
            const id = $(this).data('id');
            console.log('Editar plantilla clicada, id =', id);
            console.log('plantillasCache actuales:', plantillasCache);
            const plantilla = plantillasCache.find(p => String(p.id) === String(id));

            if (!plantilla) {
                console.warn('No se encontró plantilla en cache para id', id);
                Swal.fire('Error', 'No se encontró la plantilla seleccionada', 'error');
                return;
            }

            plantillaEditId = plantilla.id;

            console.log('Plantilla encontrada para edición:', plantilla);

            // Llenar datos básicos
            $('input[name="nombre_plantilla"]').val(plantilla.nombre_plantilla);
            $('input[name="tipo_prenda"]').val(plantilla.tipo_prenda);

            // Cargar operaciones al builder
            let ops = [];
            if (typeof plantilla.operaciones === 'string') {
                try {
                    ops = JSON.parse(plantilla.operaciones) || [];
                } catch (e) {
                    console.error('Error parseando plantilla.operaciones como JSON', e, 'valor:', plantilla.operaciones);
                    ops = [];
                }
            } else if (Array.isArray(plantilla.operaciones)) {
                ops = plantilla.operaciones;
            }

            operacionesBuilder = Array.isArray(ops) ? ops : [];
            console.log('Operaciones cargadas al builder:', operacionesBuilder);
            renderBuilder();

            // Cambiar a la pestaña de Nueva Plantilla para editar
            $('#nueva-tab').tab('show');
        });

        // Guardar Plantilla (nueva o existente)
        $('#btnGuardarPlantilla').click(function (e) {
            e.preventDefault();
            if (operacionesBuilder.length === 0) {
                Swal.fire('Error', 'Debe agregar al menos una operación a la plantilla', 'error');
                return;
            }

            // Serializar operaciones a JSON
            $('#inputOperacionesJson').val(JSON.stringify(operacionesBuilder));

            const formData = new FormData(document.getElementById('formNuevaPlantilla'));

            let url = '<?= base_url('modulo3/api/plantillas-operaciones/crear') ?>';
            if (plantillaEditId) {
                url = '<?= base_url('modulo3/api/plantillas-operaciones') ?>/' + plantillaEditId + '/editar';
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.ok) {
                        const msg = plantillaEditId ? 'Plantilla actualizada correctamente' : 'Plantilla creada correctamente';
                        Swal.fire('Éxito', msg, 'success');
                        resetBuilder();
                        cargarPlantillas();
                        // Cambiar a tab de lista
                        $('#lista-tab').tab('show');
                        // Recargar página o actualizar select de plantillas en el modal de nuevo control
                        location.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'No se pudo guardar la plantilla', 'error');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>