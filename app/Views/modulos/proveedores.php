<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .table-actions .btn {
        margin-right: .15rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Proveedores</h1>

    <button type="button" class="btn btn-primary btn-sm js-nuevo-proveedor">
        <i class="bi bi-plus-circle me-1"></i> Nuevo proveedor
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaProveedores" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre / Empresa</th>
                        <th>RFC</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Objetos que maneja</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($proveedores)): ?>
                        <?php foreach ($proveedores as $p): ?>
                            <tr>
                                <td><?= esc($p['codigo'] ?? '') ?></td>
                                <td><?= esc($p['nombre'] ?? '') ?></td>
                                <td><?= esc($p['rfc'] ?? '') ?></td>
                                <td><?= esc($p['email'] ?? '') ?></td>
                                <td><?= esc($p['telefono'] ?? '') ?></td>
                                <td><?= esc($p['direccion'] ?? '') ?></td>
                                <td><?= esc($p['objetos'] ?? '—') ?></td>
                                <td class="text-center table-actions">
                                    <!-- Historial de órdenes -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-historial-proveedor"
                                        title="Historial de órdenes" data-id="<?= (int) $p['id_proveedor'] ?>"
                                        data-nombre="<?= esc($p['nombre'] ?? '', 'attr') ?>">
                                        <i class="bi bi-clock-history"></i>
                                    </button>

                                    <!-- Crear orden de pedido -->
                                    <button type="button" class="btn btn-sm btn-outline-success js-orden-proveedor"
                                        title="Crear orden de pedido" data-id="<?= (int) $p['id_proveedor'] ?>"
                                        data-nombre="<?= esc($p['nombre'] ?? '', 'attr') ?>">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>

                                    <!-- Editar proveedor -->
                                    <button type="button" class="btn btn-sm btn-outline-primary js-editar-proveedor"
                                        title="Editar proveedor" data-id="<?= (int) $p['id_proveedor'] ?>"
                                        data-codigo="<?= esc($p['codigo'] ?? '', 'attr') ?>"
                                        data-nombre="<?= esc($p['nombre'] ?? '', 'attr') ?>"
                                        data-rfc="<?= esc($p['rfc'] ?? '', 'attr') ?>"
                                        data-email="<?= esc($p['email'] ?? '', 'attr') ?>"
                                        data-telefono="<?= esc($p['telefono'] ?? '', 'attr') ?>"
                                        data-direccion="<?= esc($p['direccion'] ?? '', 'attr') ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Eliminar proveedor -->
                                    <form action="<?= site_url('proveedores/eliminar/' . (int) $p['id_proveedor']) ?>"
                                        method="post" class="d-inline frm-eliminar-proveedor">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-eliminar-proveedor"
                                            title="Eliminar proveedor"
                                            data-nombre="<?= esc($p['nombre'] ?? 'este proveedor', 'attr') ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Alta / Edición de Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('proveedores/store') ?>" id="formProveedor">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="prov_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalProveedorLabel">Nuevo proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="prov_codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="prov_codigo" name="codigo">
                        </div>
                        <div class="col-md-8">
                            <label for="prov_nombre" class="form-label">Nombre / Empresa *</label>
                            <input type="text" class="form-control" id="prov_nombre" name="nombre" required>
                        </div>

                        <div class="col-md-4">
                            <label for="prov_rfc" class="form-label">RFC</label>
                            <input type="text" class="form-control" id="prov_rfc" name="rfc">
                        </div>
                        <div class="col-md-4">
                            <label for="prov_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="prov_email" name="email">
                        </div>
                        <div class="col-md-4">
                            <label for="prov_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="prov_telefono" name="telefono">
                        </div>

                        <div class="col-12">
                            <label for="prov_direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="prov_direccion" name="direccion" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarProveedor">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Orden de Pedido a Proveedor -->
<div class="modal fade" id="modalOrdenProveedor" tabindex="-1" aria-labelledby="modalOrdenProveedorLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('proveedores/orden') ?>" id="formOrdenProveedor">
                <?= csrf_field() ?>
                <input type="hidden" name="proveedor_id" id="ord_proveedor_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalOrdenProveedorLabel">Nueva orden de pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Proveedor</label>
                            <input type="text" class="form-control" id="ord_proveedor_nombre" readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="ord_fecha" class="form-label">Fecha de pedido</label>
                            <input type="date" class="form-control" id="ord_fecha" name="fecha">
                        </div>

                        <div class="col-md-4">
                            <label for="ord_prioridad" class="form-label">Prioridad</label>
                            <select class="form-select" id="ord_prioridad" name="prioridad">
                                <option value="Normal">Normal</option>
                                <option value="Alta">Alta</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="ord_descripcion" class="form-label">Materiales / Detalle del pedido</label>
                            <textarea class="form-control" id="ord_descripcion" name="descripcion" rows="3"
                                placeholder="Ej: Tela algodón azul 200m, hilo blanco 50 rollos, etc."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarOrdenProveedor">
                        Guardar orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historial de Órdenes -->
<div class="modal fade" id="modalHistorialProveedor" tabindex="-1" aria-labelledby="modalHistorialProveedorLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialProveedorLabel">Historial de órdenes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="tablaHistorialProveedor">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Prioridad</th>
                                <th>Estatus</th>
                                <th>Descripción</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llena por JS -->
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0" id="historialVacio" style="display:none;">
                    No hay órdenes registradas para este proveedor.
                </p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // DataTable
        if (window.jQuery && $.fn.DataTable) {
            $('#tablaProveedores').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[1, 'asc']]
            });
        }

        // URLs base
        const baseHistorialUrl = "<?= site_url('proveedores/historial') ?>";
        const baseOrdenVerUrl = "<?= site_url('proveedores/orden') ?>";
        const baseOrdenCompletarUrl = "<?= site_url('proveedores/orden/completar') ?>";
        const baseOrdenEliminarUrl = "<?= site_url('proveedores/orden/eliminar') ?>";

        const modalProveedorEl = document.getElementById('modalProveedor');
        const modalProveedor = modalProveedorEl ? new bootstrap.Modal(modalProveedorEl) : null;

        const modalOrdenEl = document.getElementById('modalOrdenProveedor');
        const modalOrden = modalOrdenEl ? new bootstrap.Modal(modalOrdenEl) : null;

        const modalHistorialEl = document.getElementById('modalHistorialProveedor');
        const modalHistorial = modalHistorialEl ? new bootstrap.Modal(modalHistorialEl) : null;

        const formProveedor = document.getElementById('formProveedor');
        const inputId = document.getElementById('prov_id');
        const tituloProv = document.getElementById('modalProveedorLabel');
        const btnGuardarProv = document.getElementById('btnGuardarProveedor');

        const formOrden = document.getElementById('formOrdenProveedor');
        const inputProvId = document.getElementById('ord_proveedor_id');
        const inputProvNombre = document.getElementById('ord_proveedor_nombre');
        const inputFechaOrden = document.getElementById('ord_fecha');
        const selectPrioridad = document.getElementById('ord_prioridad');
        const txtDescripcion = document.getElementById('ord_descripcion');
        const tituloOrden = document.getElementById('modalOrdenProveedorLabel');

        const tablaHistorialTbody = document.querySelector('#tablaHistorialProveedor tbody');
        const lblHistorial = document.getElementById('modalHistorialProveedorLabel');
        const lblHistorialVacio = document.getElementById('historialVacio');

        // Abrir modal Orden
        function abrirModalOrden(idProveedor, nombreProveedor, prioridad, descripcion) {
            if (!formOrden || !modalOrden) return;

            formOrden.reset();
            inputProvId.value = idProveedor;
            inputProvNombre.value = nombreProveedor || '';

            // Fecha hoy por defecto
            try {
                const hoy = new Date();
                const yyyy = hoy.getFullYear();
                const mm = String(hoy.getMonth() + 1).padStart(2, '0');
                const dd = String(hoy.getDate()).padStart(2, '0');
                inputFechaOrden.value = `${yyyy}-${mm}-${dd}`;
            } catch (e) { }

            selectPrioridad.value = prioridad || 'Normal';
            txtDescripcion.value = descripcion || '';

            tituloOrden.textContent = (descripcion && descripcion.trim() !== '')
                ? 'Reutilizar orden de pedido para ' + (nombreProveedor || 'proveedor')
                : 'Nueva orden de pedido para ' + (nombreProveedor || 'proveedor');

            modalOrden.show();
        }

        // Nuevo proveedor
        document.querySelectorAll('.js-nuevo-proveedor').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!formProveedor || !modalProveedor) return;

                formProveedor.reset();
                inputId.value = '';
                tituloProv.textContent = 'Nuevo proveedor';
                btnGuardarProv.textContent = 'Guardar';

                modalProveedor.show();
            });
        });

        // Editar proveedor
        document.querySelectorAll('.js-editar-proveedor').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!formProveedor || !modalProveedor) return;

                const id = btn.getAttribute('data-id');
                const codigo = btn.getAttribute('data-codigo') || '';
                const nombre = btn.getAttribute('data-nombre') || '';
                const rfc = btn.getAttribute('data-rfc') || '';
                const email = btn.getAttribute('data-email') || '';
                const telefono = btn.getAttribute('data-telefono') || '';
                const direccion = btn.getAttribute('data-direccion') || '';

                inputId.value = id;
                document.getElementById('prov_codigo').value = codigo;
                document.getElementById('prov_nombre').value = nombre;
                document.getElementById('prov_rfc').value = rfc;
                document.getElementById('prov_email').value = email;
                document.getElementById('prov_telefono').value = telefono;
                document.getElementById('prov_direccion').value = direccion;

                tituloProv.textContent = 'Editar proveedor';
                btnGuardarProv.textContent = 'Actualizar';

                modalProveedor.show();
            });
        });

        // Crear orden (nuevo)
        document.querySelectorAll('.js-orden-proveedor').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const nombre = btn.getAttribute('data-nombre') || '';
                abrirModalOrden(id, nombre, 'Normal', '');
            });
        });

        // Historial de órdenes
        document.querySelectorAll('.js-historial-proveedor').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!modalHistorial || !tablaHistorialTbody) return;

                const id = btn.getAttribute('data-id');
                const nombre = btn.getAttribute('data-nombre') || '';

                lblHistorial.textContent = 'Historial de órdenes - ' + (nombre || 'Proveedor');
                tablaHistorialTbody.innerHTML =
                    '<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>';
                lblHistorialVacio.style.display = 'none';

                fetch(baseHistorialUrl + '/' + id)
                    .then(resp => resp.json())
                    .then(data => {
                        tablaHistorialTbody.innerHTML = '';

                        if (!data || !data.length) {
                            lblHistorialVacio.style.display = 'block';
                            return;
                        }

                        lblHistorialVacio.style.display = 'none';

                        data.forEach(oc => {
                            const tr = document.createElement('tr');
                            const tdFecha = document.createElement('td');
                            const tdPrio = document.createElement('td');
                            const tdEst = document.createElement('td');
                            const tdDesc = document.createElement('td');
                            const tdAcciones = document.createElement('td');

                            tdFecha.textContent = oc.fecha || '';
                            tdPrio.textContent = oc.prioridad || '';
                            tdEst.textContent = oc.estatus || '';
                            tdDesc.textContent = oc.descripcion || '';
                            tdAcciones.classList.add('text-center');

                            // Ver orden (HTML simple)
                            const btnVer = document.createElement('a');
                            btnVer.href = baseOrdenVerUrl + '/' + oc.id_proveedorOC;
                            btnVer.target = '_blank';
                            btnVer.rel = 'noopener';
                            btnVer.className = 'btn btn-sm btn-outline-primary me-1';
                            btnVer.title = 'Ver orden';
                            btnVer.innerHTML = '<i class="bi bi-eye"></i>';

                            // Marcar como cumplida
                            const btnCompletar = document.createElement('button');
                            btnCompletar.type = 'button';
                            btnCompletar.className = 'btn btn-sm btn-outline-success me-1';
                            btnCompletar.title = 'Marcar como cumplida';
                            btnCompletar.innerHTML = '<i class="bi bi-check-circle"></i>';

                            // Eliminar orden
                            const btnEliminar = document.createElement('button');
                            btnEliminar.type = 'button';
                            btnEliminar.className = 'btn btn-sm btn-outline-danger';
                            btnEliminar.title = 'Eliminar orden';
                            btnEliminar.innerHTML = '<i class="bi bi-trash"></i>';

                            // Eventos de completar
                            btnCompletar.addEventListener('click', () => {
                                const url = baseOrdenCompletarUrl + '/' + oc.id_proveedorOC;

                                if (!window.Swal) {
                                    if (confirm('¿Marcar esta orden como cumplida?')) {
                                        window.location.href = url;
                                    }
                                    return;
                                }

                                Swal.fire({
                                    title: "¿Marcar como cumplida?",
                                    text: "La orden pasará al estatus 'Cumplida'.",
                                    icon: "question",
                                    showCancelButton: true,
                                    confirmButtonColor: "#198754",
                                    cancelButtonColor: "#6c757d",
                                    confirmButtonText: "Sí, marcar"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = url;
                                    }
                                });
                            });

                            // Eventos de eliminar
                            btnEliminar.addEventListener('click', () => {
                                const url = baseOrdenEliminarUrl + '/' + oc.id_proveedorOC;

                                if (!window.Swal) {
                                    if (confirm('¿Eliminar definitivamente esta orden?')) {
                                        window.location.href = url;
                                    }
                                    return;
                                }

                                Swal.fire({
                                    title: "¿Eliminar orden?",
                                    text: "Esta acción no se puede deshacer.",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#dc3545",
                                    cancelButtonColor: "#6c757d",
                                    confirmButtonText: "Sí, eliminar"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = url;
                                    }
                                });
                            });

                            tdAcciones.appendChild(btnVer);
                            tdAcciones.appendChild(btnCompletar);
                            tdAcciones.appendChild(btnEliminar);

                            tr.appendChild(tdFecha);
                            tr.appendChild(tdPrio);
                            tr.appendChild(tdEst);
                            tr.appendChild(tdDesc);
                            tr.appendChild(tdAcciones);

                            tablaHistorialTbody.appendChild(tr);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        tablaHistorialTbody.innerHTML =
                            '<tr><td colspan="5" class="text-center text-danger">Error al cargar el historial.</td></tr>';
                    });

                modalHistorial.show();
            });
        });

        // Confirmación al crear orden
        if (formOrden) {
            let enviandoOrden = false;

            formOrden.addEventListener('submit', function (e) {
                if (enviandoOrden) return;
                e.preventDefault();

                if (!window.Swal) {
                    enviandoOrden = true;
                    formOrden.submit();
                    return;
                }

                Swal.fire({
                    title: "¿Estás seguro?",
                    text: "Se registrará la orden de pedido.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, guardar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviandoOrden = true;
                        Swal.fire({
                            title: "Guardando...",
                            text: "La orden se está registrando.",
                            icon: "info",
                            timer: 1000,
                            showConfirmButton: false
                        });
                        formOrden.submit();
                    }
                });
            });
        }

        // Eliminar proveedor
        document.querySelectorAll('.js-eliminar-proveedor').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                const nombre = this.getAttribute('data-nombre') || 'este proveedor';

                if (!window.Swal) {
                    if (confirm('¿Eliminar ' + nombre + '?')) {
                        form.submit();
                    }
                    return;
                }

                Swal.fire({
                    title: "¿Eliminar?",
                    text: "Se eliminará \"" + nombre + "\".",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, eliminar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>