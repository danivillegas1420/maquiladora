<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>TEST - Dashboard</title>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background: #f5f5f0; }
        .test-icons { 
            padding: 20px; 
            background: white; 
            margin: 20px; 
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-icons i { 
            font-size: 24px; 
            margin: 10px; 
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">TEST DE ÍCONOS</h1>
        
        <div class="test-icons">
            <h3>Bootstrap Icons:</h3>
            <i class="bi bi-bell-fill"></i> Campana
            <i class="bi bi-person-circle"></i> Perfil
            <i class="bi bi-box-arrow-right"></i> Salir
            <i class="bi bi-moon-fill"></i> Luna
            <i class="bi bi-list"></i> Menú
        </div>
        
        <div class="test-icons">
            <h3>FontAwesome Icons:</h3>
            <i class="fas fa-bell"></i> Campana
            <i class="fas fa-user-circle"></i> Perfil
            <i class="fas fa-sign-out-alt"></i> Salir
            <i class="fas fa-moon"></i> Luna
            <i class="fas fa-bars"></i> Menú
        </div>
        
        <div class="alert alert-info">
            Si ves todos los íconos arriba, entonces las librerías cargan correctamente.
            El problema está en el layout main.php original.
        </div>
        
        <a href="<?= base_url('index.php/dashboard') ?>" class="btn btn-primary">
            Volver al Dashboard
        </a>
    </div>
</body>
</html>
