<?php
require_once "config/config.php";
require_once "models/usuario.php";

$app = new Usuario();
$mensaje = '';
$tipo_mensaje = '';

// Si ya está logueado, redirigir
if(estaLogueado()) {
    redirect('index.php');
}

// Procesar login
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
        $usuario = $app->login($email, $password);
        
        if($usuario) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_apellido'] = $usuario['apellido'];
            $_SESSION['usuario_email'] = $usuario['email'];
            
            // Contar items del carrito
            require_once "models/carrito.php";
            $carrito = new Carrito();
            $_SESSION['cart_count'] = $carrito->contarItems($usuario['id_usuario']);
            
            // Redirigir
            redirect('index.php');
        } else {
            $mensaje = 'Email o contraseña incorrectos';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Iniciar Sesión - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="img/logo/logo.png" width="120" alt="Amazon Lite" class="mb-3">
                        <h3>Iniciar sesión</h3>
                    </div>

                    <?php if($mensaje): ?>
                        <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="tu@email.com" required 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Tu contraseña" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="recordar">
                            <label class="form-check-label" for="recordar">
                                Recordarme
                            </label>
                        </div>

                        <button type="submit" name="login" class="btn btn-warning w-100 mb-3">
                            Iniciar sesión
                        </button>

                        <div class="text-center">
                            <a href="#" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted small mb-2">¿Nuevo en Amazon Lite?</p>
                        <a href="registro.php" class="btn btn-outline-dark w-100">
                            Crear tu cuenta de Amazon Lite
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted small mb-2">¿Eres vendedor?</p>
                        <a href="vendedor/login.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-shop"></i> Acceso para vendedores
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <small class="text-muted">
                    Al continuar, aceptas las Condiciones de uso y el Aviso de privacidad de Amazon Lite.
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

<?php include_once "views/footer.php"; ?>