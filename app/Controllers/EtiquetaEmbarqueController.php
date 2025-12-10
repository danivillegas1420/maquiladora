<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentoEnvioModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class EtiquetaEmbarqueController extends BaseController
{
    /* =========================================================
     * Helpers internos
     * =======================================================*/
    private function db()
    {
        return \Config\Database::connect();
    }

    private function tableExists(string $table): bool
    {
        try {
            return in_array($table, $this->db()->listTables(), true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Devuelve el nombre real de la tabla de etiquetas,
     * o null si no existe ninguna.
     */
    private function etiquetaTableName(): ?string
    {
        try {
            $tables = $this->db()->listTables();
        } catch (\Throwable $e) {
            return null;
        }

        if (in_array('etiqueta_embarque', $tables, true)) {
            return 'etiqueta_embarque';
        }

        if (in_array('embarque_etiqueta', $tables, true)) {
            return 'embarque_etiqueta';
        }

        return null;
    }

    /**
     * Igual que arriba pero lanza 404 si no existe.
     */
    private function etiquetaTableOrFail(): string
    {
        $name = $this->etiquetaTableName();
        if (!$name) {
            throw PageNotFoundException::forPageNotFound(
                'La tabla de etiquetas de embarque no existe.'
            );
        }
        return $name;
    }

    /**
     * Carga datos del embarque + cliente + etiqueta
     * Devuelve ['embarque' => ..., 'etiqueta' => ...]
     */
    private function cargarDatos(int $embarqueId): array
    {
        $db = $this->db();

        if (!$this->tableExists('embarque')) {
            throw PageNotFoundException::forPageNotFound('La tabla embarque no existe');
        }

        try {
            $colsEmb = array_flip($db->getFieldNames('embarque'));
        } catch (\Throwable $e) {
            $colsEmb = [];
        }

        $builder = $db->table('embarque e')->where('e.id', $embarqueId);

        // ----- SELECT dinámico para no reventar si faltan columnas -----
        $select = 'e.id';

        // folio
        $hasFolioLower = isset($colsEmb['folio']);
        $hasFolioUpper = isset($colsEmb['Folio']);
        if ($hasFolioLower) {
            $select .= ', e.folio';
        } elseif ($hasFolioUpper) {
            $select .= ', e.Folio AS folio';
        } else {
            $select .= ", CONCAT('EMB-', e.id) AS folio";
        }

        // fecha
        $hasFechaLower = isset($colsEmb['fecha']);
        $hasFechaUpper = isset($colsEmb['Fecha']);
        if ($hasFechaLower) {
            $select .= ', e.fecha';
        } elseif ($hasFechaUpper) {
            $select .= ', e.Fecha AS fecha';
        } else {
            $select .= ', NULL AS fecha';
        }

        // contenedor
        if (isset($colsEmb['contenedor'])) {
            $select .= ', e.contenedor';
        } else {
            $select .= ', NULL AS contenedor';
        }

        // transportista
        if (isset($colsEmb['transportista'])) {
            $select .= ', e.transportista';
        } elseif (isset($colsEmb['Transportista'])) {
            $select .= ', e.Transportista AS transportista';
        } else {
            $select .= ', NULL AS transportista';
        }

        // ----- Cliente / destino -----
        $joinCliente = false;
        if (isset($colsEmb['clienteId'])) {
            $joinCliente = true;
            $builder->join('cliente c', 'c.id = e.clienteId', 'left');
        } elseif (isset($colsEmb['id_cliente'])) {
            $joinCliente = true;
            $builder->join('cliente c', 'c.id = e.id_cliente', 'left');
        }

        $destinoExpr = "''";
        $clienteNombre = "''";

        if ($joinCliente && $this->tableExists('cliente')) {
            try {
                $colsCli = array_flip($db->getFieldNames('cliente'));
            } catch (\Throwable $e) {
                $colsCli = [];
            }

            // nombre cliente
            if (isset($colsCli['nombre'])) {
                $clienteNombre = 'c.nombre';
            } elseif (isset($colsCli['Nombre'])) {
                $clienteNombre = 'c.Nombre';
            }

            // domicilio / dirección
            $destParts = [];
            if (isset($colsCli['domicilio'])) {
                $destParts[] = 'c.domicilio';
            }
            if (isset($colsCli['Domicilio'])) {
                $destParts[] = 'c.Domicilio';
            }
            if (isset($colsCli['direccion'])) {
                $destParts[] = 'c.direccion';
            }
            if (isset($colsCli['Direccion'])) {
                $destParts[] = 'c.Direccion';
            }

            if ($destParts) {
                $destinoExpr = 'COALESCE(' . implode(',', $destParts) . ')';
            }
        }

        $select .= ', ' . $clienteNombre . ' AS clienteNombre';
        $select .= ', ' . $destinoExpr . ' AS destino';

        $embarque = $builder->select($select)->get()->getRowArray();

        if (!$embarque) {
            throw PageNotFoundException::forPageNotFound('Embarque no encontrado');
        }

        // ----- Etiqueta (si existe) -----
        $etiqueta = null;
        $tablaEt = $this->etiquetaTableName();
        if ($tablaEt) {
            $etiqueta = $db->table($tablaEt)
                ->where('embarqueId', $embarqueId)
                ->get()
                ->getRowArray();
        }

        return [
            'embarque' => $embarque,
            'etiqueta' => $etiqueta,
        ];
    }

    /* =========================================================
     *  VISTA PRINCIPAL
     * =======================================================*/
    // Asegúrate que en Routes tengas:
    // $routes->get('logistica/embarque/(:num)/etiqueta', 'EtiquetaEmbarqueController::show/$1', ...);
    public function show($embarqueId)
    {
        $embarqueId = (int) $embarqueId;
        $data = $this->cargarDatos($embarqueId);

        return view('modulos/embarque_etiqueta', $data);
    }

    /* =========================================================
     *  GUARDAR ETIQUETA + PDF
     * =======================================================*/
    public function guardar($embarqueId)
    {
        $embarqueId = (int) $embarqueId;

        $db = $this->db();
        $tablaEt = $this->etiquetaTableOrFail();
        $builder = $db->table($tablaEt);

        $id = (int) ($this->request->getPost('id') ?? 0);

        $payload = [
            'embarqueId' => $embarqueId,
            'codigo' => trim((string) $this->request->getPost('codigo')),
            'ship_to_nombre' => trim((string) $this->request->getPost('ship_to_nombre')),
            'ship_to_direccion' => trim((string) $this->request->getPost('ship_to_direccion')),
            'ship_to_ciudad' => trim((string) $this->request->getPost('ship_to_ciudad')),
            'ship_to_pais' => trim((string) $this->request->getPost('ship_to_pais')),
            'referencia' => trim((string) $this->request->getPost('referencia')),
            'bultos' => $this->request->getPost('bultos') !== '' ? (int) $this->request->getPost('bultos') : null,
            'peso_bruto' => $this->request->getPost('peso_bruto') !== '' ? (float) $this->request->getPost('peso_bruto') : null,
            'peso_neto' => $this->request->getPost('peso_neto') !== '' ? (float) $this->request->getPost('peso_neto') : null,
        ];

        // Columnas reales de la tabla
        try {
            $cols = array_flip($db->getFieldNames($tablaEt));
        } catch (\Throwable $e) {
            $cols = [];
        }

        // maquiladoraID si existe en la tabla
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID') ?? null;
        if ($maquiladoraId && isset($cols['maquiladoraID'])) {
            $payload['maquiladoraID'] = (int) $maquiladoraId;
        }

        $data = array_intersect_key($payload, $cols);

        if ($id > 0) {
            $builder->where('id', $id)->update($data);
        } else {
            $builder->insert($data);
            $id = (int) $db->insertID();
        }

        // Generar PDF y registrar en doc_embarque
        try {
            $this->generarPdfYDocumento($embarqueId, $id);
            $msg = 'Etiqueta guardada y PDF generado.';
        } catch (\Throwable $e) {
            $msg = 'Etiqueta guardada, pero hubo un problema generando el PDF: ' . $e->getMessage();
        }

        return redirect()
            ->to(site_url('logistica/embarque/' . $embarqueId . '/etiqueta'))
            ->with('ok', $msg);
    }

    /* =========================================================
     *  PDF (desde botón directo)
     * =======================================================*/
    public function pdf($id)
    {
        $id = (int) $id;

        $tablaEt = $this->etiquetaTableOrFail();

        $row = $this->db()->table($tablaEt)
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$row) {
            throw PageNotFoundException::forPageNotFound('Etiqueta no encontrada');
        }

        $embarqueId = (int) ($row['embarqueId'] ?? 0);

        $path = $this->generarPdfYDocumento($embarqueId, $id);

        if (is_file($path)) {
            return $this->response->download($path, null)->setFileName(basename($path));
        }

        return redirect()->back()->with('error', 'No se pudo generar el PDF de la etiqueta.');
    }

    /* =========================================================
     *  ELIMINAR ETIQUETA
     * =======================================================*/
    public function eliminar($id)
    {
        $id = (int) $id;

        $db = $this->db();
        $tablaEt = $this->etiquetaTableOrFail();
        $tabla = $db->table($tablaEt);

        $row = $tabla->where('id', $id)->get()->getRowArray();
        $embarqueId = (int) ($row['embarqueId'] ?? 0);

        $tabla->where('id', $id)->delete();

        // Borrar documento asociado (tipo Etiqueta) y archivo
        if ($this->tableExists('doc_embarque')) {
            $docModel = new DocumentoEnvioModel();
            $doc = $docModel
                ->where('embarqueId', $embarqueId)
                ->where('tipo', 'Etiqueta')
                ->first();

            if ($doc) {
                $rel = $doc['archivoPdf'] ?? $doc['archivoRuta'] ?? null;

                $docModel->delete($doc['id']);

                if ($rel) {
                    $paths = [
                        WRITEPATH . 'uploads/' . ltrim($rel, '/'),
                        FCPATH . ltrim($rel, '/'),
                    ];
                    foreach ($paths as $p) {
                        if (is_file($p)) {
                            @unlink($p);
                        }
                    }
                }
            }
        }

        $url = $embarqueId
            ? site_url('logistica/embarque/' . $embarqueId . '/etiqueta')
            : previous_url();

        return redirect()->to($url)->with('ok', 'Etiqueta eliminada.');
    }

    /* =========================================================
     *  GENERAR PDF + REGISTRAR EN doc_embarque
     * =======================================================*/
    /**
     * Genera el PDF, lo guarda en writable/uploads/pdfs
     * y crea/actualiza el registro en doc_embarque (tipo Etiqueta).
     *
     * @return string Ruta absoluta del archivo generado.
     */
    private function generarPdfYDocumento(int $embarqueId, int $etiquetaId): string
    {
        $db = $this->db();
        $data = $this->cargarDatos($embarqueId);

        // Asegurar que la etiqueta cargada sea la correcta
        $tablaEt = $this->etiquetaTableName();
        if ((!$data['etiqueta'] || (int) ($data['etiqueta']['id'] ?? 0) !== $etiquetaId) && $tablaEt) {
            $data['etiqueta'] = $db->table($tablaEt)
                ->where('id', $etiquetaId)
                ->get()
                ->getRowArray();
        }

        $embarque = $data['embarque'] ?? [];
        $etiqueta = $data['etiqueta'] ?? [];

        // ---------- Render HTML para el PDF ----------
        // Usa una vista simple para PDF (sin layout)
        // (ya la tienes como modulos/embarque_etiqueta_pdf.php)
        $html = view('modulos/embarque_etiqueta_pdf', [
            'embarque' => $embarque,
            'etiqueta' => $etiqueta,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        // Usar estructura estándar de almacenamiento local
        $dir = WRITEPATH . 'uploads/documentos_embarque/etiquetas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $fileName = 'etiqueta_' . $embarqueId . '_' . date('Ymd_His') . '.pdf';
        $filePath = $dir . $fileName;

        file_put_contents($filePath, $output);

        // Ruta relativa para doc_embarque
        $relativePath = 'documentos_embarque/etiquetas/' . $fileName;

        // URL para acceder al PDF
        $pdfUrl = site_url('modulo3/documentos/pdf/' . urlencode('etiquetas/' . $fileName));

        // ----- Registrar en doc_embarque -----
        if ($this->tableExists('doc_embarque')) {
            try {
                $cols = array_flip($db->getFieldNames('doc_embarque'));
            } catch (\Throwable $e) {
                $cols = [];
            }

            $numero = $etiqueta['codigo'] ?? ('ETQ-' . ($embarque['folio'] ?? $embarqueId));

            $docData = [
                'embarqueId' => $embarqueId,
                'tipo' => 'Etiqueta',
                'numero' => $numero,
                'fecha' => date('Y-m-d'),
                'estado' => 'Emitida',
                'archivoRuta' => $relativePath,
                'urlPdf' => $pdfUrl,
                'archivoPdf' => '',
            ];

            // Maquiladora si aplica
            $session = session();
            $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID') ?? null;
            if ($maquiladoraId) {
                if (isset($cols['maquiladoraID'])) {
                    $docData['maquiladoraID'] = (int) $maquiladoraId;
                } elseif (isset($cols['maquiladoraId'])) {
                    $docData['maquiladoraId'] = (int) $maquiladoraId;
                }
            }

            $docData = array_intersect_key($docData, $cols);

            $docModel = new DocumentoEnvioModel();

            $existing = $docModel
                ->where('embarqueId', $embarqueId)
                ->where('tipo', 'Etiqueta')
                ->first();

            if ($existing) {
                $docModel->update($existing['id'], $docData);
            } else {
                $docModel->insert($docData);
            }
        }

        return $filePath;
    }
}
