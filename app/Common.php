<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('can')) {
    function can(string $perm): bool
    {
        // Si existe el helper centralizado, delegar a can_menu() para evitar duplicación
        if (function_exists('can_menu')) {
            return can_menu($perm);
        }
        $roles = (array)(session()->get('role_names') ?? []);
        $roles = array_map(static function($r){ return mb_strtolower((string)$r); }, $roles);
        foreach (['administrador','jefe','superadmin'] as $super) {
            if (in_array($super, $roles, true)) return true;
        }
        $map = [
            'menu.catalogo_disenos'      => ['diseñador'],
            'menu.pedidos'               => ['empleado','envios','inspector','diseñador','calidad'],
            'menu.ordenes'               => ['empleado','inspector','rh'],
            'menu.produccion'            => ['empleado','inspector','diseñador'],
            'menu.ordenes_clientes'      => ['empleado','envios','calidad','almacenista','rh','inspector','diseñador'],
            'menu.muestras'              => ['diseñador','calidad','inspector'],
            'menu.inspeccion'            => ['inspector','calidad'],
            'menu.wip'                   => ['inspector','empleado'],
            'menu.incidencias'           => ['inspector','calidad','empleado','almacenista'],
            'menu.planificacion_materiales'=> ['rh','almacenista'],
            'menu.desperdicios'          => ['calidad','diseñador','almacenista'],
            'menu.inv_maquinas'          => ['almacenista'],
            'menu.mant_correctivo'       => ['almacenista'],
            'menu.logistica_preparacion' => ['envios','almacenista','calidad'],
            'menu.logistica_gestion'     => ['envios','almacenista','calidad'],
            'menu.logistica_documentos'  => ['envios','almacenista','calidad'],
            'menu.inventario_almacen'    => ['almacenista'],
            'menu.reportes'              => ['rh'],
            'menu.roles'                 => ['rh'],
            'menu.usuarios'              => ['rh'],
            'menu.maquiladora'           => ['rh', 'administrador', 'jefe'],
            // New permissions for all routes with static filters
            'menu.clientes'              => ['administrador','jefe','empleado','corte','envios','calidad','almacenista','rh','inspector','diseñador'],
            'menu.incidencias_modal'     => ['administrador','jefe','empleado','almacenista','calidad','inspector','diseñador'],
            'menu.mantenimiento_inventario' => ['administrador','jefe','almacenista'],
            'menu.maquinaria_crud'       => ['administrador','jefe','almacenista'],
            'menu.embarques_crud'        => ['administrador','jefe','envios','almacenista','calidad'],
            'menu.envios_crud'           => ['administrador','jefe','envios','almacenista','calidad'],
            'menu.facturacion'           => ['administrador','jefe','envios','almacenista','calidad'],
            'menu.facturacion_demo'      => ['administrador','jefe','envios','almacenista','calidad'],
            'menu.modulo1_pagos'         => ['administrador','jefe','rh'],
            'menu.aduanas'               => ['administrador','jefe','envios','almacenista','calidad'],
            'menu.etiquetas_embarque'    => ['administrador','jefe','envios','almacenista','calidad'],
            'menu.calidad_desperdicios'  => ['administrador','jefe','calidad','almacenista','diseñador'],
            'menu.calidad_reprocesos'    => ['administrador','jefe','calidad','almacenista','diseñador'],
        ];
        if (isset($map[$perm])) {
            $allowed = $map[$perm];
            if ($allowed === []) return false;
            foreach ($allowed as $r) if (in_array($r, $roles, true)) return true;
            return false;
        }
        return true;
    }
}
