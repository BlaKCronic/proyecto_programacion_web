<?php
session_start();
require_once "config/config.php";
require_once "models/usuario.php";
require_once "models/sistema.php";
require_once "models/Validator.php";

error_log("Iniciando proceso de login - Sesión ID: " . session_id());

$app = new Usuario();
$sistema = new Sistema();
$mensaje = '';
$tipo_mensaje = '';
$validacion = null;

if(estaLogueado()) {
    redirect('index.php');
}

$vista = isset($_GET['vista']) ? $_GET['vista'] : 'login';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['login'])) {
        error_log("Intento de login iniciado");
        
        $datos = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];
        
        error_log("Datos recibidos - Email: " . $datos['email']);
        
        $validacion = ValidatorHelper::validarLogin($datos);
        error_log("Resultado validación: " . ($validacion['valido'] ? 'válido' : 'inválido'));
        
        if(!$validacion['valido']) {
            error_log("Validación fallida: " . json_encode($validacion['errores']));
            $tipo_mensaje = 'danger';
        } else {
            $email = $app->sanitizar($datos['email']);
            $password = $datos['password'];

            error_log("Sanitized email: " . $email);
            error_log("Password length: " . strlen($password));
            try {
                $exists = $app->emailExists($email);
                error_log("emailExists: " . ($exists ? 'sí' : 'no'));
            } catch (\Exception $e) {
                error_log("Error comprobando existencia de email: " . $e->getMessage());
            }

            error_log("Intentando autenticar usuario: " . $email);
            $usuario = $app->login($email, $password);
            
            if($usuario) {
                error_log("Usuario autenticado correctamente. ID: " . $usuario['id_usuario']);
                
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_apellido'] = $usuario['apellido'];
                $_SESSION['usuario_email'] = $usuario['email'];
                
                require_once "models/carrito.php";
                $carrito = new Carrito();
                $_SESSION['cart_count'] = $carrito->contarItems($usuario['id_usuario']);
                
                error_log("Sesión iniciada correctamente");
                error_log("Redirigiendo a index.php");
                
                header("Location: index.php");
                exit();
            } else {
                error_log("Credenciales incorrectas");
                $mensaje = 'Email o contraseña incorrectos';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['solicitar_recuperacion'])) {
    $email = $sistema->sanitizar($_POST['email'] ?? '');
    
    if(empty($email)) {
        $mensaje = 'Por favor ingresa tu correo electrónico';
        $tipo_mensaje = 'warning';
    } else if(!$sistema->validarEmail($email)) {
        $mensaje = 'El formato del correo no es válido';
        $tipo_mensaje = 'warning';
    } else {
        if($sistema->solicitarRecuperacion($email, 'usuario')) {
            $mensaje = 'Se ha enviado un correo con las instrucciones para recuperar tu contraseña. Revisa tu bandeja de entrada.';
            $tipo_mensaje = 'success';
            $vista = 'recuperacion_enviada';
        } else {
            $mensaje = 'No se encontró una cuenta con ese correo electrónico.';
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
                        <h3><?= $vista == 'recuperar' ? 'Recuperar contraseña' : 'Iniciar sesión' ?></h3>
                    </div>

                    <?php if($mensaje): ?>
                        <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                    <?php endif; ?>

                    <?php if($validacion && !$validacion['valido'] && !$mensaje): ?>
                        <?= ValidatorHelper::formatearErrores($validacion['errores']) ?>
                    <?php endif; ?>

                    <?php if($vista == 'login'): ?>
                        <form method="POST" action="login.php" id="formLogin">
                            <input type="hidden" name="login" value="1">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control <?= isset($validacion) && !$validacion['valido'] ? ValidatorHelper::claseError($validacion['errores'], 'email') : '' ?>" 
                                       id="email" 
                                       name="email" 
                                       placeholder="tu@email.com"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                       required 
                                       autofocus>
                                <?php if(isset($validacion) && ValidatorHelper::tieneError($validacion['errores'], 'email')): ?>
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle"></i>
                                        <?= ValidatorHelper::obtenerPrimerError($validacion['errores'], 'email') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control <?= isset($validacion) && !$validacion['valido'] ? ValidatorHelper::claseError($validacion['errores'], 'password') : '' ?>" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Tu contraseña" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <?php if(isset($validacion) && ValidatorHelper::tieneError($validacion['errores'], 'password')): ?>
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle"></i>
                                        <?= ValidatorHelper::obtenerPrimerError($validacion['errores'], 'password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="recordar">
                                <label class="form-check-label" for="recordar">
                                    Recordarme
                                </label>
                            </div>

                            <button type="submit" name="login" class="btn btn-warning w-100 mb-3" id="btnLogin">
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
                                <input type="email" 
                                       class="form-control" 
                                       id="email_recuperar" 
                                       name="email" 
                                       placeholder="tu@email.com" 
                                       required
                                       autofocus>
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
                    <?php endif; ?>
                </div>
            </div>

            <?php if($vista == 'login'): ?>
            <div class="text-center mt-4">
                <small class="text-muted">
                    Al continuar, aceptas las Condiciones de uso y el Aviso de privacidad de Amazon Lite.
                </small>
            </div>
            <?php endif; ?>
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
    font-weight: 500;
}

.btn-warning:hover {
    background-color: #f3a847;
    border-color: #f3a847;
    color: #111;
}

.btn-warning:active,
.btn-warning:focus {
    background-color: #f3a847;
    border-color: #f3a847;
    color: #111;
    box-shadow: 0 0 0 0.2rem rgba(254, 189, 105, 0.25);
}

.form-control:focus,
.form-select:focus {
    border-color: #febd69;
    box-shadow: 0 0 0 0.2rem rgba(254, 189, 105, 0.25);
}

.is-invalid {
    border-color: #dc3545;
}

.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

#btnLogin:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

a {
    color: #0066c0;
    text-decoration: none;
}

a:hover {
    color: #c45500;
    text-decoration: underline;
}

.alert {
    border-radius: 4px;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c2c7;
    color: #842029;
}

.alert-success {
    background-color: #d1e7dd;
    border-color: #badbcc;
    color: #0f5132;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    color: #664d03;
}

.alert-info {
    background-color: #cff4fc;
    border-color: #b6effb;
    color: #055160;
}

@keyframes checkmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.bi-check-circle-fill {
    animation: checkmark 0.6s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if(togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if(type === 'text') {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
    }
    
    const formLogin = document.getElementById('formLogin');
    if(formLogin) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const btnLogin = document.getElementById('btnLogin');
        
        if(emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if(email === '') {
                    this.classList.remove('is-valid', 'is-invalid');
                } else if(!emailRegex.test(email)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
            
            emailInput.addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        }
        
        if(passwordInput) {
            passwordInput.addEventListener('blur', function() {
                const password = this.value;
                
                if(password === '') {
                    this.classList.remove('is-valid', 'is-invalid');
                } else if(password.length < 6) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
            
            passwordInput.addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        }
        
        formLogin.addEventListener('submit', function(e) {
            btnLogin.disabled = true;
            btnLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando sesión...';
            
            setTimeout(function() {
                btnLogin.disabled = false;
                btnLogin.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Iniciar sesión';
            }, 5000);
        });
    }
    
    const emailInput = document.getElementById('email');
    const recordarCheckbox = document.getElementById('recordar');
    
    if(emailInput && recordarCheckbox) {
        const emailGuardado = localStorage.getItem('email_recordado');
        if(emailGuardado) {
            emailInput.value = emailGuardado;
            recordarCheckbox.checked = true;
        }
        
        recordarCheckbox.addEventListener('change', function() {
            if(this.checked) {
                localStorage.setItem('email_recordado', emailInput.value);
            } else {
                localStorage.removeItem('email_recordado');
            }
        });
        
        emailInput.addEventListener('change', function() {
            if(recordarCheckbox.checked) {
                localStorage.setItem('email_recordado', this.value);
            }
        });
    }
    
    const primerError = document.querySelector('.is-invalid');
    if(primerError) {
        primerError.focus();
    }
});
</script>

<script src="js/usuario/login.js"></script>

<?php include_once "views/footer.php"; ?>