<?= $this->extend('layouts/main') ?>

<?php // --- Estilos específicos del dashboard (AdminLTE Inspired) --- ?>
<?= $this->section('head') ?>
<style>
    /* AdminLTE 3 Variables & Base */
    :root {
        --info: #17a2b8;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --primary: #007bff;
        --secondary: #6c757d;
    }

    body {
        background-color: #f4f6f9;
    }

    /* --- Small Box --- */
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: block;
        margin-bottom: 20px;
        position: relative;
        color: #333;
        background-color: #fff;
        border: 1px solid #e0e0e0;
        overflow: hidden;
        transition: all 0.3s ease-in-out;
    }
    
    .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .small-box>.inner {
        padding: 10px;
    }

    .small-box h3 {
        font-size: 2rem;
        font-weight: 700;
        margin: 10px 0 5px 0;
        white-space: nowrap;
        padding: 0;
        color: #333;
    }

    .small-box p {
        font-size: 1rem;
        color: #666;
        margin-bottom: 10px;
    }

    .small-box .icon {
        color: rgba(0, 0, 0, .15);
        z-index: 0;
    }

    .small-box .icon>i {
        font-size: 70px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: all .3s linear;
        opacity: 0.4;
    }

    .small-box:hover .icon>i {
        transform: scale(1.1);
    }

    .small-box>.small-box-footer {
        background-color: rgba(0, 0, 0, .05);
        color: #555;
        display: block;
        padding: 8px 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 10;
        font-weight: 500;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .small-box>.small-box-footer:hover {
        background-color: rgba(0, 0, 0, .15);
        color: #5f5353ff;
    }

    /* Colors */
    .bg-info {
        background-color: #17a2b8 !important;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #1f2d3d !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    /* --- Card Styles --- */
    .card {
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
        padding: 0.75rem 1.25rem;
        position: relative;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }

    .card-title {
        float: left;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: #2c3e50;
    }

    .card-body {
        flex: 1 1 auto;
        min-height: 1px;
        padding: 1.25rem;
        background-color: #fff;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }

    .card-tools .btn-tool {
        padding: 0.25rem 0.5rem;
        color: #2e3133ff;
        font-size: 0.875rem;
        background: transparent;
        border: 0;
    }

    .card-tools .btn-tool:hover {
        color: #8f979eff;
    }

    /* --- Direct Chat --- */
    .direct-chat-messages {
        transform: translate(0, 0);
        height: 250px;
        overflow: auto;
        padding: 10px;
    }

    .direct-chat-msg {
        margin-bottom: 10px;
    }

    .direct-chat-infos {
        display: block;
        font-size: 0.875rem;
        margin-bottom: 2px;
    }

    .direct-chat-name {
        font-weight: 600;
    }

    .direct-chat-timestamp {
        color: #697582;
    }

    .direct-chat-img {
        border-radius: 50%;
        float: left;
        height: 40px;
        width: 40px;
    }

    .direct-chat-text {
        border-radius: 0.3rem;
        background-color: #a8aab0ff;
        border: 1px solid #d1d5e0ff;
        color: #444;
        margin: 5px 0 0 50px;
        padding: 5px 10px;
        position: relative;
    }

    .direct-chat-text::after,
    .direct-chat-text::before {
        border: solid transparent;
        border-right-color: #3f4144ff;
        content: " ";
        height: 0;
        pointer-events: none;
        position: absolute;
        right: 100%;
        top: 15px;
        width: 0;
    }

    .direct-chat-text::after {
        border-width: 5px;
        margin-top: -5px;
    }

    .direct-chat-text::before {
        border-width: 6px;
        margin-top: -6px;
    }

    /* Right side chat (User) */
    .direct-chat-msg.right .direct-chat-img {
        float: right;
    }

    .direct-chat-msg.right .direct-chat-text {
        background-color: #3c8dbc;
        border-color: #3c8dbc;
        color: #2e2b2bff;
        margin-left: 0;
        margin-right: 50px;
    }

    .direct-chat-msg.right .direct-chat-text::after,
    .direct-chat-msg.right .direct-chat-text::before {
        border-left-color: #3c8dbc;
        border-right-color: transparent;
        left: 100%;
        right: auto;
    }

    .direct-chat-primary .right>.direct-chat-text {
        background-color: #007bff;
        border-color: #007bff;
        color: #3c3939ff;
    }

    .direct-chat-primary .right>.direct-chat-text::after,
    .direct-chat-primary .right>.direct-chat-text::before {
        border-left-color: #007bff;
    }

    /* Tabs in Card Header */
    .card-header.p-0 .nav-pills .nav-link {
        border-radius: 0;
        border-bottom: 0;
        color: #6c757d;
    }

    .card-header.p-0 .nav-pills .nav-link.active {
        background-color: transparent;
        color: #007bff;
        border-bottom: 2px solid #007bff;
    }

    .card-header.p-0 .nav-pills .nav-link:hover {
        color: #007bff;
    }

    /* Chart Heights */
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="content-header mb-3">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard v1</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Small Boxes (Stat box) -->
        <div class="row">
            <!-- Box 1: Órdenes Activas (New Orders) -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="kpi-ordenes-activas"><?= esc($kpis['ordenes_activas'] ?? 0) ?></h3>
                        <p>Órdenes Activas</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-bag"></i>
                    </div>
                    <a href="<?= base_url('modulo3/ordenes') ?>" class="small-box-footer">More info <i
                            class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <!-- Box 2: Tasa de Defectos (Bounce Rate) -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success" id="box-defectos">
                    <div class="inner">
                        <h3 id="kpi-tasa-defectos"><?= esc($kpis['tasa_defectos'] ?? 0) ?><sup style="font-size: 20px">%</sup></h3>
                        <p>Tasa de Defectos</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>
                    <a href="<?= base_url('modulo3/desperdicios') ?>" class="small-box-footer">More info <i
                            class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <!-- Box 3: Producción (User Registrations) -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="kpi-wip-cantidad"><?= esc($kpis['wip_cantidad'] ?? 0) ?></h3>
                        <p>Unidades en Proceso</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-person-add"></i>
                    </div>
                    <a href="<?= base_url('modulo3/wip') ?>" class="small-box-footer">More info <i
                            class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <!-- Box 4: Stock Crítico (Unique Visitors) -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="kpi-stock-critico"><?= esc($kpis['stock_critico'] ?? 0) ?></h3>
                        <p>Stock Crítico</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                    <a href="<?= base_url('almacen/inventario') ?>" class="small-box-footer">More info <i
                            class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <!-- Main Row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-7 connectedSortable">
                <!-- Custom tabs (Charts with tabs)-->
                <div class="card">
                    <div class="card-header d-flex p-0">
                        <h3 class="card-title p-3">
                            <i class="bi bi-pie-chart mr-1"></i>
                            Producción
                        </h3>
                        <ul class="nav nav-pills ms-auto p-2">
                            <li class="nav-item">
                                <a class="nav-link active" href="#revenue-chart" data-bs-toggle="tab">Area</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#sales-chart" data-bs-toggle="tab">Donut</a>
                            </li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <!-- Morris chart - Sales -->
                            <div class="chart tab-pane active" id="revenue-chart"
                                style="position: relative; height: 300px;">
                                <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                            </div>
                            <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                                <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div><!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- DIRECT CHAT -->
                <div class="card direct-chat direct-chat-primary">
                    <div class="card-header">
                        <h3 class="card-title">Notificaciones Recientes</h3>
                        <div class="card-tools">
                            <span title="3 New Messages" class="badge bg-primary" id="notif-badge-count">3</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="bi bi-dash"></i>
                            </button>
                            <button type="button" class="btn btn-tool" title="Contacts" data-widget="chat-pane-toggle">
                                <i class="bi bi-chat-dots"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <!-- Conversations are loaded here -->
                        <div class="direct-chat-messages" id="chat-messages">
                            <!-- Message. Default to the left -->
                            <div class="direct-chat-msg">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-start">Sistema</span>
                                    <span class="direct-chat-timestamp float-end">23 Jan 2:00 pm</span>
                                </div>
                                <!-- /.direct-chat-infos -->
                                <img class="direct-chat-img"
                                    src="https://ui-avatars.com/api/?name=System&background=0D8ABC&color=fff"
                                    alt="message user image">
                                <!-- /.direct-chat-img -->
                                <div class="direct-chat-text">
                                    Bienvenido al nuevo Dashboard estilo AdminLTE!
                                </div>
                                <!-- /.direct-chat-text -->
                            </div>
                            <!-- /.direct-chat-msg -->
                        </div>
                        <!--/.direct-chat-messages-->
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <form action="#" method="post">
                            <div class="input-group">
                                <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                                <span class="input-group-append">
                                    <button type="button" class="btn btn-primary">Send</button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <!-- /.card-footer-->
                </div>
                <!--/.direct-chat -->

            </section>
            <!-- /.Left col -->

            <!-- Right col -->
            <section class="col-lg-5 connectedSortable">

                <!-- Map card (Placeholder for Inventory Health) -->
                <div class="card bg-gradient-primary text-white"
                    style="background: linear-gradient(45deg, #007bff, #0056b3);">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="bi bi-geo-alt mr-1"></i>
                            Inventario
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse"
                                title="Collapse">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="world-map"
                            style="height: 250px; width: 100%; display: flex; align-items: center; justify-content: center;">
                            <canvas id="inventory-chart-canvas" style="max-height: 250px;"></canvas>
                        </div>
                    </div>
                    <!-- /.card-body-->
                    <div class="card-footer bg-transparent border-0">
                        <div class="row">
                            <div class="col-4 text-center">
                                <div id="sparkline-1"></div>
                                <div class="text-white">Stock</div>
                            </div>
                            <div class="col-4 text-center">
                                <div id="sparkline-2"></div>
                                <div class="text-white">Entradas</div>
                            </div>
                            <div class="col-4 text-center">
                                <div id="sparkline-3"></div>
                                <div class="text-white">Salidas</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card -->

                <!-- Calendar (Placeholder) -->
                <div class="card bg-gradient-success text-white"
                    style="background: linear-gradient(45deg, #28a745, #218838);">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="bi bi-calendar-date"></i>
                            Calendario
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                                <i class="bi bi-dash"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div id="calendar" style="width: 100%">
                            <div class="text-center py-4">
                                <h4><?= date('F Y') ?></h4>
                                <div class="display-4"><?= date('d') ?></div>
                                <div><?= date('l') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card -->

            </section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const API_URL = "<?= base_url('api/dashboard') ?>";

        // --- Chart Options (AdminLTE Style) ---
        const areaChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { display: false }, beginAtZero: true }
            },
            elements: {
                line: { tension: 0.4, fill: true },
                point: { radius: 0, hitRadius: 10, hoverRadius: 4 }
            }
        };

        const donutChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'left' }
            }
        };

        // --- Fetch Data ---
        async function loadDashboardData() {
            try {
                const res = await fetch(`${API_URL}?range=30`);
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();

                updateKPIs(data.kpis);
                renderMainCharts(data);
                renderInventoryChart(data.inventario);
                renderNotifications(data.notifications || []);

            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // --- Update KPIs ---
        function updateKPIs(kpis) {
            if (!kpis) return;
            // Mapping API keys to DOM IDs
            const map = {
                'ordenes_activas': 'kpi-ordenes-activas',
                'tasa_defectos': 'kpi-tasa-defectos',
                'wip_cantidad': 'kpi-wip-cantidad',
                'stock_critico': 'kpi-stock-critico'
            };

            for (const [key, val] of Object.entries(kpis)) {
                const el = document.getElementById(map[key]);
                if (el) {
                    el.innerHTML = val + (key === 'tasa_defectos' ? '<sup style="font-size: 20px">%</sup>' : '');
                }
            }

            // Update Bounce Rate color
            const boxDefectos = document.getElementById('box-defectos');
            if (boxDefectos) {
                if (kpis.tasa_defectos > 5) {
                    boxDefectos.classList.remove('bg-success');
                    boxDefectos.classList.add('bg-danger');
                } else {
                    boxDefectos.classList.remove('bg-danger');
                    boxDefectos.classList.add('bg-success');
                }
            }
        }

        // --- Render Main Charts ---
        let revenueChart, salesChart;

        function renderMainCharts(data) {
            // 1. Area Chart (Producción)
            const ctxRevenue = document.getElementById('revenue-chart-canvas').getContext('2d');
            if (revenueChart) revenueChart.destroy();

            revenueChart = new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: data.produccion.labels,
                    datasets: [
                        {
                            label: 'Completadas',
                            backgroundColor: 'rgba(60,141,188,0.9)',
                            borderColor: 'rgba(60,141,188,0.8)',
                            data: data.produccion.datasets[0].data,
                            fill: true
                        },
                        {
                            label: 'Pendientes',
                            backgroundColor: 'rgba(210, 214, 222, 1)',
                            borderColor: 'rgba(210, 214, 222, 1)',
                            data: data.produccion.datasets[1].data,
                            fill: true
                        }
                    ]
                },
                options: areaChartOptions
            });

            // 2. Donut Chart (Logística/Status)
            const ctxSales = document.getElementById('sales-chart-canvas').getContext('2d');
            if (salesChart) salesChart.destroy();

            salesChart = new Chart(ctxSales, {
                type: 'doughnut',
                data: {
                    labels: data.logistica.labels,
                    datasets: [{
                        data: data.logistica.data,
                        backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de']
                    }]
                },
                options: donutChartOptions
            });
        }

        // --- Render Inventory Chart (Right Col) ---
        let inventoryChart;
        function renderInventoryChart(data) {
            const ctx = document.getElementById('inventory-chart-canvas').getContext('2d');
            if (inventoryChart) inventoryChart.destroy();

            if (!data || !data.datasets || !data.datasets[0]) {
                return;
            }

            inventoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Stock',
                        data: data.datasets[0].data,
                        backgroundColor: 'rgba(60,141,188,0.9)', // Azul oscuro
                        borderColor: 'rgba(60,141,188,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#495057' }, grid: { display: false } }, // Texto oscuro
                        y: { ticks: { color: '#495057' }, grid: { color: 'rgba(0,0,0,0.1)' } } // Texto y grid oscuros
                    }
                }
            });
        }

        // --- Render Notifications (Direct Chat) ---
        function renderNotifications(notifs) {
            const container = document.getElementById('chat-messages');
            const badge = document.getElementById('notif-badge-count');

            if (!container || !badge) return;

            if (!notifs || notifs.length === 0) {
                container.innerHTML = '<div class="text-muted text-center p-3">No hay notificaciones nuevas</div>';
                badge.textContent = '0';
                badge.classList.remove('bg-danger', 'bg-warning');
                badge.classList.add('bg-secondary');
                return;
            }

            container.innerHTML = '';
            
            // Actualizar badge instantáneamente con color rojo si hay notificaciones
            badge.textContent = notifs.length;
            badge.classList.remove('bg-secondary', 'bg-primary', 'bg-warning');
            badge.classList.add('bg-danger');
            
            // Agregar animación de pulso al badge
            badge.style.transform = 'scale(1.2)';
            setTimeout(() => {
                badge.style.transform = 'scale(1)';
            }, 300);

            notifs.forEach(n => {
                const date = new Date(n.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const html = `
                        <div class="direct-chat-msg">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-start">${n.titulo}</span>
                                <span class="direct-chat-timestamp float-end">${date}</span>
                            </div>
                            <img class="direct-chat-img" src="https://ui-avatars.com/api/?name=${n.nivel}&background=random&color=fff" alt="icon">
                            <div class="direct-chat-text">
                                ${n.mensaje || n.sub}
                            </div>
                        </div>
                    `;
                container.innerHTML += html;
            });
        }
        
        // --- Real-time notification polling ---
        let notificationPollingInterval;
        let lastNotificationCount = 0;
        
        function startNotificationPolling() {
            // Poll cada 30 segundos para nuevas notificaciones
            notificationPollingInterval = setInterval(async () => {
                try {
                    const response = await fetch('<?= base_url('api/notifications/unread-count') ?>');
                    if (response.ok) {
                        const data = await response.json();
                        const currentCount = data.count || 0;
                        
                        // Si hay nuevas notificaciones, actualizar badge instantáneamente
                        if (currentCount > lastNotificationCount) {
                            const badge = document.getElementById('notif-badge-count');
                            if (badge) {
                                badge.textContent = currentCount;
                                badge.classList.remove('bg-secondary', 'bg-primary', 'bg-warning');
                                badge.classList.add('bg-danger');
                                
                                // Animación de atención para nuevas notificaciones
                                badge.style.transform = 'scale(1.3)';
                                badge.style.boxShadow = '0 0 10px rgba(220, 53, 69, 0.7)';
                                setTimeout(() => {
                                    badge.style.transform = 'scale(1)';
                                    badge.style.boxShadow = 'none';
                                }, 500);
                            }
                            
                            // Opcional: mostrar toast de nueva notificación
                            if (currentCount > lastNotificationCount && lastNotificationCount > 0) {
                                showNewNotificationToast();
                            }
                        }
                        
                        lastNotificationCount = currentCount;
                    }
                } catch (error) {
                    console.error('Error polling notifications:', error);
                }
            }, 30000); // 30 segundos
        }
        
        function showNewNotificationToast() {
            // Crear toast simple para nueva notificación
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-danger text-white">
                        <i class="bi bi-bell-fill me-2"></i>
                        <strong class="me-auto">Nueva Notificación</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        Tienes una nueva notificación en el dashboard.
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Remover después de 5 segundos
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        loadDashboardData();
        startNotificationPolling();
    });
</script>
<?= $this->endSection() ?>