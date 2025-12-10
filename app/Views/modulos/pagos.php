<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    div.dt-buttons {
        display: flex !important;
        gap: .65rem;
        flex-wrap: wrap;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    div.dt-buttons > .btn:not(:first-child) {
        margin-left: 0 !important;
    }

    div.dt-buttons > .btn {
        border-radius: .65rem !important;
        padding: .45rem 1rem !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .badge-forma-pago {
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }

    .destajo { background-color: #e3f2fd; color: #1565c0; }
    .por-dia { background-color: #e8f5e8; color: #2e7d32; }
    .por-hora { background-color: #fff3e0; color: #ef6c00; }
    .no-registrada { background-color: #f5f5f5; color: #616161; }

    .btn-editar-forma {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pagos de Empleados</h1>
        <p class="text-muted mb-0">Gestión de formas de pago del personal</p>
    </div>
    <div>
    </div>
</div>

<!-- Pestañas principales -->
<ul class="nav nav-tabs" id="tabsPagos" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="tab-empleados" data-bs-toggle="tab" href="#pane-empleados" role="tab" aria-controls="pane-empleados" aria-selected="true">
            <i class="bi bi-people me-1"></i>Lista de Empleados y Formas de Pago
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="tab-pagos" data-bs-toggle="tab" href="#pane-pagos" role="tab" aria-controls="pane-pagos" aria-selected="false">
            <i class="bi bi-cash-stack me-1"></i>Pagos
        </a>
    </li>
</ul>

<div class="tab-content mt-3" id="tabsPagosContent">
    <!-- Pane: Lista de empleados (contenido actual) -->
    <div class="tab-pane fade show active" id="pane-empleados" role="tabpanel" aria-labelledby="tab-empleados">
        <!-- Modal para configurar tarifas por forma de pago -->
        <div class="modal fade" id="modalTarifasPago" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-cash-coin me-2"></i>Configurar Tarifas de Pago
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formTarifasPago">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Forma de Pago</label>
                                <select class="form-select" id="tarifa_forma_pago" name="forma_pago" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Destajo">Destajo</option>
                                    <option value="Por día">Por día</option>
                                    <option value="Por hora">Por hora</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Monto</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="tarifa_monto" name="monto" placeholder="0.00" required>
                            </div>

                            <hr>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="tablaTarifas">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Forma de pago</th>
                                        <th>Monto</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!-- Se llena por AJAX -->
                                    </tbody>
                                </table>
                                <small class="text-muted">Haz clic en una fila para cargar la tarifa y editarla.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Guardar tarifa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill text-primary fs-2 mb-2"></i>
                        <h5 class="card-title mb-1"><?= isset($totalEmpleados) ? (int)$totalEmpleados : count($empleados) ?></h5>
                        <p class="card-text text-muted small">Total Empleados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-briefcase-fill text-info fs-2 mb-2"></i>
                        <h5 class="card-title mb-1"><?= isset($countDestajo) ? (int)$countDestajo : 0 ?></h5>
                        <p class="card-text text-muted small">Destajo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-day-fill text-success fs-2 mb-2"></i>
                        <h5 class="card-title mb-1"><?= isset($countPorDia) ? (int)$countPorDia : 0 ?></h5>
                        <p class="card-text text-muted small">Por Día</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-fill text-warning fs-2 mb-2"></i>
                        <h5 class="card-title mb-1"><?= isset($countPorHora) ? (int)$countPorHora : 0 ?></h5>
                        <p class="card-text text-muted small">Por Hora</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de empleados -->
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-table me-2"></i>Lista de Empleados y Formas de Pago</strong>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" type="button" onclick="abrirModalTarifas()">
                        <i class="bi bi-cash-coin me-1"></i> Tarifas
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="recargarTabla()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaEmpleados">
                        <thead class="table-light">
                        <tr>
                            <th>No. Empleado</th>
                            <th>Nombre Completo</th>
                            <th>Puesto</th>
                            <th>Forma de Pago</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($empleados)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay empleados registrados para esta maquiladora
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($empleados as $empleado): ?>
                                <tr data-empleado-id="<?= $empleado['id'] ?>">
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($empleado['noEmpleado']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= esc($empleado['nombre_completo']) ?></strong>
                                    </td>
                                    <td><?= esc($empleado['puesto'] ?? 'Sin puesto') ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'no-registrada';
                                        switch($empleado['Forma_pago']) {
                                            case 'Destajo': $badgeClass = 'destajo'; break;
                                            case 'Por dia': $badgeClass = 'por-dia'; break;
                                            case 'Por hora': $badgeClass = 'por-hora'; break;
                                        }
                                        ?>
                                        <span class="badge badge-forma-pago <?= $badgeClass ?>">
                                        <?= esc($empleado['Forma_pago']) ?>
                                    </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= esc($empleado['email'] ?? 'Sin email') ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= esc($empleado['telefono'] ?? 'Sin teléfono') ?></small>
                                    </td>
                                    <td>
                                    <span class="badge bg-<?= $empleado['estatus_clase'] ?>">
                                        <?= esc($empleado['estatus_texto']) ?>
                                    </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary btn-editar-forma" onclick="editarFormaPago(<?= $empleado['id'] ?>)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /tab-pane empleados -->

    <!-- Pane: Pagos (tabla de pagos diarios) -->
    <div class="tab-pane fade" id="pane-pagos" role="tabpanel" aria-labelledby="tab-pagos">
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-calendar-day me-2"></i>Pagos diarios</strong>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" id="rep_fecha_inicio" class="form-control form-control-sm">
                    <span>a</span>
                    <input type="date" id="rep_fecha_fin" class="form-control form-control-sm">
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="cargarPagosDiarios()">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" id="tablaPagosDiarios">
                        <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>No. Empleado</th>
                            <th>Nombre</th>
                            <th>Forma de pago</th>
                            <th>Horas</th>
                            <th>Tarifa</th>
                            <th>Pago del día</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Selecciona un rango de fechas y presiona buscar.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- /tab-pane pagos -->

</div><!-- /tab-content -->

<!-- Modal para editar forma de pago -->
<div class="modal fade" id="modalFormaPago" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Editar Forma de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFormaPago">
                <div class="modal-body">
                    <input type="hidden" id="empleado_id" name="empleado_id">

                    <div class="mb-3">
                        <label class="form-label">Empleado:</label>
                        <input type="text" class="form-control" id="empleado_nombre" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Forma de Pago:</label>
                        <select class="form-select" id="forma_pago" name="forma_pago" required>
                            <option value="">Seleccionar...</option>
                            <option value="Destajo">Destajo</option>
                            <option value="Por dia">Por día</option>
                            <option value="Por hora">Por hora</option>
                        </select>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        if ($.fn.dataTable && $.fn.dataTable.Buttons) {
            $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons';
        }

        var langES = {
            sProcessing: "Procesando...",
            sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados",
            sEmptyTable: "Ningún dato disponible",
            sInfo: "Filas: _TOTAL_",
            sInfoEmpty: "Filas: 0",
            sInfoFiltered: "(de _MAX_)",
            sSearch: "Buscar:",
            sLoadingRecords: "Cargando...",
            oPaginate: {
                sFirst: "Primero",
                sLast: "Último",
                sNext: "Siguiente",
                sPrevious: "Anterior"
            },
            buttons: {copy: "Copiar", csv: "CSV", excel: "Excel", pdf: "PDF", print: "Imprimir"}
        };

        var tieneDatos = $('#tablaEmpleados tbody tr').filter(function () {
            return !$(this).find('td[colspan]').length;
        }).length > 0;

        var tabla = null;
        if (tieneDatos) {
            tabla = $('#tablaEmpleados').DataTable({
                language: langES,
                pageLength: 10,
                columnDefs: [{targets: -1, orderable: false, searchable: false}]
            });

            if ($.fn.dataTable && $.fn.dataTable.Buttons) {
                var botones = new $.fn.dataTable.Buttons(tabla, {
                    buttons: [
                        {extend: 'copy', text: 'Copiar', className: 'btn btn-secondary'},
                        {extend: 'csv', text: 'CSV', className: 'btn btn-secondary'},
                        {extend: 'excel', text: 'Excel', className: 'btn btn-secondary'},
                        {extend: 'pdf', text: 'PDF', className: 'btn btn-secondary'},
                        {extend: 'print', text: 'Imprimir', className: 'btn btn-secondary'}
                    ]
                }).container();

                $('#headerEmpleados').prepend(botones);
            }
        }

        // Fechas por defecto para reporte diario (hoy)
        var hoy = new Date().toISOString().slice(0, 10);
        $('#rep_fecha_inicio').val(hoy);
        $('#rep_fecha_fin').val(hoy);

        // Submit del formulario de forma de pago
        $('#formFormaPago').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '<?= base_url('modulo1/pagos/actualizar-forma-pago') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#modalFormaPago').modal('hide');
                        recargarTabla();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor'
                    });
                }
            });
        });

        // Submit del formulario de tarifas de modo de pago
        $('#formTarifasPago').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '<?= base_url('modulo1/pagos/guardar-tarifa') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tarifa guardada',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#modalTarifasPago').modal('hide');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo guardar la tarifa'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor'
                    });
                }
            });
        });
    });

    function editarFormaPago(empleadoId) {
        // Obtener datos del empleado
        $.get('<?= base_url('modulo1/pagos/empleado/') ?>' + empleadoId, function(response) {
            if (response.success) {
                var empleado = response.empleado;
                $('#empleado_id').val(empleado.id);
                $('#empleado_nombre').val(empleado.nombre_completo);
                $('#forma_pago').val(empleado.Forma_pago);
                $('#modalFormaPago').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        }).fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al obtener datos del empleado'
            });
        });
    }

    function recargarTabla() {
        location.reload();
    }

    function abrirModalTarifas() {
        $('#formTarifasPago')[0].reset();

        // Limpiar tabla
        var $tbody = $('#tablaTarifas tbody');
        $tbody.empty().append('<tr><td colspan="2" class="text-center text-muted">Cargando...</td></tr>');

        // Cargar tarifas actuales
        $.get('<?= base_url('modulo1/pagos/tarifas') ?>', function(response) {
            $tbody.empty();
            if (response.success && response.data && response.data.length) {
                response.data.forEach(function(row) {
                    var tr = $('<tr></tr>')
                        .attr('data-forma', row.forma_pago)
                        .attr('data-monto', row.monto)
                        .append('<td>' + row.forma_pago + '</td>')
                        .append('<td>' + parseFloat(row.monto).toFixed(2) + '</td>');
                    $tbody.append(tr);
                });
            } else {
                $tbody.append('<tr><td colspan="2" class="text-center text-muted">Sin tarifas registradas</td></tr>');
            }

            // Click en fila: cargar en formulario
            $('#tablaTarifas tbody tr').on('click', function() {
                var forma = $(this).data('forma');
                var monto = $(this).data('monto');
                $('#tarifa_forma_pago').val(forma);
                $('#tarifa_monto').val(monto);
            });
        }).fail(function() {
            $tbody.empty().append('<tr><td colspan="2" class="text-center text-danger">Error al cargar tarifas</td></tr>');
        });

        $('#modalTarifasPago').modal('show');
    }

    function cargarPagosDiarios() {
        var fechaInicio = $('#rep_fecha_inicio').val();
        var fechaFin    = $('#rep_fecha_fin').val();

        if (!fechaInicio || !fechaFin) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debes seleccionar ambas fechas.'
            });
            return;
        }

        var $tbody = $('#tablaPagosDiarios tbody');
        $tbody.empty().append('<tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>');

        $.ajax({
            url: '<?= base_url('modulo1/pagos/reporte-diario') ?>',
            type: 'POST',
            data: {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            },
            success: function(response) {
                $tbody.empty();
                if (response.success && response.data && response.data.length) {
                    response.data.forEach(function(row) {
                        var tr = '<tr>' +
                            '<td>' + row.fecha + '</td>' +
                            '<td>' + row.noEmpleado + '</td>' +
                            '<td>' + row.nombre_completo + '</td>' +
                            '<td>' + row.forma_pago_empleado + '</td>' +
                            '<td class="text-end">' + parseFloat(row.horas_totales).toFixed(2) + '</td>' +
                            '<td class="text-end">' + parseFloat(row.tarifa_base).toFixed(2) + '</td>' +
                            '<td class="text-end">' + parseFloat(row.pago_dia).toFixed(2) + '</td>' +
                            '</tr>';
                        $tbody.append(tr);
                    });
                } else {
                    $tbody.append('<tr><td colspan="7" class="text-center text-muted">Sin registros para el rango seleccionado.</td></tr>');
                }
            },
            error: function() {
                $tbody.empty().append('<tr><td colspan="7" class="text-center text-danger">Error al cargar el reporte.</td></tr>');
            }
        });
    }

    function exportarDatos() {
        $.get('<?= base_url('modulo1/pagos/exportar') ?>', function(response) {
            if (response.success) {
                // Por ahora, mostramos los datos en una tabla simple
                // En el futuro, aquí se podría generar Excel o CSV
                var html = '<table class="table table-striped">';
                html += '<thead><tr>';
                Object.keys(response.data[0]).forEach(key => {
                    html += '<th>' + key + '</th>';
                });
                html += '</tr></thead><tbody>';

                response.data.forEach(row => {
                    html += '<tr>';
                    Object.values(row).forEach(value => {
                        html += '<td>' + value + '</td>';
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';

                Swal.fire({
                    title: 'Datos para Exportar',
                    html: '<div style="max-height: 400px; overflow-y: auto;">' + html + '</div>',
                    width: '80%',
                    showConfirmButton: true,
                    confirmButtonText: 'Copiar'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
