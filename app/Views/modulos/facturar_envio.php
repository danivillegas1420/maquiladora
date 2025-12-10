<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .card-result .kv {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: .25rem .75rem;
    }

    .card-result .kv div {
        padding: .15rem 0;
        border-bottom: 1px dashed #e9ecef;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="m-0">Facturación de envío</h1>
    <a href="<?= site_url('modulo3/documentos') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Datos del receptor</strong></div>
            <div class="card-body">
                <form id="frmFacturar">
                    <?= csrf_field() ?>
                    <input type="hidden" id="embarqueId" value="<?= (int) ($embarqueId ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">RFC *</label>
                        <input class="form-control" id="rfc" placeholder="XAXX010101000" value="XAXX010101000" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nombre / Razón social</label>
                        <input class="form-control" id="nombre" placeholder="Cliente Demo" value="Cliente Demo">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Uso CFDI</label>
                            <input class="form-control" id="usoCFDI" value="G03">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Régimen fiscal rec.</label>
                            <input class="form-control" id="regimenFiscalReceptor" value="601">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">CP</label>
                            <input class="form-control" id="domicilioFiscalReceptor" value="00000">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Forma de pago</label>
                            <input class="form-control" id="formaPago" value="03">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Método de pago</label>
                            <input class="form-control" id="metodoPago" value="PUE">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Moneda</label>
                            <input class="form-control" id="moneda" value="MXN">
                        </div>
                    </div>

                    <hr class="my-3">
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <strong>Conceptos</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddConcepto">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                    </div>
                    <div id="conceptos"></div>

                    <div class="d-grid mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-file-text"></i> Generar factura (demo)
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        Proveedor: <code><?= esc(getenv('facturacion.provider') ?: 'mock') ?></code>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm card-result d-none" id="cardResultado">
            <div class="card-header"><strong>Resultado</strong></div>
            <div class="card-body">
                <div class="kv" id="kv"></div>
                <div class="mt-3 d-flex gap-2">
                    <a id="btnPdf" class="btn btn-outline-secondary" href="#" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                    </a>
                    <a id="btnXml" class="btn btn-outline-secondary" href="#" target="_blank" rel="noopener">
                        <i class="bi bi-filetype-xml"></i> Descargar XML
                    </a>
                    <button id="btnGuardarPdfLocal" class="btn btn-success" style="display:none;">
                        <i class="bi bi-cloud-upload"></i> Guardar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    (function () {
        const conceptosDiv = document.getElementById('conceptos');
        const frm = document.getElementById('frmFacturar');

        // CSRF (para los POST que abrimos en nueva pestaña)
        const CSRF_NAME = '<?= esc(csrf_token()) ?>';
        const CSRF_HASH = '<?= esc(csrf_hash()) ?>';

        // Estado para reutilizar payload/resultado al descargar PDF
        let lastPayload = null;
        let lastResult = null;

        // ==== Helpers SweetAlert2 ====
        const confirmAsync = (title, text, icon = 'warning', confirmText = 'Sí, continuar') => {
            return Swal.fire({
                title, text, icon,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then(r => r.isConfirmed);
        };
        const toast = (text, icon = 'success') => {
            return Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: 1800, timerProgressBar: true, icon, title: text
            });
        };
        const loading = (title = 'Procesando...') => {
            Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        };

        // ==== Conceptos ====
        const addConcepto = (c = { descripcion: 'Servicio de envío', claveProdServ: '01010101', claveUnidad: 'E48', cantidad: 1, precioUnitario: 100 }) => {
            const wrap = document.createElement('div');
            wrap.className = 'border rounded p-2 mb-2';
            wrap.innerHTML = `
      <div class="row g-2 align-items-end">
        <div class="col-md-6">
          <label class="form-label">Descripción</label>
          <input class="form-control c-desc" value="${c.descripcion || ''}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Clave ProdServ</label>
          <input class="form-control c-cps" value="${c.claveProdServ || '01010101'}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Clave Unidad</label>
          <input class="form-control c-cu" value="${c.claveUnidad || 'E48'}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Cantidad</label>
          <input type="number" step="0.001" class="form-control c-cant" value="${c.cantidad || 1}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Precio Unit.</label>
          <input type="number" step="0.01" class="form-control c-pre" value="${c.precioUnitario || 0}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Importe</label>
          <input class="form-control c-imp" value="0" disabled>
        </div>
        <div class="col-md-3 d-grid">
          <button type="button" class="btn btn-outline-danger btnRemove"><i class="bi bi-trash"></i></button>
        </div>
      </div>`;

            conceptosDiv.appendChild(wrap);

            // Recalcular importe
            const recalc = () => {
                const cant = parseFloat(wrap.querySelector('.c-cant').value || 0);
                const pre = parseFloat(wrap.querySelector('.c-pre').value || 0);
                wrap.querySelector('.c-imp').value = (cant * pre).toFixed(2);
            };
            wrap.querySelectorAll('.c-cant,.c-pre').forEach(i => i.addEventListener('input', recalc));
            recalc();

            // Confirmar borrado del concepto
            wrap.querySelector('.btnRemove').addEventListener('click', async () => {
                if (await confirmAsync('¿Eliminar concepto?', 'Esta acción no se puede deshacer.')) {
                    wrap.remove();
                    toast('Concepto eliminado');
                }
            });
        };

        // Botón agregar concepto
        document.getElementById('btnAddConcepto').addEventListener('click', () => {
            addConcepto();
            toast('Concepto agregado');
        });

        // Uno por defecto
        addConcepto();

        // ==== Payload ====
        const buildPayload = () => {
            return {
                rfc: document.getElementById('rfc').value.trim(),
                nombre: document.getElementById('nombre').value.trim(),
                usoCFDI: document.getElementById('usoCFDI').value.trim() || 'G03',
                regimenFiscalReceptor: document.getElementById('regimenFiscalReceptor').value.trim() || '601',
                domicilioFiscalReceptor: document.getElementById('domicilioFiscalReceptor').value.trim() || '00000',
                formaPago: document.getElementById('formaPago').value.trim() || '03',
                metodoPago: document.getElementById('metodoPago').value.trim() || 'PUE',
                moneda: document.getElementById('moneda').value.trim() || 'MXN',
                conceptos: Array.from(conceptosDiv.children).map(w => ({
                    descripcion: w.querySelector('.c-desc').value.trim(),
                    claveProdServ: w.querySelector('.c-cps').value.trim() || '01010101',
                    claveUnidad: w.querySelector('.c-cu').value.trim() || 'E48',
                    cantidad: parseFloat(w.querySelector('.c-cant').value || 0),
                    precioUnitario: parseFloat(w.querySelector('.c-pre').value || 0),
                }))
            };
        };

        // ==== Submit (timbrar demo) con SweetAlert2 ====
        frm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validación mínima de conceptos
            if (conceptosDiv.children.length === 0) {
                Swal.fire('Sin conceptos', 'Agrega al menos un concepto antes de timbrar.', 'info');
                return;
            }

            // Confirmación previa
            const ok = await confirmAsync('¿Generar factura (demo)?', 'Se timbrará usando el proveedor configurado.');
            if (!ok) return;

            const id = document.getElementById('embarqueId').value;
            const payload = buildPayload();

            try {
                loading('Timbrando CFDI...');
                const r = await fetch(`<?= site_url('logistica/embarque') ?>/${id}/facturar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const d = await r.json().catch(() => null);
                if (!r.ok || !d || !d.ok) {
                    const msg = (d && d.msg) ? d.msg : 'No se pudo timbrar (demo).';
                    Swal.fire('Error', msg, 'error');
                    return;
                }

                // Guarda estado para "Descargar PDF"
                lastPayload = payload;
                lastResult = d;

                // Pinta resultado
                const kv = document.getElementById('kv');
                kv.innerHTML = '';
                const row = (k, v) => { const a = document.createElement('div'); a.textContent = k; const b = document.createElement('div'); b.textContent = (v ?? '—'); kv.append(a, b); };
                row('UUID', d.uuid);
                row('Serie', d.serie || '—');
                row('Folio', d.folio || '—');
                row('Total', (d.total != null) ? d.total : '—');
                row('Fecha', d.fecha || '—');
                row('Proveedor', d.provider || 'mock');

                // Enlaces PDF/XML
                const btnPdf = document.getElementById('btnPdf');
                const btnXml = document.getElementById('btnXml');

                if (d.pdf_url) { btnPdf.href = d.pdf_url; btnPdf.onclick = null; }
                else {
                    btnPdf.href = "#";
                    btnPdf.onclick = (ev) => {
                        ev.preventDefault();
                        postToNewTab('<?= site_url('facturacion/demo/pdf') ?>', {
                            [CSRF_NAME]: CSRF_HASH,
                            payload: JSON.stringify(lastPayload),
                            result: JSON.stringify(lastResult)
                        });
                    };
                }

                if (d.xml_url) { btnXml.href = d.xml_url; btnXml.onclick = null; }
                else {
                    btnXml.href = "#";
                    btnXml.onclick = (ev) => {
                        ev.preventDefault();
                        Swal.fire('XML no disponible', 'Demo: el XML no está disponible en el mock.', 'info');
                    };
                }

                document.getElementById('cardResultado').classList.remove('d-none');

                // Mostrar botón de guardar PDF
                document.getElementById('btnGuardarPdfLocal').style.display = 'inline-block';

                Swal.fire({
                    title: '¡Timbrado exitoso!',
                    text: 'Se generó la factura de demostración.',
                    icon: 'success',
                    confirmButtonText: 'Ver resultado'
                }).then(() => {
                    document.getElementById('cardResultado').scrollIntoView({ behavior: 'smooth', block: 'start' });
                });

            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error inesperado.', 'error');
            }
        });

        // Utilidad: abre POST en nueva pestaña (para generar PDF con Dompdf)
        function postToNewTab(action, dataObj) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = action;
            f.target = '_blank';
            Object.entries(dataObj || {}).forEach(([k, v]) => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = k;
                inp.value = typeof v === 'string' ? v : JSON.stringify(v);
                f.appendChild(inp);
            });
            document.body.appendChild(f);
            f.submit();
            setTimeout(() => f.remove(), 5000);
        }

        // ==== Guardar PDF Localmente ====
        async function guardarPdfLocal() {
            if (!lastResult || !lastResult.pdf_url) {
                await Swal.fire('Error', 'Primero genera la factura', 'warning');
                return;
            }

            const ok = await confirmAsync(
                '¿Guardar PDF localmente?',
                'El PDF se guardará en el servidor y se registrará en documentos de embarque.'
            );
            if (!ok) return;

            try {
                loading('Guardando PDF...');

                // Descargar el PDF desde la URL
                const pdfResponse = await fetch(lastResult.pdf_url);
                const pdfBlob = await pdfResponse.blob();

                // Crear FormData
                const fd = new FormData();
                const folio = `${lastResult.serie}-${lastResult.folio}`;
                fd.append('file', pdfBlob, `${folio}.pdf`);
                fd.append('tipo', 'Factura');
                fd.append('folio', folio);
                fd.append('embarqueId', document.getElementById('embarqueId').value);

                // Enviar al servidor
                const r = await fetch('<?= site_url('modulo3/documentos/guardar-pdf-local') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });

                const data = await r.json();

                if (!r.ok || !data.ok) {
                    throw new Error(data.message || 'Error al guardar');
                }

                Swal.fire({
                    title: '¡Guardado!',
                    html: `El PDF se guardó correctamente en el servidor.<br><small class="text-muted">${data.filename}</small>`,
                    icon: 'success'
                });

            } catch (err) {
                console.error(err);
                Swal.fire('Error', err.message || 'No se pudo guardar el PDF', 'error');
            }
        }

        // Event listener para el botón
        document.getElementById('btnGuardarPdfLocal')?.addEventListener('click', guardarPdfLocal);

    })();
</script>
<?= $this->endSection() ?>