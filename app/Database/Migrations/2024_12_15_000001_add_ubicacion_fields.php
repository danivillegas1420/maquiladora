<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUbicacionFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('ubicacion');

        $columnsToAdd = [];

        if (!in_array('pasillo', $fields)) {
            $columnsToAdd['pasillo'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'codigo'
            ];
        }

        if (!in_array('estante', $fields)) {
            $columnsToAdd['estante'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'pasillo'
            ];
        }

        if (!in_array('nivel', $fields)) {
            $columnsToAdd['nivel'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'estante'
            ];
        }

        if (!in_array('letra', $fields)) {
            $columnsToAdd['letra'] = [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'after' => 'nivel'
            ];
        }

        if (!in_array('descripcion', $fields)) {
            $columnsToAdd['descripcion'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'letra'
            ];
        }

        if (!empty($columnsToAdd)) {
            $this->forge->addColumn('ubicacion', $columnsToAdd);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('ubicacion');

        $columnsToDrop = [];
        foreach (['pasillo', 'estante', 'nivel', 'letra', 'descripcion'] as $col) {
            if (in_array($col, $fields)) {
                $columnsToDrop[] = $col;
            }
        }

        if (!empty($columnsToDrop)) {
            $this->forge->dropColumn('ubicacion', $columnsToDrop);
        }
    }
}
