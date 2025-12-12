<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpleadoModel extends Model
{
    protected $table = 'empleado';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'noEmpleado',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'domicilio',
        'puesto',
        'activo',
        'idusuario',
        'foto',  // Agregar este campo
        'fecha_nac',  // Agregar si no está
        'curp'        // Agregar si no está
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'noEmpleado' => 'required|min_length[3]|max_length[20]|is_unique[empleado.noEmpleado,id,{id}]',
        'nombre' => 'required|min_length[2]|max_length[100]',
        'apellido' => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email|max_length[100]|is_unique[empleado.email,id,{id}]',
        'telefono' => 'permit_empty|max_length[20]',
        'domicilio' => 'permit_empty|max_length[255]',
        'puesto' => 'required|max_length[100]',
        'activo' => 'required|in_list[0,1]',
        'idusuario' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'noEmpleado' => [
            'required' => 'El número de empleado es obligatorio',
            'min_length' => 'El número de empleado debe tener al menos 3 caracteres',
            'max_length' => 'El número de empleado no puede superar los 20 caracteres',
            'is_unique' => 'Este número de empleado ya existe',
        ],
        'nombre' => [
            'required' => 'El nombre es obligatorio',
            'min_length' => 'El nombre debe tener al menos 2 caracteres',
            'max_length' => 'El nombre no puede superar los 100 caracteres',
        ],
        'apellido' => [
            'required' => 'El apellido es obligatorio',
            'min_length' => 'El apellido debe tener al menos 2 caracteres',
            'max_length' => 'El apellido no puede superar los 100 caracteres',
        ],
        'email' => [
            'required' => 'El email es obligatorio',
            'valid_email' => 'El email debe tener un formato válido',
            'max_length' => 'El email no puede superar los 100 caracteres',
            'is_unique' => 'Este email ya está registrado',
        ],
        'telefono' => [
            'max_length' => 'El teléfono no puede superar los 20 caracteres',
        ],
        'domicilio' => [
            'max_length' => 'El domicilio no puede superar los 255 caracteres',
        ],
        'puesto' => [
            'required' => 'El puesto es obligatorio',
            'max_length' => 'El puesto no puede superar los 100 caracteres',
        ],
        'activo' => [
            'required' => 'El campo activo es obligatorio',
            'in_list' => 'El valor de activo debe ser 0 o 1',
        ],
        'idusuario' => [
            'integer' => 'El id de usuario debe ser un número entero',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Obtiene todos los empleados con sus datos de usuario asociados
     */
    public function getEmpleadosConUsuarios()
    {
        return $this->select('empleado.*, u.username as usuario, u.active as usuario_activo, u.created_at as fechaAlta, u.maquiladoraIdFK as idMaquiladora')
            ->join('users u', 'u.id = empleado.idusuario', 'left')
            ->findAll();
    }

    /**
     * Obtiene un empleado específico con sus datos de usuario
     */
    public function getEmpleadoConUsuario($id)
    {
        return $this->select('empleado.*, u.username as usuario, u.active as usuario_activo, u.created_at as fechaAlta, u.maquiladoraIdFK as idMaquiladora')
            ->join('users u', 'u.id = empleado.idusuario', 'left')
            ->where('empleado.id', $id)
            ->first();
    }

    /**
     * Obtiene empleados activos con sus usuarios
     */
    public function getEmpleadosActivos()
    {
        return $this->select('empleado.*, u.username as usuario, u.active as usuario_activo, u.created_at as fechaAlta, u.maquiladoraIdFK as idMaquiladora')
            ->join('users u', 'u.id = empleado.idusuario', 'left')
            ->where('empleado.activo', 1)
            ->findAll();
    }

    /**
     * Busca empleados por nombre o apellido
     */
    public function buscarEmpleados($termino)
    {
        return $this->select('empleado.*, u.username as usuario, u.active as usuario_activo')
            ->join('users u', 'u.id = empleado.idusuario', 'left')
            ->groupStart()
            ->like('empleado.nombre', $termino)
            ->orLike('empleado.apellido', $termino)
            ->orLike('empleado.noEmpleado', $termino)
            ->groupEnd()
            ->findAll();
    }

    /**
     * Empleados activos no asignados aún a la OP indicada.
     * Si se proporciona $maquiladoraId, solo devuelve empleados de esa maquiladora
     * usando empleado.maquiladoraID o users.maquiladoraIdFK (si existe vínculo idusuario -> users.id).
     */
    public function listarDisponiblesParaOP(int $opId, $maquiladoraId = null): array
    {
        if ($opId <= 0)
            return [];

        $sql = "SELECT e.id, e.noEmpleado, e.nombre, e.apellido, e.puesto
                FROM empleado e
                LEFT JOIN users u ON u.id = e.idusuario
                WHERE e.activo = 1
                  AND e.puesto IN ('Empleado','Corte')
                  AND e.id NOT IN (
                      SELECT at.empleadoId FROM asignacion_tarea at WHERE at.ordenProduccionId = ?
                  )";

        $params = [$opId];
        if ($maquiladoraId) {
            $sql .= " AND (e.maquiladoraID = ? OR u.maquiladoraIdFK = ?)";
            $params[] = (int) $maquiladoraId;
            $params[] = (int) $maquiladoraId;
        }

        $sql .= " ORDER BY e.nombre, e.apellido";
        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Obtiene empleados asignados a una orden de producción específica
     */
    public function getEmpleadosPorOrden(int $ordenProduccionId): array
    {
        if ($ordenProduccionId <= 0)
            return [];

        $sql = "SELECT e.id, e.noEmpleado, e.nombre, e.apellido, e.puesto
                FROM empleado e
                INNER JOIN asignacion_tarea at ON at.empleadoId = e.id
                WHERE e.activo = 1
                  AND at.ordenProduccionId = ?
                ORDER BY e.nombre, e.apellido";

        return $this->db->query($sql, [$ordenProduccionId])->getResultArray();
    }

    /**
     * Obtiene empleados activos de una maquiladora específica
     */
    public function getEmpleadosPorMaquiladora(int $maquiladoraId): array
    {
        return $this->select('empleado.id, empleado.noEmpleado, empleado.nombre, empleado.apellido, empleado.puesto')
            ->join('users u', 'u.id = empleado.idusuario', 'left')
            ->where('empleado.activo', 1)
            ->where('(empleado.maquiladoraID = ? OR u.maquiladoraIdFK = ?)', [$maquiladoraId, $maquiladoraId])
            ->orderBy('empleado.nombre, empleado.apellido')
            ->findAll();
    }

    /**
     * Búsqueda remota de empleados activos no asignados a una OP, filtrando por término.
     */
    public function buscarDisponiblesParaOP(int $opId, string $termino, int $limit = 20, $maquiladoraId = null): array
    {
        if ($opId <= 0)
            return [];
        $termino = trim($termino);
        $like = '%' . $termino . '%';
        $params = [$opId];
        $whereLike = '';
        if ($termino !== '') {
            $whereLike = ' AND (e.nombre LIKE ? OR e.apellido LIKE ? OR e.noEmpleado LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql = "SELECT e.id, e.noEmpleado, e.nombre, e.apellido, e.puesto
                FROM empleado e
                LEFT JOIN users u ON u.id = e.idusuario
                WHERE e.activo = 1
                  AND e.puesto IN ('Empleado','Corte')
                  AND e.id NOT IN (
                      SELECT at.empleadoId FROM asignacion_tarea at WHERE at.ordenProduccionId = ?
                  )";

        if ($maquiladoraId) {
            $sql .= " AND (e.maquiladoraID = ? OR u.maquiladoraIdFK = ?)";
            $params[] = (int) $maquiladoraId;
            $params[] = (int) $maquiladoraId;
        }

        $sql .= $whereLike . "
                ORDER BY e.nombre, e.apellido
                LIMIT " . (int) $limit;

        return $this->db->query($sql, $params)->getResultArray();
    }
}
