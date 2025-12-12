<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistroProduccionModel extends Model
{
    protected $table = 'registros_produccion';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'operacionControlId',
        'empleadoId',
        'cantidad_producida',
        'fecha_registro',
        'hora_inicio',
        'hora_fin',
        'tiempo_empleado',
        'registrado_por',
        'observaciones'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = ''; // Disable updated_at as column does not exist

    /**
     * Registrar producción de un empleado
     */
    public function registrarProduccion($data)
    {
        $this->db->transStart();

        // Calcular tiempo empleado si se proporcionan horas
        $tiempoEmpleado = null;
        if (!empty($data['hora_inicio']) && !empty($data['hora_fin'])) {
            $inicio = strtotime($data['hora_inicio']);
            $fin = strtotime($data['hora_fin']);
            $tiempoEmpleado = round(($fin - $inicio) / 60); // minutos
        }

        // Insertar registro
        $insertData = [
            'operacionControlId' => $data['operacionControlId'],
            'empleadoId' => $data['empleadoId'],
            'cantidad_producida' => $data['cantidad_producida'],
            'fecha_registro' => $data['fecha_registro'] ?? date('Y-m-d'),
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'tiempo_empleado' => $tiempoEmpleado,
            'registrado_por' => $data['registrado_por'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ];

        $registroId = $this->insert($insertData);

        if (!$registroId) {
            $dbError = $this->db->error();
            $this->db->transRollback();
            return ['ok' => false, 'step' => 'insert', 'errors' => $this->errors(), 'db_error' => $dbError];
        }

        // Actualizar piezas completadas en la operación
        $operacionModel = new \App\Models\OperacionControlModel();
        try {
            $res = $operacionModel->incrementarPiezas(
                $data['operacionControlId'],
                $data['cantidad_producida']
            );
            if ($res === false) {
                $this->db->transRollback();
                return ['ok' => false, 'step' => 'incrementarPiezas', 'message' => 'Fallo al incrementar piezas'];
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['ok' => false, 'step' => 'incrementarPiezas_exception', 'message' => $e->getMessage()];
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $dbError = $this->db->error();
            return ['ok' => false, 'step' => 'transaction_commit', 'db_error' => $dbError];
        }

        return ['ok' => true, 'id' => $registroId];
    }

    /**
     * Obtener registros de una operación
     */
    public function getRegistrosPorOperacion($operacionId)
    {
        return $this->db->table($this->table . ' rp')
            ->select('rp.*, e.nombre as empleadoNombre, e.apellido as empleadoApellido, 
                      u.nombre as registradoPorNombre')
            ->join('empleado e', 'e.id = rp.empleadoId', 'left')
            ->join('usuario u', 'u.id = rp.registrado_por', 'left')
            ->where('rp.operacionControlId', $operacionId)
            ->orderBy('rp.fecha_registro', 'DESC')
            ->orderBy('rp.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getResumenPorOperacion($operacionId)
    {
        return $this->db->table($this->table . ' rp')
            ->select('rp.empleadoId, e.nombre as empleadoNombre, e.apellido as empleadoApellido, SUM(rp.cantidad_producida) as total_cantidad')
            ->join('empleado e', 'e.id = rp.empleadoId', 'left')
            ->where('rp.operacionControlId', $operacionId)
            ->groupBy('rp.empleadoId, e.nombre, e.apellido')
            ->orderBy('total_cantidad', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener registros de un control de bultos
     */
    public function getRegistrosPorControl($controlId)
    {
        return $this->db->table($this->table . ' rp')
            ->select('rp.*, e.nombre as empleadoNombre, e.apellido as empleadoApellido,
                      oc.nombre_operacion, u.nombre as registradoPorNombre')
            ->join('operaciones_control oc', 'oc.id = rp.operacionControlId', 'left')
            ->join('empleado e', 'e.id = rp.empleadoId', 'left')
            ->join('usuario u', 'u.id = rp.registrado_por', 'left')
            ->where('oc.controlBultoId', $controlId)
            ->orderBy('rp.fecha_registro', 'DESC')
            ->orderBy('rp.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener producción diaria de un empleado
     */
    public function getProduccionPorEmpleado($empleadoId, $fecha = null)
    {
        $fecha = $fecha ?? date('Y-m-d');

        return $this->db->table($this->table . ' rp')
            ->select('rp.*, oc.nombre_operacion, oc.controlBultoId')
            ->join('operaciones_control oc', 'oc.id = rp.operacionControlId', 'left')
            ->where('rp.empleadoId', $empleadoId)
            ->where('rp.fecha_registro', $fecha)
            ->orderBy('rp.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener estadísticas de producción por empleado
     */
    public function getEstadisticasEmpleado($empleadoId, $fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->db->table($this->table . ' rp')
            ->select('SUM(rp.cantidad_producida) as total_piezas,
                      COUNT(DISTINCT rp.fecha_registro) as dias_trabajados,
                      AVG(rp.tiempo_empleado) as tiempo_promedio,
                      COUNT(rp.id) as total_registros')
            ->where('rp.empleadoId', $empleadoId);

        if ($fechaInicio) {
            $builder->where('rp.fecha_registro >=', $fechaInicio);
        }
        if ($fechaFin) {
            $builder->where('rp.fecha_registro <=', $fechaFin);
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Obtener top empleados por producción
     */
    public function getTopEmpleados($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->db->table($this->table . ' rp')
            ->select('e.id, e.nombre, e.apellido, 
                      SUM(rp.cantidad_producida) as total_piezas,
                      COUNT(rp.id) as total_registros')
            ->join('empleado e', 'e.id = rp.empleadoId', 'left')
            ->groupBy('e.id, e.nombre, e.apellido')
            ->orderBy('total_piezas', 'DESC')
            ->limit($limite);

        if ($fechaInicio) {
            $builder->where('rp.fecha_registro >=', $fechaInicio);
        }
        if ($fechaFin) {
            $builder->where('rp.fecha_registro <=', $fechaFin);
        }

        return $builder->get()->getResultArray();
    }
}
