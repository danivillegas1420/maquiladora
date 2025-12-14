<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        /* Separación entre los botones de DataTables */
        .dt-buttons.btn-group .btn{
            margin-left: 0 !important;
            margin-right: .5rem;
            border-radius: .375rem !important;
        }
        .dt-buttons.btn-group .btn:last-child{ margin-right: 0; }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-auto">Clientes</h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" id="btnVerClasificaciones" data-bs-toggle="modal" data-bs-target="#modalClasificaciones">
                <i class="bi bi-tags"></i> Clasificaciones
            </button>
            <button type="button" class="btn btn-success" id="btnAgregarCliente" data-bs-toggle="modal" data-bs-target="#modalCliente">
                <i class="bi bi-person-plus"></i> Agregar Cliente
            </button>
        </div>
    </div>
    <div class="modal fade" id="modalClienteDel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">¿Confirmas que deseas eliminar este cliente? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDel">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Listado de Clientes</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="tabla-clientes">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Tipo Persona</th>
                        <th>Clasificación</th>
                        <th style="width: 120px;">Fecha Registro</th>
                        <th style="width: 140px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="clientes-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalClienteVer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="v_nombre" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" name="v_email" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="v_telefono" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RFC</label>
                            <input type="text" class="form-control" name="v_rfc" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo Persona</label>
                            <input type="text" class="form-control" name="v_tipo_persona" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha registro</label>
                            <input type="text" class="form-control" name="v_fechaRegistro" readonly>
                        </div>
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-md-6">
                            <label class="form-label">Calle</label>
                            <input type="text" class="form-control" name="v_calle" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Num. Ext</label>
                            <input type="text" class="form-control" name="v_numExt" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Num. Int</label>
                            <input type="text" class="form-control" name="v_numInt" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="v_ciudad" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" name="v_estado" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">CP</label>
                            <input type="text" class="form-control" name="v_cp" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" name="v_pais" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Clasificaciones -->
    <div class="modal fade" id="modalClasificaciones" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title">Clasificaciones de Clientes</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" id="btnAgregarClasificacion">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaClasificaciones">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Maquiladora ID</th>
                                    <th style="width: 120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clasificaciones-body">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden"></span>
                                        </div>
                                    </td>
                                </tr>
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

    <!-- Modal para Agregar/Editar Clasificación -->
    <div class="modal fade" id="modalClasificacionForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClasificacionTitle">Agregar Clasificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formClasificacion">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="clasificacion_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="clasificacion_nombre" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="clasificacion_descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maquiladora ID</label>
                            <input type="number" class="form-control" name="maquiladoraID" id="clasificacion_maquiladoraID" readonly>
                            <small class="form-text text-muted">Se asigna automáticamente desde tu sesión</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            const langES = {
                "sProcessing":     "",
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
            const fileName = 'clientes_' + fecha;

            // Inicializar DataTable
            const tabla = $('#tabla-clientes').DataTable({
                language: langES,
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }],
                dom:
                    "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend:'copy',  text:'Copiar',  exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'csv',   text:'CSV',   filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'excel', text:'Excel', filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'pdf',   text:'PDF',   filename:fileName, title:fileName,
                        orientation:'portrait', pageSize:'A4',
                        exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'print', text:'Imprimir', exportOptions:{ columns: ':not(:last-child)' } }
                ]
            });

            // Cargar datos en la tabla
            function cargarClientes() {
                console.log('');
                const tbody = document.getElementById('clientes-body');
                const fmt = (v) => v == null ? '' : String(v);
                const toDate = (v) => {
                    if (!v) return '';
                    try { const d = new Date(v); return isNaN(d) ? fmt(v) : d.toISOString().slice(0,10); } catch(e){ return fmt(v); }
                };

                fetch('<?= base_url('modulo1/clientes/json') ?>' + '?_=' + Date.now(), { 
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => {
                    console.log('Respuesta del servidor:', response);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Datos recibidos:', data);
                    const items = Array.isArray(data) ? data : [];

                // Limpiar tabla
                tabla.clear().draw();

                if (items.length === 0) {
                    console.warn('No se encontraron clientes');
                    return;
                }

                // Agregar datos
                items.forEach(row => {
                        const id = row.id ?? row.ID ?? row.clienteId ?? '';
                        const nombre = row.nombre ?? row.name ?? '';
                        const email = row.email ?? row.correo ?? '';
                        const tel = row.telefono ?? row.tel ?? '';
                        const rfc = row.rfc ?? '';
                        const tipoPersona = row.tipo_persona ?? '';
                        const fecha = row.fechaRegistro ?? row.fecha ?? row.created_at ?? '';
                        // Obtener clasificación del objeto clasificacion
                        const clasificacion = row.clasificacion?.nombre ?? row.nombre_cla ?? '';

                            tabla.row.add([
                                fmt(nombre),
                                fmt(email),
                                fmt(tel),
                                fmt(tipoPersona === 'FISICA' ? 'Física' : (tipoPersona === 'MORAL' ? 'Moral' : tipoPersona)),
                                fmt(clasificacion),
                                toDate(fecha),
                                `
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-view" data-id="${fmt(id)}" aria-label="Ver">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${fmt(id)}" aria-label="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${fmt(id)}" aria-label="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        `
                            ]);
                        });

                        tabla.draw();
                        console.log('Tabla actualizada con', items.length, 'clientes');
                    })
                    .catch(error => {
                        console.error('Error al cargar clientes:', error);
                        tabla.clear().draw();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No fue posible cargar los clientes. ' + (error.message || '')
                        });
                    });
            }

            // Cargar datos iniciales
            cargarClientes();

            // Eliminar con confirmación (SweetAlert2)
            $(document).on('click', '.btn-del', async function(){
                const idSel = $(this).data('id');
                if (!idSel) return;
                const $btn = $(this);
                $btn.prop('disabled', true);
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'No podrás revertir esta acción',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(idSel) + '/eliminar', { method: 'POST', headers: { 'Accept': 'application/json' }});
                            if (res.ok) {
                                try { await res.json(); } catch(e) {}
                                await Swal.fire({ icon: 'success', title: '¡Eliminado!', text: 'El cliente ha sido eliminado.' });
                                cargarClientes();
                            } else {
                                let msg = 'No se pudo eliminar';
                                try { const js = await res.json(); if (js && js.error) msg = js.error; } catch(e) {}
                                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                            }
                        } catch(err) {
                            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo eliminar.' });
                        }
                    }
                    $btn.prop('disabled', false);
                });
            });

            let lastEditBtn = null;
            let addLocked = false;
            const btnAdd = $('#btnAgregarCliente');

            // Función para formatear fechas
            function toDate(v) {
                if (!v) return '';
                try {
                    const d = new Date(v);
                    return isNaN(d) ? '' : d.toISOString().split('T')[0];
                } catch(e) { 
                    console.error('Error al formatear fecha:', e, v);
                    return ''; 
                }
            }

            // Manejador del botón Ver
            $(document).on('click', '.btn-view', async function(){
                const id = $(this).data('id');
                if (!id) return;
                $(this).prop('disabled', true);
                
                try {
                    const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(id), { 
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    if (!res.ok) {
                        throw new Error(`Error HTTP: ${res.status}`);
                    }
                    
                    const data = await res.json();
                    console.log('Datos del cliente:', data);
                    
                    // Llenar datos básicos
                    $('#modalClienteVer [name="v_nombre"]').val(data.nombre || '');
                    $('#modalClienteVer [name="v_email"]').val(data.email || '');
                    $('#modalClienteVer [name="v_telefono"]').val(data.telefono || '');
                    $('#modalClienteVer [name="v_rfc"]').val(data.rfc || '');
                    $('#modalClienteVer [name="v_tipo_persona"]').val(
                        data.tipo_persona === 'FISICA' ? 'Física' : 
                        (data.tipo_persona === 'MORAL' ? 'Moral' : data.tipo_persona || '')
                    );
                    $('#modalClienteVer [name="v_fechaRegistro"]').val(toDate(data.fechaRegistro) || '');
                    
                    // Llenar dirección (usando direccion_detalle)
                    const direccion = data.direccion_detalle || {};
                    console.log('Datos de dirección:', direccion);
                    
                    $('#modalClienteVer [name="v_calle"]').val(direccion.calle || '');
                    $('#modalClienteVer [name="v_numExt"]').val(direccion.numExt || '');
                    $('#modalClienteVer [name="v_numInt"]').val(direccion.numInt || '');
                    $('#modalClienteVer [name="v_ciudad"]').val(direccion.ciudad || '');
                    $('#modalClienteVer [name="v_estado"]').val(direccion.estado || '');
                    $('#modalClienteVer [name="v_cp"]').val(direccion.cp || '');
                    $('#modalClienteVer [name="v_pais"]').val(direccion.pais || '');
                    
                    $('#modalClienteVer').modal('show');
                } catch (error) {
                    console.error('Error al cargar los datos del cliente:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los datos del cliente: ' + (error.message || '')
                    });
                } finally {
                    $(this).prop('disabled', false);
                }
            });

            // Manejador del botón Editar
            $(document).on('click', '.btn-edit', async function(){
                const id = $(this).data('id');
                if (!id) return;
                $(this).prop('disabled', true);
                lastEditBtn = $(this);

                try {
                    const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(id), { 
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    if (!res.ok) {
                        throw new Error(`Error HTTP: ${res.status}`);
                    }
                    
                    const data = await res.json();
                    console.log('Datos del cliente para editar:', data);
                    
                    const m = $('#modalCliente');
                    m.find('[name="id"]').val(data.id || '');
                    m.find('[name="nombre"]').val(data.nombre || '');
                    m.find('[name="email"]').val(data.email || '');
                    m.find('[name="telefono"]').val(data.telefono || '');
                    m.find('[name="rfc"]').val(data.rfc || '');
                    m.find('[name="tipo_persona"]').val(data.tipo_persona || '');
                    m.find('[name="fechaRegistro"]').val(toDate(data.fechaRegistro) || '');
                    
                    // Usar direccion_detalle en lugar de direccion
                    const direccion = data.direccion_detalle || {};
                    console.log('Datos de dirección para editar:', direccion);
                    
                    m.find('[name="calle"]').val(direccion.calle || '');
                    m.find('[name="numExt"]').val(direccion.numExt || '');
                    m.find('[name="numInt"]').val(direccion.numInt || '');
                    m.find('[name="ciudad"]').val(direccion.ciudad || '');
                    m.find('[name="estado"]').val(direccion.estado || '');
                    m.find('[name="cp"]').val(direccion.cp || '');
                    m.find('[name="pais"]').val(direccion.pais || '');
                    
                    m.find('.modal-title').text('Editar cliente');
                    
                    // Cargar clasificaciones ANTES de mostrar el modal, con el ID de clasificación
                    const clasificacionId = data.clasificacionId || (data.clasificacion && data.clasificacion.id ? data.clasificacion.id : '');
                    console.log('Clasificación ID a seleccionar:', clasificacionId);
                    await cargarClasificaciones(clasificacionId);
                    
                    m.modal('show');
                } catch (error) {
                    console.error('Error al cargar los datos del cliente:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los datos del cliente: ' + (error.message || '')
                    });
                } finally {
                    $(this).prop('disabled', false);
                }
            });

            $('#formCliente').on('submit', async function(e){
                e.preventDefault();
                const fd = new FormData(this);
                const id = fd.get('id');
                const btnSave = $(this).find('button[type="submit"]');
                
                // Get clasificacionId directly from the select element
                const clasificacionId = $('#selectClasificacion').val();
                if (!clasificacionId || clasificacionId === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campo requerido',
                        text: 'Por favor seleccione una clasificación',
                        confirmButtonText: 'Aceptar'
                    });
                    if (btnSave) btnSave.prop('disabled', false);
                    return;
                }
                
                // Make sure clasificacionId is included in form data as integer
                fd.set('clasificacionId', parseInt(clasificacionId, 10));
                
                if (btnSave) btnSave.prop('disabled', true);
                if (btnAdd) btnAdd.prop('disabled', true);
                
                try {
                    const url = id 
                        ? '<?= site_url('api/clientes') ?>/' + encodeURIComponent(id) + '/editar' 
                        : '<?= site_url('api/clientes/crear') ?>';
                        
                    console.log('Enviando datos a:', url);
                    console.log('Datos del formulario:', Object.fromEntries(fd));
                    
                    const res = await fetch(url, { 
                        method: 'POST', 
                        body: fd, 
                        headers: { 
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (res.ok) {
                        try { 
                            await res.json(); 
                        } catch(e) {
                            console.error('Error al procesar la respuesta:', e);
                        }
                        
                        $('#modalCliente').modal('hide');
                        
                        await Swal.fire({
                            icon: 'success',
                            title: id ? '¡Cambios guardados!' : '¡Cliente agregado!',
                            confirmButtonText: 'Aceptar',
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        
                        cargarClientes(); // Recargar datos
                        return;
                    }
                    
                    // Manejo de errores
                    let msg = 'No se pudo guardar el cliente';
                    let errors = [];
                    
                    try { 
                        const js = await res.json(); 
                        console.log('Respuesta de error:', js);
                        
                        if (js && js.errors) {
                            // Mostrar errores de validación si los hay
                            errors = Object.values(js.errors).flat();
                            if (errors.length > 0) {
                                msg = 'Por favor, corrige los siguientes errores:';
                            }
                        }
                        
                        if (js && js.error && errors.length === 0) {
                            msg = js.error;
                        }
                        
                        if (js && js.message) {
                            msg = js.message;
                        }
                    } catch(e) {
                        console.error('Error al procesar el error:', e);
                    }
                    
                    await Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        html: errors.length > 0 
                            ? `<p>${msg}</p><ul class="text-start mt-2">${errors.map(e => `<li>${e}</li>`).join('')}</ul>`
                            : `<p>${msg}</p>`,
                        confirmButtonText: 'Aceptar'
                    });
                    
                } catch(err) { 
                    console.error('Error en la solicitud:', err);
                    await Swal.fire({ 
                        icon: 'error', 
                        title: 'Error de red', 
                        text: 'No se pudo guardar. Intente nuevamente.',
                        confirmButtonText: 'Aceptar'
                    }); 
                } finally {
                    if (btnSave) btnSave.prop('disabled', false);
                    if (lastEditBtn) lastEditBtn.prop('disabled', false);
                    if (btnAdd) btnAdd.prop('disabled', false);
                }
            });

            $('#modalCliente').on('hidden.bs.modal', function(){
                if (lastEditBtn) { lastEditBtn.prop('disabled', false); lastEditBtn = null; }
                if (btnAdd) { btnAdd.prop('disabled', false); }
            });

            $('#btnAgregarCliente').on('click', function(){
                if (addLocked) return;
                addLocked = true;
                $(this).prop('disabled', true);
                const m = $('#modalCliente');
                m.find('[name="id"]').val('');
                m.find('[name="nombre"]').val('');
                m.find('[name="email"]').val('');
                m.find('[name="telefono"]').val('');
                m.find('[name="rfc"]').val('');
                m.find('[name="tipo_persona"]').val('');
                m.find('[name="fechaRegistro"]').val(new Date().toLocaleDateString('en-CA'));
                m.find('[name="calle"]').val('');
                m.find('[name="numExt"]').val('');
                m.find('[name="numInt"]').val('');
                m.find('[name="ciudad"]').val('');
                m.find('[name="estado"]').val('');
                m.find('[name="cp"]').val('');
                m.find('[name="pais"]').val('');
                m.find('.modal-title').text('Agregar cliente');
                
                // Limpiar el ID de clasificación guardado
                m.removeData('clasificacionId');
                
                // Cargar clasificaciones antes de mostrar el modal
                cargarClasificaciones().then(() => {
                    m.modal('show');
                    setTimeout(()=>{ addLocked = false; }, 300);
                }).catch(() => {
                    m.modal('show');
                    setTimeout(()=>{ addLocked = false; }, 300);
                });
            });

            // Función auxiliar para formatear fechas
            function toDate(v) {
                if (!v) return '';
                try {
                    const d = new Date(v);
                    return isNaN(d) ? String(v) : d.toISOString().slice(0,10);
                } catch(e){
                    return String(v);
                }
            }
            
            // Función para cargar las clasificaciones en el select
            async function cargarClasificaciones(selectedId = '') {
                try {
                    const response = await fetch('<?= site_url('api/clientes/clasificaciones') ?>', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Error al cargar las clasificaciones');
                    }
                    
                    const data = await response.json();
                    const select = $('#selectClasificacion');
                    
                    // Limpiar opciones actuales
                    select.empty();
                    select.append($('<option>', {
                        value: '',
                        text: '',
                        selected: !selectedId
                    }));
                    
                    // Agregar opciones
                    if (data.success && data.data) {
                        data.data.forEach(clasificacion => {
                            select.append($('<option>', {
                                value: clasificacion.id,
                                text: clasificacion.nombre,
                                selected: clasificacion.id == selectedId
                            }));
                        });
                    } else {
                        select.append($('<option>', {
                            value: '',
                            text: 'No hay clasificaciones disponibles'
                        }));
                    }
                    
                    return data;
                } catch (error) {
                    console.error('Error al cargar clasificaciones:', error);
                    const select = $('#selectClasificacion');
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Error al cargar clasificaciones'
                    }));
                    return { success: false, error: error.message };
                }
            }
            
            // Cargar clasificaciones cuando se muestre el modal (solo para agregar nuevo)
            $('#modalCliente').on('show.bs.modal', async function() {
                // Solo cargar si NO estamos editando (no hay ID de cliente)
                const clienteId = $('#formCliente [name="id"]').val();
                if (!clienteId || clienteId === '') {
                    // Es un nuevo cliente, cargar sin selección
                    await cargarClasificaciones();
                }
                // Si hay clienteId, significa que ya se cargaron las clasificaciones antes de abrir el modal
            });

            // Función para cargar todas las clasificaciones en el modal
            async function cargarTodasClasificaciones() {
                const tbody = $('#clasificaciones-body');
                tbody.html('<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden"></span></div></td></tr>');
                
                try {
                    const response = await fetch('<?= site_url('api/clientes/clasificaciones') ?>', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Error al cargar las clasificaciones');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success && data.data && data.data.length > 0) {
                        tbody.empty();
                        data.data.forEach(clasificacion => {
                            const row = $('<tr>');
                            row.append($('<td>').text(clasificacion.id || ''));
                            row.append($('<td>').text(clasificacion.nombre || ''));
                            row.append($('<td>').text(clasificacion.descripcion || ''));
                            row.append($('<td>').text(clasificacion.maquiladoraID || ''));
                            
                            // Botones de acción
                            const acciones = $('<td>');
                            const btnGroup = $('<div>').addClass('d-flex gap-2');
                            
                            const btnEdit = $('<button>')
                                .addClass('btn btn-sm btn-outline-primary btn-edit-clasificacion')
                                .attr('data-id', clasificacion.id)
                                .attr('aria-label', 'Editar')
                                .html('<i class="bi bi-pencil-square"></i>');
                            
                            const btnDel = $('<button>')
                                .addClass('btn btn-sm btn-outline-danger btn-del-clasificacion')
                                .attr('data-id', clasificacion.id)
                                .attr('aria-label', 'Eliminar')
                                .html('<i class="bi bi-trash"></i>');
                            
                            btnGroup.append(btnEdit).append(btnDel);
                            acciones.append(btnGroup);
                            row.append(acciones);
                            
                            tbody.append(row);
                        });
                    } else {
                        tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay clasificaciones disponibles</td></tr>');
                    }
                } catch (error) {
                    console.error('Error al cargar clasificaciones:', error);
                    tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar las clasificaciones: ' + (error.message || 'Error desconocido') + '</td></tr>');
                }
            }

            // Cargar clasificaciones cuando se muestre el modal de clasificaciones
            $('#modalClasificaciones').on('show.bs.modal', function() {
                cargarTodasClasificaciones();
            });

            // Obtener maquiladora_id de la sesión (pasado desde PHP)
            const maquiladoraId = <?= session()->get('maquiladora_id') ? (int)session()->get('maquiladora_id') : 'null' ?>;

            // Botón agregar clasificación
            $('#btnAgregarClasificacion').on('click', function() {
                $('#formClasificacion')[0].reset();
                $('#clasificacion_id').val('');
                // Llenar automáticamente el maquiladoraID desde la sesión
                if (maquiladoraId) {
                    $('#clasificacion_maquiladoraID').val(maquiladoraId);
                }
                // Hacer readonly al agregar (se asigna automáticamente)
                $('#clasificacion_maquiladoraID').prop('readonly', true);
                $('#modalClasificacionTitle').text('Agregar Clasificación');
                $('#modalClasificacionForm').modal('show');
            });

            // Botón editar clasificación
            $(document).on('click', '.btn-edit-clasificacion', async function() {
                const id = $(this).data('id');
                if (!id) return;
                
                try {
                    const response = await fetch('<?= site_url('api/clientes/clasificaciones') ?>', {
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    if (!response.ok) throw new Error('Error al cargar clasificaciones');
                    
                    const data = await response.json();
                    const clasificacion = data.data?.find(c => c.id == id);
                    
                    if (clasificacion) {
                        $('#clasificacion_id').val(clasificacion.id);
                        $('#clasificacion_nombre').val(clasificacion.nombre || '');
                        $('#clasificacion_descripcion').val(clasificacion.descripcion || '');
                        $('#clasificacion_maquiladoraID').val(clasificacion.maquiladoraID || '');
                        // Permitir editar el campo al editar
                        $('#clasificacion_maquiladoraID').prop('readonly', false);
                        $('#modalClasificacionTitle').text('Editar Clasificación');
                        $('#modalClasificacionForm').modal('show');
                    }
                } catch (error) {
                    console.error('Error al cargar clasificación:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la clasificación'
                    });
                }
            });

            // Botón eliminar clasificación
            $(document).on('click', '.btn-del-clasificacion', async function() {
                const id = $(this).data('id');
                if (!id) return;
                
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'No podrás revertir esta acción',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('<?= site_url('api/clientes/clasificaciones') ?>/' + encodeURIComponent(id) + '/eliminar', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                            }
                        });
                        
                        if (response.ok) {
                            await Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: 'La clasificación ha sido eliminada.'
                            });
                            cargarTodasClasificaciones();
                            // Recargar también el select de clasificaciones en el modal de cliente
                            if ($('#modalCliente').hasClass('show')) {
                                const selectedId = $('#selectClasificacion').val();
                                await cargarClasificaciones(selectedId);
                            }
                        } else {
                            const error = await response.json();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.message || 'No se pudo eliminar la clasificación'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de red',
                            text: 'No se pudo eliminar la clasificación'
                        });
                    }
                }
            });

            // Formulario de clasificación
            $('#formClasificacion').on('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = formData.get('id');
                const btnSubmit = $(this).find('button[type="submit"]');
                
                if (btnSubmit) btnSubmit.prop('disabled', true);
                
                try {
                    const url = id 
                        ? '<?= site_url('api/clientes/clasificaciones') ?>/' + encodeURIComponent(id) + '/editar'
                        : '<?= site_url('api/clientes/clasificaciones/crear') ?>';
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        await Swal.fire({
                            icon: 'success',
                            title: id ? '¡Actualizado!' : '¡Agregado!',
                            text: id ? 'La clasificación ha sido actualizada.' : 'La clasificación ha sido agregada.',
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        
                        $('#modalClasificacionForm').modal('hide');
                        cargarTodasClasificaciones();
                        
                        // Recargar también el select de clasificaciones en el modal de cliente
                        if ($('#modalCliente').hasClass('show')) {
                            const selectedId = $('#selectClasificacion').val();
                            await cargarClasificaciones(selectedId);
                        }
                    } else {
                        const error = await response.json();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'No se pudo guardar la clasificación'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo guardar la clasificación'
                    });
                } finally {
                    if (btnSubmit) btnSubmit.prop('disabled', false);
                }
            });
        });
    </script>

    <div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCliente">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">RFC</label>
                                <input type="text" class="form-control" name="rfc" maxlength="13" placeholder="RFC (13 caracteres)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo Persona</label>
                                <select class="form-control" name="tipo_persona">
                                    <option value="">Seleccionar...</option>
                                    <option value="FISICA">Física</option>
                                    <option value="MORAL">Moral</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha registro</label>
                                <input type="date" class="form-control" name="fechaRegistro" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Clasificación</label>
                                <select class="form-control" name="clasificacionId" id="selectClasificacion" required>
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-12"><hr class="my-2"></div>
                            <div class="col-md-6">
                                <label class="form-label">Calle</label>
                                <input type="text" class="form-control" name="calle">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Num. Ext</label>
                                <input type="text" class="form-control" name="numExt">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Num. Int</label>
                                <input type="text" class="form-control" name="numInt">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <input type="text" class="form-control" name="estado">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">CP</label>
                                <input type="text" class="form-control" name="cp">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">País</label>
                                <input type="text" class="form-control" name="pais">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>