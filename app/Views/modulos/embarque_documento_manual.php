<?= $this->extend('layouts/main') ?>

<?php
$embarque = $embarque ?? [];
$items = $items ?? [];
$e = fn($k, $d = '') => esc($embarque[$k] ?? $d);
?>

<?= $this->section('styles') ?>
<style>
    body {
        background: #f5f6f8;
    }

    .card-shadow {
        box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
    }

    .table-tight td,
    .table-tight th {
        padding: .35rem .5rem;
    }

    .table-tight th {
        background: #eef2f7;
    }

    .muted {
        color: #6c757d;
        font-size: .9rem;
    }

    /* ======= IMPRESIÓN SOLO DEL DOCUMENTO (CARTA) ======= */
    @page {
        size: Letter;
        margin: 25mm 30mm;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #printArea,
        #printArea * {
            visibility: visible !important;
        }

        #printArea {
            position: absolute;
            inset: 0;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
        }

        #printArea .card,
        #printArea .card-shadow {
            border: none !important;
            box-shadow: none !important;
        }

        #printArea .card-body {
            padding: 0 !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h2 class="mb-0">Documento de Embarque <span class="badge bg-secondary">Manual</span></h2>
        <div class="small text-muted">Captura con formulario y previsualiza al momento.</div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" id="btnPrintPopup">
            <i class="bi bi-printer"></i> Imprimir / PDF
        </button>
        <button class="btn btn-success" id="btnGuardarPdf">
            <i class="bi bi-cloud-upload"></i> Guardar PDF
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Columna izquierda: FORM (no se imprime) -->
    <div class="col-lg-5 no-print">
        <div class="card card-shadow">
            <div class="card-header bg-white"><strong>Captura del embarque</strong></div>
            <div class="card-body">
                <form id="formManual" method="post" action="<?= site_url('modulo3/embarque/manual') ?>">
                    <?= csrf_field() ?>

                    <!-- Identificación -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Folio</label>
                            <input type="text" class="form-control" name="folio" id="f-folio"
                                value="<?= $e('folio', 'EMB-2025-0012') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="f-fecha"
                                value="<?= $e('fecha', date('Y-m-d')) ?>" required>
                        </div>
                    </div>

                    <hr>

                    <!-- Remitente / Destinatario -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Remitente</label>
                            <input class="form-control" name="remitente" id="f-remitente"
                                value="<?= $e('remitente', 'Textiles XYZ S.A. de C.V.') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RFC Remitente</label>
                            <input class="form-control" name="rfcRemitente" id="f-rfcRemitente"
                                value="<?= $e('rfcRemitente', 'TXY123456789') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Domicilio Remitente</label>
                            <input class="form-control" name="domicilioRemitente" id="f-domRem"
                                value="<?= $e('domicilioRemitente', 'Blvd. Industrial 123, Puebla, PUE, MX') ?>">
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Destinatario</label>
                            <input class="form-control" name="destinatario" id="f-destinatario"
                                value="<?= $e('destinatario', 'Comercializadora ABC S.A. de C.V.') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RFC Destinatario</label>
                            <input class="form-control" name="rfcDestinatario" id="f-rfcDest"
                                value="<?= $e('rfcDestinatario', 'ABC987654321') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Domicilio Destinatario</label>
                            <input class="form-control" name="domicilioDestinatario" id="f-domDest"
                                value="<?= $e('domicilioDestinatario', 'Av. Reforma 100, Cuauhtémoc, CDMX, MX') ?>">
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-12">
                            <label class="form-label">Origen</label>
                            <input class="form-control" name="origen" id="f-origen"
                                value="<?= $e('origen', 'Planta Textiles XYZ, Blvd. Industrial 123, Puebla, PUE, MX') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Destino</label>
                            <input class="form-control" name="destino" id="f-destino"
                                value="<?= $e('destino', 'Comercializadora ABC, Av. Reforma 100, Cuauhtémoc, CDMX, MX') ?>">
                        </div>
                    </div>

                    <hr>

                    <!-- Transporte -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de transporte</label>
                            <input class="form-control" name="tipoTransporte" id="f-tipo"
                                value="<?= $e('tipoTransporte', 'Terrestre (Camión)') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transportista</label>
                            <input class="form-control" name="transportista" id="f-transportista"
                                value="<?= $e('transportista', 'Transportes Morales S.A. de C.V.') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Operador</label>
                            <input class="form-control" name="operador" id="f-operador"
                                value="<?= $e('operador', 'Juan Pérez') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Placas</label>
                            <input class="form-control" name="placas" id="f-placas"
                                value="<?= $e('placas', 'XYZ-123-4') ?>">
                        </div>
                    </div>

                    <hr>

                    <!-- Items -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Partidas / Ítems</strong>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                                <i class="bi bi-plus-circle"></i> Agregar fila
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearRows">
                                <i class="bi bi-trash"></i> Vaciar
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle" id="tblItems">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:90px;">SKU</th>
                                    <th>Descripción</th>
                                    <th style="width:90px;">Cant.</th>
                                    <th style="width:80px;">UM</th>
                                    <th style="width:90px;">Peso Unit.</th>
                                    <th style="width:110px;">Valor Unit.</th>
                                    <th style="width:46px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($items)):
                                    foreach ($items as $it): ?>
                                        <tr>
                                            <td><input name="items_sku[]" class="form-control form-control-sm"
                                                    value="<?= esc($it['sku']) ?>"></td>
                                            <td><input name="items_desc[]" class="form-control form-control-sm"
                                                    value="<?= esc($it['descripcion']) ?>"></td>
                                            <td><input name="items_cant[]" type="number" step="0.01" min="0"
                                                    class="form-control form-control-sm text-end"
                                                    value="<?= esc($it['cantidad']) ?>"></td>
                                            <td><input name="items_um[]" class="form-control form-control-sm"
                                                    value="<?= esc($it['um']) ?>"></td>
                                            <td><input name="items_peso[]" type="number" step="0.0001" min="0"
                                                    class="form-control form-control-sm text-end"
                                                    value="<?= esc($it['peso'] ?? $it['pesoUnit'] ?? 0) ?>"></td>
                                            <td><input name="items_valor[]" type="number" step="0.01" min="0"
                                                    class="form-control form-control-sm text-end"
                                                    value="<?= esc($it['valor'] ?? $it['valorUnit'] ?? 0) ?>"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btnDel"><i
                                                        class="bi bi-x"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Referencia</label>
                            <input class="form-control" name="referencia" id="f-referencia"
                                value="<?= $e('referencia', 'OC-9981 / Pedido #45021') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" id="f-notas"
                                rows="2"><?= $e('notas', 'Manipular con cuidado. No apilar más de 4 tarimas.') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3 gap-2">
                        <button class="btn btn-outline-secondary" type="reset" id="btnReset">Restablecer</button>
                        <button class="btn btn-primary" type="submit">Aplicar (recargar)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Columna derecha: PREVIEW = ÁREA DE IMPRESIÓN -->
    <div class="col-lg-7">
        <div id="printArea">
            <div class="card card-shadow">
                <div class="card-body">
                    <h3 class="mb-0">Documento de Embarque</h3>
                    <div class="muted">
                        Folio: <strong id="p-folio"><?= esc($embarque['folio'] ?? '') ?></strong>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        Fecha: <strong id="p-fecha"><?= esc($embarque['fecha'] ?? '') ?></strong>
                    </div>

                    <div class="mt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-uppercase muted fw-bold">Remitente</div>
                                    <div id="p-remitente"><strong><?= esc($embarque['remitente'] ?? '') ?></strong>
                                    </div>
                                    <div id="p-rfcRem"><?= esc($embarque['rfcRemitente'] ?? '') ?></div>
                                    <div id="p-domRem"><?= esc($embarque['domicilioRemitente'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-uppercase muted fw-bold">Destinatario</div>
                                    <div id="p-destinatario">
                                        <strong><?= esc($embarque['destinatario'] ?? '') ?></strong></div>
                                    <div id="p-rfcDest"><?= esc($embarque['rfcDestinatario'] ?? '') ?></div>
                                    <div id="p-domDest"><?= esc($embarque['domicilioDestinatario'] ?? '') ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-uppercase muted fw-bold">Origen</div>
                                    <div id="p-origen"><?= esc($embarque['origen'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-uppercase muted fw-bold">Destino</div>
                                    <div id="p-destino"><?= esc($embarque['destino'] ?? '') ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-uppercase muted fw-bold">Transporte</div>
                                    <div>Tipo: <span id="p-tipo"><?= esc($embarque['tipoTransporte'] ?? '') ?></span>
                                    </div>
                                    <div>Transportista: <span
                                            id="p-transportista"><?= esc($embarque['transportista'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-uppercase muted fw-bold">Unidad</div>
                                    <div>Operador: <span id="p-operador"><?= esc($embarque['operador'] ?? '') ?></span>
                                    </div>
                                    <div>Placas: <span id="p-placas"><?= esc($embarque['placas'] ?? '') ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-tight align-middle">
                            <thead>
                                <tr>
                                    <th style="width:80px;">SKU</th>
                                    <th>Descripción</th>
                                    <th style="width:70px;" class="text-end">Cant.</th>
                                    <th style="width:60px;">UM</th>
                                    <th style="width:90px;" class="text-end">Peso Unit.</th>
                                    <th style="width:100px;" class="text-end">Valor Unit.</th>
                                    <th style="width:100px;" class="text-end">Peso</th>
                                    <th style="width:110px;" class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody id="p-items">
                                <?php
                                $totCant = 0;
                                $totPeso = 0;
                                $totImporte = 0;
                                foreach ($items as $it):
                                    $peso = (float) $it['cantidad'] * (float) ($it['peso'] ?? $it['pesoUnit'] ?? 0);
                                    $imp = (float) $it['cantidad'] * (float) ($it['valor'] ?? $it['valorUnit'] ?? 0);
                                    $totCant += (float) $it['cantidad'];
                                    $totPeso += $peso;
                                    $totImporte += $imp;
                                    ?>
                                    <tr>
                                        <td><?= esc($it['sku']) ?></td>
                                        <td><?= esc($it['descripcion']) ?></td>
                                        <td class="text-end"><?= number_format((float) $it['cantidad'], 2) ?></td>
                                        <td><?= esc($it['um']) ?></td>
                                        <td class="text-end">
                                            <?= number_format((float) ($it['peso'] ?? $it['pesoUnit'] ?? 0), 2) ?></td>
                                        <td class="text-end">
                                            $<?= number_format((float) ($it['valor'] ?? $it['valorUnit'] ?? 0), 2) ?></td>
                                        <td class="text-end"><?= number_format($peso, 2) ?></td>
                                        <td class="text-end">$<?= number_format($imp, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Totales</th>
                                    <th class="text-end" id="p-tcant"><?= number_format($totCant, 0) ?></th>
                                    <th></th>
                                    <th class="text-end" id="p-tpesounit"></th>
                                    <th></th>
                                    <th class="text-end" id="p-tpeso"><?= number_format($totPeso, 2) ?></th>
                                    <th class="text-end" id="p-timporte">$<?= number_format($totImporte, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3">
                        <div class="text-uppercase muted fw-bold">Referencia</div>
                        <div id="p-referencia"><?= esc($embarque['referencia'] ?? '') ?></div>
                    </div>
                    <div class="mt-2">
                        <div class="text-uppercase muted fw-bold">Notas</div>
                        <div id="p-notas"><?= esc($embarque['notas'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- libs para generar PDF en el navegador -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<script>
    (function () {
        const $ = (sel, ctx = document) => ctx.querySelector(sel);
        const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
        const fmt2 = n => (isFinite(n) ? Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00');
        const money = n => '$' + fmt2(n);

        // Enlazar campos del form con la vista previa
        const map = [
            ['#f-folio', '#p-folio', true],
            ['#f-fecha', '#p-fecha'],
            ['#f-remitente', '#p-remitente', true],
            ['#f-rfcRemitente', '#p-rfcRem'],
            ['#f-domRem', '#p-domRem'],
            ['#f-destinatario', '#p-destinatario', true],
            ['#f-rfcDestinatario', '#p-rfcDest'],
            ['#f-domDest', '#p-domDest'],
            ['#f-origen', '#p-origen'],
            ['#f-destino', '#p-destino'],
            ['#f-tipo', '#p-tipo'],
            ['#f-transportista', '#p-transportista'],
            ['#f-operador', '#p-operador'],
            ['#f-placas', '#p-placas'],
            ['#f-referencia', '#p-referencia'],
            ['#f-notas', '#p-notas']
        ];
        map.forEach(([fi, pi, bold]) => {
            const f = $(fi), p = $(pi);
            if (!f || !p) return;
            const up = () => p.innerHTML = bold ? '<strong>' + f.value + '</strong>' : f.value;
            f.addEventListener('input', up);
        });

        // Items dinámicos
        const tbl = $('#tblItems tbody');
        function recalcFromForm() {
            const rows = $$('#tblItems tbody tr');
            let tCant = 0, tPeso = 0, tImp = 0;
            const out = [];
            rows.forEach(tr => {
                const v = name => tr.querySelector(`[name^="${name}"]`)?.value ?? '';
                const sku = v('items_sku'), desc = v('items_desc');
                const cant = parseFloat(v('items_cant')) || 0;
                const um = v('items_um') || '';
                const pesoU = parseFloat(v('items_peso')) || 0;
                const valU = parseFloat(v('items_valor')) || 0;
                if (!sku && !desc && cant === 0 && pesoU === 0 && valU === 0) return;
                const peso = cant * pesoU, imp = cant * valU;
                tCant += cant; tPeso += peso; tImp += imp;
                out.push(`<tr>
                <td>${sku}</td><td>${desc}</td>
                <td class="text-end">${fmt2(cant)}</td><td>${um}</td>
                <td class="text-end">${fmt2(pesoU)}</td>
                <td class="text-end">${money(valU)}</td>
                <td class="text-end">${fmt2(peso)}</td>
                <td class="text-end">${money(imp)}</td>
            </tr>`);
            });
            $('#p-items').innerHTML = out.join('') || '<tr><td colspan="8" class="text-center text-muted">Sin partidas</td></tr>';
            $('#p-tcant').textContent = tCant.toLocaleString();
            $('#p-tpeso').textContent = fmt2(tPeso);
            $('#p-timporte').textContent = money(tImp);
        }
        tbl.addEventListener('input', recalcFromForm);
        tbl.addEventListener('click', e => {
            if (e.target.closest('.btnDel')) { e.target.closest('tr').remove(); recalcFromForm(); }
        });

        $('#btnAddRow')?.addEventListener('click', () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td><input name="items_sku[]"   class="form-control form-control-sm"></td>
            <td><input name="items_desc[]"  class="form-control form-control-sm"></td>
            <td><input name="items_cant[]"  type="number" step="0.01" min="0" class="form-control form-control-sm text-end"></td>
            <td><input name="items_um[]"    class="form-control form-control-sm" value="pz"></td>
            <td><input name="items_peso[]"  type="number" step="0.0001" min="0" class="form-control form-control-sm text-end"></td>
            <td><input name="items_valor[]" type="number" step="0.01" min="0" class="form-control form-control-sm text-end"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnDel"><i class="bi bi-x"></i></button></td>
        `;
            tbl.appendChild(tr);
        });

        $('#btnClearRows')?.addEventListener('click', async () => {
            const res = await Swal.fire({
                title: "¿Vaciar todas las partidas?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, vaciar"
            });
            if (res.isConfirmed) {
                tbl.innerHTML = ''; recalcFromForm();
                await Swal.fire({ title: "Listo", text: "Se vaciaron las partidas.", icon: "success" });
            }
        });

        // Imprimir en popup (referencia)
        $('#btnPrintPopup')?.addEventListener('click', () => {
            recalcFromForm();
            const html = document.getElementById('printArea').innerHTML;
            const w = window.open('', 'PRINT', 'width=900,height=650');
            w.document.write(`<!doctype html><html><head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Documento de Embarque</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @page { size: Letter; margin: 25mm 30mm; }
                html, body { background:#fff; } body { margin:0; }
                .page { width:auto; max-width:100%; margin:0; }
                .table-tight td, .table-tight th { padding:.35rem .5rem; }
                .table-tight th { background:#eef2f7; }
                @media print { .card, .card-shadow { border:none !important; box-shadow:none !important; } .card-body { padding:0 !important; } }
            </style>
        </head><body>
            <div class="page">${html}</div>
        </body></html>`);
            w.document.close(); w.focus();
            setTimeout(() => { w.print(); w.close(); }, 350);
        });

        // ======= GENERAR PDF (desde preview) =======
        async function generarPdfBlobDesdePreview() {
            recalcFromForm();
            const area = $('#printArea');
            if (!area) { throw new Error('No se encontró el área de impresión'); }

            const canvas = await html2canvas(area, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
            const imgData = canvas.toDataURL('image/jpeg', 0.95);

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({ unit: 'pt', format: 'letter' }); // 612x792pt
            const pageW = pdf.internal.pageSize.getWidth();
            const pageH = pdf.internal.pageSize.getHeight();
            const marginL = 85;  // ~30mm
            const marginT = 72;  // ~25mm
            const maxW = pageW - marginL * 2;
            const maxH = pageH - marginT * 2;

            const imgWpx = canvas.width;
            const imgHpx = canvas.height;
            const ratio = Math.min(maxW / imgWpx, maxH / imgHpx);
            const drawW = imgWpx * ratio;
            const drawH = imgHpx * ratio;
            const x = (pageW - drawW) / 2;
            const y = marginT;

            pdf.addImage(imgData, 'JPEG', x, y, drawW, drawH, undefined, 'FAST');
            return pdf.output('blob');
        }

        // ======= GUARDAR PDF LOCALMENTE =======
        async function subirPdf() {
            const folioRaw = ($('#f-folio')?.value || '').trim();
            if (!folioRaw) {
                await Swal.fire({ icon: 'warning', title: 'Falta folio', text: 'Captura un folio antes de guardar el PDF.' });
                return;
            }

            const { isConfirmed } = await Swal.fire({
                title: "¿Guardar PDF localmente?",
                text: "Se generará el PDF de la vista y se guardará en el servidor.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, guardar"
            });
            if (!isConfirmed) return;

            try {
                Swal.fire({
                    title: 'Generando PDF...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const folio = folioRaw.replaceAll('/', '-').replaceAll(' ', '_');
                const blob = await generarPdfBlobDesdePreview();
                const file = new File([blob], `${folio}.pdf`, { type: 'application/pdf' });

                const fd = new FormData();
                fd.append('file', file);
                fd.append('tipo', 'Documento Embarque');
                fd.append('folio', folio);
                fd.append('embarqueId', 0); // Sin embarque específico

                const url = "<?= site_url('modulo3/documentos/guardar-pdf-local') ?>";
                const r = await fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!r.ok) {
                    let msg = `HTTP ${r.status}`;
                    try { const err = await r.json(); if (err?.message) msg = err.message; } catch (_) { }
                    throw new Error(msg);
                }

                const json = await r.json();
                Swal.close();

                await Swal.fire({
                    title: "¡Guardado!",
                    html: `El PDF se guardó correctamente en el servidor.<br><small class="text-muted">${json.filename}</small>`,
                    icon: "success"
                });

                if (json.url) {
                    window.open(json.url, '_blank', 'noopener');
                }
            } catch (err) {
                console.error(err);
                Swal.close();
                Swal.fire({
                    title: "Error",
                    text: err?.message || "No se pudo guardar el PDF.",
                    icon: "error"
                });
            }
        }

        $('#btnGuardarPdf')?.addEventListener('click', subirPdf);

        // Inicial
        recalcFromForm();
    })();
</script>
<?= $this->endSection() ?>