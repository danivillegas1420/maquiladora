<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --warning-color: #f72585;
        --bg-color: #f8f9fa;
        --card-bg: #ffffff;
        --text-color: #2b2d42;
        --text-muted-color: #8d99ae;
    }

    /* Dark mode variables */
    body[data-theme="dark"] {
        --bg-color: #121212;
        --card-bg: #1e1e1e;
        --text-color: #f8f9fa;
        --text-muted-color: #ced4da;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', sans-serif;
        color: var(--text-color);
    }

    /* FORCE ALL TEXT TO BE VISIBLE IN DARK MODE */
    body[data-theme="dark"] * {
        color: var(--text-color) !important;
    }

    /* Specific overrides for elements that should keep their original colors */
    body[data-theme="dark"] .bg-primary *,
    body[data-theme="dark"] .bg-success *,
    body[data-theme="dark"] .bg-warning *,
    body[data-theme="dark"] .bg-danger *,
    body[data-theme="dark"] .bg-info *,
    body[data-theme="dark"] .btn-primary *,
    body[data-theme="dark"] .btn-success *,
    body[data-theme="dark"] .btn-warning *,
    body[data-theme="dark"] .btn-danger *,
    body[data-theme="dark"] .btn-info * {
        color: inherit !important;
    }

    /* Links should be blue and visible */
    body[data-theme="dark"] a {
        color: #4dabf7 !important;
    }

    body[data-theme="dark"] a:hover {
        color: #339af0 !important;
    }

    /* Ensure dashboard containers are visible */
    body[data-theme="dark"] .container-fluid {
        background-color: var(--bg-color) !important;
        color: var(--text-color) !important;
    }

    /* Card backgrounds and text */
    body[data-theme="dark"] .kpi-card,
    body[data-theme="dark"] .chart-card {
        background-color: var(--card-bg) !important;
        color: var(--text-color) !important;
        border: 1px solid #333 !important;
    }

    /* Fix any remaining white text issues */
    body[data-theme="dark"] .text-white,
    body[data-theme="dark"] [style*="color: white"],
    body[data-theme="dark"] [style*="color:#fff"],
    body[data-theme="dark"] [style*="color: #fff"] {
        color: var(--text-color) !important;
    }

    /* Fix breadcrumb and navigation text */
    body[data-theme="dark"] .breadcrumb,
    body[data-theme="dark"] .breadcrumb-item,
    body[data-theme="dark"] .breadcrumb-item + .breadcrumb-item::before,
    body[data-theme="dark"] .topbar,
    body[data-theme="dark"] .topbar *,
    body[data-theme="dark"] .navbar-light *,
    body[data-theme="dark"] .text-gray-600,
    body[data-theme="dark"] .small {
        color: var(--text-color) !important;
    }

    /* Fix topbar background */
    body[data-theme="dark"] .navbar.bg-white,
    body[data-theme="dark"] .topbar {
        background-color: #1e1e1e !important;
        border-bottom-color: #333 !important;
    }

    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-weight: 700;
        color: var(--text-color);
    }

    .kpi-card {
        background: var(--card-bg);
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
        overflow: hidden;
        height: 100%;
    }

    /* Dark mode shadow for cards */
    body[data-theme="dark"] .kpi-card {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .kpi-card:hover {
        transform: translateY(-5px);
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.25rem;
    }

    .kpi-label {
        color: var(--text-muted-color);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-card {
        background: var(--card-bg);
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Dark mode shadow for chart cards */
    body[data-theme="dark"] .chart-card {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 1.5rem;
    }

    .notif-item {
        padding: 1rem;
        border-bottom: 1px solid #edf2f4;
        display: flex;
        align-items: start;
        gap: 1rem;
    }

    /* Dark mode border for notification items */
    body[data-theme="dark"] .notif-item {
        border-bottom-color: #333;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="dashboard-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="dashboard-title h3">Dashboard General</h1>
            <p class="text-muted mb-0">Bienvenido de nuevo, <?= esc($userName) ?></p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Reporte
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-4 mb-4">
        <!-- Órdenes Activas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Órdenes Activas</div>
                        <div class="kpi-value"><?= esc($kpis['ordenes_activas']) ?></div>
                        <div class="text-success small"><i class="bi bi-arrow-up-short"></i> En proceso</div>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- WIP -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">WIP (Piezas)</div>
                        <div class="kpi-value"><?= number_format($kpis['wip_cantidad']) ?></div>
                        <div class="text-muted small">En planta</div>
                    </div>
                    <div class="kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-gear"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calidad -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Tasa Defectos</div>
                        <div class="kpi-value"><?= esc($kpis['tasa_defectos']) ?>%</div>
                        <div class="text-<?= $kpis['tasa_defectos'] > 5 ? 'danger' : 'success' ?> small">
                            <?= $kpis['tasa_defectos'] > 5 ? 'Atención requerida' : 'Bajo control' ?>
                        </div>
                    </div>
                    <div
                        class="kpi-icon bg-<?= $kpis['tasa_defectos'] > 5 ? 'danger' : 'success' ?> bg-opacity-10 text-<?= $kpis['tasa_defectos'] > 5 ? 'danger' : 'success' ?>">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Crítico -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Stock Crítico</div>
                        <div class="kpi-value"><?= esc($kpis['stock_critico']) ?></div>
                        <div class="text-<?= $kpis['stock_critico'] > 0 ? 'warning' : 'muted' ?> small">Artículos</div>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <h5 class="chart-title">Producción Semanal (Plan vs Real)</h5>
                <div style="height: 300px;">
                    <canvas id="productionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <h5 class="chart-title">Notificaciones Recientes</h5>
                <div class="vstack gap-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-4 text-muted">No hay notificaciones nuevas</div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notif-item">
                                <div class="notif-icon"
                                    style="background-color: <?= esc($notif['color']) ?>20; color: <?= esc($notif['color']) ?>">
                                    <i class="bi bi-bell"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small"><?= esc($notif['titulo']) ?></div>
                                    <div class="text-muted small"><?= esc($notif['sub'] ?: $notif['mensaje']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('d M H:i', strtotime($notif['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <h5 class="chart-title">Tendencia de Calidad (% Defectos)</h5>
                <div style="height: 250px;">
                    <canvas id="qualityChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <h5 class="chart-title">Inventario (Top 5 Stock)</h5>
                <div style="height: 250px;">
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos inyectados desde el controlador
    const productionData = <?= json_encode($charts['produccion']) ?>;
    const qualityData = <?= json_encode($charts['calidad']) ?>;
    const inventoryData = <?= json_encode($charts['inventario']) ?>;

    // Configuración común
    Chart.defaults.font.family = "'Inter', sans-serif";
    
    // Configurar colores según el tema actual
    const isDarkMode = document.body.getAttribute('data-theme') === 'dark';
    const chartTextColor = isDarkMode ? '#f8f9fa' : '#212529';
    const gridColor = isDarkMode ? '#333333' : '#e0e0e0';
    
    Chart.defaults.color = chartTextColor;
    
    // Actualizar colores del gráfico cuando cambie el tema
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                const isDark = document.body.getAttribute('data-theme') === 'dark';
                const newTextColor = isDark ? '#f8f9fa' : '#212529';
                const newGridColor = isDark ? '#333333' : '#e0e0e0';
                
                Chart.defaults.color = newTextColor;
                
                // Actualizar todos los gráficos existentes con nuevos colores
                Chart.helpers.each(Chart.instances, (instance) => {
                    // Actualizar colores de las escalas
                    if (instance.options.scales) {
                        if (instance.options.scales.x) {
                            instance.options.scales.x.ticks = { ...instance.options.scales.x.ticks, color: newTextColor };
                            instance.options.scales.x.grid = { ...instance.options.scales.x.grid, color: newGridColor };
                        }
                        if (instance.options.scales.y) {
                            instance.options.scales.y.ticks = { ...instance.options.scales.y.ticks, color: newTextColor };
                            instance.options.scales.y.grid = { ...instance.options.scales.y.grid, color: newGridColor };
                        }
                    }
                    // Actualizar colores de leyenda
                    if (instance.options.plugins && instance.options.plugins.legend) {
                        instance.options.plugins.legend.labels = { ...instance.options.plugins.legend.labels, color: newTextColor };
                    }
                    
                    instance.update();
                });
            }
        });
    });
    
    observer.observe(document.body, { attributes: true });

    // Gráfico de Producción
    new Chart(document.getElementById('productionChart'), {
        type: 'bar',
        data: productionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { color: chartTextColor }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { borderDash: [2, 4], color: gridColor },
                    ticks: { color: chartTextColor }
                },
                x: { 
                    grid: { display: false, color: gridColor },
                    ticks: { color: chartTextColor }
                }
            }
        }
    });

    // Gráfico de Calidad
    new Chart(document.getElementById('qualityChart'), {
        type: 'line',
        data: qualityData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: false,
                    labels: { color: chartTextColor }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { borderDash: [2, 4], color: gridColor },
                    ticks: { color: chartTextColor }
                },
                x: { 
                    grid: { display: false, color: gridColor },
                    ticks: { color: chartTextColor }
                }
            }
        }
    });

    // Gráfico de Inventario
    new Chart(document.getElementById('inventoryChart'), {
        type: 'bar', // Cambiado a bar vertical para mejor ajuste o horizontal
        indexAxis: 'y',
        data: inventoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: false,
                    labels: { color: chartTextColor }
                }
            },
            scales: {
                x: { 
                    beginAtZero: true, 
                    grid: { borderDash: [2, 4], color: gridColor },
                    ticks: { color: chartTextColor }
                },
                y: { 
                    grid: { display: false, color: gridColor },
                    ticks: { color: chartTextColor }
                }
            }
        }
    });
</script>
<?= $this->endSection() ?>