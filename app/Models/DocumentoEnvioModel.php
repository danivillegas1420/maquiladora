<?php
// app/Models/DocumentoEnvioModel.php
namespace App\Models;

use CodeIgniter\Model;

class DocumentoEnvioModel extends Model
{
    protected $table = 'doc_embarque';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'embarqueId',
        'tipo',
        'archivoRuta',
        'numero',
        'fecha',
        'estado',
        'urlPdf',
        'archivoPdf',
        'maquiladoraID',
        'maquiladoraId'
    ];
}
