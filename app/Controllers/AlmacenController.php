<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarioModel;

class AlmacenController extends BaseController
{
    protected InventarioModel $inv;

    public function __construct()
    {
        $this->inv = new InventarioModel();
    }

    /* ===== VISTA ===== */
    public function inventario()
    {
        if (!can('menu.inventario_almacen')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $maquiladoraId = session()->get('maquiladora_id');
        $data = [
            'title' => 'Inventario de Almacenes',
            'almacenes' => $this->inv->obtenerAlmacenesActivos($maquiladoraId ? (int) $maquiladoraId : null),
        ];
        return view('modulos/almacen_inventario', $data);
    }

    /* ===== CATÁLOGOS ===== */
    public function apiAlmacenes()
    {
        $maquiladoraId = session()->get('maquiladora_id');
        return $this->response->setJSON([
            'data' => $this->inv->obtenerAlmacenesActivos($maquiladoraId ? (int) $maquiladoraId : null)
        ]);
    }

    public function apiUbicaciones()
    {
        $almacenId = (int) ($this->request->getGet('almacenId') ?? 0);
        $maquiladoraId = session()->get('maquiladora_id');
        return $this->response->setJSON([
            'data' => $this->inv->obtenerUbicacionesActivas(
                $almacenId ?: null,
                $maquiladoraId ? (int) $maquiladoraId : null
            )
        ]);
    }

    public function apiCrearUbicacion()
    {
        $in = $this->request->getJSON(true) ?: $this->request->getPost();

        $almacenId = (int) ($in['almacenId'] ?? 0);
        $pasillo = trim((string) ($in['pasillo'] ?? ''));
        $estante = trim((string) ($in['estante'] ?? ''));
        $nivel = trim((string) ($in['nivel'] ?? ''));
        $letra = trim((string) ($in['letra'] ?? ''));
        $descripcion = trim((string) ($in['descripcion'] ?? ''));
        $codigoManual = trim((string) ($in['codigo'] ?? ''));

        if (!$almacenId) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Debe seleccionar un almacén'
            ]);
        }

        $db = \Config\Database::connect();

        // Generar código automático si no se proporciona
        if ($codigoManual === '') {
            $parts = [];
            if ($pasillo !== '')
                $parts[] = 'P' . $pasillo;
            if ($estante !== '')
                $parts[] = 'E' . $estante;
            if ($nivel !== '')
                $parts[] = 'N' . $nivel;
            if ($letra !== '')
                $parts[] = $letra;

            $codigo = !empty($parts) ? implode('-', $parts) : 'UB-' . time();
        } else {
            $codigo = $codigoManual;
        }

        // Verificar que el código no exista en ese almacén
        $existe = $db->table('ubicacion')
            ->where('almacenId', $almacenId)
            ->where('codigo', $codigo)
            ->countAllResults();

        if ($existe > 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'ok' => false,
                'message' => 'Ya existe una ubicación con ese código en este almacén'
            ]);
        }

        // Insertar nueva ubicación
        $data = [
            'almacenId' => $almacenId,
            'codigo' => $codigo,
            'pasillo' => $pasillo ?: null,
            'estante' => $estante ?: null,
            'nivel' => $nivel ?: null,
            'letra' => $letra ?: null,
            'descripcion' => $descripcion ?: null,
            'activo' => 1
        ];

        try {
            $db->table('ubicacion')->insert($data);
            $ubicacionId = $db->insertID();

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Ubicación creada exitosamente',
                'data' => [
                    'id' => $ubicacionId,
                    'codigo' => $codigo
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error al crear la ubicación: ' . $e->getMessage()
            ]);
        }
    }

    public function apiCrearAlmacen()
    {
        $in = $this->request->getJSON(true) ?: $this->request->getPost();

        $codigo = trim((string) ($in['codigo'] ?? ''));
        $nombre = trim((string) ($in['nombre'] ?? ''));

        if ($codigo === '' || $nombre === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Código y nombre son obligatorios'
            ]);
        }

        $db = \Config\Database::connect();

        // Verificar que el código no exista
        $existe = $db->table('almacen')
            ->where('codigo', $codigo)
            ->countAllResults();

        if ($existe > 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'ok' => false,
                'message' => 'Ya existe un almacén con ese código'
            ]);
        }

        // Insertar nuevo almacén
        $data = [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'activo' => 1
        ];

        // Si la tabla tiene maquiladoraID, asociarlo
        try {
            $maquiladoraId = session()->get('maquiladora_id');
            if ($maquiladoraId && $this->inv->tableHas('almacen', 'maquiladoraID')) {
                $data['maquiladoraID'] = (int) $maquiladoraId;
            }
        } catch (\Throwable $e) {
        }

        try {
            $db->table('almacen')->insert($data);
            $almacenId = $db->insertID();

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Almacén creado exitosamente',
                'data' => [
                    'id' => $almacenId,
                    'codigo' => $codigo,
                    'nombre' => $nombre
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error al crear el almacén: ' . $e->getMessage()
            ]);
        }
    }

    /* ===== INVENTARIO ===== */
    public function apiInventario()
    {
        $almacenId = $this->request->getGet('almacenId');
        $maquiladoraId = session()->get('maquiladora_id');
        return $this->response->setJSON([
            'data' => $this->inv->obtenerInventario(
                $almacenId ? (int) $almacenId : null,
                $maquiladoraId ? (int) $maquiladoraId : null
            )
        ]);
    }

    public function apiLotes()
    {
        $articuloId = (int) ($this->request->getGet('articuloId') ?? 0);
        $almacenId = $this->request->getGet('almacenId');
        $ubicacionId = $this->request->getGet('ubicacionId');
        if (!$articuloId)
            return $this->response->setJSON(['data' => []]);

        $maquiladoraId = session()->get('maquiladora_id');
        $rows = $this->inv->obtenerLotesArticulo(
            $articuloId,
            $almacenId !== null && $almacenId !== '' ? (int) $almacenId : null,
            $ubicacionId !== null && $ubicacionId !== '' ? (int) $ubicacionId : null,
            $maquiladoraId ? (int) $maquiladoraId : null
        );
        return $this->response->setJSON(['data' => $rows]);
    }

    public function apiMovimientos($articuloId)
    {
        $loteId = $this->request->getGet('loteId');
        $ubicacionId = $this->request->getGet('ubicacionId');
        $data = $this->inv->obtenerMovimientos((int) $articuloId, $loteId ? (int) $loteId : null, $ubicacionId ? (int) $ubicacionId : null);
        return $this->response->setJSON(['data' => $data]);
    }

    /* ===== EDITAR ===== */
    public function apiEditar()
    {
        $in = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($in['stockId']) || empty($in['articuloId'])) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Faltan IDs']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Artículo
        $art = [];
        foreach (['articuloNombre' => 'nombre', 'unidadMedida' => 'unidadMedida', 'stockMin' => 'stockMin', 'stockMax' => 'stockMax'] as $k => $col) {
            if (array_key_exists($k, $in))
                $art[$col] = $in[$k];
        }
        if ($art)
            $db->table('articulo')->update($art, ['id' => (int) $in['articuloId']]);

        // Lote
        $loteId = !empty($in['loteId']) ? (int) $in['loteId'] : null;
        $loteData = [];
        foreach (['loteCodigo' => 'codigo', 'fechaFabricacion' => 'fechaFabricacion', 'fechaCaducidad' => 'fechaCaducidad', 'loteNotas' => 'notas'] as $k => $col) {
            if (array_key_exists($k, $in))
                $loteData[$col] = $in[$k] ?: null;
        }
        if ($loteId) {
            if ($loteData)
                $db->table('lote')->update($loteData, ['id' => $loteId]);
        } elseif (!empty($loteData)) {
            $loteData['articuloId'] = (int) $in['articuloId'];
            $db->table('lote')->insert($loteData);
            $loteId = (int) $db->insertID();
        }

        // Stock
        $stockSet = [];
        if (array_key_exists('cantidad', $in))
            $stockSet['cantidad'] = $in['cantidad'];
        if (array_key_exists('ubicacionId', $in) && $in['ubicacionId'] !== '')
            $stockSet['ubicacionId'] = (int) $in['ubicacionId'];
        if ($loteId)
            $stockSet['loteId'] = $loteId;
        if ($stockSet)
            $db->table('stock')->update($stockSet, ['id' => (int) $in['stockId']]);

        $db->transComplete();
        if (!$db->transStatus())
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'No se pudo guardar']);
        return $this->response->setJSON(['ok' => true, 'message' => 'Guardado', 'loteId' => $loteId]);
    }

    /* ===== AGREGAR / UPSERT ===== */
    public function apiAgregar()
    {
        $in = $this->request->getJSON(true) ?: $this->request->getPost();

        $ubicacionId = (int) ($in['ubicacionId'] ?? 0);
        $cantidad = isset($in['cantidad']) ? (float) $in['cantidad'] : null;
        $operacion = in_array(($in['operacion'] ?? 'sumar'), ['sumar', 'restar', 'reemplazar']) ? $in['operacion'] : 'sumar';

        $articuloId = isset($in['articuloId']) && is_numeric($in['articuloId']) ? (int) $in['articuloId'] : null;
        $sku = trim((string) ($in['sku'] ?? ''));
        $articuloTexto = trim((string) ($in['articuloTexto'] ?? $in['articulo'] ?? ''));
        $unidadMedida = trim((string) ($in['unidadMedida'] ?? ''));
        $stockMin = $in['stockMin'] ?? null;
        $stockMax = $in['stockMax'] ?? null;
        $autoCrear = (bool) ($in['autoCrear'] ?? true);

        if (!$ubicacionId || $cantidad === null) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'ubicacionId y cantidad son obligatorios']);
        }

        $db = \Config\Database::connect();

        // ===== BLOQUEO DE DUPLICADOS EN MODO CREAR (evita sumar por nombre repetido) =====
        if ($autoCrear && !$articuloId && $sku === '' && $articuloTexto !== '') {
            $dup = $db->table('articulo')->select('id, sku, nombre')
                ->where('activo', 1)
                ->groupStart()
                ->where('nombre', $articuloTexto)
                ->orWhere('sku', $articuloTexto)
                ->groupEnd()
                ->get()->getRowArray();

            if ($dup) {
                return $this->response
                    ->setStatusCode(409) // Conflict
                    ->setJSON([
                        'ok' => false,
                        'code' => 'duplicate',
                        'message' => 'Artículo ya en existencia',
                        'articuloId' => (int) $dup['id'],
                        'sku' => $dup['sku'],
                        'nombre' => $dup['nombre'],
                    ]);
            }
        }

        $db->transStart();

        // 1) Resolver o crear artículo
        $resArt = $this->inv->resolverOCrearArticulo($articuloId, $sku, $articuloTexto, $unidadMedida ?: null, $stockMin, $stockMax, $autoCrear);
        if (!$resArt['ok']) {
            $db->transComplete();
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $resArt['message'] ?? 'No se pudo resolver el artículo']);
        }
        $articuloId = (int) $resArt['articuloId'];

        // si mandan UM/min/max, actualizar
        $upd = [];
        if ($unidadMedida !== '')
            $upd['unidadMedida'] = $unidadMedida;
        if ($stockMin !== null)
            $upd['stockMin'] = $stockMin === '' ? null : (float) $stockMin;
        if ($stockMax !== null)
            $upd['stockMax'] = $stockMax === '' ? null : (float) $stockMax;
        if ($upd)
            $db->table('articulo')->update($upd, ['id' => $articuloId]);

        // 2) Lote: NO crear si viene vacío
        $codigo = trim((string) ($in['loteCodigo'] ?? ''));
        $fab = ($in['fechaFabricacion'] ?? null) ?: null;
        $cad = ($in['fechaCaducidad'] ?? null) ?: null;
        $notas = ($in['loteNotas'] ?? null);

        $hayLote = ($codigo !== '') || $fab || $cad || ($notas !== null && $notas !== '');
        $loteId = $hayLote ? $this->inv->findOrCreateLote($articuloId, $codigo, $fab, $cad, $notas) : null;

        // 3) Upsert de stock (sumar/restar/reemplazar sobre el existente)
        $resStock = $this->inv->upsertStock($articuloId, $ubicacionId, $loteId, (float) $cantidad, $operacion);
        if (!$resStock['ok']) {
            $db->transComplete();
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $resStock['message'] ?? 'No se pudo actualizar el stock']);
        }

        $db->transComplete();
        if (!$db->transStatus())
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'No se pudo guardar']);

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Guardado',
            'articuloId' => $articuloId,
            'stockId' => $resStock['stockId'],
            'cantidad' => $resStock['cantidad']
        ]);
    }

    /* ===== ELIMINAR ===== */
    public function apiEliminar($stockId)
    {
        $id = (int) $stockId;
        if (!$id)
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);

        try {
            \Config\Database::connect()->table('stock')->delete(['id' => $id]);
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'No se pudo eliminar']);
        }
    }

    /* ===== NUEVO: verificar existencia por (artículo + ubicación + lote) ===== */
    public function apiExiste()
    {
        $db = \Config\Database::connect();

        $ubicacionId = (int) ($this->request->getGet('ubicacionId') ?? 0);
        $articuloId = $this->request->getGet('articuloId');
        $sku = trim((string) ($this->request->getGet('sku') ?? ''));
        $loteCodigo = trim((string) ($this->request->getGet('loteCodigo') ?? ''));

        if (!$ubicacionId)
            return $this->response->setJSON(['exists' => false]);

        // Resolver artículo
        $artId = null;
        if ($articuloId) {
            $artId = (int) $articuloId;
        } elseif ($sku !== '') {
            $art = $db->table('articulo')->where('sku', $sku)->get()->getRowArray();
            if ($art)
                $artId = (int) $art['id'];
        }
        if (!$artId)
            return $this->response->setJSON(['exists' => false]);

        // Resolver lote (si viene código)
        $loteId = null;
        if ($loteCodigo !== '') {
            $l = $db->table('lote')->where(['articuloId' => $artId, 'codigo' => $loteCodigo])->get()->getRowArray();
            if ($l)
                $loteId = (int) $l['id'];
            else
                return $this->response->setJSON(['exists' => false]);
        }

        // Buscar stock exacto (nota: IS NULL cuando corresponde)
        $b = $db->table('stock')
            ->select('stock.id as id, stock.cantidad, a.unidadMedida')
            ->join('articulo a', 'a.id=stock.articuloId', 'left')
            ->where('stock.articuloId', $artId)
            ->where('stock.ubicacionId', $ubicacionId);

        if ($loteId === null) {
            $b->where('stock.loteId IS NULL', null, false);
        } else {
            $b->where('stock.loteId', $loteId);
        }

        $row = $b->get()->getRowArray();
        return $this->response->setJSON([
            'exists' => (bool) $row,
            'data' => $row ? [
                'id' => (int) $row['id'],
                'cantidad' => (float) $row['cantidad'],
                'unidadMedida' => $row['unidadMedida'] ?? ''
            ] : null
        ]);
    }

    /* ===== NUEVO: búsqueda por id/sku/nombre con existencias totales ===== */
    public function apiBuscarArticulos()
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        if ($q === '')
            return $this->response->setJSON(['data' => []]);

        $db = \Config\Database::connect();
        $b = $db->table('articulo a')
            ->select('a.id, a.sku, a.nombre, a.unidadMedida, a.stockMin, a.stockMax, COALESCE(SUM(s.cantidad),0) AS existencias', false)
            ->join('stock s', 's.articuloId=a.id', 'left')
            ->groupBy('a.id')
            ->limit(10);

        $b->groupStart();
        if (ctype_digit($q))
            $b->orWhere('a.id', (int) $q);
        $b->orLike('a.sku', $q)
            ->orLike('a.nombre', $q);
        $b->groupEnd();

        $rows = $b->get()->getResultArray();
        return $this->response->setJSON(['data' => $rows]);
    }

    /* ===== NUEVO: detalle de artículo por id o sku ===== */
    public function apiArticuloDetalle()
    {
        $id = $this->request->getGet('id');
        $sku = trim((string) ($this->request->getGet('sku') ?? ''));

        if (!$id && $sku === '')
            return $this->response->setJSON(['data' => null]);

        $db = \Config\Database::connect();
        $b = $db->table('articulo a')
            ->select('a.id, a.sku, a.nombre, a.unidadMedida, a.stockMin, a.stockMax, COALESCE(SUM(s.cantidad),0) AS existencias', false)
            ->join('stock s', 's.articuloId=a.id', 'left')
            ->groupBy('a.id')
            ->limit(1);

        if ($id)
            $b->where('a.id', (int) $id);
        if ($sku !== '')
            $b->where('a.sku', $sku);

        $row = $b->get()->getRowArray();
        return $this->response->setJSON(['data' => $row]);
    }

    /* ===== NUEVO: KPIs y Gráficas ===== */
    public function apiKpis()
    {
        $maquiladoraId = session()->get('maquiladora_id');
        $db = \Config\Database::connect();

        // Helper to check if table has column
        $hasAlmacenMaq = false;
        $hasArticuloMaq = false;
        try {
            foreach ($db->getFieldData('almacen') as $f) {
                if ($f->name === 'maquiladoraID') {
                    $hasAlmacenMaq = true;
                    break;
                }
            }
            foreach ($db->getFieldData('articulo') as $f) {
                if ($f->name === 'maquiladoraID') {
                    $hasArticuloMaq = true;
                    break;
                }
            }
        } catch (\Throwable $e) {
        }

        // 1. Total Productos (SKUs únicos con stock > 0)
        $qTotal = $db->table('stock s')
            ->select('COUNT(DISTINCT s.articuloId) as total')
            ->where('s.cantidad >', 0);

        if ($maquiladoraId && ($hasAlmacenMaq || $hasArticuloMaq)) {
            $qTotal->join('ubicacion u', 'u.id = s.ubicacionId', 'left')
                ->join('almacen al', 'al.id = u.almacenId', 'left')
                ->join('articulo a', 'a.id = s.articuloId', 'left');

            $qTotal->groupStart();
            if ($hasAlmacenMaq) {
                $qTotal->where('al.maquiladoraID', (int) $maquiladoraId);
            }
            if ($hasArticuloMaq) {
                $qTotal->orWhere('a.maquiladoraID', (int) $maquiladoraId);
            }
            $qTotal->groupEnd();
        }

        $totalProductos = $qTotal->get()->getRow()->total;

        // 2. Stock Bajo (Items donde cantidad < stockMin)
        $qBajo = $db->table('stock s')
            ->join('articulo a', 'a.id = s.articuloId')
            ->where('s.cantidad < a.stockMin')
            ->where('a.stockMin IS NOT NULL');

        if ($maquiladoraId && ($hasAlmacenMaq || $hasArticuloMaq)) {
            $qBajo->join('ubicacion u', 'u.id = s.ubicacionId', 'left')
                ->join('almacen al', 'al.id = u.almacenId', 'left');

            $qBajo->groupStart();
            if ($hasAlmacenMaq) {
                $qBajo->where('al.maquiladoraID', (int) $maquiladoraId);
            }
            if ($hasArticuloMaq) {
                $qBajo->orWhere('a.maquiladoraID', (int) $maquiladoraId);
            }
            $qBajo->groupEnd();
        }

        $stockBajo = $qBajo->countAllResults();

        // 3. Por Caducar (< 30 días)
        $qCaducar = $db->table('stock s')
            ->join('lote l', 'l.id = s.loteId')
            ->where('l.fechaCaducidad IS NOT NULL')
            ->where('l.fechaCaducidad <=', date('Y-m-d', strtotime('+30 days')))
            ->where('l.fechaCaducidad >=', date('Y-m-d'))
            ->where('s.cantidad >', 0);

        if ($maquiladoraId && ($hasAlmacenMaq || $hasArticuloMaq)) {
            $qCaducar->join('ubicacion u', 'u.id = s.ubicacionId', 'left')
                ->join('almacen al', 'al.id = u.almacenId', 'left')
                ->join('articulo a', 'a.id = s.articuloId', 'left');

            $qCaducar->groupStart();
            if ($hasAlmacenMaq) {
                $qCaducar->where('al.maquiladoraID', (int) $maquiladoraId);
            }
            if ($hasArticuloMaq) {
                $qCaducar->orWhere('a.maquiladoraID', (int) $maquiladoraId);
            }
            $qCaducar->groupEnd();
        }

        $porCaducar = $qCaducar->countAllResults();

        // 4. Distribución por Almacén
        $qDist = $db->table('stock s')
            ->select('al.nombre as almacen, COUNT(*) as items, SUM(s.cantidad) as total_stock')
            ->join('ubicacion u', 'u.id = s.ubicacionId')
            ->join('almacen al', 'al.id = u.almacenId')
            ->where('s.cantidad >', 0);

        if ($maquiladoraId && $hasAlmacenMaq) {
            $qDist->where('al.maquiladoraID', (int) $maquiladoraId);
        }

        $distribucion = $qDist->groupBy('al.id')->get()->getResultArray();

        return $this->response->setJSON([
            'kpis' => [
                'total_productos' => $totalProductos,
                'stock_bajo' => $stockBajo,
                'por_caducar' => $porCaducar
            ],
            'chart' => $distribucion
        ]);
    }

    /* ===== NUEVO: resumen de existencias por artículo ===== */
    public function apiResumenArticulo($articuloId)
    {
        $id = (int) $articuloId;
        if (!$id)
            return $this->response->setJSON(['data' => []]);

        $db = \Config\Database::connect();
        $rows = $db->table('stock s')
            ->select('al.codigo AS almacenCodigo, u.codigo AS ubicacionCodigo, l.codigo AS loteCodigo, l.fechaFabricacion AS fechaFab, l.fechaCaducidad AS fechaCad, s.cantidad')
            ->join('ubicacion u', 'u.id=s.ubicacionId', 'left')
            ->join('almacen al', 'al.id=u.almacenId', 'left')
            ->join('lote l', 'l.id=s.loteId', 'left')
            ->where('s.articuloId', $id)
            ->orderBy('al.codigo', 'ASC')->orderBy('u.codigo', 'ASC')->orderBy('l.codigo', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }
}
