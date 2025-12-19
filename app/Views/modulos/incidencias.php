<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .btn-outline-primary.btn-ghost {
        border-width: 2px;
        font-weight: 600;
    }

    .btn-outline-primary.btn-ghost:hover,
    .btn-outline-primary.btn-ghost:focus {
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
    }

    #vi-descripcion {
        white-space: pre-wrap;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Encabezado: título + botón Agregar (outline) -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3 mb-0">Incidencias</h1>
        <span class="badge bg-danger">Reportes</span>
    </div>
    <button type="button" class="btn btn-outline-primary btn-ghost" data-bs-toggle="modal"
        data-bs-target="#incidenciaModal">
        <i class="bi bi-plus-lg me-1"></i> Agregar
    </button>
</div>

<?php if (session()->getFlashdata('ok')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('ok')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong>Historial de Incidencias</strong>
        <!-- (sin botón Agregar aquí) -->
    </div>

    <div class="card-body table-responsive">
        <?php
        $rows = (isset($lista) && is_array($lista)) ? $lista : [];
        // QUITAMOS "OP" y "Empleado" de la tabla:
        $columns = ['Fecha', 'Tipo', 'Prioridad', 'Descripción', 'Acción', 'Acciones'];
        $tableId = 'tablaIncidencias';
        ?>
        <table id="<?= esc($tableId) ?>" class="table table-striped table-bordered align-middle">
            <thead class="table-primary text-center">
                <tr><?php foreach ($columns as $c): ?>
                        <th><?= esc($c) ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $i): ?>
                        <?php
                        $id = (int) ($i['Ide'] ?? 0);
                        $prio = strtolower($i['Prioridad'] ?? '');
                        $badge = $prio === 'alta' ? 'danger' : ($prio === 'media' ? 'warning' : ($prio === 'baja' ? 'success' : 'secondary'));
                        $detJson = json_encode($i, JSON_UNESCAPED_UNICODE);
                        ?>
                        <tr>
                            <td class="text-center"><?= esc($i['Fecha'] ?? '') ?></td>
                            <td class="text-center"><?= esc($i['Tipo'] ?? '') ?></td>
                            <td class="text-center"><span
                                    class="badge bg-<?= $badge ?>"><?= esc($i['Prioridad'] ?? '') ?></span></td>
                            <td class="text-start"><?= esc($i['Descripcion'] ?? '') ?></td>
                            <td class="text-start"><?= esc($i['Accion'] ?? '') ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <!-- VER -->
                                    <button type="button" class="btn btn-sm btn-outline-primary ver-inc" data-bs-toggle="modal"
                                        data-bs-target="#verIncModal" data-id="<?= $id ?>"
                                        data-det='<?= esc($detJson, "attr") ?>'>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <!-- EDITAR (usa el mismo modal de alta, precargado) -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-inc"
                                        data-bs-toggle="modal" data-bs-target="#incidenciaModal" data-id="<?= $id ?>"
                                        data-det='<?= esc($detJson, "attr") ?>'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <!-- ELIMINAR -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger eliminar-inc"
                                            data-id="<?= $id ?>"
                                            data-url="<?= site_url('modulo3/incidencias/eliminar/' . $id) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay incidencias registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Crear/Editar Incidencia (misma vista, se rellena si es edición) -->
<div class="modal fade" id="incidenciaModal" tabindex="-1" aria-labelledby="incidenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="inc-form" action="<?= site_url('modulo3/incidencias/crear') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="inc-id">
            <div class="modal-header">
                <h5 class="modal-title" id="incidenciaModalLabel">
                    <i class="bi bi-clipboard-plus me-2"></i><span id="inc-modal-title">Nuevo Reporte de
                        Incidencia</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">OP (Folio)</label>
                        <select id="inc-op" name="ordenProduccionFK" class="form-select">
                            <option value="">-- Selecciona --</option>
                            <?php foreach (($ops ?? []) as $op): ?>
                                <option value="<?= (int) $op['id'] ?>"><?= esc($op['folio']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
                            <option>Baja</option>
                            <option>Media</option>
                            <option>Alta</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Empleado responsable</label>
                        <select id="inc-empleado" name="empleadoFK" class="form-select">
                            <option value="">(Sin asignar)</option>
                            <?php foreach (($empleados ?? []) as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"><?= esc($e['nombre'] . ' ' . $e['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Acción/Seguimiento</label>
                        <input id="inc-accion" name="accion" class="form-control"
                            placeholder="Ej. Cambiar sensor, solicitar material, etc.">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea id="inc-descripcion" name="descripcion" class="form-control" rows="3"
                            placeholder="Describe la incidencia..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button id="inc-submit" class="btn btn-danger" type="submit">
                    <i class="bi bi-send me-1"></i><span id="inc-submit-text">Reportar</span>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Ver Incidencia (muestra campos extra, incluidos OP y Empleado) -->
<div class="modal fade" id="verIncModal" tabindex="-1" aria-labelledby="verIncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="verIncModalLabel"><i class="bi bi-eye me-2"></i>Detalle de la incidencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-3">
                    <dt class="col-sm-3">Fecha</dt>
                    <dd class="col-sm-9" id="vi-fecha">-</dd>
                    <dt class="col-sm-3">OP</dt>
                    <dd class="col-sm-9" id="vi-op">-</dd>
                    <dt class="col-sm-3">Tipo</dt>
                    <dd class="col-sm-9" id="vi-tipo">-</dd>
                    <dt class="col-sm-3">Prioridad</dt>
                    <dd class="col-sm-9" id="vi-prioridad">-</dd>
                    <dt class="col-sm-3">Empleado</dt>
                    <dd class="col-sm-9" id="vi-empleado">-</dd>
                    <dt class="col-sm-3">Acción</dt>
                    <dd class="col-sm-9" id="vi-accion">-</dd>
                </dl>
                <div class="mb-3">
                    <h6 class="mb-1">Descripción</h6>
                    <div id="vi-descripcion" class="border rounded p-2 bg-light">-</div>
                </div>
                <div id="vi-extra-wrap" class="mt-3 d-none">
                    <h6 class="mb-1">Más detalles</h6>
                    <dl id="vi-extras" class="row mb-0"></dl>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons (exportación) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Separación precisa de botones de exportación (sin btn-group) -->
<script>
    $.fn.dataTable.Buttons.defaults.dom.container.className =
        'dt-buttons d-inline-flex flex-wrap gap-2';
</script>

<script>
    (function () {
        const tableSel = '#<?= esc($tableId) ?>';

        const langES = {
            sProcessing: "Procesando...",
            sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados",
            sEmptyTable: "Sin datos",
            sInfo: "Mostrando _START_–_END_ de _TOTAL_",
            sInfoEmpty: "Mostrando 0–0 de 0",
            sInfoFiltered: "(filtrado de _MAX_)",
            sSearch: "Buscar:",
            oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
            buttons: { copy: "Copiar" }
        };

        const hoy = new Date().toISOString().slice(0, 10);

        const $table = $(tableSel);
        const headerCells = $table.find('thead th').length;
        const firstBodyRow = $table.find('tbody tr:first');
        const firstBodyCells = firstBodyRow.length ? firstBodyRow.find('td').length : 0;

        // Si no hay filas de datos reales (o solo la fila con colspan), no inicializar DataTables
        if (firstBodyCells !== 0 && firstBodyCells !== headerCells) {
            return;
        }

        $table.DataTable({
            language: langES,
            columnDefs: [{ targets: -1, orderable: false, searchable: false }], // Acciones
            order: [[0, 'desc']], // Fecha
            pageLength: 10,
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'copy', text: 'Copy', exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'csv', text: 'CSV', filename: 'incidencias_' + hoy, exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'excel', text: 'Excel', filename: 'incidencias_' + hoy, exportOptions: { columns: ':not(:last-child)' } },
                {
                    extend: 'pdf', text: 'PDF', filename: 'incidencias_' + hoy, title: 'Historial de Incidencias',
                    orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':not(:last-child)' }
                },
                { extend: 'print', text: 'Print', exportOptions: { columns: ':not(:last-child)' } }
            ]
        });

        // ===== Helpers
        const text = s => (s == null || s === '') ? '' : String(s);
        const getFrom = (obj, ...keys) => {
            for (const k of keys) {
                if (obj[k] != null && obj[k] !== '') return obj[k];
                const lc = k.toLowerCase(), uc = k.toUpperCase();
                if (obj[lc] != null && obj[lc] !== '') return obj[lc];
                if (obj[uc] != null && obj[uc] !== '') return obj[uc];
            }
            return '';
        };
        const toDateInput = (s) => {
            s = text(s);
            if (!s) return '';
            // intenta normalizar a YYYY-MM-DD
            const m = s.match(/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/);
            if (m) {
                const y = m[1], mo = ('0' + m[2]).slice(-2), d = ('0' + m[3]).slice(-2);
                return `${y}-${mo}-${d}`;
            }
            return s.slice(0, 10);
        };

        // ===== Modal "Ver"
        $(document).on('click', '.ver-inc', function () {
            let det = {};
            try { det = JSON.parse(this.getAttribute('data-det') || '{}'); } catch (e) { det = {}; }

            const prio = String(getFrom(det, 'Prioridad')).toLowerCase();
            const badge = prio === 'alta' ? 'danger' : (prio === 'media' ? 'warning' : (prio === 'baja' ? 'success' : 'secondary'));

            $('#vi-fecha').text(getFrom(det, 'Fecha') || '-');
            $('#vi-op').text(getFrom(det, 'OP', 'Folio', 'ordenProduccion', 'ordenProduccionFK') || '-');
            $('#vi-tipo').text(getFrom(det, 'Tipo') || '-');
            $('#vi-prioridad').html(`<span class="badge bg-${badge}">${getFrom(det, 'Prioridad') || '-'}</span>`);
            $('#vi-empleado').text(getFrom(det, 'Empleado', 'EmpleadoNombre', 'empleadoFK') || '-');
            $('#vi-accion').text(getFrom(det, 'Accion', 'Acción') || '-');
            $('#vi-descripcion').text(getFrom(det, 'Descripcion', 'Descripción') || '-');

            // Extras
            const base = new Set(['Fecha', 'Tipo', 'Prioridad', 'Descripcion', 'Descripción', 'Accion', 'Acción', 'Empleado', 'EmpleadoNombre', 'OP', 'Folio', 'ordenProduccion', 'ordenProduccionFK', 'empleadoFK', 'Ide', 'id']);
            const $wrap = $('#vi-extra-wrap'), $dl = $('#vi-extras').empty();
            const extras = [];
            Object.entries(det).forEach(([k, v]) => {
                if (v == null || v === '') return;
                if (base.has(k)) return;
                const lk = k.toLowerCase();
                if (['__proto__'].includes(lk)) return;
                const pretty = ({ area: 'Área', turno: 'Turno', causa: 'Causa', causaraiz: 'Causa raíz', notas: 'Notas', estatus: 'Estatus', estado: 'Estado', creado: 'Creado', actualizado: 'Actualizado', tiempo: 'Tiempo', duracion: 'Duración' }[lk]) || k;
                extras.push([pretty, String(v)]);
            });
            if (extras.length) {
                extras.forEach(([k, v]) => $dl.append(`<dt class="col-sm-3">${k}</dt><dd class="col-sm-9">${$('<div>').text(v).html()}</dd>`));
                $wrap.removeClass('d-none');
            } else {
                $wrap.addClass('d-none');
            }
        });

        // ===== Modal "Crear/Editar"
        const $incModal = $('#incidenciaModal');
        const $form = $('#inc-form');

        // Reset para modo CREAR si el trigger NO es .edit-inc
        $incModal.on('show.bs.modal', function (e) {
            const isEdit = e.relatedTarget && e.relatedTarget.classList.contains('edit-inc');
            if (!isEdit) {
                // crear
                $('#inc-modal-title').text('Nuevo Reporte de Incidencia');
                $('#inc-submit-text').text('Reportar');
                $form.attr('action', '<?= site_url('modulo3/incidencias/crear') ?>');
                $('#inc-id').val('');
                $('#inc-op').val('');
                $('#inc-tipo').val('');
                $('#inc-fecha').val('');
                $('#inc-prioridad').val('Baja');
                $('#inc-empleado').val('');
                $('#inc-accion').val('');
                $('#inc-descripcion').val('');
            } else {
                // editar
                let det = {};
                try { det = JSON.parse(e.relatedTarget.getAttribute('data-det') || '{}'); } catch (err) { det = {}; }

                $('#inc-modal-title').text('Editar Incidencia');
                $('#inc-submit-text').text('Guardar cambios');
                $form.attr('action', '<?= site_url('modulo3/incidencias/editar') ?>/' + (getFrom(det, 'Ide', 'id') || ''));
                $('#inc-id').val(getFrom(det, 'Ide', 'id') || '');

                // Intenta setear por ID; si no viene, deja vacío (user elige en el select)
                $('#inc-op').val(String(getFrom(det, 'ordenProduccionFK', 'opId', 'OP_ID') || ''));
                $('#inc-empleado').val(String(getFrom(det, 'empleadoFK', 'empleadoId', 'EMP_ID') || ''));

                $('#inc-tipo').val(getFrom(det, 'tipo', 'Tipo') || '');
                $('#inc-fecha').val(toDateInput(getFrom(det, 'fecha', 'Fecha')) || '');
                $('#inc-prioridad').val((getFrom(det, 'prioridad', 'Prioridad') || 'Baja').replace(/^\w/, c => c.toUpperCase()));
                $('#inc-accion').val(getFrom(det, 'accion', 'Accion', 'Acción') || '');
                $('#inc-descripcion').val(getFrom(det, 'descripcion', 'Descripcion', 'Descripción') || '');
            }
        });

        // ===== Eliminar con confirmación SweetAlert2
        $(document).on('click', '.eliminar-inc', function () {
            const url = this.getAttribute('data-url');
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) =\u003e {
                if(result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    }) ();
</script>
<?= $this->endSection() ?>