<?php
require_once "../config/config.php";
require_once "../models/sistema.php";

$app = new Sistema();
$mensaje = '';
$tipo_mensaje = '';
$paso = 'solicitar';

if(isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    if($app->verificarToken($email, $token, 'vendedor')) {
        $paso = 'restablecer';
    } else {
        $paso = 'solicitar';
        $mensaje = 'El enlace de recuperación ha expirado o no es válido.';
        $tipo_mensaje = 'danger';
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['solicitar_recuperacion'])) {
    $email = $app->sanitizar($_POST['email']);
    
    if(empty($email)) {
        $mensaje = 'Por favor ingresa tu correo electrónico';
        $tipo_mensaje = 'warning';
    } else if(!$app->validarEmail($email)) {
        $mensaje = 'El formato del correo no es válido';
        $tipo_mensaje = 'warning';
    } else {
        if($app->solicitarRecuperacion($email, 'vendedor')) {
            $mensaje = 'Se ha enviado un correo con las instrucciones.';
            $tipo_mensaje = 'success';
            $paso = 'exito';
        } else {
            $mensaje = 'No se encontró una cuenta con ese correo.';
            $tipo_mensaje = 'danger';
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restablecer_password'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if(empty($password) || empty($password_confirm)) {
        $mensaje = 'Por favor completa todos los campos';
        $tipo_mensaje = 'warning';
    } else if(strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'warning';
    } else if($password !== $password_confirm) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'warning';
    } else {
        if($app->restablecerPassword($email, $token, $password, 'vendedor')) {
            $mensaje = '¡Contraseña restablecida correctamente!';
            $tipo_mensaje = 'success';
            $paso = 'exito_restablecer';
            header("refresh:3;url=login.php");
        } else {
            $mensaje = 'Error al restablecer la contraseña.';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Recuperar contraseña - Vendedor';
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
                        <?php if($paso == 'solicitar'): ?>
                            <div class="text-center mb-4">
                                <i class="bi bi-shop-window fs-1 text-warning mb-3 d-block"></i>
                                <h3>Recuperar contraseña</h3>
                                <p class="text-muted">Portal de Vendedores</p>
                            </div>

                            <?php if($mensaje): ?>
                                <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="tutienda@email.com" required>
                                </div>

                                <button type="submit" name="solicitar_recuperacion" class="btn btn-warning w-100 mb-3">
                                    Enviar enlace de recuperación
                                </button>

                                <div class="text-center">
                                    <a href="login.php" class="text-decoration-none">
                                        <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                                    </a>
                                </div>
                            </form>

                        <?php elseif($paso == 'restablecer'): ?>
                            <div class="text-center mb-4">
                                <i class="bi bi-key fs-1 text-warning mb-3 d-block"></i>
                                <h3>Nueva contraseña</h3>
                            </div>

                            <?php if($mensaje): ?>
                                <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                                <div class="mb-3">
                                    <label for="password" class="form-label">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Mínimo 6 caracteres" required minlength="6">
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">Confirmar contraseña</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                           placeholder="Repite tu contraseña" required minlength="6">
                                </div>

                                <button type="submit" name="restablecer_password" class="btn btn-warning w-100 mb-3">
                                    Restablecer contraseña
                                </button>

                                <div class="text-center">
                                    <a href="login.php">Volver al inicio de sesión</a>
                                </div>
                            </form>

                        <?php elseif($paso == 'exito'): ?>
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                                <h3 class="text-success my-3">¡Correo enviado!</h3>
                                <?php if($mensaje): ?>
                                    <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                                <?php endif; ?>
                                <a href="login.php" class="btn btn-outline-secondary mt-3">
                                    Volver al inicio de sesión
                                </a>
                            </div>

                        <?php elseif($paso == 'exito_restablecer'): ?>
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                                <h3 class="text-success my-3">¡Contraseña actualizada!</h3>
                                <?php if($mensaje): ?>
                                    <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                                <?php endif; ?>
                                <a href="login.php" class="btn btn-warning mt-3">
                                    Ir al inicio de sesión
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="../../js/vendedor/recuperar-password.js"></script>
</body>
</html>