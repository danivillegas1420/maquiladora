<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolNotificacionTable extends Migration
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
            'rol_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'tipo_notificacion' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => false,
            ],
            'maquiladoraID' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['rol_id', 'tipo_notificacion'], false, true); // Unique key
        $this->forge->addForeignKey('rol_id', 'rol', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rol_notificacion');
    }

    public function down()
    {
        $this->forge->dropTable('rol_notificacion');
    }
}
