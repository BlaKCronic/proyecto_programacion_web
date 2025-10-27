<?php
require_once "config/config.php";
require_once "models/usuario.php";

$app = new Usuario();
$mensaje = '';
$tipo_mensaje = '';

if(estaLogueado()) {
    redirect('index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro'])) {
    $data = [];
    $data['nombre'] = $app->sanitizar($_POST['nombre']);
    $data['apellido'] = $app->sanitizar($_POST['apellido']);
    $data['email'] = $app->sanitizar($_POST['email']);
    $data['telefono'] = $app->sanitizar($_POST['telefono']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if(empty($data['nombre']) || empty($data['apellido']) || empty($data['email']) || 
       empty($password) || empty($password_confirm)) {
        $mensaje = 'Por favor complete todos los campos obligatorios';
        $tipo_mensaje = 'warning';
    } else if(!$app->validarEmail($data['email'])) {
        $mensaje = 'El formato del email no es válido';
        $tipo_mensaje = 'warning';
    } else if(strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'warning';
    } else if($password !== $password_confirm) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'warning';
    } else if($app->emailExists($data['email'])) {
        $mensaje = 'Este email ya está registrado';
        $tipo_mensaje = 'danger';
    } else {
        $data['direccion'] = isset($_POST['direccion']) ? $app->sanitizar($_POST['direccion']) : '';
        $data['ciudad'] = isset($_POST['ciudad']) ? $app->sanitizar($_POST['ciudad']) : '';
        $data['estado'] = isset($_POST['estado']) ? $app->sanitizar($_POST['estado']) : '';
        $data['codigo_postal'] = isset($_POST['codigo_postal']) ? $app->sanitizar($_POST['codigo_postal']) : '';
        $data['password'] = $password;
        
        $filas = $app->create($data);
        
        if($filas > 0) {
            $mensaje = '¡Registro exitoso! Ahora puedes iniciar sesión';
            $tipo_mensaje = 'success';
            
            $_POST = array();
            
            header("refresh:2;url=login.php");
        } else {
            $mensaje = 'Error al registrar. Intenta nuevamente';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Crear cuenta - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="img/logo/logo.png" width="120" alt="Amazon Lite" class="mb-3">
                        <h3>Crear cuenta</h3>
                    </div>

                    <?php if($mensaje): ?>
                        <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                    <?php endif; ?>

                    <form method="POST" action="registro.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       placeholder="Juan" required 
                                       value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       placeholder="Pérez" required 
                                       value="<?= isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="tu@email.com" required 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            <small class="text-muted">Usarás este email para iniciar sesión</small>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   placeholder="4611234567" maxlength="10"
                                   value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Mínimo 6 caracteres" required minlength="6">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                       placeholder="Repite tu contraseña" required minlength="6">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="mostrar_direccion">
                                <label class="form-check-label" for="mostrar_direccion">
                                    <small>Agregar dirección de envío (opcional)</small>
                                </label>
                            </div>
                        </div>

                        <div id="direccion_fields" style="display: none;">
                            <hr>
                            <h6 class="mb-3">Dirección de envío</h6>
                            
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Calle y número</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       placeholder="Ej: Calle Principal #123"
                                       value="<?= isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : '' ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                           placeholder="Ej: Celaya"
                                           value="<?= isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : '' ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="">Selecciona un estado</option>
                                        <option value="Aguascalientes">Aguascalientes</option>
                                        <option value="Baja California">Baja California</option>
                                        <option value="Baja California Sur">Baja California Sur</option>
                                        <option value="Campeche">Campeche</option>
                                        <option value="Chiapas">Chiapas</option>
                                        <option value="Chihuahua">Chihuahua</option>
                                        <option value="Coahuila">Coahuila</option>
                                        <option value="Colima">Colima</option>
                                        <option value="Durango">Durango</option>
                                        <option value="Guanajuato" selected>Guanajuato</option>
                                        <option value="Guerrero">Guerrero</option>
                                        <option value="Hidalgo">Hidalgo</option>
                                        <option value="Jalisco">Jalisco</option>
                                        <option value="México">México</option>
                                        <option value="Michoacán">Michoacán</option>
                                        <option value="Morelos">Morelos</option>
                                        <option value="Nayarit">Nayarit</option>
                                        <option value="Nuevo León">Nuevo León</option>
                                        <option value="Oaxaca">Oaxaca</option>
                                        <option value="Puebla">Puebla</option>
                                        <option value="Querétaro">Querétaro</option>
                                        <option value="Quintana Roo">Quintana Roo</option>
                                        <option value="San Luis Potosí">San Luis Potosí</option>
                                        <option value="Sinaloa">Sinaloa</option>
                                        <option value="Sonora">Sonora</option>
                                        <option value="Tabasco">Tabasco</option>
                                        <option value="Tamaulipas">Tamaulipas</option>
                                        <option value="Tlaxcala">Tlaxcala</option>
                                        <option value="Veracruz">Veracruz</option>
                                        <option value="Yucatán">Yucatán</option>
                                        <option value="Zacatecas">Zacatecas</option>
                                        <option value="Ciudad de México">Ciudad de México</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                       placeholder="38000" maxlength="5"
                                       value="<?= isset($_POST['codigo_postal']) ? htmlspecialchars($_POST['codigo_postal']) : '' ?>">
                            </div>
                        </div>

                        <button type="submit" name="registro" class="btn btn-warning w-100 mb-3 mt-3">
                            Crear tu cuenta de Amazon Lite
                        </button>

                        <div class="text-center">
                            <small class="text-muted">
                                Al crear una cuenta, aceptas las Condiciones de uso y el Aviso de privacidad de Amazon Lite.
                            </small>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">¿Ya tienes una cuenta? 
                            <a href="login.php" class="text-decoration-none">Inicia sesión</a>
                        </p>
                    </div>
                </div>
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

.form-control:focus, .form-select:focus {
    border-color: #febd69;
    box-shadow: 0 0 0 0.2rem rgba(254, 189, 105, 0.25);
}

.text-danger {
    color: #dc3545 !important;
}
</style>

<script>
document.getElementById('mostrar_direccion').addEventListener('change', function() {
    const direccionFields = document.getElementById('direccion_fields');
    if(this.checked) {
        direccionFields.style.display = 'block';
    } else {
        direccionFields.style.display = 'none';
    }
});

document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const password_confirm = document.getElementById('password_confirm').value;
    
    if(password !== password_confirm) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
});

document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('codigo_postal').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php include_once "views/footer.php"; ?>