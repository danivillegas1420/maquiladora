<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .table td,
    .table th {
        padding: .85rem .8rem;
    }

    .table.tbl-head thead th {
        background: #e6f0fb;
        color: #0b1720;
        font-weight: 700;
        vertical-align: middle;
        border-color: #cfddec;
        position: relative;
    }

    .table.tbl-head thead th:not(:last-child) {
        box-shadow: inset -1px 0 0 #cfddec;
    }

    .table.tbl-head tbody td {
        background: #f9fbfe;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3 mb-0">Desperdicios & Reprocesos</h1>
        <span class="badge bg-danger">Calidad</span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDesecho">
            <i class="bi bi-plus-lg me-1"></i>Agregar RD
        </button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalReproceso">
            <i class="bi bi-plus-lg me-1"></i>Agregar UP
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">

    <!-- ====== DESECHOS ====== -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Registro de desecho</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaDesechos"
                        class="table table-striped table-bordered align-middle text-center mb-0 tbl-head">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>OP</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($desp)):
                                foreach ($desp as $x): ?>
                                    <tr data-row-id="<?= (int) $x['id'] ?>">
                                        <td><?= esc($x['fecha']) ?></td>
                                        <td><?= esc($x['op']) ?></td>
                                        <td><?= esc($x['cantidad']) ?></td>
                                        <td><?= esc($x['observaciones']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info ver-desecho"
                                                    data-id="<?= (int) $x['id'] ?>" data-bs-toggle="modal"
                                                    data-bs-target="#modalVistaDesecho" title="Ver"><i
                                                        class="bi bi-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-primary edit-desecho"
                                                    data-id="<?= (int) $x['id'] ?>" data-fecha="<?= esc($x['fecha']) ?>"
                                                    data-op="<?= esc($x['op']) ?>" data-cantidad="<?= esc($x['cantidad']) ?>"
                                                    data-motivo="<?= esc($x['observaciones']) ?>" data-bs-toggle="modal"
                                                    data-bs-target="#modalDesecho" title="Editar"><i
                                                        class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger del-desecho"
                                                    data-id="<?= (int) $x['id'] ?>" title="Eliminar"><i
                                                        class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-muted">Sin datos</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== REPROCESOS ====== -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Unidades en reproceso</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaReprocesos"
                        class="table table-striped table-bordered align-middle text-center mb-0 tbl-head">
                        <thead>
                            <tr>
                                <th>OP</th>
                                <th>Tarea</th>
                                <th>Pend.</th>
                                <th>ETA</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rep)):
                                foreach ($rep as $r): ?>
                                    <tr data-row-id="<?= (int) $r['id'] ?>">
                                        <td><?= esc($r['op']) ?></td>
                                        <td><?= esc($r['tarea']) ?></td>
                                        <td><?= (int) $r['pendientes'] ?></td>
                                        <td><?= esc($r['eta']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info ver-rep"
                                                    data-id="<?= (int) $r['id'] ?>" data-bs-toggle="modal"
                                                    data-bs-target="#modalVistaReproceso" title="Ver"><i
                                                        class="bi bi-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-primary edit-rep"
                                                    data-id="<?= (int) $r['id'] ?>" data-op="<?= esc($r['op']) ?>"
                                                    data-tarea="<?= esc($r['tarea']) ?>"
                                                    data-pendientes="<?= (int) $r['pendientes'] ?>"
                                                    data-eta="<?= esc($r['eta']) ?>" data-bs-toggle="modal"
                                                    data-bs-target="#modalReproceso" title="Editar"><i
                                                        class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger del-rep"
                                                    data-id="<?= (int) $r['id'] ?>" title="Eliminar"><i
                                                        class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-muted">Sin datos</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===================== MODALES ===================== -->
<!-- Agregar/Editar Desecho -->
<div class="modal fade" id="modalDesecho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Desecho</h5>
                <div class="ms-auto"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formDesecho" action="<?= site_url('calidad/desperdicios/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="d-fecha" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Orden de Producción</label>
                            <select class="form-select" name="op" id="d-op" required>
                                <option value="">Seleccione una OP...</option>
                                <?php if (!empty($ordenesProduccion)):
                                    foreach ($ordenesProduccion as $op): ?>
                                        <option value="<?= esc($op['folio']) ?>">
                                            <?= esc($op['folio']) ?>
                                            <?= !empty($op['cliente_nombre']) ? ' - ' . esc($op['cliente_nombre']) : '' ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="0.01" class="form-control" name="cantidad" id="d-cantidad"
                                required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo (observaciones)</label>
                            <input class="form-control" name="motivo" id="d-motivo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vista Desecho -->
<div class="modal fade" id="modalVistaDesecho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del desecho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Fecha</dt>
                    <dd class="col-sm-9" id="vd-fecha">-</dd>
                    <dt class="col-sm-3">OP</dt>
                    <dd class="col-sm-9" id="vd-op">-</dd>
                    <dt class="col-sm-3">Cantidad</dt>
                    <dd class="col-sm-9" id="vd-cantidad">-</dd>
                    <dt class="col-sm-3">Motivo</dt>
                    <dd class="col-sm-9" id="vd-motivo">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Agregar/Editar Reproceso -->
<div class="modal fade" id="modalReproceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Reproceso</h5>
                <div class="ms-auto"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formReproceso" action="<?= site_url('calidad/reprocesos/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Orden de Producción</label>
                            <select class="form-select" name="op" id="r-op" required>
                                <option value="">Seleccione...</option>
                                <?php if (!empty($ordenesProduccion)):
                                    foreach ($ordenesProduccion as $op): ?>
                                        <option value="<?= esc($op['folio']) ?>">
                                            <?= esc($op['folio']) ?>        <?= !empty($op['cliente_nombre']) ? ' - ' . esc($op['cliente_nombre']) : '' ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tarea</label>
                            <input class="form-control" name="tarea" id="r-tarea" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pendientes</label>
                            <input type="number" min="0" class="form-control" name="pendientes" id="r-pendientes"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ETA</label>
                            <input type="date" class="form-control" name="eta" id="r-eta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="r-estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="terminado">Terminado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vista Reproceso -->
<div class="modal fade" id="modalVistaReproceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del reproceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">OP</dt>
                    <dd class="col-sm-9" id="vr-op">-</dd>
                    <dt class="col-sm-3">Tarea</dt>
                    <dd class="col-sm-9" id="vr-tarea">-</dd>
                    <dt class="col-sm-3">Pendientes</dt>
                    <dd class="col-sm-9" id="vr-pendientes">-</dd>
                    <dt class="col-sm-3">ETA</dt>
                    <dd class="col-sm-9" id="vr-eta">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $.fn.dataTable.Buttons.defaults.dom.container.className =
        'dt-buttons d-inline-flex flex-wrap gap-2';
</script>

<script>
    $(function () {
        const langES = {
            sProcessing: "Procesando...", sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados", sEmptyTable: "Sin datos",
            sInfo: "Mostrando _START_–_END_ de _TOTAL_", sInfoEmpty: "Mostrando 0–0 de 0",
            sInfoFiltered: "(filtrado de _MAX_)", sSearch: "Buscar:",
            oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
            buttons: { copy: "Copiar" }
        };
        const hoy = new Date().toISOString().slice(0, 10);

        const tieneDatosDesechos = $('#tablaDesechos tbody tr').filter(function () {
            return !$(this).find('td[colspan]').length;
        }).length > 0;

        if (tieneDatosDesechos) {
            $('#tablaDesechos').DataTable({
                language: langES,
                columnDefs: [{ orderable: false, searchable: false, targets: [4] }],
                dom: "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'copy', text: 'Copy', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'csv', text: 'CSV', filename: 'calidad_desechos_' + hoy, exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'excel', text: 'Excel', filename: 'calidad_desechos_' + hoy, exportOptions: { columns: ':not(:last-child)' } },
                    {
                        extend: 'pdf', text: 'PDF', filename: 'calidad_desechos_' + hoy, title: 'Registro de desecho',
                        orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':not(:last-child)' }
                    },
                    { extend: 'print', text: 'Print', exportOptions: { columns: ':not(:last-child)' } }
                ]
            });
        }

        const tieneDatosReprocesos = $('#tablaReprocesos tbody tr').filter(function () {
            return !$(this).find('td[colspan]').length;
        }).length > 0;

        if (tieneDatosReprocesos) {
            $('#tablaReprocesos').DataTable({
                language: langES,
                columnDefs: [{ orderable: false, searchable: false, targets: [4] }],
                dom: "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'copy', text: 'Copy', exportOptions: { columns: [0, 1, 2, 3] } },
                    { extend: 'csv', text: 'CSV', filename: 'calidad_reprocesos_' + hoy, exportOptions: { columns: [0, 1, 2, 3] } },
                    { extend: 'excel', text: 'Excel', filename: 'calidad_reprocesos_' + hoy, exportOptions: { columns: [0, 1, 2, 3] } },
                    {
                        extend: 'pdf', text: 'PDF', filename: 'calidad_reprocesos_' + hoy, title: 'Unidades en reproceso',
                        orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0, 1, 2, 3] }
                    },
                    { extend: 'print', text: 'Print', exportOptions: { columns: [0, 1, 2, 3] } }
                ]
            });
        }

        // Ver: Desecho
        $(document).on('click', '.ver-desecho', async function () {
            const id = this.dataset.id;
            const r = await (await fetch('<?= site_url('calidad/desperdicios') ?>/' + id)).json();
            vd_fecha.textContent = r.fecha || '-';
            vd_op.textContent = r.op || '-';
            vd_cantidad.textContent = r.cantidad || '-';
            vd_motivo.textContent = r.observaciones || '-';
        });

        // Ver: Reproceso
        $(document).on('click', '.ver-rep', async function () {
            const id = this.dataset.id;
            const r = await (await fetch('<?= site_url('calidad/reprocesos') ?>/' + id)).json();
            vr_op.textContent = r.op || '-';
            vr_tarea.textContent = r.accion || r.tarea || '-';
            vr_pendientes.textContent = (r.cantidad ?? r.pendientes) || '-';
            vr_eta.textContent = (r.fecha ?? r.eta) || '-';
        });

        // Editar: Desecho
        $(document).on('click', '.edit-desecho', function () {
            const f = formDesecho;
            f.action = '<?= site_url('calidad/desperdicios') ?>/' + this.dataset.id + '/editar';
            d_fecha.value = this.dataset.fecha || '';
            d_op.value = this.dataset.op || '';
            d_cantidad.value = this.dataset.cantidad || '';
            d_motivo.value = this.dataset.motivo || '';
        });
        modalDesecho.addEventListener('show.bs.modal', e => {
            if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-desecho')) {
                formDesecho.action = '<?= site_url('calidad/desperdicios/guardar') ?>';
                document.getElementById('d-fecha').value = '';
                document.getElementById('d-op').value = '';
                document.getElementById('d-cantidad').value = '';
                document.getElementById('d-motivo').value = '';
            }
        });

        // Editar: Reproceso
        $(document).on('click', '.edit-rep', function () {
            const f = formReproceso;
            f.action = '<?= site_url('calidad/reprocesos') ?>/' + this.dataset.id + '/editar';
            r_op.value = this.dataset.op || '';
            r_tarea.value = this.dataset.tarea || '';
            r_pendientes.value = this.dataset.pendientes || '';
            r_eta.value = this.dataset.eta || '';
            r_estado.value = 'pendiente';
        });
        modalReproceso.addEventListener('show.bs.modal', e => {
            if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-rep')) {
                formReproceso.action = '<?= site_url('calidad/reprocesos/guardar') ?>';
                document.getElementById('r-op').value = '';
                document.getElementById('r-tarea').value = '';
                document.getElementById('r-pendientes').value = '';
                document.getElementById('r-eta').value = '';
                r_estado.value = 'pendiente';
            }
        });

        // ===== Eliminar con SweetAlert2 =====
        const csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        async function confirmarYEliminar({ url, row }) {
            const result = await Swal.fire({
                title: "¿Eliminar registro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            });
            if (!result.isConfirmed) return;

            const fd = new FormData(); fd.append(csrfName, csrfHash);

            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            let json = {}; try { json = await resp.json(); } catch (_) { }
            if (json.csrf) csrfHash = json.csrf;

            if (resp.ok && (json.ok ?? true)) {
                row?.remove();
                await Swal.fire({ title: "Eliminado", text: json.message ?? "El registro fue eliminado correctamente.", icon: "success", confirmButtonColor: "#0d6efd" });
            } else {
                await Swal.fire({ title: "Error", text: json.message ?? "No se pudo eliminar el registro.", icon: "error", confirmButtonColor: "#0d6efd" });
            }
        }

        $(document).on('click', '.del-desecho', function () {
            const id = this.dataset.id;
            const row = this.closest('tr');
            const url = '<?= site_url('calidad/desperdicios') ?>/' + id + '/eliminar';
            confirmarYEliminar({ url, row });
        });
        $(document).on('click', '.del-rep', function () {
            const id = this.dataset.id;
            const row = this.closest('tr');
            const url = '<?= site_url('calidad/reprocesos') ?>/' + id + '/eliminar';
            confirmarYEliminar({ url, row });
        });

        // ===== Enviar formularios con Swal (AJAX) + FIX cierre modal =====
        async function enviarFormularioConSwal(form) {
            const btn = form.querySelector('[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = 'Guardando...';

            const fd = new FormData(form);

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });

                let json = null; try { json = await resp.json(); } catch (e) { }
                if (json && json.csrf) {
                    document.querySelectorAll('input[name="<?= csrf_token() ?>"]').forEach(i => i.value = json.csrf);
                    csrfHash = json.csrf;
                }

                // Cerrar modales abiertos ANTES de Swal (evita warning aria-hidden)
                document.querySelectorAll('.modal.show').forEach(el => {
                    const inst = bootstrap.Modal.getInstance(el);
                    if (inst) inst.hide();
                });

                if (resp.ok && (!json || json.ok !== false)) {
                    await Swal.fire({ icon: 'success', title: (json?.message) || 'Guardado', text: 'La información se registró correctamente.', confirmButtonColor: '#0d6efd' });
                    location.reload();
                } else {
                    await Swal.fire({ icon: 'error', title: 'Error', text: (json?.message) || 'No se pudo guardar. Revisa los datos.', confirmButtonColor: '#0d6efd' });
                }
            } catch (err) {
                // Cerrar modales para evitar overlay bloqueado
                document.querySelectorAll('.modal.show').forEach(el => {
                    const inst = bootstrap.Modal.getInstance(el);
                    if (inst) inst.hide();
                });
                await Swal.fire({ icon: 'error', title: 'Error de red', text: 'Ocurrió un problema al enviar la petición.', confirmButtonColor: '#0d6efd' });
            } finally {
                btn.disabled = false; btn.innerHTML = originalText;
            }
        }

        const formDesecho = document.getElementById('formDesecho');
        const formReproceso = document.getElementById('formReproceso');
        if (formDesecho) formDesecho.addEventListener('submit', e => { e.preventDefault(); enviarFormularioConSwal(e.target); });
        if (formReproceso) formReproceso.addEventListener('submit', e => { e.preventDefault(); enviarFormularioConSwal(e.target); });

        // Flashdata -> Swal
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Éxito', text: '<?= esc(session()->getFlashdata('success')) ?>', confirmButtonColor: '#0d6efd' });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Error', text: '<?= esc(session()->getFlashdata('error')) ?>', confirmButtonColor: '#0d6efd' });
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>