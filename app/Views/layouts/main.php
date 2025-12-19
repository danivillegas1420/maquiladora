<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="theme-color" content="#ffffff">
    <?php if (function_exists('csrf_token')): ?>
        <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <?php endif; ?>

    <title><?= esc($title ?? 'Maquiladora') ?></title>

    <!-- Custom fonts for this template -->
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css?v=<?= time() ?>" rel="stylesheet">

    <!-- FontAwesome (temporal para compatibilidad con sb-admin-2.min.css) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css?v=<?= time() ?>" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css?v=<?= time() ?>">

    <!-- Animate CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css?v=<?= time() ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom styles for this template-->
    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/maquila.css') ?>" rel="stylesheet">

    <style>
        /* ========== MODO CLARO (Light Theme) ========== */

        /* Textos principales en modo claro - Fondo suave para no lastimar la vista */
        body:not([data-theme="dark"]) {
            background-color: #f5f5f0;
            color: #212529;
        }

        body:not([data-theme="dark"]) .text-gray-800,
        body:not([data-theme="dark"]) h1,
        body:not([data-theme="dark"]) h2,
        body:not([data-theme="dark"]) h3,
        body:not([data-theme="dark"]) h4,
        body:not([data-theme="dark"]) h5,
        body:not([data-theme="dark"]) h6 {
            color: #212529 !important;
        }

        body:not([data-theme="dark"]) .text-gray-600 {
            color: #5a5c69 !important;
        }

        body:not([data-theme="dark"]) p,
        body:not([data-theme="dark"]) span,
        body:not([data-theme="dark"]) div,
        body:not([data-theme="dark"]) label {
            color: #212529;
        }

        /* Topbar en modo claro - fondo suave */
        body:not([data-theme="dark"]) .topbar {
            background-color: #fafaf8 !important;
        }

        /* Specific fix for notification bell icon in light mode */
        body:not([data-theme="dark"]) .topbar .fa-bell,
        body:not([data-theme="dark"]) .topbar .fas.fa-bell {
            color: #212529 !important;
            font-size: 1.1rem !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Alinear ícono de notificaciones con demás elementos */
        .topbar .nav-link[href*="notificaciones"] {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: auto !important;
            padding: 0.5rem 0.75rem !important;
            margin-top: 12px !important;
            vertical-align: middle !important;
        }

        .topbar .nav-link[href*="notificaciones"] .bi-bell-fill {
            font-size: 1.1rem !important;
            line-height: 1 !important;
            vertical-align: middle !important;
            margin: 0 !important;
            position: relative !important;
            top: 3px !important;
        }

        /* Ensure notification link is always visible */
        body:not([data-theme="dark"]) .topbar .nav-link[href*="notificaciones"] {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        body:not([data-theme="dark"]) .topbar .nav-link[href*="notificaciones"] .fa-bell,
        body:not([data-theme="dark"]) .topbar .nav-link[href*="notificaciones"] .fas.fa-bell {
            color: #212529 !important;
            font-size: 1.1rem !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        body[data-theme="dark"] .topbar .nav-link[href*="notificaciones"] .fa-bell,
        body[data-theme="dark"] .topbar .nav-link[href*="notificaciones"] .fas.fa-bell {
            color: #f8f9fa !important;
            font-size: 1.1rem !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Sidebar en modo claro */
        body:not([data-theme="dark"]) .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        body:not([data-theme="dark"]) .sidebar .nav-link:hover {
            color: #ffffff !important;
        }

        body:not([data-theme="dark"]) .sidebar .collapse-item {
            color: #3a3b45 !important;
        }

        body:not([data-theme="dark"]) .sidebar .collapse-item:hover {
            color: #2e59d9 !important;
        }

        body:not([data-theme="dark"]) .sidebar .sidebar-heading {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Tablas en modo claro */
        body:not([data-theme="dark"]) table,
        body:not([data-theme="dark"]) .table {
            color: #212529 !important;
            background-color: #fafaf8;
        }

        /* Responsive tables (mobile): evita que las tablas se salgan del contenedor */
        @media (max-width: 768px) {
            .table-responsive,
            .dataTables_wrapper {
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table.dataTable,
            .table {
                width: 100% !important;
            }

            table.dataTable th,
            table.dataTable td,
            .table th,
            .table td {
                white-space: nowrap;
            }

            /* Controles de DataTables: que no rompan el layout en móvil */
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none !important;
                text-align: left !important;
                width: 100% !important;
            }

            .dataTables_wrapper .dt-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: .25rem;
                margin-bottom: .5rem;
            }

            .dataTables_wrapper .dt-buttons .btn {
                flex: 1 1 auto;
            }
        }

        body:not([data-theme="dark"]) table thead th,
        body:not([data-theme="dark"]) .table thead th {
            color: #212529 !important;
            background-color: #f0f0eb;
        }

        body:not([data-theme="dark"]) table tbody td,
        body:not([data-theme="dark"]) .table tbody td {
            color: #212529 !important;
        }

        body:not([data-theme="dark"]) .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f5f5f0;
        }

        body:not([data-theme="dark"]) .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.075);
            color: #ffffff !important;
        }

        /* Cards en modo claro - fondo suave */
        body:not([data-theme="dark"]) .card {
            background-color: #fafaf8;
            color: #212529;
        }

        body:not([data-theme="dark"]) .card-header {
            color: #212529 !important;
            background-color: #f0f0eb;
        }

        body:not([data-theme="dark"]) .card-body {
            color: #212529;
        }

        body:not([data-theme="dark"]) .bg-white {
            background-color: #fafaf8 !important;
        }

        /* Formularios en modo claro - fondo suave */
        body:not([data-theme="dark"]) .form-control,
        body:not([data-theme="dark"]) .form-select {
            color: #212529 !important;
            background-color: #fafaf8;
            border-color: #d1d3e2;
        }

        body:not([data-theme="dark"]) .form-control:focus,
        body:not([data-theme="dark"]) .form-select:focus {
            background-color: #ffffff;
            border-color: #4e73df;
        }

        body:not([data-theme="dark"]) .form-control::placeholder {
            color: #858796 !important;
        }

        /* Dropdown menus en modo claro - fondo suave */
        body:not([data-theme="dark"]) .dropdown-menu {
            background-color: #fafaf8;
        }

        body:not([data-theme="dark"]) .dropdown-item {
            color: #3a3b45 !important;
        }

        body:not([data-theme="dark"]) .dropdown-item:hover {
            color: #2e59d9 !important;
            background-color: #f0f0eb;
        }

        /* Modals en modo claro - fondo suave */
        body:not([data-theme="dark"]) .modal-content {
            background-color: #fafaf8;
        }

        body:not([data-theme="dark"]) .modal-header {
            background-color: #f0f0eb;
        }

        /* Footer en modo claro - fondo suave */
        body:not([data-theme="dark"]) .sticky-footer {
            background-color: #fafaf8 !important;
        }

        /* ========== MODO OSCURO (Dark Theme) ========== */

        /* Textos principales en modo oscuro */
        body[data-theme="dark"] {
            background-color: #1a1d20;
            color: #f8f9fa;
        }

        body[data-theme="dark"] .text-gray-800,
        body[data-theme="dark"] h1,
        body[data-theme="dark"] h2,
        body[data-theme="dark"] h3,
        body[data-theme="dark"] h4,
        body[data-theme="dark"] h5,
        body[data-theme="dark"] h6 {
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] .text-gray-600 {
            color: #cbd3da !important;
        }

        body[data-theme="dark"] p,
        body[data-theme="dark"] span,
        body[data-theme="dark"] div,
        body[data-theme="dark"] label {
            color: #f8f9fa;
        }

        /* Sidebar en modo oscuro */
        body[data-theme="dark"] .sidebar {
            background: linear-gradient(180deg, #1e3a5f 10%, #2c3e50 100%);
        }

        body[data-theme="dark"] .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        body[data-theme="dark"] .sidebar .nav-link:hover {
            color: #ffffff !important;
        }

        body[data-theme="dark"] .sidebar .collapse-item {
            color: #e9ecef !important;
        }

        body[data-theme="dark"] .sidebar .collapse-item:hover {
            color: #4e73df !important;
            background-color: rgba(78, 115, 223, 0.1);
        }

        body[data-theme="dark"] .sidebar .sidebar-heading {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        body[data-theme="dark"] .sidebar .bg-white {
            background-color: #2c3e50 !important;
        }

        /* Topbar en modo oscuro */
        body[data-theme="dark"] .topbar {
            background-color: #2c3e50 !important;
        }

        body[data-theme="dark"] .topbar .nav-link {
            color: #f8f9fa !important;
        }

        /* Tablas en modo oscuro */
        body[data-theme="dark"] table,
        body[data-theme="dark"] .table {
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] table thead th,
        body[data-theme="dark"] .table thead th {
            color: #f8f9fa !important;
            background-color: #2c3e50;
            border-color: #495057;
        }

        body[data-theme="dark"] table tbody td,
        body[data-theme="dark"] .table tbody td {
            color: #f8f9fa !important;
            border-color: #495057;
        }

        body[data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.03);
        }

        body[data-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.075);
            color: #ffffff !important;
        }

        /* Cards en modo oscuro */
        body[data-theme="dark"] .card {
            background-color: #2c3e50;
            color: #f8f9fa;
            border-color: #495057;
        }

        body[data-theme="dark"] .card-header {
            color: #f8f9fa !important;
            background-color: #34495e;
            border-color: #495057;
        }

        body[data-theme="dark"] .card-body {
            color: #f8f9fa;
        }

        body[data-theme="dark"] .bg-white {
            background-color: #2c3e50 !important;
        }

        /* Formularios en modo oscuro */
        body[data-theme="dark"] .form-control,
        body[data-theme="dark"] .form-select {
            color: #f8f9fa !important;
            background-color: #34495e;
            border-color: #495057;
        }

        body[data-theme="dark"] .form-control:focus,
        body[data-theme="dark"] .form-select:focus {
            background-color: #3d5a80;
            border-color: #4e73df;
            color: #ffffff !important;
        }

        body[data-theme="dark"] .form-control::placeholder {
            color: #adb5bd !important;
        }

        /* Dropdown menus en modo oscuro */
        body[data-theme="dark"] .dropdown-menu {
            background-color: #2c3e50;
            border-color: #495057;
        }

        body[data-theme="dark"] .dropdown-item {
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] .dropdown-item:hover,
        body[data-theme="dark"] .dropdown-item:focus {
            color: #ffffff !important;
            background-color: #34495e;
        }

        body[data-theme="dark"] .dropdown-divider {
            border-color: #495057;
        }

        /* Borders en modo oscuro */
        body[data-theme="dark"] .border {
            border-color: #495057 !important;
        }

        body[data-theme="dark"] .border-bottom {
            border-bottom-color: #495057 !important;
        }

        /* Badges en modo oscuro */
        body[data-theme="dark"] .badge {
            color: #ffffff;
        }

        /* Modals en modo oscuro */
        body[data-theme="dark"] .modal-content {
            background-color: #2c3e50;
            color: #f8f9fa;
            border-color: #495057;
        }

        body[data-theme="dark"] .modal-header {
            border-color: #495057;
        }

        body[data-theme="dark"] .modal-footer {
            border-color: #495057;
        }

        body[data-theme="dark"] .modal-title {
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] .close,
        body[data-theme="dark"] .btn-close {
            color: #f8f9fa;
            filter: invert(1);
        }

        /* ========== ESTILOS COMUNES ========== */

        /* Ajuste del logo en sidebar */
        .sidebar-brand-icon img {
            max-width: 50px;
            height: auto;
        }

        /* Mejorar visibilidad de iconos */
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }

        /* Asegurar que los links sean visibles */
        body[data-theme="dark"] a:not(.btn):not(.nav-link):not(.dropdown-item):not(.collapse-item) {
            color: #4e73df;
        }

        body[data-theme="dark"] a:not(.btn):not(.nav-link):not(.dropdown-item):not(.collapse-item):hover {
            color: #6c9aff;
        }

        /* Footer en modo oscuro */
        body[data-theme="dark"] .sticky-footer {
            background-color: #2c3e50 !important;
        }

        body[data-theme="dark"] .sticky-footer .copyright {
            color: #adb5bd !important;
        }

        /* GLOBAL FIX FOR WHITE TEXT IN DARK MODE */
        body[data-theme="dark"] * {
            color: #f8f9fa !important;
        }

        /* Aggressive fix for any white backgrounds */
        body[data-theme="dark"] div,
        body[data-theme="dark"] .container-fluid,
        body[data-theme="dark"] .row,
        body[data-theme="dark"] .col,
        body[data-theme="dark"] .bg-white,
        body[data-theme="dark"] .bg-light {
            background-color: transparent !important;
            background: transparent !important;
        }

        /* Only restore backgrounds for specific elements that need them */
        body[data-theme="dark"] .card,
        body[data-theme="dark"] .card-body,
        body[data-theme="dark"] .kpi-card,
        body[data-theme="dark"] .chart-card,
        body[data-theme="dark"] .sidebar,
        body[data-theme="dark"] .topbar,
        body[data-theme="dark"] .dropdown-menu,
        body[data-theme="dark"] .breadcrumb {
            background-color: #1e1e1e !important;
        }

        body[data-theme="dark"] .breadcrumb {
            background-color: #2a2a2a !important;
        }

        /* Preserve colors for specific elements */
        body[data-theme="dark"] .bg-primary *,
        body[data-theme="dark"] .bg-success *,
        body[data-theme="dark"] .bg-warning *,
        body[data-theme="dark"] .bg-danger *,
        body[data-theme="dark"] .bg-info *,
        body[data-theme="dark"] .btn-primary *,
        body[data-theme="dark"] .btn-success *,
        body[data-theme="dark"] .btn-warning *,
        body[data-theme="dark"] .btn-danger *,
        body[data-theme="dark"] .btn-info *,
        body[data-theme="dark"] .navbar-custom * {
            color: inherit !important;
        }

        /* Fix form labels and information fields in dark mode */
        body[data-theme="dark"] label,
        body[data-theme="dark"] .form-label,
        body[data-theme="dark"] dt,
        body[data-theme="dark"] .info-label,
        body[data-theme="dark"] .field-label,
        body[data-theme="dark"] th,
        body[data-theme="dark"] .table th,
        body[data-theme="dark"] .text-dark,
        body[data-theme="dark"] .fw-bold {
            color: #f8f9fa !important;
        }

        /* Fix form controls and inputs */
        body[data-theme="dark"] .form-control,
        body[data-theme="dark"] .form-control::placeholder,
        body[data-theme="dark"] input,
        body[data-theme="dark"] select,
        body[data-theme="dark"] textarea {
            color: #f8f9fa !important;
            background-color: #2a2a2a !important;
            border-color: #444 !important;
        }

        /* Fix readonly/disabled inputs */
        body[data-theme="dark"] .form-control[readonly],
        body[data-theme="dark"] .form-control:disabled,
        body[data-theme="dark"] .form-control-plaintext {
            background-color: #343a40 !important;
            color: #f8f9fa !important;
            border-color: #454d55 !important;
        }

        /* Links should be blue */
        body[data-theme="dark"] a {
            color: #4dabf7 !important;
        }

        body[data-theme="dark"] a:hover {
            color: #339af0 !important;
        }

        /* Fix cards and containers */
        body[data-theme="dark"] .card,
        body[data-theme="dark"] .card-body {
            background-color: #1e1e1e !important;
            color: #f8f9fa !important;
        }

        /* Fix all buttons in dark mode */
        body[data-theme="dark"] .btn,
        body[data-theme="dark"] button {
            background-color: #343a40 !important;
            color: #f8f9fa !important;
            border-color: #454d55 !important;
        }

        body[data-theme="dark"] .btn:hover,
        body[data-theme="dark"] button:hover {
            background-color: #454d55 !important;
            border-color: #5a6268 !important;
            color: #ffffff !important;
        }

        /* Primary buttons keep their color but with better contrast */
        body[data-theme="dark"] .btn-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #ffffff !important;
        }

        body[data-theme="dark"] .btn-primary:hover {
            background-color: #0b5ed7 !important;
            border-color: #0a58ca !important;
        }

        /* Success buttons */
        body[data-theme="dark"] .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }

        /* Warning buttons */
        body[data-theme="dark"] .btn-warning {
            background-color: #fd7e14 !important;
            border-color: #fd7e14 !important;
            color: #000000 !important;
        }

        /* Danger buttons */
        body[data-theme="dark"] .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
        }

        /* Fix any inline white text styles */
        body[data-theme="dark"] [style*="color: white"],
        body[data-theme="dark"] [style*="color:#fff"],
        body[data-theme="dark"] [style*="color: #fff"],
        body[data-theme="dark"] .text-white {
            color: #f8f9fa !important;
        }

        /* Fix theme toggle button and navbar icons */
        body[data-theme="dark"] .js-theme-toggle,
        body[data-theme="dark"] .js-theme-toggle .js-theme-icon,
        body[data-theme="dark"] .topbar .nav-link,
        body[data-theme="dark"] .navbar-light .nav-link {
            background-color: transparent !important;
            color: #adb5bd !important;
            border: 1px solid transparent !important;
        }

        body[data-theme="dark"] .js-theme-toggle:hover,
        body[data-theme="dark"] .topbar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8f9fa !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        /* Fix notification bell and user name */
        body[data-theme="dark"] .topbar .fas,
        body[data-theme="dark"] .topbar .fa,
        body[data-theme="dark"] .topbar .text-gray-600,
        body[data-theme="dark"] .topbar .small,
        body[data-theme="dark"] .topbar .dropdown-toggle {
            color: #f8f9fa !important;
        }

        /* Specific fix for notification bell icon */
        body[data-theme="dark"] .topbar .fa-bell,
        body[data-theme="dark"] .topbar .fas.fa-bell {
            color: #ffffff !important;
            font-size: 1.1rem !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Ensure notification link is always visible in dark mode */
        body[data-theme="dark"] .topbar .nav-link[href*="notificaciones"] {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Ensure all topbar elements are visible */
        body[data-theme="dark"] .navbar-nav .nav-link *,
        body[data-theme="dark"] .navbar-nav .dropdown-item {
            color: #f8f9fa !important;
        }

        /* Fix topbar (navigation bar) in dark mode */
        body[data-theme="dark"] .topbar {
            background-color: #1e1e1e !important;
            border-bottom-color: #333 !important;
        }

        body[data-theme="dark"] .topbar .navbar-brand,
        body[data-theme="dark"] .topbar .nav-link,
        body[data-theme="dark"] .topbar .text-gray-600,
        body[data-theme="dark"] .topbar .small {
            color: #f8f9fa !important;
        }

        /* Fix breadcrumb area */
        body[data-theme="dark"] .breadcrumb {
            background-color: #2a2a2a !important;
            border-radius: .35rem;
            padding: .75rem 1rem;
        }

        body[data-theme="dark"] .breadcrumb-item,
        body[data-theme="dark"] .breadcrumb-item + .breadcrumb-item::before {
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] .breadcrumb-item.active {
            color: #ced4da !important;
        }

        /* Fix any white containers that might hold breadcrumbs */
        body[data-theme="dark"] .container-fluid .bg-white,
        body[data-theme="dark"] .bg-white .breadcrumb,
        body[data-theme="dark"] .bg-light .breadcrumb {
            background-color: #2a2a2a !important;
        }

        /* Force breadcrumb text to be visible regardless of container */
        body[data-theme="dark"] .breadcrumb *,
        body[data-theme="dark"] .breadcrumb-item,
        body[data-theme="dark"] .breadcrumb-item a {
            color: #f8f9fa !important;
        }

        /* Fix any white backgrounds in dark mode */
        body[data-theme="dark"] .bg-white {
            background-color: #1e1e1e !important;
            color: #f8f9fa !important;
        }

        /* Ensure dropdown menus are dark */
        body[data-theme="dark"] .dropdown-menu {
            background-color: #2a2a2a !important;
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] .dropdown-item {
            color: #f8f9fa !important;
        }

        body[data-theme="dark"] .dropdown-item:hover {
            background-color: #404040 !important;
            color: #ffffff !important;
        }
    </style>

    <?= $this->renderSection('head') ?>
</head>

<body id="page-top">
    <?php
    // --- Defaults y cálculos de visibilidad (una sola vez) ---
    $notifCount = $notifCount ?? 0;

    $secGestion = can('menu.catalogo_disenos') || can('menu.pedidos') || can('menu.ordenes') || can('menu.produccion') || can('menu.ordenes_clientes');
    $secMuestrasInspeccion = can('menu.muestras') || can('menu.inspeccion');
    $secIncidencias = can('menu.incidencias') || can('menu.wip');
    $secPlanificacion = can('menu.planificacion_materiales') || can('menu.desperdicios') || can('menu.proveedores');
    $secMantenimiento = can('menu.inv_maquinas') || can('menu.mant_correctivo');
    $secLogistica = can('menu.logistica_preparacion') || can('menu.logistica_gestion') || can('menu.logistica_documentos') || can('menu.inventario_almacen');
    $secAdmin = can('menu.reportes') || can('menu.roles') || can('menu.usuarios');
    ?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center"
                href="<?= base_url('modulo3/dashboard') ?>">
                <div class="sidebar-brand-icon">
                    <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo">
                </div>
                <div class="sidebar-brand-text mx-3">Maquiladora</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('modulo3/dashboard') ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <?php if ($secGestion): ?>
                <div class="sidebar-heading">Gestión</div>
            <?php endif; ?>

            <!-- Mi Maquiladora -->
            <?php if (can('menu.maquiladora')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('maquiladora') ?>">
                        <i class="bi bi-building"></i>
                        <span>Mi Maquiladora</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Clientes -->
            <?php if (can('menu.ordenes_clientes')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('clientes') ?>">
                        <i class="bi bi-people"></i>
                        <span>Clientes</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Catálogo de Diseños -->
            <?php if (can('menu.catalogo_disenos')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo2/catalogodisenos') ?>">
                        <i class="bi bi-brush"></i>
                        <span>Catálogo de Diseños</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Pedidos -->
            <?php if (can('menu.pedidos')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo1/pedidos') ?>">
                        <i class="bi bi-bag"></i>
                        <span>Pedidos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Pagos -->
            <?php if (can('menu.pagos')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo1/pagos') ?>">
                        <i class="bi bi-credit-card"></i>
                        <span>Pagos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Órdenes en proceso -->
            <?php if (can('menu.ordenes')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo1/ordenes') ?>">
                        <i class="bi bi-card-checklist"></i>
                        <span>Órdenes en proceso</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Producción -->
            <?php if (can('menu.produccion')): ?>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProduccion"
                        aria-expanded="true" aria-controls="collapseProduccion">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span>Producción</span>
                    </a>
                    <div id="collapseProduccion" class="collapse" aria-labelledby="headingProduccion"
                        data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item" href="<?= base_url('modulo1/produccion') ?>">Gestión Producción</a>
                            <a class="collapse-item" href="<?= base_url('modulo3/cortes') ?>">Gestión de Cortes</a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <?php if ($secGestion && $secMuestrasInspeccion): ?>
                <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Heading - Calidad -->
            <?php if ($secMuestrasInspeccion): ?>
                <div class="sidebar-heading">Calidad</div>
            <?php endif; ?>

            <!-- Muestras -->
            <?php if (can('menu.muestras')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('muestras') ?>">
                        <i class="bi bi-palette2"></i>
                        <span>Muestras</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Inspección -->
            <?php if (can('menu.inspeccion')): ?>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseInspeccion"
                        aria-expanded="true" aria-controls="collapseInspeccion">
                        <i class="bi bi-search"></i>
                        <span>Inspección</span>
                    </a>
                    <div id="collapseInspeccion" class="collapse" aria-labelledby="headingInspeccion"
                        data-bs-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item" href="<?= base_url('modulo3/control-bultos') ?>">Inspección Bultos</a>
                            <a class="collapse-item" href="<?= base_url('modulo3/inspeccion') ?>">Inspección Producto</a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <!-- Incidencias -->
            <?php if (can('menu.incidencias')): ?>
                <?php
                $roleName = current_role_name();
                $roleNorm = $roleName ? mb_strtolower(trim($roleName)) : '';
                ?>
                <li class="nav-item">
                    <?php if ($roleNorm === 'empleado'): ?>
                        <a class="nav-link js-open-incidencia-modal" href="#">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Incidencias</span>
                        </a>
                    <?php else: ?>
                        <a class="nav-link" href="<?= base_url('modulo3/incidencias') ?>">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Incidencias</span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <?php if ($secMuestrasInspeccion && $secPlanificacion): ?>
                <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Heading - Planificación -->
            <?php if ($secPlanificacion): ?>
                <div class="sidebar-heading">Planificación</div>
            <?php endif; ?>

            <!-- MRP -->
            <?php if (can('menu.planificacion_materiales')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/mrp') ?>">
                        <i class="bi bi-diagram-2"></i>
                        <span>Planificación Materiales</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Proveedores -->
            <?php if (can('menu.proveedores')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('proveedores') ?>">
                        <i class="bi bi-truck-front"></i>
                        <span>Proveedores</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Desperdicios -->
            <?php if (can('menu.desperdicios')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/desperdicios') ?>">
                        <i class="bi bi-recycle"></i>
                        <span>Desperdicios</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <?php if ($secPlanificacion && $secMantenimiento): ?>
                <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Heading - Mantenimiento -->
            <?php if ($secMantenimiento): ?>
                <div class="sidebar-heading">Mantenimiento</div>
            <?php endif; ?>

            <!-- Inventario Máquinas -->
            <?php if (can('menu.inv_maquinas')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/mantenimiento_inventario') ?>">
                        <i class="bi bi-tools"></i>
                        <span>Inventario Máquinas</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Calendario Mantenimiento -->
            <?php if ($secMantenimiento): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('mtto/calendario') ?>">
                        <i class="bi bi-calendar3"></i>
                        <span>Calendario Mtto</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Mantenimiento Correctivo -->
            <?php if (can('menu.mant_correctivo')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/mantenimiento_correctivo') ?>">
                        <i class="bi bi-wrench-adjustable-circle"></i>
                        <span>Mant. Correctivo</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <?php if ($secMantenimiento && $secLogistica): ?>
                <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Heading - Logística -->
            <?php if ($secLogistica): ?>
                <div class="sidebar-heading">Logística</div>
            <?php endif; ?>

            <!-- Preparación Envíos -->
            <?php if (can('menu.logistica_preparacion')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/logistica_preparacion') ?>">
                        <i class="bi bi-box-seam"></i>
                        <span>Prep. Envíos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Gestión Envíos -->
            <?php if (can('menu.logistica_gestion')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/logistica_gestion') ?>">
                        <i class="bi bi-truck"></i>
                        <span>Gestión Envíos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Documentos Embarque -->
            <?php if (can('menu.logistica_documentos')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/logistica_documentos') ?>">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Docs. Embarque</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Inventario Almacén -->
            <?php if (can('menu.inventario_almacen')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('almacen/inventario') ?>">
                        <i class="bi bi-boxes"></i>
                        <span>Inventario Almacén</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <?php if ($secLogistica && $secAdmin): ?>
                <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Heading - Administración -->
            <?php if ($secAdmin): ?>
                <div class="sidebar-heading">Administración</div>
            <?php endif; ?>

            <!-- Reportes -->
            <?php if (can('menu.reportes')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo3/reportes') ?>">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Reportes</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Roles -->
            <?php if (can('menu.roles')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo11/roles') ?>">
                        <i class="bi bi-person-gear"></i>
                        <span>Roles</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Usuarios -->
            <?php if (can('menu.usuarios')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('modulo11/usuarios') ?>">
                        <i class="bi bi-shield-lock"></i>
                        <span>Gestión Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="bi bi-list"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link" href="<?= base_url('modulo3/notificaciones2') ?>">
                                <i class="bi bi-bell-fill"></i>
                                <!-- Counter - Alerts -->
                                <?php if ($notifCount > 0): ?>
                                    <span class="badge badge-danger badge-counter"><?= esc($notifCount) ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Nav Item - Theme Toggle -->
                        <li class="nav-item">
                            <button type="button" class="btn btn-link nav-link js-theme-toggle" title="Cambiar tema">
                                <i class="bi bi-moon-fill js-theme-icon"></i>
                            </button>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= esc(session()->get('user_name') ?? session()->get('username') ?? 'Usuario') ?>
                                </span>
                                <i class="bi bi-person-circle fs-4"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?= base_url('modulo1/perfilempleado') ?>">
                                    <i class="bi bi-person me-2"></i>
                                    Perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= base_url('logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Cerrar sesión
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?= $this->renderSection('content') ?>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span> Maquiladora</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- Scripts: cargados al final para mejor performance -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('js/sb-admin-2.min.js') ?>"></script>

    <script>
        // Evitar que el sidebar se auto-colapse en móvil por eventos de resize
        // (en algunos navegadores móviles el viewport cambia al mostrar/ocultar la barra de dirección).
        (function ($) {
            if (!$) return;
            $(function () {
                $(window).off('resize');
                $(window).on('resize', function () {
                    if ($(window).width() < 768) {
                        $('.sidebar .collapse').collapse('hide');
                    }
                });
            });
        })(window.jQuery);
    </script>

    <!-- Modo oscuro / claro -->
    <script>
        (function () {
            const STORAGE_KEY = 'theme';
            function getPreferredTheme() {
                try {
                    const stored = localStorage.getItem(STORAGE_KEY);
                    if (stored === 'light' || stored === 'dark') return stored;
                } catch (e) {
                    // ignore storage errors
                }
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return 'dark';
                }
                return 'light';
            }

            function applyTheme(theme) {
                const body = document.body;
                if (!body) return;
                body.setAttribute('data-theme', theme);
                document.querySelectorAll('.js-theme-icon').forEach(function (icon) {
                    icon.classList.toggle('bi-moon-fill', theme === 'light');
                    icon.classList.toggle('bi-sun-fill', theme === 'dark');
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                const initial = getPreferredTheme();
                applyTheme(initial);

                // Soportar varios toggles (escritorio + móvil)
                document.querySelectorAll('.js-theme-toggle').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const current = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                        const next = current === 'dark' ? 'light' : 'dark';
                        try { localStorage.setItem(STORAGE_KEY, next); } catch (e) { }
                        applyTheme(next);
                    });
                });

                if (window.matchMedia) {
                    const mq = window.matchMedia('(prefers-color-scheme: dark)');
                    if (mq.addEventListener) {
                        mq.addEventListener('change', function (e) {
                            try {
                                const stored = localStorage.getItem(STORAGE_KEY);
                                if (stored === 'light' || stored === 'dark') return; // usuario ya eligió
                            } catch (err) {
                                // ignore
                            }
                            applyTheme(e.matches ? 'dark' : 'light');
                        });
                    }
                }
            });
        })();
    </script>

    <!-- Notification Polling System -->
    <script src="<?= base_url('js/notification-poller.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>