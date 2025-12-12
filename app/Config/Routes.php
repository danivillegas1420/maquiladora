<?php

use Config\Services;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes = Services::routes();

/* --------------------------------------------------------------------
 * Rutas del sistema (NO tocar)
 * ------------------------------------------------------------------*/
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/* --------------------------------------------------------------------
 * Defaults
 * ------------------------------------------------------------------*/
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('UsuarioController');
$routes->setDefaultMethod('login');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // todo debe declararse explíclicitamente

/* --------------------------------------------------------------------
 * Auth
 * ------------------------------------------------------------------*/
$routes->get('/register', 'UsuarioController::index', ['as' => 'register']);
$routes->post('/register/store', 'UsuarioController::store');
$routes->get('/login', 'UsuarioController::login', ['as' => 'login']);
$routes->post('/login', 'UsuarioController::authenticate');
$routes->get('/login_maquiladoras', 'UsuarioController::login_maquiladoras', ['as' => 'login_maquiladoras']);
$routes->post('/login_maquiladoras', 'UsuarioController::authenticate_maquiladoras');
$routes->get('/logout', 'UsuarioController::logout');

// API sueltos
$routes->get('api/maquiladoras', 'UsuarioController::getMaquiladoras');

// Gestión de Maquilas
$routes->get('gestion_maquilas', function () {
    return view('modulos/gestion_maquilas', [
        'title' => 'Gestión de Maquilas',
        'notifCount' => 0,
    ]);
}, ['filter' => 'auth']);

// API para Gestión de Maquiladoras
$routes->group('api/maquiladoras', ['filter' => 'auth'], function ($routes) {
    $routes->get('listar', 'MaquiladorasAPI::listar');
    $routes->get('obtener/(:num)', 'MaquiladorasAPI::obtener/$1');
    $routes->post('crear', 'MaquiladorasAPI::crear');
    $routes->post('actualizar', 'MaquiladorasAPI::actualizar');
    $routes->delete('eliminar/(:num)', 'MaquiladorasAPI::eliminar/$1');
    $routes->get('roles/(:num)', 'MaquiladorasAPI::roles/$1');
    $routes->get('usuarios/(:num)', 'MaquiladorasAPI::usuarios/$1');
    $routes->post('usuarios/(:num)', 'MaquiladorasAPI::crearUsuario/$1');
    $routes->post('usuarios/(:num)/status', 'MaquiladorasAPI::actualizarUsuarioStatus/$1');
    $routes->post('usuarios/(:num)/rol', 'MaquiladorasAPI::actualizarUsuarioRol/$1');
});

// Backup (BD y archivos uploads) - requiere estar autenticado
$routes->group('api/backup', ['filter' => 'auth'], function ($routes) {
    $routes->get('db', 'Backup::db');
    $routes->get('files', 'Backup::files');
});

// Maquiladora principal
$routes->get('maquiladora', 'Maquiladora::index', ['as' => 'maquiladora']);
$routes->post('maquiladora/update', 'Maquiladora::update');

// Dashboard API
$routes->get('api/dashboard', 'Dashboard::api');
$routes->get('api/debug-inventory', 'Dashboard::debugInventory');

/* --------------------------------------------------------------------
 * Módulo 11 - Usuarios (acciones AJAX sueltas)
 * ------------------------------------------------------------------*/
$routes->post('modulo11/eliminar_usuario', 'Modulos::m11_eliminar_usuario');
$routes->get('modulo11/obtener_usuario/(:num)', 'Modulos::m11_obtener_usuario/$1');
$routes->post('modulo11/actualizar_usuario', 'Modulos::m11_actualizar_usuario');

/* =========================
 * Inventario de Almacenes
 * ========================= */
$routes->get('almacen/inventario', 'AlmacenController::inventario');
$routes->get('api/almacenes', 'AlmacenController::apiAlmacenes');
$routes->get('api/ubicaciones', 'AlmacenController::apiUbicaciones');
$routes->get('api/inventario', 'AlmacenController::apiInventario');
$routes->get('api/inventario/lotes', 'AlmacenController::apiLotes');
$routes->get('api/inventario/movimientos/(:num)', 'AlmacenController::apiMovimientos/$1');
$routes->post('api/inventario/agregar', 'AlmacenController::apiAgregar');
$routes->post('api/inventario/editar', 'AlmacenController::apiEditar');
$routes->delete('api/inventario/eliminar/(:num)', 'AlmacenController::apiEliminar/$1');
// extras
$routes->get('api/inventario/existe', 'AlmacenController::apiExiste');
$routes->get('api/articulos/buscar', 'AlmacenController::apiBuscarArticulos');
$routes->get('api/articulos/detalle', 'AlmacenController::apiArticuloDetalle');
$routes->get('api/inventario/kpis', 'AlmacenController::apiKpis');
$routes->get('api/inventario/resumen-articulo/(:num)', 'AlmacenController::apiResumenArticulo/$1');

// Ordenes (Logistics)
$routes->post('modulo3/ordenes/crear', 'OrdenesController::crear');
$routes->get('modulo3/ordenes/(:num)/json', 'OrdenesController::json/$1');
$routes->post('modulo3/ordenes/(:num)/editar', 'OrdenesController::editar/$1');
$routes->delete('modulo3/ordenes/(:num)/eliminar', 'OrdenesController::eliminar/$1');

// Transportistas
$routes->get('modulo3/transportistas/listar', 'LogisticaController::listarTransportistas');
$routes->post('modulo3/transportistas/crear', 'LogisticaController::crearTransportista');
$routes->get('modulo3/transportistas/(:num)/json', 'LogisticaController::transportistaJson/$1');
$routes->post('modulo3/transportistas/(:num)/editar', 'LogisticaController::editarTransportista/$1');
$routes->delete('modulo3/transportistas/(:num)/eliminar', 'LogisticaController::eliminarTransportista/$1');

// Desperdicios & Reprocesos (Calidad)
$routes->get('calidad/desperdicios', 'Calidad::desperdicios');
$routes->post('calidad/desperdicios/guardar', 'Calidad::guardarDesecho');
$routes->post('calidad/desperdicios/(:num)/editar', 'Calidad::editarDesecho/$1');
$routes->post('calidad/desperdicios/(:num)/eliminar', 'Calidad::eliminarDesecho/$1');
$routes->get('calidad/desperdicios/(:num)', 'Calidad::verDesecho/$1');

$routes->post('calidad/reprocesos/guardar', 'Calidad::guardarReproceso');
$routes->post('calidad/reprocesos/(:num)/editar', 'Calidad::editarReproceso/$1');
$routes->post('calidad/reprocesos/(:num)/eliminar', 'Calidad::eliminarReproceso/$1');
$routes->get('calidad/reprocesos/(:num)', 'Calidad::verReproceso/$1');

/* =========================
 * PROVEEDORES
 * ========================= */
// Vista principal de proveedores
$routes->get(
    'proveedores',
    'ProveedorController::index'
);
// Crear / actualizar proveedor
$routes->post(
    'proveedores/store',
    'ProveedorController::store'
);
// Eliminar proveedor
$routes->post(
    'proveedores/eliminar/(:num)',
    'ProveedorController::eliminar/$1'
);
// Crear orden de pedido a proveedor (POST del modal)
$routes->post(
    'proveedores/orden',
    'ProveedorController::crearOrden'
);
// Historial de órdenes de un proveedor (AJAX que devuelve JSON)
$routes->get(
    'proveedores/historial/(:num)',
    'ProveedorController::historial/$1'
);
// Ver la orden en HTML
$routes->get(
    'proveedores/orden/(:num)',
    'ProveedorController::verOrden/$1'
);
// Marcar orden como cumplida (acepta GET/POST)
$routes->match(
    ['get', 'post'],
    'proveedores/orden/completar/(:num)',
    'ProveedorController::completarOrden/$1'
);
// Eliminar orden de proveedor (acepta GET/POST)
$routes->match(
    ['get', 'post'],
    'proveedores/orden/eliminar/(:num)',
    'ProveedorController::eliminarOrden/$1'
);

/* --------------------------------------------------------------------
 * Home / Auth landing
 * ------------------------------------------------------------------*/
$routes->get('/', 'UsuarioController::login');
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

/* --------------------------------------------------------------------
 * API (Dashboard y Clientes sin duplicados)
 * ------------------------------------------------------------------*/
$routes->group('api', static function ($routes) {
    $routes->get('dashboard', 'Api::dashboard'); // ?range=30

    // Clientes (con CORS para POST si lo necesitas)
    $routes->match(['post', 'options'], 'clientes/crear', 'Clientes::crear');
    $routes->match(['post', 'options'], 'clientes/(:num)/editar', 'Clientes::actualizar/$1');
    $routes->match(['post', 'options'], 'clientes/(:num)/eliminar', 'Clientes::eliminar/$1');

    // Lectura/detalle
    $routes->get('clientes/(:num)', 'Clientes::json_detalle/$1');

    // Obtener clasificaciones de clientes
    $routes->get('clientes/clasificaciones', 'Clientes::getClasificaciones');
    $routes->match(['post', 'options'], 'clientes/clasificaciones/crear', 'Clientes::crearClasificacion');
    $routes->match(['post', 'options'], 'clientes/clasificaciones/(:num)/editar', 'Clientes::editarClasificacion/$1');
    $routes->match(['post', 'options'], 'clientes/clasificaciones/(:num)/eliminar', 'Clientes::eliminarClasificacion/$1');
});

/* --------------------------------------------------------------------
 * Muestras (prototipos)
 * ------------------------------------------------------------------*/
$routes->get('muestras', 'Muestras::index');
$routes->post('muestras/data', 'Muestras::data');
$routes->get('muestras/evaluar/(:num)', 'Muestras::evaluar/$1');
$routes->post('muestras/guardar', 'Muestras::guardar');
$routes->get('muestras/archivo/(:num)', 'Muestras::archivo/$1');

/* --------------------------------------------------------------------
 * Legacy
 * ------------------------------------------------------------------*/
$routes->get('/perfilempleado', 'Modulos::m1_perfilempleado', ['filter' => 'auth']);
$routes->get('/pedidos', 'Modulos::m1_pedidos');
$routes->get('/agregar_pedido', 'Modulos::m1_agregar');
$routes->get('/editarpedido/(:num)', 'Modulos::m1_editar/$1');
$routes->get('/detalle_pedido/(:num)', 'Modulos::m1_detalles/$1');
$routes->get('/perfildisenador', 'Modulos::m2_perfildisenador', ['filter' => 'auth']);
$routes->get('/catalogodisenos', 'Modulos::m2_catalogodisenos');

/* --------------------------------------------------------------------
 * Módulo 1
 * ------------------------------------------------------------------*/
$routes->group('modulo1', [], function ($routes) {
    $routes->get('/', 'Modulos::m1_index');
    $routes->get('pedidos', 'Modulos::m1_pedidos');
    $routes->get('pedidos_clientes', 'Modulos::m1_pedidos_clientes');
    // Pagos de empleados: se maneja en PagosController
    $routes->get('pagos', 'PagosController::index');
    $routes->get('produccion', 'Modulos::m1_produccion');
    $routes->get('agregar', 'Modulos::m1_agregar');
    $routes->get('agregar_pedido', 'Modulos::m1_agregar');
    $routes->get('editar/(:num)', 'Modulos::m1_editar/$1');
    $routes->post('editar', 'Modulos::m1_editar');
    $routes->get('detalles/(:num)', 'Modulos::m1_detalles/$1');
    $routes->get('perfilempleado', 'Modulos::m1_perfilempleado');
    $routes->get('ordenes', 'Modulos::m1_ordenes');
    $routes->post('ordenes/compartir', 'Modulos::m1_ordenes_compartir');
    $routes->post('empleado/guardar', 'Modulos::m1_empleado_guardar', ['filter' => 'auth']);
    // APIs / Producción
    $routes->get('pedido/(:num)/json', 'Modals::pedido_json/$1');
    $routes->get('pedido/(:num)/pdf', 'Modulos::m1_pedido_pdf/$1');
    $routes->get('pedido/(:num)/excel', 'Modulos::m1_pedido_excel/$1');
    $routes->get('clientes/json', 'Clientes::json_catalogo');
    $routes->get('disenos/json', 'Disenos::json_catalogo');
    $routes->get('disenos/(:num)/json', 'Disenos::json_detalle/$1');
    $routes->get('disenos/debug', 'Disenos::debug');
    $routes->get('ordenes-produccion', 'Produccion::ordenes');
    $routes->post('ordenes/estatus', 'Produccion::actualizarEstatus');
    $routes->post('ordenes/eliminar', 'Produccion::orden_eliminar');
    $routes->get('ordenes/(:num)/json', 'Produccion::orden_json/$1');
    // Tareas del empleado (producción)
    $routes->get('produccion/tareas', 'Produccion::tareas_empleado_json');
    // Tiempo de trabajo
    $routes->match(['post', 'options'], 'produccion/tiempo/iniciar', 'Produccion::tiempo_trabajo_iniciar');
    $routes->match(['post', 'options'], 'produccion/tiempo/finalizar', 'Produccion::tiempo_trabajo_finalizar');
    // Crear/Eliminar pedido (OC + OP)
    $routes->post('pedidos/crear', 'Modulos::m1_pedidos_crear');
    $routes->post('pedidos/eliminar', 'Modulos::m1_pedido_eliminar');
    // Cargar datos de pedido desde PDF
    $routes->post('pedidos/cargar-pdf', 'Modulos::m1_pedidos_cargar_pdf');

    // Endpoints para modales
    $routes->get('ordenes/folio/(:segment)/json', 'Produccion::orden_json_folio/$1');
    $routes->get('ordenes/(:num)/asignaciones', 'Produccion::asignaciones/$1');
    $routes->match(['post', 'options'], 'ordenes/asignaciones/agregar', 'Produccion::asignaciones_agregar');
    $routes->match(['post', 'options'], 'ordenes/asignaciones/agregar-multiple', 'Produccion::asignaciones_agregar_multiple');
    $routes->match(['post', 'options'], 'ordenes/asignaciones/eliminar', 'Produccion::asignaciones_eliminar');

    // Evaluación
    $routes->get('evaluar/(:num)', 'Modulos::m1_evaluar/$1');
    $routes->post('guardar-evaluacion', 'Modulos::m1_guardarEvaluacion');
});

/* --------------------------------------------------------------------
 * Diag rápidos
 * ------------------------------------------------------------------*/
$routes->get('diag/ping', fn() => 'OK DIAG PING');
$routes->get('diag/agregar', function () {
    return view('modulos/agregar_pedido', ['title' => 'Diag · Agregar Pedido', 'notifCount' => 0]);
});
$routes->get('clientes', 'ClientesPage::index', ['filter' => 'auth']);

/* --------------------------------------------------------------------
 * Módulo 2
 * ------------------------------------------------------------------*/
$routes->group('modulo2', [], function ($routes) {
    $routes->get('/', 'Modulos::m2_index');
    $routes->get('perfildisenador', 'Modulos::m2_perfildisenador');
    $routes->get('catalogodisenos', 'Modulos::m2_catalogodisenos');

    // Diseños - CRUD y JSON
    $routes->get('diseno/(:num)/json', 'Modulos::m2_diseno_json/$1');
    $routes->post('disenos/crear', 'Modulos::m2_crear_diseno');
    $routes->post('disenos/actualizar/(:num)', 'Modulos::m2_actualizar/$1');
    $routes->get('disenos/json', 'Modulos::m2_disenos_json');

    // Catálogos - Sexo
    $routes->get('catalogos/sexo', 'CatalogoDisenos::catalogoSexo');
    $routes->get('catalogos/sexo/debug', 'CatalogoDisenos::debugCatalogoSexo');
    $routes->post('catalogos/sexo/crear', 'CatalogoDisenos::catalogoSexoCrear');
    $routes->post('catalogos/sexo/actualizar/(:num)', 'CatalogoDisenos::catalogoSexoActualizar/$1');
    $routes->post('catalogos/sexo/eliminar/(:num)', 'CatalogoDisenos::catalogoSexoEliminar/$1');

    // Catálogos - Tallas
    $routes->get('catalogos/tallas', 'CatalogoDisenos::catalogoTallas');
    $routes->post('catalogos/tallas/crear', 'CatalogoDisenos::catalogoTallasCrear');
    $routes->post('catalogos/tallas/actualizar/(:num)', 'CatalogoDisenos::catalogoTallasActualizar/$1');
    $routes->post('catalogos/tallas/eliminar/(:num)', 'CatalogoDisenos::catalogoTallasEliminar/$1');

    // Catálogos - Tipo de Corte
    $routes->get('catalogos/tipo-corte', 'CatalogoDisenos::catalogoTipoCorte');
    $routes->post('catalogos/tipo-corte/crear', 'CatalogoDisenos::catalogoTipoCorteCrear');
    $routes->post('catalogos/tipo-corte/actualizar/(:num)', 'CatalogoDisenos::catalogoTipoCorteActualizar/$1');
    $routes->post('catalogos/tipo-corte/eliminar/(:num)', 'CatalogoDisenos::catalogoTipoCorteEliminar/$1');

    // Catálogos - Tipo de Ropa
    $routes->get('catalogos/tipo-ropa', 'CatalogoDisenos::catalogoTipoRopa');
    $routes->post('catalogos/tipo-ropa/crear', 'CatalogoDisenos::catalogoTipoRopaCrear');
    $routes->post('catalogos/tipo-ropa/actualizar/(:num)', 'CatalogoDisenos::catalogoTipoRopaActualizar/$1');
    $routes->post('catalogos/tipo-ropa/eliminar/(:num)', 'CatalogoDisenos::catalogoTipoRopaEliminar/$1');

    // Artículos (para materiales)
    $routes->get('articulos/json', 'CatalogoDisenos::articulosJson');
    $routes->get('articulos/debug', 'CatalogoDisenos::debugArticulos');
});

/* --------------------------------------------------------------------
 * Rutas raíz · Mantenimiento Correctivo (alias directo)
 * ------------------------------------------------------------------*/
$routes->group('mantenimiento', static function ($r) {
    $r->get('correctivo', 'MantenimientoCorrectivo::index');
    $r->post('correctivo/crear', 'MantenimientoCorrectivo::crear');
    $r->post('correctivo/actualizar/(:num)', 'MantenimientoCorrectivo::actualizar/$1');
    $r->post('correctivo/eliminar/(:num)', 'MantenimientoCorrectivo::eliminar/$1');

    // JSON: Historial por máquina
    $r->get('correctivo/historial/maquina/(:num)', 'MantenimientoCorrectivo::historialPorMaquina/$1');
});

/* --------------------------------------------------------------------
 * Notificaciones + Mantenimiento Programado
 * ------------------------------------------------------------------*/
// Notificaciones (vista nueva)
$routes->get('notificaciones1', 'Notificaciones1::index', ['filter' => 'auth']);
$routes->post('notificaciones1/marcar/(:num)', 'Notificaciones1::marcar/$1', ['filter' => 'auth']);
$routes->post('notificaciones1/borrar/(:num)', 'Notificaciones1::borrar/$1', ['filter' => 'auth']);
// Compatibilidad con enlaces antiguos del menú
$routes->get('modulo3/notificaciones', 'Notificaciones1::index', ['filter' => 'auth']);

// Programación/Calendario/Alertas (mantenimiento preventivo)
$routes->get('mtto/calendario', 'MttoProgramacion::calendario', ['filter' => 'auth']);
$routes->get('mtto/programacion', 'MttoProgramacion::lista', ['filter' => 'auth']); // vista tipo lista
$routes->post('mtto/programacion/guardar', 'MttoProgramacion::guardar', ['filter' => 'auth']);

// Feed de eventos para FullCalendar
$routes->get('mtto/api/eventos', 'MttoProgramacion::apiEventos', ['filter' => 'auth']);
// Alias opcional (compatibilidad con nombre anterior)
$routes->get('mtto/api/revisiones', 'MttoProgramacion::apiEventos', ['filter' => 'auth']);

// “Cron” HTTP para generar instancias y notificaciones (proteger con token si lo deseas)
$routes->get('mtto/alertas/run', 'MttoAlertas::run');

/* --------------------------------------------------------------------
 * Módulo 3 (Dashboard, WIP, Inspección, Mantenimiento, Logística)
 * ------------------------------------------------------------------*/
$routes->group('modulo3', ['filter' => 'auth'], function ($routes) {

    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('ordenes', 'Modulos::ordenes');
    $routes->get('reportes', 'ReportesController::index');
    $routes->group('reportes', function ($r) {
        $r->get('api/eficiencia', 'ReportesController::produccionEficiencia');
        $r->get('api/mensual', 'ReportesController::produccionMensual');
        $r->get('api/calidad', 'ReportesController::calidadControl');
        $r->get('exportar/(:segment)', 'ReportesController::exportar/$1');
    });

    // ===== MRP (ALIAS bajo /modulo3/mrp) =====
    $routes->group('mrp', function ($r) {
        $r->get('', 'Modulos::mrp');

        // Requerimientos CRUD
        $r->post('requerimientos/crear', 'Modulos::crearRequerimiento');
        $r->post('requerimientos/(:num)/editar', 'Modulos::editarRequerimiento/$1');
        $r->post('requerimientos/(:num)/eliminar', 'Modulos::eliminarRequerimiento/$1');
        $r->get('requerimientos/(:num)/json', 'Modulos::requerimientoJson/$1');
        $r->post('requerimientos/(:num)/generar-oc', 'Modulos::generarOCDesdeRequerimiento/$1');

        // OCs CRUD
        $r->post('oc/crear', 'Modulos::crearOC');
        $r->post('oc/(:num)/editar', 'Modulos::editarOC/$1');
        $r->post('oc/(:num)/eliminar', 'Modulos::eliminarOC/$1');
        $r->get('oc/(:num)/json', 'Modulos::ocJson/$1');
        $r->get('oc/(:num)/pdf', 'Modulos::descargarOCPdf/$1');
    });

    // ===== Notifications API =====
    $routes->group('api/notifications', function ($r) {
        $r->get('', 'NotificationController::index');
        $r->get('unread-count', 'NotificationController::unreadCount');
        $r->get('all', 'NotificationController::indexAll');
        $r->get('permissions', 'NotificationController::getUserPermissions');
        $r->post('(:num)/read', 'NotificationController::markAsRead/$1');
        $r->post('read-all', 'NotificationController::markAllAsRead');
    });

    // ===== Notificaciones2 (Demo View) =====
    $routes->get('notificaciones2', 'Notificaciones2::index');
    $routes->post('notificaciones2/mark-read/(:num)', 'Notificaciones2::markAsRead/$1');
    $routes->post('notificaciones2/mark-all-read', 'Notificaciones2::markAllAsRead');
    $routes->post('notificaciones2/delete/(:num)', 'Notificaciones2::delete/$1');
    $routes->get('notificaciones2/generate-test', 'Notificaciones2::generateTestNotifications');

    // Incidencias - modal ligero
    $routes->get('incidencias/modal', 'Incidencias::modal', ['filter' => 'auth']);

    // ===== ALIAS Calidad (Desperdicios & Reprocesos) bajo /modulo3 =====
    $routes->get('desperdicios', 'Calidad::desperdicios');
    $routes->post('desperdicios/guardar', 'Calidad::guardarDesecho');
    $routes->get('desperdicios/(:num)', 'Calidad::verDesecho/$1');
    $routes->post('desperdicios/(:num)/editar', 'Calidad::editarDesecho/$1');
    $routes->post('reprocesos/guardar', 'Calidad::guardarReproceso');
    $routes->get('reprocesos/(:num)', 'Calidad::verReproceso/$1');
    $routes->post('reprocesos/(:num)/editar', 'Calidad::editarReproceso/$1');

    // WIP
    $routes->get('wip', 'Wip::index');
    $routes->get('wip/json', 'Wip::json');
    $routes->get('wip/debug', 'Wip::debug');
    $routes->post('wip/actualizar/(:num)', 'Wip::actualizar/$1');

    // Incidencias
    $routes->get('incidencias', 'Incidencias::index');
    $routes->post('incidencias/crear', 'Incidencias::store');
    $routes->get('incidencias/eliminar/(:num)', 'Incidencias::delete/$1');

    // Grupo Inspección
    $routes->group('inspeccion', function ($routes) {
        $routes->get('/', 'Inspeccion::index');
        $routes->get('nueva', 'Inspeccion::nueva');
        $routes->post('guardar', 'Inspeccion::guardar');
        $routes->get('ver/(:num)', 'Inspeccion::ver/$1');
        $routes->get('editar/(:num)', 'Inspeccion::editar/$1');
        $routes->post('actualizar/(:num)', 'Inspeccion::actualizar/$1');
        $routes->get('eliminar/(:num)', 'Inspeccion::eliminar/$1');
        $routes->get('evaluar/(:num)', 'Inspeccion::evaluar/$1');
        $routes->post('evaluar/guardar/(:num)', 'Inspeccion::guardarEvaluacion/$1');
        $routes->post('actualizar-punto', 'Inspeccion::actualizarPunto');
        // CRUD puntos de inspección
        $routes->get('puntos/json', 'Inspeccion::puntosJson');
        $routes->post('puntos/crear', 'Inspeccion::puntoCrear');
        $routes->post('puntos/editar', 'Inspeccion::puntoEditar');
        $routes->post('puntos/eliminar', 'Inspeccion::puntoEliminar');
        // Evidencia fotográfica
        $routes->get('evidencia/(:num)', 'Inspeccion::evidencia/$1');
    });

    // Grupo Control de Bultos
    $routes->group('control-bultos', function ($routes) {
        $routes->get('/', 'ControlBultosController::index');
        $routes->get('(:num)', 'ControlBultosController::detalle/$1');
        $routes->get('(:num)/matriz', 'ControlBultosController::vistaMatriz/$1');
        // Editor de Plantillas
        $routes->get('plantillas/editor/(:num)', 'ControlBultosController::editorPlantilla/$1');
        $routes->get('plantillas/nueva', 'ControlBultosController::nuevaPlantilla');
    });

    // Reportes
    $routes->get('reportes', 'ReportesController::index');
    $routes->get('reportes/api/(:segment)', 'ReportesController::$1');
    $routes->get('reportes/exportar/(:segment)', 'ReportesController::exportar/$1');
    $routes->get('reportes/costos', 'ReportesController::costos');
    $routes->get('reportes/costos/ver/(:num)', 'ReportesController::verCosto/$1');
    $routes->get('reportes/costos/descargar/(:num)', 'ReportesController::descargarCosto/$1');

    // Gestión de Cortes
    $routes->group('cortes', [], function ($routes) {
        $routes->get('/', 'CorteController::index');
        $routes->get('nuevo', 'CorteController::nuevo');
        $routes->get('editar/(:num)', 'CorteController::editar/$1');
        $routes->post('guardar', 'CorteController::guardar');
    });

    // API Control de Bultos
    $routes->group('api/control-bultos', function ($routes) {

        $routes->get('/', 'ControlBultosController::listar');
        $routes->get('(:num)', 'ControlBultosController::detalle/$1');
        $routes->get('(:num)/progreso', 'ControlBultosController::progreso/$1');
        $routes->post('crear', 'ControlBultosController::crear');
        $routes->post('(:num)/editar', 'ControlBultosController::editar/$1');
        $routes->delete('(:num)/eliminar', 'ControlBultosController::eliminar/$1');
        $routes->post('registrar-produccion', 'ControlBultosController::registrarProduccion');
        $routes->post('registrar-produccion-matriz', 'ControlBultosController::registrarProduccionMatriz');
        $routes->post('crear-bultos-auto', 'ControlBultosController::crearBultosAuto');
        $routes->post('registrar-rendimiento-empleado', 'ControlBultosController::registrarRendimientoEmpleado');
        $routes->get('por-op/(:num)', 'ControlBultosController::obtenerControlesPorOP/$1');
        $routes->get('(:num)/registros-produccion', 'ControlBultosController::registrosProduccion/$1');
        $routes->get('(:num)/export-excel', 'ControlBultosController::exportExcel/$1');
    });

    // API Plantillas de Operaciones
    $routes->group('api/plantillas-operaciones', function ($routes) {
        $routes->get('/', 'ControlBultosController::listarPlantillas');
        $routes->post('crear', 'ControlBultosController::crearPlantilla');
        $routes->post('guardar-completo', 'ControlBultosController::guardarPlantillaCompleta');
        $routes->post('(:num)/editar', 'ControlBultosController::editarPlantilla/$1');
    });


    // Inventario / Mantenimiento (modulo3)
    $routes->get('mantenimiento_inventario', 'Maquinaria::index', ['filter' => 'auth']);
    $routes->get('mantenimiento_preventivo', 'Modulos::mantenimientoPreventivo');
    $routes->get('mantenimiento_correctivo', fn() => redirect()->to(site_url('mantenimiento/correctivo')));

    // CRUD Maquinaria
    $routes->get('maquinaria', 'Maquinaria::index', ['filter' => 'auth']);
    $routes->post('maquinaria/guardar', 'Maquinaria::guardar', ['filter' => 'auth']);
    $routes->get('maquinaria/editar/(:num)', 'Maquinaria::editar/$1', ['filter' => 'auth']);
    $routes->post('maquinaria/actualizar/(:num)', 'Maquinaria::actualizar/$1', ['filter' => 'auth']);
    $routes->post('maquinaria/eliminar/(:num)', 'Maquinaria::eliminar/$1', ['filter' => 'auth']);

    /* =========================
     * LOGÍSTICA · PREPARACIÓN / PACKING
     * ========================= */
    // Vista principal
    $routes->get(
        'logistica_preparacion',
        'EmbarquesController::packing',
        ['filter' => 'auth']
    );
    // Alias corto
    $routes->get('preparacion', 'EmbarquesController::packing');
    // Alias opcional
    $routes->get('embarques/packing', 'EmbarquesController::packing');

    // Embarques (acciones)
    $routes->post(
        'embarques/crear',
        'EmbarquesController::crear',
        ['filter' => 'auth']
    );
    $routes->post(
        'embarques/(:num)/agregar-orden',
        'EmbarquesController::agregarOrden/$1'
    );
    $routes->get(
        'embarques/(:num)/packing-list',
        'EmbarquesController::packingList/$1'
    );
    $routes->get(
        'embarques/(:num)/etiquetas',
        'EmbarquesController::etiquetas/$1'
    );

    // Acciones por pedido (OC)
    $routes->get(
        'ordenes/(:num)/json',
        'OrdenesController::json/$1'
    );
    $routes->post(
        'ordenes/(:num)/editar',
        'OrdenesController::editar/$1'
    );

    /* =========================
     * LOGÍSTICA · GESTIÓN (Tracking)
     * ========================= */
    $routes->get('logistica_gestion', 'LogisticaController::gestion', ['filter' => 'auth']);
    $routes->get('gestion', 'LogisticaController::gestion');

    // CRUD envíos
    $routes->post('envios/crear', 'LogisticaController::crearEnvio', ['filter' => 'auth']);
    $routes->get('envios/(:num)/json', 'LogisticaController::envioJson/$1', ['filter' => 'auth']);
    $routes->post('envios/(:num)/editar', 'LogisticaController::editarEnvio/$1', ['filter' => 'auth']);

    /* =========================
     * LOGÍSTICA · DOCUMENTOS
     * ========================= */
    $routes->get('documentos', 'LogisticaController::documentos', ['filter' => 'auth']);
    $routes->get('logistica_documentos', 'LogisticaController::documentos', ['filter' => 'auth']);
    // CRUD documentos
    $routes->post('documentos/crear', 'LogisticaController::crearDocumento');
    $routes->get('documentos/(:num)/json', 'LogisticaController::docJson/$1');
    $routes->post('documentos/(:num)/editar', 'LogisticaController::editarDocumento/$1');
    $routes->post('documentos/(:num)/eliminar', 'LogisticaController::eliminarDocumento/$1');
    $routes->get('documentos/(:num)/pdf', 'LogisticaController::descargarPdf/$1');
    // Almacenamiento local
    $routes->get('documentos/listar-pdfs/(:any)', 'LogisticaController::listarPdfsLocales/$1');
    $routes->get('documentos/pdf/(:any)', 'LogisticaController::servirPdfLocal/$1');
    $routes->post('documentos/guardar-pdf-local', 'LogisticaController::guardarPdfLocal');

    /* =========================
     * LOGÍSTICA · DOCUMENTO MANUAL (sin BD)
     * ========================= */
    $routes->get('embarque/manual', 'LogisticaController::documentoManual');
    $routes->post('embarque/manual', 'LogisticaController::documentoManual');
    $routes->get('embarque/manual/print', 'LogisticaController::documentoManualPrint');
    $routes->post('embarque/manual/print', 'LogisticaController::documentoManualPrint');

    // Proxy Storage
    $routes->post('storage/list', 'StorageProxy::list');

    // Órdenes de clientes
    $routes->get('ordenesclientes', 'Modulos::m1_ordenesclientes', ['filter' => 'auth']);
});

/* --------------------------------------------------------------------
 * Calidad (Desperdicios & Reprocesos) - grupo raíz
 * ------------------------------------------------------------------*/
$routes->group('calidad', [], function ($routes) {
    $routes->get('desperdicios', 'Calidad::desperdicios', ['filter' => 'auth']);
    $routes->post('desperdicios/guardar', 'Calidad::guardarDesecho', ['filter' => 'auth']);
    $routes->get('desperdicios/(:num)', 'Calidad::verDesecho/$1', ['filter' => 'auth']);
    $routes->post('desperdicios/(:num)/editar', 'Calidad::editarDesecho/$1', ['filter' => 'auth']);
    $routes->post('reprocesos/guardar', 'Calidad::guardarReproceso', ['filter' => 'auth']);
    $routes->get('reprocesos/(:num)', 'Calidad::verReproceso/$1', ['filter' => 'auth']);
    $routes->post('reprocesos/(:num)/editar', 'Calidad::editarReproceso/$1', ['filter' => 'auth']);
});



/* --------------------------------------------------------------------
 * Módulo 11 · Usuarios (UI)
 * Ahora el acceso fino se controla vía permisos (menu.roles, menu.usuarios),
 * por eso dejamos aquí solo el filtro de autenticación genérico.
 * ------------------------------------------------------------------*/
$routes->group('modulo11', ['filter' => 'auth'], function ($routes) {
    $routes->get('roles', 'Modulos::m11_roles');
    $routes->post('roles/actualizar', 'Modulos::m11_roles_actualizar');
    $routes->post('roles/agregar', 'Modulos::m11_roles_agregar');
    $routes->post('roles/eliminar', 'Modulos::m11_roles_eliminar');
    $routes->post('roles/permisos', 'Modulos::m11_roles_permisos');
    $routes->post('roles/guardar_permisos', 'Modulos::m11_roles_guardar_permisos');
    $routes->get('roles/inicializar_permisos', 'Modulos::m11_roles_inicializar_permisos');
    $routes->get('usuarios', 'Modulos::m11_usuarios');
    $routes->get('usuarios/agregar', 'Modulos::m11_agregar_usuario');
    $routes->post('usuarios/agregar', 'Modulos::m11_agregar_usuario');
    $routes->get('usuarios/editar/(:num)', 'Modulos::m11_editar_usuario/$1');
    $routes->post('usuarios/editar/(:num)', 'Modulos::m11_editar_usuario/$1');
});

/* --------------------------------------------------------------------
 * Utils DB (diagnóstico rápido)
 * ------------------------------------------------------------------*/
$routes->get('dbcheck', function () {
    $db = \Config\Database::connect();
    try {
        $row = $db->query('SELECT COUNT(*) AS c FROM maquina')->getRowArray();
        return 'OK DB. Registros en maquina: ' . ($row['c'] ?? 0);
    } catch (\Throwable $e) {
        return 'ERROR DB: ' . $e->getMessage();
    }
});
$routes->get('dbschema', function () {
    $db = \Config\Database::connect();
    $out = [];
    try {
        $tables = $db->query('SHOW TABLES')->getResultArray();
        $out[] = '<h3>SHOW TABLES</h3><pre>' . print_r($tables, true) . '</pre>';
        $candidates = ['orden_compra', 'OrdenCompra', 'cliente', 'Cliente', 'orden_produccion', 'OrdenProduccion'];
        foreach ($candidates as $t) {
            try {
                $desc = $db->query('DESCRIBE ' . $t)->getResultArray();
                $out[] = '<h4>DESCRIBE ' . $t . '</h4><pre> ' . print_r($desc, true) . '</pre>';
            } catch (\Throwable $e) {
                $out[] = '<h4>DESCRIBE ' . $t . '</h4><pre>ERROR: ' . $e->getMessage() . '</pre>';
            }
        }
        return implode("\n", $out);
    } catch (\Throwable $e) {
        return 'ERROR DBSchema: ' . $e->getMessage();
    }
});
$routes->get('pedido-debug/(:num)', function ($id) {
    $db = \Config\Database::connect();
    $out = [];
    try {
        $rowOc = null;
        $tablaOC = null;
        foreach (['orden_compra', 'OrdenCompra'] as $t) {
            try {
                $rowOc = $db->query('SELECT * FROM ' . $t . ' WHERE id = ?', [$id])->getRowArray();
                if ($rowOc) {
                    $tablaOC = $t;
                    break;
                }
            } catch (\Throwable $e) {
            }
        }
        $out[] = '<h3>Orden (' . ($tablaOC ?: 'no encontrada') . ')</h3><pre>' . print_r($rowOc, true) . '</pre>';
        $rowCli = null;
        $tablaCli = null;
        if ($rowOc && isset($rowOc['clienteId'])) {
            foreach (['cliente', 'Cliente'] as $t) {
                try {
                    $rowCli = $db->query('SELECT * FROM ' . $t . ' WHERE id = ?', [$rowOc['clienteId']])->getRowArray();
                    if ($rowCli) {
                        $tablaCli = $t;
                        break;
                    }
                } catch (\Throwable $e) {
                }
            }
        }
        $out[] = '<h3>Cliente (' . ($tablaCli ?: 'no encontrada') . ')</h3><pre>' . print_r($rowCli, true) . '</pre>';
        return implode("\n", $out);
    } catch (\Throwable $e) {
        return 'ERROR PedidoDebug: ' . $e->getMessage();
    }
});

/* --------------------------------------------------------------------
 * API · Supabase Storage (guardar PDFs)
 * ------------------------------------------------------------------*/
$routes->group('api/storage', static function ($r) {
    $r->post('pdf', 'StorageController::guardarPdf'); // subir PDF a Doc_Embarque (opcional)
});

/* --------------------------------------------------------------------
 * Logística · Facturación
 * ------------------------------------------------------------------*/
$routes->get(
    'logistica/embarque/(:num)/facturar/ui',
    'LogisticaController::facturarUI/$1',
    ['filter' => 'auth']
);
$routes->post(
    'logistica/embarque/(:num)/facturar',
    'LogisticaController::facturar/$1',
    ['filter' => 'auth']
);

/* --------------------------------------------------------------------
 * Logística · Aduanas (NUEVO)
 * ------------------------------------------------------------------*/
$routes->get(
    'logistica/embarque/(:num)/aduanas',
    'EmbarqueAduana::index/$1',
    ['filter' => 'auth']
);
$routes->get(
    'logistica/embarque/(:num)/aduanas/listar',
    'EmbarqueAduana::listar/$1',
    ['filter' => 'auth']
);
$routes->post(
    'logistica/embarque/aduanas/guardar',
    'EmbarqueAduana::guardar',
    ['filter' => 'auth']
);
$routes->post(
    'logistica/embarque/aduanas/eliminar',
    'EmbarqueAduana::eliminar',
    ['filter' => 'auth']
);

/* --------------------------------------------------------------------
 * Logística · Etiquetas de embarque (NUEVO)
 * ------------------------------------------------------------------*/
$routes->get(
    'logistica/embarque/(:num)/etiqueta',
    'EtiquetaEmbarqueController::show/$1',
    ['filter' => 'auth']
);
$routes->post(
    'logistica/embarque/(:num)/etiqueta/guardar',
    'EtiquetaEmbarqueController::guardar/$1',
    ['filter' => 'auth']
);
$routes->get(
    'logistica/etiqueta/(:num)/pdf',
    'EtiquetaEmbarqueController::pdf/$1',
    ['filter' => 'auth']
);
$routes->get(
    'logistica/etiqueta/(:num)/eliminar',
    'EtiquetaEmbarqueController::eliminar/$1',
    ['filter' => 'auth']
);

/* --------------------------------------------------------------------
 * FACTURA DEMO (Preview HTML + PDF por GET usando sesión)
 * ------------------------------------------------------------------*/
$routes->get('logistica/factura', 'FacturaDemoController::preview', ['filter' => 'auth']);
$routes->get('logistica/factura/(:num)', 'FacturaDemoController::preview/$1', ['filter' => 'auth']);
$routes->get('logistica/factura/(:num)/pdf', 'FacturaDemoController::pdf/$1', ['filter' => 'auth']);

/* --------------------------------------------------------------------
 * (Opcional) Endpoints POST para facturación
 * ------------------------------------------------------------------*/
$routes->group('facturacion', ['filter' => 'auth'], static function ($r) {
    // Espacio para futuras rutas POST
    // $r->post('demo/html', 'FacturaDemoController::html');
    // $r->post('demo/pdf',  'FacturaDemoController::pdfPost');
});

/* --------------------------------------------------------------------
 * Pedidos de Clientes (Módulo Clientes)
 * ------------------------------------------------------------------*/
$routes->get('pedidos-clientes', 'PedidosClientesController::index', ['filter' => 'auth:Cliente']);
$routes->get('pedidos-clientes/detalles/(:num)', 'PedidosClientesController::getOrdenDetalles/$1', ['filter' => 'auth:Cliente']);
$routes->get('pedidos-clientes/descargar-pdf/(:num)', 'PedidosClientesController::descargarPDF/$1', ['filter' => 'auth:Cliente']);

/* --------------------------------------------------------------------
 * Pagos de Empleados (endpoints auxiliares)
 * ------------------------------------------------------------------*/
$routes->group('modulo1/pagos', ['filter' => 'auth'], static function ($r) {
    $r->get('empleado/(:num)', 'PagosController::getEmpleado/$1');
    $r->post('actualizar-forma-pago', 'PagosController::actualizarFormaPago');
    $r->post('guardar-tarifa', 'PagosController::guardarTarifa');
    $r->get('tarifas', 'PagosController::tarifas');
    $r->post('reporte-diario', 'PagosController::reporteDiario');
    $r->get('exportar', 'PagosController::exportar');
});

// Database fix routes
$routes->get('db-fix/add-tipo-notificacion', 'DatabaseFixController::addTipoNotificacionColumn');

// Ruta temporal para probar íconos
$routes->get('test-icons', function () {
    return view('layouts/test');
});