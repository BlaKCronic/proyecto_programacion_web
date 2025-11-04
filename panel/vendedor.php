<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";

if(!esAdmin()) redirect('login.php');

$app = new Vendedor();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$mensaje = '';
$tipo_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['aprobar'])) {
        $id = (int)$_POST['id_vendedor'];
        $app->updateEstadoAprobacion($id, 'aprobado');
        $mensaje = 'Vendedor aprobado exitosamente';
        $tipo_mensaje = 'success';
    } elseif(isset($_POST['rechazar'])) {
        $id = (int)$_POST['id_vendedor'];
        $app->updateEstadoAprobacion($id, 'rechazado');
        $mensaje = 'Vendedor rechazado';
        $tipo_mensaje = 'warning';
    } elseif(isset($_POST['actualizar'])) {
        $id = (int)$_POST['id_vendedor'];
        $data = [
            'nombre_tienda' => $app->sanitizar($_POST['nombre_tienda']),
            'email' => $app->sanitizar($_POST['email']),
            'nombre_contacto' => $app->sanitizar($_POST['nombre_contacto']),
            'telefono' => $app->sanitizar($_POST['telefono']),
            'direccion' => $app->sanitizar($_POST['direccion']),
            'rfc' => $app->sanitizar($_POST['rfc']),
            'razon_social' => $app->sanitizar($_POST['razon_social']),
            'descripcion' => $app->sanitizar($_POST['descripcion']),
            'logo' => '', // Mantener el existente
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $vendedor_actual = $app->readOne($id);
        $data['logo'] = $vendedor_actual['logo'];
        
        $app->update($data, $id);
        $mensaje = 'Vendedor actualizado';
        $tipo_mensaje = 'success';
        $action = 'list';
    } elseif(isset($_POST['eliminar'])) {
        $app->delete((int)$_POST['id_vendedor']);
        $mensaje = 'Vendedor eliminado';
        $tipo_mensaje = 'success';
    }
}

if($action == 'aprobar') {
    $vendedores = $app->readPendientes();
} else {
    $vendedores = $app->read();
}

$pageTitle = 'Gestión de Vendedores';
include_once "views/header.php";
?>

<?php if($action == 'list'): ?>
    <h1 class="h3 mb-2 text-gray-800">Gestión de Vendedores</h1>
    <p class="mb-4">Administra todos los vendedores de la plataforma</p>

    <?php if($mensaje): echo mostrarAlerta($mensaje, $tipo_mensaje); endif; ?>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <a href="?action=list" class="btn btn-<?= $action == 'list' ? 'primary' : 'outline-primary' ?>">
                    Todos los vendedores
                </a>
                <a href="?action=aprobar" class="btn btn-<?= $action == 'aprobar' ? 'warning' : 'outline-warning' ?>">
                    Pendientes de aprobación (<?= count($app->readPendientes()) ?>)
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= $action == 'aprobar' ? 'Vendedores Pendientes' : 'Lista de Vendedores' ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tienda</th>
                            <th>Contacto</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                            <th>Ventas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($vendedores as $vendedor): ?>
                            <tr>
                                <td><?= $vendedor['id_vendedor'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($vendedor['nombre_tienda']) ?></strong>
                                    <?php if($vendedor['logo']): ?>
                                        <br><img src="../img/vendedores/<?= $vendedor['logo'] ?>" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($vendedor['nombre_contacto']) ?></td>
                                <td><?= htmlspecialchars($vendedor['email']) ?></td>
                                <td><?= htmlspecialchars($vendedor['telefono']) ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'pendiente' => 'warning',
                                        'aprobado' => 'success',
                                        'rechazado' => 'danger'
                                    ];
                                    $color = $badges[$vendedor['estado_aprobacion']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst($vendedor['estado_aprobacion']) ?>
                                    </span>
                                    <br>
                                    <?php if($vendedor['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= generarEstrellas($vendedor['calificacion_promedio']) ?>
                                    <br>
                                    <small>(<?= number_format($vendedor['calificacion_promedio'], 1) ?>)</small>
                                </td>
                                <td><?= $vendedor['total_ventas'] ?></td>
                                <td>
                                    <?php if($vendedor['estado_aprobacion'] == 'pendiente'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id_vendedor" value="<?= $vendedor['id_vendedor'] ?>">
                                            <button type="submit" name="aprobar" class="btn btn-sm btn-success" 
                                                    title="Aprobar">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('¿Rechazar este vendedor?');">
                                            <input type="hidden" name="id_vendedor" value="<?= $vendedor['id_vendedor'] ?>">
                                            <button type="submit" name="rechazar" class="btn btn-sm btn-danger" 
                                                    title="Rechazar">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="?action=edit&id=<?= $vendedor['id_vendedor'] ?>" 
                                       class="btn btn-sm btn-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <a href="../vendedor/dashboard.php" target="_blank" 
                                       class="btn btn-sm btn-info" title="Ver tienda">
                                        <i class="bi bi-shop"></i>
                                    </a>
                                    
                                    <form method="POST" style="display:inline;" 
                                          onsubmit="return confirm('¿Eliminar vendedor? Esto eliminará todos sus productos.');">
                                        <input type="hidden" name="id_vendedor" value="<?= $vendedor['id_vendedor'] ?>">
                                        <button type="submit" name="eliminar" class="btn btn-sm btn-danger" 
                                                title="Eliminar">
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

<?php elseif($action == 'edit'): ?>
    <?php
    $vendedor = null;
    if(isset($_GET['id'])) {
        $vendedor = $app->readOne((int)$_GET['id']);
    }
    
    if(!$vendedor) redirect('vendedor.php');
    ?>
    
    <h1 class="h3 mb-2 text-gray-800">Editar Vendedor</h1>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id_vendedor" value="<?= $vendedor['id_vendedor'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre de la Tienda *</label>
                        <input type="text" class="form-control" name="nombre_tienda" required
                               value="<?= htmlspecialchars($vendedor['nombre_tienda']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required
                               value="<?= htmlspecialchars($vendedor['email']) ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre de Contacto *</label>
                        <input type="text" class="form-control" name="nombre_contacto" required
                               value="<?= htmlspecialchars($vendedor['nombre_contacto']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono *</label>
                        <input type="text" class="form-control" name="telefono" required
                               value="<?= htmlspecialchars($vendedor['telefono']) ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Dirección *</label>
                    <input type="text" class="form-control" name="direccion" required
                           value="<?= htmlspecialchars($vendedor['direccion']) ?>">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">RFC</label>
                        <input type="text" class="form-control" name="rfc"
                               value="<?= htmlspecialchars($vendedor['rfc']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Razón Social</label>
                        <input type="text" class="form-control" name="razon_social"
                               value="<?= htmlspecialchars($vendedor['razon_social']) ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($vendedor['descripcion']) ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado de Aprobación</label>
                        <div>
                            <span class="badge bg-<?= $badges[$vendedor['estado_aprobacion']] ?? 'secondary' ?>">
                                <?= ucfirst($vendedor['estado_aprobacion']) ?>
                            </span>
                        </div>
                        <small class="text-muted">Usa los botones de aprobar/rechazar en la lista</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Información</label>
                        <div>
                            <p class="mb-1"><strong>Calificación:</strong> <?= number_format($vendedor['calificacion_promedio'], 1) ?></p>
                            <p class="mb-1"><strong>Total Ventas:</strong> <?= $vendedor['total_ventas'] ?></p>
                            <p class="mb-0"><strong>Comisión:</strong> <?= $vendedor['comision'] ?>%</p>
                        </div>
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo"
                           <?= $vendedor['activo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">Vendedor activo</label>
                </div>
                
                <div class="text-end">
                    <a href="vendedor.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="actualizar" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include_once "views/footer.php"; ?>