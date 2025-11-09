<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";
require_once "../models/Validator.php";

$app = new Vendedor();
$mensaje = '';
$tipo_mensaje = '';

if(esVendedor()) {
    redirect('dashboard.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro'])) {
    $data = [];
    $data['nombre_tienda'] = $_POST['nombre_tienda'] ?? '';
    $data['email'] = $_POST['email'] ?? '';
    $data['nombre_contacto'] = $_POST['nombre_contacto'] ?? '';
    $data['telefono'] = $_POST['telefono'] ?? '';
    $data['direccion'] = $_POST['direccion'] ?? '';
    $data['rfc'] = $_POST['rfc'] ?? '';
    $data['razon_social'] = $_POST['razon_social'] ?? '';
    $data['descripcion'] = $_POST['descripcion'] ?? '';
    $data['password'] = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $validacion = ValidatorHelper::validarRegistroVendedor($data);

    if(!$validacion['valido']) {
        $mensaje = ValidatorHelper::formatearErrores($validacion['errores']);
        $tipo_mensaje = 'danger';
    } else if($data['password'] !== $password_confirm) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'warning';
    } else if($app->emailExists($data['email'])) {
        $mensaje = 'Este email ya está registrado';
        $tipo_mensaje = 'danger';
    } else {
        foreach($data as $k => $v) {
            if($k !== 'password') {
                $data[$k] = $app->sanitizar($v);
            }
        }

        $id_vendedor = $app->create($data);

        if($id_vendedor > 0) {
            $mensaje = '¡Registro exitoso! Tu cuenta está pendiente de aprobación. Te notificaremos por email cuando sea aprobada.';
            $tipo_mensaje = 'success';
            
            $_POST = array();
            
            header("refresh:3;url=login.php");
        } else {
            $mensaje = 'Error al registrar. Intenta nuevamente';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Registro de Vendedor - Amazon Lite';
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
            <span class="text-white">
                ¿Ya tienes cuenta? <a href="login.php" class="text-warning">Inicia sesión</a>
            </span>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shop fs-1 text-warning"></i>
                            <h2 class="mt-3">Vende con Amazon Lite</h2>
                            <p class="text-muted">Regístrate y comienza a vender tus productos</p>
                        </div>

                        <?php if($mensaje): ?>
                            <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-4 text-center mb-3">
                                <i class="bi bi-people fs-3 text-warning"></i>
                                <p class="small mb-0 mt-2">Millones de clientes</p>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <i class="bi bi-graph-up fs-3 text-warning"></i>
                                <p class="small mb-0 mt-2">Aumenta tus ventas</p>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <i class="bi bi-shield-check fs-3 text-warning"></i>
                                <p class="small mb-0 mt-2">Pagos seguros</p>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <form method="POST" action="registro.php">
                            <h5 class="mb-3">Información de la tienda</h5>
                            
                            <div class="mb-3">
                                <label for="nombre_tienda" class="form-label">
                                    Nombre de la tienda <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre_tienda" name="nombre_tienda" 
                                       placeholder="Ej: Electrónica Moderna" required 
                                       value="<?= isset($_POST['nombre_tienda']) ? htmlspecialchars($_POST['nombre_tienda']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción de tu negocio</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                          placeholder="Describe brevemente qué vendes..."><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Información de contacto</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_contacto" class="form-label">
                                        Nombre del responsable <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto" 
                                           placeholder="Juan Pérez" required 
                                           value="<?= isset($_POST['nombre_contacto']) ? htmlspecialchars($_POST['nombre_contacto']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">
                                        Teléfono <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           placeholder="4611234567" maxlength="10" required 
                                           value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="tutienda@email.com" required 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                <small class="text-muted">Usarás este email para iniciar sesión</small>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">
                                    Dirección del negocio <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       placeholder="Calle, número, colonia, ciudad, estado" required 
                                       value="<?= isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : '' ?>">
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Información fiscal</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rfc" class="form-label">RFC</label>
                                    <input type="text" class="form-control" id="rfc" name="rfc" 
                                           placeholder="XAXX010101000" maxlength="13"
                                           value="<?= isset($_POST['rfc']) ? htmlspecialchars($_POST['rfc']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="razon_social" class="form-label">Razón Social</label>
                                    <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                           placeholder="Mi Empresa S.A. de C.V."
                                           value="<?= isset($_POST['razon_social']) ? htmlspecialchars($_POST['razon_social']) : '' ?>">
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Seguridad</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Mínimo 6 caracteres" required minlength="6">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirm" class="form-label">
                                        Confirmar contraseña <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                           placeholder="Repite tu contraseña" required minlength="6">
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="aceptoTerminos" required>
                                <label class="form-check-label" for="aceptoTerminos">
                                    Acepto los <a href="#" target="_blank">términos y condiciones</a> para vendedores
                                    y la <a href="#" target="_blank">política de comisiones</a> (15%)
                                </label>
                            </div>

                            <button type="submit" name="registro" class="btn btn-warning btn-lg w-100 mb-3">
                                <i class="bi bi-check-circle"></i> Registrar mi tienda
                            </button>

                            <div class="text-center">
                                <small class="text-muted">
                                    Tu cuenta será revisada y aprobada en 24-48 horas
                                </small>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">¿Ya tienes una cuenta de vendedor? 
                                <a href="login.php" class="text-decoration-none">Inicia sesión</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('telefono').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('rfc').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
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
    </script>
</body>
</html>