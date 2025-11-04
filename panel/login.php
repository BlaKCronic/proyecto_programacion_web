<?php
require_once "../config/config.php";

$mensaje = '';
$tipo_mensaje = '';

if(esAdmin()) {
    redirect('index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    if($usuario === 'admin' && $password === 'admin123') {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_nombre'] = 'Administrador';
        $_SESSION['admin_usuario'] = 'admin';
        redirect('index.php');
    } else {
        $mensaje = 'Credenciales incorrectas';
        $tipo_mensaje = 'danger';
    }
}

$pageTitle = 'Login Admin - Amazon Lite';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-7">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center mb-4">
                                <img src="../img/logo/logo.png" width="120" alt="Amazon Lite" class="mb-3">
                                <h1 class="h4 text-gray-900 mb-2">Panel de Administración</h1>
                                <p class="text-muted">Ingresa tus credenciales</p>
                            </div>

                            <?php if($mensaje): ?>
                                <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                                    <?= $mensaje ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="login.php" class="user">
                                <div class="form-group mb-3">
                                    <input type="text" class="form-control form-control-user" 
                                           id="usuario" name="usuario" placeholder="Usuario" required>
                                </div>
                                <div class="form-group mb-3">
                                    <input type="password" class="form-control form-control-user" 
                                           id="password" name="password" placeholder="Contraseña" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary btn-user btn-block w-100">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </button>
                            </form>

                            <hr>

                            <div class="text-center">
                                <a href="../index.php" class="small">Volver a la tienda</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center text-white mb-3">
                    <small>Credenciales por defecto: admin / admin123</small>
                </div>
            </div>
        </div>
    </div>

    <style>
    .bg-gradient-primary {
        background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
    }

    .form-control-user {
        border-radius: 10rem;
        padding: 1.5rem 1rem;
    }

    .btn-user {
        border-radius: 10rem;
        padding: 0.75rem 1rem;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>