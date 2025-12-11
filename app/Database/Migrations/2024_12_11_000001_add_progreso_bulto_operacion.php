<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgresoBultoOperacion extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'bultoId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'operacionControlId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'completado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'cantidad_completada' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'empleadoId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'fecha_completado' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('bultoId');
        $this->forge->addKey('operacionControlId');
        $this->forge->addUniqueKey(['bultoId', 'operacionControlId'], 'unique_bulto_operacion');
        $this->forge->addForeignKey('bultoId', 'bultos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('operacionControlId', 'operaciones_control', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('progreso_bulto_operacion', true);
    }

    public function down()
    {
        $this->forge->dropTable('progreso_bulto_operacion', true);
    }
}
