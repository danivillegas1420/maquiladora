<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Models\PedidoModel;

class OrdenProduccionModel extends Model
{
    protected $table = 'orden_produccion';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ordenCompraId', 'disenoVersionId', 'folio', 'cantidadPlan', 'cantidadBultos', 'fechaInicioPlan', 'fechaFinPlan', 'status'];

    public function getListado($maquiladoraId = null)
    {
        $builder = $this->db->table($this->table . ' op');
        $builder->select('
            op.id              AS opId,
            op.folio           AS op,
            op.cantidadPlan    AS cantidadPlan,
            op.fechaInicioPlan AS ini,
            op.fechaFinPlan    AS fin,
            op.status          AS estatus,
            op.maquiladoraID,
            op.maquiladoraCompartidaID,
            d.nombre           AS diseno,
            c.nombre           AS cliente
        ');
        $builder->join('diseno_version dv', 'dv.id = op.disenoVersionId', 'left');
        $builder->join('diseno d', 'd.id = dv.disenoId', 'left');
        $builder->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left');
        $builder->join('cliente c', 'c.id = oc.clienteId', 'left');

        if ($maquiladoraId) {
            $builder->groupStart()
                ->where('op.maquiladoraID', (int) $maquiladoraId)
                ->orWhere('op.maquiladoraCompartidaID', (int) $maquiladoraId)
                ->groupEnd();
        }

        $builder->orderBy('op.fechaInicioPlan', 'DESC');

        $rows = $builder->get()->getResultArray();

        // Completa columnas que la vista espera
        foreach ($rows as &$r) {
            $r['cliente'] = $r['cliente'] ?? 'N/D';
        }
        return $rows;
    }

    /**
     * Lista de órdenes de producción que aún NO tienen control de bultos.
     */
    public function getListadoSinControl($maquiladoraId = null)
    {
        $builder = $this->db->table($this->table . ' op');
        $builder->select('
            op.id              AS opId,
            op.folio           AS op,
            op.cantidadPlan    AS cantidadPlan,
            op.fechaInicioPlan AS ini,
            op.fechaFinPlan    AS fin,
            op.status          AS estatus,
            op.maquiladoraID,
            op.maquiladoraCompartidaID,
            d.nombre           AS diseno,
            c.nombre           AS cliente
        ');
        $builder->join('diseno_version dv', 'dv.id = op.disenoVersionId', 'left');
        $builder->join('diseno d', 'd.id = dv.disenoId', 'left');
        $builder->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left');
        $builder->join('cliente c', 'c.id = oc.clienteId', 'left');

        // Excluir órdenes que ya tienen al menos un control de bultos
        $builder->join('control_bultos cb', 'cb.ordenProduccionId = op.id', 'left');
        $builder->where('cb.id IS NULL');

        if ($maquiladoraId) {
            $builder->groupStart()
                ->where('op.maquiladoraID', (int) $maquiladoraId)
                ->orWhere('op.maquiladoraCompartidaID', (int) $maquiladoraId)
                ->groupEnd();
        }

        $builder->orderBy('op.fechaInicioPlan', 'DESC');

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$r) {
            $r['cliente'] = $r['cliente'] ?? 'N/D';
        }

        return $rows;
    }

    /**
     * Actualiza el estatus de una orden de producción.
     */
    public function updateEstatus(int $id, string $estatus): bool
    {
        if ($id <= 0 || $estatus === '')
            return false;
        return (bool) $this->update($id, ['status' => $estatus]);
    }

    /**
     * Obtiene el detalle completo de una orden de producción.
     */
    public function getDetalle(int $id): ?array
    {
        if ($id <= 0)
            return null;

        $builder = $this->db->table($this->table . ' op');
        $builder->select('
            op.id,
            op.ordenCompraId,
            op.disenoVersionId,
            op.folio,
            op.cantidadPlan,
            op.cantidadBultos,
            op.fechaInicioPlan,
            op.fechaFinPlan,
            op.status,
            d.codigo   AS disenoCodigo,
            d.nombre   AS disenoNombre,
            d.descripcion AS disenoDescripcion,
            d.precio_unidad AS disenoPrecio,
            dv.version AS disenoVersion,
            dv.fecha   AS disenoFecha,
            dv.notas   AS disenoNotas,
            dv.aprobado AS disenoAprobado,
            dv.foto     AS disenoFoto,
            dv.patron   AS disenoPatron,
            c.nombre    AS clienteNombre,
            oc.total    AS pedidoTotal
        ');
        $builder->join('diseno_version dv', 'dv.id = op.disenoVersionId', 'left');
        $builder->join('diseno d', 'd.id = dv.disenoId', 'left');
        $builder->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left');
        $builder->join('cliente c', 'c.id = oc.clienteId', 'left');
        $builder->where('op.id', $id);

        $row = $builder->get()->getRowArray();

        if (!$row)
            return null;

        $fmt = function ($v) {
            return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };

        // Convertir BLOBs a base64
        $fotoBase64 = null;
        if (!empty($row['disenoFoto'])) {
            $fotoBase64 = 'data:image/jpeg;base64,' . base64_encode($row['disenoFoto']);
        }

        $patronBase64 = null;
        if (!empty($row['disenoPatron'])) {
            $patronBase64 = 'data:image/jpeg;base64,' . base64_encode($row['disenoPatron']);
        }

        // Obtener tallas del pedido ligadas a esta OP (si existen)
        $tallas = [];
        try {
            $pedidoModel = new PedidoModel();
            $tallas = $pedidoModel->getTallasPorOP((int) $row['id']);
        } catch (\Throwable $e) {
            // Silencioso: si falla, simplemente no devolvemos tallas
            $tallas = [];
        }

        return [
            'id' => (int) $row['id'],
            'ordenCompraId' => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio' => $row['folio'] ?? '',
            'cantidadPlan' => isset($row['cantidadPlan']) ? (int) $row['cantidadPlan'] : null,
            'cantidadBultos' => isset($row['cantidadBultos']) ? (int) $row['cantidadBultos'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan' => $fmt($row['fechaFinPlan'] ?? null),
            'status' => $row['status'] ?? '',
            'cliente' => $row['clienteNombre'] ?? '',
            'total' => $row['pedidoTotal'] ?? null,
            'tallas' => $tallas,

            'diseno' => [
                'codigo' => $row['disenoCodigo'] ?? '',
                'nombre' => $row['disenoNombre'] ?? '',
                'descripcion' => $row['disenoDescripcion'] ?? '',
                'precio_unidad' => $row['disenoPrecio'] ?? null,
                'version' => $row['disenoVersion'] ?? '',
                'fecha' => $fmt($row['disenoFecha'] ?? null),
                'notas' => $row['disenoNotas'] ?? '',
                'aprobado' => isset($row['disenoAprobado']) ? (int) $row['disenoAprobado'] : null,
                'archivoCadUrl' => $fotoBase64, // Usamos foto como archivoCadUrl para compatibilidad con frontend
                'archivoPatronUrl' => $patronBase64,
                'archivos' => [] // Array vacío para compatibilidad
            ],
        ];
    }

    /**
     * Detalle básico solo desde orden_produccion (sin joins), para poblar el modal inicialmente.
     */
    public function getDetalleBasico(int $id): ?array
    {
        if ($id <= 0)
            return null;

        $row = $this->select('id, ordenCompraId, disenoVersionId, folio, cantidadPlan, cantidadBultos, fechaInicioPlan, fechaFinPlan, status')
            ->where('id', $id)
            ->first();

        if (!$row)
            return null;
        $fmt = function ($v) {
            return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };
        return [
            'id' => (int) $row['id'],
            'ordenCompraId' => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio' => $row['folio'] ?? '',
            'cantidadPlan' => isset($row['cantidadPlan']) ? (int) $row['cantidadPlan'] : null,
            'cantidadBultos' => isset($row['cantidadBultos']) ? (int) $row['cantidadBultos'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan' => $fmt($row['fechaFinPlan'] ?? null),
            'status' => $row['status'] ?? '',
        ];
    }

    /**
     * Detalle básico por folio (sin joins)
     */
    public function getDetalleBasicoPorFolio(string $folio): ?array
    {
        $folio = trim($folio);
        if ($folio === '')
            return null;

        $row = $this->select('id, ordenCompraId, disenoVersionId, folio, cantidadPlan, cantidadBultos, fechaInicioPlan, fechaFinPlan, status')
            ->where('folio', $folio)
            ->first();

        if (!$row)
            return null;

        $fmt = function ($v) {
            return $v ? date('Y-m-d H:i:s', strtotime($v)) : '';
        };

        return [
            'id' => (int) $row['id'],
            'ordenCompraId' => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio' => $row['folio'] ?? '',
            'cantidadPlan' => isset($row['cantidadPlan']) ? (int) $row['cantidadPlan'] : null,
            'cantidadBultos' => isset($row['cantidadBultos']) ? (int) $row['cantidadBultos'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan' => $fmt($row['fechaFinPlan'] ?? null),
            'status' => $row['status'] ?? '',
        ];
    }
}