<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";
require_once "../models/sistema.php";

$app = new Vendedor();
$sistema = new Sistema();
$mensaje = '';
$tipo_mensaje = '';

if(esVendedor()) {
    redirect('dashboard.php');
}

$vista = isset($_GET['vista']) ? $_GET['vista'] : 'login';

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

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['solicitar_recuperacion'])) {
    $email = $sistema->sanitizar($_POST['email']);
    
    if(empty($email)) {
        $mensaje = 'Por favor ingresa tu correo electrónico';
        $tipo_mensaje = 'warning';
    } else if(!$sistema->validarEmail($email)) {
        $mensaje = 'El formato del correo no es válido';
        $tipo_mensaje = 'warning';
    } else {
        if($sistema->solicitarRecuperacion($email, 'vendedor')) {
            $mensaje = 'Se ha enviado un correo con las instrucciones para recuperar tu contraseña.';
            $tipo_mensaje = 'success';
            $vista = 'recuperacion_enviada';
        } else {
            $mensaje = 'No se encontró una cuenta con ese correo electrónico.';
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
                            <h3><?= $vista == 'recuperar' ? 'Recuperar contraseña' : 'Portal de Vendedores' ?></h3>
                            <?php if($vista == 'login'): ?>
                                <p class="text-muted">Ingresa a tu panel de control</p>
                            <?php endif; ?>
                        </div>

                        <?php if($mensaje): ?>
                            <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                        <?php endif; ?>

                        <?php if($vista == 'login'): ?>
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
                                    <a href="login.php?vista=recuperar" class="text-decoration-none small">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                            </form>

                        <?php elseif($vista == 'recuperar'): ?>
                            <div class="text-center mb-4">
                                <i class="bi bi-lock fs-1 text-warning"></i>
                                <p class="text-muted mt-3">
                                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña
                                </p>
                            </div>

                            <form method="POST" action="login.php?vista=recuperar">
                                <div class="mb-3">
                                    <label for="email_recuperar" class="form-label">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email_recuperar" name="email" 
                                               placeholder="tutienda@email.com" required>
                                    </div>
                                </div>

                                <button type="submit" name="solicitar_recuperacion" class="btn btn-warning w-100 mb-3">
                                    <i class="bi bi-envelope"></i> Enviar enlace de recuperación
                                </button>

                                <div class="text-center">
                                    <a href="login.php" class="text-decoration-none small">
                                        <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                                    </a>
                                </div>
                            </form>

                        <?php elseif($vista == 'recuperacion_enviada'): ?>
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                                </div>
                                <h4 class="text-success mb-3">¡Correo enviado!</h4>
                                <p class="mb-4">
                                    Revisa tu bandeja de entrada y sigue las instrucciones para restablecer tu contraseña.
                                </p>
                                <div class="alert alert-info text-start">
                                    <small>
                                        <strong>Nota:</strong> Si no ves el correo en tu bandeja de entrada, 
                                        revisa tu carpeta de spam o correo no deseado.
                                    </small>
                                </div>
                                <a href="login.php" class="btn btn-warning">
                                    Volver al inicio de sesión
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if($vista == 'login'): ?>
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
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($vista == 'login'): ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>