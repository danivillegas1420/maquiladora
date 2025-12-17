<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCantidadBultosToOrdenProduccion extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('cantidadBultos', 'orden_produccion')) {
            return;
        }

        $this->forge->addColumn('orden_produccion', [
            'cantidadBultos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'cantidadPlan',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->fieldExists('cantidadBultos', 'orden_produccion')) {
            return;
        }

        $this->forge->dropColumn('orden_produccion', 'cantidadBultos');
    }
}
