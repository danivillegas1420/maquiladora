<?php

namespace App\Models;

use CodeIgniter\Model;

class ControlBultosModel extends Model
{
    protected $table = 'control_bultos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'idmaquiladora',
        'ordenProduccionId',
        'inspeccionId',
        'estilo',
        'orden',
        'cantidad_total',
        'estado',
        'fecha_creacion',
        'usuario_creacion'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener controles con información de maquiladora
     */
    public function getConMaquiladora($maquiladoraId = null)
    {
        $builder = $this->db->table($this->table . ' cb')
            ->select('cb.*, m.Nombre_Maquila as maquiladoraNombre, op.folio as ordenFolio')
            ->join('maquiladora m', 'm.idmaquiladora = cb.idmaquiladora', 'left')
            ->join('orden_produccion op', 'op.id = cb.ordenProduccionId', 'left')
            ->orderBy('cb.created_at', 'DESC');

        if ($maquiladoraId) {
            $builder->where('cb.idmaquiladora', $maquiladoraId);
        }

        $controles = $builder->get()->getResultArray();

        // Calcular progreso para cada control
        foreach ($controles as &$control) {
            $control['progreso_general'] = $this->calcularProgresoGeneral($control['id']);
        }

        return $controles;
    }

    /**
     * Obtener control detallado con bultos, operaciones y progreso
     */
    public function getDetallado($id)
    {
        // Control principal
        $control = $this->db->table($this->table . ' cb')
            ->select('cb.*, m.Nombre_Maquila as maquiladoraNombre, op.folio as ordenFolio, op.cantidadPlan as ordenCantidad')
            ->join('maquiladora m', 'm.idmaquiladora = cb.idmaquiladora', 'left')
            ->join('orden_produccion op', 'op.id = cb.ordenProduccionId', 'left')
            ->where('cb.id', $id)
            ->get()
            ->getRowArray();

        if (!$control) {
            return null;
        }

        // Bultos
        $control['bultos'] = $this->db->table('bultos')
            ->where('controlBultoId', $id)
            ->orderBy('numero_bulto', 'ASC')
            ->get()
            ->getResultArray();

        // Operaciones con progreso
        $control['operaciones'] = $this->db->table('operaciones_control')
            ->where('controlBultoId', $id)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        // Calcular progreso general
        $control['progreso_general'] = $this->calcularProgresoGeneral($id);

        return $control;
    }

    /**
     * Crear control desde plantilla
     */
    public function crearControl($data)
    {
        $this->db->transStart();

        // Insertar control
        $controlId = $this->insert([
            'idmaquiladora' => $data['idmaquiladora'],
            'ordenProduccionId' => $data['ordenProduccionId'],
            'inspeccionId' => $data['inspeccionId'] ?? null,
            'estilo' => $data['estilo'],
            'orden' => $data['orden'],
            'cantidad_total' => $data['cantidad_total'],
            'con_tallas' => $data['con_tallas'] ?? 0,
            'estado' => 'en_proceso',
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'usuario_creacion' => $data['usuario_creacion'] ?? null,
        ]);

        // Guardar tallas si es control con tallas
        if (!empty($data['con_tallas']) && !empty($data['tallas'])) {
            foreach ($data['tallas'] as $talla) {
                $this->db->table('control_bultos_tallas')->insert([
                    'control_bulto_id' => $controlId,
                    'id_talla' => $talla['id_talla'],
                    'cantidad' => $talla['cantidad'],
                    'creado_en' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Si hay plantilla, crear operaciones
        if (!empty($data['plantillaId'])) {
            $plantilla = $this->db->table('plantillas_operaciones')
                ->where('id', $data['plantillaId'])
                ->get()
                ->getRowArray();

            if ($plantilla && !empty($plantilla['operaciones'])) {
                $operaciones = json_decode($plantilla['operaciones'], true);
                $operacionControlModel = new \App\Models\OperacionControlModel();

                foreach ($operaciones as $op) {
                    $piezasRequeridas = $data['cantidad_total'] * ($op['piezas_por_prenda'] ?? 1);

                    $operacionControlModel->insert([
                        'controlBultoId' => $controlId,
                        'nombre_operacion' => $op['nombre'],
                        'piezas_requeridas' => $piezasRequeridas,
                        'piezas_completadas' => 0,
                        'porcentaje_completado' => 0,
                        'es_componente' => $op['es_componente'] ?? 1,
                        'orden' => $op['orden'] ?? 0,
                    ]);
                }
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $controlId : false;
    }

    /**
     * Calcular progreso general del control
     */
    public function calcularProgresoGeneral($controlId)
    {
        $operaciones = $this->db->table('operaciones_control')
            ->where('controlBultoId', $controlId)
            ->get()
            ->getResultArray();

        if (empty($operaciones)) {
            return 0;
        }

        $totalPorcentaje = 0;
        foreach ($operaciones as $op) {
            $totalPorcentaje += $op['porcentaje_completado'];
        }

        return round($totalPorcentaje / count($operaciones), 2);
    }

    /**
     * Verificar si todos los componentes están completos (100%)
     */
    public function verificarListoParaArmado($controlId)
    {
        $componentesIncompletos = $this->db->table('operaciones_control')
            ->where('controlBultoId', $controlId)
            ->where('es_componente', 1)
            ->where('porcentaje_completado <', 100)
            ->countAllResults();

        return $componentesIncompletos === 0;
    }

    /**
     * Actualizar estado del control basado en progreso
     */
    public function actualizarEstado($controlId)
    {
        $listoParaArmado = $this->verificarListoParaArmado($controlId);

        if ($listoParaArmado) {
            // Verificar si el armado también está completo
            $armadoCompleto = $this->db->table('operaciones_control')
                ->where('controlBultoId', $controlId)
                ->where('es_componente', 0)
                ->where('porcentaje_completado', 100)
                ->countAllResults();

            $nuevoEstado = $armadoCompleto > 0 ? 'completado' : 'listo_armado';

            $this->update($controlId, ['estado' => $nuevoEstado]);

            // Si se completó, actualizar también la Orden de Producción
            if ($nuevoEstado === 'completado') {
                $control = $this->find($controlId);
                if ($control && !empty($control['ordenProduccionId'])) {
                    $this->db->table('orden_produccion')
                        ->where('id', $control['ordenProduccionId'])
                        ->update(['status' => 'Completada', 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }

            return $nuevoEstado;
        }

        return 'en_proceso';
    }
}
