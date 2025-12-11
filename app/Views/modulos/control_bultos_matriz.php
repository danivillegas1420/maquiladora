<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .matriz-container {
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .matriz-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px 8px 0 0;
        margin: -20px -20px 20px -20px;
    }

    .matriz-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .matriz-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 8px;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
        text-align: center;
    }

    .matriz-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .matriz-table tbody tr:hover {
        background: #e9ecef;
    }

    .matriz-table td {
        padding: 10px 8px;
        border-bottom: 1px solid #dee2e6;
        text-align: center;
    }

    .matriz-table td.info-cell {
        text-align: left;
        font-weight: 500;
    }

    .checkbox-cell {
        cursor: pointer;
        position: relative;
    }

    .checkbox-cell input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #28a745;
    }

    .checkbox-cell input[type="checkbox"]:checked {
        background-color: #28a745;
    }

    .checkbox-cell .completed-badge {
        display: inline-block;
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 0.75rem;
    }

    .progress-footer {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #dee2e6;
    }

    .operation-progress {
        margin-bottom: 10px;
    }

    .operation-progress .progress {
        height: 25px;
    }

    .legend {
        display: flex;
        gap: 20px;
        margin-top: 15px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-box {
        width: 20px;
        height: 20px;
        border: 2px solid #dee2e6;
        border-radius: 3px;
    }

    .legend-box.completed {
        background-color: #28a745;
        border-color: #28a745;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .matriz-header {
            background: #667eea !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h2 class="mb-0">Control de Bultos - Vista Matriz</h2>
        <small class="text-muted">Orden: <?= esc($control['ordenFolio']) ?> | Estilo:
            <?= esc($control['estilo']) ?></small>
    </div>
    <div>
        <a href="<?= base_url('modulo3/control-bultos') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>

<div class="matriz-container">
    <div class="matriz-header">
        <h4 class="mb-2">Orden #<?= esc($control['ordenFolio']) ?></h4>
        <p class="mb-0">
            Estilo: <?= esc($control['estilo']) ?> |
            Cantidad Total: <?= esc($control['cantidad_total']) ?> piezas
        </p>
        <div class="progress mt-3" style="height: 30px; background: rgba(255,255,255,0.2);">
            <div class="progress-bar bg-success" role="progressbar"
                style="width: <?= $control['progreso_general'] ?? 0 ?>%">
                <?= $control['progreso_general'] ?? 0 ?>% Completado
            </div>
        </div>
    </div>

    <?php if (empty($bultos)): ?>
        <!-- Mensaje cuando no hay bultos -->
        <div class="alert alert-warning mt-4" role="alert">
            <h5><i class="fas fa-exclamation-triangle"></i> No hay bultos creados para este control</h5>
            <p class="mb-3">Para usar la vista de matriz, primero necesitas crear los bultos individuales.</p>

            <div class="card bg-light">
                <div class="card-body">
                    <h6>Crear Bultos Automáticamente</h6>
                    <p class="small mb-3">Puedes crear bultos automáticamente basados en la cantidad total de la orden.</p>

                    <form id="formCrearBultos" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Número de Bultos</label>
                            <input type="number" class="form-control" id="numeroBultos" value="1" min="1" max="100"
                                required>
                            <small class="text-muted">Divide la producción en bultos</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Talla por Defecto</label>
                            <select class="form-select" id="tallaDefecto">
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M" selected>M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                                <option value="MIXTA">MIXTA</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="btnCrearBultos">
                                <i class="fas fa-plus"></i> Crear Bultos
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Ejemplo:</strong> Si tienes 100 piezas y creas 4 bultos, cada bulto tendrá 25 piezas.
                        </small>
                    </div>
                </div>
            </div>

            <hr>

            <p class="mb-0">
                <strong>O bien:</strong> Puedes crear bultos manualmente desde la vista de detalle del control.
                <a href="<?= base_url('modulo3/control-bultos') ?>" class="btn btn-sm btn-secondary ms-2">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Tabla de matriz cuando hay bultos -->
        <table class="matriz-table">
            <thead>
                <tr>
                    <th style="width: 100px;">Bulto #</th>
                    <th style="width: 80px;">Talla</th>
                    <th style="width: 80px;">Cantidad</th>
                    <?php foreach ($operaciones as $op): ?>
                        <th style="min-width: 120px;">
                            <?= esc($op['nombre']) ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bultos as $bulto): ?>
                    <tr>
                        <td class="info-cell"><?= esc($bulto['numero_bulto']) ?></td>
                        <td><?= esc($bulto['talla']) ?></td>
                        <td><?= esc($bulto['cantidad']) ?></td>
                        <?php foreach ($operaciones as $op): ?>
                            <td class="checkbox-cell">
                                <?php
                                $progreso = $bulto['operaciones'][$op['id']] ?? ['completado' => 0];
                                $checked = $progreso['completado'] ? 'checked' : '';
                                ?>
                                <?php if ($progreso['completado']): ?>
                                    <span class="completed-badge" title="Completado">✓</span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-registrar-bulto"
                                        data-bulto-id="<?= $bulto['id'] ?>" data-bulto-numero="<?= esc($bulto['numero_bulto']) ?>"
                                        data-bulto-cantidad="<?= $bulto['cantidad'] ?>" data-operacion-id="<?= $op['id'] ?>"
                                        data-operacion-nombre="<?= esc($op['nombre']) ?>">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="progress-footer">
            <h5>Progreso por Operación</h5>
            <?php foreach ($progresoOperaciones as $progOp): ?>
                <div class="operation-progress">
                    <div class="d-flex justify-content-between mb-1">
                        <span><?= esc($progOp['nombre_operacion']) ?></span>
                        <span><?= $progOp['bultos_completados'] ?> / <?= $progOp['total_bultos'] ?> bultos</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $progOp['porcentaje'] ?>%">
                            <?= number_format($progOp['porcentaje'], 1) ?>%
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-box completed"></div>
                    <span>Completado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box"></div>
                    <span>Pendiente</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Registrar Producción (Reutilizando el modal existente) -->
<div class="modal fade" id="modalRegistroProduccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistroProduccion">
                    <input type="hidden" name="controlBultoId" id="regControlId" value="<?= $control['id'] ?>">
                    <input type="hidden" name="bultoId" id="regBultoId">
                    <input type="hidden" name="operacionControlId" id="regOperacion">

                    <div class="mb-3">
                        <label class="form-label">Bulto</label>
                        <input type="text" class="form-control" id="regBultoInfo" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Operación</label>
                        <input type="text" class="form-control" id="regOperacionInfo" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Empleado</label>
                        <select class="form-select" name="empleadoId" id="regEmpleado" required>
                            <option value="">Seleccione empleado...</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= ($empleadoActual && $empleadoActual['id'] == $emp['id']) ? 'selected' : '' ?>>
                                    <?= esc($emp['nombre'] . ' ' . $emp['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cantidad Realizada</label>
                            <input type="number" class="form-control" name="cantidad_producida" id="regCantidad"
                                required min="1">
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
                            <input type="time" class="form-control" name="hora_inicio" id="regHoraInicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Fin</label>
                            <input type="time" class="form-control" name="hora_fin" id="regHoraFin">
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Abrir modal al hacer clic en botón de registrar
        $('.btn-registrar-bulto').click(function () {
            const bultoId = $(this).data('bulto-id');
            const bultoNumero = $(this).data('bulto-numero');
            const bultoCantidad = $(this).data('bulto-cantidad');
            const operacionId = $(this).data('operacion-id');
            const operacionNombre = $(this).data('operacion-nombre');

            // Llenar campos del modal
            $('#regBultoId').val(bultoId);
            $('#regBultoInfo').val(`${bultoNumero} (${bultoCantidad} piezas)`);
            $('#regOperacion').val(operacionId);
            $('#regOperacionInfo').val(operacionNombre);
            $('#regCantidad').val(bultoCantidad);

            // Establecer hora actual
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hh}:${mm}`;
            $('#regHoraInicio').val(currentTime);
            $('#regHoraFin').val(currentTime);

            // Mostrar modal
            $('#modalRegistroProduccion').modal('show');
        });

        // Guardar registro de producción
        $('#btnGuardarProduccion').click(function () {
            const formData = new FormData(document.getElementById('formRegistroProduccion'));

            // Validar que se haya seleccionado empleado
            if (!$('#regEmpleado').val()) {
                Swal.fire('Error', 'Debe seleccionar un empleado', 'error');
                return;
            }

            $.ajax({
                url: '<?= base_url('modulo3/api/control-bultos/registrar-produccion-matriz') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.ok) {
                        $('#modalRegistroProduccion').modal('hide');
                        Swal.fire({
                            title: 'Éxito',
                            text: 'Producción registrada correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Recargar la página para actualizar la matriz
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'No se pudo registrar la producción', 'error');
                }
            });
        });

        // Crear bultos automáticamente
        $('#btnCrearBultos').click(function() {
            const numeroBultos = parseInt($('#numeroBultos').val());
            const tallaDefecto = $('#tallaDefecto').val();
            const cantidadTotal = <?= $control['cantidad_total'] ?>;
            const controlId = <?= $control['id'] ?>;
            
            if (numeroBultos < 1 || numeroBultos > 100) {
                Swal.fire('Error', 'El número de bultos debe estar entre 1 y 100', 'error');
                return;
            }
            
            Swal.fire({
                title: '¿Crear bultos?',
                text: `Se crearán ${numeroBultos} bultos con aproximadamente ${Math.ceil(cantidadTotal / numeroBultos)} piezas cada uno`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('modulo3/api/control-bultos/crear-bultos-auto') ?>',
                        type: 'POST',
                        data: {
                            controlBultoId: controlId,
                            numeroBultos: numeroBultos,
                            talla: tallaDefecto,
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            if (response.ok) {
                                Swal.fire({
                                    title: 'Éxito',
                                    text: `Se crearon ${numeroBultos} bultos correctamente`,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudieron crear los bultos', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>