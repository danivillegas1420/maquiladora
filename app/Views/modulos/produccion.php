<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    if (!function_exists('current_role_name')) { helper('auth'); }
    $__roleName = function_exists('current_role_name') ? (string)current_role_name() : '';
?>
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Iniciar Producción de Pedidos</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>

    <!-- Secciones con pestañas: Pendientes / Finalizados -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <style>
                .timer-badge{ font-size:1.6rem; font-weight:600; padding:.35rem .8rem; min-width:150px; text-align:center; }
                .status-badge{ font-size:1.25rem; font-weight:700; padding:.4rem .7rem; min-width:110px; text-align:center; }
                @media (max-width: 768px) {
                    .container { padding: 0.5rem; }
                    .card { border-radius: 0; }
                    .card-body { padding: 0.75rem; }
                    .d-flex.justify-content-between.align-items-center {
                        flex-direction: column;
                        gap: 0.5rem;
                        align-items: flex-start !important;
                    }
                    .d-flex.justify-content-between.align-items-center > *:last-child {
                        align-self: flex-end;
                    }
                    .border.rounded.p-3.mb-2 {
                        padding: 0.75rem !important;
                        flex-direction: column;
                        gap: 0.75rem;
                        align-items: flex-start !important;
                    }
                    .border.rounded.p-3.mb-2 .d-flex.align-items-center {
                        flex-wrap: wrap;
                        gap: 0.5rem;
                    }
                    .border.rounded.p-3.mb-2 .d-flex.align-items-center .fw-semibold.fs-5 {
                        font-size: 1.1rem !important;
                    }
                    .border.rounded.p-3.mb-2 .d-flex.gap-3 {
                        flex-direction: column;
                        gap: 0.5rem;
                        align-items: stretch !important;
                        width: 100%;
                    }
                    .border.rounded.p-3.mb-2 .d-flex.gap-3 .d-flex.align-items-center {
                        justify-content: flex-start;
                    }
                    .border.rounded.p-3.mb-2 .d-flex.gap-3 .d-flex.align-items-center:last-child {
                        justify-content: flex-end;
                    }
                    .btn-sm {
                        font-size: 0.75rem;
                        padding: 0.25rem 0.5rem;
                    }
                    .status-badge {
                        font-size: 0.9rem;
                        min-width: auto;
                        padding: 0.25rem 0.5rem;
                    }
                    .timer-badge {
                        font-size: 1.2rem;
                        min-width: auto;
                        padding: 0.25rem 0.5rem;
                    }
                    .modal-dialog {
                        margin: 0.5rem;
                        max-width: calc(100vw - 1rem);
                    }
                    .modal-body {
                        padding: 1rem;
                        overflow-x: hidden;
                    }
                    .modal-body .row.g-3 > * {
                        margin-bottom: 0.75rem;
                    }
                    .table-responsive {
                        font-size: 0.875rem;
                    }
                    .nav-tabs .nav-link {
                        font-size: 0.9rem;
                        padding: 0.5rem 0.75rem;
                    }
                    /* Evitar que el spinner y botones se salgan del modal */
                    .spinner-border {
                        max-width: 2rem;
                        max-height: 2rem;
                    }
                    .btn {
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        max-width: 100%;
                    }
                    .modal-footer {
                        flex-wrap: wrap;
                        gap: 0.5rem;
                    }
                    .modal-footer .btn {
                        flex: 1 1 auto;
                        min-width: 0;
                    }
                }
            </style>
            <ul class="nav nav-tabs mb-3" id="prodTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-pend" data-bs-toggle="tab" data-bs-target="#pane-pend" type="button" role="tab">
                        <i class="bi bi-list-ul me-1"></i> Pendientes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-done" data-bs-toggle="tab" data-bs-target="#pane-done" type="button" role="tab">
                        <i class="bi bi-check2-circle me-1"></i> Finalizados
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="prodTabsContent">
                <div class="tab-pane fade show active" id="pane-pend" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Todos los pedidos pendientes</h5>
                            <span class="badge bg-secondary" id="pendingCount">0 pendientes</span>
                        </div>
                        <div id="pendingOrdersList"></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pane-done" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Pedidos finalizados</h5>
                            <span class="badge bg-success" id="completedCount">0</span>
                        </div>
                        <div id="completedOrdersList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div id="searchResults" class="mt-4"></div>

    
</div>

<!-- Modal para confirmar inicio de producción -->
<div class="modal fade" id="startProductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Iniciar producción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas iniciar la producción del pedido <strong id="modalOrderCode"></strong>?</p>
                <div class="mb-3">
                    <label for="productionNotes" class="form-label">Notas iniciales (opcional)</label>
                    <textarea class="form-control" id="productionNotes" rows="2" placeholder="Agregar notas sobre el inicio de producción..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmStartProduction">
                    <i class="bi bi-play-circle me-1"></i> Iniciar producción
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reporte OP -->
<div class="modal fade" id="opReporteModal" tabindex="-1" aria-labelledby="opReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="opReporteLabel">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Información de Orden <span id="rep-op-folio" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rep-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando información de la orden...</p>
                </div>
                
                <div id="rep-content" style="display: none;">
                    <!-- Información General -->
                    <div class="card mb-2 shadow-sm border-0">
                        <div class="card-header bg-light text-primary fw-bold border-0">
                            <i class="bi bi-clipboard-data me-2"></i>Información General
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Folio:</strong> <span id="rep-folio">-</span></p>
                                    <p class="mb-1"><strong>Cliente:</strong> <span id="rep-cliente">-</span></p>

                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Fecha Inicio:</strong> <span id="rep-fecha-inicio">-</span></p>
                                    <p class="mb-1"><strong>Fecha Fin:</strong> <span id="rep-fecha-fin">-</span></p>
                                    <p class="mb-1"><strong>Estatus:</strong> <span id="rep-estatus" class="badge">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tallas del Pedido -->
                    <div class="card mb-2 shadow-sm border-0">
                        <div class="card-header bg-light text-secondary fw-bold border-0">
                            <i class="bi bi-grid-3x3-gap me-2"></i>Tallas del Pedido
                        </div>
                        <div class="card-body py-2">
                            <div id="rep-tallas-wrapper">
                                <p class="text-muted mb-0" id="rep-tallas-empty">No hay tallas registradas para esta orden.</p>
                                <div class="table-responsive" id="rep-tallas-table-container" style="display:none;">
                                    <table class="table table-sm table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Sexo</th>
                                                <th>Talla</th>
                                                <th class="text-end">Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rep-tallas-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Diseño -->
                    <div class="card mb-2 shadow-sm border-0">
                        <div class="card-header bg-light text-success fw-bold border-0">
                            <i class="bi bi-palette me-2"></i>Diseño
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Código:</strong> <span id="rep-diseno-codigo">-</span></p>
                                    <p class="mb-1"><strong>Nombre:</strong> <span id="rep-diseno-nombre">-</span></p>
                                    <p class="mb-1"><strong>Versión:</strong> <span id="rep-diseno-version">-</span></p>

                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Descripción:</strong></p>
                                    <p id="rep-diseno-descripcion" class="text-muted fst-italic mb-1">-</p>
                                </div>
                            </div>
                            <div class="row mt-2" id="rep-diseno-notas-container" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-warning py-1 mb-0">
                                        <strong><i class="bi bi-sticky me-2"></i>Notas del Diseño:</strong>
                                        <p id="rep-diseno-notas" class="mb-0 mt-1">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Archivos del Diseño -->
                    <div class="card mb-2 shadow-sm border-0">
                        <div class="card-header bg-light text-warning fw-bold border-0">
                            <i class="bi bi-images me-2"></i>Archivos del Diseño
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6 text-center">
                                    <h6 class="mb-2 small text-muted text-uppercase">Foto del Diseño</h6>
                                    <div id="rep-foto-container">
                                        <p class="text-muted small">No disponible</p>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <h6 class="mb-2 small text-muted text-uppercase">Patrón</h6>
                                    <div id="rep-patron-container">
                                        <p class="text-muted small">No disponible</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="rep-error" style="display: none;" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="rep-error-msg">No se pudo cargar la información de la orden.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Incidencia OP (Estilo Incidencias) -->
<div class="modal fade" id="opIncidenciaModal" tabindex="-1" aria-labelledby="opIncidenciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="formIncidenciaOp">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="opIncidenciaLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Reportar Incidencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- OP (Solo lectura, ya que viene del click) -->
                    <div class="col-md-4">
                        <label class="form-label">OP (Folio)</label>
                        <input type="text" class="form-control" id="inc-op-folio-input" readonly>
                        <input type="hidden" id="inc-op-id-input" name="ordenProduccionFK">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select id="inc-tipo" name="tipo" class="form-select" required>
                            <option value="">-- Selecciona --</option>
                            <option>Paro de máquina</option>
                            <option>Falta de material</option>
                            <option>Calidad</option>
                            <option>Seguridad</option>
                            <option>Otro</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input id="inc-fecha" type="date" name="fecha" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Prioridad</label>
                        <select id="inc-prioridad" name="prioridad" class="form-select">
                            <option>Baja</option><option>Media</option><option>Alta</option>
                        </select>
                    </div>

                    <!-- Empleado: Se omite select, se podría enviar el actual oculto si fuera necesario -->
                    
                    <div class="col-md-8">
                        <label class="form-label">Acción/Seguimiento</label>
                        <input id="inc-accion" name="accion" class="form-control" placeholder="Ej. Cambiar sensor, solicitar material, etc.">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea id="inc-descripcion" name="descripcion" class="form-control" rows="3" placeholder="Describe la incidencia..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-send me-1"></i> Reportar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registro de Rendimiento -->
<div class="modal fade" id="rendimientoModal" tabindex="-1" aria-labelledby="rendimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formRendimiento">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="rendimientoLabel">
                        <i class="bi bi-clipboard-check me-2"></i>Registrar Mi Rendimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
            <div class="row g-3">
                <!-- OP (Solo lectura) -->
                <div class="col-md-6">
                    <label class="form-label">OP (Folio)</label>
                    <input type="text" class="form-control" id="rend-op-folio" readonly>
                    <input type="hidden" id="rend-op-id" name="ordenProduccionId">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="rend-fecha" name="fecha_registro" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Operación</label>
                    <select class="form-select" id="rend-operacion" name="operacionControlId" required>
                        <option value="">Seleccione operación...</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Anota el número de bulto</label>
                    <input type="text" class="form-control" id="rend-notas" name="notas" placeholder="Ej. Bulto 12">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cantidad Producida</label>
                    <input type="number" class="form-control" id="rend-cantidad" name="cantidad" min="0" required placeholder="Piezas producidas">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Hora Inicio</label>
                    <input type="time" class="form-control" id="rend-hora-inicio" name="hora_inicio" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Hora Fin</label>
                    <input type="time" class="form-control" id="rend-hora-fin" name="hora_fin">
                </div>

                
                <!-- Información del empleado (oculto) -->
                <input type="hidden" id="rend-empleado-id" name="empleadoId">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> Guardar Rendimiento
            </button>
        </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const __isRolCorte = <?= json_encode(strcasecmp(trim($__roleName), 'corte') === 0) ?>;
    const __isRolEmpleado = <?= json_encode(strcasecmp(trim($__roleName), 'empleado') === 0) ?>;
    const __isRolAdminJefe = <?= json_encode(in_array(mb_strtolower(trim($__roleName)), ['administrador','jefe','superadmin'])) ?>;
    const empId = <?= json_encode($empleadoId ?? null) ?>;
    const maquiladoraId = <?= json_encode(session()->get('maquiladora_id') ?? null) ?>;
    
    // Función para cargar las órdenes
    function cargarOrdenes() {
        const base = '<?= base_url('modulo1/produccion/tareas') ?>';
        // Agregar timestamp para evitar caché
        const timestamp = Date.now();
        const sendEmp = empId && !__isRolAdminJefe; // Solo filtrar por empleado si NO es admin/jefe
        const url = base
            + (sendEmp ? ('?empleadoId=' + encodeURIComponent(empId)) : '')
            + (sendEmp ? '&' : '?')
            + 't=' + timestamp + '&_nocache=' + timestamp;
        const $pendList = document.getElementById('pendingOrdersList');
        const $pendCount = document.getElementById('pendingCount');
        const $doneList = document.getElementById('completedOrdersList');
        const $doneCount = document.getElementById('completedCount');
        if ($pendList) { $pendList.innerHTML = '<div class="text-muted">Cargando pendientes...</div>'; }
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                console.log('=== CARGAR ORDENES ===');
                console.log('Items recibidos:', data.items);
                const items = Array.isArray(data.items) ? data.items : [];
                // Log del estatus de cada item
                items.forEach(item => {
                    console.log('OP:', item.folio, 'Estatus:', item.status);
                });
                const renderCard = (it) => {
                    const folio = it.folio || '-';
                    const status = it.status || '-';
                    const desde = it.asignadoDesde || '-';
                    const hasta = it.asignadoHasta || '-';
                    const tieneFinalizado = it.tieneFinalizado === true;
                    const lower = String(status).toLowerCase();
                    // Mostrar botón si: el estatus es "En proceso" o "En corte" (permite reactivar cuando vuelve a proceso)
                    // O si no tiene finalizado y el estatus es correcto
                    const isEnProceso = (__isRolEmpleado && lower === 'en proceso');
                    const isEnCorte = (__isRolCorte && lower === 'en corte');
                    const showStart = (isEnProceso || isEnCorte) && 
                                     (lower !== 'completada' && lower !== 'corte finalizado');
                    
                    let actionButton = '';
                    if (showStart) {
                        // Si está en proceso/corte, mostrar botón incluso si tiene finalizado (permite reactivar)
                        actionButton = '<button class="btn btn-sm btn-success btn-start-production" data-folio="' + (folio||'') + '" data-id="' + (it.opId||it.id||'') + '"><i class="bi bi-play-circle me-1"></i>Empezar</button>';
                    } else if (tieneFinalizado && !showStart) {
                        // Solo mostrar "Ya finalizado" si no está en proceso/corte
                        actionButton = '<span class="badge bg-secondary">Ya finalizado</span>';
                    }
                    
                    // Botón de registrar rendimiento (solo para empleados y corte cuando están en proceso)
                    let rendimientoButton = '';
                    if ((__isRolEmpleado && lower === 'en proceso') || (__isRolCorte && lower === 'en corte')) {
                        rendimientoButton = '<button class="btn btn-sm btn-outline-primary btn-registrar-rendimiento" data-folio="' + (folio||'') + '" data-id="' + (it.opId||it.id||'') + '" title="Registrar mi rendimiento"><i class="bi bi-clipboard-check me-1"></i>Mi Rendimiento</button>';
                    }
                    
                    const reportButton = '<button class="btn btn-outline-secondary border-0 p-1 ms-2 btn-reporte-op" data-folio="' + (folio||'') + '" data-bs-toggle="modal" data-bs-target="#opReporteModal" title="Ver Reporte"><i class="bi bi-file-earmark-text fs-4"></i></button>';
                    const warningButton = '<button class="btn btn-outline-warning border-0 p-1 me-2 btn-incidencia-op" data-folio="' + (folio||'') + '" data-id="' + (it.opId||it.id||'') + '" data-bs-toggle="modal" data-bs-target="#opIncidenciaModal" title="Reportar Incidencia"><i class="bi bi-exclamation-triangle fs-4"></i></button>';
                    
                    const left = (
                        '<div>'
                        + '<div class="d-flex align-items-center">'
                        +   '<div class="fw-semibold fs-5">OP ' + folio + '</div>'
                        +   reportButton
                        + '</div>'
                        + '<div class="text-muted small">Desde: ' + desde + ' · Hasta: ' + hasta + '</div>'
                        + '</div>'
                    );

                    const right = (
                        '<div class="d-flex align-items-center gap-3 flex-wrap">'
                        + '<div class="d-flex align-items-center gap-2 flex-wrap">'
                        +   actionButton
                        +   rendimientoButton
                        + '</div>'
                        + '<div class="d-flex align-items-center">'
                        +   warningButton
                        +   '<span class="badge bg-info text-dark status-badge">' + status + '</span>'
                        + '</div>'
                        + '</div>'
                    );

                    return (
                        '<div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">'
                        + left
                        + right
                        + '</div>'
                    );
                };

                if ($pendList && $pendCount) {
                    // Filtrar por estatus completado (case-insensitive)
                    const pending = items.filter(it => {
                        const statusLower = String(it.status || '').toLowerCase();
                        return statusLower !== 'completada' && statusLower !== 'corte finalizado';
                    });
                    $pendCount.textContent = pending.length + ' pendientes';
                    $pendList.innerHTML = pending.length ? pending.map(renderCard).join('') : '<div class="text-muted">No hay pedidos pendientes.</div>';
                }

                if ($doneList && $doneCount) {
                    // Filtrar por estatus completado (case-insensitive)
                    const done = items.filter(it => {
                        const statusLower = String(it.status || '').toLowerCase();
                        return statusLower === 'completada' || statusLower === 'corte finalizado';
                    });
                    $doneCount.textContent = done.length;
                    $doneList.innerHTML = done.length ? done.map(renderCard).join('') : '<div class="text-muted">No hay pedidos finalizados.</div>';
                }
            })
            .catch(() => {
                if ($pendList) $pendList.innerHTML = '<div class="text-danger">No se pudieron cargar los pendientes.</div>';
            });
    }
    
    // Cargar órdenes al inicio
    cargarOrdenes();

    // Handler para el botón de reporte
    document.addEventListener('click', function(ev){
        const btn = ev.target.closest('.btn-reporte-op');
        if (btn) {
            const folio = btn.getAttribute('data-folio');
            const el = document.getElementById('rep-op-folio');
            if(el) el.textContent = folio;
            
            // Mostrar loading y ocultar contenido/error
            document.getElementById('rep-loading').style.display = 'block';
            document.getElementById('rep-content').style.display = 'none';
            document.getElementById('rep-error').style.display = 'none';
            
            // Cargar datos de la orden
            fetch('<?= base_url('modulo1/ordenes/folio/') ?>' + encodeURIComponent(folio) + '/json', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Error al cargar la orden');
                return r.json();
            })
            .then(data => {
                console.log('Datos de la orden:', data);
                
                // Ocultar loading y mostrar contenido
                document.getElementById('rep-loading').style.display = 'none';
                document.getElementById('rep-content').style.display = 'block';
                
                // Información General
                document.getElementById('rep-folio').textContent = data.folio || '-';
                document.getElementById('rep-cliente').textContent = data.cliente || '-';

                document.getElementById('rep-fecha-inicio').textContent = data.fechaInicioPlan ? new Date(data.fechaInicioPlan).toLocaleDateString() : '-';
                document.getElementById('rep-fecha-fin').textContent = data.fechaFinPlan ? new Date(data.fechaFinPlan).toLocaleDateString() : '-';
                
                // Estatus con badge
                const estatusEl = document.getElementById('rep-estatus');
                estatusEl.textContent = data.status || '-';
                estatusEl.className = 'badge bg-info';
                
                // Información del Diseño
                if (data.diseno) {
                    document.getElementById('rep-diseno-codigo').textContent = data.diseno.codigo || '-';
                    document.getElementById('rep-diseno-nombre').textContent = data.diseno.nombre || '-';
                    document.getElementById('rep-diseno-version').textContent = data.diseno.version || '-';

                    document.getElementById('rep-diseno-descripcion').textContent = data.diseno.descripcion || 'Sin descripción';
                    
                    // Notas (solo mostrar si existen)
                    if (data.diseno.notas && data.diseno.notas.trim() !== '') {
                        document.getElementById('rep-diseno-notas').textContent = data.diseno.notas;
                        document.getElementById('rep-diseno-notas-container').style.display = 'block';
                    } else {
                        document.getElementById('rep-diseno-notas-container').style.display = 'none';
                    }
                    
                    // Foto del diseño
                    const fotoContainer = document.getElementById('rep-foto-container');
                    if (data.diseno.archivoCadUrl) {
                        fotoContainer.innerHTML = '<img src="' + data.diseno.archivoCadUrl + '" class="img-fluid rounded shadow" style="max-height: 400px;" alt="Foto del diseño">';
                    } else {
                        fotoContainer.innerHTML = '<p class="text-muted">No disponible</p>';
                    }
                    
                    // Patrón
                    const patronContainer = document.getElementById('rep-patron-container');
                    if (data.diseno.archivoPatronUrl) {
                        patronContainer.innerHTML = '<img src="' + data.diseno.archivoPatronUrl + '" class="img-fluid rounded shadow" style="max-height: 400px;" alt="Patrón del diseño">';
                    } else {
                        patronContainer.innerHTML = '<p class="text-muted">No disponible</p>';
                    }
                } else {
                    // Si no hay diseño, mostrar valores por defecto
                    document.getElementById('rep-diseno-codigo').textContent = '-';
                    document.getElementById('rep-diseno-nombre').textContent = '-';
                    document.getElementById('rep-diseno-version').textContent = '-';

                    document.getElementById('rep-diseno-descripcion').textContent = 'Sin información';
                    document.getElementById('rep-diseno-notas-container').style.display = 'none';
                    document.getElementById('rep-foto-container').innerHTML = '<p class="text-muted">No disponible</p>';
                    document.getElementById('rep-patron-container').innerHTML = '<p class="text-muted">No disponible</p>';
                }

                // Tallas del pedido (pedido_tallas_detalle)
                const tallas = Array.isArray(data.tallas) ? data.tallas : [];
                const tallasEmpty = document.getElementById('rep-tallas-empty');
                const tallasTableContainer = document.getElementById('rep-tallas-table-container');
                const tallasBody = document.getElementById('rep-tallas-body');
                if (tallasBody && tallasEmpty && tallasTableContainer) {
                    if (tallas.length === 0) {
                        tallasEmpty.style.display = 'block';
                        tallasTableContainer.style.display = 'none';
                        tallasBody.innerHTML = '';
                    } else {
                        tallasEmpty.style.display = 'none';
                        tallasTableContainer.style.display = 'block';
                        tallasBody.innerHTML = tallas.map(t => {
                            const sexo = t.nombre_sexo || '-';
                            const talla = t.nombre_talla || '-';
                            const cant = typeof t.cantidad !== 'undefined' ? t.cantidad : '';
                            return '<tr>'
                                + '<td>' + sexo + '</td>'
                                + '<td>' + talla + '</td>'
                                + '<td class="text-end">' + cant + '</td>'
                                + '</tr>';
                        }).join('');
                    }
                }
            })
            .catch(err => {
                console.error('Error al cargar datos de la orden:', err);
                document.getElementById('rep-loading').style.display = 'none';
                document.getElementById('rep-error').style.display = 'block';
                document.getElementById('rep-error-msg').textContent = 'No se pudo cargar la información de la orden. Por favor, intente nuevamente.';
            });
        }
        
        const btnInc = ev.target.closest('.btn-incidencia-op');
        if (btnInc) {
            const folio = btnInc.getAttribute('data-folio');
            const id = btnInc.getAttribute('data-id');
            
            // Pre-llenar campos
            const elFolio = document.getElementById('inc-op-folio-input');
            const elId = document.getElementById('inc-op-id-input');
            const elFecha = document.getElementById('inc-fecha');
            
            if(elFolio) elFolio.value = folio;
            if(elId) elId.value = id;
            if(elFecha && !elFecha.value) {
                elFecha.value = new Date().toISOString().slice(0,10);
            }
        }
    });

    // Submit del formulario de incidencia via AJAX
    document.getElementById('formIncidenciaOp').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        // Agregar empleado si está disponible
        if (empId) formData.append('empleadoFK', empId);
        // Agregar maquiladora si está disponible
        if (maquiladoraId) formData.append('maquiladoraID', maquiladoraId);

        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';

        fetch('<?= base_url('modulo3/incidencias/crear') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            if (!r.ok) throw new Error('Error en la respuesta del servidor');
            // Si redirige, fetch puede seguirlo, pero aquí esperamos JSON o redirección manual
            return r.text().then(text => {
                try { return JSON.parse(text); } catch(e) { return { ok: true, msg: 'Incidencia registrada (respuesta no JSON)' }; }
            });
        })
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Reportado',
                text: 'La incidencia ha sido registrada correctamente.',
                timer: 1500,
                showConfirmButton: false
            });
            // Cerrar modal y limpiar
            const modalEl = document.getElementById('opIncidenciaModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if(modal) modal.hide();
            form.reset();
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar la incidencia. Verifique la conexión.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // Handler para el botón de registrar rendimiento
    document.addEventListener('click', function(ev){
        const rendBtn = ev.target.closest('.btn-registrar-rendimiento');
        if (rendBtn) {
            const folio = rendBtn.getAttribute('data-folio') || '';
            const id = rendBtn.getAttribute('data-id') || '';
            
            // Pre-llenar campos del modal
            document.getElementById('rend-op-folio').value = folio;
            document.getElementById('rend-op-id').value = id;
            document.getElementById('rend-empleado-id').value = empId;
            
            // Establecer fecha actual
            const today = new Date().toISOString().slice(0,10);
            document.getElementById('rend-fecha').value = today;
            
            // Establecer hora actual
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hours}:${minutes}`;
            document.getElementById('rend-hora-inicio').value = currentTime;
            document.getElementById('rend-hora-fin').value = currentTime;
            
            // Cargar operaciones del control de bultos para esta OP
            cargarOperacionesParaRendimiento(id);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('rendimientoModal'));
            modal.show();
        }
    });
    
    // Función para cargar operaciones disponibles para rendimiento
    function cargarOperacionesParaRendimiento(opId) {
        const selectOperacion = document.getElementById('rend-operacion');
        selectOperacion.innerHTML = '<option value="">Cargando operaciones...</option>';
        
        // Buscar controles de bultos para esta OP
        const url = `<?= base_url('modulo3/api/control-bultos/por-op/') ?>${opId}`;
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            return r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    parsed = null;
                }
                return { ok: r.ok, status: r.status, text, json: parsed };
            });
        })
        .then(data => {
            selectOperacion.innerHTML = '<option value="">Seleccione operación...</option>';

            if (!data.ok) {
                console.error('Error HTTP al cargar operaciones:', { url, status: data.status, body: data.text });
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Error al cargar operaciones';
                option.disabled = true;
                selectOperacion.appendChild(option);
                return;
            }

            const payload = data.json;
            if (!payload || typeof payload !== 'object') {
                console.error('Respuesta no JSON al cargar operaciones:', { url, status: data.status, body: data.text });
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Error al cargar operaciones';
                option.disabled = true;
                selectOperacion.appendChild(option);
                return;
            }

            if (payload.ok && payload.data && payload.data.length > 0) {
                // Agrupar operaciones por control de bultos
                payload.data.forEach(control => {
                    if (control.operaciones && control.operaciones.length > 0) {
                        // Crear grupo para este control
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = `Control: ${control.estilo || 'N/A'} (ID: ${control.id})`;
                        
                        control.operaciones.forEach(op => {
                            const option = document.createElement('option');
                            option.value = op.id;
                            option.textContent = `${op.nombre_operacion} - ${op.piezas_requeridas || 0} piezas`;
                            option.dataset.controlId = control.id;
                            optgroup.appendChild(option);
                        });
                        
                        selectOperacion.appendChild(optgroup);
                    }
                });
            } else {
                // Si no hay controles, mostrar mensaje
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No hay operaciones disponibles';
                option.disabled = true;
                selectOperacion.appendChild(option);
            }
        })
        .catch(err => {
            console.error('Error al cargar operaciones:', err);
            selectOperacion.innerHTML = '<option value="">Error al cargar operaciones</option>';
        });
    }
    
    // Submit del formulario de rendimiento
    document.getElementById('formRendimiento').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        // Validar que la hora fin sea mayor o igual a la hora inicio
        const horaInicio = formData.get('hora_inicio');
        const horaFin = formData.get('hora_fin');
        if (horaFin && horaFin < horaInicio) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La hora de fin no puede ser anterior a la hora de inicio'
            });
            return;
        }
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        fetch('<?= base_url('modulo3/api/control-bultos/registrar-rendimiento-empleado') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Rendimiento registrado!',
                    text: 'Tu producción ha sido registrada correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Cerrar modal y limpiar formulario
                const modal = bootstrap.Modal.getInstance(document.getElementById('rendimientoModal'));
                modal.hide();
                form.reset();
                
                // Recargar lista de órdenes para actualizar estado
                setTimeout(() => {
                    cargarOrdenes();
                }, 1000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo registrar el rendimiento'
                });
            }
        })
        .catch(err => {
            console.error('Error al registrar rendimiento:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar el rendimiento. Verifique la conexión.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    const __runningTimers = new Map();
    function __format(ms){
        const s = Math.floor(ms/1000);
        const hh = String(Math.floor(s/3600)).padStart(2,'0');
        const mm = String(Math.floor((s%3600)/60)).padStart(2,'0');
        const ss = String(s%60).padStart(2,'0');
        return hh+':'+mm+':'+ss;
    }
    document.addEventListener('click', function(ev){
        const startBtn = ev.target.closest('.btn-start-production');
        if (startBtn) {
            const folio = startBtn.getAttribute('data-folio') || '';
            const id = startBtn.getAttribute('data-id') || '';
            Swal.fire({
                title: '¿Empezar producción?',
                text: 'Se iniciará el cronómetro de esta OP.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, empezar',
                cancelButtonText: 'Cancelar'
            }).then(function(res){
                if (!res.isConfirmed) return;
                // Llamar al endpoint para iniciar tiempo de trabajo
                const urlIniciar = '<?= base_url('modulo1/produccion/tiempo/iniciar') ?>';
                fetch(urlIniciar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'ordenProduccionId=' + encodeURIComponent(id) + (empId && empId !== null ? '&empleadoId=' + encodeURIComponent(empId) : '')
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        Swal.fire({ icon:'success', title:'Iniciado', text:'Cronómetro en marcha.', timer:1200, showConfirmButton:false });
                        const right = startBtn.parentElement;
                        const statusEl = right.querySelector('.status-badge');
                        const timer = document.createElement('span');
                        timer.className = 'badge bg-success timer-badge';
                        const t0 = Date.now();
                        timer.textContent = __format(0);
                        const finBtn = document.createElement('button');
                        finBtn.className = 'btn btn-sm btn-outline-danger btn-finalizar-production';
                        finBtn.setAttribute('data-id', id);
                        finBtn.setAttribute('data-tiempo-id', data.id || '');
                        finBtn.innerHTML = '<i class="bi bi-stop-circle me-1"></i>Finalizar';
                        const tick = setInterval(function(){ timer.textContent = __format(Date.now()-t0); }, 1000);
                        __runningTimers.set(id, {t0, tick, el: timer, tiempoTrabajoId: data.id || null});
                        // Reordenar: [Timer grande, Finalizar] y al final el estatus
                        right.innerHTML = '';
                        const centerWrap = document.createElement('div');
                        centerWrap.className = 'd-flex align-items-center justify-content-center gap-3 flex-wrap';
                        centerWrap.appendChild(timer);
                        centerWrap.appendChild(finBtn);
                        right.appendChild(centerWrap);
                        if (statusEl) right.appendChild(statusEl);
                    } else {
                        Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo iniciar el tiempo de trabajo.' });
                    }
                })
                .catch(err => {
                    console.error('Error al iniciar tiempo de trabajo:', err);
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo iniciar el tiempo de trabajo.' });
                });
            });
            return;
        }
        const finBtn = ev.target.closest('.btn-finalizar-production');
        if (finBtn) {
            const id = finBtn.getAttribute('data-id') || '';
            const tiempoTrabajoId = finBtn.getAttribute('data-tiempo-id') || '';
            const rec = __runningTimers.get(id);
            if (rec) { clearInterval(rec.tick); __runningTimers.delete(id); }
            
            // Llamar al endpoint para finalizar tiempo de trabajo
            const urlFinalizar = '<?= base_url('modulo1/produccion/tiempo/finalizar') ?>';
            const bodyData = tiempoTrabajoId 
                ? 'tiempoTrabajoId=' + encodeURIComponent(tiempoTrabajoId)
                : 'ordenProduccionId=' + encodeURIComponent(id) + (empId && empId !== null ? '&empleadoId=' + encodeURIComponent(empId) : '');
            
            fetch(urlFinalizar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: bodyData
            })
            .then(r => r.json())
            .then(data => {
                // Mostrar información de debug en la consola
                console.log('=== DEBUG FINALIZAR TIEMPO TRABAJO ===');
                console.log('Respuesta completa:', data);
                if (data.debug) {
                    console.log('Debug info:', data.debug);
                    console.log('Puesto:', data.debug.puesto);
                    console.log('Tipo verificado:', data.debug.tipoVerificado);
                    console.log('Todos finalizados:', data.debug.todosFinalizados);
                    console.log('Nuevo estatus:', data.debug.nuevoEstatus);
                    console.log('Estatus actualizado:', data.debug.estatusActualizado);
                    if (data.debug.tiempos) {
                        console.log('Registros de tiempo:', data.debug.tiempos);
                    }
                    if (data.debug.asignaciones) {
                        console.log('Asignaciones:', data.debug.asignaciones);
                    }
                }
                console.log('========================================');
                
                if (data.ok) {
                    // Buscar el contenedor correcto
                    const card = finBtn.closest('.border.rounded');
                    if (card) {
                        const right = finBtn.parentElement;
                        if (right) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-secondary';
                            badge.textContent = 'Finalizado';
                            finBtn.remove();
                            if (rec && rec.el) { rec.el.className = 'badge bg-primary me-2'; }
                            right.appendChild(badge);
                        }
                    }
                    
                    // Mostrar mensaje principal sin mencionar el cambio de estatus (se mostrará después si aplica)
                    let mensaje = 'La producción fue finalizada. Horas trabajadas: ' + (data.horas ? parseFloat(data.horas).toFixed(2) : '0.00');
                    if (data.todosFinalizados === false) {
                        mensaje += '\nEsperando que otros empleados finalicen...';
                    }
                    
                    Swal.fire({ 
                        icon:'success', 
                        title:'Finalizado', 
                        text: mensaje, 
                        timer:2000, 
                        showConfirmButton:false 
                    });
                    
                    // Si el estatus se actualizó, mostrar una notificación separada más discreta y recargar
                    if (data.estatusActualizado && data.nuevoEstatus) {
                        console.log('Recargando lista de órdenes porque el estatus se actualizó a:', data.nuevoEstatus);
                        // Mostrar notificación discreta después de un breve delay
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'info',
                                title: 'Estatus actualizado',
                                text: 'La orden ahora está: ' + data.nuevoEstatus,
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }, 2500);
                        // Esperar más tiempo para asegurar que la actualización se haya completado en la BD
                        setTimeout(() => {
                            cargarOrdenes();
                        }, 1500);
                    } else if (data.todosFinalizados === true && data.nuevoEstatus) {
                        // Si todos finalizaron pero no se actualizó, recargar de todas formas
                        console.log('Recargando lista de órdenes porque todos finalizaron:', data.nuevoEstatus);
                        setTimeout(() => {
                            cargarOrdenes();
                        }, 1500);
                    }
                } else {
                    Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo finalizar el tiempo de trabajo.' });
                }
            })
            .catch(err => {
                console.error('Error al finalizar tiempo de trabajo:', err);
                Swal.fire({ icon:'error', title:'Error', text:'No se pudo finalizar el tiempo de trabajo.' });
            });
        }
    });
    </script>
<?= $this->endSection() ?>
