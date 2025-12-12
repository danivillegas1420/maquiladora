<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;

class FacturaDemoController extends Controller
{
    /** Datos demo (ajústalos si quieres) */
    private function demoData(int $embarqueId = 1): array
    {
        // ---- emisor / receptor / conceptos / totales / timbre: DEMO ----
        $emisor = [
            'logo'            => '',
            'nombre'          => 'Textiles XYZ S.A. de C.V.',
            'rfc'             => 'TXY123456789',
            'regimen'         => '601',
            'lugarExpedicion' => '72000',
            'noCertCSD'       => '30001000000300023708',
        ];
        $receptor = [
            'nombre'    => 'Cliente DEMO',
            'rfc'       => 'XAXX010101000',
            'usoCfdi'   => 'G03',
            'domicilio' => '00000',
        ];
        $factura = [
            'tipo'        => 'I',
            'serie'       => 'DEMO',
            'folio'       => (string) rand(1000, 9999),
            'fecha'       => date('Y-m-d H:i:s'),
            'moneda'      => 'MXN',
            'tipoCambio'  => '1',
            'formaPago'   => '03',
            'metodoPago'  => 'PUE',
            'condiciones' => 'Contado',
        ];
        $conceptos = [
            [
                'prodserv'     => '01010101',
                'claveUnidad'  => 'E48',
                'cantidad'     => 1,
                'unidad'       => 'SERV',
                'descripcion'  => 'Servicio de envío - Embarque #'.$embarqueId,
                'valorUnitario'=> 100.00,
                'descuento'    => 0,
                'importe'      => 100.00,
                'iva'          => 16.00,
                'ieps'         => 0.00,
            ],
        ];
        $totales = [
            'subtotal'  => 100.00,
            'descuento' => 0.00,
            'trasladados'=> 16.00,
            'retenidos' => 0.00,
            'total'     => 116.00,
            'letra'     => 'CIENTO DIECISÉIS PESOS 00/100 M.N.',
        ];
        $timbre = [
            'uuid'          => '00000000-0000-4000-8000-'.substr(md5(uniqid('', true)), 0, 12),
            'fechaTimbrado' => date('c'),
            'noCertCSD'     => $emisor['noCertCSD'],
            'selloCfdi'     => substr(hash('sha256', 'cfdi-demo'), 0, 80).'…',
            'selloSat'      => substr(hash('sha256', 'sat-demo'), 0, 80).'…',
            'qr'            => '', // puedes poner una URL de imagen si quieres
        ];

        return compact('embarqueId','emisor','receptor','factura','conceptos','totales','timbre');
    }

    /** Vista HTML de previsualización */
    public function preview(int $embarqueId = 1)
    {
        if (!can('menu.facturacion_demo')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $data = $this->demoData($embarqueId);
        // guardamos en sesión para usar mismos datos al pedir el PDF
        session()->set('factura_demo_'.$embarqueId, $data);
        return view('modulos/factura_cfdi_demo', $data);
    }

    /** Descarga/visualización del PDF (sin corrupción de bytes) */
    public function pdf(int $embarqueId = 1)
    {
        if (!can('menu.facturacion_demo')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        // mismos datos que viste en HTML
        $data = session()->get('factura_demo_'.$embarqueId) ?? $this->demoData($embarqueId);

        // 1) Renderizar HTML de la vista
        $html = view('modulos/factura_cfdi_demo', $data);

        // 2) Configurar Dompdf
        $opt = new Options();
        $opt->set('isRemoteEnabled', true);       // para logos/QR remotos
        $opt->set('isHtml5ParserEnabled', true);  // parser HTML5
        $dompdf = new Dompdf($opt);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfBytes = $dompdf->output();

        // 3) Evitar bytes extra (toolbar, BOM, espacios, var_dump accidental, etc.)
        while (ob_get_level() > 0) { @ob_end_clean(); }

        // 4) Responder SOLO bytes PDF con headers correctos
        $filename = 'factura_demo_'.$embarqueId.'.pdf';

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="'.$filename.'"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setBody($pdfBytes);
    }
}
