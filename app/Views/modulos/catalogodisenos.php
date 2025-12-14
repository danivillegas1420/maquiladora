<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Botones DataTables: estilo píldora y separación uniforme */
    .dt-buttons.btn-group .btn{
        margin-left: 0 !important;
        margin-right: .5rem;
        border-radius: 50rem !important; /* pill */
        padding: .35rem .75rem;
    }

    .dt-buttons.btn-group .btn:last-child{ margin-right: 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="me-3">CATÁLOGO DE DISEÑOS</h1>
    <div class="d-flex gap-2">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoDisenoModal">
        <i class="bi bi-plus-circle"></i> NUEVO DISEÑO
    </button>
        <button type="button" class="btn btn-outline-primary" title="Sexo" data-bs-toggle="modal" data-bs-target="#modalSexo">
            <i class="bi bi-gender-ambiguous"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" title="Tallas" data-bs-toggle="modal" data-bs-target="#modalTallas">
            <i class="bi bi-rulers"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" title="Corte" data-bs-toggle="modal" data-bs-target="#modalCorte">
            <i class="bi bi-scissors"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" title="Tipo de ropa" data-bs-toggle="modal" data-bs-target="#modalTipoRopa">
            <i class="bi bi-bag"></i>
        </button>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <table id="tablaDisenos" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>NOMBRE</th>
                <th>DESCRIPCIÓN</th>
                <th>VERSIÓN</th>
                <th>PRECIO UNIDAD</th>
                <th>MATERIALES</th>
                <th>ACCIONES</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($disenos)): ?>
                <?php foreach ($disenos as $d): ?>
                    <tr>
                        <td><?= esc($d['id']) ?></td>
                        <td><strong><?= esc($d['nombre']) ?></strong></td>
                        <td class="text-start"><?= esc($d['descripcion']) ?></td>
                        <td><?= esc($d['version']) ?></td>
                        <td><?= isset($d['precio_unidad']) && $d['precio_unidad'] !== '' ? esc($d['precio_unidad']) : '-' ?></td>
                        <td class="text-start">
                            <?php if (!empty($d['materiales'])): ?>
                                <ul class="material-list">
                                    <?php foreach ($d['materiales'] as $m): ?>
                                        <li><?= esc($m) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <em>Sin materiales</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-ver-modal"
                                    data-id="<?= (int)$d['id'] ?>"
                                    data-nombre="<?= esc($d['nombre']) ?>"
                                    data-descripcion="<?= esc($d['descripcion']) ?>"
                                    data-version="<?= esc($d['version']) ?>"
                                    data-materiales='<?= esc(!empty($d['materiales']) ? implode(", ", $d['materiales']) : "Sin materiales") ?>'
                                    data-imagen="<?= isset($d['imagen']) ? esc($d['imagen']) : '' ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#disenoModal">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary me-1 btn-editar-modal"
                                    title="Editar"
                                    data-id="<?= (int)$d['id'] ?>"
                                    data-codigo="<?= isset($d['codigo']) ? esc($d['codigo']) : '' ?>"
                                    data-nombre="<?= esc($d['nombre']) ?>"
                                    data-descripcion="<?= esc($d['descripcion']) ?>"
                                    data-version="<?= esc($d['version']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editarDisenoModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar"
                                    data-id="<?= (int)$d['id'] ?>">
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

<!-- Modal Bootstrap: Nuevo Diseño -->
<div class="modal fade" id="nuevoDisenoModal" tabindex="-1" aria-labelledby="nuevoDisenoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoDisenoLabel">Nuevo diseño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoDiseno">
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vista previa Foto</label>
                            <div id="nuevoFotoPreview" class="border rounded p-2 bg-light mb-2" style="min-height:160px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted">Sin foto seleccionada</span>
                            </div>
                            <input type="file" name="foto" id="inputFoto" class="form-control" accept="image/*" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vista previa Patrón</label>
                            <div id="nuevoPatronPreview" class="border rounded p-2 bg-light mb-2" style="min-height:160px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted">Sin patrón seleccionado</span>
                            </div>
                            <input type="file" name="patron" id="inputPatron" class="form-control" accept="image/*,.pdf" />
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" class="form-control" />
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre<span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Versión<span class="text-danger">*</span></label>
                            <input type="text" name="version" class="form-control" placeholder="v1.0" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notas</label>
                            <input type="text" name="notas" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio por unidad</label>
                            <input type="number" name="precio_unidad" class="form-control" min="0" step="0.01" placeholder="0.00" inputmode="decimal" />
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Cliente (opcional)</label>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select" name="clienteId" id="selClienteNuevo" disabled>
                                </select>
                                <div id="clienteNuevoSpinner" class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem; display: none;">
                                    <span class="visually-hidden"></span>
                                </div>
                            </div>
                            <small class="text-muted">Si lo dejas vacío, el diseño se crea sin cliente asignado.</small>
                        </div>
                        <!-- Catálogos: sexo, talla, tipo corte, tipo ropa -->
                        <div class="col-md-3">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="idSexoFK" id="selSexo" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Talla</label>
                            <select class="form-select" name="idTallasFK" id="selTalla" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de corte</label>
                            <select class="form-select" name="idTipoCorteFK" id="selTipoCorte" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de ropa</label>
                            <select class="form-select" name="idTipoRopaFK" id="selTipoRopa" disabled>
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="aprobadoCheck" name="aprobado" disabled>
                                <label class="form-check-label" for="aprobadoCheck">Aprobado</label>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label">Materiales (desde artículos)</label>
                            <input type="text" id="buscarArticulo" class="form-control mb-2" placeholder="Buscar por nombre, unidad o SKU..." />
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="fw-semibold small mb-1">Disponibles</div>
                                    <div id="listaDisponibles" class="border rounded p-2" style="max-height: 220px; overflow:auto;"></div>
                                </div>
                            </div>
                            <small class="text-muted">Marca los materiales; aparecerán abajo para capturar cantidades.</small>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm align-middle">
                                    <thead>
                                    <tr>
                                        <th style="width:5%"></th>
                                        <th style="width:55%">Artículo</th>
                                        <th style="width:20%">Cantidad por unidad</th>
                                        <th style="width:20%">Merma % (opc)</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tblMaterialesBody">
                                    <tr class="text-muted" id="rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="nuevoDisenoAlert" class="alert alert-danger mt-3 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarDiseno" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Editar Diseño -->
<div class="modal fade" id="editarDisenoModal" tabindex="-1" aria-labelledby="editarDisenoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarDisenoLabel">Editar diseño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarDiseno">
                    <input type="hidden" name="id" id="e-id" />
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vista previa Foto</label>
                            <div id="eFotoPreview" class="border rounded p-2 bg-light mb-2" style="min-height:160px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted">Sin foto</span>
                            </div>
                            <input type="file" name="foto" id="eInputFoto" class="form-control" accept="image/*" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vista previa Patrón</label>
                            <div id="ePatronPreview" class="border rounded p-2 bg-light mb-2" style="min-height:160px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted">Sin patrón</span>
                            </div>
                            <input type="file" name="patron" id="eInputPatron" class="form-control" accept="image/*,.pdf" />
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" id="e-codigo" class="form-control" />
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre<span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="e-nombre" class="form-control" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="e-descripcion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Versión<span class="text-danger">*</span></label>
                            <input type="text" name="version" id="e-version" class="form-control" placeholder="v1.0" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="e-fecha" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notas</label>
                            <input type="text" name="notas" id="e-notas" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="idSexoFK" id="e-selSexo" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Talla</label>
                            <select class="form-select" name="idTallasFK" id="e-selTalla" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de corte</label>
                            <select class="form-select" name="idTipoCorteFK" id="e-selTipoCorte" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de ropa</label>
                            <select class="form-select" name="idTipoRopaFK" id="e-selTipoRopa" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio por unidad</label>
                            <input type="number" name="precio_unidad" id="e-precio_unidad" class="form-control" min="0" step="0.01" placeholder="0.00" inputmode="decimal" />
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="e-aprobadoCheck" name="aprobado" disabled>
                                <label class="form-check-label" for="e-aprobadoCheck">Aprobado</label>
                            </div>
                        </div>
                        <div class="col-md-9 d-flex align-items-end">
                            <div class="w-100">
                                <label class="form-label">Cliente (opcional)</label>
                                <div class="d-flex align-items-center gap-2">
                                    <select class="form-select" name="clienteId" id="e-selCliente" disabled>
                                        <option value=""></option>
                                    </select>
                                    <div id="e-clienteSpinner" class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem; display: none;">
                                        <span class="visualmente-hidden"></span>
                                    </div>
                                </div>
                                <small class="text-muted">Si lo dejas vacío, se mantiene o elimina la relación (según guardes).</small>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div id="e-materialesSummary" class="alert alert-secondary py-2 d-none"></div>
                        </div>

                        <div class="col-12 mt-2">
                            <label class="form-label">Materiales (agregar/quitar)</label>
                            <input type="text" id="e-buscarArticulo" class="form-control mb-2" placeholder="Buscar por nombre, unidad o SKU..." />
                            <div class="row g-2">
                                <div class="col-12">
                                    <div id="e-listaDisponibles" class="border rounded p-2" style="max-height: 220px; overflow:auto;"></div>
                                </div>
                            </div>
                            <small class="text-muted">Marca para agregar; aparecerán abajo para capturar cantidades.</small>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm align-middle">
                                    <thead>
                                    <tr>
                                        <th style="width:5%"></th>
                                        <th style="width:55%">Artículo</th>
                                        <th style="width:20%">Cantidad por unidad</th>
                                        <th style="width:20%">Merma % (opc)</th>
                                    </tr>
                                    </thead>
                                    <tbody id="e-tblMaterialesBody">
                                    <tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="editarDisenoAlert" class="alert alert-danger mt-3 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnActualizarDiseno" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Detalles del diseño -->
<div class="modal fade" id="disenoModal" tabindex="-1" aria-labelledby="disenoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="disenoModalLabel"><i class="bi bi-eye"></i> Detalles del diseño</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <!-- Sección de imágenes visuales -->
                <div class="row g-3 mb-4" id="m-imagenes-section">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-image"></i> Fotografía del diseño</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px; background: #f8f9fa;">
                                <div id="m-foto-preview" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                    <span class="text-muted"><i class="bi bi-image" style="font-size: 3rem;"></i><br>Sin fotografía</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Patrón / Documento</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px; background: #f8f9fa;">
                                <div id="m-patron-preview" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                    <span class="text-muted"><i class="bi bi-file-earmark" style="font-size: 3rem;"></i><br>Sin patrón</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del diseño -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información del diseño</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 text-dark">
                            <dt class="col-sm-3 fw-semibold text-dark">ID</dt>
                            <dd class="col-sm-3 text-dark" id="m-id">-</dd>
                            <dt class="col-sm-3 fw-semibold text-dark">Código</dt>
                            <dd class="col-sm-3 text-dark" id="m-codigo">-</dd>
                            
                            <dt class="col-sm-3 fw-semibold text-dark">Nombre</dt>
                            <dd class="col-sm-9 text-dark" id="m-nombre">-</dd>
                            
                            <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>
                            <dd class="col-sm-9 text-dark" id="m-descripcion">-</dd>
                            
                            <dt class="col-sm-3 fw-semibold text-dark">Versión</dt>
                            <dd class="col-sm-3 text-dark" id="m-version">-</dd>
                            <dt class="col-sm-3 fw-semibold text-dark">Fecha versión</dt>
                            <dd class="col-sm-3 text-dark" id="m-fecha">-</dd>
                            
                            <dt class="col-sm-3 fw-semibold text-dark">Notas</dt>
                            <dd class="col-sm-9 text-dark" id="m-notas">-</dd>
                            
                            <dt class="col-sm-3 fw-semibold text-dark">Precio unidad</dt>
                            <dd class="col-sm-3 text-dark" id="m-precio">-</dd>
                            <dt class="col-sm-3 fw-semibold text-dark">Aprobado</dt>
                            <dd class="col-sm-3 text-dark" id="m-aprobado">-</dd>
                            
                            <dt class="col-sm-3 fw-semibold text-dark">Materiales</dt>
                            <dd class="col-sm-9 text-dark" id="m-materiales">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Catálogo Sexo -->
<div class="modal fade" id="modalSexo" tabindex="-1" aria-labelledby="modalSexoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSexoLabel">Catálogo de Sexo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar/editar -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <form id="formSexo" class="row g-2 align-items-end">
                            <input type="hidden" id="sexo-id-edit" value="">
                            <div class="col-md-4">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sexo-nombre" required placeholder="Ej: Masculino">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="sexo-descripcion" placeholder="Descripción opcional">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="btnSexoGuardar">
                                    <i class="bi bi-plus-circle"></i> Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Tabla de datos -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaSexo">
                        <thead class="table-light">
                        <tr>
                            <th style="width:10%">ID</th>
                            <th style="width:30%">Nombre</th>
                            <th style="width:45%">Descripción</th>
                            <th style="width:15%">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="4" class="text-center text-muted"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Catálogo Tallas -->
<div class="modal fade" id="modalTallas" tabindex="-1" aria-labelledby="modalTallasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTallasLabel">Catálogo de Tallas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar/editar -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <form id="formTallas" class="row g-2 align-items-end">
                            <input type="hidden" id="tallas-id-edit" value="">
                            <div class="col-md-4">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tallas-nombre" required placeholder="Ej: Chica">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="tallas-descripcion" placeholder="Descripción opcional">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="btnTallasGuardar">
                                    <i class="bi bi-plus-circle"></i> Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Tabla de datos -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaTallas">
                        <thead class="table-light">
                        <tr>
                            <th style="width:10%">ID</th>
                            <th style="width:30%">Nombre</th>
                            <th style="width:45%">Descripción</th>
                            <th style="width:15%">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="4" class="text-center text-muted"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Catálogo Tipo de Corte -->
<div class="modal fade" id="modalCorte" tabindex="-1" aria-labelledby="modalCorteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCorteLabel">Catálogo de Tipo de Corte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar/editar -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <form id="formCorte" class="row g-2 align-items-end">
                            <input type="hidden" id="corte-id-edit" value="">
                            <div class="col-md-4">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="corte-nombre" required placeholder="Ej: Recto">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="corte-descripcion" placeholder="Descripción opcional">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="btnCorteGuardar">
                                    <i class="bi bi-plus-circle"></i> Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Tabla de datos -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaCorte">
                        <thead class="table-light">
                        <tr>
                            <th style="width:10%">ID</th>
                            <th style="width:30%">Nombre</th>
                            <th style="width:45%">Descripción</th>
                            <th style="width:15%">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="4" class="text-center text-muted"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Catálogo Tipo de Ropa -->
<div class="modal fade" id="modalTipoRopa" tabindex="-1" aria-labelledby="modalTipoRopaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTipoRopaLabel">Catálogo de Tipo de Ropa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar/editar -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <form id="formTipoRopa" class="row g-2 align-items-end">
                            <input type="hidden" id="tiporopa-id-edit" value="">
                            <div class="col-md-4">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tiporopa-nombre" required placeholder="Ej: Camisa">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="tiporopa-descripcion" placeholder="Descripción opcional">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="btnTipoRopaGuardar">
                                    <i class="bi bi-plus-circle"></i> Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Tabla de datos -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaTipoRopa">
                        <thead class="table-light">
                        <tr>
                            <th style="width:10%">ID</th>
                            <th style="width:30%">Nombre</th>
                            <th style="width:45%">Descripción</th>
                            <th style="width:15%">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="4" class="text-center text-muted"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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

<!-- Export helpers (Buttons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Visores DXF (si no cargan, se hace fallback) -->
<script src="https://unpkg.com/three@0.158.0/build/three.min.js"></script>
<script src="https://unpkg.com/dxf-parser@4.11.5/dist/dxf-parser.min.js"></script>
<script src="https://unpkg.com/three-dxf@1.0.0/dist/three-dxf.js"></script>

<script>
    // Render genérico según extensión: imagen/PDF/DXF/otro
    function renderFilePreview(containerId, url) {
        const el = document.getElementById(containerId.replace(/^#/,''));
        if (!el) return;
        el.innerHTML = '';
        if (!url) { el.innerHTML = '<div class="text-muted">Sin archivo</div>'; return; }
        const u = String(url);
        const lower = u.toLowerCase();
        const isImg = /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/.test(lower) || /^blob:/.test(lower) || /^data:image\//.test(lower);
        const isPdf = /\.pdf(\?.*)?$/.test(lower) || /^data:application\/pdf/.test(lower);
        const isDxf = /\.dxf(\?.*)?$/.test(lower);
        if (isImg) {
            el.innerHTML = '<img src="'+u+'" class="img-fluid rounded border" alt="preview">';
            return;
        }
        if (isPdf) {
            el.innerHTML = '<object data="'+u+'" type="application/pdf" width="100%" height="360"><div class="text-muted p-2">No se pudo mostrar el PDF.</div></object>';
            return;
        }
        if (isDxf) {
            el.innerHTML = '<div style="height:360px; background:#f8f9fa;" class="rounded border d-flex align-items-center justify-content-center"></div>';
            // pequeño delay para asegurar que el div exista
            setTimeout(function(){ renderDXF(containerId.replace(/^#/,''), u); }, 30);
            return;
        }
        el.innerHTML = '<a href="'+u+'" target="_blank" class="btn btn-outline-secondary btn-sm">Abrir archivo</a>';
    }
    // Helper: renderizar DXF si hay librerías disponibles
    function renderDXF(containerId, url) {
        const el = document.getElementById(containerId);
        if (!el) return;
        try {
            if (window.THREE && window.DXF && typeof window.DXF.Viewer === 'function') {
                new window.DXF.Viewer(el, url);
                return;
            }
            if (window.THREE && window.DxfViewer) {
                const viewer = new window.DxfViewer(el, { autoResize: true, clearColor: 0xffffff });
                if (typeof viewer.load === 'function') viewer.load(url);
                return;
            }
            if (window.DxfParser) {
                fetch(url).then(r => r.text()).then(txt => {
                    try {
                        const parser = new window.DxfParser();
                        parser.parseSync(txt);
                        el.innerHTML = '<div class="p-3 text-muted">DXF cargado (se requieren librerías de render para ver la geometría).</div>';
                    } catch (e) {
                        el.innerHTML = '<div class="p-3 text-muted">No fue posible interpretar el DXF.</div>';
                    }
                }).catch(() => {
                    el.innerHTML = '<div class="p-3 text-muted">No fue posible descargar el DXF.</div>';
                });
            }
            el.innerHTML = 'Vista previa DXF no disponible en este navegador.';
        } catch (e) {
            el.innerHTML = 'Error cargando DXF.';
        }
    }

    // ====== Lógica existente (editar/nuevo) — se conserva ======
    let eSeleccionados = new Set();

    function e_labelArticulo(a){
        return (a.nombre || ('ID '+a.id)) + (a.unidadMedida ? ' ('+a.unidadMedida+')' : '') + (a.sku ? ' • ' + a.sku : '');
    }
    function e_pintarDisponibles(filtro = ''){
        const $disp = $('#e-listaDisponibles');
        $disp.empty();
        const idsSeleccionados = eSeleccionados;
        const q = (filtro||'').toLowerCase();
        let count = 0;
        (articulosCache||[]).forEach(a => {
            if (idsSeleccionados.has(String(a.id))) return;
            const hay = [a.nombre, a.unidadMedida, a.sku].filter(Boolean).join(' ').toLowerCase();
            if (q && !hay.includes(q)) return;
            const item = '<label class="form-check d-flex align-items-center gap-2 mb-1">'
                + '<input class="form-check-input e-chk-disp" type="checkbox" value="'+a.id+'" />'
                + '<span>'+e_labelArticulo(a)+'</span>'
                + '</label>';
            $disp.append(item);
            count++;
        });
        if (count === 0) $disp.html('<div class="text-muted px-2">Sin coincidencias</div>');
    }
    function e_renderMateriales(){
        const ids = Array.from(eSeleccionados);
        const $tb = $('#e-tblMaterialesBody');
        $tb.empty();
        if (ids.length === 0){
            $tb.append('<tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
            return;
        }
        ids.forEach(id => {
            const art = (articulosCache||[]).find(a => String(a.id)===String(id));
            const nombre = art ? e_labelArticulo(art) : ('ID '+id);
            const row = `
                    <tr data-id="${id}">
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger e-btn-del-row" title="Quitar"><i class="bi bi-x"></i></button></td>
                        <td>${nombre}</td>
                        <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm e-inp-cant" placeholder="0" /></td>
                        <td><input type="number" min="0" step="0.01" class="form-control form-control-sm e-inp-merma" placeholder="0" /></td>
                    </tr>`;
            $tb.append(row);
        });
    }
    $(document).on('click', '.btn-editar-modal', function(){
        const id = $(this).data('id');
        eSeleccionados = new Set();
        $('#formEditarDiseno')[0].reset();
        $('#e-id').val(id);
        $('#e-materialesSummary').addClass('d-none').empty();
        $('#e-tblMaterialesBody').html('<tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
        const modalEl = document.getElementById('editarDisenoModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
        const $btn = $(this);
        if ($btn.data('codigo') !== undefined) $('#e-codigo').val($btn.data('codigo'));
        if ($btn.data('nombre') !== undefined) $('#e-nombre').val($btn.data('nombre'));
        if ($btn.data('descripcion') !== undefined) $('#e-descripcion').val($btn.data('descripcion'));
        if ($btn.data('version') !== undefined) $('#e-version').val($btn.data('version'));
        if (articulosCache === null) { cargarArticulos(); }
        e_pintarDisponibles();
        // Cargar catálogos para edición (solo registros de la maquiladora actual)
        const q1 = cargarCatalogo('<?= base_url('modulo2/catalogos/sexo') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#e-selSexo');
        const q2 = cargarCatalogo('<?= base_url('modulo2/catalogos/tallas') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#e-selTalla');
        const q3 = cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-corte') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#e-selTipoCorte');
        const q4 = cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-ropa') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#e-selTipoRopa');
        // Cargar clientes (opcional)
        let preCli = null;
        const cargarClientesEditar = function(preselectId){
            const $sel = $('#e-selCliente');
            const $sp = $('#e-clienteSpinner');
            if (window._clientesCacheEditar) {
                $sel.empty();
                $sel.append('<option value="">(Sin cliente)</option>');
                window._clientesCacheEditar.forEach(c => $sel.append(`<option value="${c.id}">${c.nombre}</option>`));
                if (preselectId) {
                    $sel.val(String(preselectId));
                    // Si no existe en el catálogo, agregar opción de fallback
                    if ($sel.val() !== String(preselectId)) {
                        $sel.append(`<option value="${preselectId}">(ID ${preselectId})</option>`);
                        $sel.val(String(preselectId));
                    }
                }
                $sel.prop('disabled', false); $sp.hide(); return;
            }
            $sp.show(); $sel.prop('disabled', true);
            $.ajax({ url: '<?= base_url('modulo1/clientes/json') ?>', method: 'GET' })
                .done(function(list){
                    window._clientesCacheEditar = (list || []).map(it => ({ id: it.id, nombre: it.nombre }));
                    $sel.empty();
                    $sel.append('<option value="">(Sin cliente)</option>');
                    window._clientesCacheEditar.forEach(c => $sel.append(`<option value="${c.id}">${c.nombre}</option>`));
                    if (preselectId) {
                        $sel.val(String(preselectId));
                        if ($sel.val() !== String(preselectId)) {
                            $sel.append(`<option value="${preselectId}">(ID ${preselectId})</option>`);
                            $sel.val(String(preselectId));
                        }
                    }
                    $sel.prop('disabled', false);
                })
                .fail(function(){ $sel.empty().append('<option value="">Error al cargar</option>'); })
                .always(function(){ $sp.hide(); });
        };
        $.getJSON('<?= base_url('modulo2/diseno') ?>/' + id + '/json')
            .done(function (data) {
                $('#e-codigo').val(data.codigo||'');
                $('#e-nombre').val(data.nombre||'');
                $('#e-descripcion').val(data.descripcion||'');
                $('#e-version').val(data.version||'');
                if (data.fecha) { $('#e-fecha').val(String(data.fecha).slice(0,10)); }
                $('#e-notas').val(data.notas||'');
                if (data.precio_unidad !== undefined && data.precio_unidad !== null) { $('#e-precio_unidad').val(data.precio_unidad); }
                $('#e-archivoCadUrl').val(data.archivoCadUrl||'');
                $('#e-archivoPatronUrl').val(data.archivoPatronUrl||'');
                const apr = data.aprobado;
                $('#e-aprobadoCheck').prop('checked', apr === 1 || apr === true || apr === '1');
                preCli = (data.clienteId !== undefined && data.clienteId !== null && data.clienteId !== '') ? String(data.clienteId) : null;
                
                
                // Previews iniciales en modal Editar
                console.log('Datos recibidos:', data);
                console.log('Foto:', data.foto ? 'Sí tiene foto' : 'No tiene foto');
                console.log('Patron:', data.patron ? 'Sí tiene patron' : 'No tiene patron');
                
                try {
                    let fotoUrl = '';
                    if (data.foto) {
                        // Detectar tipo de imagen simple
                        let mime = 'image/jpeg';
                        if (String(data.foto).startsWith('iVBOR')) mime = 'image/png';
                        fotoUrl = 'data:' + mime + ';base64,' + data.foto;
                        console.log('FotoUrl generada:', fotoUrl.substring(0, 50) + '...');
                    }
                    renderFilePreview('eFotoPreview', fotoUrl);

                    let patronUrl = '';
                    if (data.patron) {
                        if (String(data.patron).startsWith('JVBERi')) {
                            patronUrl = 'data:application/pdf;base64,' + data.patron;
                        } else {
                            // Asumir imagen
                            let mime = 'image/jpeg';
                            if (String(data.patron).startsWith('iVBOR')) mime = 'image/png';
                            patronUrl = 'data:' + mime + ';base64,' + data.patron;
                        }
                        console.log('PatronUrl generada:', patronUrl.substring(0, 50) + '...');
                    }
                    // Render especial para PDF en iframe si es necesario, o usar renderFilePreview
                    if (patronUrl.startsWith('data:application/pdf')) {
                        const el = document.getElementById('ePatronPreview');
                        if (el) el.innerHTML = '<iframe src="'+patronUrl+'" width="100%" height="400px" style="border:none;"></iframe>';
                    } else {
                        renderFilePreview('ePatronPreview', patronUrl);
                    }
                } catch(e) { console.error('Error rendering previews', e); }

                // Setear FKs cuando carguen los catálogos
                console.log('idTallasFK:', data.idTallasFK);
                $.when(q1, q2, q3, q4).always(function(){
                    if (data.idSexoFK !== undefined && data.idSexoFK !== null) {
                        console.log('Seteando sexo:', data.idSexoFK);
                        $('#e-selSexo').val(String(data.idSexoFK));
                    }
                    if (data.idTallasFK !== undefined && data.idTallasFK !== null) {
                        console.log('Seteando talla:', data.idTallasFK);
                        $('#e-selTalla').val(String(data.idTallasFK));
                        console.log('Valor del select talla después:', $('#e-selTalla').val());
                    }
                    if (data.idTipoCorteFK !== undefined && data.idTipoCorteFK !== null) $('#e-selTipoCorte').val(String(data.idTipoCorteFK));
                    if (data.idTipoRopaFK !== undefined && data.idTipoRopaFK !== null) $('#e-selTipoRopa').val(String(data.idTipoRopaFK));
                    cargarClientesEditar(preCli);
                });

                const mats = data.materiales || [];
                if (mats.length > 0) {
                    let html = '<div class="small"><strong>Materiales actuales:</strong><ul class="mb-0">';
                    mats.forEach(m => {
                        if (typeof m === 'string') html += '<li>'+m+'</li>';
                        else html += '<li>'+(m.nombre||'Material') + (m.cantidad? (' x '+m.cantidad):'') + (m.merma? (' • merma '+m.merma+'%'):'') + '</li>';
                    });
                    html += '</ul></div>';
                    $('#e-materialesSummary').removeClass('d-none').html(html);
                }

                const det = data.materialesDet || [];
                if (det.length > 0) {
                    $('#e-materialesSummary').addClass('d-none').empty();
                    eSeleccionados = new Set(det.map(r => String(r.articuloId)));
                    e_renderMateriales();
                    det.forEach(r => {
                        const tr = $('#e-tblMaterialesBody tr[data-id="'+String(r.articuloId)+'"]');
                        if (tr.length) {
                            if (r.cantidadPorUnidad !== undefined && r.cantidadPorUnidad !== null) {
                                tr.find('.e-inp-cant').val(r.cantidadPorUnidad);
                            }
                            if (r.mermaPct !== undefined && r.mermaPct !== null) {
                                tr.find('.e-inp-merma').val(r.mermaPct);
                            }
                        }
                    });
                }
                
                // Siempre actualizar la lista de disponibles después de cargar los datos
                const q = ($('#e-buscarArticulo').val()||'').toString().toLowerCase().trim();
                e_pintarDisponibles(q);
            })
            .fail(function(xhr){
                $('#editarDisenoAlert').removeClass('d-none').text('No fue posible cargar los datos');
            });
    });
    // Actualizar previews en cambios de campos (Editar)
    $(document).on('change', '#editarDisenoModal input[name="foto"]', function(){
        const f = this.files && this.files[0];
        renderFilePreview('eFotoPreview', f ? URL.createObjectURL(f) : '');
    });
    $(document).on('change', '#editarDisenoModal input[name="patron"]', function(){
        const f = this.files && this.files[0];
        if (!f) {
            renderFilePreview('ePatronPreview', '');
            return;
        }
        const url = URL.createObjectURL(f);
        if (f.type === 'application/pdf') {
            const el = document.getElementById('ePatronPreview');
            el.innerHTML = '<iframe src="'+url+'" width="100%" height="400px" style="border:none;"></iframe>';
        } else {
            renderFilePreview('ePatronPreview', url);
        }
    });

    // Nuevo: limpiar y actualizar previews
    $('#nuevoDisenoModal').on('show.bs.modal', function(){
        renderFilePreview('nuevoFotoPreview', '');
        renderFilePreview('nuevoPatronPreview', '');
        $('#inputFoto').val('');
        $('#inputPatron').val('');
    });
    
    $(document).on('change', '#nuevoDisenoModal input[name="foto"]', function(){
        const f = this.files && this.files[0];
        renderFilePreview('nuevoFotoPreview', f ? URL.createObjectURL(f) : '');
    });
    $(document).on('change', '#nuevoDisenoModal input[name="patron"]', function(){
        const f = this.files && this.files[0];
        if (!f) {
            renderFilePreview('nuevoPatronPreview', '');
            return;
        }
        const url = URL.createObjectURL(f);
        if (f.type === 'application/pdf') {
            const el = document.getElementById('nuevoPatronPreview');
            el.innerHTML = '<iframe src="'+url+'" width="100%" height="400px" style="border:none;"></iframe>';
        } else {
            renderFilePreview('nuevoPatronPreview', url);
        }
    });
    $(document).on('input', '#e-buscarArticulo', function(){
        const q = ($(this).val()||'').toString().toLowerCase().trim();
        e_pintarDisponibles(q);
    });
    $(document).on('change', '#e-listaDisponibles .e-chk-disp', function(){
        const id = String(this.value);
        if (this.checked) eSeleccionados.add(id); else eSeleccionados.delete(id);
        e_renderMateriales();
        if (!this.checked) {
            const q = ($('#e-buscarArticulo').val()||'').toString().toLowerCase().trim();
            e_pintarDisponibles(q);
        }
    });
    $(document).on('click', '.e-btn-del-row', function(){
        const $tr = $(this).closest('tr');
        const id = String($tr.data('id'));
        $tr.remove();
        eSeleccionados.delete(id);
        const q = ($('#e-buscarArticulo').val()||'').toString().toLowerCase().trim();
        e_pintarDisponibles(q);
        if ($('#e-tblMaterialesBody tr[data-id]').length === 0) {
            $('#e-tblMaterialesBody').html('<tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
        }
    });
    $('#btnActualizarDiseno').on('click', function(){
        const id = $('#e-id').val();
        const $alert = $('#editarDisenoAlert');
        $alert.addClass('d-none').text('');
        
        // Habilitar todos los selects antes de crear el FormData para que sus valores se incluyan
        try { $('#e-selCliente').prop('disabled', false); } catch(e) {}
        try { $('#e-selSexo').prop('disabled', false); } catch(e) {}
        try { $('#e-selTalla').prop('disabled', false); } catch(e) {}
        try { $('#e-selTipoCorte').prop('disabled', false); } catch(e) {}
        try { $('#e-selTipoRopa').prop('disabled', false); } catch(e) {}
        
        const formEl = document.getElementById('formEditarDiseno');
        const fd = new FormData(formEl);
        fd.set('clienteId', ($('#e-selCliente').val() || ''));
        const materials = [];
        $('#e-tblMaterialesBody tr').each(function(){
            const idm = $(this).data('id');
            if (!idm) return;
            const cant = parseFloat($(this).find('.e-inp-cant').val() || '0');
            const merma = $(this).find('.e-inp-merma').val();
            const mermaNum = merma === '' ? null : parseFloat(merma);
            materials.push({ articuloId: parseInt(idm,10), cantidadPorUnidad: isNaN(cant)?0:cant, mermaPct: (mermaNum===null||isNaN(mermaNum))?null:mermaNum });
        });
        if (materials.length > 0) fd.append('materials', JSON.stringify(materials));
        Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se actualizará el diseño.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('modulo2/disenos/actualizar') ?>/' + id,
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
            }).done(function(resp){
                if (resp && (resp.ok || resp.success)) {
                    Swal.fire({ title:'¡Guardado!', text:'Cambios aplicados.', icon:'success' }).then(()=>{
                        const modalEl = document.getElementById('editarDisenoModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        window.location.reload();
                    });
                } else {
                    const msg = resp && (resp.message||resp.error) ? (resp.message||resp.error) : 'No se pudo actualizar.';
                    Swal.fire({ title:'Error', text: msg, icon:'error' });
                }
            }).fail(function(xhr){
                let msg = 'Error al actualizar.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire({ title:'Error', text: msg, icon:'error' });
            });
        });
    });

    // ====== Nuevo diseño ======
    let articulosCache = null;
    const seleccionados = new Set();
    const renderMateriales = () => {
        const selected = Array.from(seleccionados);
        const $tb = $('#tblMaterialesBody');
        $tb.empty();
        if (selected.length === 0) {
            $tb.append('<tr class="text-muted" id="rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
            return;
        }
        selected.forEach(id => {
            const art = (articulosCache || []).find(a => String(a.id) === String(id));
            const nombre = art ? (art.nombre + (art.unidadMedida ? ' ('+art.unidadMedida+')' : '')) : ('ID ' + id);
            const row = `
                    <tr data-id="${id}">
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-del-row" title="Quitar"><i class="bi bi-x"></i></button></td>
                        <td>${nombre}</td>
                        <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm inp-cant" placeholder="0" /></td>
                        <td><input type="number" min="0" step="0.01" class="form-control form-control-sm inp-merma" placeholder="0" /></td>
                    </tr>`;
            $tb.append(row);
        });
    };
    function cargarArticulos(){
        const url = '<?= base_url('modulo2/articulos/json') ?>' + '?_=' + Date.now();
        const $disp = $('#listaDisponibles');
        $disp.html('<div class="text-muted px-2"></div>');
        return $.getJSON(url)
            .done(function(resp){
                articulosCache = (resp && resp.items) ? resp.items : [];
                pintarDisponibles();
            })
            .fail(function(xhr){
                console.error('Error cargando artículos', xhr && xhr.status, xhr && xhr.responseText);
                $('#listaDisponibles').html('<div class="text-danger px-2">Error</div>');
            });
    }
    function labelArticulo(a){
        return (a.nombre || ('ID '+a.id)) + (a.unidadMedida ? ' ('+a.unidadMedida+')' : '') + (a.sku ? ' • ' + a.sku : '');
    }
    function pintarDisponibles(filtro = ''){
        const $disp = $('#listaDisponibles');
        $disp.empty();
        const idsSeleccionados = seleccionados;
        const q = (filtro||'').toLowerCase();
        let count = 0;
        (articulosCache||[]).forEach(a => {
            if (idsSeleccionados.has(String(a.id))) return;
            const hay = [a.nombre, a.unidadMedida, a.sku].filter(Boolean).join(' ').toLowerCase();
            if (q && !hay.includes(q)) return;
            const item = '<label class="form-check d-flex align-items-center gap-2 mb-1">'
                + '<input class="form-check-input chk-disp" type="checkbox" value="'+a.id+'" />'
                + '<span>'+labelArticulo(a)+'</span>'
                + '</label>';
            $disp.append(item);
            count++;
        });
        if (count === 0) { $disp.html('<div class="text-muted px-2">Sin coincidencias</div>'); }
    }
    function initCatalogos(){
        // Cargar catálogos en paralelo con cache-busting
        cargarCatalogo('<?= base_url('modulo2/catalogos/sexo') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#selSexo');
        cargarCatalogo('<?= base_url('modulo2/catalogos/tallas') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#selTalla');
        cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-corte') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#selTipoCorte');
        cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-ropa') ?>' + '?solo_maquiladora=1&_=' + Date.now(), '#selTipoRopa');
    }
    // Clientes (opcional) para nuevo diseño
    let clientesCacheNuevo = null;
    function cargarClientesNuevo(preselectId){
        const $sel = $('#selClienteNuevo');
        const $sp = $('#clienteNuevoSpinner');
        if (clientesCacheNuevo) {
            $sel.empty();
            $sel.append('<option value="">(Sin cliente)</option>');
            clientesCacheNuevo.forEach(c => $sel.append(`<option value="${c.id}">${c.nombre}</option>`));
            if (preselectId) { $sel.val(String(preselectId)); }
            $sel.prop('disabled', false);
            $sp.hide();
            return;
        }
        $sp.show();
        $sel.prop('disabled', true);
        $.ajax({ url: '<?= base_url('modulo1/clientes/json') ?>', method: 'GET' })
            .done(function(list){
                clientesCacheNuevo = (list || []).map(it => ({ id: it.id, nombre: it.nombre }));
                $sel.empty();
                $sel.append('<option value="">(Sin cliente)</option>');
                clientesCacheNuevo.forEach(c => $sel.append(`<option value="${c.id}">${c.nombre}</option>`));
                if (preselectId) { $sel.val(String(preselectId)); }
                $sel.prop('disabled', false);
            })
            .fail(function(){
                $sel.empty().append('<option value="">Error al cargar</option>');
            })
            .always(function(){ $sp.hide(); });
    }
    $('#nuevoDisenoModal').on('show.bs.modal shown.bs.modal', function(){
        if (articulosCache === null) { cargarArticulos(); }
        initCatalogos();
        cargarClientesNuevo();
    });
    $(document).on('input', '#buscarArticulo', function(){
        const q = ($(this).val() || '').toString().toLowerCase().trim();
        pintarDisponibles(q);
    });
    $(document).on('change', '#listaDisponibles .chk-disp', function(){
        const id = String(this.value);
        if (this.checked) seleccionados.add(id); else seleccionados.delete(id);
        renderMateriales();
        if (!this.checked) {
            const q = ($('#buscarArticulo').val()||'').toString().toLowerCase().trim();
            pintarDisponibles(q);
        }
    });
    $(document).on('click', '.btn-del-row', function(){
        const $tr = $(this).closest('tr');
        const id = String($tr.data('id'));
        $tr.remove();
        const $sel = $('#listaSeleccionados');
        const opt = $sel.find('option[value="'+id+'"]');
        if (opt.length) { opt.remove(); }
        pintarDisponibles($('#buscarArticulo').val()||'');
        if ($('#tblMaterialesBody tr[data-id]').length === 0) {
            $('#tblMaterialesBody').html('<tr class="text-muted" id="rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
        }
    });
    $('#btnGuardarDiseno').on('click', function(){
        const $alert = $('#nuevoDisenoAlert');
        $alert.addClass('d-none').text('');
        const formEl = document.getElementById('formNuevoDiseno');
        const fd = new FormData(formEl);
        // Asegurar clienteId en el payload (por si el select está cargando)
        try { $('#selClienteNuevo').prop('disabled', false); } catch(e) {}
        fd.set('clienteId', ($('#selClienteNuevo').val() || ''));
        const materials = [];
        $('#tblMaterialesBody tr').each(function(){
            const id = $(this).data('id');
            if (!id) return;
            const cant = parseFloat($(this).find('.inp-cant').val() || '0');
            const merma = $(this).find('.inp-merma').val();
            const mermaNum = merma === '' ? null : parseFloat(merma);
            materials.push({ articuloId: parseInt(id,10), cantidadPorUnidad: isNaN(cant)?0:cant, mermaPct: (mermaNum===null||isNaN(mermaNum))?null:mermaNum });
        });
        if (materials.length > 0) fd.append('materials', JSON.stringify(materials));
        Swal.fire({
            title: '¿Guardar diseño?',
            text: 'Se creará un nuevo diseño.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('modulo2/disenos/crear') ?>',
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
            }).done(function(resp){
                if (resp && resp.ok) {
                    Swal.fire({ title:'¡Guardado!', text:'Diseño creado correctamente.', icon:'success' }).then(()=>{
                        const modalEl = document.getElementById('nuevoDisenoModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        window.location.reload();
                    });
                } else {
                    const msg = resp && resp.message ? resp.message : 'No se pudo guardar.';
                    Swal.fire({ title:'Error', text: msg, icon:'error' });
                }
            }).fail(function(xhr){
                let msg = 'Error al guardar.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire({ title:'Error', text: msg, icon:'error' });
            });
        });
    });

    // ==== Helpers: cargar catálogos con spinner ====
    function cargarCatalogo(url, selectSel){
        const $sel = $(selectSel);
        $sel.prop('disabled', true).html('<option value=""></option>');
        return $.getJSON(url)
            .done(function(resp){
                const items = (resp && resp.items) ? resp.items : [];
                if (!items.length){
                    $sel.html('<option value="">Sin datos</option>');
                } else {
                    const opts = ['<option value="">Seleccione…</option>'];
                    items.forEach(function(it){
                        const id = it.id ?? it.ID ?? it.Id;
                        const nombre = (it.nombre || it.name || ('ID '+id));
                        opts.push('<option value="'+id+'">'+nombre+'</option>');
                    });
                    $sel.html(opts.join(''));
                }
            })
            .fail(function(){ $sel.html('<option value="">Error al cargar</option>'); })
            .always(function(){ $sel.prop('disabled', false); });
    }

    // ====== DataTable con Buttons (izquierda) ======
    $(document).ready(function () {
        // Fallback: por si no se dispara el evento del modal
        if (document.getElementById('nuevoDisenoModal')) {
            try { initCatalogos(); } catch(e) { console.warn('initCatalogos error', e); }
            try { if (articulosCache === null) cargarArticulos(); } catch(e) { console.warn('cargarArticulos error', e); }
        }
        // Español + layout con botones a la izquierda y buscador a la derecha
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
        const fileName = 'catalogo_disenos_' + fecha;

        $('#tablaDisenos').DataTable({
            language: langES,
            columnDefs: [{ targets: -1, orderable:false, searchable:false }], // ACCIONES
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

        // Botones de acción (editar/eliminar) con delegación para soportar paginación/redraw
        $(document).on('click', '.btn-accion', function() {
            const action = $(this).attr('title');
            const id = $(this).data('id');
            if (action === 'Editar') {
                window.location.href = '<?= base_url('modulo2/editardiseno/') ?>' + id;
            } else if (action === 'Eliminar') {
                Swal.fire({
                    title: '¿Eliminar diseño?',
                    text: 'No podrás revertir esta acción.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result)=>{
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: '<?= base_url('modulo2/disenos/eliminar') ?>/'+id,
                        method: 'POST',
                    }).done(function(resp){
                        if (resp && resp.ok) {
                            Swal.fire({ title:'Eliminado', text: 'Diseño eliminado correctamente', icon:'success' }).then(()=>{
                                window.location.reload();
                            });
                        } else {
                            const msg = (resp && (resp.message || resp.error)) ? (resp.message || resp.error) : 'No se pudo eliminar';
                            Swal.fire({ title:'Error', text: msg, icon:'error' });
                        }
                    }).fail(function(xhr){
                        let msg = 'Error al eliminar';
                        if (xhr && xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                            msg = xhr.responseJSON.message || xhr.responseJSON.error;
                        } else if (xhr && typeof xhr.responseText === 'string' && xhr.responseText.trim() !== '') {
                            msg = xhr.responseText;
                        }
                        // Mostrar motivo específico cuando el backend devuelve 409/422 etc.
                        Swal.fire({ title:'No se pudo eliminar', text: msg, icon:'error' });
                    });
                });
            }
        });

        // Modal Detalles (AJAX)
        $(document).on('click', '.btn-ver-modal', function () {
            const id = $(this).data('id');
            // Limpiar campos
            $('#m-id,#m-codigo,#m-nombre,#m-version,#m-fecha,#m-notas,#m-aprobado,#m-precio').text('-');
            $('#m-descripcion').text('');
            $('#m-materiales').text('-');
            
            // Limpiar previews
            $('#m-foto-preview').html('<span class="text-muted"><i class="bi bi-image" style="font-size: 3rem;"></i><br>Sin fotografía</span>');
            $('#m-patron-preview').html('<span class="text-muted"><i class="bi bi-file-earmark" style="font-size: 3rem;"></i><br>Sin patrón</span>');

            $.getJSON('<?= base_url('modulo2/diseno') ?>/' + id + '/json')
                .done(function (data) {
                    $('#m-id').text(data.id || id);
                    $('#m-codigo').text(data.codigo || '-');
                    $('#m-nombre').text(data.nombre || '-');
                    $('#m-descripcion').text(data.descripcion || '-');
                    $('#m-version').text(data.version || '-');
                    $('#m-fecha').text(data.fecha ? (new Date(data.fecha)).toISOString().slice(0,10) : '-');
                    $('#m-notas').text(data.notas || '-');
                    $('#m-precio').text((data.precio_unidad !== undefined && data.precio_unidad !== null && data.precio_unidad !== '') ? data.precio_unidad : '-');
                    
                    // Mostrar materiales
                    if (data.materiales && Array.isArray(data.materiales) && data.materiales.length > 0) {
                        const matHtml = '<ul class="mb-0">' + data.materiales.map(m => {
                            if (typeof m === 'string') return '<li>' + m + '</li>';
                            return '<li>' + (m.nombre || 'Material') + 
                                   (m.cantidad ? ' x ' + m.cantidad : '') + 
                                   (m.merma ? ' • merma ' + m.merma + '%' : '') + '</li>';
                        }).join('') + '</ul>';
                        $('#m-materiales').html(matHtml);
                    } else {
                        $('#m-materiales').text('Sin materiales');
                    }

                    // Mostrar aprobado
                    const apr = data.aprobado;
                    $('#m-aprobado').text(apr === 1 || apr === true || apr === '1' ? 'Sí' : (apr === 0 || apr === false || apr === '0' ? 'No' : '-'));

                    // Mostrar foto (base64 o URL)
                    if (data.foto) {
                        const fotoSrc = data.foto.startsWith('data:') ? data.foto : 'data:image/jpeg;base64,' + data.foto;
                        $('#m-foto-preview').html('<img src="' + fotoSrc + '" class="img-fluid rounded" style="max-height: 280px;" alt="Fotografía del diseño">');
                    } else if (data.imagenes && data.imagenes.length > 0) {
                        $('#m-foto-preview').html('<img src="' + data.imagenes[0] + '" class="img-fluid rounded" style="max-height: 280px;" alt="Fotografía del diseño">');
                    } else if (data.imagenUrl) {
                        $('#m-foto-preview').html('<img src="' + data.imagenUrl + '" class="img-fluid rounded" style="max-height: 280px;" alt="Fotografía del diseño">');
                    }

                    // Mostrar patrón (base64 o URL)
                    if (data.patron) {
                        const patronSrc = data.patron.startsWith('data:') ? data.patron : 'data:application/pdf;base64,' + data.patron;
                        // Intentar detectar si es imagen o PDF
                        if (patronSrc.includes('image/')) {
                            $('#m-patron-preview').html('<img src="' + patronSrc + '" class="img-fluid rounded" style="max-height: 280px;" alt="Patrón">');
                        } else {
                            $('#m-patron-preview').html('<iframe src="' + patronSrc + '" width="100%" height="280px" style="border: none;" class="rounded"></iframe>');
                        }
                    } else if (data.archivoPatronUrl) {
                        const isPdf = /\.(pdf)$/i.test(data.archivoPatronUrl);
                        const isImage = /\.(png|jpg|jpeg|gif|bmp|webp|svg)$/i.test(data.archivoPatronUrl);
                        if (isImage) {
                            $('#m-patron-preview').html('<img src="' + data.archivoPatronUrl + '" class="img-fluid rounded" style="max-height: 280px;" alt="Patrón">');
                        } else if (isPdf) {
                            $('#m-patron-preview').html('<iframe src="' + data.archivoPatronUrl + '" width="100%" height="280px" style="border: none;" class="rounded"></iframe>');
                        } else {
                            $('#m-patron-preview').html('<div class="text-muted"><i class="bi bi-file-earmark"></i><br>Archivo: ' + data.archivoPatronUrl + '</div>');
                        }
                    }
                })
                .fail(function () {
                    $('#m-descripcion').text('No fue posible cargar los datos');
                });
        });


        // Configuración de catálogos
        const catalogosConfig = {
            sexo: {
                url: '<?= base_url('modulo2/catalogos/sexo') ?>',
                tabla: '#tablaSexo',
                form: '#formSexo',
                idEdit: '#sexo-id-edit',
                nombre: '#sexo-nombre',
                descripcion: '#sexo-descripcion',
                btnGuardar: '#btnSexoGuardar',
                tablaNombre: 'sexo',
                idField: 'id_sexo'
            },
            tallas: {
                url: '<?= base_url('modulo2/catalogos/tallas') ?>',
                tabla: '#tablaTallas',
                form: '#formTallas',
                idEdit: '#tallas-id-edit',
                nombre: '#tallas-nombre',
                descripcion: '#tallas-descripcion',
                btnGuardar: '#btnTallasGuardar',
                tablaNombre: 'tallas',
                idField: 'id_talla'
            },
            corte: {
                url: '<?= base_url('modulo2/catalogos/tipo-corte') ?>',
                tabla: '#tablaCorte',
                form: '#formCorte',
                idEdit: '#corte-id-edit',
                nombre: '#corte-nombre',
                descripcion: '#corte-descripcion',
                btnGuardar: '#btnCorteGuardar',
                tablaNombre: 'tipo-corte',
                idField: 'id_tipo_corte'
            },
            tiporopa: {
                url: '<?= base_url('modulo2/catalogos/tipo-ropa') ?>',
                tabla: '#tablaTipoRopa',
                form: '#formTipoRopa',
                idEdit: '#tiporopa-id-edit',
                nombre: '#tiporopa-nombre',
                descripcion: '#tiporopa-descripcion',
                btnGuardar: '#btnTipoRopaGuardar',
                tablaNombre: 'tipo-ropa',
                idField: 'id_tipo_ropa'
            }
        };

        // Función genérica para cargar datos de catálogo en una tabla
        function cargarCatalogoEnModal(config) {
            const $tbody = $(config.tabla + ' tbody');
            $tbody.html('<tr><td colspan="4" class="text-center text-muted"></td></tr>');

            // Siempre filtrar por maquiladora actual en estos catálogos
            const sep = config.url.indexOf('?') !== -1 ? '&' : '?';
            const url = config.url + sep + 'solo_maquiladora=1&t=' + Date.now();

            $.getJSON(url)
                .done(function(resp) {
                    const items = (resp && resp.items) ? resp.items : [];
                    $tbody.empty();
                    
                    if (items.length === 0) {
                        $tbody.html('<tr><td colspan="4" class="text-center text-muted">No hay registros</td></tr>');
                        return;
                    }
                    
                    items.forEach(function(item) {
                        const id = item.id || '-';
                        const nombre = $('<div>').text(item.nombre || '').html();
                        const descripcion = $('<div>').text(item.descripcion || '').html();
                        const row = '<tr data-id="' + id + '">' +
                            '<td>#' + id + '</td>' +
                            '<td><input type="text" class="form-control form-control-sm" value="' + nombre + '" data-field="nombre" readonly></td>' +
                            '<td><input type="text" class="form-control form-control-sm" value="' + descripcion + '" data-field="descripcion" readonly></td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-success btn-editar-item" data-id="' + id + '" title="Editar">' +
                            '<i class="bi bi-pencil"></i></button> ' +
                            '<button type="button" class="btn btn-sm btn-danger btn-eliminar-item" data-id="' + id + '" title="Eliminar">' +
                            '<i class="bi bi-trash"></i></button>' +
                            '</td>' +
                            '</tr>';
                        $tbody.append(row);
                    });
                })
                .fail(function(xhr) {
                    $tbody.html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los datos</td></tr>');
                    console.error('Error:', xhr);
                });
        }

        // Función para limpiar formulario
        function limpiarFormulario(config) {
            $(config.idEdit).val('');
            $(config.nombre).val('');
            $(config.descripcion).val('');
            $(config.btnGuardar).html('<i class="bi bi-plus-circle"></i> Agregar');
        }

        // Función para cargar datos en formulario para editar
        function cargarEnFormulario(config, id, nombre, descripcion) {
            $(config.idEdit).val(id);
            $(config.nombre).val(nombre);
            $(config.descripcion).val(descripcion);
            $(config.btnGuardar).html('<i class="bi bi-floppy"></i> Guardar');
        }

        // Obtener token CSRF
        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) return meta.getAttribute('content');
            const input = document.querySelector('input[name="csrf_test_name"]');
            if (input) return input.value;
            return '';
        }
        
        // Obtener nombre del campo CSRF
        function getCsrfTokenName() {
            return 'csrf_test_name';
        }

        // Función para guardar (crear o actualizar)
        function guardarCatalogo(config) {
            const $btn = $(config.btnGuardar);
            
            // Bloquear botón si ya está procesando
            if ($btn.prop('disabled')) {
                return;
            }
            
            const id = $(config.idEdit).val();
            const nombre = $(config.nombre).val().trim();
            const descripcion = $(config.descripcion).val().trim();
            
            if (!nombre) {
                Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre es obligatorio' });
                return;
            }

            // Bloquear botón y campos del formulario
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Guardando...');
            $(config.nombre).prop('disabled', true);
            $(config.descripcion).prop('disabled', true);

            const url = id ? 
                '<?= base_url('modulo2/catalogos') ?>/' + config.tablaNombre + '/actualizar/' + id :
                '<?= base_url('modulo2/catalogos') ?>/' + config.tablaNombre + '/crear';
            
            const method = 'POST';
            const csrfToken = getCsrfToken();
            const data = {
                nombre: nombre,
                descripcion: descripcion || null
            };
            
            // Agregar token CSRF si existe (en body y header)
            const headers = { 
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (csrfToken) {
                data[getCsrfTokenName()] = csrfToken;
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            $.ajax({
                url: url,
                method: method,
                data: data,
                processData: true,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                headers: headers
            })
            .done(function(resp) {
                console.log('Respuesta del servidor:', resp);
                // Manejar respuesta como texto si no es JSON
                if (typeof resp === 'string') {
                    try {
                        resp = JSON.parse(resp);
                    } catch(e) {
                        console.error('Error parseando respuesta:', e);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Respuesta inválida del servidor' });
                        return;
                    }
                }
                
                if (resp && (resp.ok === true || resp.success === true)) {
                    Swal.fire({ icon: 'success', title: 'Guardado', text: id ? 'Registro actualizado' : 'Registro creado', timer: 1500, showConfirmButton: false });
                    limpiarFormulario(config);
                    cargarCatalogoEnModal(config);
                } else {
                    const msg = resp && resp.message ? resp.message : (resp && resp.error ? resp.error : 'No se pudo guardar');
                    console.error('Error en respuesta:', resp);
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            })
            .always(function() {
                // Desbloquear botón y campos siempre (éxito o error)
                $btn.prop('disabled', false).html(originalHtml);
                $(config.nombre).prop('disabled', false);
                $(config.descripcion).prop('disabled', false);
            })
            .fail(function(xhr) {
                console.error('Error guardando catálogo:', xhr.status, xhr.statusText, xhr.responseText);
                let msg = 'Error al guardar';
                
                if (xhr.status === 403) {
                    msg = 'Error de autenticación CSRF. Por favor, recarga la página.';
                } else if (xhr.status === 404) {
                    msg = 'Endpoint no encontrado. Verifica la URL.';
                } else if (xhr.status === 500) {
                    msg = 'Error interno del servidor.';
                }
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    else if (xhr.responseJSON.error) msg = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        msg = resp.message || resp.error || msg;
                    } catch(e) {
                        if (xhr.responseText.length < 200) {
                            msg = xhr.responseText;
                        }
                    }
                }
                
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error (' + xhr.status + ')', 
                    text: msg,
                    footer: 'URL: ' + url
                });
            });
        }

        // Función para eliminar
        function eliminarCatalogo(config, id, $btnEliminar) {
            Swal.fire({
                title: '¿Eliminar registro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;
                
                // Bloquear botón si ya está procesando
                if ($btnEliminar && $btnEliminar.prop('disabled')) {
                    return;
                }
                
                // Bloquear botón y mostrar estado de procesamiento
                let originalHtml = '';
                if ($btnEliminar) {
                    originalHtml = $btnEliminar.html();
                    $btnEliminar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                }
                
                const csrfToken = getCsrfToken();
                const data = {};
                const headers = { 
                    'X-Requested-With': 'XMLHttpRequest'
                };
                if (csrfToken) {
                    data[getCsrfTokenName()] = csrfToken;
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }
                
                $.ajax({
                    url: '<?= base_url('modulo2/catalogos') ?>/' + config.tablaNombre + '/eliminar/' + id,
                    method: 'POST',
                    data: data,
                    processData: true,
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    headers: headers
                })
                .done(function(resp) {
                    if (resp && (resp.ok || resp.success)) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', text: 'Registro eliminado', timer: 1500, showConfirmButton: false });
                        cargarCatalogoEnModal(config);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo eliminar' });
                    }
                })
                .always(function() {
                    // Desbloquear botón siempre (éxito o error)
                    if ($btnEliminar && originalHtml) {
                        $btnEliminar.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    let msg = 'Error al eliminar';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const resp = JSON.parse(xhr.responseText);
                            msg = resp.message || msg;
                        } catch(e) {
                            msg = xhr.responseText.substring(0, 100);
                        }
                    }
                    console.error('Error eliminando catálogo:', xhr.status, xhr.responseText);
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                });
            });
        }

        // Mapeo de keys a IDs de modales
        const modalIds = {
            sexo: 'Sexo',
            tallas: 'Tallas',
            corte: 'Corte',
            tiporopa: 'TipoRopa'
        };

        // Event handlers para cada catálogo
        Object.keys(catalogosConfig).forEach(function(key) {
            const config = catalogosConfig[key];
            const modalId = '#modal' + modalIds[key];
            
            // Cargar datos al abrir modal
            $(modalId).on('show.bs.modal', function() {
                limpiarFormulario(config);
                cargarCatalogoEnModal(config);
            });

            // Submit del formulario
            $(config.form).on('submit', function(e) {
                e.preventDefault();
                guardarCatalogo(config);
            });

            // Editar item (hacer campos editables y cambiar botón)
            $(document).on('click', config.tabla + ' .btn-editar-item', function() {
                const $btn = $(this);
                
                // Bloquear botón si ya está procesando
                if ($btn.prop('disabled')) {
                    return;
                }
                
                const $row = $btn.closest('tr');
                const id = $row.data('id');
                const $inputs = $row.find('input[data-field]');
                
                if ($btn.hasClass('editing')) {
                    // Guardar cambios inline
                    const nombre = $row.find('input[data-field="nombre"]').val().trim();
                    const descripcion = $row.find('input[data-field="descripcion"]').val().trim();
                    
                    if (!nombre) {
                        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre es obligatorio' });
                        return;
                    }

                    // Bloquear botón y mostrar estado de procesamiento
                    const originalHtml = $btn.html();
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

                    const csrfToken = getCsrfToken();
                    const data = { nombre: nombre, descripcion: descripcion || null };
                    const headers = { 
                        'X-Requested-With': 'XMLHttpRequest'
                    };
                    if (csrfToken) {
                        data[getCsrfTokenName()] = csrfToken;
                        headers['X-CSRF-TOKEN'] = csrfToken;
                    }

                    $.ajax({
                        url: '<?= base_url('modulo2/catalogos') ?>/' + config.tablaNombre + '/actualizar/' + id,
                        method: 'POST',
                        data: data,
                        processData: true,
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        headers: headers
                    })
                    .done(function(resp) {
                        if (resp && (resp.ok || resp.success)) {
                            $inputs.prop('readonly', true);
                            $btn.removeClass('editing').html('<i class="bi bi-pencil"></i>').attr('title', 'Editar');
                            Swal.fire({ icon: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false });
                            // Recargar tabla para reflejar cambios
                            cargarCatalogoEnModal(config);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo guardar' });
                        }
                    })
                    .always(function() {
                        // Desbloquear botón siempre (éxito o error)
                        if (!$btn.hasClass('editing')) {
                            // Si ya no está en modo edición, restaurar icono de lápiz
                            $btn.prop('disabled', false).html('<i class="bi bi-pencil"></i>').attr('title', 'Editar');
                        } else {
                            // Si sigue en modo edición, restaurar icono de check
                            $btn.prop('disabled', false).html('<i class="bi bi-check"></i>').attr('title', 'Guardar');
                        }
                    })
                    .fail(function(xhr) {
                        let msg = 'Error al guardar';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const resp = JSON.parse(xhr.responseText);
                                msg = resp.message || msg;
                            } catch(e) {
                                msg = xhr.responseText.substring(0, 100);
                            }
                        }
                        console.error('Error actualizando catálogo:', xhr.status, xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    });
                } else {
                    // Activar edición inline
                    $inputs.prop('readonly', false);
                    $btn.addClass('editing').html('<i class="bi bi-check"></i>').attr('title', 'Guardar');
                }
            });

            // Eliminar item
            $(document).on('click', config.tabla + ' .btn-eliminar-item', function() {
                const $btn = $(this);
                const id = $btn.data('id');
                eliminarCatalogo(config, id, $btn);
            });
        });
    });
</script>
<?= $this->endSection() ?>
