<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Separación entre los botones de DataTables */
    .dt-buttons.btn-group .btn{
        margin-left: 0 !important;
        margin-right: .5rem;
        border-radius: .375rem !important;
    }
    .dt-buttons.btn-group .btn:last-child{ margin-right: 0; }

    .pedidos-actions{ flex-wrap: wrap; }
    @media (max-width: 576px){
        .pedidos-header{ flex-wrap: wrap; gap: .75rem; }
        .pedidos-actions{
            width: 100%;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .pedidos-actions > .btn{ flex: 0 0 auto; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4 pedidos-header">
    <h1 class="me-3">Pedidos</h1>
    <span class="badge bg-primary">Módulo 1</span>
    <div class="ms-auto d-flex gap-2 pedidos-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pedidoPdfModal">
            <i class="bi bi-file-arrow-up"></i> Cargar desde PDF
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#pedidoAddModal">
            <i class="bi bi-person-plus"></i> Agregar Pedido
        </button>
    </div>
</div>

<!-- Modal Bootstrap: Agregar pedido (primero datos del cliente) -->
<div class="modal fade" id="pedidoAddModal" tabindex="-1" aria-labelledby="pedidoAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="pedidoAddModalLabel">Agregar pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <!-- Paso 1: Cliente -->
                <div class="mb-3">
                    <h6 class="mb-2">Cliente</h6>
                    <div id="pa-select-wrap" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">Seleccionar cliente</label>
                            <select class="form-select" id="pa-cliente-select">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-4 text-end">
                            <div id="pa-cli-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                                <span class="visually-hidden"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <input type="hidden" id="pa-cliente-id" name="cliente_id" value="">
                        <div class="col-md-4">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="pa-cli-nombre" name="cli_nombre">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="pa-cli-email" name="cli_email">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="pa-cli-telefono" name="cli_telefono">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Paso 2: Domicilio principal -->
                <div class="mb-3">
                    <h6 class="mb-2">Domicilio</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Calle</label>
                            <input type="text" class="form-control" id="pa-dir-calle" name="cli_calle">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Num. Ext</label>
                            <input type="text" class="form-control" id="pa-dir-numext" name="cli_numext">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Num. Int</label>
                            <input type="text" class="form-control" id="pa-dir-numint" name="cli_numint">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="pa-dir-ciudad" name="cli_ciudad">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" id="pa-dir-estado" name="cli_estado">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" id="pa-dir-pais" name="cli_pais">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">CP</label>
                            <input type="text" class="form-control" id="pa-dir-cp" name="cli_cp">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Resumen</label>
                            <input type="text" class="form-control" id="pa-dir-resumen" name="cli_dir_resumen" readonly>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Paso 3: Clasificación -->
                <div class="mb-2">
                    <h6 class="mb-2">Clasificación</h6>
                    <div class="row g-3">
                        <input type="hidden" id="pa-cla-id" name="cla_id" value="">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="pa-cla-nombre" name="cla_nombre" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="pa-cla-descripcion" name="cla_descripcion" readonly>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Paso 4: Diseño -->
                <div class="mb-2">
                    <h6 class="mb-2">Diseño</h6>
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Seleccionar diseño</label>
                            <select class="form-select" id="pa-dis-select">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <div id="pa-dis-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                                <span class="visually-hidden"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="pa-dis-nombre" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Versión</label>
                            <input type="text" class="form-control" id="pa-dis-version" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha versión</label>
                            <input type="text" class="form-control" id="pa-dis-fecha" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio unidad</label>
                            <input type="number" step="0.01" class="form-control" id="pa-dis-precio" readonly>
                        </div>
                        <input type="hidden" id="pa-dis-version-id" name="pa_dis_version_id" value="">
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="pa-dis-descripcion" rows="2" readonly></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" id="pa-dis-notas" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-7">
                            <label class="form-label">Imágenes</label>
                            <div id="pa-dis-carousel-wrap" style="display:none;">
                                <div id="pa-dis-carousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner" id="pa-dis-carousel-inner"></div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#pa-dis-carousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Anterior</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#pa-dis-carousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Siguiente</span>
                                    </button>
                                </div>
                            </div>
                            <div id="pa-dis-noimg" class="text-muted" style="display:none;">Sin imágenes disponibles</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Lista de materiales</label>
                            <ul class="list-group" id="pa-dis-materiales"></ul>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Paso 5: Detalle por tallas -->
                <div class="mb-2">
                    <h6 class="mb-2">Detalle por tallas</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Captura las cantidades por sexo y talla para esta orden de producción.</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="op-tallas-add-row">Agregar línea</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" id="op-tallas-tabla">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%;">Sexo</th>
                                    <th style="width: 35%;">Talla</th>
                                    <th style="width: 20%;">Cantidad</th>
                                    <th style="width: 10%;">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Paso 6: Orden de Producción -->
                <div class="mb-2">
                    <h6 class="mb-2">Orden de Producción</h6>
                    <div class="row g-3">
                        <input type="hidden" id="op-folio" name="op_folio" value="">
                        <div class="col-md-3">
                            <label class="form-label">Cantidad plan</label>
                            <input type="number" min="1" step="1" class="form-control" id="op-cantidadPlan" name="op_cantidadPlan" placeholder="100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad de bultos</label>
                            <input type="number" min="1" step="1" class="form-control" id="op-cantidadBultos" name="op_cantidadBultos" placeholder="1">
                        </div>
                        <input type="hidden" id="op-status" name="op_status" value="Planeada">
                        <div class="col-md-3">
                            <label class="form-label">Inicio plan</label>
                            <input type="date" class="form-control" id="op-fechaInicioPlan" name="op_fechaInicioPlan" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fin plan</label>
                            <input type="date" class="form-control" id="op-fechaFinPlan" name="op_fechaFinPlan">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Paso 7: Orden de Compra -->
                <div class="mb-2">
                    <h6 class="mb-2">Orden de Compra</h6>
                    <div class="row g-3">
                        <input type="hidden" id="oc-clienteId" name="oc_clienteId" value="">
                        <input type="hidden" id="oc-estatus" name="oc_estatus" value="Pendiente">
                        <input type="hidden" id="oc-folio" name="oc_folio" value="">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="oc-fecha" name="oc_fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Moneda</label>
                            <select class="form-select" id="oc-moneda" name="oc_moneda">
                                <option value="MXN">MXN</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="oc-total" name="oc_total" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="pa-continuar">Continuar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Detalles del pedido -->
<div class="modal fade" id="pedidoModal" tabindex="-1" aria-labelledby="pedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pedidoModalLabel">
                    <i class="bi bi-file-text me-2"></i>Detalle del Pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información General -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información General</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Folio:</strong>
                                <p class="mb-2" id="p-folio">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Fecha:</strong>
                                <p class="mb-2" id="p-fecha">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Estatus:</strong>
                                <p class="mb-2"><span class="badge bg-secondary" id="p-estatus">-</span></p>
                            </div>
                            <div class="col-md-3">
                                <strong>Total:</strong>
                                <p class="mb-2 text-success fw-bold" id="p-total">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Cliente</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nombre/Empresa:</strong>
                                <p class="mb-2" id="p-cli-nombre">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Email:</strong>
                                <p class="mb-2" id="p-cli-email">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Teléfono:</strong>
                                <p class="mb-2" id="p-cli-telefono">-</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Dirección:</strong>
                                <p class="mb-0" id="p-dir-resumen">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalle por Tallas -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-grid-3x3 me-2"></i>Detalle por Tallas</h6>
                    </div>
                    <div class="card-body">
                        <div id="p-sin-tallas" class="alert alert-info mb-0">
                            No se han registrado tallas para este pedido.
                        </div>
                        <div id="p-con-tallas" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="p-tallas-detalle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sexo</th>
                                            <th>Talla</th>
                                            <th class="text-end">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Las filas se generarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Diseño y Producción -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Diseño y Producción</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Código Diseño:</strong>
                                <p class="mb-2" id="p-dis-codigo">-</p>
                            </div>
                            <div class="col-md-8">
                                <strong>Nombre:</strong>
                                <p class="mb-2" id="p-dis-nombre">-</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Descripción:</strong>
                                <p class="mb-2" id="p-dis-descripcion">-</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Versión:</strong>
                                <p class="mb-2" id="p-dis-version">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Precio Unitario:</strong>
                                <p class="mb-2 text-primary fw-bold" id="p-dis-precio">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Cantidad Planeada:</strong>
                                <p class="mb-2 fw-bold" id="p-op-cantidadPlan">-</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Fecha Entrega:</strong>
                                <p class="mb-2" id="p-op-fechaFinPlan">-</p>
                            </div>
                        </div>
                        
                        <!-- Imágenes del Diseño -->
                        <div class="row mt-3" id="p-dis-imagenes-container" style="display: none;">
                            <div class="col-12">
                                <hr>
                                <h6 class="mb-3"><i class="bi bi-images me-2"></i>Archivos del Diseño</h6>
                            </div>
                            <div class="col-md-6 text-center" id="p-dis-foto-container" style="display: none;">
                                <strong>Foto del Diseño:</strong>
                                <div class="mt-2">
                                    <img id="p-dis-foto" src="" alt="Foto del diseño" class="img-fluid rounded border" style="max-height: 300px;">
                                </div>
                            </div>
                            <div class="col-md-6 text-center" id="p-dis-patron-container" style="display: none;">
                                <strong>Patrón:</strong>
                                <div class="mt-2">
                                    <img id="p-dis-patron" src="" alt="Patrón del diseño" class="img-fluid rounded border" style="max-height: 300px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Editar pedido (Simplificado) -->
<div class="modal fade" id="pedidoEditModal" tabindex="-1" aria-labelledby="pedidoEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pedidoEditModalLabel">Editar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPedidoEditar" action="<?= base_url('modulo1/editar') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pe-id">
                    
                    <!-- Información General (Solo lectura) -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Folio</label>
                            <input type="text" class="form-control-plaintext" id="pe-folio" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Cliente</label>
                            <input type="text" class="form-control-plaintext" id="pe-cliente" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="text" class="form-control-plaintext" id="pe-cli-email" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" class="form-control-plaintext" id="pe-cli-telefono" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" class="form-control-plaintext" id="pe-cli-direccion" readonly>
                        </div>
                    </div>

                    <hr>

                    <!-- Campos Editables -->
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="pe-diseno" class="form-label">Diseño / Modelo</label>
                            <select class="form-select" id="pe-diseno" name="disenoId" required>
                                <option value=""></option>
                            </select>
                            <div class="form-text">Selecciona el diseño para este pedido.</div>
                        </div>

                        <!-- Detalles del Diseño Seleccionado -->
                        <div id="pe-detalles-diseno" class="col-12" style="display:none;">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 text-center">
                                            <img id="pe-preview-img" src="" alt="Diseño" class="img-fluid rounded shadow-sm" style="max-height: 120px; object-fit: cover;">
                                            <div id="pe-preview-noimg" class="text-muted small fst-italic mt-1">Sin imagen</div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="fw-bold mb-1" id="pe-preview-nombre"></h6>
                                            <p class="mb-1 small text-muted">Versión: <span id="pe-preview-version"></span> <span id="pe-preview-fecha"></span></p>
                                            
                                            <div class="mt-2">
                                                <span class="badge bg-secondary mb-1">Patrón:</span>
                                                <span id="pe-preview-patron-link">
                                                    <span class="text-muted small">No disponible</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <!-- Sección de Detalle por Tallas -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-grid-3x3 me-2"></i>Detalle por Tallas</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-text">Agrega o modifica las cantidades por talla</div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="pe-tallas-add-row">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar Talla
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="pe-tallas-tabla">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sexo</th>
                                            <th>Talla</th>
                                            <th class="text-end">Cantidad</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Las filas se generarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                        <div class="col-md-6">
                            <label for="pe-cantidad" class="form-label">Cantidad Planeada</label>
                            <input type="number" class="form-control" id="pe-cantidad" name="op_cantidadPlan" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="pe-cantidad-bultos" class="form-label">Cantidad de Bultos</label>
                            <input type="number" class="form-control" id="pe-cantidad-bultos" name="op_cantidadBultos" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="pe-fecha-fin" class="form-label">Fecha Fin Planeada</label>
                            <input type="date" class="form-control" id="pe-fecha-fin" name="op_fechaFinPlan" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Precio Unitario</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" id="pe-precio-unitario" readonly value="0.00">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Estimado</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control fw-bold" id="pe-total-estimado" readonly value="0.00">
                            </div>
                            <div class="form-text">Calculado basado en el precio del diseño y la cantidad.</div>
                        </div>
                    </div>

                   
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Documento PDF del pedido -->
<div class="modal fade" id="pedidoDocumentoModal" tabindex="-1" aria-labelledby="pedidoDocumentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pedidoDocumentoModalLabel">Documento del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="pedido-documento-loading" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <p class="mt-2"></p>
                </div>
                <iframe id="pedido-documento-iframe" src="" style="width: 100%; height: 80vh; border: none; display: none;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="pedido-documento-download-pdf" href="#" class="btn btn-primary" download>
                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                </a>
                <a id="pedido-documento-download-excel" href="#" class="btn btn-success" download>
                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cargar pedido desde PDF -->
<div class="modal fade" id="pedidoPdfModal" tabindex="-1" aria-labelledby="pedidoPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pedidoPdfModalLabel">Cargar pedido desde PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Selecciona el PDF del pedido (ficha técnica). El sistema leerá el archivo y mostrará aquí los datos detectados (sin guardar nada todavía).</p>
                <input type="file" class="form-control mb-3" id="pedido-pdf-input" accept="application/pdf">

                <div id="pedido-pdf-result" style="display:none;">
                    <hr>
                    <div class="mb-3" id="pedido-pdf-img-wrap" style="display:none;">
                        <label class="form-label">Imágenes del PDF</label>
                        <div class="d-flex flex-wrap gap-2" id="pedido-pdf-imgs"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Cliente (PDF)</label>
                            <input type="text" class="form-control" id="pdf-cli-nombre">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo (PDF)</label>
                            <input type="text" class="form-control" id="pdf-modelo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha (PDF)</label>
                            <input type="text" class="form-control" id="pdf-fecha">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Modelo cliente (PDF)</label>
                            <input type="text" class="form-control" id="pdf-modelo-cliente">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cantidad plan (Total tallas, PDF)</label>
                            <input type="text" class="form-control" id="pdf-cantidad-plan">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nombre de diseño (PDF)</label>
                        <input type="text" class="form-control" id="pdf-diseno-nombre">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descripción (PDF)</label>
                        <textarea class="form-control" id="pdf-diseno-descripcion" rows="4"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Notas / Etiquetas (PDF)</label>
                        <textarea class="form-control" id="pdf-diseno-notas" rows="3"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tallas (bloque de texto PDF)</label>
                        <textarea class="form-control" id="pdf-tallas-texto" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="pedido-pdf-usar">Usar estos datos en el pedido</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Pedidos</strong>
    </div>
    <div class="card-body">
        <table id="tablaPedidos" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>Folio</th>
                <th>Empresa</th>
                <th>Fecha</th>
                <th>Estatus</th>
                <th>Fecha Entrega</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($pedidos)): ?>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= esc($p['id']) ?></td>
                        <td><?= esc($p['folio'] ?? '-') ?></td>
                        <td><?= esc($p['empresa'] ?? '-') ?></td>
                        <td><?= isset($p['fecha']) ? esc(date('Y-m-d', strtotime($p['fecha']))) : '-' ?></td>
                        <td>
                            <?php
                            $badgeClass = 'secondary';
                            $estatus = strtolower($p['estatus'] ?? 'pendiente');
                            if (strpos($estatus, 'aprobada') !== false || strpos($estatus, 'finalizada') !== false) {
                                $badgeClass = 'success';
                            } elseif (strpos($estatus, 'pendiente') !== false) {
                                $badgeClass = 'warning';
                            } elseif (strpos($estatus, 'cancelada') !== false || strpos($estatus, 'rechazada') !== false) {
                                $badgeClass = 'danger';
                            } elseif (strpos($estatus, 'proceso') !== false || strpos($estatus, 'en curso') !== false) {
                                $badgeClass = 'info';
                            }
                            ?>
                            <span class="badge bg-<?= $badgeClass ?>"><?= esc(ucfirst($p['estatus'] ?? 'Pendiente')) ?></span>
                        </td>
                        <td><?= isset($p['fechaFinPlan']) ? esc(date('Y-m-d', strtotime($p['fechaFinPlan']))) : '-' ?></td>
                        <td><?= isset($p['total']) ? number_format((float)$p['total'], 2) : '0.00' ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info btn-ver-pedido"
                                    data-id="<?= (int)$p['id'] ?>" data-bs-toggle="modal" data-bs-target="#pedidoModal">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-editar-pedido" 
                                    data-id="<?= (int)$p['id'] ?>" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-ver-documento"
                                    data-id="<?= (int)$p['id'] ?>" data-bs-toggle="modal" data-bs-target="#pedidoDocumentoModal" title="Ver documento PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-pedido" data-id="<?= (int)$p['id'] ?>" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
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
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Variables globales para los catálogos
let sexos = [];
let tallas = [];
// Prefill desde PDF (se usa al abrir el modal Agregar pedido)
let paPdfPrefill = null;

// Función para cargar los catálogos de sexos y tallas (filtrados por maquiladora)
function cargarCatalogosTallas() {
    return new Promise((resolve) => {
        let sexosLoaded = false;
        let tallasLoaded = false;

        const checkIfLoaded = () => {
            if (sexosLoaded && tallasLoaded) {
                resolve();
            }
        };

        // Cargar sexos desde CatalogoDisenos::catalogoSexo (solo de la maquiladora)
        if (sexos.length === 0) {
            $.getJSON('<?= base_url('modulo2/catalogos/sexo') ?>', { solo_maquiladora: 1 })
                .done(function(data) {
                    const items = Array.isArray(data?.items) ? data.items : (Array.isArray(data) ? data : []);
                    sexos = items.map(it => ({
                        id_sexo: it.id_sexo ?? it.id,
                        nombre: it.nombre ?? '',
                        descripcion: it.descripcion ?? ''
                    })).filter(it => it.id_sexo);
                    console.log('Catálogo de sexos cargado:', sexos);

                    // NO mostrar fallback si no hay registros para esta maquiladora
                    if (sexos.length === 0) {
                        console.log('No hay sexos configurados para esta maquiladora');
                        // No agregar valores predeterminados
                    }
                    sexosLoaded = true;
                    checkIfLoaded();
                })
                .fail(function() {
                    console.error('Error al cargar el catálogo de sexos');
                    // NO mostrar fallback en caso de error
                    console.log('No se pudieron cargar los sexos');
                    sexosLoaded = true;
                    checkIfLoaded();
                });
        } else {
            sexosLoaded = true;
            checkIfLoaded();
        }

        // Cargar tallas desde CatalogoDisenos::catalogoTallas (solo de la maquiladora)
        if (tallas.length === 0) {
            $.getJSON('<?= base_url('modulo2/catalogos/tallas') ?>', { solo_maquiladora: 1 })
                .done(function(data) {
                    const items = Array.isArray(data?.items) ? data.items : (Array.isArray(data) ? data : []);
                    tallas = items.map(it => ({
                        id_talla: it.id_talla ?? it.id,
                        nombre: it.nombre ?? '',
                        descripcion: it.descripcion ?? ''
                    })).filter(it => it.id_talla);
                    console.log('Catálogo de tallas cargado:', tallas);

                    // NO mostrar fallback si no hay registros para esta maquiladora
                    if (tallas.length === 0) {
                        console.log('No hay tallas configuradas para esta maquiladora');
                        // No agregar valores predeterminados
                    }
                    tallasLoaded = true;
                    checkIfLoaded();
                })
                .fail(function() {
                    console.error('Error al cargar el catálogo de tallas');
                    // NO mostrar fallback en caso de error
                    console.log('No se pudieron cargar las tallas');
                    tallasLoaded = true;
                    checkIfLoaded();
                });
        } else {
            tallasLoaded = true;
            checkIfLoaded();
        }
    });
}

// Función para agregar una nueva fila de talla
function agregarFilaTallaEditar(sexoId = '', tallaId = '', cantidad = '', nombreSexo = '', nombreTalla = '') {
    // Crear opciones de sexo
    let opcionesSexo = '<option value="">Seleccionar...</option>';
    let selectedSexo = null;
    
    sexos.forEach(function(sexo) {
        const selected = (sexoId && sexoId == sexo.id_sexo) ? 'selected' : '';
        opcionesSexo += `<option value="${sexo.id_sexo}" ${selected}>${sexo.nombre}</option>`;
        if (selected) selectedSexo = sexo.nombre;
    });

    // Si no se encontró el sexo en los catálogos pero tenemos el nombre, lo agregamos
    if (sexoId && !selectedSexo && nombreSexo) {
        opcionesSexo = `<option value="${sexoId}" selected>${nombreSexo}</option>` + opcionesSexo;
    }

    // Crear opciones de talla
    let opcionesTalla = '<option value="">Seleccionar...</option>';
    let selectedTalla = null;
    
    tallas.forEach(function(talla) {
        const selected = (tallaId && tallaId == talla.id_talla) ? 'selected' : '';
        opcionesTalla += `<option value="${talla.id_talla}" ${selected}>${talla.nombre}</option>`;
        if (selected) selectedTalla = talla.nombre;
    });

    // Si no se encontró la talla en los catálogos pero tenemos el nombre, lo agregamos
    if (tallaId && !selectedTalla && nombreTalla) {
        opcionesTalla = `<option value="${tallaId}" selected>${nombreTalla}</option>` + opcionesTalla;
    }

    const fila = `
        <tr class="pe-talla-row">
            <td>
                <select class="form-select form-select-sm pe-talla-sexo" name="tallas[][id_sexo]" required>
                    ${opcionesSexo}
                </select>
                ${sexoId && nombreSexo && !selectedSexo ? `<input type="hidden" name="tallas[][nombre_sexo]" value="${nombreSexo}">` : ''}
            </td>
            <td>
                <select class="form-select form-select-sm pe-talla-talla" name="tallas[][id_talla]" required>
                    ${opcionesTalla}
                </select>
                ${tallaId && nombreTalla && !selectedTalla ? `<input type="hidden" name="tallas[][nombre_talla]" value="${nombreTalla}">` : ''}
            </td>
            <td>
                <input type="number" min="0" class="form-control form-control-sm text-end pe-talla-cantidad" 
                       name="tallas[][cantidad]" value="${cantidad || ''}" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger pe-talla-del">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#pe-tallas-tabla tbody').append(fila);

    // Actualizar cantidad planeada al agregar una fila nueva
    if (typeof actualizarCantidadPlanEditarDesdeTallas === 'function') {
        actualizarCantidadPlanEditarDesdeTallas();
    }
}

// Función para cargar las tallas en el formulario de edición
function cargarTallasEnEdicion(tallas) {
    const $tbody = $('#pe-tallas-tabla tbody');
    $tbody.empty();
    
    // Función para agregar filas una vez que los catálogos estén listos
    const agregarFilas = () => {
        if (tallas && tallas.length > 0) {
            console.log('', tallas);
            tallas.forEach(talla => {
                // Aceptar tanto nombre_sexo/nombre_talla como sexo_nombre/talla_nombre
                const nombreSexo  = talla.nombre_sexo || talla.sexo_nombre || '';
                const nombreTalla = talla.nombre_talla || talla.talla_nombre || '';

                if (nombreSexo && nombreTalla) {
                    // Si ya vienen los nombres, usarlos directamente
                    agregarFilaTallaEditar(
                        talla.id_sexo,
                        talla.id_talla,
                        talla.cantidad,
                        nombreSexo,
                        nombreTalla
                    );
                } else {
                    // Si no vienen los nombres, buscar en los catálogos
                    const sexo = sexos.find(s => s.id_sexo == talla.id_sexo);
                    const tallaItem = window.tallas.find(t => t.id_talla == talla.id_talla);

                    agregarFilaTallaEditar(
                        talla.id_sexo,
                        talla.id_talla,
                        talla.cantidad,
                        sexo ? sexo.nombre : '',
                        tallaItem ? tallaItem.nombre : ''
                    );
                }
            });
        } else {
            console.log('No hay tallas, agregando fila vacía');
            // Agregar una fila vacía por defecto
            agregarFilaTallaEditar();
        }
    };

    // Verificar si los catálogos ya están cargados
    if (sexos.length > 0 && tallas.length > 0) {
        agregarFilas();
    } else {
        // Si no están cargados, cargarlos primero
        console.log('');
        cargarCatalogosTallas().then(agregarFilas).catch(error => {
            console.error('Error al cargar catálogos:', error);
            // Intentar de todos modos con lo que tengamos
            agregarFilas();
        });
    }
}

// Actualizar "Cantidad Planeada" (pe-cantidad) a partir de las cantidades por talla en edición
function actualizarCantidadPlanEditarDesdeTallas(){
    let suma = 0;
    $('#pe-tallas-tabla tbody tr.pe-talla-row').each(function(){
        const cantidad = parseInt($(this).find('.pe-talla-cantidad').val() || '0', 10);
        if (!isNaN(cantidad) && cantidad > 0) {
            suma += cantidad;
        }
    });
    const $cantidadPlan = $('#pe-cantidad');
    if ($cantidadPlan.length) {
        $cantidadPlan.val(suma || '');
        // Disparar evento para que se recalcule el total estimado
        $cantidadPlan.trigger('input');
    }
}

// Modificar el manejador del formulario para incluir las tallas
$('#formPedidoEditar').on('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    // Recolectar datos de las tallas
    const tallasData = [];
    $('.pe-talla-row').each(function() {
        const $row = $(this);
        tallasData.push({
            id_sexo: $row.find('.pe-talla-sexo').val(),
            id_talla: $row.find('.pe-talla-talla').val(),
            cantidad: $row.find('.pe-talla-cantidad').val()
        });
    });
    
    // Agregar las tallas al FormData
    formData.append('tallas', JSON.stringify(tallasData));
    
    // Mostrar indicador de carga
    const $submitBtn = $(form).find('button[type="submit"]');
    const originalBtnText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span> Guardando...');
    
    // Enviar el formulario
    $.ajax({
        url: form.action,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.fire({
                title: '¡Éxito!',
                text: 'El pedido se ha actualizado correctamente',
                icon: 'success'
            }).then(() => {
                $('#pedidoEditModal').modal('hide');
                // Recargar la página o actualizar la tabla
                if (typeof tablePedidos !== 'undefined') {
                    tablePedidos.ajax.reload(null, false);
                } else {
                    location.reload();
                }
            });
        },
        error: function(xhr) {
            let errorMessage = 'No se pudo actualizar el pedido. Por favor, inténtalo de nuevo.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire('Error', errorMessage, 'error');
        },
        complete: function() {
            $submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});

// Cargar los catálogos cuando el documento esté listo
    cargarCatalogosTallas();
    
    // También cargar catálogos cuando se muestre el modal de edición
    $('#pedidoEditModal').on('show.bs.modal', function() {
        cargarCatalogosTallas();
    });
$(document).ready(function () {
        function toNum(v, def=0){
            if (v === undefined || v === null) return def;
            const s = String(v).replace(/,/g,'').trim();
            const n = parseFloat(s);
            return isNaN(n) ? def : n;
        }
        function recalcTotal(){
            // Obtener cantidad: priorizar Edit (#pe-op-cantidadPlan) si tiene valor, luego Add (#op-cantidadPlan)
            const peCantVal = $('#pe-op-cantidadPlan').length ? $('#pe-op-cantidadPlan').val() : undefined;
            const paCantVal = $('#op-cantidadPlan').length ? $('#op-cantidadPlan').val() : undefined;
            let cant = toNum(peCantVal, NaN);
            if (isNaN(cant) || cant === 0) {
                cant = toNum(paCantVal, 0);
            }

            // Obtener precio: priorizar Edit (#pe-dis-precio) si tiene valor, luego Add (#pa-dis-precio)
            const pePrecioVal = $('#pe-dis-precio').length ? $('#pe-dis-precio').val() : undefined;
            const paPrecioVal = $('#pa-dis-precio').length ? $('#pa-dis-precio').val() : undefined;
            let precio = toNum(pePrecioVal, NaN);
            if (isNaN(precio) || precio === 0) {
                precio = toNum(paPrecioVal, 0);
            }

            // Recalcular siempre (permite ver 0.00 si falta alguno)
            const total = cant * precio;
            if ($('#oc-total').length) { $('#oc-total').val(total.toFixed(2)); }
            if ($('#pe-total').length) { $('#pe-total').val(total.toFixed(2)); }
        }

        let paSexoCache = null;
        let paTallasCache = null;

        function paFillSexoOptions($sel){
            if (!$sel || !$sel.length) return;
            $sel.empty().append('<option value="">Seleccionar...</option>');
            if (!Array.isArray(paSexoCache)) return;
            paSexoCache.forEach(function(it){
                const id = it.id ?? it.id_sexo ?? '';
                const nombre = (it.nombre || '').toString();
                if (!id) return;
                $sel.append('<option value="'+ id +'">'+ nombre +'</option>');
            });
        }

        function paFillTallaOptions($sel){
            if (!$sel || !$sel.length) return;
            $sel.empty().append('<option value="">Seleccionar...</option>');
            if (!Array.isArray(paTallasCache)) return;
            paTallasCache.forEach(function(it){
                const id = it.id ?? it.id_talla ?? '';
                const nombre = (it.nombre || '').toString();
                if (!id) return;
                $sel.append('<option value="'+ id +'">'+ nombre +'</option>');
            });
        }

        // Función para calcular y actualizar cantidad plan desde las tallas
        function actualizarCantidadPlanDesdeTallas(){
            let suma = 0;
            $('#op-tallas-tabla tbody tr.op-talla-row').each(function(){
                const cantidad = parseInt($(this).find('.op-talla-cantidad').val() || '0', 10);
                if (!isNaN(cantidad) && cantidad > 0) {
                    suma += cantidad;
                }
            });
            const $cantidadPlan = $('#op-cantidadPlan');
            if ($cantidadPlan.length) {
                $cantidadPlan.val(suma || '');
                // Disparar evento para recalcular total
                $cantidadPlan.trigger('input');
            }
        }

        function opAddTallaRow(){
            const $tbody = $('#op-tallas-tabla tbody');
            if (!$tbody.length) { return; }
            const rowHtml =
                '<tr class="op-talla-row">' +
                '  <td>' +
                '    <select class="form-select form-select-sm op-talla-sexo" name="op_tallas[][id_sexo]"></select>' +
                '  </td>' +
                '  <td>' +
                '    <select class="form-select form-select-sm op-talla-talla" name="op_tallas[][id_talla]"></select>' +
                '  </td>' +
                '  <td>' +
                '    <input type="number" min="1" step="1" class="form-control form-control-sm op-talla-cantidad" name="op_tallas[][cantidad]" value="">' +
                '  </td>' +
                '  <td class="text-center">' +
                '    <button type="button" class="btn btn-sm btn-outline-danger op-talla-del">&times;</button>' +
                '  </td>' +
                '</tr>';
            const $row = $(rowHtml).appendTo($tbody);
            paFillSexoOptions($row.find('.op-talla-sexo'));
            paFillTallaOptions($row.find('.op-talla-talla'));
            // Actualizar cantidad plan después de agregar fila
            actualizarCantidadPlanDesdeTallas();
        }

        $(document).on('click', '#op-tallas-add-row', function(){
            opAddTallaRow();
        });

        $(document).on('click', '.op-talla-del', function(){
            const $row = $(this).closest('tr');
            $row.remove();
            // Actualizar cantidad plan después de eliminar fila
            actualizarCantidadPlanDesdeTallas();
        });

        // Actualizar cantidad plan cuando cambie cualquier cantidad de talla
        $(document).on('input change blur', '.op-talla-cantidad', function(){
            actualizarCantidadPlanDesdeTallas();
        });
        const langES = {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_",
            "sInfoEmpty":      "Mostrando 0 a 0 de 0",
            "sInfoFiltered":   "(filtrado de _MAX_)",
            "sSearch":         "Buscar:",
            "sLoadingRecords": "",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "buttons": { "copy": "Copiar", "colvis": "Columnas" }
        };
        const fecha = new Date().toISOString().slice(0,10);
        const fileName = 'pedidos_' + fecha;

        const dtPedidos = $('#tablaPedidos').DataTable({
            language: langES,
            columnDefs: [{ targets: -1, orderable:false, searchable:false }], // Acciones
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

        // Eliminar Pedido (OC + cascada OP/inspeccion/reproceso/asignaciones)
        $(document).on('click', '.btn-eliminar-pedido', function(){
            const $btn = $(this);
            const id = parseInt($btn.data('id')||0,10);
            if (!id) return;
            Swal.fire({
                title: '¿Eliminar pedido?',
                text: 'Se eliminará la Orden de Compra y sus datos relacionados (OP, inspección, reproceso y asignaciones).',
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
                    url: '<?= base_url('modulo1/pedidos/eliminar') ?>',
                    method: 'POST',
                    data: { id },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).done(function(resp){
                    Swal.fire({ icon:'success', title:'Eliminado', text:'Pedido eliminado correctamente.', timer:1400, showConfirmButton:false });
                    try { dtPedidos.row($btn.closest('tr')).remove().draw(false); } catch(e) { $btn.closest('tr').remove(); }
                }).fail(function(xhr){
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo eliminar el pedido.' });
                }).always(function(){
                    $btn.prop('disabled', false);
                });
            });
        });

        // Guardar pedido (OC + OP) con confirmación SweetAlert y bloqueo anti-duplicados
        let paSubmitting = false;
        $(document).on('click', '#pa-continuar', function(){
            if (paSubmitting) { return; }
            const $btn = $(this);
            const originalText = $btn.text();
            $btn.prop('disabled', true).text('Guardando...');
            const selDis = ($('#pa-dis-select').length ? $('#pa-dis-select') : $('#pe-dis-select'));
            const disIdSel = selDis.find('option:selected').data('id') || selDis.val() || '';
            const payload = {
                oc_clienteId:      $('#oc-clienteId').val(),
                oc_estatus:        $('#oc-estatus').val(),
                oc_folio:          $('#oc-folio').val(),
                oc_fecha:          $('#oc-fecha').val(),
                oc_moneda:         $('#oc-moneda').val(),
                oc_total:          $('#oc-total').val(),
                op_folio:          $('#op-folio').val(),
                op_cantidadPlan:   $('#op-cantidadPlan').val(),
                op_cantidadBultos: $('#op-cantidadBultos').val(),
                op_fechaInicioPlan:$('#op-fechaInicioPlan').val(),
                op_fechaFinPlan:   $('#op-fechaFinPlan').val(),
                op_status:         $('#op-status').val(),
                disenoVersionId:   ($('#pa-dis-version-id').val() || $('#pe-dis-version-id').val()),
                disenoId:          disIdSel
            };

            payload.tallas = [];
            $('#op-tallas-tabla tbody tr.op-talla-row').each(function(){
                const id_sexo = $(this).find('.op-talla-sexo').val();
                const id_talla = $(this).find('.op-talla-talla').val();
                const cantidad = parseInt($(this).find('.op-talla-cantidad').val() || '0', 10);
                if (id_sexo && id_talla && cantidad > 0){
                    payload.tallas.push({ id_sexo: id_sexo, id_talla: id_talla, cantidad: cantidad });
                }
            });

            // Validaciones rápidas
            if (!payload.oc_clienteId) { 
                Swal.fire({ icon:'warning', title:'Validación', text:'Selecciona un cliente.' });
                $btn.prop('disabled', false).text(originalText); 
                return; 
            }
            if (!payload.disenoVersionId) { 
                const $sel = $('#pa-dis-select').length ? $('#pa-dis-select') : $('#pe-dis-select');
                const verIdOpt = $sel.find('option:selected').data('versionId') || $sel.find('option:selected').data('verId') || '';
                if (verIdOpt) {
                    payload.disenoVersionId = verIdOpt;
                    confirmAndPost();
                    return;
                }
                const disId = $sel.find('option:selected').data('id') || $sel.val();
                if (!disId) { 
                    Swal.fire({ icon:'warning', title:'Validación', text:'Selecciona un diseño.' });
                    $btn.prop('disabled', false).text(originalText); 
                    return; 
                }
                const url = '<?= base_url('modulo2/diseno') ?>/' + disId + '/json?t=' + Date.now();
                $.getJSON(url).done(function(data){
                    let verObj = null;
                    if (data && typeof data.version === 'object') verObj = data.version;
                    else if (data && typeof data.ultima_version === 'object') verObj = data.ultima_version;
                    else if (Array.isArray(data?.versiones) && data.versiones.length) {
                        // tomar la más reciente (última)
                        verObj = data.versiones[data.versiones.length - 1];
                    } else if (data && typeof data.diseno_version === 'object') verObj = data.diseno_version;
                    const verId = verObj ? (verObj.id ?? verObj.versionId ?? verObj.disenoVersionId ?? verObj.diseno_version_id ?? null)
                                          : (data.versionId ?? data.disenoVersionId ?? data.diseno_version_id ?? null);
                    if (!verId) {
                        // Fallback: continuar y que backend resuelva con disenoId
                        payload.disenoVersionId = '';
                        confirmAndPost();
                        return;
                    }
                    payload.disenoVersionId = verId;
                    if ($('#pa-dis-version-id').length) { $('#pa-dis-version-id').val(verId); }
                    if ($('#pe-dis-version-id').length) { $('#pe-dis-version-id').val(verId); }
                    confirmAndPost();
                }).fail(function(){
                    // Si tenemos disenoId, continuar y backend resuelve
                    if (disId) { payload.disenoVersionId = ''; confirmAndPost(); return; }
                    Swal.fire({ icon:'warning', title:'Validación', text:'Selecciona un diseño.' });
                    $btn.prop('disabled', false).text(originalText);
                });
                return;
            }

            // Confirmación con SweetAlert2 para agregar pedido
            function confirmAndPost(){
                Swal.fire({
                  title: '¿Agregar pedido?',
                  text: 'Se guardará el pedido. Puedes confirmar o seguir editando.',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Sí, guardar',
                  cancelButtonText: 'Seguir editando',
                  reverseButtons: true
                }).then((result) => {
                  if (result.isConfirmed) {
                    paSubmitting = true;
                    $.ajax({
                        url: '<?= base_url('modulo1/pedidos/crear') ?>',
                        method: 'POST',
                        data: payload,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function(resp){
                            if (resp && (resp.ok === true || resp.success === true)) {
                                // Intentar agregar fila sin recargar; si no hay ID, recargar
                                const newId = resp.id || resp.pedidoId || resp.oc_id || resp.ocId || resp.orderId || null;
                                const finalizeSuccess = function(){
                                    try { $('#pedidoAddModal').modal('hide'); } catch(e) {}
                                    Swal.fire({
                                      title: '¡Pedido agregado!',
                                      text: 'El pedido se guardó correctamente.',
                                      icon: 'success',
                                      confirmButtonText: 'Aceptar'
                                    }).then(() => {
                                      // Si no se agregó fila (por falta de datos), recargar
                                      if (!finalizeSuccess.rowAdded) { location.reload(); }
                                    });
                                };
                                finalizeSuccess.rowAdded = false;

                                if (newId) {
                                    const urlDetalle = '<?= base_url('modulo1/pedido') ?>/' + newId + '/json?t=' + Date.now();
                                    $.getJSON(urlDetalle).done(function(data){
                                        const id = data?.id || newId;
                                        const empresa = (data?.empresa) || (data?.cliente?.nombre) || '-';
                                        const folio = data?.folio || payload.oc_folio || '-';
                                        const fechaV = data?.fecha || payload.oc_fecha || '';
                                        const fechaFmt = fechaV ? (new Date(fechaV).toISOString().slice(0,10)) : '-';
                                        const estatus = data?.estatus || payload.oc_estatus || 'Pendiente';
                                        const fechaFinPlanV = data?.op_fechaFinPlan || payload.op_fechaFinPlan || '';
                                        const fechaFinPlanFmt = fechaFinPlanV ? (new Date(fechaFinPlanV).toISOString().slice(0,10)) : '-';
                                        const total = (data?.total ?? payload.oc_total ?? 0);
                                        const totalFmt = (parseFloat(total)||0).toFixed(2);
                                        const acciones = `
                                            <button type="button" class="btn btn-sm btn-outline-info btn-ver-pedido" data-id="${id}" data-bs-toggle="modal" data-bs-target="#pedidoModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('modulo1/editar') ?>/${id}" role="button" onclick="return false;">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <span class="text-muted">—</span>`;
                                        try {
                                            dtPedidos.row.add([id, folio, empresa, fechaFmt, estatus, fechaFinPlanFmt, totalFmt, acciones]).draw(false);
                                            finalizeSuccess.rowAdded = true;
                                        } catch(e) {
                                            finalizeSuccess.rowAdded = false;
                                        }
                                        finalizeSuccess();
                                    }).fail(function(){
                                        finalizeSuccess();
                                    });
                                } else {
                                    finalizeSuccess();
                                }
                            } else {
                                const msg = resp && (resp.message || resp.msg) ? (resp.message||resp.msg) : 'Error al crear el pedido';
                                Swal.fire({ icon:'error', title:'Error', text: msg });
                            }
                        },
                        error: function(xhr){
                            const msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error de conexión al guardar';
                            Swal.fire({ icon:'error', title:'Error', text: msg });
                        },
                        complete: function(){
                            paSubmitting = false;
                            $btn.prop('disabled', false).text(originalText);
                        }
                    });
                  } else {
                    $btn.prop('disabled', false).text(originalText);
                  }
                });
            }

            confirmAndPost();
        });

        // Manejar clic en el botón de agregar fila de talla (editar)
        $(document).on('click', '#pe-tallas-add-row', function() {
            agregarFilaTallaEditar();
        });

        // Manejar eliminación de fila de talla (editar)
        $(document).on('click', '.pe-talla-del', function() {
            $(this).closest('tr').remove();
            if (typeof actualizarCantidadPlanEditarDesdeTallas === 'function') {
                actualizarCantidadPlanEditarDesdeTallas();
            }
        });

        // Cuando cambie cualquier cantidad por talla en edición, actualizar la suma
        $(document).on('input change keyup mouseup blur', '.pe-talla-cantidad', function(){
            if (typeof actualizarCantidadPlanEditarDesdeTallas === 'function') {
                actualizarCantidadPlanEditarDesdeTallas();
            }
        });

        // Recalcular total cuando cambie cantidad plan o si manualmente cambian el precio (aunque es de solo lectura)
        $(document).on('input change keyup mouseup blur', '#op-cantidadPlan', recalcTotal);
        $(document).on('input change keyup mouseup blur', '#pe-op-cantidadPlan', recalcTotal);
        
        // Cargar los catálogos de tallas y sexos al iniciar
        cargarCatalogosTallas();
        $(document).on('input change keyup mouseup blur', '#pa-dis-precio', recalcTotal);
        $(document).on('input change keyup mouseup blur', '#pe-dis-precio', recalcTotal);

        // (Estatus inline removido aquí; se implementará en m1_ordenes.php)

        // Manejar selección de diseño (ambos selects soportados)
        $(document).on('change', '#pa-dis-select, #pe-dis-select', function(){
            const $opt = $(this).find('option:selected');
            const id = $opt.data('id');
            const key = $(this).val();
            // Limpiar UI
            const $carWrap = $('#pa-dis-carousel-wrap');
            const $carInner = $('#pa-dis-carousel-inner');
            const $noimg = $('#pa-dis-noimg');
            const $matList = $('#pa-dis-materiales');
            $carInner.empty();
            $carWrap.hide();
            $noimg.hide();
            $matList.empty();
            $('#pa-dis-nombre, #pa-dis-version, #pa-dis-fecha, #pa-dis-descripcion, #pa-dis-notas').val('');
            if ($('#pa-dis-version-id').length) { $('#pa-dis-version-id').val(''); }

            if (!id) { return; }
            $('#pa-dis-loading').show();
            const url = '<?= base_url('modulo1/disenos') ?>/' + id + '/json?t=' + Date.now();
            $.getJSON(url)
                .done(function(data){
                    // Campos básicos
                    const nombre = pick(data, ['nombre','Nombre'], '');
                    const descripcion = pick(data, ['descripcion','Descripcion'], '');
                    let verObj = null;
                    if (data && typeof data.version === 'object') verObj = data.version;
                    else if (data && typeof data.ultima_version === 'object') verObj = data.ultima_version;
                    else if (Array.isArray(data?.versiones) && data.versiones.length) { verObj = data.versiones[data.versiones.length-1]; }
                    else if (data && typeof data.diseno_version === 'object') verObj = data.diseno_version;
                    const version = verObj ? (verObj.version ?? verObj.ver ?? '') : (data.version ?? '');
                    const fecha = verObj ? (verObj.fecha ?? '') : (data.fecha ?? '');
                    const notas = verObj ? (verObj.notas ?? '') : (data.notas ?? '');
                    $('#pa-dis-nombre').val(nombre || '');
                    $('#pa-dis-descripcion').val(descripcion || '');
                    $('#pa-dis-version').val(version || '');
                    if (fecha){
                        const d = new Date(fecha);
                        $('#pa-dis-fecha').val(isNaN(d) ? String(fecha).slice(0,10) : d.toISOString().slice(0,10));
                    } else {
                        $('#pa-dis-fecha').val('');
                    }
                    $('#pa-dis-notas').val(notas || '');
                    // Guardar ID de versión de diseño
                    const verId = verObj ? (verObj.id ?? verObj.versionId ?? verObj.disenoVersionId ?? verObj.diseno_version_id ?? null)
                                          : (data.versionId ?? data.disenoVersionId ?? data.diseno_version_id ?? null);
                    if (verId) { if ($('#pa-dis-version-id').length) { $('#pa-dis-version-id').val(verId); } if ($('#pe-dis-version-id').length) { $('#pe-dis-version-id').val(verId); } }
                    // Precio unidad
                    const precio = (data && (data.precio_unidad ?? data.precioUnidad)) ?? (verObj && (verObj.precio_unidad ?? verObj.precioUnidad)) ?? 0;
                    if ($('#pa-dis-precio').length) { $('#pa-dis-precio').val(precio || 0); }
                    if ($('#pe-dis-precio').length) { $('#pe-dis-precio').val(precio || 0); }
                    recalcTotal();

                    // Imágenes y archivos (tolerante)
                    let imgs = [];
                    const tryArrays = [
                        data?.imagenes, data?.images, data?.fotos,
                        verObj?.imagenes, verObj?.images, verObj?.fotos
                    ];
                    for (let i=0;i<tryArrays.length;i++){
                        if (Array.isArray(tryArrays[i]) && tryArrays[i].length){ imgs = tryArrays[i]; break; }
                    }
                    const isImage = (u) => /\.(png|jpe?g|gif|bmp|webp|svg)$/i.test(u||'');
                    const isPdf   = (u) => /\.(pdf)$/i.test(u||'');
                    const isDxf   = (u) => /\.(dxf)$/i.test(u||'');
                    let anySlide = false;
                    imgs.forEach(function(it, idx){
                        const url = typeof it === 'string' ? it : (it.url || it.src || '');
                        if (!url) return;
                        const active = anySlide ? '' : ' active';
                        $carInner.append(
                            '<div class="carousel-item'+active+'">\n' +
                            '  <img class="d-block w-100" src="'+ url +'" alt="imagen">\n' +
                            '</div>'
                        );
                        anySlide = true;
                    });

                    const cadUrl = verObj?.archivoCadUrl || data?.archivoCadUrl || '';
                    const patronUrl = verObj?.archivoPatronUrl || data?.archivoPatronUrl || '';
                    const addFileSlide = (url, label) => {
                        if (!url) return;
                        const active = anySlide ? '' : ' active';
                        let html = '';
                        if (isImage(url)) {
                            html = '<img class="d-block w-100" src="'+url+'" alt="'+label+'" />';
                        } else if (isPdf(url)) {
                            html = '<object data="'+url+'" type="application/pdf" width="100%" height="420"><div class="text-muted p-3">No se pudo mostrar el PDF '+label+'.</div></object>';
                        } else if (isDxf(url)) {
                            html = '<div class="p-3 text-muted">Archivo DXF: <a target="_blank" href="'+url+'">Abrir '+label+'</a></div>';
                        } else {
                            html = '<div class="p-3 text-muted">Archivo '+label+': <a target="_blank" href="'+url+'">'+url+'</a></div>';
                        }
                        $carInner.append('<div class="carousel-item'+active+'">'+ html +'</div>');
                        anySlide = true;
                    };
                    addFileSlide(cadUrl, 'CAD');
                    addFileSlide(patronUrl, 'Patrón');

                    if (anySlide) { $carWrap.show(); } else { $noimg.show(); }

                    // Materiales (tolerante)
                    let mats = [];
                    const tryMats = [data?.materiales, data?.lista_materiales, verObj?.materiales];
                    for (let i=0;i<tryMats.length;i++){
                        const src = tryMats[i];
                        if (!src) continue;
                        if (Array.isArray(src) && src.length){ mats = src; break; }
                        if (typeof src === 'string'){
                            const arr = src.split(/[|]{2,}|[|,;\n]+/).map(s=>s.trim()).filter(Boolean);
                            if (arr.length){ mats = arr; break; }
                        }
                    }
                    if (mats.length){
                        mats.forEach(function(m){
                            if (typeof m === 'string') {
                                const parts = m.split(/\s+x\s+/i);
                                const nombre = (parts[0] || m).trim();
                                const cant = (parts[1] || '').trim();
                                const extra = cant ? ('<span class="badge bg-light text-dark">Cant: ' + cant + '</span>') : '';
                                if (nombre) {
                                    $('#pa-dis-materiales').append('<li class="list-group-item d-flex justify-content-between align-items-center">'+ nombre + extra +'</li>');
                                }
                            } else if (m && typeof m === 'object') {
                                let nombre = pick(m, ['nombre','articulo','articuloNombre','descripcion'], '');
                                const artId = pick(m, ['articuloId','articulo_id','articuloID'], '');
                                if (!nombre) { nombre = artId ? ('Art ' + artId) : ''; }
                                const cant = pick(m, ['cantidad','cantidadPorUnidad','qty','cantidad_por_unidad'], '');
                                const merma = pick(m, ['merma','mermaPct','merma_pct'], '');
                                const extra = [cant ? ('Cant: ' + cant) : null, merma ? ('Merma: ' + merma) : null].filter(Boolean).join(' · ');
                                if (nombre || extra){
                                    $('#pa-dis-materiales').append('<li class="list-group-item d-flex justify-content-between align-items-center">'+ (nombre||'Material') + (extra? ('<span class="badge bg-light text-dark">'+ extra +'</span>'):'') +'</li>');
                                }
                            }
                        });
                    }
                })
                .always(function(){ $('#pa-dis-loading').hide(); });
        });

        // Cargar catálogo de clientes al abrir el modal Agregar
        let paClientesCache = null;
        let paClasifCache = null;
        let paNuevoCliente = false;
        let paClientesMap = {};
        function pick(obj, keys, def=''){
            if (!obj) return def;
            for (let i=0;i<keys.length;i++){
                const k = keys[i];
                if (Object.prototype.hasOwnProperty.call(obj, k) && obj[k] != null) return obj[k];
            }
            return def;
        }
        function paFillFromCliente(cli){
            if (!cli) {
                $('#pa-cliente-id').val('');
                $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono').val('');
                $('#pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-cp, #pa-dir-pais, #pa-dir-resumen').val('');
                $('#pa-cla-nombre, #pa-cla-descripcion').val('');
                return;
            }
            $('#pa-cliente-id').val(cli.id ?? '');
            $('#pa-cli-nombre').val(cli.nombre || '');
            $('#pa-cli-email').val(cli.email || '');
            $('#pa-cli-telefono').val(cli.telefono || '');
            const d0 = cli.direccion_detalle || cli.direccion || {};
            const d = {
                calle:  pick(d0, ['calle','Calle']),
                numExt: pick(d0, ['numExt','num_ext','numext','numeroExterior','numero_exterior','NumeroExterior']),
                numInt: pick(d0, ['numInt','num_int','numint','numeroInterior','numero_interior','NumeroInterior']),
                ciudad: pick(d0, ['ciudad','Ciudad']),
                estado: pick(d0, ['estado','Estado','provincia','Provincia']),
                cp:     pick(d0, ['cp','CP','codigo_postal','codigoPostal','CodigoPostal','zip','ZIP']),
                pais:   pick(d0, ['pais','País','Pais','country','Country'])
            };
            $('#pa-dir-calle').val(d.calle || '');
            $('#pa-dir-numext').val(d.numExt || '');
            $('#pa-dir-numint').val(d.numInt || '');
            $('#pa-dir-ciudad').val(d.ciudad || '');
            $('#pa-dir-estado').val(d.estado || '');
            $('#pa-dir-cp').val(d.cp || '');
            $('#pa-dir-pais').val(d.pais || '');
            const resumen = [d.calle, d.numExt ? ('#'+d.numExt) : null, d.numInt ? ('Int '+d.numInt) : null, d.ciudad, d.estado, d.pais, d.cp ? ('CP '+d.cp) : null]
                .filter(Boolean).join(', ');
            $('#pa-dir-resumen').val(resumen || '');
            const cla = cli.clasificacion || {};
            $('#pa-cla-nombre').val(cla.nombre || '');
            $('#pa-cla-descripcion').val(cla.descripcion || '');
        }

        function paToggleNuevoCliente(on){
            paNuevoCliente = !!on;
            if (paNuevoCliente){
                $('#pa-cliente-select').closest('.col-md-8').hide();
                $('#pa-cliente-id').val('');
                $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono').val('');
                $('#pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-cp, #pa-dir-pais, #pa-dir-resumen').val('');
                $('#pa-cla-nombre, #pa-cla-descripcion').val('');
            } else {
                $('#pa-cliente-select').closest('.col-md-8').show();
            }
        }

        $('#pedidoAddModal').on('show.bs.modal', function(){
            $('#pa-cliente-select').empty().append('<option value=""></option>');
            $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono').val('');
            $('#pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-cp, #pa-dir-pais, #pa-dir-resumen').val('');
            $('#pa-cla-nombre, #pa-cla-descripcion').val('');
            $('#pa-cliente-id').val('');
            $('#pa-nuevo-cliente').prop('checked', false);
            paToggleNuevoCliente(false);
            paClientesCache = null;

            const $tbodyTallas = $('#op-tallas-tabla tbody');
            if ($tbodyTallas.length){
                $tbodyTallas.empty();
                opAddTallaRow();
                // Resetear cantidad plan al limpiar tabla
                $('#op-cantidadPlan').val('');
            }

            if (paSexoCache === null){
                $.getJSON('<?= base_url('modulo2/catalogos/sexo') ?>' + '?t=' + Date.now(), { solo_maquiladora: 1 })
                    .done(function(resp){
                        paSexoCache = Array.isArray(resp?.items) ? resp.items : [];
                        $('#op-tallas-tabla .op-talla-sexo').each(function(){
                            paFillSexoOptions($(this));
                        });
                    }).fail(function(){ paSexoCache = []; });
            } else {
                $('#op-tallas-tabla .op-talla-sexo').each(function(){
                    paFillSexoOptions($(this));
                });
            }

            if (paTallasCache === null){
                $.getJSON('<?= base_url('modulo2/catalogos/tallas') ?>' + '?t=' + Date.now(), { solo_maquiladora: 1 })
                    .done(function(resp){
                        paTallasCache = Array.isArray(resp?.items) ? resp.items : [];
                        $('#op-tallas-tabla .op-talla-talla').each(function(){
                            paFillTallaOptions($(this));
                        });
                    }).fail(function(){ paTallasCache = []; });
            } else {
                $('#op-tallas-tabla .op-talla-talla').each(function(){
                    paFillTallaOptions($(this));
                });
            }

            // Diseños
            const $selDis = $('#pa-dis-select');
            const $spinDis = $('#pa-dis-loading');
            const $carWrap = $('#pa-dis-carousel-wrap');
            const $carInner = $('#pa-dis-carousel-inner');
            const $noimg = $('#pa-dis-noimg');
            const $matList = $('#pa-dis-materiales');
            $selDis.empty().append('<option value="">Seleccionar...</option>');
            $carInner.empty();
            $carWrap.hide();
            $noimg.hide();
            $matList.empty();
            $('#pa-dis-nombre, #pa-dis-version, #pa-dis-fecha, #pa-dis-descripcion, #pa-dis-notas').val('');
            if ($('#pa-dis-version-id').length) { $('#pa-dis-version-id').val(''); }

            $spinDis.show();
            $.getJSON('<?= base_url('modulo1/disenos/json') ?>' + '?t=' + Date.now())
                .done(function(arr){
                    const list = Array.isArray(arr) ? arr : [];
                    list.forEach(function(it, idx){
                        const key = (it.id != null) ? String(it.id) : (it.codigo ? String(it.codigo) : String(idx));
                        const label = [it.codigo||'', it.nombre||''].filter(Boolean).join(' — ');
                        $selDis.append('<option value="'+ key +'" data-id="'+ (it.id ?? '') +'">'+ label +'</option>');
                    });
                })
                .always(function(){ $spinDis.hide(); });

            const cargar = function(list){
                const $sel = $('#pa-cliente-select');
                $sel.empty().append('<option value="">Seleccionar...</option>');
                const keys = [];
                paClientesMap = {};
                list.forEach(function(c, idx){
                    let key = (c.id != null) ? String(c.id) : (c.codigo ? String(c.codigo) : '');
                    if (!key) { key = 'idx_' + idx; }
                    keys.push(key);
                    paClientesMap[key] = c;
                    const label = [c.nombre||'', c.email||''].filter(Boolean).join(' — ');
                    $sel.append('<option value="'+ key +'">'+ label +'</option>');
                });
                $sel.val('');
                paFillFromCliente(null);
            };

            $('#pa-cli-loading').show();
            const urlClientes = '<?= base_url('modulo1/clientes/json') ?>' + '?t=' + Date.now();
            $.getJSON(urlClientes)
                .done(function(arr){
                    paClientesCache = Array.isArray(arr) ? arr : [];
                    cargar(paClientesCache);
                })
                .always(function(){ $('#pa-cli-loading').hide(); });

            // Si venimos de un PDF, aplicar prefill suave (solo en campos vacíos)
            if (paPdfPrefill && typeof paPdfPrefill === 'object') {
                if (paPdfPrefill.cliente_nombre && !($('#pa-cli-nombre').val()||'').trim()) {
                    $('#pa-cli-nombre').val(paPdfPrefill.cliente_nombre);
                }
                if (paPdfPrefill.fecha) {
                    if (!($('#oc-fecha').val()||'').trim()) {
                        $('#oc-fecha').val(paPdfPrefill.fecha);
                    }
                    if (!($('#op-fechaInicioPlan').val()||'').trim()) {
                        $('#op-fechaInicioPlan').val(paPdfPrefill.fecha);
                    }
                }
                if (paPdfPrefill.diseno_nombre && !($('#pa-dis-nombre').val()||'').trim()) {
                    $('#pa-dis-nombre').val(paPdfPrefill.diseno_nombre);
                }
                if (paPdfPrefill.diseno_descripcion && !($('#pa-dis-descripcion').val()||'').trim()) {
                    $('#pa-dis-descripcion').val(paPdfPrefill.diseno_descripcion);
                }
                if (paPdfPrefill.diseno_notas && !($('#pa-dis-notas').val()||'').trim()) {
                    $('#pa-dis-notas').val(paPdfPrefill.diseno_notas);
                }
                if (paPdfPrefill.cantidad_plan && !($('#op-cantidadPlan').val()||'').trim()) {
                    $('#op-cantidadPlan').val(paPdfPrefill.cantidad_plan);
                }
            }
        });

        $(document).on('change', '#pa-cliente-select', function(){
            const val = $(this).val();
            if (!paClientesCache) return;
            if (!val) { 
                paFillFromCliente(null); 
                return; 
            }
            let cli = (val && paClientesMap && paClientesMap[val]) ? paClientesMap[val] : null;
            if (!cli && val) {
                cli = paClientesCache.find(function(c){
                    const idMatch = String(c.id ?? '') === String(val);
                    const codMatch = String(c.codigo ?? '') === String(val);
                    return idMatch || codMatch;
                }) || null;
            }
            if (cli) {
                paFillFromCliente(cli);
                // Marcar campos como solo lectura
                $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono, #pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-pais, #pa-dir-cp, #pa-dir-resumen')
                    .prop('readonly', true);
            } else {
                paFillFromCliente(null);
            }
        });

        $(document).on('change', '#pa-nuevo-cliente', function(){
            paToggleNuevoCliente($(this).is(':checked'));
        });

        $(document).on('click', '#pa-continuar', function(){
            $('#pedidoAddModal').modal('hide');
        });

        // Carga de pedido desde PDF
        $(document).on('change', '#pedido-pdf-input', function(){
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) { return; }
            if (!/pdf$/i.test(file.name)) {
                Swal.fire('Archivo no válido', 'Selecciona un archivo PDF.', 'warning');
                $(this).val('');
                return;
            }

            const formData = new FormData();
            formData.append('pdf', file);

            Swal.fire({
                title: 'Leyendo PDF...',
                text: 'Por favor espera mientras se extrae la información del pedido.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '<?= base_url('modulo1/pedidos/cargar-pdf') ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).done(function(resp){
                if (!resp || resp.ok !== true) {
                    const msg = resp && resp.message ? resp.message : 'No se pudo interpretar el PDF.';
                    Swal.fire('Aviso', msg, 'warning');
                    return;
                }

                Swal.close();

                // Guardar respuesta en variable global
                paPdfPrefill = resp;

                // Mostrar los datos en el propio modal PDF
                $('#pedido-pdf-result').show();
                $('#pdf-cli-nombre').val(resp.cliente_nombre || '');
                $('#pdf-modelo').val(resp.modelo || '');
                $('#pdf-fecha').val(resp.fecha || '');
                $('#pdf-modelo-cliente').val(resp.modelo_cliente || '');
                $('#pdf-cantidad-plan').val(resp.cantidad_plan != null ? resp.cantidad_plan : '');
                $('#pdf-diseno-nombre').val(resp.diseno_nombre || '');
                $('#pdf-diseno-descripcion').val(resp.diseno_descripcion || '');
                $('#pdf-diseno-notas').val(resp.diseno_notas || '');
                $('#pdf-tallas-texto').val(resp.tallas_texto || '');

                // Imágenes de páginas del PDF
                const $imgWrap = $('#pedido-pdf-img-wrap');
                const $imgContainer = $('#pedido-pdf-imgs');
                $imgContainer.empty();
                if (Array.isArray(resp.paginas_img) && resp.paginas_img.length > 0) {
                    resp.paginas_img.forEach(function(url, idx){
                        if (!url) return;
                        const imgHtml = `
                            <div class="border rounded p-1 bg-light">
                                <img src="${url}" alt="Página ${idx+1} del PDF" class="img-fluid" style="max-height:220px;">
                            </div>`;
                        $imgContainer.append(imgHtml);
                    });
                    $imgWrap.show();
                } else {
                    $imgWrap.hide();
                }

                if (resp.texto_crudo) {
                    console.log('Texto PDF:', resp.texto_crudo.substring(0, 500));
                }
            }).fail(function(xhr){
                let msg = 'Error al procesar el PDF.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            }).always(() => {
                $('#pedido-pdf-input').val('');
            });
        });

        // Usar datos del modal PDF para prellenar el modal Agregar pedido
        $(document).on('click', '#pedido-pdf-usar', function(){
            // Asegurar que exista el objeto
            paPdfPrefill = paPdfPrefill && typeof paPdfPrefill === 'object' ? paPdfPrefill : {};

            // Tomar los valores actuales (incluyendo ediciones del usuario)
            paPdfPrefill.cliente_nombre      = $('#pdf-cli-nombre').val() || '';
            paPdfPrefill.modelo              = $('#pdf-modelo').val() || '';
            paPdfPrefill.modelo_cliente      = $('#pdf-modelo-cliente').val() || '';
            paPdfPrefill.fecha               = $('#pdf-fecha').val() || '';
            paPdfPrefill.diseno_nombre       = $('#pdf-diseno-nombre').val() || '';
            paPdfPrefill.diseno_descripcion  = $('#pdf-diseno-descripcion').val() || '';
            paPdfPrefill.diseno_notas        = $('#pdf-diseno-notas').val() || '';
            paPdfPrefill.cantidad_plan       = $('#pdf-cantidad-plan').val() || '';
            paPdfPrefill.tallas_texto        = $('#pdf-tallas-texto').val() || '';

            // Cerrar el modal de PDF y abrir el modal Agregar pedido
            $('#pedidoPdfModal').modal('hide');
            $('#pedidoAddModal').modal('show');
        });

        // Abrir y poblar el modal de pedido (AJAX JSON)
        $(document).on('click', '.btn-ver-pedido', function () {
            const id = $(this).data('id');

            $('#p-id,#p-empresa,#p-folio,#p-fecha,#p-estatus,#p-moneda,#p-total').text('...');
            $('#p-cli-codigo,#p-cli-nombre,#p-cli-email,#p-cli-telefono,#p-cli-clasificacion').text('...');
            $('#p-dir-calle,#p-dir-numext,#p-dir-numint,#p-dir-ciudad,#p-dir-estado,#p-dir-cp,#p-dir-pais,#p-dir-resumen').text('...');
            $('#p-dis-codigo,#p-dis-nombre,#p-dis-descripcion,#p-dis-version,#p-dis-version-fecha,#p-dis-version-aprobado').text('...');
            $('#p-editar').attr('href', '#');
            $('#p-doc').hide().attr('href','#');

            const url = '<?= base_url('modulo1/pedido') ?>/' + id + '/json';

            $.getJSON(url)
                .done(function (data) {
                    $('#p-id').text(data.id || id);
                    $('#p-empresa').text(data.empresa || '-');
                    $('#p-folio').text(data.folio || '-');
                    $('#p-fecha').text(data.fecha || '-');
                    $('#p-estatus').text(data.estatus || '-');
                    $('#p-moneda').text(data.moneda || '-');
                    $('#p-total').text(data.total || '0.00');
                    $('#p-editar').attr('href', '<?= base_url('modulo1/editar/') ?>' + id);

                    const cli = data.cliente || {};
                    $('#p-cli-codigo').text(cli.codigo || '-');
                    $('#p-cli-nombre').text(cli.nombre || '-');
                    $('#p-cli-email').text(cli.email || '-');
                    $('#p-cli-telefono').text(cli.telefono || '-');
                    const dir = (cli.direccion_detalle || {});
                    $('#p-dir-calle').text(dir.calle || '-');
                    $('#p-dir-numext').text(dir.numExt || '-');
                    $('#p-dir-numint').text(dir.numInt || '-');
                    $('#p-dir-ciudad').text(dir.ciudad || '-');
                    $('#p-dir-estado').text(dir.estado || '-');
                    $('#p-dir-cp').text(dir.cp || '-');
                    $('#p-dir-pais').text(dir.pais || '-');
                    const resumen = [dir.calle, dir.numExt ? ('#'+dir.numExt):null, dir.numInt?('Int '+dir.numInt):null, dir.ciudad, dir.estado, dir.pais, dir.cp?('CP '+dir.cp):null]
                        .filter(Boolean).join(', ');
                    $('#p-dir-resumen').text(resumen || '-');
                    const cla = (cli.clasificacion || {});
                    const claTxt = (cla.nombre ? cla.nombre : '-') + (cla.descripcion ? (' · ' + cla.descripcion) : '');
                    $('#p-cli-clasificacion').text(claTxt);

                    if (data.documento_url) {
                        $('#p-doc').attr('href', data.documento_url).show();
                    }

                    let dis = data.diseno || null;
                    if (!dis && Array.isArray(data.disenos) && data.disenos.length > 0) {
                        dis = data.disenos[data.disenos.length - 1];
                    }
                    $('#p-dis-codigo').text(dis?.codigo || '-');
                    $('#p-dis-nombre').text(dis?.nombre || '-');
                    $('#p-dis-descripcion').text(dis?.descripcion || '-');

                    // Procesar tallas si existen
                    if (data.tallas && data.tallas.length > 0) {
                        const $tallasTbody = $('#p-tallas-detalle tbody');
                        $tallasTbody.empty(); // Limpiar filas existentes

                        // Ordenar por sexo y talla para mejor presentación
                        data.tallas.sort((a, b) => {
                            const sexoA  = (a.nombre_sexo || a.sexo_nombre  || '').toLowerCase();
                            const sexoB  = (b.nombre_sexo || b.sexo_nombre  || '').toLowerCase();
                            const tallaA = (a.nombre_talla || a.talla_nombre || '').toLowerCase();
                            const tallaB = (b.nombre_talla || b.talla_nombre || '').toLowerCase();

                            if (sexoA < sexoB) return -1;
                            if (sexoA > sexoB) return 1;
                            if (tallaA < tallaB) return -1;
                            if (tallaA > tallaB) return 1;
                            return 0;
                        });

                        // Agregar filas para cada talla
                        data.tallas.forEach(talla => {
                            const nombreSexo  = talla.nombre_sexo  || talla.sexo_nombre  || '-';
                            const nombreTalla = talla.nombre_talla || talla.talla_nombre || '-';
                            const row = `
                                <tr>
                                    <td>${nombreSexo}</td>
                                    <td>${nombreTalla}</td>
                                    <td class="text-end">${parseInt(talla.cantidad) || 0}</td>
                                </tr>
                            `;
                            $tallasTbody.append(row);
                        });

                        // Mostrar la tabla y ocultar el mensaje de "sin tallas"
                        $('#p-con-tallas').show();
                        $('#p-sin-tallas').hide();
                    } else {
                        // No hay tallas, mostrar mensaje
                        $('#p-con-tallas').hide();
                        $('#p-sin-tallas').show();
                    }
                    let ver = dis && (dis.version && typeof dis.version === 'object' ? dis.version : null);
                    const vNum = ver?.version ?? dis?.version ?? null;
                    const vFechaRaw = ver?.fecha ?? dis?.fecha ?? null;
                    const vAprob = ver?.aprobado ?? dis?.aprobado ?? null;
                    $('#p-dis-version').text(vNum ?? '-');
                    if (vFechaRaw) {
                        const d = new Date(vFechaRaw);
                        $('#p-dis-version-fecha').text(isNaN(d) ? String(vFechaRaw).slice(0,10) : d.toISOString().slice(0,10));
                    } else {
                        $('#p-dis-version-fecha').text('-');
                    }
                    $('#p-dis-version-aprobado').text((vAprob === 1 || vAprob === true || vAprob === '1') ? 'Sí' : (vAprob === 0 || vAprob === false || vAprob === '0' ? 'No' : '-'));

                    // Mostrar enlace de archivo de diseño si está disponible (CAD/PDF/DXF)
                    const cadUrl = dis?.archivoCadUrl || ver?.archivoCadUrl || null;
                    const patUrl = dis?.archivoPatronUrl || ver?.archivoPatronUrl || dis?.patron || ver?.patron || null;
                    const fotoUrl = dis?.fotoUrl || ver?.fotoUrl || dis?.foto || ver?.foto || null;
                    
                    console.log('Diseño:', dis);
                    console.log('Versión:', ver);
                    console.log('Foto (raw):', fotoUrl);
                    console.log('Patrón (raw):', patUrl);
                    
                    // Mostrar imágenes del diseño si están disponibles
                    let hasImages = false;
                    
                    // Foto del diseño - puede ser base64 o URL
                    if (fotoUrl) {
                        let fotoSrc = fotoUrl;
                        // Si es base64 sin prefijo, agregarlo
                        if (typeof fotoUrl === 'string' && !fotoUrl.startsWith('http') && !fotoUrl.startsWith('data:')) {
                            fotoSrc = 'data:image/jpeg;base64,' + fotoUrl;
                        }
                        $('#p-dis-foto').attr('src', fotoSrc);
                        $('#p-dis-foto-container').show();
                        hasImages = true;
                    } else {
                        $('#p-dis-foto-container').hide();
                    }
                    
                    // Patrón - puede ser base64 o URL
                    if (patUrl) {
                        let patSrc = patUrl;
                        // Si es base64 sin prefijo, agregarlo
                        if (typeof patUrl === 'string' && !patUrl.startsWith('http') && !patUrl.startsWith('data:')) {
                            patSrc = 'data:image/jpeg;base64,' + patUrl;
                        }
                        $('#p-dis-patron').attr('src', patSrc);
                        $('#p-dis-patron-container').show();
                        hasImages = true;
                    } else {
                        $('#p-dis-patron-container').hide();
                    }
                    
                    // Mostrar/ocultar contenedor de imágenes
                    if (hasImages) {
                        $('#p-dis-imagenes-container').show();
                    } else {
                        $('#p-dis-imagenes-container').hide();
                    }

                    // Precio unidad y cantidad plan (si existen elementos en la vista de detalles)
                    if ($('#p-dis-precio').length) {
                        const precio = dis?.precio_unidad ?? null;
                        $('#p-dis-precio').text(precio != null ? '$' + parseFloat(precio).toFixed(2) : '-');
                    }
                    if ($('#p-op-cantidadPlan').length) {
                        const cant = data.op_cantidadPlan ?? data.op_cantidad_plan ?? null;
                        $('#p-op-cantidadPlan').text(cant != null ? String(cant) : '-');
                    }
                    if ($('#p-op-fechaFinPlan').length) {
                        const fechaFin = data.op_fechaFinPlan ?? data.op_fecha_fin_plan ?? null;
                        if (fechaFin) {
                            const d = new Date(fechaFin);
                            $('#p-op-fechaFinPlan').text(isNaN(d) ? String(fechaFin).slice(0,10) : d.toISOString().slice(0,10));
                        } else {
                            $('#p-op-fechaFinPlan').text('-');
                        }
                    }
                })
                .fail(function () {
                    $('#p-empresa').text('No fue posible cargar los datos');
                    $('#p-dis-codigo,#p-dis-nombre,#p-dis-descripcion,#p-dis-version,#p-dis-version-fecha,#p-dis-version-aprobado').text('-');
                });
        });

        function cargarPedidoEnModal(id){
            const url = '<?= base_url('modulo1/pedido') ?>/' + id + '/json';
            $('#pe-id').val(id);
            $('#pe-folio, #pe-fecha, #pe-estatus, #pe-moneda, #pe-total, #pe-progreso, #pe-descripcion, #pe-cantidad, #pe-cantidad-bultos, #pe-fechaentrega, #pe-modelo, #pe-tallas, #pe-color, #pe-materiales, #pe-especificaciones').val('');
            $('#pe-empresa, #pe-dir, #pe-dis').text('');
            
            // Limpiar la tabla de tallas
            $('#pe-tallas-tabla tbody').empty();
            
            // Mostrar indicador de carga
            const $modal = $('#pedidoEditModal');
            $modal.find('.modal-body').addClass('loading');
            $('#pe-cli-nombre, #pe-cli-email, #pe-cli-telefono').val('');
            $('#pe-dir-calle, #pe-dir-numext, #pe-dir-numint, #pe-dir-ciudad, #pe-dir-estado, #pe-dir-pais, #pe-dir-cp, #pe-dir-resumen').val('');
            $('#pe-dis-codigo, #pe-dis-nombre, #pe-dis-descripcion, #pe-dis-version, #pe-dis-version-fecha').val('');
            $('#pe-dis-version-aprobado').val('');
            const $selDis = $('#pe-dis-select');
            if ($selDis.length){
                $selDis.empty().append('<option value="">Seleccionar...</option>');
            }
            $('#pe-dis-loading').hide();

            $('#pe-dis-loading').show();

            $.getJSON(url).done(function(data){
                $('#pe-folio').val(data.folio || '');
                $('#pe-fecha').val(data.fecha || '');
                $('#pe-estatus').val(data.estatus || 'Pendiente');
                if ($('#pe-estatus-hidden').length) { $('#pe-estatus-hidden').val($('#pe-estatus').val()); }
                $('#pe-moneda').val(data.moneda || '');
                const total = (data.total||'').toString().replace(/,/g,'');
                $('#pe-total').val(total || '');
                $('#pe-progreso').val(data.progreso || '');
                $('#pe-descripcion').val(data.descripcion || '');
                $('#pe-cantidad').val(data.cantidad || '');
                $('#pe-cantidad-bultos').val(data.op_cantidadBultos || '');
                $('#pe-fechaentrega').val(data.fecha_entrega || '');
                $('#pe-modelo').val(data.modelo || '');
                $('#pe-tallas').val(data.tallas || '');
                $('#pe-color').val(data.color || '');
                $('#pe-materiales').val(data.materiales || '');
                $('#pe-especificaciones').val(data.especificaciones || '');
                // Fin plan si viene de la OP
                if (data.op_fechaFinPlan) {
                    const fpf = String(data.op_fechaFinPlan);
                    const d = new Date(fpf);
                    $('#pe-op-fechaFinPlan').val(isNaN(d) ? fpf.slice(0,10) : d.toISOString().slice(0,10));
                } else { $('#pe-op-fechaFinPlan').val(''); }

                const cli = data.cliente || {};
                $('#pe-empresa').text(cli.nombre || (data.empresa||'-'));
                $('#pe-cli-nombre').val(cli.nombre || '');
                $('#pe-cli-email').val(cli.email || '');
                $('#pe-cli-telefono').val(cli.telefono || '');
                const d0 = cli.direccion_detalle || cli.direccion || {};
                const d = {
                    calle:  pick(d0, ['calle','Calle']),
                    numExt: pick(d0, ['numExt','num_ext','numext','numeroExterior','numero_exterior','NumeroExterior']),
                    numInt: pick(d0, ['numInt','num_int','numint','numeroInterior','numero_interior','NumeroInterior']),
                    ciudad: pick(d0, ['ciudad','Ciudad']),
                    estado: pick(d0, ['estado','Estado','provincia','Provincia']),
                    cp:     pick(d0, ['cp','CP','codigo_postal','codigoPostal','CodigoPostal','zip','ZIP']),
                    pais:   pick(d0, ['pais','País','Pais','country','Country'])
                };
                $('#pe-dir-calle').val(d.calle || '');
                $('#pe-dir-numext').val(d.numExt || '');
                $('#pe-dir-numint').val(d.numInt || '');
                $('#pe-dir-ciudad').val(d.ciudad || '');
                $('#pe-dir-estado').val(d.estado || '');
                $('#pe-dir-cp').val(d.cp || '');
                $('#pe-dir-pais').val(d.pais || '');
                const dirTxt = [d.calle, d.numExt ? ('#'+d.numExt):null, d.numInt?('Int '+d.numInt):null, d.ciudad, d.estado, d.pais, d.cp?('CP '+d.cp):null]
                    .filter(Boolean).join(', ');
                $('#pe-dir-resumen').val(dirTxt || '');
                let dis = data.diseno || null;

                const lista = Array.isArray(data.disenos) ? data.disenos : [];
                const mapByKey = {};
                if ($selDis.length){
                    $selDis.empty().append('<option value="">Seleccionar...</option>');
                    lista.forEach((it, idx) => {
                        const key = (it.id != null) ? String(it.id) : (it.codigo ? String(it.codigo) : String(idx));
                        mapByKey[key] = it;
                        const label = [it.codigo||'', it.nombre||''].filter(Boolean).join(' — ');
                        $selDis.append(`<option value="${key}">${label}</option>`);
                    });
                }

                // Cargar las tallas si existen
                if (data.tallas && data.tallas.length > 0) {
                    console.log('', data.tallas);
                    cargarTallasEnEdicion(data.tallas);
                } else {
                    console.log('No se encontraron tallas para este pedido');
                    // Agregar una fila vacía
                    agregarFilaTallaEditar();
                }
                
                if ($selDis.length && (!lista || lista.length <= 1)) {
                    $('#pe-dis-loading').show();
                    $.getJSON('<?= base_url('modulo2/disenos/json') ?>')
                        .done(function(cat){
                            const arr = Array.isArray(cat) ? cat : [];
                            $selDis.empty().append('<option value="">Seleccionar...</option>');
                            for (let i = 0; i < arr.length; i++) {
                                const it = arr[i] || {};
                                const key = (it.id != null) ? String(it.id) : (it.codigo ? String(it.codigo) : String(i));
                                mapByKey[key] = it;
                                const label = [it.codigo || '', it.nombre || ''].filter(Boolean).join(' — ');
                                $selDis.append('<option value="' + key + '\">' + label + '</option>');
                            }

                            if (dis) {
                                const selKey = (dis.id != null) ? String(dis.id) : (dis.codigo ? String(dis.codigo) : '');
                                if (selKey && mapByKey[selKey]) $selDis.val(selKey);
                            }

                            if (!$selDis.val() && arr.length){
                                const firstKey = (arr[0].id != null) ? String(arr[0].id) : (arr[0].codigo ? String(arr[0].codigo) : '0');
                                $selDis.val(firstKey);
                                dis = mapByKey[firstKey];
                            }

                            fillDesignFields(dis || null);
                        })
                        .always(function(){
                            $('#pe-dis-loading').hide();
                        });
                }

                function fillDesignFields(dx){
                    $('#pe-dis').text(dx ? ((dx.codigo||'') + ' ' + (dx.nombre||'')) : '-');
                    $('#pe-dis-codigo').val(dx?.codigo || '');
                    $('#pe-dis-nombre').val(dx?.nombre || '');
                    $('#pe-dis-descripcion').val(dx?.descripcion || '');
                    // Precio unidad desde el diseño del pedido si existe
                    if ($('#pe-dis-precio').length) {
                        // intentar desde dx; si no, desde option seleccionado (data-precio); si no, desde data.diseno
                        const optPrecio = ($('#pe-dis-select').length ? $('#pe-dis-select option:selected').data('precio') : undefined);
                        const p = (dx && dx.precio_unidad != null)
                            ? dx.precio_unidad
                            : (optPrecio != null ? optPrecio : (data.diseno && data.diseno.precio_unidad != null ? data.diseno.precio_unidad : ''));
                        $('#pe-dis-precio').val(p);
                    }
                    let ver = dx && (dx.version && typeof dx.version === 'object' ? dx.version : null);
                    const vNum = ver?.version ?? dx?.version ?? null;
                    const vFechaRaw = ver?.fecha ?? dx?.fecha ?? null;
                    const vAprob = ver?.aprobado ?? dx?.aprobado ?? null;
                    $('#pe-dis-version').val(vNum ?? '');
                    if (vFechaRaw) {
                        const dt = new Date(vFechaRaw);
                        $('#pe-dis-version-fecha').val(isNaN(dt) ? String(vFechaRaw).slice(0,10) : dt.toISOString().slice(0,10));
                    } else {
                        $('#pe-dis-version-fecha').val('');
                    }
                    const apr = (vAprob === 1 || vAprob === true || vAprob === '1') ? '1' : (vAprob === 0 || vAprob === false || vAprob === '0' ? '0' : '');
                    $('#pe-dis-version-aprobado').val(apr);
                    if ($('#pe-dis-version-aprobado-hidden').length) { $('#pe-dis-version-aprobado-hidden').val(apr); }
                    // recalcular total con el nuevo precio y cantidad
                    setTimeout(recalcTotal, 0);
                    // set hidden version id if exists
                    if ($('#pe-dis-version-id').length && ver && ver.id) {
                        $('#pe-dis-version-id').val(ver.id);
                    }
                    const cadUrl = dx?.archivoCadUrl || ver?.archivoCadUrl || null;
                    const patUrl = dx?.archivoPatronUrl || ver?.archivoPatronUrl || null;
                    const firstFile = cadUrl || patUrl || null;
                    if (firstFile && $('#pe-dis-doc').length) {
                        $('#pe-dis-doc').attr('href', firstFile).show();
                    }
                    if (typeof recalcTotal === 'function') recalcTotal();
                }

                if ($selDis.length){
                    let selectedKey = '';
                    if (dis){
                        selectedKey = (dis.id != null) ? String(dis.id) : (dis.codigo ? String(dis.codigo) : '');
                        $selDis.val(selectedKey);
                    }
                    if (!$selDis.val() && lista.length){
                        const firstKey = (lista[0].id != null) ? String(lista[0].id) : (lista[0].codigo ? String(lista[0].codigo) : '0');
                        $selDis.val(firstKey);
                        dis = mapByKey[firstKey];
                    }
                    fillDesignFields(dis || null);
                    $selDis.off('change').on('change', function(){
                        const key = $(this).val();
                        let dx = mapByKey[key] || null;
                        // Si no tenemos precio en dx, intentar obtenerlo vía AJAX por id
                        const selId = $(this).find('option:selected').data('id') || key;
                        const needPrice = !(dx && dx.precio_unidad != null);
                        if (needPrice && selId) {
                            $.getJSON('<?= base_url('modulo2/diseno') ?>/'+ selId +'/json')
                                .done(function(resp){
                                    if (!dx) dx = {};
                                    const pr = resp?.diseno?.precio_unidad ?? resp?.precio_unidad ?? null;
                                    if (pr != null) dx.precio_unidad = pr;
                                    fillDesignFields(dx);
                                })
                                .fail(function(){ fillDesignFields(dx); });
                        } else {
                            fillDesignFields(dx);
                        }
                    });
                } else {
                    fillDesignFields(dis || null);
                }

                // Cantidad plan desde la OP del pedido
                if ($('#pe-op-cantidadPlan').length) {
                    const cant = data.op_cantidadPlan != null ? data.op_cantidadPlan : '';
                    $('#pe-op-cantidadPlan').val(cant);
                }

                // Setear id y total inicial de la BD
                if ($('#pe-id').length) { $('#pe-id').val(data.id || ''); }
                if ($('#pe-total').length && (data.total != null)) {
                    $('#pe-total').val(String(data.total).replace(/,/g,'').toString());
                }

                $('#pe-dis-loading').hide();
            }).fail(function(xhr, status, error){
                console.error('Error al cargar el pedido:', status, error);
                console.error('Response JSON:', xhr.responseJSON);
                console.error('Response Text:', xhr.responseText);
                
                let errorMsg = 'No se pudieron cargar los datos del pedido.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg += '\n\nDetalle: ' + xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.file && xhr.responseJSON.line) {
                        errorMsg += '\n\nArchivo: ' + xhr.responseJSON.file + ':' + xhr.responseJSON.line;
                    }
                } else if (xhr.responseText) {
                    errorMsg += '\n\n(Status: ' + xhr.status + ')';
                }
                Swal.fire('Error', errorMsg, 'error');
            });
        }

        // ==========================================
        // NUEVA LÓGICA DE EDICIÓN DE PEDIDO
        // ==========================================

        // Función para recalcular total
        function recalcularTotalEditar() {
            const $sel = $('#pe-diseno option:selected');
            const precio = parseFloat($sel.data('precio')) || 0;
            const cantidad = parseInt($('#pe-cantidad').val()) || 0;
            const total = precio * cantidad;
            
            $('#pe-precio-unitario').val(precio.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#pe-total-estimado').val(total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Listener para cambio de diseño en Editar (Cargar detalles visuales y recalcular)
        $('#pe-diseno').on('change', function() {
            recalcularTotalEditar();
            
            const id = $(this).val();
            const $container = $('#pe-detalles-diseno');
            
            if (!id) {
                $container.slideUp();
                return;
            }


            // Mostrar loading o estado intermedio
            $('#pe-preview-nombre').text('');
            $container.slideDown();

            $.getJSON('<?= base_url('modulo1/disenos') ?>/' + id + '/json?t=' + Date.now())
                .done(function(data) {
                    // Nombre
                    $('#pe-preview-nombre').text(data.nombre || 'Diseño sin nombre');
                    
                    // Versión y Fecha
                    let verObj = null;
                    if (data.version && typeof data.version === 'object') verObj = data.version;
                    else if (Array.isArray(data.versiones) && data.versiones.length) verObj = data.versiones[data.versiones.length-1];
                    else if (data.diseno_version && typeof data.diseno_version === 'object') verObj = data.diseno_version;
                    
                    const ver = verObj ? (verObj.version || verObj.ver || '') : (data.version || '');
                    const fecha = verObj ? (verObj.fecha || '') : (data.fecha || '');
                    
                    $('#pe-preview-version').text(ver ? 'v'+ver : '');
                    $('#pe-preview-fecha').text(fecha ? '('+fecha.substring(0,10)+')' : '');

                    // Imagen
                    let imgUrl = '';
                    if (data.imagen_url) imgUrl = data.imagen_url;
                    else if (verObj && verObj.imagen_url) imgUrl = verObj.imagen_url;
                    else if (Array.isArray(data.imagenes) && data.imagenes.length) {
                        const first = data.imagenes[0];
                        imgUrl = typeof first === 'string' ? first : (first.url || first.src || '');
                    }
                    
                    if (imgUrl) {
                        $('#pe-preview-img').attr('src', imgUrl).show();
                        $('#pe-preview-noimg').hide();
                    } else {
                        $('#pe-preview-img').hide();
                        $('#pe-preview-noimg').show();
                    }

                    // Patrón
                    const patUrl = verObj?.archivoPatronUrl || data.archivoPatronUrl || '';
                    const $patContainer = $('#pe-preview-patron-link');
                    if (patUrl) {
                        const ext = patUrl.split('.').pop().toLowerCase();
                        let html = '';
                        if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                            html = `<a href="${patUrl}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-image"></i> Ver Imagen</a>`;
                        } else {
                            html = `<a href="${patUrl}" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-earmark-arrow-down"></i> Descargar (${ext})</a>`;
                        }
                        $patContainer.html(html);
                    } else {
                        $patContainer.html('<span class="text-muted small">No disponible</span>');
                    }
                })
                .fail(function() {
                    $('#pe-preview-nombre').text('Error al cargar detalles');
                });
        });

        // 1. Abrir modal y cargar datos
        $(document).on('click', '.btn-editar-pedido', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!id) return;
            
            // Cargar los catálogos antes de abrir el modal
            cargarCatalogosTallas();

            // Mostrar loading
            Swal.fire({title:'', didOpen:()=>Swal.showLoading()});
            
            // Cargar datos del pedido y lista de diseños en paralelo
            $.when(
                $.getJSON('<?= base_url('modulo1/pedido') ?>/' + id + '/json'),
                $.getJSON('<?= base_url('modulo2/disenos/json') ?>')
            ).done(function(pedidoResp, disenosResp) {
                Swal.close();
                
                const pedido = pedidoResp[0]; // $.when devuelve array [data, status, xhr]
                const disenos = disenosResp[0] || [];
                
                // Adaptar estructura plana del backend a objetos op/oc
                const op = {
                    cantidadPlan: pedido.op_cantidadPlan,
                    fechaFinPlan: pedido.op_fechaFinPlan,
                    disenoVersionId: pedido.op_disenoVersionId
                };
                const oc = {
                    folio: pedido.folio
                };

                // Rellenar campos estáticos
                $('#pe-id').val(id);
                $('#pe-folio').val(oc.folio || '-');
                
                const cli = pedido.cliente || {};
                $('#pe-cliente').val(cli.nombre || '-');
                $('#pe-cli-email').val(cli.email || '-');
                $('#pe-cli-telefono').val(cli.telefono || '-');
                
                const dir = cli.direccion_detalle || {};
                const dirTxt = [
                    dir.calle, 
                    dir.numExt ? ('#'+dir.numExt) : null, 
                    dir.numInt ? ('Int '+dir.numInt) : null,
                    dir.ciudad, 
                    dir.estado,
                    dir.cp ? ('CP '+dir.cp) : null
                ].filter(Boolean).join(', ');
                $('#pe-cli-direccion').val(dirTxt || '-');

                // Rellenar dropdown de diseños
                const $sel = $('#pe-diseno');
                $sel.empty().append('<option value="">Seleccionar diseño...</option>');
                
                let currentVersionId = op.disenoVersionId;
                
                disenos.forEach(d => {
                    // Usamos la versión más reciente del diseño
                    const verId = d.version ? d.version.id : null; // Asumiendo que el endpoint devuelve esto
                    // Si el endpoint devuelve solo 'version' como string, necesitamos el ID.
                    // Revisando m2_disenos_json, devuelve 'version' como string (nombre de la versión).
                    // Necesitamos el ID de la versión. Esto es un problema.
                    
                    // FIX: Usaremos el ID del diseño y dejaremos que el backend resuelva la última versión
                    // O mejor, modificaremos el endpoint para que devuelva el ID de la versión.
                    // Por ahora, asumiremos que el value es el ID del diseño, y el backend buscará la última versión.
                    // Pero el backend m1_editar espera 'disenoVersionId'.
                    
                    // Vamos a usar el ID del diseño en el value, y en el backend ajustaremos si es necesario.
                    // O mejor, modifiquemos m2_disenos_json para devolver version_id.
                    
                    // Por ahora, mostremos los diseños.
                    $sel.append(`<option value="${d.id}" data-precio="${d.precio_unidad}">${d.codigo} - ${d.nombre}</option>`);
                });

                // Seleccionar el diseño actual
                // El pedido tiene op.disenoVersionId. Necesitamos saber a qué diseño corresponde.
                // El endpoint de pedido devuelve 'diseno' con id.
                if (pedido.diseno && pedido.diseno.id) {
                    $sel.val(pedido.diseno.id);
                }
                // Disparar evento change para cargar la vista previa
                $sel.trigger('change');

                // Rellenar campos editables
                $('#pe-cantidad').val(op.cantidadPlan || '');
                $('#pe-cantidad-bultos').val(pedido.op_cantidadBultos || '');
                $('#pe-fecha-fin').val(op.fechaFinPlan || '');
                
                // Recalcular total inicial
                recalcularTotalEditar();

                // Cargar detalle de tallas en el modal de edición
                const tallasPedido = Array.isArray(pedido.tallas) ? pedido.tallas : [];
                cargarCatalogosTallas()
                    .then(() => {
                        cargarTallasEnEdicion(tallasPedido);
                    })
                    .catch(() => {
                        // Si falla el catálogo, al menos intentar mostrar las tallas con lo que haya
                        cargarTallasEnEdicion(tallasPedido);
                    });

                // Mostrar modal
                $('#pedidoEditModal').modal('show');

            }).fail(function(xhr, status, error) {
                console.error('Error al cargar pedido:', xhr, status, error);
                console.error('Response JSON:', xhr.responseJSON);
                console.error('Response Text:', xhr.responseText);
                
                let errorMsg = 'No se pudieron cargar los datos del pedido.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg += '\n\nDetalle: ' + xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.file && xhr.responseJSON.line) {
                        errorMsg += '\n\nArchivo: ' + xhr.responseJSON.file + ':' + xhr.responseJSON.line;
                    }
                } else if (xhr.responseText) {
                    errorMsg += '\n\n(Status: ' + xhr.status + ')';
                }
                Swal.fire('Error', errorMsg, 'error');
            });
        });
        
        // Listener para cambio de cantidad
        $('#pe-cantidad').on('input change', function() {
            recalcularTotalEditar();
        });

        // 2. Guardar cambios
        $('#formPedidoEditar').on('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            
            // Validación básica
            if (!formData.get('disenoId') || !formData.get('op_cantidadPlan')) {
                Swal.fire('Atención', 'Por favor completa todos los campos requeridos.', 'warning');
                return;
            }

            // Confirmación
            Swal.fire({
                title: '¿Guardar cambios?',
                text: "Se actualizará la información del pedido.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({title:'Guardando...', didOpen:()=>Swal.showLoading()});
                    
                    // Ajuste: El select envía el ID del diseño, pero el backend espera disenoVersionId o maneja la lógica.
                    // En m1_editar, si recibe disenoVersionId, lo usa.
                    // Necesitamos que el backend sea capaz de recibir 'disenoId' y buscar la última versión, 
                    // O obtener la versión aquí.
                    
                    // Como no tenemos el ID de la versión en la lista simple, enviaremos el ID del diseño
                    // y confiaremos en que el backend lo maneje o lo actualizaremos ahora.
                    
                    // Para asegurar que funcione, vamos a modificar el backend m1_editar para aceptar 'disenoId' 
                    // y buscar la última versión si no se envía 'disenoVersionId'.
                    // Pero primero enviemos los datos.
                    
                    // Renombramos el campo para ser claros si enviamos disenoId
                    // En el HTML el select tiene name="disenoVersionId". 
                    // Si enviamos el ID del diseño en ese campo, el backend intentará usarlo como ID de versión, lo cual fallará.
                    
                    // CAMBIO EN EL PLAN:
                    // 1. Modificar el select name a "disenoId".
                    // 2. Modificar el backend para aceptar "disenoId" y buscar la última versión.
                    
                    // Por ahora, enviamos el form tal cual.
                    
                    $.ajax({
                        url: form.action,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function(resp) {
                            if (resp.success || resp.ok) {
                                Swal.fire('¡Guardado!', 'El pedido ha sido actualizado.', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', resp.message || resp.error || 'Error desconocido', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            let msg = 'Error al procesar la solicitud.';
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                                else if (xhr.responseJSON.error) msg = xhr.responseJSON.error;
                            } else {
                                msg += ` (${xhr.status} ${xhr.statusText})`;
                                console.error('Error response:', xhr.responseText);
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });

        // ====== Clientes: cargar catálogo y rellenar datos al seleccionar ======
        function cargarClientesPedido(preselectId){
            const $sel = $('#pa-cliente-select');
            const $sp = $('#pa-cli-loading');
            $sp.show();
            $.ajax({ url: '<?= base_url('modulo1/clientes/json') ?>', method:'GET' })
                .done(function(list){
                    $sel.empty().append('<option value="">Seleccionar...</option>');
                    (list||[]).forEach(c => {
                        const nombre = c.nombre || ('ID '+c.id);
                        const correo = c.email || c.correo || '';
                        const label = correo ? (nombre + ' — ' + correo) : nombre;
                        // soporta shape con direccion anidada
                        const d = c.direccion || {};
                        const attrs = [
                            'data-email="'+(correo||'')+'"',
                            'data-telefono="'+(c.telefono||'')+'"',
                            'data-calle="'+(d.calle||'')+'"',
                            'data-numext="'+(d.numext||'')+'"',
                            'data-numint="'+(d.numint||'')+'"',
                            'data-ciudad="'+(d.ciudad||'')+'"',
                            'data-estado="'+(d.estado||'')+'"',
                            'data-pais="'+(d.pais||'')+'"',
                            'data-cp="'+(d.cp||'')+'"'
                        ].join(' ');
                        $sel.append('<option value="'+c.id+'" '+attrs+'>'+label+'</option>');
                    });
                    if (preselectId) $sel.val(String(preselectId));
                    // Si hay selección válida, disparar cambio
                    const v = $sel.val();
                    if (v && v !== 'null' && v !== 'undefined') { $sel.trigger('change'); }
                })
                .fail(function(){
                    $sel.empty().append('<option value="">Error al cargar</option>');
                })
                .always(function(){ $sp.hide(); });
        }
        // Helper: actualizar folios OC/OP según cliente
        function actualizarFolios(clienteId){
            const year = new Date().getFullYear();
            if (clienteId && clienteId !== 'null' && clienteId !== 'undefined') {
                $('#oc-folio').val('OC-' + year + '-' + clienteId);
                $('#op-folio').val('OP-' + year + '-' + clienteId);
            } else {
                $('#oc-folio').val('');
                $('#op-folio').val('');
            }
        }

        // Cargar al abrir modal
        $('#pedidoAddModal').on('show.bs.modal', function(){
            cargarClientesPedido();
            const cid = $('#pa-cliente-select').val();
            actualizarFolios(cid);
        });

        // Al seleccionar cliente: rellenar datos y setear clienteId para OC
        // intenta obtener detalle del cliente de distintas rutas conocidas
        function fetchClienteDetalle(id){
            if (!id || id === 'null' || id === 'undefined') {
                return Promise.reject(new Error('id invalido'));
            }
            const endpoints = [
                '<?= base_url('api/clientes') ?>/'+encodeURIComponent(id),
                '<?= base_url('modulo1/clientes') ?>/'+encodeURIComponent(id),
                '<?= base_url('clientes') ?>/'+encodeURIComponent(id)
            ];
            let idx = 0;
            return new Promise((resolve, reject) => {
                function tryNext(){
                    if (idx >= endpoints.length) return reject(new Error('No endpoints disponibles'));
                    const url = endpoints[idx++];
                    $.getJSON(url).done(resolve).fail(tryNext);
                }
                tryNext();
            });
        }

        $(document).on('change', '#pa-cliente-select', function(){
            const id = $(this).val();
            if (!id || id === 'null' || id === 'undefined') { 
                paFillFromCliente(null);
                return; 
            }
            $('#pa-cliente-id').val(id||'');
            $('#oc-clienteId').val(id||'');
            actualizarFolios(id);
            fetchClienteDetalle(id)
                .then(function(cli){
                    if (!cli) {
                        paFillFromCliente(null);
                        return;
                    }
                    // Usar la función paFillFromCliente que ya maneja correctamente direccion_detalle
                    paFillFromCliente(cli);
                    // Marcar campos como solo lectura
                    $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono, #pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-pais, #pa-dir-cp, #pa-dir-resumen')
                        .prop('readonly', true);
                })
                .catch(function(){
                    // Fallback: intentar obtener desde el cache de clientes
                    if (paClientesCache && Array.isArray(paClientesCache)) {
                        const cli = paClientesCache.find(function(c){
                            return String(c.id ?? '') === String(id);
                        });
                        if (cli) {
                            paFillFromCliente(cli);
                            $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono, #pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-pais, #pa-dir-cp, #pa-dir-resumen')
                                .prop('readonly', true);
                            return;
                        }
                    }
                    // Último fallback: usar data-* del option seleccionado
                    const $opt = $('#pa-cliente-select option:selected');
                    const nombre = $opt.text().split(' — ')[0] || '';
                    $('#pa-cli-nombre').val(nombre);
                    $('#pa-cli-email').val($opt.data('email')||'');
                    $('#pa-cli-telefono').val($opt.data('telefono')||'');
                    $('#pa-dir-calle').val($opt.data('calle')||'');
                    $('#pa-dir-numext').val($opt.data('numext')||'');
                    $('#pa-dir-numint').val($opt.data('numint')||'');
                    $('#pa-dir-ciudad').val($opt.data('ciudad')||'');
                    $('#pa-dir-estado').val($opt.data('estado')||'');
                    $('#pa-dir-pais').val($opt.data('pais')||'');
                    $('#pa-dir-cp').val($opt.data('cp')||'');
                    const resumen = [
                        $opt.data('calle'), $opt.data('numext'), $opt.data('numint'),
                        $opt.data('ciudad'), $opt.data('estado'), $opt.data('cp'), $opt.data('pais')
                    ].filter(Boolean).join(', ');
                    $('#pa-dir-resumen').val(resumen);
                    // Cargar clasificación si está disponible en el cache
                    if (paClientesCache && Array.isArray(paClientesCache)) {
                        const cli = paClientesCache.find(function(c){
                            return String(c.id ?? '') === String(id);
                        });
                        if (cli && cli.clasificacion) {
                            $('#pa-cla-nombre').val(cli.clasificacion.nombre || '');
                            $('#pa-cla-descripcion').val(cli.clasificacion.descripcion || '');
                        }
                    }
                    $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono, #pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-pais, #pa-dir-cp, #pa-dir-resumen')
                        .prop('readonly', true);
                });
        });

        // Hint: aquí puedes implementar el POST para crear OC y OP con estos datos cuando des a Continuar/Guardar.

        // Manejar click en botón de ver documento PDF
        $(document).on('click', '.btn-ver-documento', function(){
            const id = $(this).data('id');
            if (!id) return;
            
            const $modal = $('#pedidoDocumentoModal');
            const $loading = $('#pedido-documento-loading');
            const $iframe = $('#pedido-documento-iframe');
            const $downloadPdf = $('#pedido-documento-download-pdf');
            const $downloadExcel = $('#pedido-documento-download-excel');
            const loadTimeoutMs = 15000;
            
            // Mostrar loading y ocultar iframe
            $loading.show();
            $iframe.hide();
            $iframe.off('load');
            
            // URLs del PDF y Excel
            const pdfUrl = '<?= base_url('modulo1/pedido') ?>/' + id + '/pdf';
            const excelUrl = '<?= base_url('modulo1/pedido') ?>/' + id + '/excel';
            
            // Configurar iframe y enlaces de descarga
            $iframe.attr('src', pdfUrl);
            $downloadPdf.attr('href', pdfUrl);
            $downloadExcel.attr('href', excelUrl);
            
            // Resetear estado de botones al abrir modal
            $downloadPdf.prop('disabled', false).html('<i class="bi bi-file-earmark-pdf"></i> Descargar PDF');
            $downloadExcel.prop('disabled', false).html('<i class="bi bi-file-earmark-excel"></i> Descargar Excel');
            
            // Cuando el iframe cargue, ocultar loading y mostrar iframe
            const timeoutId = setTimeout(function(){
                if ($iframe.is(':hidden')) {
                    $loading.hide();
                    $iframe.show();
                }
            }, loadTimeoutMs);

            $iframe.one('load', function(){
                clearTimeout(timeoutId);
                $loading.hide();
                $iframe.show();
            });
        });

        // Manejar click en botón de descargar PDF
        $(document).on('click', '#pedido-documento-download-pdf', function(e){
            const $btn = $(this);
            if ($btn.prop('disabled')) {
                e.preventDefault();
                return false;
            }
            
            // Bloquear botón
            $btn.prop('disabled', true);
            const originalHtml = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm text-light me-2" role="status" aria-hidden="true"></span>');
            
            // Re-habilitar después de 5 segundos (tiempo suficiente para iniciar descarga)
            setTimeout(function(){
                $btn.prop('disabled', false).html(originalHtml);
            }, 5000);
        });

        // Manejar click en botón de descargar Excel
        $(document).on('click', '#pedido-documento-download-excel', function(e){
            const $btn = $(this);
            if ($btn.prop('disabled')) {
                e.preventDefault();
                return false;
            }
            
            // Bloquear botón
            $btn.prop('disabled', true);
            const originalHtml = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm me-2 text-light" role="status" aria-hidden="true"></span>');
            
            // Re-habilitar después de 5 segundos (tiempo suficiente para iniciar descarga)
            setTimeout(function(){
                $btn.prop('disabled', false).html(originalHtml);
            }, 5000);
        });
        
        // Limpiar iframe al cerrar el modal
        $('#pedidoDocumentoModal').on('hidden.bs.modal', function(){
            $('#pedido-documento-iframe').attr('src', '').hide();
            $('#pedido-documento-loading').show();
            // Resetear botones al cerrar modal
            $('#pedido-documento-download-pdf').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf"></i> Descargar PDF');
            $('#pedido-documento-download-excel').prop('disabled', false).html('<i class="bi bi-file-earmark-excel"></i> Descargar Excel');
        });
    });
    
    // Estilos para el indicador de carga
    $('<style>')
        .appendTo('head')
        .text(`
            .loading {
                position: relative;
                min-height: 200px;
            }
            .loading:after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: #666;
                font-weight: bold;
            }
            .spinner-border {
                border-right-color: transparent !important;
            }
            .pe-talla-row td {
                vertical-align: middle;
            }
            #pe-tallas-tabla th {
                white-space: nowrap;
            }
            #pe-tallas-tabla .form-select-sm {
                min-width: 100px;
            }
            #pe-tallas-tabla .pe-talla-cantidad {
                max-width: 100px;
                margin-left: auto;
            }

            #op-tallas-tabla select,
            #op-tallas-tabla input,
            #pe-tallas-tabla select,
            #pe-tallas-tabla input {
                color: #212529 !important;
                background-color: #fff !important;
            }

            #op-tallas-tabla select option,
            #pe-tallas-tabla select option {
                color: #212529 !important;
            }
        `);
</script>

<?= $this->endSection() ?>

<!-- (La copia extra del modal y script que sigue en tu plantilla original se deja intacta por compatibilidad) -->
