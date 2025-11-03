<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";

$app = new Vendedor();
$mensaje = '';
$tipo_mensaje = '';

if(esVendedor()) {
    redirect('dashboard.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $app->sanitizar($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)) {
        $mensaje = 'Por favor complete todos los campos';
        $tipo_mensaje = 'warning';
    } else if(!$app->validarEmail($email)) {
        $mensaje = 'El formato del email no es válido';
        $tipo_mensaje = 'warning';
    } else {
        $vendedor = $app->login($email, $password);
        
        if($vendedor) {
            $_SESSION['vendedor_id'] = $vendedor['id_vendedor'];
            $_SESSION['vendedor_nombre_tienda'] = $vendedor['nombre_tienda'];
            $_SESSION['vendedor_email'] = $vendedor['email'];
            
            redirect('dashboard.php');
        } else {
            $mensaje = 'Email o contraseña incorrectos, o tu cuenta aún no ha sido aprobada';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Login Vendedor - Amazon Lite';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/vendedor.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../img/logo/logo.png" width="100" alt="Amazon Lite">
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shop-window fs-1 text-warning mb-3 d-block"></i>
                            <h3>Portal de Vendedores</h3>
                            <p class="text-muted">Ingresa a tu panel de control</p>
                        </div>

                        <?php if($mensaje): ?>
                            <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="tutienda@email.com" required 
                                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Tu contraseña" required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="recordar">
                                <label class="form-check-label" for="recordar">
                                    Recordarme
                                </label>
                            </div>

                            <button type="submit" name="login" class="btn btn-warning w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                            </button>

                            <div class="text-center">
                                <a href="#" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="text-muted small mb-2">¿Nuevo en Amazon Lite?</p>
                            <a href="registro.php" class="btn btn-outline-dark w-100">
                                <i class="bi bi-shop"></i> Registra tu tienda
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="text-muted small mb-2">¿Eres comprador?</p>
                            <a href="../login.php" class="btn btn-link">
                                Ir al login de compradores
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Beneficios de vender con nosotros:</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Acceso a millones de clientes</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Panel de control completo</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Gestión de inventario</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Reportes de ventas</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Pagos seguros</li>
                            <li class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>Comisión competitiva del 15%</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>