<?php
require_once "config/config.php";
require_once "models/sistema.php";

$app = new Sistema();
$mensaje = '';
$tipo_mensaje = '';
$paso = 'solicitar';

if(isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    if($app->verificarToken($email, $token, 'usuario')) {
        $paso = 'restablecer';
    } else {
        $paso = 'solicitar';
        $mensaje = 'El enlace de recuperación ha expirado o no es válido. Por favor solicita uno nuevo.';
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
        if($app->solicitarRecuperacion($email, 'usuario')) {
            $mensaje = 'Se ha enviado un correo con las instrucciones para recuperar tu contraseña.';
            $tipo_mensaje = 'success';
            $paso = 'exito';
        } else {
            $mensaje = 'No se encontró una cuenta con ese correo electrónico.';
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
        if($app->restablecerPassword($email, $token, $password, 'usuario')) {
            $mensaje = '¡Contraseña restablecida correctamente! Ya puedes iniciar sesión.';
            $tipo_mensaje = 'success';
            $paso = 'exito_restablecer';
            header("refresh:3;url=login.php");
        } else {
            $mensaje = 'Error al restablecer la contraseña. El enlace puede haber expirado.';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Recuperar contraseña - Amazon Lite';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/main.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-amazon-dark px-3 py-2">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="img/logo/logo.png" width="100" alt="Amazon Lite">
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
                                <i class="bi bi-lock fs-1 text-warning mb-3 d-block"></i>
                                <h3>Recuperar contraseña</h3>
                                <p class="text-muted">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña</p>
                            </div>

                            <?php if($mensaje): ?>
                                <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                            <?php endif; ?>

                            <form method="POST" action="recuperar_password.php">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="tu@email.com" required>
                                </div>

                                <button type="submit" name="solicitar_recuperacion" class="btn btn-warning w-100 mb-3">
                                    <i class="bi bi-envelope"></i> Enviar enlace de recuperación
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
                                <p class="text-muted">Ingresa tu nueva contraseña</p>
                            </div>

                            <?php if($mensaje): ?>
                                <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                            <?php endif; ?>

                            <form method="POST" action="recuperar_password.php">
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
                                    <i class="bi bi-check-circle"></i> Restablecer contraseña
                                </button>

                                <div class="text-center">
                                    <a href="login.php" class="text-decoration-none">
                                        <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                                    </a>
                                </div>
                            </form>

                        <?php elseif($paso == 'exito'): ?>
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                                </div>
                                <h3 class="text-success mb-3">¡Correo enviado!</h3>
                                <p class="mb-4">
                                    Revisa tu bandeja de entrada y sigue las instrucciones para restablecer tu contraseña.
                                </p>
                                <div class="alert alert-info text-start">
                                    <small>
                                        <strong>Nota:</strong> Si no ves el correo en tu bandeja de entrada, 
                                        revisa tu carpeta de spam o correo no deseado.
                                    </small>
                                </div>
                                <a href="login.php" class="btn btn-outline-secondary">
                                    Volver al inicio de sesión
                                </a>
                            </div>

                        <?php elseif($paso == 'exito_restablecer'): ?>
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                                </div>
                                <h3 class="text-success mb-3">¡Contraseña actualizada!</h3>
                                <?php if($mensaje): ?>
                                    <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                                <?php endif; ?>
                                <p class="mb-4">
                                    Serás redirigido al inicio de sesión en unos segundos...
                                </p>
                                <a href="login.php" class="btn btn-warning">
                                    Ir al inicio de sesión
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        ¿Necesitas ayuda? <a href="#">Contáctanos</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <style>
    .card {
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .btn-warning {
        background-color: #febd69;
        border-color: #febd69;
        color: #111;
    }

    .btn-warning:hover {
        background-color: #f3a847;
        border-color: #f3a847;
        color: #111;
    }

    .form-control:focus {
        border-color: #febd69;
        box-shadow: 0 0 0 0.2rem rgba(254, 189, 105, 0.25);
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="js/usuario/recuperar-password.js"></script>
</body>
</html>