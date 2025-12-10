<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosModel extends Model
{
    protected $table = 'empleado';
    protected $primaryKey = 'id';
    protected $allowedFields = [];
    protected $returnType = 'array';

    /**
     * Obtener todos los empleados de la maquiladora con su forma de pago
     */
    public function getEmpleadosPorMaquiladora($maquiladoraId = null)
    {
        $db = \Config\Database::connect();

        try {
            // Si no se proporciona ID, usar el de la sesión
            if (!$maquiladoraId) {
                $maquiladoraId = session()->get('maquiladora_id');
            }

            if (!$maquiladoraId) {
                return [];
            }

            // Consulta principal (snake case)
            $empleados = $db->query(
                'SELECT 
                    e.id,
                    e.noEmpleado,
                    e.nombre,
                    e.apellido,
                    e.Forma_Pago AS Forma_pago,
                    e.puesto,
                    e.email,
                    e.telefono,
                    e.domicilio,
                    e.fecha_nac,
                    e.curp,
                    e.activo,
                    CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                    u.username,
                    u.correo AS correo_usuario,
                    u.active AS usuario_activo,
                    m.Nombre_Maquila AS nombre_maquiladora
                 FROM empleado e 
                 INNER JOIN users u ON e.idusuario = u.id
                 LEFT JOIN maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                 WHERE u.maquiladoraIdFK = ? 
                 ORDER BY e.nombre ASC, e.apellido ASC',
                [$maquiladoraId]
            )->getResultArray();

            // Si no hay resultados, intentar con mayúsculas
            if (empty($empleados)) {
                $empleados = $db->query(
                    'SELECT 
                        e.id,
                        e.noEmpleado,
                        e.nombre,
                        e.apellido,
                        e.Forma_Pago AS Forma_pago,
                        e.puesto,
                        e.email,
                        e.telefono,
                        e.domicilio,
                        e.fecha_nac,
                        e.curp,
                        e.activo,
                        CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                        u.username,
                        u.correo AS correo_usuario,
                        u.active AS usuario_activo,
                        m.Nombre_Maquila AS nombre_maquiladora
                     FROM Empleado e 
                     INNER JOIN Users u ON e.idusuario = u.id
                     LEFT JOIN Maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                     WHERE u.maquiladoraIdFK = ? 
                     ORDER BY e.nombre ASC, e.apellido ASC',
                    [$maquiladoraId]
                )->getResultArray();
            }

            // Calcular edad y formatear datos
            foreach ($empleados as &$empleado) {
                // Calcular edad si tiene fecha de nacimiento
                if ($empleado['fecha_nac']) {
                    try {
                        $fechaNac = new \DateTime($empleado['fecha_nac']);
                        $hoy = new \DateTime();
                        $edad = $hoy->diff($fechaNac);
                        $empleado['edad'] = $edad->y;
                    } catch (\Exception $e) {
                        $empleado['edad'] = null;
                    }
                } else {
                    $empleado['edad'] = null;
                }

                // Formatear forma de pago
                $empleado['Forma_pago'] = $empleado['Forma_pago'] ?? 'No registrada';

                // Determinar estatus
                $empleado['estatus_texto'] = ($empleado['activo'] == 1 && $empleado['usuario_activo'] == 1) ? 'Activo' : 'Inactivo';
                $empleado['estatus_clase'] = ($empleado['activo'] == 1 && $empleado['usuario_activo'] == 1) ? 'success' : 'danger';
            }

            return $empleados;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getEmpleadosPorMaquiladora: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un empleado específico por ID
     */
    public function getEmpleadoPorId($empleadoId)
    {
        $db = \Config\Database::connect();

        try {
            $empleado = $db->query(
                'SELECT 
                    e.id,
                    e.noEmpleado,
                    e.nombre,
                    e.apellido,
                    e.Forma_Pago AS Forma_pago,
                    e.puesto,
                    e.email,
                    e.telefono,
                    e.domicilio,
                    e.fecha_nac,
                    e.curp,
                    e.activo,
                    CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                    u.username,
                    u.correo AS correo_usuario,
                    u.active AS usuario_activo,
                    m.Nombre_Maquila AS nombre_maquiladora
                 FROM empleado e 
                 INNER JOIN users u ON e.idusuario = u.id
                 LEFT JOIN maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                 WHERE e.id = ? AND u.maquiladoraIdFK = ? 
                 LIMIT 1',
                [$empleadoId, session()->get('maquiladora_id')]
            )->getRowArray();

            return $empleado;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getEmpleadoPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar forma de pago de un empleado
     */
    public function actualizarFormaPago($empleadoId, $formaPago)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table('empleado');
            $result = $builder->where('id', $empleadoId)->update(['Forma_pago' => $formaPago]);

            return $result;

        } catch (\Throwable $e) {
            log_message('error', 'Error en actualizarFormaPago: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear o actualizar tarifa por forma de pago para una maquiladora
     */
    public function guardarTarifaModoPago($maquiladoraId, $formaPago, $monto)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table('tarifa_modo_pago');

            $existe = $builder
                ->where('maquiladoraID', $maquiladoraId)
                ->where('forma_pago', $formaPago)
                ->get()
                ->getRowArray();

            if ($existe) {
                $result = $builder
                    ->where('maquiladoraID', $maquiladoraId)
                    ->where('forma_pago', $formaPago)
                    ->update(['monto' => $monto]);
            } else {
                $result = $builder->insert([
                    'maquiladoraID' => $maquiladoraId,
                    'forma_pago'    => $formaPago,
                    'monto'         => $monto,
                ]);
            }

            return (bool) $result;

        } catch (\Throwable $e) {
            log_message('error', 'Error en guardarTarifaModoPago: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las tarifas de modo de pago para una maquiladora
     */
    public function getTarifasModoPagoPorMaquiladora($maquiladoraId)
    {
        $db = \Config\Database::connect();

        try {
            return $db->table('tarifa_modo_pago')
                ->select('id, forma_pago, monto, maquiladoraID')
                ->where('maquiladoraID', $maquiladoraId)
                ->orderBy('forma_pago', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Error en getTarifasModoPagoPorMaquiladora: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reporte diario de pagos: horas trabajadas y monto a pagar por empleado y fecha
     */
    public function getPagosDiarios($maquiladoraId, $fechaInicio, $fechaFin)
    {
        $db = \Config\Database::connect();

        try {
            $sql = 'SELECT 
                        e.id AS empleado_id,
                        e.noEmpleado,
                        CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                        e.Forma_Pago AS forma_pago_empleado,
                        tt.fecha,
                        tt.horas_totales,
                        COALESCE(tmp.monto, 0) AS tarifa_base,
                        CASE 
                            WHEN e.Forma_Pago COLLATE utf8mb4_unicode_ci = "Por hora" THEN tt.horas_totales * COALESCE(tmp.monto, 0)
                            WHEN e.Forma_Pago COLLATE utf8mb4_unicode_ci = "Por dia" THEN COALESCE(tmp.monto, 0)
                            ELSE 0
                        END AS pago_dia
                    FROM (
                        SELECT 
                            empleadoId,
                            DATE(inicio) AS fecha,
                            SUM(horas) AS horas_totales
                        FROM tiempo_trabajo
                        WHERE DATE(inicio) BETWEEN ? AND ?
                        GROUP BY empleadoId, DATE(inicio)
                    ) tt
                    JOIN empleado e ON e.id = tt.empleadoId
                    LEFT JOIN tarifa_modo_pago tmp 
                        ON tmp.maquiladoraID = e.maquiladoraID
                        AND tmp.forma_pago COLLATE utf8mb4_unicode_ci = CASE 
                            WHEN e.Forma_Pago COLLATE utf8mb4_unicode_ci = "Por dia" THEN "Por día"
                            ELSE e.Forma_Pago COLLATE utf8mb4_unicode_ci
                        END';

            $params = [$fechaInicio, $fechaFin];

            // Filtro por maquiladora solo si existe en sesión
            if ($maquiladoraId !== null) {
                $sql .= ' WHERE e.maquiladoraID = ?';
                $params[] = $maquiladoraId;
            }

            $sql .= ' AND e.Forma_Pago COLLATE utf8mb4_unicode_ci IN ("Por dia", "Por hora")
                    ORDER BY tt.fecha ASC, nombre_completo ASC';

            log_message('debug', 'getPagosDiarios SQL: ' . $sql);
            log_message('debug', 'getPagosDiarios params: ' . json_encode($params));

            $result = $db->query($sql, $params)->getResultArray();

            log_message('debug', 'getPagosDiarios rows: ' . count($result));

            return $result;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getPagosDiarios: ' . $e->getMessage());
            return [];
        }
    }
}
