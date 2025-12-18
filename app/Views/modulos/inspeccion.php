<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Estilos para botones gris intenso con letras blancas */
    .dt-buttons .btn {
        margin-right: .5rem;
        border-radius: .375rem !important;
        background-color: #6c757d !important;
        border: 1px solid #6c757d !important;
        color: #ffffff !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        transition: all 0.15s ease-in-out;
    }

    .dt-buttons .btn:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .dt-buttons .btn:active {
        background-color: #545b62 !important;
        border-color: #4e555b !important;
        transform: translateY(0);
    }

    .dt-buttons .btn:last-child {
        margin-right: 0;
    }

    /* Estilos para botones de acción minimalistas */
    .btn-accion {
        background-color: transparent !important;
        border: 1px solid #dee2e6 !important;
        color: #6c757d !important;
        width: 32px;
        height: 32px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px !important;
        transition: all 0.15s ease-in-out;
    }

    .btn-accion:hover {
        background-color: #f8f9fa !important;
        border-color: #adb5bd !important;
        color: #495057 !important;
        transform: translateY(-1px);
    }

    .btn-accion:active {
        background-color: #e9ecef !important;
        transform: translateY(0);
    }

    .badge {
        font-size: 0.8rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inspecciones</h1>
    <span class="badge bg-info">Calidad</span>
    <button type="button" id="btnPuntosInspeccion" class="btn btn-primary ms-auto">
        <i class="fas fa-list-ul me-1"></i> Puntos de Inspección
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Inspecciones</strong>
    </div>
    <div class="card-body table-responsive">
        <table id="tablaInspeccion" class="table table-striped table-bordered align-middle" style="width:100%">
            <thead class="table-primary">
                <tr>
                    <th>No.</th>
                    <th>No. Inspección</th>
                    <th>Folio</th>
                    <th>Punto Inspección</th>
                    <th>Resultado</th>
                    <th>Fecha</th>
                    <th>Observaciones</th>
                    <th>Reproceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lista) && is_array($lista)): ?>
                    <?php foreach ($lista as $item): ?>
                        <tr>
                            <td><?= $item['num'] ?? '' ?></td>
                            <td><?= esc($item['numero_inspeccion'] ?? 'N/A') ?></td>
                            <td><?= esc($item['ordenFolio']) ?></td>
                            <td>
                                <select class="form-select form-select-sm punto-inspeccion-select"
                                    data-inspeccion-id="<?= $item['inspeccionId'] ?? $item['id'] ?>" style="min-width: 150px;">
                                    <?php foreach ($puntosInspeccion as $punto): ?>
                                        <option value="<?= $punto['id'] ?>" <?= (($item['puntoInspeccionId'] ?? '') == ($punto['id'] ?? '')) ? 'selected' : '' ?>>
                                            <?= esc($punto['tipo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'secondary';
                                $resultado = strtolower($item['resultado'] ?? 'pendiente');
                                if (strpos($resultado, 'aprobado') !== false) {
                                    $badgeClass = 'success';
                                } elseif (strpos($resultado, 'rechazado') !== false) {
                                    $badgeClass = 'danger';
                                } elseif (strpos($resultado, 'pendiente') !== false) {
                                    $badgeClass = 'warning';
                                }
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>"><?= esc(ucfirst($resultado)) ?></span>
                            </td>
                            <td><?= !empty($item['fecha']) ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A' ?></td>
                            <td class="small"><?= esc($item['observaciones'] ?? 'Sin observaciones') ?></td>
                            <td>
                                <?php if (!empty($item['reprocesoId'])): ?>
                                    <div class="small">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-tools text-primary me-1"></i>
                                            <span class="fw-medium"><?= esc($item['accion'] ?? 'Sin acción definida') ?></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-boxes text-info me-1"></i>
                                            <span>Cantidad: <?= $item['cantidad'] ?? 0 ?></span>
                                        </div>
                                        <?php if (!empty($item['fechaReproceso'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-calendar-alt text-success me-1"></i>
                                                <span><?= date('d/m/Y', strtotime($item['fechaReproceso'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Sin reproceso</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-accion btn-ver-detalles me-1" title="Ver Detalles"
                                    data-id="<?= $item['inspeccionId'] ?? $item['id'] ?>"
                                    data-inspeccion="<?= esc($item['numero_inspeccion'] ?? 'N/A', 'attr') ?>"
                                    data-orden-produccion="<?= esc($item['ordenFolio'] ?? 'N/A', 'attr') ?>"
                                    data-punto-inspeccion="<?= esc($item['puntoInspeccionId'] ?? 'N/A', 'attr') ?>"
                                    data-fecha="<?= !empty($item['fecha']) ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A' ?>"
                                    data-resultado="<?= strtolower($item['resultado'] ?? 'pendiente') ?>"
                                    data-observaciones="<?= esc($item['observaciones'] ?? '', 'attr') ?>"
                                    data-reproceso-id="<?= $item['reprocesoId'] ?? '' ?>"
                                    data-accion-reproceso="<?= esc($item['accion'] ?? '', 'attr') ?>"
                                    data-cantidad-reproceso="<?= $item['cantidad'] ?? '0' ?>"
                                    data-fecha-reproceso="<?= $item['fechaReproceso'] ?? '' ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-accion btn-evaluar" title="Evaluar"
                                    data-id="<?= $item['inspeccionId'] ?? $item['id'] ?>"
                                    data-inspeccion="<?= esc($item['numero_inspeccion'] ?? 'N/A', 'attr') ?>"
                                    data-orden-produccion="<?= esc($item['ordenFolio'] ?? 'N/A', 'attr') ?>"
                                    data-punto-inspeccion="<?= esc($item['puntoInspeccionId'] ?? 'N/A', 'attr') ?>"
                                    data-fecha="<?= !empty($item['fecha']) ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A' ?>"
                                    data-resultado="<?= strtolower($item['resultado'] ?? 'pendiente') ?>"
                                    data-observaciones="<?= esc($item['observaciones'] ?? '', 'attr') ?>"
                                    data-defectos="<?= esc(json_encode($item['defectos'] ?? []), 'attr') ?>"
                                    data-reproceso-id="<?= $item['reprocesoId'] ?? '' ?>"
                                    data-accion-reproceso="<?= esc($item['accion'] ?? '', 'attr') ?>"
                                    data-cantidad-reproceso="<?= $item['cantidad'] ?? '0' ?>"
                                    data-fecha-reproceso="<?= $item['fechaReproceso'] ?? '' ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Lista oculta de Puntos de Inspección para mostrar en Swal -->
<div id="_listaPuntosHidden" class="d-none">
    <ul class="list-group text-start">
        <?php if (!empty($puntosInspeccion)): ?>
            <?php foreach ($puntosInspeccion as $p): ?>
                <li class="list-group-item d-flex align-items-center">
                    <span class="badge bg-secondary me-2">#<?= esc($p['id']) ?></span>
                    <span><?= esc($p['tipo']) ?></span>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="list-group-item">No hay puntos configurados</li>
        <?php endif; ?>
    </ul>
</div>

<!-- Modal de Evaluación -->
<div class="modal fade" id="evaluarModal" tabindex="-1" aria-labelledby="evaluarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="evaluarModalLabel">Evaluar Inspección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <?= form_open('inspeccion/evaluar', ['id' => 'formEvaluar']) ?>
            <div class="modal-body">
                <input type="hidden" name="id" id="inspeccion_id">

                <!-- Encabezado de la inspección -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>No. Inspección:</strong>
                            <div id="numero_inspeccion_display" class="fw-bold"></div>
                        </div>
                        <div class="mb-2">
                            <strong>Orden de Producción:</strong>
                            <div id="orden_produccion_display"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Punto de Inspección:</strong>
                            <div id="punto_inspeccion_display"></div>
                        </div>
                        <div class="mb-2">
                            <strong>Fecha:</strong>
                            <div id="fecha_display"></div>
                        </div>
                    </div>
                </div>

                <!-- Resultado de la evaluación -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Resultado de la Inspección</label>
                    <select class="form-select" name="resultado" id="resultado_select" required>
                        <option value="">Seleccione un resultado</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>

                <!-- Observaciones -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Observaciones</label>
                    <textarea class="form-control" name="observaciones" id="observaciones_text" rows="3"
                        placeholder="Ingrese las observaciones de la inspección"></textarea>
                </div>

                <!-- Sección de Evidencia Fotográfica (solo visible cuando resultado es "Rechazado") -->
                <div class="mb-3" id="evidencia-section" style="display: none;">
                    <label class="form-label fw-bold">Evidencia Fotográfica <span class="text-danger">*</span></label>
                    <div class="card">
                        <div class="card-body">
                            <!-- Evidencia existente (si hay) -->
                            <div id="evidenciaExistente" class="mb-3" style="display: none;">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span>Esta inspección ya tiene una foto de evidencia</span>
                                </div>
                                <div class="text-center mb-3">
                                    <img id="evidenciaExistenteImg" src="" alt="Evidencia Anterior"
                                        class="img-fluid rounded border" style="max-height: 300px;">
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Si captura una nueva foto, reemplazará la evidencia anterior
                                </div>
                            </div>

                            <!-- Botón para capturar foto -->
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" class="btn btn-primary" id="btnCapturarFoto">
                                    <i class="fas fa-camera me-2"></i><span id="btnCapturarTexto">Capturar Foto con
                                        Cámara</span>
                                </button>
                            </div>

                            <!-- Preview de la foto capturada -->
                            <div id="fotoPreview" class="text-center" style="display: none;">
                                <img id="fotoPreviewImg" src="" alt="Evidencia" class="img-fluid rounded border"
                                    style="max-height: 300px;">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="btnEliminarFoto">
                                        <i class="fas fa-trash me-1"></i>Eliminar foto
                                    </button>
                                </div>
                            </div>

                            <!-- Input oculto para guardar la foto en base64 -->
                            <input type="hidden" name="evidencia" id="evidenciaInput">

                            <!-- Video para captura (oculto) -->
                            <video id="videoCaptura" autoplay playsinline
                                style="display: none; width: 100%; max-width: 400px; margin: 0 auto;"></video>
                            <canvas id="canvasCaptura" style="display: none;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Sección de Reproceso -->
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="toggleReproceso">
                    <label class="form-check-label" for="toggleReproceso">Registrar reproceso</label>
                    <input type="hidden" name="con_reproceso" id="conReproceso" value="0">
                </div>
                <div class="mb-3 reproceso-section" style="display:none;">
                    <h5 class="fw-bold mb-3">Información de Reproceso</h5>
                    <input type="hidden" name="reproceso_id" id="reprocesoId">
                    <div class="mb-3">
                        <label for="accionReproceso" class="form-label">Acción de Reproceso</label>
                        <textarea class="form-control" id="accionReproceso" name="accion_reproceso" rows="3"
                            placeholder="Describa las acciones necesarias para el reproceso"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cantidadReproceso" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidadReproceso" name="cantidad_reproceso"
                                min="0" value="">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fechaReproceso" class="form-label">Fecha de Reproceso</label>
                            <input type="date" class="form-control" id="fechaReproceso" name="fecha_reproceso">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="defectos" id="defectosJson" value="[]">
                <input type="hidden" name="fecha" id="fecha_inspeccion" value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Botones en el footer correcto -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal de Ver Detalles (Solo Lectura) -->
<div class="modal fade" id="verDetallesModal" tabindex="-1" aria-labelledby="verDetallesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="verDetallesModalLabel">
                    <i class="fas fa-eye me-2"></i>Detalles de Inspección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Información básica -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong class="text-muted">No. Inspección:</strong>
                            <div id="ver_numero_inspeccion" class="fw-bold fs-5"></div>
                        </div>
                        <div class="mb-3">
                            <strong class="text-muted">Orden de Producción:</strong>
                            <div id="ver_orden_produccion"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong class="text-muted">Punto de Inspección:</strong>
                            <div id="ver_punto_inspeccion"></div>
                        </div>
                        <div class="mb-3">
                            <strong class="text-muted">Fecha:</strong>
                            <div id="ver_fecha"></div>
                        </div>
                    </div>
                </div>

                <!-- Resultado -->
                <div class="mb-3">
                    <strong class="text-muted">Resultado:</strong>
                    <div id="ver_resultado"></div>
                </div>

                <!-- Observaciones -->
                <div class="mb-3">
                    <strong class="text-muted">Observaciones:</strong>
                    <div id="ver_observaciones" class="border rounded p-2 bg-light"></div>
                </div>

                <!-- Evidencia Fotográfica -->
                <div class="mb-3" id="ver_evidencia_section" style="display: none;">
                    <strong class="text-muted">Evidencia Fotográfica:</strong>
                    <div class="card mt-2">
                        <div class="card-body text-center">
                            <img id="ver_evidencia_img" src="" alt="Evidencia" class="img-fluid rounded border"
                                style="max-height: 400px;">
                        </div>
                    </div>
                </div>

                <!-- Información de Reproceso -->
                <div id="ver_reproceso_section" style="display: none;">
                    <hr>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fas fa-tools me-2"></i>Información de Reproceso
                    </h6>
                    <div class="mb-3">
                        <strong class="text-muted">Acción de Reproceso:</strong>
                        <div id="ver_accion_reproceso" class="border rounded p-2 bg-light"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Cantidad:</strong>
                            <div id="ver_cantidad_reproceso"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Fecha de Reproceso:</strong>
                            <div id="ver_fecha_reproceso"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- JS Bootstrap + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Helpers de exportación (Buttons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        const langES = {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No hay inspecciones registradas.",

            "sInfo": "Mostrando _START_ a _END_ de _TOTAL_",
            "sInfoEmpty": "Mostrando 0 a 0 de 0",
            "sInfoFiltered": "(filtrado de _MAX_)",
            "sSearch": "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "buttons": { "copy": "Copiar", "colvis": "Columnas" }
        };
        const fecha = new Date().toISOString().slice(0, 10);
        const fileName = 'inspecciones_' + fecha;

        var table = $('#tablaInspeccion').DataTable({
            language: langES,
            columnDefs: [{
                targets: -1,
                orderable: false,
                searchable: false
            }],
            dom:
                "<'row mb-3'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copy',
                    className: 'btn',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'csv',
                    text: 'CSV',
                    className: 'btn',
                    filename: fileName,
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'excel',
                    text: 'Excel',
                    className: 'btn',
                    filename: fileName,
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    className: 'btn',
                    filename: fileName,
                    title: 'Inspecciones - ' + fecha,
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    className: 'btn',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            order: [[5, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
        });

        // Obtener maquiladoraId de la sesión
        const maquiladoraId = <?= json_encode(session()->get('maquiladora_id') ?? null) ?>;

        // Botón Puntos de Inspección (CRUD)
        $(document).on('click', '#btnPuntosInspeccion', function () {
            const cargarYMostrar = () => {
                $.getJSON('<?= base_url('modulo3/inspeccion/puntos/json') ?>', function (resp) {
                    if (!resp || resp.success !== true) {
                        Swal.fire({ icon: 'error', title: 'Error', text: (resp && resp.message) ? resp.message : 'No se pudo cargar el catálogo' });
                        return;
                    }
                    const rows = resp.data || [];

                    // DEBUG: Ver datos recibidos
                    console.log('maquiladoraId de sesión:', maquiladoraId);
                    console.log('Puntos recibidos del backend:', rows);

                    // Filtrar para mostrar SOLO puntos de la maquiladora actual (sin generales)
                    const rowsFiltrados = rows.filter(r => {
                        const puntoMaqId = r.maquiladoraID;
                        const mostrar = puntoMaqId && puntoMaqId == maquiladoraId;
                        console.log(`Punto #${r.id}: maquiladoraID=${puntoMaqId}, ¿Mostrar? ${mostrar}`);
                        return mostrar;
                    });

                    console.log('Puntos después de filtrar:', rowsFiltrados);

                    const lista = rowsFiltrados.map(r => `
                            <tr data-id="${r.id}" data-maquiladora="${r.maquiladoraID || ''}">
                                <td style="width:70px" class="text-muted">#${r.id}</td>
                                <td><input type="text" class="form-control form-control-sm ip-tipo" value="${(r.tipo || '').replace(/"/g, '&quot;')}"/></td>
                                <td><input type="text" class="form-control form-control-sm ip-criterio" value="${(r.criterio || '').replace(/"/g, '&quot;')}"/></td>
                                <td class="text-muted small">${r.maquiladoraNombre || 'General'}</td>
                                <td class="text-nowrap" style="width:140px">
                                    <button class="btn btn-sm btn-success me-1 btn-pi-guardar" title="Guardar"><i class="fas fa-save"></i></button>
                                    <button class="btn btn-sm btn-danger btn-pi-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`).join('');

                    const html = `
                        <div class="mb-2 text-start">
                          <div class="bg-light border rounded p-2 mb-3">
                            <div class="row g-2 align-items-center">
                              <div class="col-12 col-md-4">
                                <div class="input-group input-group-sm">
                                  <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                  <input id="pi-nuevo-tipo" type="text" class="form-control" placeholder="Tipo (requerido)"/>
                                </div>
                              </div>
                              <div class="col-12 col-md-6">
                                <div class="input-group input-group-sm">
                                  <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                  <input id="pi-nuevo-criterio" type="text" class="form-control" placeholder="Criterio (opcional)"/>
                                </div>
                              </div>
                              <div class="col-12 col-md-2 text-md-end">
                                <button id="pi-agregar" class="btn btn-primary btn-sm w-100"><i class="fas fa-plus me-1"></i>Agregar</button>
                              </div>
                            </div>
                          </div>
                          <div class="table-responsive" style="max-height:50vh;">
                            <table class="table table-sm table-hover align-middle mb-0">
                              <thead class="table-secondary"><tr><th>ID</th><th>Tipo</th><th>Criterio</th><th>Maquiladora</th><th>Acciones</th></tr></thead>
                              <tbody id="pi-tbody">${lista}</tbody>
                            </table>
                          </div>
                        </div>`;

                    Swal.fire({
                        title: 'Puntos de Inspección',
                        html: html,
                        width: 900,
                        showConfirmButton: false,
                        showCloseButton: true,
                        didOpen: () => {
                            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                            // Agregar
                            $('#pi-agregar').on('click', function () {
                                const $btn = $(this);
                                if ($btn.prop('disabled')) return;
                                const tipo = ($('#pi-nuevo-tipo').val() || '').trim();
                                const criterio = ($('#pi-nuevo-criterio').val() || '').trim();
                                if (!tipo) { Swal.fire({ icon: 'warning', title: 'Tipo requerido' }); return; }
                                const originalHtml = $btn.html();
                                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Agregando...');
                                $.ajax({
                                    url: '<?= base_url('modulo3/inspeccion/puntos/crear') ?>',
                                    method: 'POST',
                                    data: {
                                        tipo,
                                        criterio,
                                        maquiladoraID: maquiladoraId,
                                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                                    },
                                    dataType: 'json'
                                }).done(function (r) {
                                    if (r && r.success && r.data) {
                                        // Agregar la nueva fila sin cerrar el Swal
                                        const d = r.data;
                                        const rowHtml = `
                                              <tr data-id="${d.id}" data-maquiladora="${d.maquiladoraID || ''}">
                                                <td style="width:70px" class="text-muted">#${d.id}</td>
                                                <td><input type="text" class="form-control form-control-sm ip-tipo" value="${(d.tipo || '').replace(/"/g, '&quot;')}"/></td>
                                                <td><input type="text" class="form-control form-control-sm ip-criterio" value="${(d.criterio || '').replace(/"/g, '&quot;')}"/></td>
                                                <td class="text-muted small">${d.maquiladoraNombre || 'General'}</td>
                                                <td class="text-nowrap" style="width:140px">
                                                  <button class="btn btn-sm btn-success me-1 btn-pi-guardar" title="Guardar"><i class="fas fa-save"></i></button>
                                                  <button class="btn btn-sm btn-danger btn-pi-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </td>
                                              </tr>`;
                                        $('#pi-tbody').append(rowHtml);
                                        $('#pi-nuevo-tipo').val('');
                                        $('#pi-nuevo-criterio').val('');
                                        Toast.fire({ icon: 'success', title: 'Agregado' });
                                    } else {
                                        Toast.fire({ icon: 'error', title: (r && r.message) || 'No se pudo crear' });
                                    }
                                }).fail(function (xhr) {
                                    let msg = 'Error'; try { if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; } } catch (e) { }
                                    Toast.fire({ icon: 'error', title: msg || 'No se pudo crear' });
                                }).always(function () {
                                    $btn.prop('disabled', false).html(originalHtml);
                                });
                            });

                            // Guardar (editar)
                            $(document).off('click', '.btn-pi-guardar').on('click', '.btn-pi-guardar', function () {
                                const $btn = $(this);
                                if ($btn.prop('disabled')) return;
                                const $tr = $btn.closest('tr');
                                const id = parseInt($tr.data('id'), 10) || 0;
                                const tipo = ($tr.find('.ip-tipo').val() || '').trim();
                                const criterio = ($tr.find('.ip-criterio').val() || '').trim();
                                const maqID = $tr.data('maquiladora') || null;
                                if (!id || !tipo) { Swal.fire({ icon: 'warning', title: 'Datos inválidos' }); return; }
                                const originalHtml = $btn.html();
                                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                                $.ajax({
                                    url: '<?= base_url('modulo3/inspeccion/puntos/editar') ?>',
                                    method: 'POST',
                                    data: {
                                        id,
                                        tipo,
                                        criterio,
                                        maquiladoraID: maqID,
                                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                                    },
                                    dataType: 'json'
                                }).done(function (r) {
                                    if (r && r.success) { Toast.fire({ icon: 'success', title: 'Guardado' }); }
                                    else { Toast.fire({ icon: 'error', title: (r && r.message) || 'No se pudo guardar' }); }
                                }).fail(function (xhr) {
                                    let msg = 'Error'; try { if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; } } catch (e) { }
                                    Toast.fire({ icon: 'error', title: msg || 'No se pudo guardar' });
                                }).always(function () {
                                    $btn.prop('disabled', false).html(originalHtml);
                                });
                            });

                            // Eliminar
                            $(document).off('click', '.btn-pi-eliminar').on('click', '.btn-pi-eliminar', function () {
                                const $btn = $(this);
                                const $tr = $btn.closest('tr');
                                const id = parseInt($tr.data('id'), 10) || 0;
                                if (!id) return;
                                // Confirmación inline
                                const $cell = $btn.parent();
                                const original = $cell.html();
                                $cell.html(`
                                      <div class="d-inline-flex gap-2">
                                        <button class="btn btn-warning btn-sm btn-pi-cancelar-del"><i class="fas fa-times"></i></button>
                                        <button class="btn btn-danger btn-sm btn-pi-confirmar-del"><i class="fas fa-check"></i></button>
                                      </div>`);
                                $cell.off('click', '.btn-pi-cancelar-del').on('click', '.btn-pi-cancelar-del', function () {
                                    $cell.html(original);
                                });
                                $cell.off('click', '.btn-pi-confirmar-del').on('click', '.btn-pi-confirmar-del', function () {
                                    const $confirmBtn = $(this);
                                    if ($confirmBtn.prop('disabled')) return;
                                    const backup = $cell.html();
                                    $cell.html('<span class="spinner-border spinner-border-sm"></span>');
                                    $tr.find('input').prop('disabled', true);
                                    $.ajax({
                                        url: '<?= base_url('modulo3/inspeccion/puntos/eliminar') ?>',
                                        method: 'POST',
                                        data: { id, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                                        dataType: 'json'
                                    }).done(function (r) {
                                        if (r && r.success) { $tr.remove(); Toast.fire({ icon: 'success', title: 'Eliminado' }); }
                                        else { $cell.html(backup); $tr.find('input').prop('disabled', false); Toast.fire({ icon: 'error', title: (r && r.message) || 'No se pudo eliminar' }); }
                                    }).fail(function (xhr) {
                                        let msg = 'Error'; try { if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; } } catch (e) { }
                                        $cell.html(backup); $tr.find('input').prop('disabled', false);
                                        Toast.fire({ icon: 'error', title: msg || 'No se pudo eliminar' });
                                    });
                                });
                            });
                        }
                    });
                }).fail(function (xhr) {
                    let msg = 'Error'; try { if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; } } catch (e) { }
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                });
            };
            cargarYMostrar();
        });

        // Fix for Bootstrap 5 modal compatibility
        table.on('draw', function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Evaluar button functionality
        $('.btn-evaluar').on('click', function () {
            const id = $(this).data('id');
            const inspeccion = $(this).data('inspeccion');

            $('#inspeccion_id').val(id);
            $('#numero_inspeccion').val(inspeccion);

            // Reset form
            $('#formEvaluar')[0].reset();
            $('#defectosContainer').hide();
            $('#tablaDefectos').empty();
            $('#defectosJson').val('[]');
        });

        // Toggle reproceso section
        $('#toggleReproceso').on('change', function () {
            const on = $(this).is(':checked');
            $('#conReproceso').val(on ? '1' : '0');
            if (on) { $('.reproceso-section').show(); $('#defectosContainer').show(); }
            else { $('.reproceso-section').hide(); $('#defectosContainer').hide(); }
        });

        // Agregar defecto functionality
        $('#agregarDefecto').on('click', function () {
            const tipo = $('#tipo_defecto').val();
            const descripcion = $('textarea[name="descripcion_defecto"]').val();
            const cantidad = $('input[name="cantidad_defectos"]').val();
            const accion = $('textarea[name="accion_correctiva"]').val();

            if (!tipo || !descripcion) {
                Swal.fire({icon:'warning', title:'Atención', text:'Por favor complete el tipo y descripción del defecto'});
                return;
            }

            const defectoId = Date.now();
            const newRow = `
                <tr id="defecto-${defectoId}">
                    <td>${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</td>
                    <td>${descripcion}</td>
                    <td>${cantidad}</td>
                    <td>${accion || 'N/A'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-defecto" data-id="${defectoId}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#tablaDefectos').append(newRow);

            // Update JSON hidden field
            updateDefectosJson();

            // Clear form fields
            $('textarea[name="descripcion_defecto"]').val('');
            $('textarea[name="accion_correctiva"]').val('');
            $('input[name="cantidad_defectos"]').val('1');
        });

        // Eliminar defecto
        $(document).on('click', '.btn-eliminar-defecto', function () {
            const defectoId = $(this).data('id');
            $(`#defecto-${defectoId}`).remove();
            updateDefectosJson();
        });

        function updateDefectosJson() {
            const defectos = [];
            $('#tablaDefectos tr').each(function () {
                const cells = $(this).find('td');
                if (cells.length > 0) {
                    defectos.push({
                        tipo: $(cells[0]).text(),
                        descripcion: $(cells[1]).text(),
                        cantidad: $(cells[2]).text(),
                        accion_correctiva: $(cells[3]).text()
                    });
                }
            });
            $('#defectosJson').val(JSON.stringify(defectos));
        }

        // Función para formatear la fecha para el input date
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Manejar la carga de datos en el modal de evaluación
        $(document).on('click', '.btn-evaluar', function (e) {
            e.preventDefault();

            // Obtener el botón que disparó el evento
            const $boton = $(this);

            // Obtener los datos usando attr('data-*') en lugar de data()
            const inspeccionId = $boton.attr('data-id');
            const numeroInspeccion = $boton.attr('data-inspeccion');
            const ordenProduccion = $boton.attr('data-orden-produccion');
            const puntoInspeccion = $boton.attr('data-punto-inspeccion');
            const fecha = $boton.attr('data-fecha');
            const resultado = $boton.attr('data-resultado');
            const observaciones = $boton.attr('data-observaciones');

            // Mostrar los datos en la consola para depuración
            console.log('Datos obtenidos del botón:', {
                inspeccionId,
                numeroInspeccion,
                ordenProduccion,
                puntoInspeccion,
                fecha,
                resultado,
                observaciones
            });

            // Llenar el formulario con los datos básicos
            if (inspeccionId) $('#inspeccion_id').val(inspeccionId);
            if (numeroInspeccion) $('#numero_inspeccion_display').text(numeroInspeccion);
            if (ordenProduccion) $('#orden_produccion_display').text(ordenProduccion);
            // Mostrar texto del punto de inspección desde el select de la fila
            const puntoTexto = $boton.closest('tr').find('.punto-inspeccion-select option:selected').text() || (puntoInspeccion || 'N/A');
            $('#punto_inspeccion_display').text(puntoTexto);
            if (fecha) $('#fecha_display').text(fecha);
            if (resultado) $('#resultado_select').val(resultado).trigger('change');
            if (observaciones) $('#observaciones_text').val(observaciones);

            // Limpiar sección de evidencia
            $('#evidencia-section').hide();
            $('#evidenciaInput').val('');
            $('#fotoPreview').hide();
            $('#fotoPreviewImg').attr('src', '');
            stopCamera();

            // Manejar datos de reproceso
            const reprocesoId = $boton.attr('data-reproceso-id');
            const accionReproceso = $boton.attr('data-accion-reproceso') || '';
            const cantidadReproceso = $boton.attr('data-cantidad-reproceso') || '0';
            const fechaReproceso = $boton.attr('data-fecha-reproceso');

            // Siempre resetear el checkbox y ocultar la sección de reproceso al abrir el modal
            $('#toggleReproceso').prop('checked', false);
            $('#conReproceso').val('0');
            $('.reproceso-section').hide();

            // Guardar los datos de reproceso existentes en campos ocultos (si existen)
            // pero NO mostrar la sección automáticamente
            if (reprocesoId) {
                $('#reprocesoId').val(reprocesoId);
                $('#accionReproceso').val(accionReproceso);
                $('#cantidadReproceso').val(cantidadReproceso);

                // Establecer la fecha de reproceso si existe
                const fechaHoy = new Date().toISOString().split('T')[0];
                $('#fechaReproceso').val(fechaReproceso ? formatDateForInput(fechaReproceso) : fechaHoy);
            } else {
                // Limpiar campos de reproceso
                $('#reprocesoId').val('');
                $('#accionReproceso').val('');
                $('#cantidadReproceso').val('');
                $('#fechaReproceso').val('');
            }

            // Manejar defectos
            try {
                const defectosStr = $boton.attr('data-defectos');
                if (defectosStr) {
                    const defectosData = JSON.parse(defectosStr);
                    console.log('Datos de defectos:', defectosData);
                    // Aquí puedes manejar los defectos si es necesario
                }
            } catch (e) {
                console.error('Error al procesar defectos:', e);
            }

            // Cargar evidencia existente si hay
            $('#evidenciaExistente').hide();
            $('#evidenciaExistenteImg').attr('src', '');

            if (inspeccionId) {
                console.log('Cargando evidencia para inspección ID:', inspeccionId);
                const evidenciaUrl = '<?= base_url('modulo3/inspeccion/evidencia') ?>/' + inspeccionId;
                console.log('URL de evidencia:', evidenciaUrl);

                $.ajax({
                    url: evidenciaUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (response) {
                    console.log('Respuesta de evidencia:', response);
                    if (response.success && response.evidencia) {
                        console.log('Evidencia encontrada, mostrando imagen');
                        // Mostrar la evidencia existente
                        $('#evidenciaExistenteImg').attr('src', response.evidencia);
                        $('#evidenciaExistente').show();
                        $('#btnCapturarTexto').text('Capturar Nueva Foto (Reemplazar)');

                        // Pre-llenar el input oculto con la evidencia existente
                        // para que no sea obligatorio capturar una nueva
                        $('#evidenciaInput').val(response.evidencia);

                        // Mostrar la sección de evidencia si hay foto, independientemente del resultado
                        $('#evidencia-section').show();
                    } else {
                        console.log('No hay evidencia en la respuesta');
                        $('#btnCapturarTexto').text('Capturar Foto con Cámara');
                    }
                }).fail(function (xhr, status, error) {
                    console.error('Error al cargar evidencia:', status, error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    $('#btnCapturarTexto').text('Capturar Foto con Cámara');
                });
            }

            // Limpiar y cargar defectos si existen
            defectos = [];
            try {
                const defectosStr = $boton.attr('data-defectos');
                if (defectosStr) {
                    defectos = JSON.parse(defectosStr);
                    console.log('Datos de defectos:', defectos);
                    actualizarTablaDefectos();
                } else {
                    $('#tablaDefectos').empty();
                }
            } catch (e) {
                console.error('Error al procesar defectos:', e);
                $('#tablaDefectos').empty();
            }

            // Ajustar action del formulario al endpoint correcto con ID
            if (inspeccionId) {
                $('#formEvaluar').attr('action', '<?= base_url('modulo3/inspeccion/evaluar/guardar') ?>/' + inspeccionId);
            }

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('evaluarModal'));
            modal.show();
        });

        // Variables para la cámara
        let stream = null;

        // Manejar el evento de cambio en el select de resultado
        $('#resultado_select').on('change', function () {
            const resultado = $(this).val();

            if (resultado === 'rechazado') {
                $('#defectosContainer').show();
                $('#evidencia-section').show();
            } else {
                $('#defectosContainer').hide();
                $('#evidencia-section').hide();
                // Limpiar la foto si cambia de rechazado a otro estado
                $('#evidenciaInput').val('');
                $('#fotoPreview').hide();
                $('#fotoPreviewImg').attr('src', '');
                stopCamera();
            }
        });

        // Función para detener la cámara
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            $('#videoCaptura').hide();
            $('#btnCapturarFoto').html('<i class="fas fa-camera me-2"></i>Capturar Foto con Cámara');
        }

        // Botón para capturar foto
        $('#btnCapturarFoto').on('click', function () {
            const $video = $('#videoCaptura');
            const $btn = $(this);

            if (stream) {
                // Si la cámara está activa, capturar la foto
                const canvas = document.getElementById('canvasCaptura');
                const video = document.getElementById('videoCaptura');

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convertir a base64
                const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);

                // Guardar en el input oculto
                $('#evidenciaInput').val(fotoBase64);

                // Mostrar preview
                $('#fotoPreviewImg').attr('src', fotoBase64);
                $('#fotoPreview').show();

                // Detener la cámara
                stopCamera();
            } else {
                // Activar la cámara
                navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment' // Usar cámara trasera en móviles
                    }
                })
                    .then(function (mediaStream) {
                        stream = mediaStream;
                        const video = document.getElementById('videoCaptura');
                        video.srcObject = stream;
                        $video.show();
                        $btn.html('<i class="fas fa-camera me-2"></i>Tomar Foto');
                    })
                    .catch(function (err) {
                        console.error('Error al acceder a la cámara:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo acceder a la cámara. Verifica los permisos.'
                        });
                    });
            }
        });

        // Botón para eliminar foto
        $('#btnEliminarFoto').on('click', function () {
            $('#evidenciaInput').val('');
            $('#fotoPreview').hide();
            $('#fotoPreviewImg').attr('src', '');
            stopCamera();
        });

        // Validar que se haya capturado foto cuando es rechazado
        $('#formEvaluar').on('submit', function (e) {
            const resultado = $('#resultado_select').val();
            const evidencia = $('#evidenciaInput').val();

            if (resultado === 'rechazado' && !evidencia) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Evidencia requerida',
                    text: 'Debe capturar una foto como evidencia cuando el resultado es Rechazado.'
                });
                return false;
            }
        });

        // Botón Ver Detalles (Solo Lectura)
        $(document).on('click', '.btn-ver-detalles', function (e) {
            e.preventDefault();

            const $boton = $(this);
            const inspeccionId = $boton.attr('data-id');
            const numeroInspeccion = $boton.attr('data-inspeccion');
            const ordenProduccion = $boton.attr('data-orden-produccion');
            const puntoInspeccion = $boton.attr('data-punto-inspeccion');
            const fecha = $boton.attr('data-fecha');
            const resultado = $boton.attr('data-resultado');
            const observaciones = $boton.attr('data-observaciones');
            const reprocesoId = $boton.attr('data-reproceso-id');
            const accionReproceso = $boton.attr('data-accion-reproceso') || '';
            const cantidadReproceso = $boton.attr('data-cantidad-reproceso') || '0';
            const fechaReproceso = $boton.attr('data-fecha-reproceso');

            // Llenar información básica
            $('#ver_numero_inspeccion').text(numeroInspeccion || 'N/A');
            $('#ver_orden_produccion').text(ordenProduccion || 'N/A');

            // Obtener el texto del punto de inspección
            const puntoTexto = $boton.closest('tr').find('.punto-inspeccion-select option:selected').text() || 'N/A';
            $('#ver_punto_inspeccion').text(puntoTexto);
            $('#ver_fecha').text(fecha || 'N/A');

            // Mostrar resultado con badge
            let badgeClass = 'secondary';
            let resultadoTexto = resultado || 'pendiente';
            if (resultadoTexto === 'aprobado') badgeClass = 'success';
            else if (resultadoTexto === 'rechazado') badgeClass = 'danger';
            else if (resultadoTexto === 'pendiente') badgeClass = 'warning';

            $('#ver_resultado').html(`<span class="badge bg-${badgeClass} fs-6">${resultadoTexto.charAt(0).toUpperCase() + resultadoTexto.slice(1)}</span>`);

            // Observaciones
            $('#ver_observaciones').text(observaciones || 'Sin observaciones');

            // Ocultar evidencia y reproceso por defecto
            $('#ver_evidencia_section').hide();
            $('#ver_reproceso_section').hide();

            // Cargar evidencia fotográfica desde el servidor si existe
            if (inspeccionId) {
                $.ajax({
                    url: '<?= base_url('modulo3/inspeccion/evidencia') ?>/' + inspeccionId,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (response) {
                    if (response.success && response.evidencia) {
                        $('#ver_evidencia_img').attr('src', response.evidencia);
                        $('#ver_evidencia_section').show();
                    }
                }).fail(function () {
                    console.log('No hay evidencia fotográfica para esta inspección');
                });
            }

            // Mostrar información de reproceso si existe
            if (reprocesoId) {
                $('#ver_accion_reproceso').text(accionReproceso || 'Sin acción definida');
                $('#ver_cantidad_reproceso').text(cantidadReproceso);
                $('#ver_fecha_reproceso').text(fechaReproceso ? formatDateForInput(fechaReproceso) : 'N/A');
                $('#ver_reproceso_section').show();
            }

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('verDetallesModal'));
            modal.show();
        });

        // Inicializar el array de defectos
        let defectos = [];

        // Función para actualizar la tabla de defectos
        function actualizarTablaDefectos() {
            const tbody = $('#tablaDefectos');
            tbody.empty();

            defectos.forEach((defecto, index) => {
                const tr = $(`
                <tr>
                    <td>${defecto.tipo ? defecto.tipo.charAt(0).toUpperCase() + defecto.tipo.slice(1) : ''}</td>
                    <td>${defecto.descripcion || ''}</td>
                    <td class="text-center">${defecto.cantidad || ''}</td>
                    <td>${defecto.accion_correctiva || defecto.accion || ''}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-defecto"
                            data-index="${index}" title="Eliminar defecto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);

                tbody.append(tr);
            });

            // Actualizar el campo oculto con el JSON de los defectos
            $('#defectosJson').val(JSON.stringify(defectos));
        }

        // Agregar un nuevo defecto
        $('#agregarDefecto').on('click', function () {
            const tipo = $('#tipo_defecto').val();
            const descripcion = $('textarea[name="descripcion_defecto"]').val();
            const cantidad = $('input[name="cantidad_defectos"]').val();
            const accion = $('input[name="accion_correctiva"]').val();

            if (tipo && descripcion && cantidad) {
                const defecto = {
                    tipo: tipo,
                    descripcion: descripcion,
                    cantidad: cantidad,
                    accion_correctiva: accion || ''
                };

                defectos.push(defecto);
                actualizarTablaDefectos();

                // Limpiar campos
                $('#tipo_defecto').val('');
                $('textarea[name="descripcion_defecto"]').val('');
                $('input[name="cantidad_defectos"]').val('1');
                $('input[name="accion_correctiva"]').val('');

                // Enfocar el primer campo para facilitar la entrada rápida
                $('#tipo_defecto').focus();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor complete los campos obligatorios: Tipo de Defecto, Descripción y Cantidad',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });

        // Eliminar defecto
        $(document).on('click', '.btn-eliminar-defecto', function () {
            const index = $(this).data('index');
            defectos.splice(index, 1);
            actualizarTablaDefectos();
        });

        // Limpiar el modal cuando se cierre
        $('#evaluarModal').on('hidden.bs.modal', function () {
            defectos.length = 0; // Vaciar el array de defectos
            $('#tablaDefectos').empty();
            $('#defectosJson').val('[]');
        }); // <-- Faltaba este cierre

        // Manejar el envío del formulario
        $('#formEvaluar').on('submit', function (e) {
            e.preventDefault();

            // Mostrar indicador de carga
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

            // Normalizar cantidad de reproceso si toggle apagado
            if ($('#conReproceso').val() !== '1') {
                $('#accionReproceso').val('');
                $('#cantidadReproceso').val('0');
                $('#fechaReproceso').val('');
                $('#defectosJson').val('[]');
            } else {
                if ($('#cantidadReproceso').val() === '') { $('#cantidadReproceso').val('0'); }
            }

            // Enviar el formulario vía AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataType: 'json',
                success: function (response, textStatus, xhr) {
                    try {
                        if (response && response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message || 'La inspección se ha guardado correctamente',
                                confirmButtonColor: '#198754',
                                allowOutsideClick: false
                            }).then(() => location.reload());
                            return;
                        }
                    } catch (e) { /* ignore */ }
                    // Si no viene JSON válido pero el status es 200, asumir éxito
                    if (xhr && xhr.status >= 200 && xhr.status < 300) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'La inspección se ha guardado correctamente',
                            confirmButtonColor: '#198754',
                            allowOutsideClick: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la inspección' });
                    }
                },
                error: function (xhr, status, errorThrown) {
                    // Si hubo parsererror pero el servidor devolvió 200/201, tratar como éxito
                    if ((status === 'parsererror') && xhr && xhr.status >= 200 && xhr.status < 300) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'La inspección se ha guardado correctamente',
                            confirmButtonColor: '#198754',
                            allowOutsideClick: false
                        }).then(() => location.reload());
                        return;
                    }
                    let errorMessage = 'Error al procesar la solicitud';
                    if (xhr.responseJSON && xhr.responseJSON.message) { errorMessage = xhr.responseJSON.message; }
                    Swal.fire({ icon: 'error', title: 'Error', text: errorMessage, confirmButtonColor: '#dc3545' });
                },
                complete: function () {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        // Guardar valor previo en focus
        $(document).on('focusin', '.punto-inspeccion-select', function () {
            $(this).data('prev', $(this).val());
        });

        // Manejar el cambio de punto de inspección con confirmación
        $(document).on('change', '.punto-inspeccion-select', function () {
            const select = $(this);
            const prevVal = select.data('prev');
            const inspeccionId = select.data('inspeccion-id');
            const nuevoPuntoId = select.val();

            Swal.fire({
                title: '¿Cambiar punto de inspección?',
                text: 'Se actualizará el punto de inspección de esta evaluación.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then(function (res) {
                if (!res.isConfirmed) {
                    // Revertir selección
                    select.val(prevVal);
                    return;
                }

                select.prop('disabled', true).addClass('opacity-75');

                $.ajax({
                    url: '<?= base_url('modulo3/inspeccion/actualizar-punto') ?>',
                    method: 'POST',
                    data: {
                        id: inspeccionId,
                        puntoInspeccionId: nuevoPuntoId,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Actualizar etiqueta del seleccionado (por si cambia texto)
                            select.find('option:selected').text(response.punto_tipo);
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: 'Punto de inspección actualizado correctamente.',
                                icon: 'success'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo actualizar',
                                text: response.message || 'Error desconocido'
                            });
                            // Revertir al valor previo
                            select.val(prevVal);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error en la petición AJAX:', error, xhr.responseText);
                        let msg = 'Error al conectar con el servidor';
                        try { if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; } } catch (e) { }
                        Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: msg });
                        select.val(prevVal);
                    },
                    complete: function () {
                        select.prop('disabled', false).removeClass('opacity-75');
                    }
                });
            });
        });
    });
</script>

<!-- Toast para notificaciones -->
<div class="toast position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true"
    data-bs-delay="3000">
    <div class="toast-header">
        <strong class="me-auto">Sistema</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body"></div>
</div>

<?= $this->endSection() ?>