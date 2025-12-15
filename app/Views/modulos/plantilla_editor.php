<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .editor-container {
        font-size: 0.9rem;
    }

    .table-editor input {
        border: none;
        background: transparent;
        width: 100%;
        padding: 2px 5px;
    }

    .table-editor input:focus {
        background: #fff;
        outline: 2px solid #86b7fe;
    }

    .table-editor td {
        padding: 0 !important;
        vertical-align: middle;
    }

    .table-editor td.readonly {
        padding: 4px 8px !important;
        font-weight: 500;
        color: #495057;
    }

    .header-input {
        font-weight: bold;
        border: none;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0;
        background: transparent;
    }

    .input-group-text {
        background: transparent;
        border: none;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0;
    }

    .summary-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid editor-container mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Hoja de Costos y Balanceo</h4>
            <small class="text-muted">Editor de Plantilla de Operaciones</small>
        </div>
        <div>
            <a href="<?= base_url('modulo3/reportes/costos') ?>" class="btn btn-secondary me-2">Cancelar</a>
            <button class="btn btn-primary" id="btnGuardar">
                <i class="fas fa-save me-1"></i> Guardar Plantilla
            </button>
        </div>
    </div>

    <form id="formPlantilla">
        <input type="hidden" name="id" value="<?= $plantilla['id'] ?? '' ?>">

        <!-- Header Info -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="small text-muted">Nombre Plantilla / Estilo</label>
                        <input type="text" class="form-control header-input" name="nombre_plantilla"
                            value="<?= $plantilla['nombre_plantilla'] ?? '' ?>" required
                            placeholder="Ej. POLO FRACCIONADA">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Tipo Prenda</label>
                        <input type="text" class="form-control header-input" name="tipo_prenda"
                            value="<?= $plantilla['tipo_prenda'] ?? '' ?>" required placeholder="Ej. POLO">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Cliente (Ref)</label>
                        <input type="text" class="form-control header-input" id="clienteRef" placeholder="Opcional">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Cantidad (Simulación)</label>
                        <input type="number" class="form-control header-input" id="simCantidad" value="1000">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Precio Autorizado</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control header-input" id="precioAutorizado" step="0.01"
                                value="0.00">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Grid -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-editor mb-0" id="gridOperaciones">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 40px;">No.</th>
                                <th>Descripción Operación</th>
                                <th style="width: 80px;">Tiempo (Seg)</th>
                                <th style="width: 80px;">Cuota Diaria</th>
                                <th style="width: 80px;">Cuota Bihoraria</th>
                                <th style="width: 80px;">Precio ($)</th>
                                <th style="width: 120px;">Sección</th>
                                <th style="width: 120px;">Depto</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyOperaciones">
                            <!-- Rows loaded via JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="p-2">
                                    <button type="button" class="btn btn-sm btn-outline-success" id="btnAddRow">
                                        <i class="fas fa-plus"></i> Agregar Operación
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Footer -->
        <div class="row">
            <div class="col-md-6">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <h6 class="card-title">Resumen de Costos</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Total Segundos:</td>
                                <td class="text-end fw-bold" id="totalSegundos">0</td>
                            </tr>
                            <tr>
                                <td>Total Minutos:</td>
                                <td class="text-end fw-bold" id="totalMinutos">0.00</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr class="my-1">
                                </td>
                            </tr>
                            <tr>
                                <td>Costo Total Mano de Obra:</td>
                                <td class="text-end fw-bold text-primary" id="totalCosto">$0.00</td>
                            </tr>
                            <tr>
                                <td>Utilidad Estimada:</td>
                                <td class="text-end fw-bold text-success" id="utilidad">$0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <h6 class="card-title">Parámetros de Cálculo</h6>
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-8"><small>Segundos por Jornada:</small></div>
                            <div class="col-4">
                                <input type="number" class="form-control form-control-sm text-end" id="segundosJornada"
                                    value="34200"> <!-- 9.5 horas * 3600 -->
                            </div>
                        </div>
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-8"><small>Costo Minuto (Base):</small></div>
                            <div class="col-4">
                                <input type="number" class="form-control form-control-sm text-end" id="costoMinuto"
                                    value="0.045" step="0.001">
                            </div>
                        </div>
                        <div class="alert alert-info py-1 px-2 mt-3 mb-0 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Cuota Diaria = Segundos Jornada / Tiempo Operación
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Datos iniciales (si es edición)
    const operacionesIniciales = <?= isset($plantilla['operaciones']) ? json_encode($plantilla['operaciones']) : '[]' ?>;

    // Catálogo de operaciones (Nombre -> Detalles)
    const catalogoOperaciones = {};
    <?php if (!empty($operacionesUnicas)): ?>
        <?php foreach ($operacionesUnicas as $op): ?>
            catalogoOperaciones[<?= json_encode($op['nombre']) ?>] = <?= json_encode($op) ?>;
        <?php endforeach; ?>
    <?php endif; ?>

    console.log('Catálogo de operaciones cargado:', Object.keys(catalogoOperaciones).length, 'operaciones');

    $(document).ready(function () {
        // Cargar operaciones iniciales o una fila vacía
        if (operacionesIniciales.length > 0) {
            operacionesIniciales.forEach(op => addRow(op));
        } else {
            // Agregar 5 filas vacías por defecto
            for (let i = 0; i < 5; i++) addRow();
        }

        // Eventos
        $('#btnAddRow').click(() => addRow());

        // Delegación de eventos para inputs en la tabla
        $('#gridOperaciones').on('input', 'input.calc-trigger', function () {
            const row = $(this).closest('tr');
            calcularFila(row);
            calcularTotales();
        });

        // Auto-completado de operaciones
        $('#gridOperaciones').on('input', '.op-nombre', function () {
            const input = $(this);
            const nombre = input.val().trim();
            const row = input.closest('tr');
            const spinner = row.find('.op-spinner');

            if (catalogoOperaciones[nombre]) {
                // Mostrar spinner
                spinner.show();

                // Simular pequeña carga para UX (opcional, pero solicitado)
                setTimeout(() => {
                    const op = catalogoOperaciones[nombre];

                    // Rellenar campos si están vacíos o si el usuario lo confirma (aquí lo hacemos directo)
                    row.find('.op-tiempo').val(op.tiempo_segundos);
                    row.find('.op-precio').val(op.precio_operacion);
                    row.find('.op-seccion').val(op.seccion);
                    row.find('.op-depto').val(op.departamento);

                    // Recalcular
                    calcularFila(row);
                    calcularTotales();

                    // Ocultar spinner
                    spinner.hide();
                }, 300);
            }
        });

        $('#gridOperaciones').on('click', '.btn-remove', function () {
            $(this).closest('tr').remove();
            renumerarFilas();
            calcularTotales();
        });

        // Eventos para parámetros globales
        $('#segundosJornada, #costoMinuto, #precioAutorizado').on('input', function () {
            recalcularTodo();
        });

        // Guardar
        $('#btnGuardar').click(guardarPlantilla);

        calcularTotales();
    });

    function addRow(data = null) {
        const tbody = $('#tbodyOperaciones');
        const index = tbody.children().length + 1;

        const nombre = data ? data.nombre : '';
        const tiempo = data ? (data.tiempo_segundos || '') : '';
        const seccion = data ? (data.seccion || '') : '';
        const depto = data ? (data.departamento || '') : '';
        const precio = data ? (data.precio_operacion || '') : '';

        const row = `
            <tr>
                <td class="text-center align-middle row-index">${index}</td>
                <td>
                    <div class="position-relative">
                        <input type="text" class="op-nombre" value="${nombre}" placeholder="Descripción" list="operacionesList">
                        <div class="op-spinner position-absolute top-50 end-0 translate-middle-y me-2" style="display: none;">
                            <i class="fas fa-spinner fa-spin text-primary"></i>
                        </div>
                    </div>
                </td>
                <td><input type="number" class="op-tiempo calc-trigger text-center" value="${tiempo}" placeholder="0"></td>
                <td class="readonly text-end op-cuota-diaria">0</td>
                <td class="readonly text-end op-cuota-bi">0</td>
                <td><input type="number" class="op-precio calc-trigger text-end" value="${precio}" step="0.001" placeholder="0.00"></td>
                <td><input type="text" class="op-seccion" value="${seccion}" list="seccionesList"></td>
                <td><input type="text" class="op-depto" value="${depto}" list="deptosList"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger btn-remove p-0"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
        tbody.append(row);

        if (data) {
            calcularFila(tbody.children().last());
        }
    }

    function calcularFila(row) {
        const tiempo = parseFloat(row.find('.op-tiempo').val()) || 0;
        const segundosJornada = parseFloat($('#segundosJornada').val()) || 0;

        // Cuota Diaria
        let cuotaDiaria = 0;
        if (tiempo > 0) {
            cuotaDiaria = Math.floor(segundosJornada / tiempo);
        }
        row.find('.op-cuota-diaria').text(cuotaDiaria);

        // Cuota Bihoraria (Jornada / 4 aprox, o 2 horas)
        // Asumiendo jornada de 9.5 horas -> 4.75 bloques de 2 horas. 
        // Simplificación: Cuota Diaria / (Horas Jornada / 2)
        // Usaremos un factor estándar: Cuota Diaria / 4.5 (9 horas)
        const cuotaBi = Math.floor(cuotaDiaria / 4.5);
        row.find('.op-cuota-bi').text(cuotaBi);

        // Si no hay precio manual, sugerir precio base
        // const costoMinuto = parseFloat($('#costoMinuto').val()) || 0;
        // if (tiempo > 0 && row.find('.op-precio').val() === '') {
        //     const precioSugerido = (tiempo / 60) * costoMinuto;
        //     row.find('.op-precio').val(precioSugerido.toFixed(3));
        // }
    }

    function renumerarFilas() {
        $('#tbodyOperaciones tr').each(function (index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function recalcularTodo() {
        $('#tbodyOperaciones tr').each(function () {
            calcularFila($(this));
        });
        calcularTotales();
    }

    function calcularTotales() {
        let totalSegundos = 0;
        let totalCosto = 0;

        $('#tbodyOperaciones tr').each(function () {
            const tiempo = parseFloat($(this).find('.op-tiempo').val()) || 0;
            const precio = parseFloat($(this).find('.op-precio').val()) || 0;

            totalSegundos += tiempo;
            totalCosto += precio;
        });

        const totalMinutos = totalSegundos / 60;

        $('#totalSegundos').text(totalSegundos);
        $('#totalMinutos').text(totalMinutos.toFixed(2));
        $('#totalCosto').text('$' + totalCosto.toFixed(2));

        const precioAutorizado = parseFloat($('#precioAutorizado').val()) || 0;
        const utilidad = precioAutorizado - totalCosto;

        const elUtilidad = $('#utilidad');
        elUtilidad.text('$' + utilidad.toFixed(2));
        if (utilidad < 0) {
            elUtilidad.removeClass('text-success').addClass('text-danger');
        } else {
            elUtilidad.removeClass('text-danger').addClass('text-success');
        }
    }

    function guardarPlantilla() {
        const nombre = $('input[name="nombre_plantilla"]').val().trim();
        const tipo = $('input[name="tipo_prenda"]').val().trim();

        if (!nombre || !tipo) {
            Swal.fire('Error', 'Nombre y Tipo de Prenda son obligatorios', 'error');
            return;
        }

        const operaciones = [];
        $('#tbodyOperaciones tr').each(function () {
            const row = $(this);
            const nombreOp = row.find('.op-nombre').val().trim();
            if (nombreOp) {
                operaciones.push({
                    nombre: nombreOp,
                    tiempo_segundos: parseFloat(row.find('.op-tiempo').val()) || 0,
                    precio_operacion: parseFloat(row.find('.op-precio').val()) || 0,
                    seccion: row.find('.op-seccion').val().trim(),
                    departamento: row.find('.op-depto').val().trim(),
                    es_componente: true, // Por defecto
                    orden: parseInt(row.find('.row-index').text())
                });
            }
        });

        if (operaciones.length === 0) {
            Swal.fire('Error', 'Debe agregar al menos una operación', 'warning');
            return;
        }

        const data = {
            id: $('input[name="id"]').val(),
            nombre_plantilla: nombre,
            tipo_prenda: tipo,
            operaciones: JSON.stringify(operaciones)
        };

        $.post('<?= base_url('modulo3/api/plantillas-operaciones/guardar-completo') ?>', data)
            .done(function (response) {
                if (response.ok) {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Plantilla guardada correctamente',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = '<?= base_url('modulo3/reportes/costos') ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            })
            .fail(function () {
                Swal.fire('Error', 'Error al guardar la plantilla', 'error');
            });
    }
</script>

<!-- Datalists for autocomplete -->
<datalist id="seccionesList">
    <option value="ENSAMBLE">
    <option value="CORTE">
    <option value="ACABADO">
</datalist>
<datalist id="deptosList">
    <option value="BORDADO">
    <option value="LINEA 1">
    <option value="LINEA 2">
    <option value="EMPAQUE">
</datalist>
<datalist id="operacionesList">
    <?php if (!empty($operacionesUnicas)): ?>
        <?php foreach ($operacionesUnicas as $op): ?>
            <option value="<?= esc($op['nombre']) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
</datalist>
<?= $this->endSection() ?>