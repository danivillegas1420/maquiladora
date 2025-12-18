<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        <h1 class="me-3 mb-0">Planificación de requerimientos de materiales</h1>
        <span class="badge bg-primary">MRP</span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reqEditModal">
            <i class="bi bi-plus-lg me-1"></i> Agregar CA
        </button>
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#ocEditModal">
            <i class="bi bi-plus-lg me-1"></i> Agregar OC
        </button>
    </div>
</div>

<div class="row g-3">

    <!-- ===== Requerimientos ===== -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Cálculo automático de necesidades</strong>
            </div>
            <div class="card-body">
                <?php
                if (!isset($reqs) || !is_array($reqs) || !count($reqs)) {
                    $reqs = [
                        ['id' => 1, 'mat' => 'Tela Algodón 180g', 'u' => 'm', 'necesidad' => 1200, 'stock' => 450, 'comprar' => 750],
                        ['id' => 2, 'mat' => 'Hilo 40/2', 'u' => 'rollo', 'necesidad' => 35, 'stock' => 10, 'comprar' => 25],
                        ['id' => 3, 'mat' => 'Etiqueta talla', 'u' => 'pz', 'necesidad' => 1000, 'stock' => 1200, 'comprar' => 0],
                    ];
                }
                ?>
                <div class="table-responsive">
                    <table id="tablaReqs"
                        class="table table-striped table-bordered align-middle text-center mb-0 tbl-head">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>U.</th>
                                <th>Necesidad</th>
                                <th>Stock</th>
                                <th>A comprar</th>
                                <th>Generar OC</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reqs as $r): ?>
                                <tr>
                                    <td><?= esc($r['mat']) ?></td>
                                    <td><?= esc($r['u']) ?></td>
                                    <td><?= esc($r['necesidad']) ?></td>
                                    <td><?= esc($r['stock']) ?></td>
                                    <td class="<?= $r['comprar'] > 0 ? 'text-danger fw-bold' : '' ?>">
                                        <?= esc($r['comprar']) ?>
                                    </td>
                                    <td>
                                        <?php if ($r['comprar'] > 0): ?>
                                            <form method="post"
                                                action="<?= base_url('modulo3/mrp/requerimientos/' . $r['id'] . '/generar-oc') ?>"
                                                style="display: inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-cart-plus"></i> Generar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info ver-req" data-bs-toggle="modal"
                                                data-bs-target="#reqViewModal" data-id="<?= (int) $r['id'] ?>"
                                                data-mat="<?= esc($r['mat']) ?>" data-u="<?= esc($r['u']) ?>"
                                                data-necesidad="<?= esc($r['necesidad']) ?>"
                                                data-stock="<?= esc($r['stock']) ?>"
                                                data-comprar="<?= esc($r['comprar']) ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary edit-req" data-bs-toggle="modal"
                                                data-bs-target="#reqEditModal" data-id="<?= (int) $r['id'] ?>"
                                                data-mat="<?= esc($r['mat']) ?>" data-u="<?= esc($r['u']) ?>"
                                                data-necesidad="<?= esc($r['necesidad']) ?>"
                                                data-stock="<?= esc($r['stock']) ?>"
                                                data-comprar="<?= esc($r['comprar']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-req"
                                                data-id="<?= (int) $r['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== OCs sugeridas ===== -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Órdenes de Compra sugeridas</strong>
            </div>
            <div class="card-body">
                <?php
                if (!isset($ocs) || !is_array($ocs) || !count($ocs)) {
                    $ocs = [
                        ['id' => 101, 'prov' => 'Textiles MX', 'mat' => 'Tela Algodón 180g', 'cant' => 750, 'u' => 'm', 'eta' => '2025-10-02'],
                        ['id' => 102, 'prov' => 'Hilos del Norte', 'mat' => 'Hilo 40/2', 'cant' => 25, 'u' => 'rollo', 'eta' => '2025-09-30'],
                    ];
                }
                ?>
                <div class="table-responsive">
                    <table id="tablaOCs"
                        class="table table-striped table-bordered align-middle text-center mb-0 tbl-head">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Material</th>
                                <th>Cantidad</th>
                                <th>ETA</th>
                                <th>PDF</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ocs as $o): ?>
                                <tr>
                                    <td><?= esc($o['prov']) ?></td>
                                    <td><?= esc($o['mat']) ?></td>
                                    <td><?= esc($o['cant']) ?>     <?= esc($o['u']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($o['eta'])) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($o['pdf_path'])): ?>
                                            <a href="<?= base_url('modulo3/mrp/oc/' . $o['id'] . '/pdf') ?>"
                                                class="btn btn-sm btn-danger" target="_blank" title="Descargar PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info ver-oc" data-bs-toggle="modal"
                                                data-bs-target="#ocViewModal" data-id="<?= (int) $o['id'] ?>"
                                                data-prov="<?= esc($o['prov']) ?>" data-mat="<?= esc($o['mat']) ?>"
                                                data-cant="<?= esc($o['cant']) ?>" data-u="<?= esc($o['u']) ?>"
                                                data-eta="<?= esc($o['eta']) ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary edit-oc" data-bs-toggle="modal"
                                                data-bs-target="#ocEditModal" data-id="<?= (int) $o['id'] ?>"
                                                data-prov="<?= esc($o['prov']) ?>" data-mat="<?= esc($o['mat']) ?>"
                                                data-cant="<?= esc($o['cant']) ?>" data-u="<?= esc($o['u']) ?>"
                                                data-eta="<?= esc($o['eta']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-oc"
                                                data-id="<?= (int) $o['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- ===== Modales ===== -->

<!-- Ver Requerimiento -->
<div class="modal fade" id="reqViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del requerimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Material</dt>
                    <dd class="col-sm-9" id="rv-mat">-</dd>
                    <dt class="col-sm-3">Unidad</dt>
                    <dd class="col-sm-9" id="rv-u">-</dd>
                    <dt class="col-sm-3">Necesidad</dt>
                    <dd class="col-sm-9" id="rv-nec">-</dd>
                    <dt class="col-sm-3">Stock</dt>
                    <dd class="col-sm-9" id="rv-stk">-</dd>
                    <dt class="col-sm-3">A comprar</dt>
                    <dd class="col-sm-9" id="rv-comp">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Agregar/Editar Requerimiento -->
<div class="modal fade" id="reqEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Requerimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formReq" action="<?= base_url('modulo3/mrp/requerimientos/crear') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="req-id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Material *</label>
                            <input class="form-control" name="mat" id="req-mat" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unidad *</label>
                            <input class="form-control" name="u" id="req-u" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Necesidad *</label>
                            <input type="number" step="0.01" class="form-control" name="necesidad" id="req-nec"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock *</label>
                            <input type="number" step="0.01" class="form-control" name="stock" id="req-stk" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">A comprar *</label>
                            <input type="number" step="0.01" class="form-control" name="comprar" id="req-comp" required>
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

<!-- Ver OC -->
<div class="modal fade" id="ocViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de OC sugerida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Proveedor</dt>
                    <dd class="col-sm-9" id="ov-prov">-</dd>
                    <dt class="col-sm-3">Material</dt>
                    <dd class="col-sm-9" id="ov-mat">-</dd>
                    <dt class="col-sm-3">Cantidad</dt>
                    <dd class="col-sm-9" id="ov-cant">-</dd>
                    <dt class="col-sm-3">ETA</dt>
                    <dd class="col-sm-9" id="ov-eta">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Agregar/Editar OC -->
<div class="modal fade" id="ocEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">OC sugerida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formOC" action="<?= base_url('modulo3/mrp/oc/crear') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="oc-id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Proveedor</label>
                            <input class="form-control" name="prov" id="oc-prov" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Material</label>
                            <input class="form-control" name="mat" id="oc-mat" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="0.01" class="form-control" name="cant" id="oc-cant" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unidad</label>
                            <input class="form-control" name="u" id="oc-u" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ETA</label>
                            <input type="date" class="form-control" name="eta" id="oc-eta" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnGenTodas">Generar todas</button>
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- ===== Separación precisa de botones (global) ===== -->
<script>
    // Hace que el contenedor de Buttons no use 'btn-group' y tenga gap entre botones.
    $.fn.dataTable.Buttons.defaults.dom.container.className =
        'dt-buttons d-inline-flex flex-wrap gap-2';
</script>

<script>
    $(function () {
        const langES = {
            sProcessing: "Procesando...",
            sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados",
            sEmptyTable: "Ningún dato disponible en esta tabla",
            sInfo: "Mostrando registros del _START_ al _END_ de _TOTAL_",
            sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
            sSearch: "Buscar:",
            sLoadingRecords: "Cargando...",
            oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
            buttons: { copy: "Copiar" }
        };

        const hoy = new Date().toISOString().slice(0, 10);

        // ===== Requerimientos =====
        $('#tablaReqs').DataTable({
            language: langES,
            columnDefs: [
                { targets: -1, orderable: false, searchable: false } // Acciones
            ],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'copy', text: 'Copy', exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'csv', text: 'CSV', filename: 'mrp_reqs_' + hoy, exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'excel', text: 'Excel', filename: 'mrp_reqs_' + hoy, exportOptions: { columns: ':not(:last-child)' } },
                {
                    extend: 'pdf', text: 'PDF', filename: 'mrp_reqs_' + hoy, title: 'Requerimientos',
                    orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':not(:last-child)' }
                },
                { extend: 'print', text: 'Print', exportOptions: { columns: ':not(:last-child)' } }
            ]
        });

        // ===== OCs sugeridas =====
        $('#tablaOCs').DataTable({
            language: langES,
            columnDefs: [
                { targets: [-1, -2], orderable: false, searchable: false } // Acciones y Generar
            ],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                // Exportar SOLO las primeras 4 columnas (Proveedor, Material, Cantidad, ETA)
                { extend: 'copy', text: 'Copy', exportOptions: { columns: [0, 1, 2, 3] } },
                { extend: 'csv', text: 'CSV', filename: 'mrp_ocs_' + hoy, exportOptions: { columns: [0, 1, 2, 3] } },
                { extend: 'excel', text: 'Excel', filename: 'mrp_ocs_' + hoy, exportOptions: { columns: [0, 1, 2, 3] } },
                {
                    extend: 'pdf', text: 'PDF', filename: 'mrp_ocs_' + hoy, title: 'Órdenes de compra sugeridas',
                    orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0, 1, 2, 3] }
                },
                { extend: 'print', text: 'Print', exportOptions: { columns: [0, 1, 2, 3] } }
            ]
        });

        // ----- Lógica de modales (demo) -----
        $('#btnCalcular').on('click', () => Swal.fire({icon:'info', title:'Demo', text:'Calcular (demo)'}));
        $('#btnImportBOM').on('click', () => Swal.fire({icon:'info', title:'Demo', text:'Importar BOM (demo)'}));
        $('#btnGenTodas').on('click', () => Swal.fire({icon:'info', title:'Demo', text:'Generar todas (demo)'}));
        $(document).on('click', '.gen-oc', function () { Swal.fire({icon:'info', title:'Demo', text:'Generar OC #' + this.dataset.id + ' (demo)'}); });

        // ----- Requerimientos -----
        $(document).on('click', '.ver-req', function () {
            const g = a => this.getAttribute(a) || '-';
            $('#rv-mat').text(g('data-mat'));
            $('#rv-u').text(g('data-u'));
            $('#rv-nec').text(g('data-necesidad'));
            $('#rv-stk').text(g('data-stock'));
            $('#rv-comp').text(g('data-comprar'));
        });

        $(document).on('click', '.edit-req', function () {
            const id = this.dataset.id || '';
            $('#formReq').attr('action', id ? '<?= base_url('modulo3/mrp/requerimientos/') ?>' + id + '/editar' : '<?= base_url('modulo3/mrp/requerimientos/crear') ?>');
            $('#req-id').val(id);
            $('#req-mat').val(this.dataset.mat || '');
            $('#req-u').val(this.dataset.u || '');
            $('#req-nec').val(this.dataset.necesidad || '');
            $('#req-stk').val(this.dataset.stock || '');
            $('#req-comp').val(this.dataset.comprar || '');
        });

        document.getElementById('reqEditModal').addEventListener('show.bs.modal', e => {
            if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-req')) {
                $('#formReq').attr('action', '<?= base_url('modulo3/mrp/requerimientos/crear') ?>');
                ['req-id', 'req-oc', 'req-mat', 'req-u', 'req-nec', 'req-stk', 'req-comp'].forEach(id => $('#' + id).val(''));
                $('#req-bom').val('BOM-TSHIRT-001');
            }
        });

        // Delete requerimiento
        $(document).on('click', '.delete-req', function () {
            const id = this.dataset.id;
            Swal.fire({
                title: '¿Eliminar requerimiento?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        method: 'POST',
                        action: '<?= base_url('modulo3/mrp/requerimientos/') ?>' + id + '/eliminar'
                    }).append('<?= csrf_field() ?>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        // ----- OCs -----
        $(document).on('click', '.ver-oc', function () {
            const g = a => this.getAttribute(a) || '-';
            $('#ov-prov').text(g('data-prov'));
            $('#ov-mat').text(g('data-mat'));
            $('#ov-cant').text(`${g('data-cant')} ${g('data-u')}`);
            $('#ov-eta').text(g('data-eta'));
        });

        $(document).on('click', '.edit-oc', function () {
            const id = this.dataset.id || '';
            $('#formOC').attr('action', id ? '<?= base_url('modulo3/mrp/oc/') ?>' + id + '/editar' : '<?= base_url('modulo3/mrp/oc/crear') ?>');
            $('#oc-id').val(id);
            $('#oc-prov').val(this.dataset.prov || '');
            $('#oc-mat').val(this.dataset.mat || '');
            $('#oc-cant').val(this.dataset.cant || '');
            $('#oc-u').val(this.dataset.u || '');
            $('#oc-eta').val(this.dataset.eta || '');
        });

        document.getElementById('ocEditModal').addEventListener('show.bs.modal', e => {
            if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-oc')) {
                $('#formOC').attr('action', '<?= base_url('modulo3/mrp/oc/crear') ?>');
                ['oc-id', 'oc-prov', 'oc-mat', 'oc-cant', 'oc-u', 'oc-eta'].forEach(id => $('#' + id).val(''));
            }
        });

        // Delete OC
        $(document).on('click', '.delete-oc', function () {
            const id = this.dataset.id;
            Swal.fire({
                title: '¿Eliminar OC?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        method: 'POST',
                        action: '<?= base_url('modulo3/mrp/oc/') ?>' + id + '/eliminar'
                    }).append('<?= csrf_field() ?>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        // Show success/error messages
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '<?= session()->getFlashdata('success') ?>',
                timer: 2000
            });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= session()->getFlashdata('error') ?>'
            });
        <?php endif; ?>
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?= $this->endSection() ?>
```