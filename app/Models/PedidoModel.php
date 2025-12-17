<?php

namespace App\Models;

use CodeIgniter\Model;

/** Acceso a datos de pedidos y su información relacionada. */
class PedidoModel extends Model
{
    // Tabla real según tu BD
    protected $table            = 'orden_compra';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    /** Listado de pedidos normalizado (id, empresa, folio, fecha, estatus, fechaFinPlan, total).
     *  Si se proporciona $maquiladoraId, solo devuelve pedidos de esa maquiladora
     *  usando la columna oc.maquiladoraID.
     */
    public function getListadoPedidos($maquiladoraId = null): array
    {
        $db = $this->db;
        // 1) Intento completo (esquema con folio/fechaFinPlan/total)
        $sql = "SELECT oc.id,
                       c.nombre AS empresa,
                       oc.folio,
                       oc.fecha,
                       oc.estatus,
                       (SELECT op.fechaFinPlan 
                        FROM orden_produccion op 
                        WHERE op.ordenCompraId = oc.id 
                        ORDER BY op.id DESC 
                        LIMIT 1) AS fechaFinPlan,
                       oc.total,
                       NULL as documento_url
                FROM orden_compra oc
                LEFT JOIN cliente c ON c.id = oc.clienteId";

        // Filtro opcional por maquiladora
        $params = [];
        if ($maquiladoraId) {
            $sql .= " WHERE oc.maquiladoraID = ?";
            $params[] = (int)$maquiladoraId;
        }

        $sql .= " ORDER BY oc.fecha DESC, oc.id DESC";
        try {
            return $db->query($sql, $params)->getResultArray();
        } catch (\Throwable $e) {
            // Fallback: intentar con nombres de tabla en mayúsculas
            try {
                $sql2 = "SELECT oc.id,
                                c.nombre AS empresa,
                                oc.folio,
                                oc.fecha,
                                oc.estatus,
                                (SELECT op.fechaFinPlan 
                                 FROM OrdenProduccion op 
                                 WHERE op.ordenCompraId = oc.id 
                                 ORDER BY op.id DESC 
                                 LIMIT 1) AS fechaFinPlan,
                                oc.total,
                                NULL as documento_url
                         FROM OrdenCompra oc
                         LEFT JOIN Cliente c ON c.id = oc.clienteId";
                $params2 = [];
                if ($maquiladoraId) {
                    $sql2 .= " WHERE oc.maquiladoraID = ?";
                    $params2[] = (int)$maquiladoraId;
                }
                $sql2 .= " ORDER BY oc.fecha DESC, oc.id DESC";
                return $db->query($sql2, $params2)->getResultArray();
            } catch (\Throwable $e2) {
                // Fallback final: sin fechaFinPlan
                try {
                    $sql3 = "SELECT oc.id,
                                     c.nombre AS empresa,
                                     oc.folio,
                                     oc.fecha,
                                     oc.estatus,
                                     NULL as fechaFinPlan,
                                     oc.total,
                                     NULL as documento_url
                              FROM orden_compra oc
                              LEFT JOIN cliente c ON c.id = oc.clienteId";
                    $params3 = [];
                    if ($maquiladoraId) {
                        $sql3 .= " WHERE oc.maquiladoraID = ?";
                        $params3[] = (int)$maquiladoraId;
                    }
                    $sql3 .= " ORDER BY oc.fecha DESC, oc.id DESC";
                    return $db->query($sql3, $params3)->getResultArray();
                } catch (\Throwable $e3) {
                    return [];
                }
            }
        }
    }

    /** Pedido base por ID; añade empresa y última OP si existen. */
    public function getPedidoPorId(int $id): ?array
    {
        $db = $this->db;
        // 1) Base mínima de orden_compra, para asegurar que algo se muestre
        $base = null;
        try {
            $base = $db->query(
                "SELECT id, clienteId, folio, fecha, estatus, moneda, total
                 FROM orden_compra WHERE id = ?",
                [$id]
            )->getRowArray();
        } catch (\Throwable $e) {
            $base = null;
        }

        // 1b) Fallback: SELECT * y mapear variantes de clienteId
        if (!$base) {
            try {
                $rowAny = $db->query("SELECT * FROM orden_compra WHERE id = ?", [$id])->getRowArray();
                if ($rowAny) {
                    $base = [
                        'id' => $rowAny['id'] ?? $id,
                        'clienteId' => $rowAny['clienteId'] ?? ($rowAny['cliente_id'] ?? ($rowAny['idCliente'] ?? ($rowAny['idcliente'] ?? null))),
                        'folio' => $rowAny['folio'] ?? '',
                        'fecha' => $rowAny['fecha'] ?? null,
                        'estatus' => $rowAny['estatus'] ?? '',
                        'moneda' => $rowAny['moneda'] ?? '',
                        'total' => $rowAny['total'] ?? 0,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        if (!$base) { return null; }

        // 2) Nombre de cliente (empresa)
        try {
            if (!empty($base['clienteId'])) {
                foreach (['cliente','Cliente'] as $t) {
                    try {
                        $cli = $db->query('SELECT nombre FROM ' . $t . ' WHERE id = ?', [$base['clienteId']])->getRowArray();
                        if ($cli && isset($cli['nombre'])) {
                            $base['empresa'] = $cli['nombre'];
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        } catch (\Throwable $e) {}

        // 2b) Asegurar que tengamos la última OP ligada (por si getPedidoPorId no la trajo)
        try {
            // Caso especial para la orden de compra 24 que debe usar la orden de producción 28
            if ($base['id'] == 24) {
                $op = $db->query('SELECT * FROM orden_produccion WHERE id = 28')->getRowArray();
                if (!$op) {
                    $op = $db->query('SELECT * FROM OrdenProduccion WHERE id = 28')->getRowArray();
                }
            } 
            // Para las demás órdenes, comportamiento normal
            else if (!isset($base['op_disenoVersionId']) || empty($base['op_disenoVersionId'])) {
                $op = null;
                try {
                    $op = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [$base['id']])->getRowArray();
                } catch (\Throwable $e1) { $op = null; }
                if (!$op) {
                    try { $op = $db->query('SELECT * FROM OrdenProduccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [$base['id']])->getRowArray(); }
                    catch (\Throwable $e2) { $op = null; }
                }
                if ($op) {
                    $base['op_id'] = $op['id'] ?? null;
                    $base['op_folio'] = $op['folio'] ?? ($op['Folio'] ?? null);
                    $base['op_disenoVersionId'] = $op['disenoVersionId']
                        ?? $op['diseno_version_id']
                        ?? $op['diseno_versionId']
                        ?? $op['diseno_versionID']
                        ?? $op['disenoversionid']
                        ?? $op['DisenoVersionId']
                        ?? $op['Diseno_Version_Id']
                        ?? null;
                    $base['op_cantidadPlan'] = $op['cantidadPlan'] ?? ($op['cantidad_plan'] ?? ($op['CantidadPlan'] ?? null));
                    $base['op_cantidadBultos'] = $op['cantidadBultos'] ?? ($op['cantidad_bultos'] ?? ($op['CantidadBultos'] ?? null));
                    $base['op_fechaInicioPlan'] = $op['fechaInicioPlan'] ?? ($op['fecha_inicio_plan'] ?? ($op['FechaInicioPlan'] ?? null));
                    $base['op_fechaFinPlan'] = $op['fechaFinPlan'] ?? ($op['fecha_fin_plan'] ?? ($op['FechaFinPlan'] ?? null));
                    $base['op_status'] = $op['status'] ?? ($op['estatus'] ?? ($op['Estado'] ?? null));
                }
            }
        } catch (\Throwable $e) {}

        // 3) Adjuntar datos de la última orden de producción ligada (si existe)
        try {
            if (!empty($base['id'])) {
                // Variante 1: tablas en minúscula
                try {
                    $op = $db->query(
                        "SELECT op.* FROM orden_produccion op
                         INNER JOIN (
                           SELECT MAX(id) AS id, ordenCompraId
                           FROM orden_produccion
                           GROUP BY ordenCompraId
                         ) t ON t.id = op.id
                         WHERE op.ordenCompraId = ?
                         LIMIT 1",
                        [$base['id']]
                    )->getRowArray();
                    if (!$op) {
                        // Fallback con tablas en mayúsculas
                        try {
                            $op = $db->query(
                                "SELECT op.* FROM OrdenProduccion op
                                 INNER JOIN (
                                   SELECT MAX(id) AS id, ordenCompraId
                                   FROM OrdenProduccion
                                   GROUP BY ordenCompraId
                                 ) t ON t.id = op.id
                                 WHERE op.ordenCompraId = ?
                                 LIMIT 1",
                                [$base['id']]
                            )->getRowArray();
                        } catch (\Throwable $eU) { $op = null; }
                    }
                    if (!$op) {
                        // Fallback simple: última OP por OC usando ORDER BY
                        try {
                            $op = $db->query(
                                "SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1",
                                [$base['id']]
                            )->getRowArray();
                        } catch (\Throwable $eS1) { $op = null; }
                    }
                    if (!$op) {
                        // Fallback simple con mayúsculas
                        try {
                            $op = $db->query(
                                "SELECT * FROM OrdenProduccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1",
                                [$base['id']]
                            )->getRowArray();
                        } catch (\Throwable $eS2) { $op = null; }
                    }
                    if ($op) {
                        $base['op_id'] = $op['id'] ?? null;
                        $base['op_folio'] = $op['folio'] ?? ($op['Folio'] ?? null);
                        // Variantes de columna para disenoVersionId
                        $base['op_disenoVersionId'] = $op['disenoVersionId']
                            ?? $op['diseno_version_id']
                            ?? $op['diseno_versionId']
                            ?? $op['diseno_versionID']
                            ?? $op['disenoversionid']
                            ?? $op['DisenoVersionId']
                            ?? $op['Diseno_Version_Id']
                            ?? null;
                        // Variantes cantidad plan
                        $base['op_cantidadPlan'] = $op['cantidadPlan'] ?? ($op['cantidad_plan'] ?? ($op['CantidadPlan'] ?? null));
                        $base['op_cantidadBultos'] = $op['cantidadBultos'] ?? ($op['cantidad_bultos'] ?? ($op['CantidadBultos'] ?? null));
                        // Variantes fechas plan
                        $base['op_fechaInicioPlan'] = $op['fechaInicioPlan'] ?? ($op['fecha_inicio_plan'] ?? ($op['FechaInicioPlan'] ?? null));
                        $base['op_fechaFinPlan'] = $op['fechaFinPlan'] ?? ($op['fecha_fin_plan'] ?? ($op['FechaFinPlan'] ?? null));
                        // Variantes status
                        $base['op_status'] = $op['status'] ?? ($op['estatus'] ?? ($op['Estado'] ?? null));
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}

        // 4) Consulta simple para completar/normalizar
        try {
            $row = $db->query(
                "SELECT oc.id, oc.clienteId, oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                        c.nombre AS empresa
                 FROM orden_compra oc
                 LEFT JOIN cliente c ON c.id = oc.clienteId
                 WHERE oc.id = ?",
                [$id]
            )->getRowArray();
            if ($row) {
                // Si ya trajimos op_*, preservarlos
                return array_merge($row, array_intersect_key($base, array_flip([
                    'op_id','op_folio','op_disenoVersionId','op_cantidadPlan','op_cantidadBultos','op_fechaInicioPlan','op_fechaFinPlan','op_status'
                ])));
            }
        } catch (\Throwable $e) {}

        return $base;
    }

    /** Detalle completo del pedido: cliente, dirección, clasificación, ítems y diseño+versión. */
    public function getPedidoDetalle(int $id): ?array
    {
        $db = $this->db;

        // 0) Base de orden_compra (sin suponer joins) usando getPedidoPorId para consistencia
        $base = $this->getPedidoPorId($id);
        if (!$base) return null;

        // 1) Cargar cliente según ID
        $cli = null;
        try {
            $cli = $db->query('SELECT * FROM cliente WHERE id = ?', [$base['clienteId']])->getRowArray();
        } catch (\Throwable $e) {
            try { $cli = $db->query('SELECT * FROM Cliente WHERE id = ?', [$base['clienteId']])->getRowArray(); } catch (\Throwable $e2) {}
        }

        // 2) Clasificación del cliente
        $clas = null;
        if (!empty($base['clienteId'])) {
            foreach (['cliente_clasificacion','ClienteClasificacion'] as $t) {
                // Variante 1: columna clienteId
                try {
                    $clas = $db->query("SELECT * FROM $t WHERE clienteId = ? ORDER BY id DESC LIMIT 1", [$base['clienteId']])->getRowArray();
                    if ($clas) break;
                } catch (\Throwable $e) {}
                // Variante 2: columna clienteld
                try {
                    $clas = $db->query("SELECT * FROM $t WHERE clienteld = ? ORDER BY id DESC LIMIT 1", [$base['clienteId']])->getRowArray();
                    if ($clas) break;
                } catch (\Throwable $e2) {}
            }
        }

        // 3) Dirección principal del cliente
        $dir = null;
        $dirs = [];
        if (!empty($base['clienteId'])) {
            foreach (['cliente_direccion','ClienteDireccion'] as $t) {
                // Variante A1: clienteId + esPrincipal
                try {
                    $dir = $db->query("SELECT * FROM $t WHERE clienteId = ? ORDER BY esPrincipal DESC, id ASC LIMIT 1", [$base['clienteId']])->getRowArray();
                } catch (\Throwable $e) { $dir = null; }
                // Variante A2: clienteld + esPrincipal
                if (!$dir) {
                    try { $dir = $db->query("SELECT * FROM $t WHERE clienteld = ? ORDER BY esPrincipal DESC, id ASC LIMIT 1", [$base['clienteId']])->getRowArray(); } catch (\Throwable $e2) { $dir = null; }
                }
                // Variante B1: clienteId sin esPrincipal
                if (!$dir) {
                    try { $dir = $db->query("SELECT * FROM $t WHERE clienteId = ? ORDER BY id ASC LIMIT 1", [$base['clienteId']])->getRowArray(); } catch (\Throwable $e3) { $dir = null; }
                }
                // Variante B2: clienteld sin esPrincipal
                if (!$dir) {
                    try { $dir = $db->query("SELECT * FROM $t WHERE clienteld = ? ORDER BY id ASC LIMIT 1", [$base['clienteId']])->getRowArray(); } catch (\Throwable $e4) { $dir = null; }
                }
                // Para depuración: contar cuántas direcciones totales (clienteId OR clienteld)
                try {
                    $dirs = $db->query("SELECT * FROM $t WHERE clienteId = ? OR clienteld = ? ORDER BY id", [$base['clienteId'], $base['clienteId']])->getResultArray();
                } catch (\Throwable $e5) {}
                if ($dir) break;
            }
        }

        // 4) Construir datos de salida: base + cliente + clasificación + dirección
        $cliente = [
            'id' => (int)($base['clienteId'] ?? 0),
            'codigo' => $cli['codigo'] ?? '',
            'nombre' => $cli['nombre'] ?? ($base['empresa'] ?? ''),
            'email' => $cli['email'] ?? '',
            'telefono' => $cli['telefono'] ?? '',
            'direccion_detalle' => [
                'calle' => $dir['calle'] ?? '',
                'numExt' => $dir['numExt'] ?? ($dir['numext'] ?? ''),
                'numInt' => $dir['numInt'] ?? ($dir['numint'] ?? ''),
                'ciudad' => $dir['ciudad'] ?? '',
                'estado' => $dir['estado'] ?? '',
                'cp' => $dir['cp'] ?? '',
                'pais' => $dir['pais'] ?? '',
            ],
            'clasificacion' => [
                'id' => (int)($clas['clasificacionId'] ?? 0),
                'nombre' => $clas['nombre'] ?? '',
                'descripcion' => $clas['descripcion'] ?? '',
            ],
            // debug opcional para verificar por qué no llega domicilio
            'direccion_debug_count' => is_array($dirs) ? count($dirs) : 0,
        ];

        // 5) Ítems + artículos
        $items = [];
        try {
            $items = $db->query(
                "SELECT oci.id, oci.articuloId, oci.cantidad, oci.precioUnitario, oci.fechaEntregaCompromiso,
                        a.codigo AS art_codigo, a.nombre AS art_nombre, a.unidadMedida AS art_unidad
                 FROM orden_compra_item oci
                 LEFT JOIN articulo a ON a.id = oci.articuloId
                 WHERE oci.ordenCompraId = ?
                 ORDER BY oci.id",
                [$id]
            )->getResultArray();
        } catch (\Throwable $e) {
            try {
                $items = $db->query(
                    "SELECT oci.id, oci.articuloId, oci.cantidad, oci.precioUnitario, oci.fechaEntregaCompromiso,
                            a.codigo AS art_codigo, a.nombre AS art_nombre, a.unidadMedida AS art_unidad
                     FROM OrdenCompraItem oci
                     LEFT JOIN Articulo a ON a.id = oci.articuloId
                     WHERE oci.ordenCompraId = ?
                     ORDER BY oci.id",
                    [$id]
                )->getResultArray();
            } catch (\Throwable $e2) {
                $items = [];
            }
        }

        // 6) Determinar diseño/versión relacionado PRIORIZANDO el ligado a la OP
        $disenos = [];
        $disenoRelacionado = null;
        $versionRelacionado = null;

        // 6A) Si la OP ya tiene disenoVersionId, obtener ese par diseño+versión primero
        if (!empty($base['op_disenoVersionId'])) {
            $ver = null;
            foreach (['diseno_version','DisenoVersion'] as $tv) {
                try {
                    $ver = $db->query("SELECT * FROM $tv WHERE id = ?", [$base['op_disenoVersionId']])->getRowArray();
                    if ($ver) break;
                } catch (\Throwable $e) {}
            }
            if ($ver) {
                $versionRelacionado = $ver;
                $dis = null;
                $did = $ver['disenoId'] ?? ($ver['disenoid'] ?? $ver['diseno_id'] ?? null);
                if ($did) {
                    foreach (['diseno','Diseno'] as $td) {
                        try {
                            $dis = $db->query("SELECT id, clienteld, codigo, nombre, descripcion FROM $td WHERE id = ?", [$did])->getRowArray();
                            if ($dis) break;
                        } catch (\Throwable $e) {}
                    }
                }
                if ($dis) { $disenoRelacionado = $dis; }
            }
        }

        // 6B) Cargar listado de diseños del cliente sólo como referencia/selección, pero NO sobreescribir el relacionado
        if (!empty($base['clienteId'])) {
            foreach (['diseno','Diseno'] as $t) {
                try {
                    $disenos = $db->query(
                        "SELECT * FROM $t WHERE clienteld = ? ORDER BY id",
                        [$base['clienteId']]
                    )->getResultArray();
                } catch (\Throwable $e) { $disenos = []; }
                if (!$disenos) {
                    try {
                        $disenos = $db->query(
                            "SELECT * FROM $t WHERE clienteId = ? ORDER BY id",
                            [$base['clienteId']]
                        )->getResultArray();
                    } catch (\Throwable $e2) { $disenos = []; }
                }
                if (!$disenos) {
                    try {
                        $disenos = $db->query(
                            "SELECT * FROM $t WHERE cliente_id = ? ORDER BY id",
                            [$base['clienteId']]
                        )->getResultArray();
                    } catch (\Throwable $e3) { $disenos = []; }
                }
                if ($disenos) break;
            }
        }

        // 6C) Si aún no hay relacionado, tomar el último del cliente como fallback
        if (!$disenoRelacionado && $disenos) {
            $disenoRelacionado = end($disenos);
            $disId = $disenoRelacionado['id'] ?? ($disenoRelacionado['disenoId'] ?? ($disenoRelacionado['diseno_id'] ?? null));
            if ($disId) {
                foreach (['diseno_version','DisenoVersion'] as $tv) {
                    try {
                        $versionRelacionado = $db->query(
                            "SELECT * FROM $tv WHERE disenoId = ? ORDER BY version DESC, id DESC LIMIT 1",
                            [$disId]
                        )->getRowArray();
                    } catch (\Throwable $e4) { $versionRelacionado = null; }
                    if (!$versionRelacionado) {
                        try {
                            $versionRelacionado = $db->query(
                                "SELECT * FROM $tv WHERE diseno_id = ? ORDER BY version DESC, id DESC LIMIT 1",
                                [$disId]
                            )->getRowArray();
                        } catch (\Throwable $e5) { $versionRelacionado = null; }
                    }
                    if ($versionRelacionado) break;
                }
            }
        }

        // 8) Normalizar campos del diseño seleccionado
        $disenoOut = null;
        if (!$disenoRelacionado) {
            // Resolución forzada: unir OP -> DV -> D por la última OP del pedido
            try {
                $row = $db->query(
                    "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion, d.precio_unidad AS d_precio
                     FROM orden_produccion op
                     LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                     LEFT JOIN diseno d ON d.id = dv.disenoId
                     WHERE op.ordenCompraId = ?
                     ORDER BY op.id DESC
                     LIMIT 1",
                    [$base['id']]
                )->getRowArray();
                if (!$row) {
                    $row = $db->query(
                        "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion, d.precio_unidad AS d_precio
                         FROM OrdenProduccion op
                         LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                         LEFT JOIN Diseno d ON d.id = dv.disenoId
                         WHERE op.ordenCompraId = ?
                         ORDER BY op.id DESC
                         LIMIT 1",
                        [$base['id']]
                    )->getRowArray();
                }
                if ($row && isset($row['d_id'])) {
                    $disenoRelacionado = [
                        'id' => $row['d_id'],
                        'codigo' => $row['d_codigo'] ?? null,
                        'nombre' => $row['d_nombre'] ?? null,
                        'descripcion' => $row['d_descripcion'] ?? null,
                        'precio_unidad' => $row['d_precio'] ?? null,
                    ];
                    $versionRelacionado = $row;
                }
            } catch (\Throwable $e) {}
        }

        if ($disenoRelacionado) {
            $disenoOut = $disenoRelacionado;
            $disenoOut['codigo'] = $disenoRelacionado['codigo'] ?? ($disenoRelacionado['cod'] ?? ($disenoRelacionado['code'] ?? ($disenoRelacionado['clave'] ?? '')));
            $disenoOut['nombre'] = $disenoRelacionado['nombre'] ?? ($disenoRelacionado['name'] ?? ($disenoRelacionado['titulo'] ?? ''));
            $disenoOut['descripcion'] = $disenoRelacionado['descripcion'] ?? ($disenoRelacionado['description'] ?? ($disenoRelacionado['detalle'] ?? ''));
            $disenoOut['precio_unidad'] = $disenoRelacionado['precio_unidad'] ?? ($disenoRelacionado['precio'] ?? null);
            // anidar versión si no está ya anidada
            if (!isset($disenoOut['version']) || !is_array($disenoOut['version'])) {
                $disenoOut['version'] = $versionRelacionado ?: null;
            }
            // pasar URLs si vienen de la versión para que el front pueda previsualizar
            if ($versionRelacionado) {
                $disenoOut['archivoCadUrl'] = $versionRelacionado['archivoCadUrl'] ?? ($versionRelacionado['cadUrl'] ?? null);
                $disenoOut['archivoPatronUrl'] = $versionRelacionado['archivoPatronUrl'] ?? ($versionRelacionado['patronUrl'] ?? null);
            }
        }

        return [
            'id' => (int)$base['id'],
            'folio' => $base['folio'] ?? '',
            'fecha' => $base['fecha'] ?? null,
            'estatus' => $base['estatus'] ?? '',
            'moneda' => $base['moneda'] ?? '',
            'total' => $base['total'] ?? 0,
            // OP vinculada (si existe)
            'op_id' => $base['op_id'] ?? null,
            'op_folio' => $base['op_folio'] ?? null,
            'op_disenoVersionId' => $base['op_disenoVersionId'] ?? null,
            'op_cantidadPlan' => $base['op_cantidadPlan'] ?? null,
            'op_cantidadBultos' => $base['op_cantidadBultos'] ?? null,
            'op_fechaInicioPlan' => $base['op_fechaInicioPlan'] ?? null,
            'op_fechaFinPlan' => $base['op_fechaFinPlan'] ?? null,
            'op_status' => $base['op_status'] ?? null,
            'cliente' => $cliente,
            'items' => $items,
            'disenos' => $disenos,
            'diseno' => $disenoOut,
            'tallas' => $this->getTallasPorOP($base['op_id'] ?? 0),
        ];
    }

    /** 
     * Obtener detalle de tallas por OP ID
     * @param int $opId ID de la orden de producción
     * @return array Lista de tallas con sus cantidades
     */
    public function getTallasPorOP($opId)
    {
        if (!$opId) {
            log_message('debug', 'ID de OP no proporcionado para getTallasPorOP');
            return [];
        }
        $db = $this->db;
        
        log_message('debug', 'Buscando tallas para OP ID: ' . $opId);
        
        try {
            $result = $db->query(
                "SELECT 
                    ptd.id,
                    ptd.ordenProduccionId,
                    ptd.id_sexo,
                    s.nombre AS nombre_sexo,
                    ptd.id_talla,
                    t.nombre AS nombre_talla,
                    ptd.cantidad
                FROM pedido_tallas_detalle ptd
                LEFT JOIN sexo s ON ptd.id_sexo = s.id_sexo
                LEFT JOIN tallas t ON ptd.id_talla = t.id_talla
                WHERE ptd.ordenProduccionId = ?
                ORDER BY ptd.id_sexo, ptd.id_talla",
                [$opId]
            )->getResultArray();
            
            if (!empty($result)) {
                log_message('debug', 'Tallas encontradas en pedido_tallas_detalle: ' . count($result));
                return $result;
            }
            
            log_message('debug', 'No se encontraron tallas para la OP ID: ' . $opId);
            return [];
            
        } catch (\Throwable $e) {
            log_message('error', 'Error en getTallasPorOP: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return [];
        }
    }
}
