<?php
require_once "../config/config.php";
require_once "../models/usuario.php";

if(!esAdmin()) redirect('login.php');

$app = new Usuario();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$mensaje = '';
$tipo_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['crear'])) {
        $data = [
            'nombre' => $app->sanitizar($_POST['nombre']),
            'apellido' => $app->sanitizar($_POST['apellido']),
            'email' => $app->sanitizar($_POST['email']),
            'telefono' => $app->sanitizar($_POST['telefono']),
            'direccion' => $app->sanitizar($_POST['direccion'] ?? ''),
            'ciudad' => $app->sanitizar($_POST['ciudad'] ?? ''),
            'estado' => $app->sanitizar($_POST['estado'] ?? ''),
            'codigo_postal' => $app->sanitizar($_POST['codigo_postal'] ?? ''),
            'password' => $_POST['password']
        ];
        
        if($app->create($data)) {
            $mensaje = 'Usuario creado exitosamente';
            $tipo_mensaje = 'success';
            $action = 'list';
        }
    } elseif(isset($_POST['actualizar'])) {
        $id = (int)$_POST['id_usuario'];
        $data = [
            'nombre' => $app->sanitizar($_POST['nombre']),
            'apellido' => $app->sanitizar($_POST['apellido']),
            'email' => $app->sanitizar($_POST['email']),
            'telefono' => $app->sanitizar($_POST['telefono']),
            'direccion' => $app->sanitizar($_POST['direccion']),
            'ciudad' => $app->sanitizar($_POST['ciudad']),
            'estado' => $app->sanitizar($_POST['estado']),
            'codigo_postal' => $app->sanitizar($_POST['codigo_postal']),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $app->update($data, $id);
        $mensaje = 'Usuario actualizado';
        $tipo_mensaje = 'success';
        $action = 'list';
    } elseif(isset($_POST['eliminar'])) {
        $app->delete((int)$_POST['id_usuario']);
        $mensaje = 'Usuario eliminado';
        $tipo_mensaje = 'success';
    }
}

$usuarios = $app->read();
$pageTitle = 'Gestión de Usuarios';
include_once "views/header.php";
?>

<?php if($action == 'list'): ?>
    <h1 class="h3 mb-2 text-gray-800">Gestión de Usuarios</h1>
    <p class="mb-4">Administra todos los usuarios registrados en la plataforma</p>

    <?php if($mensaje): echo mostrarAlerta($mensaje, $tipo_mensaje); endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
            <a href="?action=create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nuevo Usuario
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $usuario): ?>
                            <tr>
                                <td><?= $usuario['id_usuario'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['telefono'] ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                <td>
                                    <?php if($usuario['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?action=edit&id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                                        <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                        <button type="submit" name="eliminar" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php
    $usuario = null;
    if($action == 'edit' && isset($_GET['id'])) {
        $usuario = $app->readOne((int)$_GET['id']);
    }
    ?>
    
    <h1 class="h3 mb-2 text-gray-800"><?= $action == 'create' ? 'Nuevo Usuario' : 'Editar Usuario' ?></h1>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST">
                <?php if($usuario): ?>
                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" required
                               value="<?= $usuario ? htmlspecialchars($usuario['nombre']) : '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido *</label>
                        <input type="text" class="form-control" name="apellido" required
                               value="<?= $usuario ? htmlspecialchars($usuario['apellido']) : '' ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required
                               value="<?= $usuario ? htmlspecialchars($usuario['email']) : '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono"
                               value="<?= $usuario ? htmlspecialchars($usuario['telefono']) : '' ?>">
                    </div>
                </div>
                
                <?php if($action == 'create'): ?>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control" name="direccion"
                           value="<?= $usuario ? htmlspecialchars($usuario['direccion']) : '' ?>">
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad"
                               value="<?= $usuario ? htmlspecialchars($usuario['ciudad']) : '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control" name="estado"
                               value="<?= $usuario ? htmlspecialchars($usuario['estado']) : '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Código Postal</label>
                        <input type="text" class="form-control" name="codigo_postal"
                               value="<?= $usuario ? htmlspecialchars($usuario['codigo_postal']) : '' ?>">
                    </div>
                </div>
                
                <?php if($action == 'edit'): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo"
                               <?= $usuario && $usuario['activo'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activo">Usuario activo</label>
                    </div>
                <?php endif; ?>
                
                <div class="text-end">
                    <a href="usuario.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="<?= $action == 'create' ? 'crear' : 'actualizar' ?>" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include_once "views/footer.php"; ?>