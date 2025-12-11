<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgresoBultoOperacionModel extends Model
{
    protected $table = 'progreso_bulto_operacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'bultoId',
        'operacionControlId',
        'completado',
        'cantidad_completada',
        'empleadoId',
        'fecha_completado',
        'observaciones'
    ];

    /**
     * Marcar operación de un bulto como completada
     */
    public function marcarCompletado($bultoId, $operacionControlId, $empleadoId = null, $cantidadCompletada = null)
    {
        $data = [
            'bultoId' => $bultoId,
            'operacionControlId' => $operacionControlId,
            'completado' => 1,
            'empleadoId' => $empleadoId,
            'fecha_completado' => date('Y-m-d H:i:s')
        ];

        if ($cantidadCompletada !== null) {
            $data['cantidad_completada'] = $cantidadCompletada;
        }

        // Buscar si ya existe
        $existing = $this->where([
            'bultoId' => $bultoId,
            'operacionControlId' => $operacionControlId
        ])->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Desmarcar operación de un bulto
     */
    public function desmarcarCompletado($bultoId, $operacionControlId)
    {
        return $this->where([
            'bultoId' => $bultoId,
            'operacionControlId' => $operacionControlId
        ])->set([
                    'completado' => 0,
                    'fecha_completado' => null
                ])->update();
    }

    /**
     * Obtener matriz de progreso para un control
     */
    public function getMatrizProgreso($controlBultoId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                b.id as bultoId,
                b.numero_bulto,
                b.talla,
                b.cantidad,
                oc.id as operacionId,
                oc.nombre_operacion,
                oc.orden as operacion_orden,
                COALESCE(pbo.completado, 0) as completado,
                COALESCE(pbo.cantidad_completada, 0) as cantidad_completada,
                pbo.empleadoId,
                pbo.fecha_completado
            FROM bultos b
            CROSS JOIN operaciones_control oc
            LEFT JOIN progreso_bulto_operacion pbo 
                ON b.id = pbo.bultoId 
                AND oc.id = pbo.operacionControlId
            WHERE b.controlBultoId = ?
                AND oc.controlBultoId = ?
            ORDER BY b.numero_bulto ASC, oc.orden ASC
        ", [$controlBultoId, $controlBultoId]);

        return $query->getResultArray();
    }

    /**
     * Calcular progreso por operación
     */
    public function getProgresoPorOperacion($controlBultoId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                oc.id as operacionId,
                oc.nombre_operacion,
                COUNT(DISTINCT b.id) as total_bultos,
                COUNT(DISTINCT CASE WHEN pbo.completado = 1 THEN b.id END) as bultos_completados,
                ROUND(
                    (COUNT(DISTINCT CASE WHEN pbo.completado = 1 THEN b.id END) * 100.0) / 
                    COUNT(DISTINCT b.id), 
                    2
                ) as porcentaje
            FROM operaciones_control oc
            CROSS JOIN bultos b ON b.controlBultoId = oc.controlBultoId
            LEFT JOIN progreso_bulto_operacion pbo 
                ON b.id = pbo.bultoId 
                AND oc.id = pbo.operacionControlId
            WHERE oc.controlBultoId = ?
            GROUP BY oc.id, oc.nombre_operacion, oc.orden
            ORDER BY oc.orden ASC
        ", [$controlBultoId]);

        return $query->getResultArray();
    }

    /**
     * Verificar si un bulto tiene una operación completada
     */
    public function estaCompletado($bultoId, $operacionControlId)
    {
        $progreso = $this->where([
            'bultoId' => $bultoId,
            'operacionControlId' => $operacionControlId
        ])->first();

        return $progreso && $progreso['completado'] == 1;
    }
}
