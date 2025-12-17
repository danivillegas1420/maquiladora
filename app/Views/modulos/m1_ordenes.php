<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Espaciado y estilo de los botones de exportación */
    .dt-buttons.btn-group .btn{
        margin-right:.5rem;
        border-radius:.375rem !important;
    }
    .dt-buttons.btn-group .btn:last-child{ margin-right:0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // Cargar helper de autenticación para obtener el rol actual
    if (!function_exists('current_role_name')) { helper('auth'); }
    $__roleName = function_exists('current_role_name') ? (string)current_role_name() : '';
?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Órdenes de Producción</h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Órdenes de Producción</strong>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills mb-3" id="compartidasTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" type="button" data-compartidas-filter="all">Todas</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" type="button" data-compartidas-filter="entrante">Entrantes</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" type="button" data-compartidas-filter="saliente">Salientes</button>
            </li>
        </ul>
        <table id="tablaOrdenes" class="table table-striped table-bordered text-center align-middle">
            <thead class="table-light">
            <tr>
                <th>OP</th>
                <th>Cliente</th>
                <th>Diseño</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <?php
                        $__tipoCompartida = 'normal';
                        if (isset($orden['maquiladoraID']) && isset($currentMaquiladoraId) && $orden['maquiladoraID'] != $currentMaquiladoraId) {
                            $__tipoCompartida = 'entrante';
                        } elseif (!empty($orden['maquiladoraCompartidaID'])) {
                            $__tipoCompartida = 'saliente';
                        }
                    ?>
                    <tr data-compartida-tipo="<?= esc($__tipoCompartida) ?>">
                        <td>
                            <?= esc($orden['op']) ?>
                            <?php if (isset($orden['maquiladoraID']) && isset($currentMaquiladoraId) && $orden['maquiladoraID'] != $currentMaquiladoraId): ?>
                                <span class="badge bg-info text-dark" title="Orden compartida de otra maquiladora"><i class="bi bi-box-arrow-in-down"></i> Externa</span>
                            <?php elseif (!empty($orden['maquiladoraCompartidaID'])): ?>
                                <span class="badge bg-warning text-dark" title="Transferida a otra maquiladora"><i class="bi bi-share"></i> Compartida</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($orden['cliente']) ?></td>
                        <td><?= esc($orden['diseno'] ?? '') ?></td>
                        <td><?= esc($orden['ini']) ?></td>
                        <td><?= esc($orden['fin']) ?></td>
                        <td>
                            <?php 
                            $estatusActual = trim($orden['estatus'] ?? '');
                            $badgeClass = 'secondary';
                            $estatusLower = strtolower($estatusActual);
                            if (strpos($estatusLower, 'completada') !== false || strpos($estatusLower, 'finalizada') !== false) {
                                $badgeClass = 'success';
                            } elseif (strpos($estatusLower, 'planificada') !== false || strpos($estatusLower, 'planeada') !== false) {
                                $badgeClass = 'info';
                            } elseif (strpos($estatusLower, 'proceso') !== false || strpos($estatusLower, 'corte') !== false) {
                                $badgeClass = 'primary';
                            } elseif (strpos($estatusLower, 'pausada') !== false || strpos($estatusLower, 'detenida') !== false) {
                                $badgeClass = 'secondary';
                            } elseif (strpos($estatusLower, 'cancelada') !== false) {
                                $badgeClass = 'danger';
                            }
                            ?>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="spinner-border spinner-border-sm text-primary op-estatus-saving" role="status" style="display:none;" aria-hidden="true"></div>
                                <select class="form-select form-select-sm op-estatus-select bg-<?= $badgeClass ?> text-white border-<?= $badgeClass ?>" 
                                        data-id="<?= esc($orden['opId'] ?? '') ?>" 
                                        data-prev="<?= esc($estatusActual) ?>" 
                                        style="min-width: 150px; font-weight: 500;">
                                    <option value="Planificada" <?= strcasecmp($estatusActual,'Planificada')===0 ? 'selected' : '' ?>>Planificada</option>
                                    <option value="En corte"     <?= strcasecmp($estatusActual,'En corte')===0 ? 'selected' : '' ?>>En corte</option>
                                    <option value="Corte finalizado"     <?= strcasecmp($estatusActual,'Corte finalizado')===0 ? 'selected' : '' ?>>Corte finalizado</option>
                                    <option value="En proceso"  <?= strcasecmp($estatusActual,'En proceso')===0 ? 'selected' : '' ?>>En proceso</option>
                                    <option value="Completada"  <?= strcasecmp($estatusActual,'Completada')===0 ? 'selected' : '' ?>>Completada</option>
                                    <option value="Pausada"     <?= strcasecmp($estatusActual,'Pausada')===0 ? 'selected' : '' ?>>Pausada</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-sm btn-outline-info btn-ver-op" data-folio="<?= esc($orden['op'] ?? '') ?>" data-bs-toggle="modal" data-bs-target="#opDetalleModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-ver-comparticion" data-id="<?= esc($orden['opId'] ?? '') ?>" data-bs-toggle="modal" data-bs-target="#opComparticionModal">
                                    <i class="bi bi-diagram-3"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-agregar-op" data-id="<?= esc($orden['opId'] ?? '') ?>" data-folio="<?= esc($orden['op'] ?? '') ?>" data-bs-toggle="modal" data-bs-target="#opAsignacionesModal">
                                    <i class="bi bi-person-plus"></i>
                                </button>
                                <?php
                                $__isEnCorte = (isset($estatusActual) && strcasecmp(trim($estatusActual), 'En corte') === 0);
                                $__isRolCorte = (strcasecmp(trim($__roleName), 'corte') === 0);
                                if ($__isEnCorte && $__isRolCorte): ?>
                                    <a href="<?= base_url('modulo1/produccion') ?>" class="btn btn-sm btn-success btn-empezar-produccion">
                                        <i class="bi bi-play-circle"></i> Empezar
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-warning btn-transferir-op" 
                                        data-id="<?= esc($orden['opId'] ?? '') ?>" 
                                        data-folio="<?= esc($orden['op'] ?? '') ?>"
                                        data-maquiladora-compartida="<?= esc($orden['maquiladoraCompartidaID'] ?? '') ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#opTransferirModal">
                                    <i class="bi bi-share"></i> 
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-op" data-id="<?= esc($orden['opId'] ?? '') ?>" data-folio="<?= esc($orden['op'] ?? '') ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="opComparticionModal" tabindex="-1" aria-labelledby="opComparticionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="opComparticionLabel">Detalle de Compartición</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>OP</strong>
                            <div id="comp-op-folio">—</div>
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo</strong>
                            <div><span class="badge bg-secondary" id="comp-op-tipo">—</span></div>
                        </div>
                        <div class="col-md-4">
                            <strong>Cantidad Plan</strong>
                            <div id="comp-op-cantidad">—</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Maquila Origen</strong>
                            <div id="comp-origen">—</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Maquila Destino</strong>
                            <div id="comp-destino">—</div>
                        </div>
                    </div>
                    <hr>
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>Avance</strong>
                        </div>
                        <div class="card-body">
                            <div id="comp-avance-na" class="text-muted">—</div>
                            <div id="comp-avance-wrap" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="badge bg-info text-dark" id="comp-avance-estado">—</span>
                                        <span class="ms-2">Cantidad: <span id="comp-avance-cantidad">—</span></span>
                                    </div>
                                    <a id="comp-avance-link" class="btn btn-sm btn-outline-primary" href="#" target="_blank" rel="noopener">Ver Control</a>
                                </div>
                                <div class="progress" role="progressbar" aria-label="Progreso">
                                    <div class="progress-bar" id="comp-avance-bar" style="width:0%">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle OP -->
    <div class="modal fade" id="opDetalleModal" tabindex="-1" aria-labelledby="opDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="opDetalleLabel">Detalle de Orden de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información de la Orden -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Información de la Orden</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Folio OP:</strong>
                                    <p class="mb-2" id="op-folio">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Estatus:</strong>
                                    <p class="mb-2"><span class="badge bg-secondary" id="op-status">-</span></p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Inicio Plan:</strong>
                                    <p class="mb-2" id="op-ini">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Fin Plan:</strong>
                                    <p class="mb-2" id="op-fin">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Cliente y Pedido -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-person me-2"></i>Cliente y Pedido</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Cliente:</strong>
                                    <p class="mb-2" id="op-cliente">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Cantidad Planeada:</strong>
                                    <p class="mb-2 fw-bold" id="op-cant">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Cantidad de Bultos:</strong>
                                    <p class="mb-2 fw-bold" id="op-bultos">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Pedido:</strong>
                                    <p class="mb-2 text-success fw-bold" id="op-total">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Diseño -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Diseño</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Código:</strong>
                                    <p class="mb-2" id="op-dis-codigo">-</p>
                                </div>
                                <div class="col-md-8">
                                    <strong>Nombre:</strong>
                                    <p class="mb-2" id="op-dis-nombre">-</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <strong>Descripción:</strong>
                                    <p class="mb-2" id="op-dis-descripcion">-</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Versión:</strong>
                                    <p class="mb-2" id="op-dis-version">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Fecha Versión:</strong>
                                    <p class="mb-2" id="op-dis-fecha">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Aprobado:</strong>
                                    <p class="mb-2" id="op-dis-aprobado">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Precio Unitario:</strong>
                                    <p class="mb-2 text-primary fw-bold" id="op-dis-precio">-</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <strong>Notas:</strong>
                                    <p class="mb-0" id="op-dis-notas">-</p>
                                </div>
                            </div>
                            
                            <!-- Archivos del Diseño -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <strong>Archivos:</strong>
                                    <div id="op-dis-archivos">
                                        <div id="opDisCarousel" class="carousel slide" data-bs-ride="false" style="display:none;">
                                            <div class="carousel-inner"></div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#opDisCarousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Anterior</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#opDisCarousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Siguiente</span>
                                            </button>
                                        </div>
                                        <span id="op-dis-archivos-na" class="text-muted">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asignaciones OP -->
    <div class="modal fade" id="opAsignacionesModal" tabindex="-1" aria-labelledby="opAsignacionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="opAsignacionesLabel">Asignación de Tareas a OP <span id="asg-opid"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Empleados disponibles (marque para asignar)</label>
                            <div id="asg-disponibles-list" class="border rounded p-2" style="max-height:260px; overflow:auto;"></div>
                            <div class="mt-3"></div>
                            <label class="form-label">Personal de Corte (marque para asignar)</label>
                            <div id="asg-corte-list" class="border rounded p-2" style="max-height:260px; overflow:auto;"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Desde</label>
                            <input type="datetime-local" id="asg-desde" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hasta</label>
                            <input type="datetime-local" id="asg-hasta" class="form-control" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small text-muted">Seleccione uno o más empleados y luego presione Asignar.</div>
                        <button id="asg-btn-assign-selected" type="button" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Asignar seleccionados
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle" id="asg-tabla">
                            <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Puesto</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Transferir OP -->
    <div class="modal fade" id="opTransferirModal" tabindex="-1" aria-labelledby="opTransferirLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="opTransferirLabel">Transferir Orden <span id="trans-op-folio"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Seleccione la maquiladora con la que desea compartir esta orden de producción:</p>
                    <form id="formTransferirOp">
                        <input type="hidden" id="trans-op-id" name="opId">
                        <div class="mb-3">
                            <label for="trans-maquiladora" class="form-label">Maquiladora Destino</label>
                            <select class="form-select" id="trans-maquiladora" name="maquiladoraId" required>
                                <option value="">Seleccione una opción...</option>
                                <?php if (!empty($maquiladoras)): ?>
                                    <?php foreach ($maquiladoras as $m): ?>
                                        <option value="<?= esc($m['id']) ?>"><?= esc($m['nombre']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay otras maquiladoras disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-confirmar-transferencia">Transferir</button>
                </div>
            </div>
        </div>
    </div>
    <?= $this->endSection() ?>

    <?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Export helpers (Buttons) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const __isRolCorte = <?= json_encode(strcasecmp(trim($__roleName), 'corte') === 0) ?>;
        $(function(){
            const langES = {
                sProcessing:"Procesando...",
                sLengthMenu:"Mostrar _MENU_ registros",
                sZeroRecords:"No se encontraron resultados",
                sEmptyTable:"Ningún dato disponible en esta tabla",
                sInfo:"Mostrando _START_ a _END_ de _TOTAL_",
                sInfoEmpty:"Mostrando 0 a 0 de 0",
                sInfoFiltered:"(filtrado de _MAX_ en total)",
                sSearch:"Buscar:",
                oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
                oAria:{ sSortAscending:": Orden asc", sSortDescending:": Orden desc" },
                buttons:{ copy:"Copiar" }
            };
            const fecha = new Date().toISOString().slice(0,10);
            const fileName = 'ordenes_produccion_' + fecha;

            function toLocalInput(dt){
                const pad = n => String(n).padStart(2,'0');
                return dt.getFullYear()+'-'+pad(dt.getMonth()+1)+'-'+pad(dt.getDate())+'T'+pad(dt.getHours())+':'+pad(dt.getMinutes());
            }

            let __compartidasFilter = 'all';
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
                if (settings.nTable && settings.nTable.id !== 'tablaOrdenes') return true;
                if (__compartidasFilter === 'all') return true;
                try {
                    const node = settings.aoData[dataIndex].nTr;
                    const tipo = (node && node.getAttribute('data-compartida-tipo')) ? String(node.getAttribute('data-compartida-tipo')) : 'normal';
                    return tipo === __compartidasFilter;
                } catch (e) {
                    return true;
                }
            });

            // DataTable + Botones
            const __dtOrdenes = $('#tablaOrdenes').DataTable({
                language: langES,
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false } // Acciones
                ],
                dom:
                    "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend:'copy',  text:'Copiar',  exportOptions:{ columns: ':visible' } },
                    { extend:'csv',   text:'CSV',     filename:fileName, exportOptions:{ columns: ':visible' } },
                    { extend:'excel', text:'Excel',   filename:fileName, exportOptions:{ columns: ':visible' } },
                    { extend:'pdf',   text:'PDF',     filename:fileName, title:fileName,
                        orientation:'landscape', pageSize:'A4',
                        exportOptions:{ columns: ':visible' } },
                    { extend:'print', text:'Imprimir', exportOptions:{ columns: ':visible' } }
                ]
            });

            $(document).on('click', '[data-compartidas-filter]', function(){
                const $btn = $(this);
                const f = String($btn.data('compartidas-filter') || 'all');
                __compartidasFilter = f;
                $('#compartidasTabs [data-compartidas-filter]').removeClass('active');
                $btn.addClass('active');
                __dtOrdenes.draw();
            });

            function setComparticionTexto(sel, val){
                $(sel).text(val);
            }

            $(document).on('click', '.btn-ver-comparticion', function(){
                const opId = parseInt($(this).data('id') || 0, 10);
                const $btn = $(this);
                if (!opId) {
                    setComparticionTexto('#comp-op-folio', '—');
                    setComparticionTexto('#comp-op-tipo', '—');
                    setComparticionTexto('#comp-op-cantidad', '—');
                    setComparticionTexto('#comp-origen', '—');
                    setComparticionTexto('#comp-destino', '—');
                    $('#comp-avance-wrap').hide();
                    $('#comp-avance-na').text('No se pudo determinar el ID de la OP.').show();
                    return;
                }

                $btn.prop('disabled', true);
                setComparticionTexto('#comp-op-folio', 'Cargando...');
                setComparticionTexto('#comp-op-tipo', '...');
                setComparticionTexto('#comp-op-cantidad', '...');
                setComparticionTexto('#comp-origen', '...');
                setComparticionTexto('#comp-destino', '...');
                $('#comp-avance-wrap').hide();
                $('#comp-avance-na').text('Cargando...').show();

                $.getJSON('<?= base_url('modulo1/ordenes') ?>/' + opId + '/comparticion?t=' + Date.now())
                    .done(function(resp){
                        if (!resp || !resp.ok) {
                            setComparticionTexto('#comp-op-folio', '—');
                            $('#comp-avance-wrap').hide();
                            $('#comp-avance-na').text((resp && resp.message) ? resp.message : 'No se pudo cargar el detalle.').show();
                            return;
                        }

                        const folio = resp.op && resp.op.folio ? resp.op.folio : '—';
                        const tipo = resp.op && resp.op.tipo ? String(resp.op.tipo) : 'normal';
                        const cant = (resp.op && (resp.op.cantidadPlan ?? null) !== null) ? String(resp.op.cantidadPlan) : '—';

                        setComparticionTexto('#comp-op-folio', folio);
                        setComparticionTexto('#comp-op-cantidad', cant);

                        const $badge = $('#comp-op-tipo');
                        $badge.removeClass('bg-secondary bg-info bg-warning bg-primary');
                        if (tipo === 'entrante') {
                            $badge.addClass('bg-info text-dark').text('Entrante');
                        } else if (tipo === 'saliente') {
                            $badge.addClass('bg-warning text-dark').text('Saliente');
                        } else {
                            $badge.addClass('bg-secondary').text('Normal');
                        }

                        const origen = (resp.origen && resp.origen.nombre) ? resp.origen.nombre : (resp.origen && resp.origen.id ? ('#' + resp.origen.id) : '—');
                        const destino = (resp.destino && resp.destino.nombre) ? resp.destino.nombre : (resp.destino && resp.destino.id ? ('#' + resp.destino.id) : '—');
                        setComparticionTexto('#comp-origen', origen);
                        setComparticionTexto('#comp-destino', destino);

                        if (resp.avance && resp.avance.controlBultoId) {
                            const pct = Math.max(0, Math.min(100, parseFloat(resp.avance.progreso_general ?? 0) || 0));
                            $('#comp-avance-estado').text(resp.avance.estado || '—');
                            $('#comp-avance-cantidad').text((resp.avance.cantidad_total ?? '—'));
                            $('#comp-avance-bar').css('width', pct + '%').text(pct + '%');
                            $('#comp-avance-link').attr('href', '<?= base_url('modulo3/control-bultos') ?>/' + encodeURIComponent(String(resp.avance.controlBultoId)) + '/matriz');
                            $('#comp-avance-na').hide();
                            $('#comp-avance-wrap').show();
                        } else {
                            $('#comp-avance-wrap').hide();
                            $('#comp-avance-na').text('Sin control de bultos para esta OP.').show();
                        }
                    })
                    .fail(function(xhr){
                        $('#comp-avance-wrap').hide();
                        $('#comp-avance-na').text('No se pudo cargar el detalle (HTTP ' + (xhr?.status || '?') + ').').show();
                    })
                    .always(function(){
                        $btn.prop('disabled', false);
                    });
            });

            // Eliminar OP
            $(document).on('click', '.btn-eliminar-op', function(){
                const $btn = $(this);
                const id = parseInt($btn.data('id')||0,10);
                const folio = ($btn.data('folio')||'').toString();
                if (!id) return;
                Swal.fire({
                    title: '¿Eliminar orden?',
                    text: `Se eliminará la OP ${folio||('#'+id)} y sus datos (inspección, reproceso y asignaciones).`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(res){
                    if (!res.isConfirmed) return;
                    $btn.prop('disabled', true);
                    Swal.fire({title:'Eliminando...', allowOutsideClick:false, allowEscapeKey:false, didOpen:()=>Swal.showLoading()});
                    $.ajax({
                        url: '<?= base_url('modulo1/ordenes/eliminar') ?>',
                        method: 'POST',
                        data: { id },
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function(resp){
                        Swal.fire({ icon:'success', title:'Eliminada', text:'La OP y sus datos relacionados fueron eliminados.', timer:1400, showConfirmButton:false });
                        try { $btn.closest('tr').remove(); } catch(e) {}
                    }).fail(function(xhr){
                        Swal.fire({ icon:'error', title:'Error', text:'No se pudo eliminar la orden.' });
                    }).always(function(){
                        $btn.prop('disabled', false);
                    });
                });
            });

            // Transferir OP
            $(document).on('click', '.btn-transferir-op', function(){
                const id = $(this).data('id');
                const folio = $(this).data('folio');
                const maquiladoraCompartida = $(this).data('maquiladora-compartida') || '';
                
                $('#trans-op-id').val(id);
                $('#trans-op-folio').text(folio);
                
                // Si ya tiene una maquiladora compartida, preseleccionarla
                if (maquiladoraCompartida) {
                    $('#trans-maquiladora').val(maquiladoraCompartida);
                } else {
                    $('#trans-maquiladora').val('');
                }
            });

            $('#btn-confirmar-transferencia').on('click', function(){
                const opId = $('#trans-op-id').val();
                const maquiladoraId = $('#trans-maquiladora').val();
                
                if (!opId || !maquiladoraId) {
                    Swal.fire('Error', 'Por favor seleccione una maquiladora', 'error');
                    return;
                }
                
                const $btn = $(this);
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: '<?= base_url('modulo1/ordenes/compartir') ?>',
                    method: 'POST',
                    data: { opId, maquiladoraId },
                    dataType: 'json'
                }).done(function(resp){
                    if(resp.success){
                        Swal.fire('Éxito', resp.message, 'success');
                        $('#opTransferirModal').modal('hide');
                    } else {
                        Swal.fire('Error', resp.message, 'error');
                    }
                }).fail(function(){
                    Swal.fire('Error', 'Ocurrió un error al procesar la solicitud', 'error');
                }).always(function(){
                    $btn.prop('disabled', false);
                });
            });

            // ---------------- Asignaciones ----------------
            function cargarAsignaciones(opId){
                const $modal = $('#opAsignacionesModal');
                $modal.find('#asg-opid').text(opId);
                const $list = $modal.find('#asg-disponibles-list');
                const $listCorte = $modal.find('#asg-corte-list');
                const $tbody = $modal.find('#asg-tabla tbody');
                // Prefijar Desde con ahora
                try { $modal.find('#asg-desde').val(toLocalInput(new Date())); } catch(e) {}
                // Prefijar Hasta con fechaFinPlan de la OP
                $.getJSON('<?= base_url('modulo1/ordenes') ?>/' + opId + '/json?t=' + Date.now())
                    .done(function(det){
                        const fin = det && (det.fechaFinPlan || det.fin);
                        if (fin) {
                            const d = new Date(String(fin).replace(' ', 'T'));
                            if (!isNaN(d)) { try { $modal.find('#asg-hasta').val(toLocalInput(d)); } catch(e) {} }
                        }
                    });
                $list.html('<div class="text-muted"></div>');
                $listCorte.html('<div class="text-muted"></div>');
                $tbody.html('<tr><td colspan="5" class="text-center text-muted"></td></tr>');
                $.getJSON('<?= base_url('modulo1/ordenes') ?>/' + opId + '/asignaciones?t=' + Date.now())
                    .done(function(data){
                        const emps = data.empleados || [];
                        const isCorte = e => String(e?.puesto||'').trim().toLowerCase() === 'corte';
                        const corte = emps.filter(isCorte);
                        const otros = emps.filter(e => !isCorte(e));

                        if (otros.length) {
                            const itemsOtros = otros.map(function(e){
                                const label = (e.noEmpleado?('['+e.noEmpleado+'] '):'') + (e.nombre||'') + ' ' + (e.apellido||'');
                                return `<div class="form-check">
                        <input class="form-check-input asg-chk" type="checkbox" value="${e.id}" id="asg-chk-${e.id}">
                        <label class="form-check-label" for="asg-chk-${e.id}">${label}</label>
                      </div>`;
                            });
                            $list.html(itemsOtros.join(''));
                        } else {
                            $list.html('<div class="text-muted">No hay empleados disponibles.</div>');
                        }

                        if (corte.length) {
                            const itemsCorte = corte.map(function(e){
                                const label = (e.noEmpleado?('['+e.noEmpleado+'] '):'') + (e.nombre||'') + ' ' + (e.apellido||'');
                                return `<div class="form-check">
                        <input class="form-check-input asg-chk" type="checkbox" value="${e.id}" id="asg-chk-${e.id}">
                        <label class="form-check-label" for="asg-chk-${e.id}">${label}</label>
                      </div>`;
                            });
                            $listCorte.html(itemsCorte.join(''));
                        } else {
                            $listCorte.html('<div class="text-muted">No hay personal de Corte disponible.</div>');
                        }
                        $tbody.empty();
                        if (!data.asignadas || !data.asignadas.length){
                            $tbody.html('<tr><td colspan="5" class="text-center text-muted">Sin asignaciones</td></tr>');
                        } else {
                            data.asignadas.forEach(function(a){
                                const nombre = (a.noEmpleado?('['+a.noEmpleado+'] '):'') + (a.nombre||'') + ' ' + (a.apellido||'');
                                const tr = `<tr>
                <td>${nombre}</td>
                <td>${a.puesto||'-'}</td>
                <td>${a.asignadoDesde||'-'}</td>
                <td>${a.asignadoHasta||'-'}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger asg-del" data-id="${a.id}"><i class="bi bi-trash"></i></button>
                </td>
              </tr>`;
                                $tbody.append(tr);
                            });
                        }
                    })
                    .fail(function(xhr){
                        $tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar asignaciones</td></tr>');
                        console.error('Asignaciones error', xhr?.status, xhr?.responseText);
                    });
            }

            $(document).on('click', '.btn-agregar-op', function(){
                let opId = parseInt($(this).data('id') || 0, 10);
                const folio = ($(this).data('folio')||'').toString();
                const $modal = $('#opAsignacionesModal');
                const setAndLoad = (id) => {
                    $modal.data('opid', id);
                    $('#asg-btn-add').data('opid', id);
                    cargarAsignaciones(id);
                };
                if (opId > 0) { setAndLoad(opId); return; }
                if (folio) {
                    $.getJSON('<?= base_url('modulo1/ordenes/folio') ?>/' + encodeURIComponent(folio) + '/json?t=' + Date.now())
                        .done(function(data){
                            const id = parseInt(data.id||0,10);
                            if (id>0) setAndLoad(id); else alert('No se pudo resolver la OP por folio.');
                        })
                        .fail(function(xhr){
                            console.error('No se pudo resolver OP por folio', folio, xhr?.status, xhr?.responseText);
                            alert('No se pudo resolver la OP por folio.');
                        });
                    return;
                }
                alert('No se pudo determinar el ID de la OP.');
            });

            $('#opAsignacionesModal').on('shown.bs.modal', function(){
                const opId = $(this).data('opid');
                if (opId) cargarAsignaciones(opId);
            });

            $(document).on('click', '#asg-btn-assign-selected', function(){
                const $btn = $(this);
                const $modal = $('#opAsignacionesModal');
                const opId = $modal.data('opid');
                const desde = $modal.find('#asg-desde').val() || '';
                const hasta = $modal.find('#asg-hasta').val() || '';
                const empleados = $('#asg-disponibles-list .asg-chk:checked, #asg-corte-list .asg-chk:checked')
                    .map(function(){ return parseInt($(this).val(),10); }).get()
                    .filter(n=>!isNaN(n) && n>0);
                if (!opId || empleados.length===0){
                    Swal.fire({icon:'info', title:'Seleccione empleados', text:'Seleccione al menos un empleado para asignar.'});
                    return;
                }
                const url = empleados.length > 1
                    ? '<?= base_url('modulo1/ordenes/asignaciones/agregar-multiple') ?>'
                    : '<?= base_url('modulo1/ordenes/asignaciones/agregar') ?>';
                const payload = empleados.length > 1
                    ? { opId, empleados, desde, hasta }
                    : { opId, empleadoId: empleados[0], desde, hasta };

                Swal.fire({
                    title: 'Confirmar asignación',
                    text: `Asignar ${empleados.length} empleado(s) a la OP ${opId}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, asignar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result){
                    if (!result.isConfirmed) return;
                    $btn.prop('disabled', true);
                    Swal.fire({title:'Asignando...', allowOutsideClick:false, allowEscapeKey:false, didOpen:()=>Swal.showLoading()});
                    $.ajax({ url, method: 'POST', data: payload, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .done(function(){
                            Swal.fire({icon:'success', title:'Asignación exitosa', timer:1200, showConfirmButton:false});
                            cargarAsignaciones(opId);
                        })
                        .fail(function(xhr){
                            Swal.fire({icon:'error', title:'No se pudo asignar', text: 'HTTP ' + (xhr?.status||'')});
                            console.error('asignar fail', xhr?.status, xhr?.responseText);
                        })
                        .always(function(){
                            $btn.prop('disabled', false);
                        });
                });
            });

            $(document).on('click', '.asg-del', function(){
                const $btn = $(this);
                const asignacionId = $btn.data('id');
                const opId = $('#opAsignacionesModal').data('opid');
                if (!asignacionId || !opId) return;
                Swal.fire({
                    title: '¿Eliminar asignación?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(res){
                    if (!res.isConfirmed) return;
                    $btn.prop('disabled', true);
                    Swal.fire({title:'Eliminando...', allowOutsideClick:false, allowEscapeKey:false, didOpen:()=>Swal.showLoading()});
                    $.ajax({
                        url: '<?= base_url('modulo1/ordenes/asignaciones/eliminar') ?>',
                        method: 'POST',
                        data: { asignacionId },
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function(){
                        Swal.fire({icon:'success', title:'Asignación eliminada', timer:1100, showConfirmButton:false});
                        cargarAsignaciones(opId);
                    }).fail(function(xhr){
                        Swal.fire({icon:'error', title:'No se pudo eliminar', text:'HTTP ' + (xhr?.status||'')});
                        console.error('eliminar fail', xhr?.status, xhr?.responseText);
                    }).always(function(){
                        $btn.prop('disabled', false);
                    });
                });
            });

            // Función para actualizar el color del select según el estatus
            function updateSelectColor($sel) {
                const estatus = ($sel.val() || '').toString().toLowerCase();
                // Remover todas las clases de color anteriores
                $sel.removeClass('bg-success bg-info bg-primary bg-warning bg-danger bg-secondary');
                $sel.removeClass('border-success border-info border-primary border-warning border-danger border-secondary');
                
                let badgeClass = 'secondary';
                if (estatus.includes('completada') || estatus.includes('finalizada')) {
                    badgeClass = 'success';
                } else if (estatus.includes('planificada') || estatus.includes('planeada')) {
                    badgeClass = 'info';
                } else if (estatus.includes('proceso') || estatus.includes('corte')) {
                    badgeClass = 'primary';
                } else if (estatus.includes('pausada') || estatus.includes('detenida')) {
                    badgeClass = 'secondary';
                } else if (estatus.includes('cancelada')) {
                    badgeClass = 'danger';
                }
                
                $sel.addClass('bg-' + badgeClass + ' border-' + badgeClass);
            }

            // Guardar estatus con confirmación y alertas
            $(document).on('change', '.op-estatus-select', function(){
                const $sel = $(this);
                const id = $sel.data('id');
                const estatus = ($sel.val() || '').toString();
                const prev = ($sel.data('prev') || '').toString();
                const $td = $sel.closest('td');
                const $spin = $td.find('.op-estatus-saving');
                if (!id || !estatus) return;

                Swal.fire({
                    title: '¿Confirmar cambio?',
                    text: `Cambiar estatus a "${estatus}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result){
                    if (!result.isConfirmed) { 
                        if (prev) {
                            $sel.val(prev);
                            updateSelectColor($sel);
                        }
                        return; 
                    }
                    $sel.prop('disabled', true);
                    $spin.show();
                    $.ajax({
                        url: '<?= base_url('modulo1/ordenes/estatus') ?>',
                        method: 'POST',
                        data: { id, estatus },
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function(resp){
                        $sel.addClass('is-valid');
                        setTimeout(()=> $sel.removeClass('is-valid'), 1200);
                        $sel.data('prev', estatus);
                        
                        // Actualizar color del select
                        updateSelectColor($sel);
                        
                        try {
                            if (resp && resp.ok) {
                                let msg = 'Estatus actualizado correctamente.';
                                if (estatus === 'En proceso' && (resp.inspeccionId || resp.reprocesoId)) {
                                    msg = `Estatus actualizado. Inspección #${resp.inspeccionId||'-'} y Reproceso #${resp.reprocesoId||'-'} creados.`;
                                }
                                Swal.fire({ icon:'success', title:'Listo', text: msg, timer: 1600, showConfirmButton:false });
                            } else {
                                Swal.fire({ icon:'success', title:'Listo', text:'Estatus actualizado', timer: 1400, showConfirmButton:false });
                            }
                            const $row = $sel.closest('tr');
                            const $actions = $row.find('td').last().find('.d-flex');
                            if (String(estatus).toLowerCase() === 'en corte' && __isRolCorte) {
                                if ($actions.find('.btn-empezar-produccion').length === 0) {
                                    $actions.prepend('<a href="<?= base_url('modulo1/produccion') ?>" class="btn btn-sm btn-success btn-empezar-produccion"><i class="bi bi-play-circle"></i> Empezar</a>');
                                }
                            } else {
                                $actions.find('.btn-empezar-produccion').remove();
                            }
                        } catch(e) { /* noop */ }
                    }).fail(function(xhr){
                        if (prev) $sel.val(prev);
                        $sel.addClass('is-invalid');
                        setTimeout(()=> $sel.removeClass('is-invalid'), 1500);
                        Swal.fire({ icon:'error', title:'Error', text:'No se pudo actualizar el estatus de la OP.' });
                    }).always(function(){
                        $sel.prop('disabled', false);
                        $spin.hide();
                    });
                });
            });

            // Ver detalle (modal)
            $(document).on('click', '.btn-ver-op', function(){
                const folio = $(this).data('folio');
                if (!folio) return;
                const $modal = $('#opDetalleModal');
                const $btn = $(this);
                $btn.prop('disabled', true);
                const setText = (sel, val) => $modal.find(sel).text(val);
                
                // Inicializar campos
                setText('#op-folio',''); setText('#op-status','');
                setText('#op-cant',''); setText('#op-bultos',''); setText('#op-ini',''); setText('#op-fin','');
                setText('#op-cliente',''); setText('#op-total','');
                setText('#op-dis-codigo',''); setText('#op-dis-nombre',''); 
                setText('#op-dis-descripcion',''); setText('#op-dis-version',''); 
                setText('#op-dis-fecha',''); setText('#op-dis-aprobado',''); 
                setText('#op-dis-precio',''); setText('#op-dis-notas','');

                const $car = $('#opDisCarousel'), $inner = $car.find('.carousel-inner');
                $inner.empty(); $car.hide(); $('#op-dis-archivos-na').show();

                $.getJSON('<?= base_url('modulo1/ordenes/folio') ?>/' + encodeURIComponent(folio) + '/json?t=' + Date.now())
                    .done(function(data){
                        setText('#op-folio', data.folio || '-');
                        setText('#op-status', data.status || '-');
                        setText('#op-cant', (data.cantidadPlan ?? '') || '-');
                        setText('#op-bultos', (data.cantidadBultos ?? '') || '-');
                        setText('#op-ini', data.fechaInicioPlan || '-');
                        setText('#op-fin', data.fechaFinPlan || '-');
                        
                        // Cliente y Total
                        setText('#op-cliente', data.cliente || '-');
                        const total = data.total ?? data.pedido?.total ?? null;
                        setText('#op-total', total ? '$' + parseFloat(total).toFixed(2) : '-');
                        
                        if (data.diseno){
                            setText('#op-dis-codigo', data.diseno.codigo || '-');
                            setText('#op-dis-nombre', data.diseno.nombre || '-');
                            setText('#op-dis-descripcion', data.diseno.descripcion || '-');
                            setText('#op-dis-version', data.diseno.version || '-');
                            setText('#op-dis-fecha', data.diseno.fecha || '-');
                            const aprobado = (data.diseno.aprobado===1 || data.diseno.aprobado==='1') ? 'Sí'
                                : (data.diseno.aprobado===0 || data.diseno.aprobado==='0' ? 'No' : '-');
                            setText('#op-dis-aprobado', aprobado);
                            setText('#op-dis-notas', data.diseno.notas || '-');
                            
                            // Precio unitario
                            const precio = data.diseno.precio_unidad ?? data.diseno.precioUnidad ?? null;
                            setText('#op-dis-precio', precio ? '$' + parseFloat(precio).toFixed(2) : '-');

                            const files = [];
                            if (data.diseno.archivoCadUrl) files.push({url: data.diseno.archivoCadUrl, label:'CAD'});
                            if (data.diseno.archivoPatronUrl) files.push({url: data.diseno.archivoPatronUrl, label:'Patrón'});
                            if (Array.isArray(data.diseno.archivos)) {
                                data.diseno.archivos.forEach((u,i)=>{ if(u) files.push({url:u, label:'Archivo '+(i+1)}) });
                            }
                            if (files.length){
                                const buildSlideContent = (url, label) => {
                                    const u = String(url || '');
                                    
                                    // Detectar si es base64 imagen
                                    const isBase64Img = u.startsWith('data:image/');
                                    
                                    const extMatch = u.match(/\.([a-z0-9]+)(?:\?|#|$)/i);
                                    const ext = extMatch ? extMatch[1].toLowerCase() : '';
                                    const isImg   = isBase64Img || /^(png|jpg|jpeg|gif|webp|bmp|svg)$/.test(ext);
                                    const isPdf   = ext === 'pdf';
                                    const isVideo = /^(mp4|webm|ogv|ogg)$/.test(ext);
                                    const isAudio = /^(mp3|wav|ogg)$/.test(ext);
                                    const isOffice= /^(doc|docx|xls|xlsx|ppt|pptx)$/.test(ext);
                                    const isText  = /^(txt|csv|json|xml|md|log)$/.test(ext);
                                    const isCad   = /^(dwg|dxf|stp|step|igs|iges)$/.test(ext);
                                    
                                    // Si es base64, no usamos encodeURI porque ya es seguro y encodeURI rompería la data
                                    const safeUrl = isBase64Img ? u : encodeURI(u);

                                    if (isImg)   return `<img src="${safeUrl}" class="d-block w-100" alt="${label}" style="max-height:460px; object-fit:contain; background:#f8f9fa;">`;
                                    if (isPdf)   return `<iframe src="${safeUrl}" class="d-block w-100" style="height:460px; border:0;" title="${label}"></iframe>`;
                                    if (isVideo) return `<video class="d-block w-100" style="max-height:460px; background:#000;" controls src="${safeUrl}"></video>`;
                                    if (isAudio) return `<div class="p-3 text-center bg-light"><audio controls src="${safeUrl}" style="width:100%"></audio><div class="small mt-2">${label}</div></div>`;
                                    if (isOffice){
                                        const gview = 'https://docs.google.com/gview?embedded=1&url=' + encodeURIComponent(u);
                                        return `<iframe src="${gview}" class="d-block w-100" style="height:460px; border:0;" title="${label}"></iframe>`;
                                    }
                                    if (isText)  return `<iframe src="${safeUrl}" class="d-block w-100" style="height:460px; border:0; background:#fff;" title="${label}"></iframe>`;
                                    if (isCad)   return `<div class="p-4 text-center bg-light"><div class="mb-2">Formato CAD no previsualizable en el navegador.</div><a class="btn btn-outline-primary" href="${safeUrl}" target="_blank" rel="noopener">Descargar ${label}</a></div>`;
                                    return `<div class="p-4 text-center bg-light"><div class="mb-2">Previsualización no disponible para este formato.</div><a class="btn btn-outline-primary" href="${safeUrl}" target="_blank" rel="noopener">Abrir / Descargar</a></div>`;
                                };

                                files.forEach((f, idx)=>{
                                    const content = buildSlideContent(f.url, f.label);
                                    $inner.append(`<div class="carousel-item${idx===0?' active':''}">${content}<div class="carousel-caption d-none d-md-block"><span class="badge bg-dark">${f.label}</span></div></div>`);
                                });
                                $('#op-dis-archivos-na').hide();
                                $car.show();
                            }
                        }
                    })
                    .fail(function(xhr){
                        setText('#op-folio', '-');
                        setText('#op-status', 'Error HTTP ' + (xhr?.status || '?'));
                        setText('#op-cant', '-'); setText('#op-bultos', '-'); setText('#op-ini', '-'); setText('#op-fin', '-');
                        setText('#op-cliente', '-'); setText('#op-total', '-');
                        setText('#op-dis-codigo', '-'); setText('#op-dis-nombre', '-'); 
                        setText('#op-dis-descripcion', '-'); setText('#op-dis-version', '-'); 
                        setText('#op-dis-fecha', '-'); setText('#op-dis-aprobado', '-'); 
                        setText('#op-dis-precio', '-'); setText('#op-dis-notas', 'No se pudo cargar el detalle de la orden.');
                        console.error('OP detalle error', xhr?.status, xhr?.responseText);
                    })
                    .always(function(){
                        $btn.prop('disabled', false);
                    });
            });
        });
    </script>
    <?= $this->endSection() ?>
