<?php

namespace App\Controllers;

class ClientesPage extends BaseController
{
    public function index()
    {
        if (!can('menu.clientes')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        return view('modulos/agregar_cliente', [
            'title' => 'Clientes',
        ]);
    }
}
