<?php
namespace App\Controllers;

use App\Models\MaquinaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Maquinaria extends BaseController
{
    /** Devuelve el siguiente código incremental: MC-0001, MC-0002, ... */
    protected function nextCodigo(string $prefix = 'MC-', int $pad = 4): string
    {
        $db   = \Config\Database::connect();
        $max  = 0;
        try {
            $rows = $db->table('maquina')->select('codigo')
                ->like('codigo', $prefix, 'after')->get()->getResultArray();

            foreach ($rows as $r) {
                $c = (string)($r['codigo'] ?? '');
                if (strpos($c, $prefix) === 0) {
                    $numStr = preg_replace('/\D/', '', substr($c, strlen($prefix)));
                    if ($numStr !== '') {
                        $n = (int)$numStr;
                        if ($n > $max) $max = $n;
                    }
                }
            }
        } catch (\Throwable $e) {
            // si algo falla, arranca desde 0
        }
        return $prefix . str_pad((string)($max + 1), $pad, '0', STR_PAD_LEFT);
    }

    /** Normaliza POST y valida */
    protected function sanitizeAndValidate(array $post, ?int $id = null): array
    {
        // Estado → 1/0 (acepta 'Operativa'/'En reparación' o 1/0)
        $post['activa'] = $post['activa'] ?? 'Operativa';
        $post['activa'] = ($post['activa'] === 'Operativa' || $post['activa'] === '1' || $post['activa'] === 1) ? 1 : 0;

        // Normalizar fecha dd/mm/yyyy → Y-m-d
        if (!empty($post['fechaCompra']) && strpos($post['fechaCompra'], '/') !== false) {
            [$d,$m,$y] = explode('/', $post['fechaCompra']);
            if (@checkdate((int)$m, (int)$d, (int)$y)) {
                $post['fechaCompra'] = sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }

        // Reglas de validación
        $rules = [
            'codigo'      => 'required|min_length[3]|max_length[50]',
            'modelo'      => 'required|min_length[2]|max_length[120]',
            'fabricante'  => 'permit_empty|max_length[120]',
            'serie'       => 'permit_empty|max_length[120]',
            'fechaCompra' => 'permit_empty|valid_date[Y-m-d]',
            'ubicacion'   => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            throw new \RuntimeException(implode(' ', $this->validator->getErrors()));
        }

        return $post;
    }

    /** Inventario */
    public function index()
    {
        if (!can('menu.maquinaria_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $model = new MaquinaModel();
        $db    = \Config\Database::connect();
        $fields = $db->getFieldNames($model->getTable());
        $maquiladoraId = session()->get('maquiladora_id');

        // Listado
        $builder = $model->orderBy('codigo','ASC');
        if ($maquiladoraId && in_array('maquiladoraID', $fields, true)) {
            $builder = $builder->where('maquiladoraID', (int)$maquiladoraId);
        }
        $maquinas = $builder->findAll();
        $maquinas = $model->withEstado($maquinas);

        // Catálogos seguros
        $modelos = in_array('modelo', $fields, true)
            ? array_values(array_filter($model->select('modelo')->distinct()->orderBy('modelo','ASC')->findColumn('modelo') ?? [], fn($v)=>(string)$v!==''))
            : [];
        $fabricantes = in_array('fabricante', $fields, true)
            ? array_values(array_filter($model->select('fabricante')->distinct()->orderBy('fabricante','ASC')->findColumn('fabricante') ?? [], fn($v)=>(string)$v!==''))
            : [];
        $ubicaciones = in_array('ubicacion', $fields, true)
            ? array_values(array_filter($model->select('ubicacion')->distinct()->orderBy('ubicacion','ASC')->findColumn('ubicacion') ?? [], fn($v)=>(string)$v!==''))
            : [];
        $series = in_array('serie', $fields, true)
            ? array_values(array_filter($model->select('serie')->distinct()->orderBy('serie','ASC')->findColumn('serie') ?? [], fn($v)=>(string)$v!==''))
            : [];

        // Adaptar a la vista
        $maq = array_map(function ($m) {
            return [
                'id'         => $m['id'] ?? null,
                'cod'        => $m['codigo'] ?? '',
                'modelo'     => $m['modelo'] ?? '',
                'fabricante' => $m['fabricante'] ?? '',
                'serie'      => $m['serie'] ?? '',
                'compra'     => $m['fechaCompra'] ?? '',
                'ubic'       => $m['ubicacion'] ?? '',
                'estado'     => $m['estado_txt'] ?? 'Operativa',
            ];
        }, $maquinas);

        return view('modulos/mantenimiento_inventario', [
            'title'       => 'Inventario de Maquinaria',
            'maq'         => $maq,
            'modelos'     => $modelos,
            'fabricantes' => $fabricantes,
            'ubicaciones' => $ubicaciones,
            'series'      => $series,
            'sigCodigo'   => $this->nextCodigo(), // <-- para autorrellenar
        ]);
    }

    /** Guardar (genera código si viene vacío y evita choques) */
    public function guardar()
    {
        if (!can('menu.maquinaria_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        try {
            $post = $this->request->getPost([
                'codigo','modelo','fabricante','serie','fechaCompra','ubicacion','activa'
            ]);

            // Código incremental si viene vacío
            if (empty($post['codigo'])) {
                $post['codigo'] = $this->nextCodigo();
            }

            $post = $this->sanitizeAndValidate($post);

            $model  = new MaquinaModel();
            $db     = \Config\Database::connect();
            $fields = $db->getFieldNames($model->getTable());

            $maquiladoraId = session()->get('maquiladora_id');

            // Si el código ya existe, genera otro
            if ($model->where('codigo', $post['codigo'])->first()) {
                $post['codigo'] = $this->nextCodigo();
            }

            $data = [];
            foreach (['codigo','modelo','fabricante','serie','fechaCompra','ubicacion','activa'] as $k) {
                if (in_array($k, $fields, true)) $data[$k] = $post[$k] ?? null;
            }
            if ($maquiladoraId && in_array('maquiladoraID', $fields, true)) {
                $data['maquiladoraID'] = (int)$maquiladoraId;
            }

            $model->insert($data);

            return redirect()->to(base_url('modulo3/mantenimiento_inventario'))
                ->with('success', 'Máquina guardada correctamente.');
        } catch (\Throwable $e) {
            log_message('error', 'Guardar Máquina: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar. ' . $e->getMessage());
        }
    }

    /** Formulario de edición */
    public function editar($id)
    {
        if (!can('menu.maquinaria_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $model = new MaquinaModel();
        $row   = $model->find($id);
        if (!$row) throw new PageNotFoundException('Máquina no encontrada');

        $row['estado_txt'] = ((int)$row['activa'] === 1) ? 'Operativa' : 'En reparación';

        return view('modulos/maquinaria_editar', ['m' => $row]);
    }

    /** Actualizar registro */
    public function actualizar($id)
    {
        if (!can('menu.maquinaria_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        try {
            $model = new MaquinaModel();
            $row   = $model->find($id);
            if (!$row) throw new PageNotFoundException('Máquina no encontrada');

            $post = $this->request->getPost([
                'codigo','modelo','fabricante','serie','fechaCompra','ubicacion','activa'
            ]);

            // Si dejan vacío el código al editar, le asignamos uno nuevo
            if (empty($post['codigo'])) {
                $post['codigo'] = $this->nextCodigo();
            }

            $post = $this->sanitizeAndValidate($post, (int)$id);

            // Verificar choque de código con otro registro
            $existe = $model->where('codigo', $post['codigo'])->where('id !=', $id)->first();
            if ($existe) {
                // Si choca, generamos el siguiente
                $post['codigo'] = $this->nextCodigo();
            }

            $db     = \Config\Database::connect();
            $fields = $db->getFieldNames($model->getTable());
            $data   = [];
            foreach (['codigo','modelo','fabricante','serie','fechaCompra','ubicacion','activa'] as $k) {
                if (in_array($k, $fields, true)) $data[$k] = $post[$k] ?? null;
            }

            $model->update($id, $data);

            return redirect()->to(base_url('modulo3/mantenimiento_inventario'))
                ->with('success', 'Máquina actualizada correctamente.');
        } catch (\Throwable $e) {
            log_message('error', 'Actualizar Máquina: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar. ' . $e->getMessage());
        }
    }

    /** Eliminar */
    public function eliminar($id)
    {
        if (!can('menu.maquinaria_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $model = new MaquinaModel();
        if (!$model->find($id)) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }
        $model->delete($id);
        return redirect()->to(base_url('modulo3/mantenimiento_inventario'))
            ->with('success', 'Máquina eliminada.');
    }
}
