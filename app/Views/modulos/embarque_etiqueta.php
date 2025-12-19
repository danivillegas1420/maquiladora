<?= $this->extend('layouts/main') ?>

<?php
$embarque = $embarque ?? [];
$etiqueta = $etiqueta ?? null;

$embarqueId   = (int)($embarque['id'] ?? 0);
$folio        = $embarque['folio']        ?? ('ID '.$embarqueId);
$clienteNom   = $embarque['clienteNombre']?? '—';
$destino      = $embarque['destino']      ?? ($embarque['Domicilio'] ?? $embarque['direccion'] ?? '—');
$fechaSalida  = $embarque['fecha']        ?? '—';      // ajusta si usas fecha_salida
$contenedor   = $embarque['contenedor']   ?? '—';
$transportista= $embarque['transportista']?? '—';

$idEtiqueta   = $etiqueta['id']                  ?? '';
$codigo       = $etiqueta['codigo']              ?? ('ETQ-'.$folio);
$shipNom      = $etiqueta['ship_to_nombre']      ?? $clienteNom;
$shipDir      = $etiqueta['ship_to_direccion']   ?? $destino;
$shipCiudad   = $etiqueta['ship_to_ciudad']      ?? '';
$shipPais     = $etiqueta['ship_to_pais']        ?? 'México';
$referencia   = $etiqueta['referencia']          ?? '';
$pesoBruto    = $etiqueta['peso_bruto']          ?? '';
$pesoNeto     = $etiqueta['peso_neto']           ?? '';
$bultos       = $etiqueta['bultos']              ?? '';
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .card-etiqueta {
        max-width: 420px;
        border: 2px dashed #0d6efd;
        border-radius: 1rem;
        padding: 1.25rem;
        font-size: .9rem;
    }
    .card-etiqueta h5 {
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .card-etiqueta .small-label {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: .08em;
    }
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <h1 class="mb-0">Etiqueta de embarque</h1>
        <span class="badge bg-primary"><?= esc($folio) ?></span>
    </div>
    <a href="<?= site_url('modulo3/documentos') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a documentos
    </a>
</div>

<?php if (session()->getFlashdata('ok')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('ok')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Datos del embarque</strong>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Folio</dt>
                    <dd class="col-sm-8"><?= esc($folio) ?></dd>

                    <dt class="col-sm-4">Cliente</dt>
                    <dd class="col-sm-8"><?= esc($clienteNom) ?></dd>

                    <dt class="col-sm-4">Destino</dt>
                    <dd class="col-sm-8"><?= esc($destino) ?></dd>

                    <dt class="col-sm-4">Fecha</dt>
                    <dd class="col-sm-8"><?= esc($fechaSalida) ?></dd>

                    <dt class="col-sm-4">Contenedor</dt>
                    <dd class="col-sm-8"><?= esc($contenedor) ?></dd>

                    <dt class="col-sm-4">Transportista</dt>
                    <dd class="col-sm-8"><?= esc($transportista) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Etiqueta</strong>
                <?php if ($idEtiqueta): ?>
                    <span class="badge bg-success">Registrada</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pendiente</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-etiqueta mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Embarque</h5>
                        <span class="fw-bold"><?= esc($folio) ?></span>
                    </div>

                    <div class="mb-2">
                        <div class="small-label">Ship to</div>
                        <div><?= esc($shipNom) ?></div>
                        <div><?= esc($shipDir) ?></div>
                        <?php if ($shipCiudad || $shipPais): ?>
                            <div><?= esc(trim($shipCiudad.' '.$shipPais)) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-1 mb-2">
                        <div class="col-6">
                            <div class="small-label">Referencia</div>
                            <div><?= esc($referencia ?: '—') ?></div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="small-label">Código etiqueta</div>
                            <div><?= esc($codigo) ?></div>
                        </div>
                    </div>

                    <div class="row g-1">
                        <div class="col-4">
                            <div class="small-label">Bultos</div>
                            <div><?= esc($bultos ?: '—') ?></div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="small-label">Peso bruto</div>
                            <div><?= $pesoBruto !== '' ? esc($pesoBruto).' kg' : '—' ?></div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="small-label">Peso neto</div>
                            <div><?= $pesoNeto !== '' ? esc($pesoNeto).' kg' : '—' ?></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#etiquetaModal">
                        <i class="bi bi-pencil-square"></i>
                        <?= $idEtiqueta ? 'Editar etiqueta' : 'Crear etiqueta' ?>
                    </button>

                    <?php if ($idEtiqueta): ?>
                        <button type="button"
                                class="btn btn-outline-danger eliminar-etiqueta"
                                data-url="<?= site_url('logistica/etiqueta/'.$idEtiqueta.'/eliminar') ?>">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="etiquetaModal" tabindex="-1" aria-labelledby="etiquetaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="etiquetaModalLabel">
                    <?= $idEtiqueta ? 'Editar etiqueta' : 'Crear etiqueta' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('logistica/embarque/'.$embarqueId.'/etiqueta/guardar') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc($idEtiqueta) ?>">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código etiqueta</label>
                            <input type="text" name="codigo" class="form-control"
                                   value="<?= esc($codigo) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Referencia</label>
                            <input type="text" name="referencia" class="form-control"
                                   value="<?= esc($referencia) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ship to - nombre</label>
                            <input type="text" name="ship_to_nombre" class="form-control"
                                   value="<?= esc($shipNom) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ship to - dirección</label>
                            <textarea name="ship_to_direccion" class="form-control" rows="2" required><?= esc($shipDir) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ship_to_ciudad" class="form-control"
                                   value="<?= esc($shipCiudad) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">País</label>
                            <input type="text" name="ship_to_pais" class="form-control"
                                   value="<?= esc($shipPais) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bultos</label>
                            <input type="number" name="bultos" class="form-control" min="0"
                                   value="<?= esc($bultos) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peso bruto (kg)</label>
                            <input type="number" name="peso_bruto" step="0.01" min="0"
                                   class="form-control" value="<?= esc($pesoBruto) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peso neto (kg)</label>
                            <input type="number" name="peso_neto" step="0.01" min="0"
                                   class="form-control" value="<?= esc($pesoNeto) ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div></div>
</div>

<script>
document.querySelectorAll('.eliminar-etiqueta').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar esta etiqueta?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
