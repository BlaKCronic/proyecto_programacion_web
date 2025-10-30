<?php
require_once "config/config.php";
require_once "models/usuario.php";
require_once "models/pedido.php";

if(!estaLogueado()) {
    redirect('login.php');
}

$appUsuario = new Usuario();
$appPedido = new Pedido();

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$tipo_mensaje = '';

$usuario = $appUsuario->readOne($usuario_id);

$pedidos_usuario = $appPedido->readByUsuario($usuario_id);
$total_pedidos = count($pedidos_usuario);
$total_gastado = 0;
foreach($pedidos_usuario as $pedido) {
    if($pedido['estado'] != 'cancelado') {
        $total_gastado += $pedido['total'];
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['actualizar_perfil'])) {
        $data = [];
        $data['nombre'] = $appUsuario->sanitizar($_POST['nombre']);
        $data['apellido'] = $appUsuario->sanitizar($_POST['apellido']);
        $data['email'] = $appUsuario->sanitizar($_POST['email']);
        $data['telefono'] = $appUsuario->sanitizar($_POST['telefono']);
        $data['direccion'] = $appUsuario->sanitizar($_POST['direccion']);
        $data['ciudad'] = $appUsuario->sanitizar($_POST['ciudad']);
        $data['estado'] = $appUsuario->sanitizar($_POST['estado']);
        $data['codigo_postal'] = $appUsuario->sanitizar($_POST['codigo_postal']);
        $data['activo'] = $usuario['activo']; // Mantener estado actual
        
        if($data['email'] != $usuario['email']) {
            if($appUsuario->emailExists($data['email'])) {
                $mensaje = 'El email ya está registrado por otro usuario';
                $tipo_mensaje = 'danger';
            } else {
                $filas = $appUsuario->update($data, $usuario_id);
                if($filas > 0) {
                    $_SESSION['usuario_nombre'] = $data['nombre'];
                    $_SESSION['usuario_email'] = $data['email'];
                    $mensaje = 'Perfil actualizado correctamente';
                    $tipo_mensaje = 'success';
                    $usuario = $appUsuario->readOne($usuario_id);
                } else {
                    $mensaje = 'No se realizaron cambios';
                    $tipo_mensaje = 'info';
                }
            }
        } else {
            $filas = $appUsuario->update($data, $usuario_id);
            if($filas > 0) {
                $_SESSION['usuario_nombre'] = $data['nombre'];
                $mensaje = 'Perfil actualizado correctamente';
                $tipo_mensaje = 'success';
                $usuario = $appUsuario->readOne($usuario_id);
            } else {
                $mensaje = 'No se realizaron cambios';
                $tipo_mensaje = 'info';
            }
        }
    } elseif(isset($_POST['cambiar_password'])) {
        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];
        
        // Validar contraseña actual
        if(!password_verify($password_actual, $usuario['password'])) {
            $mensaje = 'La contraseña actual es incorrecta';
            $tipo_mensaje = 'danger';
        } elseif(strlen($password_nueva) < 6) {
            $mensaje = 'La nueva contraseña debe tener al menos 6 caracteres';
            $tipo_mensaje = 'warning';
        } elseif($password_nueva !== $password_confirmar) {
            $mensaje = 'Las contraseñas nuevas no coinciden';
            $tipo_mensaje = 'warning';
        } else {
            $filas = $appUsuario->updatePassword($usuario_id, $password_nueva);
            if($filas > 0) {
                $mensaje = 'Contraseña actualizada correctamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar la contraseña';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

$pageTitle = 'Mi Perfil - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-warning text-white mb-3">
                            <span class="fs-2 fw-bold">
                                <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="mb-1"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h5>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="perfil.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-person-fill me-2"></i> Mi perfil
                        </a>
                        <a href="pedidos.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-box-seam me-2"></i> Mis pedidos
                            <?php if($total_pedidos > 0): ?>
                                <span class="badge bg-warning text-dark float-end"><?= $total_pedidos ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="carrito.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-cart3 me-2"></i> Mi carrito
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart me-2"></i> Lista de deseos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-geo-alt me-2"></i> Direcciones
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">Estadísticas</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Pedidos realizados</small>
                            <strong><?= $total_pedidos ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Total gastado</small>
                            <strong class="text-success"><?= formatearPrecio($total_gastado) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Miembro desde</small>
                            <strong><?= date('Y', strtotime($usuario['fecha_registro'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if($mensaje): ?>
                <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
            <?php endif; ?>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-person-fill text-warning"></i> Información personal</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="perfil.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($usuario['email']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                                       maxlength="10">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="mb-3">Dirección de envío predeterminada</h5>
                        
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Calle y número</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                       value="<?= htmlspecialchars($usuario['ciudad'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Selecciona un estado</option>
                                    <?php 
                                    $estados = [
                                        'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas',
                                        'Chihuahua', 'Coahuila', 'Colima', 'Durango', 'Guanajuato', 'Guerrero', 'Hidalgo',
                                        'Jalisco', 'México', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca',
                                        'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora',
                                        'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas', 'Ciudad de México'
                                    ];
                                    foreach($estados as $edo):
                                    ?>
                                        <option value="<?= $edo ?>" <?= $usuario['estado'] == $edo ? 'selected' : '' ?>>
                                            <?= $edo ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                       value="<?= htmlspecialchars($usuario['codigo_postal'] ?? '') ?>"
                                       maxlength="5">
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" name="actualizar_perfil" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-shield-lock text-warning"></i> Seguridad</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="perfil.php" id="formPassword">
                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password_nueva" class="form-label">Nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_nueva" name="password_nueva" 
                                       required minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmar" class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" 
                                       required minlength="6">
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" name="cambiar_password" class="btn btn-warning">
                                <i class="bi bi-shield-check"></i> Cambiar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.list-group-item.active {
    background-color: #febd69;
    border-color: #febd69;
    color: #111;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('codigo_postal').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('formPassword').addEventListener('submit', function(e) {
    const nueva = document.getElementById('password_nueva').value;
    const confirmar = document.getElementById('password_confirmar').value;
    
    if(nueva !== confirmar) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
});
</script>

<?php include_once "views/footer.php"; ?>