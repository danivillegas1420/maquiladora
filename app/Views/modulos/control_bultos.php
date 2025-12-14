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

    /* Centrar checkboxes en la tabla de empleados */
    #tablaEmpleadosCantidad tbody td:first-child {
        text-align: center;
        vertical-align: middle;
    }

    #tablaEmpleadosCantidad .empleado-checkbox {
        margin: 0;
    }

    #tablaEmpleadosCantidad th:first-child {
        text-align: center;
    }

    #tablaEmpleadosCantidad th:nth-child(4),
    #tablaEmpleadosCantidad td:nth-child(4) {
        display: none;
    }

    .matriz-bitacora-wrap {
        overflow: auto;
        max-height: 70vh;
        border: 1px solid #cfcfcf;
        border-radius: 6px;
        background: #0f1115;
    }

    /* Forzar estilos dentro del modal (evitar overrides por tema/Bootstrap) */
    #modalBitacoraMatriz .matriz-bitacora-wrap {
        background: #0f1115 !important;
        border-color: rgba(255,255,255,0.18) !important;
    }

    .matriz-bitacora {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .matriz-bitacora th,
    .matriz-bitacora td {
        border: 1px solid #d6d6d6;
        padding: 6px 8px;
        vertical-align: top;
        word-wrap: break-word;
        color: #e9ecef;
        font-size: 12px;
    }

    #modalBitacoraMatriz .matriz-bitacora th,
    #modalBitacoraMatriz .matriz-bitacora td {
        border-color: rgba(255,255,255,0.18) !important;
        color: #ffffff !important;
    }

    .matriz-bitacora thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #1f2d3d;
        color: #ffffff;
        text-align: center;
    }

    #modalBitacoraMatriz .matriz-bitacora thead th {
        background: #1f2d3d !important;
        color: #ffffff !important;
    }

    .matriz-bitacora .op-col {
        position: sticky;
        left: 0;
        z-index: 6;
        background: #1f2d3d;
        color: #ffffff;
        width: 260px;
        min-width: 260px;
        max-width: 260px;
    }

    #modalBitacoraMatriz .matriz-bitacora .op-col {
        background: #1f2d3d !important;
        color: #ffffff !important;
    }

    .matriz-bitacora tbody td {
        background: rgba(255,255,255,0.03);
    }

    #modalBitacoraMatriz .matriz-bitacora tbody td {
        background: rgba(255,255,255,0.04) !important;
        color: #ffffff !important;
    }

    .matriz-bitacora td {
        min-width: 140px;
        height: 74px;
    }

    .matriz-cell-item {
        display: block;
        font-size: 11px;
        line-height: 1.2;
        padding: 1px 0;
    }

    .matriz-cell-item .emp {
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        max-width: none;
    }

    .matriz-cell-item .qty {
        font-weight: 600;
        display: block;
    }

    .matriz-col-head {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        line-height: 1.1;
    }

    .matriz-col-head .num {
        font-weight: 700;
        color: #ffffff;
    }

    .matriz-col-head .meta {
        font-size: 10px;
        color: rgba(255,255,255,0.75);
    }

    #modalBitacoraMatriz .matriz-col-head .num,
    #modalBitacoraMatriz .matriz-col-head .meta,
    #modalBitacoraMatriz .matriz-cell-item,
    #modalBitacoraMatriz .matriz-cell-item .emp,
    #modalBitacoraMatriz .matriz-cell-item .qty {
        color: #ffffff !important;
    }

    @media (max-width: 768px) {
        #modalBitacoraMatriz .modal-dialog {
            max-width: 98vw;
            margin: 0.5rem auto;
        }

        #modalBitacoraMatriz .matriz-bitacora-wrap {
            max-height: 60vh;
        }

        /* Dejar que la tabla crezca horizontalmente y se scrollee (evita texto en vertical) */
        #modalBitacoraMatriz .matriz-bitacora {
            table-layout: auto !important;
            width: max-content !important;
            min-width: 100%;
        }

        #modalBitacoraMatriz .matriz-bitacora th,
        #modalBitacoraMatriz .matriz-bitacora td {
            min-width: 120px;
        }

        #modalBitacoraMatriz .matriz-bitacora .op-col {
            width: 180px;
            min-width: 180px;
            max-width: 180px;
        }

        #modalBitacoraMatriz .matriz-col-head {
            white-space: nowrap;
        }

        #modalBitacoraMatriz .matriz-cell-item .emp {
            white-space: normal;
            word-break: normal;
            overflow-wrap: anywhere;
        }
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
                        <!-- Sección de tallas (se mostrará dinámicamente) -->
                        <div class="col-12" id="seccionTallas" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Esta orden de producción contiene múltiples tallas. Especifique las cantidades por talla:
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="tablaTallas">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Talla</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyTallas">
                                        <!-- Se generará dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
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

                <!-- Sección de tallas (se mostrará dinámicamente) -->
                <div class="mb-3" id="seccionTallasDetalle" style="display: none;">
                    <h6>Detalles por Talla (Pedido)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Sexo</th>
                                    <th>Talla</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTallasDetalle">
                                <!-- Se generará dinámicamente -->
                            </tbody>
                        </table>
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
                        <label class="form-label">Empleados y Cantidades</label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover mb-0" id="tablaEmpleadosCantidad">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;" class="text-center">
                                            <input type="checkbox" id="selectAllEmpleados" title="Seleccionar todos">
                                        </th>
                                        <th>Empleado</th>
                                        <th style="width: 120px;">Registrado</th>
                                        <th style="width: 120px;">Cantidad</th>
                                        <th style="width: 100px;">Puesto</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyEmpleados">
                                    <!-- Se generará dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <strong>Total Registrado:</strong> <span id="totalCantidad">0</span> unidades
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha_registro" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
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

<!-- Modal Bitácora (Vista Matriz) -->
<div class="modal fade" id="modalBitacoraMatriz" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bitacoraMatrizTitulo">Vista Matriz - Bitácora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="bitacoraMatrizLoading" class="text-muted"></div>
                <div id="bitacoraMatrizEmpty" class="text-muted" style="display:none;">Sin registros.</div>
                <div id="bitacoraMatrizContent" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-success" id="btnDescargarMatrizPDF" title="Descargar matriz en PDF">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                    <button class="btn btn-success" id="btnDescargarMatrizExcel" title="Descargar matriz en Excel">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                    <a href="#" class="btn btn-primary" id="btnIrMatriz" target="_blank" style="display:none;">Ir a Vista Matriz</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    let empleadosOrden = []; // Empleados específicos de la orden de producción actual

    $(document).ready(function () {
        // Función para poblar la tabla de empleados
        function poblarTablaEmpleados() {
            const tbody = $('#tbodyEmpleados');
            tbody.empty();
            
            // Filtrar empleados por la orden de producción actual si es necesario
            const empleadosAMostrar = empleadosOrden.length > 0 ? empleadosOrden : empleados;
            
            if (empleadosAMostrar.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay empleados disponibles</td></tr>');
                return;
            }
            
            empleadosAMostrar.forEach(empleado => {
                const row = `
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input empleado-checkbox" 
                                   data-empleado-id="${empleado.id}" data-empleado-nombre="${empleado.nombre} ${empleado.apellido}">
                        </td>
                        <td>${empleado.nombre} ${empleado.apellido}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary registrado-actual" data-empleado-id="${empleado.id}">0</span>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm cantidad-input" 
                                   min="0" placeholder="0" disabled
                                   data-empleado-id="${empleado.id}">
                        </td>
                        <td><small class="text-muted">${empleado.puesto || 'N/A'}</small></td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            // Actualizar total
            actualizarTotalCantidad();

            // Aplicar marcación guardada (si existe)
            aplicarMarcacionGuardada();

            // Si ya hay operación seleccionada, cargar resumen para mostrar lo ya registrado
            const opId = $('#regOperacion').val();
            if (opId) {
                cargarResumenProduccionOperacion(opId);
            }
        }

        function limpiarResumenRegistrado() {
            $('.registrado-actual').text('0').removeClass('bg-success').addClass('bg-secondary');
        }

        function cargarResumenProduccionOperacion(operacionId) {
            limpiarResumenRegistrado();

            $.ajax({
                url: `<?= base_url('modulo3/api/control-bultos') ?>/${operacionId}/resumen-produccion`,
                type: 'GET',
                success: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    const resumen = Array.isArray(response.data) ? response.data : [];

                    resumen.forEach(item => {
                        const empleadoId = String(item.empleadoId);
                        const total = parseInt(item.total_cantidad || 0) || 0;
                        const badge = $(`.registrado-actual[data-empleado-id="${empleadoId}"]`);
                        if (badge.length) {
                            badge.text(total);
                            if (total > 0) {
                                badge.removeClass('bg-secondary').addClass('bg-success');
                            }
                        }
                    });
                }
            });
        }
        
        // Función para actualizar el total de cantidades
        function actualizarTotalCantidad() {
            let total = 0;
            $('.cantidad-input').each(function() {
                const valor = parseInt($(this).val()) || 0;
                total += valor;
            });
            $('#totalCantidad').text(total);
        }

        // Event handlers para la tabla de empleados
        $(document).on('change', '.empleado-checkbox', function() {
            const isChecked = $(this).is(':checked');
            const empleadoId = $(this).data('empleado-id');
            const cantidadInput = $(`.cantidad-input[data-empleado-id="${empleadoId}"]`);
            
            cantidadInput.prop('disabled', !isChecked);
            if (!isChecked) {
                cantidadInput.val(0);
            }
            
            actualizarTotalCantidad();

            // Guardar/actualizar marcación persistente
            const currentIds = new Set(getMarcacionEmpleadoIds());
            const strId = String(empleadoId);
            if (isChecked) {
                currentIds.add(strId);
            } else {
                currentIds.delete(strId);
            }
            setMarcacionEmpleadoIds(Array.from(currentIds));
        });

        $(document).on('input', '.cantidad-input', function() {
            actualizarTotalCantidad();
        });

        // Checkbox "Seleccionar todos"
        $(document).on('change', '#selectAllEmpleados', function() {
            const isChecked = $(this).is(':checked');
            $('.empleado-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Al cambiar la operación, refrescar los registros ya capturados por empleado
        $(document).on('change', '#regOperacion', function() {
            const opId = $(this).val();
            if (opId) {
                cargarResumenProduccionOperacion(opId);
            } else {
                limpiarResumenRegistrado();
            }

            // Al cambiar operación, reaplicar marcación guardada para esa operación
            aplicarMarcacionGuardada();
        });

        // Función para obtener la clave de marcación
        function getMarcacionKey() {
            const controlId = $('#regControlId').val() || '';
            const operacionId = $('#regOperacion').val() || '';
            return `control_bultos_marcacion:${controlId}:${operacionId}`;
        }

        // Función para obtener los IDs de empleados marcados
        function getMarcacionEmpleadoIds() {
            const key = getMarcacionKey();
            try {
                const raw = localStorage.getItem(key);
                const parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed.map(String) : [];
            } catch (e) {
                return [];
            }
        }

        // Función para establecer los IDs de empleados marcados
        function setMarcacionEmpleadoIds(ids) {
            const key = getMarcacionKey();
            try {
                localStorage.setItem(key, JSON.stringify(ids));
            } catch (e) {
                // ignore
            }
        }

        // Función para aplicar la marcación guardada
        function aplicarMarcacionGuardada() {
            const ids = new Set(getMarcacionEmpleadoIds());
            $('.empleado-checkbox').each(function () {
                const empleadoId = String($(this).data('empleado-id'));
                if (ids.has(empleadoId)) {
                    $(this).prop('checked', true);
                }
            });

            // Disparar change para que habilite/deshabilite inputs y recalcule total
            $('.empleado-checkbox').trigger('change');
        }

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
                        return `<div class="progress"><div class="progress-bar bg-info" role="progressbar" style="width: ${progreso}%" aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">${progreso}%</div></div>`;
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
                            <button class="btn btn-sm btn-success btn-matriz-bitacora" data-id="${data.id}" title="Vista Matriz"><i class="fas fa-th"></i></button>
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
                "loadingRecords": "",
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
            const ordenId = selected.val();

            if (diseno) {
                $('input[name="estilo"]').val(diseno);
            }
            if (cantidad) {
                $('input[name="cantidad_total"]').val(cantidad);
            }

            // Verificar si la orden tiene múltiples tallas
            if (ordenId) {
                verificarTallasOrden(ordenId);
            } else {
                ocultarSeccionTallas();
            }
        });

        // Función para verificar si una orden tiene múltiples tallas
        function verificarTallasOrden(ordenId) {
            // Buscar la orden en el array de órdenes
            const orden = ordenes.find(o => (o.opId || o.id) == ordenId);
            
            if (orden && orden.tallas && orden.tallas.length > 1) {
                // Mostrar sección de tallas si hay más de una talla
                mostrarSeccionTallas(orden.tallas);
            } else if (orden && orden.tallas && orden.tallas.length === 1) {
                // Si hay una sola talla, ocultar sección pero usar esa cantidad
                ocultarSeccionTallas();
                $('input[name="cantidad_total"]').val(orden.tallas[0].cantidad);
            } else {
                // No hay información de tallas, ocultar sección
                ocultarSeccionTallas();
            }
        }

        // Función para mostrar la sección de tallas
        function mostrarSeccionTallas(tallas) {
            const tbody = $('#tbodyTallas');
            tbody.empty();
            
            let totalCantidad = 0;
            tallas.forEach(talla => {
                const row = `
                    <tr>
                        <td>${talla.nombre_talla || 'Talla ' + talla.id_talla}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm cantidad-talla" 
                                   name="talla_${talla.id_talla}" value="${talla.cantidad || 0}" 
                                   min="0" data-talla-id="${talla.id_talla}">
                        </td>
                    </tr>
                `;
                tbody.append(row);
                totalCantidad += parseInt(talla.cantidad || 0);
            });
            
            // Actualizar cantidad total
            $('input[name="cantidad_total"]').val(totalCantidad);
            
            // Mostrar sección
            $('#seccionTallas').show();
            
            // Agregar event listeners para actualizar total
            $('.cantidad-talla').on('input', function() {
                actualizarTotalCantidadTallas();
            });
        }

        // Función para ocultar la sección de tallas
        function ocultarSeccionTallas() {
            $('#seccionTallas').hide();
            $('#tbodyTallas').empty();
        }

        // Función para actualizar el total de cantidades de tallas
        function actualizarTotalCantidadTallas() {
            let total = 0;
            $('.cantidad-talla').each(function() {
                const valor = parseInt($(this).val()) || 0;
                total += valor;
            });
            $('input[name="cantidad_total"]').val(total);
        }

        // Funciones para manejar tallas en el modal de detalles
        function mostrarTallasEnDetalle(tallas) {
            const tbody = $('#tbodyTallasDetalle');
            tbody.empty();
            
            tallas.forEach(talla => {
                const row = `
                    <tr>
                        <td>${talla.nombre_sexo || 'N/A'}</td>
                        <td>${talla.nombre_talla || 'N/A'}</td>
                        <td>${talla.cantidad || 0}</td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            // Mostrar sección
            $('#seccionTallasDetalle').show();
        }

        function ocultarTallasEnDetalle() {
            $('#seccionTallasDetalle').hide();
            $('#tbodyTallasDetalle').empty();
        }

                
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
            console.log('Botón Ver Detalles presionado');
            const id = $(this).data('id');
            console.log('ID del control:', id);
            
            if (!id) {
                console.error('No se encontró el ID del control');
                Swal.fire('Error', 'No se encontró el ID del control', 'error');
                return;
            }
            
            cargarDetalleControl(id);
        });

        // Abrir Bitácora desde botón "Vista Matriz" (columna Acciones)
        $('#tablaControles tbody').on('click', '.btn-matriz-bitacora', function () {
            const controlId = $(this).data('id');
            if (!controlId) return;

            function fetchJsonSafe(url) {
                return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async (r) => {
                        const ct = (r.headers.get('content-type') || '').toLowerCase();
                        if (!ct.includes('application/json')) {
                            const raw = await r.text().catch(() => '');
                            return { ok: false, status: r.status, message: 'Respuesta no JSON', raw: raw.slice(0, 250) };
                        }
                        try {
                            const json = await r.json();
                            if (typeof json === 'object' && json !== null) {
                                return json;
                            }
                            return { ok: false, status: r.status, message: 'Respuesta JSON inválida' };
                        } catch (e) {
                            return { ok: false, message: 'Error parseando JSON', error: e.message };
                        }
                    })
                    .catch((e) => ({ ok: false, message: 'Error de red', error: e.message }));
            }

            $('#bitacoraMatrizTitulo').text('Vista Matriz - Bitácora (Control #' + controlId + ')');
            $('#bitacoraMatrizLoading').show();
            $('#bitacoraMatrizEmpty').hide();
            $('#bitacoraMatrizContent').hide().empty();
            $('#btnIrMatriz').hide().attr('href', `<?= base_url('modulo3/control-bultos') ?>/${controlId}/matriz`);
            $('#modalBitacoraMatriz').modal('show');

            const pReg = fetchJsonSafe(`<?= base_url('modulo3/api/control-bultos') ?>/${controlId}/registros-produccion`);
            const pBul = fetchJsonSafe(`<?= base_url('modulo3/api/control-bultos') ?>/${controlId}/bultos`);
            const pProg = fetchJsonSafe(`<?= base_url('modulo3/api/control-bultos') ?>/${controlId}/progreso`);

            Promise.all([pReg, pBul, pProg])
                .then(([respReg, respBul, respProg]) => {
                    $('#bitacoraMatrizLoading').hide();

                    const bulOk = respBul && respBul.ok;
                    const progOk = respProg && respProg.ok;
                    const regOk = respReg && respReg.ok;

                    if (!bulOk || !progOk || !regOk) {
                        const parts = [];
                        if (!bulOk) parts.push('Bultos: ' + (respBul?.message || respBul?.error || respBul?.raw || 'Error'));
                        if (!progOk) parts.push('Operaciones: ' + (respProg?.message || respProg?.error || respProg?.raw || 'Error'));
                        if (!regOk) parts.push('Registros: ' + (respReg?.message || respReg?.error || respReg?.raw || 'Error'));
                        $('#bitacoraMatrizEmpty').text(parts.join(' | ') || 'No se pudo cargar la información.').show();
                        return;
                    }

                    const bultos = Array.isArray(respBul.data) ? respBul.data : [];
                    const operaciones = (respProg.data && Array.isArray(respProg.data.operaciones)) ? respProg.data.operaciones : [];
                    const registros = Array.isArray(respReg.data) ? respReg.data : [];

                    if (operaciones.length === 0) {
                        $('#bitacoraMatrizEmpty').text('No hay operaciones configuradas para este control.').show();
                        return;
                    }

                    let cols = [];
                    if (bultos.length > 0) {
                        cols = bultos.map(b => ({
                            key: String(b.numero_bulto || '').trim(),
                            label: String(b.numero_bulto || '').trim(),
                            talla: String(b.talla || '').trim(),
                            cantidad: String(b.cantidad ?? '').trim(),
                        })).filter(c => c.key !== '');
                    } else {
                        const uniq = new Set();
                        registros.forEach(r => {
                            const k = String(r.observaciones || '').trim();
                            if (k) uniq.add(k);
                        });
                        cols = Array.from(uniq).sort().map(k => ({ key: k, label: k, talla: '', cantidad: '' }));
                    }

                    if (cols.length === 0) {
                        $('#bitacoraMatrizEmpty').text('No hay bultos (observaciones) ni registros para construir la matriz.').show();
                        $('#btnIrMatriz').show();
                        return;
                    }

                    // index[opNombre][colKey][empleado] = total
                    const idx = {};
                    registros.forEach(r => {
                        const opNombre = String(r.nombre_operacion || '').trim();
                        const colKey = String(r.observaciones || '').trim();
                        if (!opNombre || !colKey) return;
                        const emp = (String(r.empleadoNombre || '') + ' ' + String(r.empleadoApellido || '')).trim() || ('ID ' + (r.empleadoId || ''));
                        const cant = parseInt(r.cantidad_producida || 0) || 0;
                        if (!idx[opNombre]) idx[opNombre] = {};
                        if (!idx[opNombre][colKey]) idx[opNombre][colKey] = {};
                        if (!idx[opNombre][colKey][emp]) idx[opNombre][colKey][emp] = 0;
                        idx[opNombre][colKey][emp] += cant;
                    });

                    let html = '<div class="matriz-bitacora-wrap">';
                    html += '<table class="matriz-bitacora">';
                    html += '<thead>';
                    html += '<tr><th class="op-col">BULTOS</th>';
                    cols.forEach(c => {
                        html += '<th><div class="matriz-col-head"><div class="num">' + (c.label || '') + '</div></div></th>';
                    });
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    operaciones.forEach(op => {
                        const opNombre = String(op.nombre_operacion || op.nombre || '').trim() || '---';
                        html += '<tr>';
                        html += '<td class="op-col">' + opNombre + '</td>';
                        cols.forEach(c => {
                            const cell = (idx[opNombre] && idx[opNombre][c.key]) ? idx[opNombre][c.key] : null;
                            if (!cell) {
                                html += '<td></td>';
                                return;
                            }
                            let cellHtml = '';
                            Object.keys(cell).forEach(emp => {
                                cellHtml += '<div class="matriz-cell-item"><span class="emp">' + emp + '</span><span class="qty">' + cell[emp] + '</span></div>';
                            });
                            html += '<td>' + cellHtml + '</td>';
                        });
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $('#bitacoraMatrizContent').html(html).show();
                    if (registros.length === 0) {
                        $('#bitacoraMatrizEmpty').text('Sin registros de producción aún (se muestra la matriz vacía).').show();
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    $('#bitacoraMatrizLoading').hide();
                    $('#bitacoraMatrizEmpty').text('No se pudo cargar la bitácora: ' + (err?.message || err)).show();
                });
        });

        // Descargar matriz en PDF
        $(document).on('click', '#btnDescargarMatrizPDF', function() {
            // Obtener el controlId desde el botón Ir a Vista Matriz
            const href = $('#btnIrMatriz').attr('href');
            if (!href) {
                Swal.fire('Error', 'No se pudo identificar el control.', 'error');
                return;
            }
            // Extraer el ID del final de la URL: /modulo3/control-bultos/123/matriz
            const parts = href.split('/');
            const id = parts[parts.length - 2]; // penúltimo elemento
            if (!id || isNaN(id)) {
                Swal.fire('Error', 'ID de control no válido.', 'error');
                return;
            }
            const url = `<?= base_url('modulo3/control-bultos') ?>/${id}/matriz/pdf`;
            console.log('URL PDF:', url);
            window.open(url, '_blank');
        });

        // Descargar matriz en Excel
        $(document).on('click', '#btnDescargarMatrizExcel', function() {
            // Obtener el controlId desde el botón Ir a Vista Matriz
            const href = $('#btnIrMatriz').attr('href');
            if (!href) {
                Swal.fire('Error', 'No se pudo identificar el control.', 'error');
                return;
            }
            // Extraer el ID del final de la URL: /modulo3/control-bultos/123/matriz
            const parts = href.split('/');
            const id = parts[parts.length - 2]; // penúltimo elemento
            if (!id || isNaN(id)) {
                Swal.fire('Error', 'ID de control no válido.', 'error');
                return;
            }
            const url = `<?= base_url('modulo3/control-bultos') ?>/${id}/matriz/excel`;
            console.log('URL Excel:', url);
            window.open(url, '_blank');
        });
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
            console.log('Cargando detalles para control ID:', id);
            
            $.ajax({
                url: `<?= base_url('modulo3/api/control-bultos') ?>/${id}/progreso`,
                type: 'GET',
                success: function (response) {
                    console.log('Respuesta de progreso:', response);
                    
                    if (response.ok) {
                        const data = response.data;

                        // Extraer empleados específicos de la orden de producción desde la respuesta
                        empleadosOrden = data.empleados || [];

                        // Mostrar tallas del pedido si el control tiene múltiples tallas
                        if (data.con_tallas && data.tallas && data.tallas.length > 0) {
                            mostrarTallasEnDetalle(data.tallas);
                        } else {
                            ocultarTallasEnDetalle();
                        }

                        // Actualizar cabecera del modal
                        $('#detalleOrden').text(data.orden || '---');
                        $('#detalleEstilo').text(data.estilo || '---');
                        $('#detalleEstado').html(`<span class="badge bg-secondary">${data.estado || 'Desconocido'}</span>`);
                        $('#regControlId').val(id);

                        // Cargar operaciones en la tabla (una sola tabla)
                        const tbodyOperaciones = $('#tablaOperaciones tbody');
                        tbodyOperaciones.empty();

                        (data.operaciones || []).forEach(op => {
                            const progreso = op.porcentaje_completado || 0;
                            const row = `
                                <tr>
                                    <td>${op.nombre_operacion}</td>
                                    <td>${op.es_componente == 1 ? '<span class="badge bg-info">Componente</span>' : '<span class="badge bg-primary">Armado</span>'}</td>
                                    <td>${op.piezas_requeridas || 0}</td>
                                    <td>${op.piezas_completadas || 0}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: ${progreso}%" aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">${progreso}%</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-success btn-op-registrar" 
                                                data-id="${op.id}"
                                                title="Registrar producción para esta operación">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbodyOperaciones.append(row);
                        });

                        // Poblar select de operaciones (solo operaciones no completadas)
                        const selectOperacion = $('#regOperacion');
                        selectOperacion.empty().append('<option value="">Seleccione operación...</option>');

                        (data.operaciones || []).forEach(op => {
                            const progreso = op.porcentaje_completado || 0;
                            const completado = parseFloat(progreso) >= 100;
                            if (!completado) {
                                selectOperacion.append(`<option value="${op.id}">${op.nombre_operacion}</option>`);
                            }
                        });

                        console.log('Mostrando modal de detalles...');
                        $('#modalDetalleControl').modal('show');
                    } else {
                        console.error('Error en progreso:', response.message);
                        Swal.fire('Error', 'No se pudo cargar el progreso del control: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar progreso:', error);
                    Swal.fire('Error', 'No se pudo cargar el progreso del control', 'error');
                }
            });
        }

        // Abrir modal de registro desde detalle
        $('#btnRegistrarProduccion').click(function () {

            // Poblar tabla de empleados
            poblarTablaEmpleados();

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

            // Poblar tabla de empleados
            poblarTablaEmpleados();

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
            // Recopilar empleados y cantidades seleccionadas
            const empleadosSeleccionados = [];
            $('.empleado-checkbox:checked').each(function() {
                const empleadoId = $(this).data('empleado-id');
                const cantidad = parseInt($(`.cantidad-input[data-empleado-id="${empleadoId}"]`).val()) || 0;
                
                if (cantidad > 0) {
                    empleadosSeleccionados.push({
                        empleadoId: empleadoId,
                        cantidad: cantidad
                    });
                }
            });

            // Validar que se haya seleccionado al menos un empleado con cantidad
            if (empleadosSeleccionados.length === 0) {
                Swal.fire('Atención', 'Debe seleccionar al menos un empleado y especificar una cantidad mayor a 0', 'warning');
                return;
            }

            // Obtener datos del formulario
            const formData = new FormData(document.getElementById('formRegistroProduccion'));
            
            // Agregar los datos de empleados y cantidades como JSON
            formData.append('empleados_cantidades', JSON.stringify(empleadosSeleccionados));

            $.ajax({
                url: '<?= base_url('modulo3/api/control-bultos/registrar-produccion') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.ok) {
                        $('#modalRegistroProduccion').modal('hide');
                        Swal.fire('Éxito', 'Producción registrada correctamente', 'success');
                        document.getElementById('formRegistroProduccion').reset();
                        // Limpiar tabla de empleados
                        $('#tbodyEmpleados').empty();
                        $('#selectAllEmpleados').prop('checked', false);
                        $('#totalCantidad').text('0');
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