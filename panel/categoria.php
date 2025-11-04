<?php
require_once "../config/config.php";
require_once "../models/categoria.php";

if(!esAdmin()) redirect('login.php');

$app = new Categoria();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$mensaje = '';
$tipo_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['crear'])) {
        $data = [
            'nombre' => $app->sanitizar($_POST['nombre']),
            'descripcion' => $app->sanitizar($_POST['descripcion']),
            'imagen' => ''
        ];
        
        $imagen = $app->cargarImagen('imagen', 'categorias');
        if($imagen) {
            $data['imagen'] = $imagen;
        }
        
        if($app->create($data)) {
            $mensaje = 'Categoría creada exitosamente';
            $tipo_mensaje = 'success';
            $action = 'list';
        }
    } elseif(isset($_POST['actualizar'])) {
        $id = (int)$_POST['id_categoria'];
        $categoria_actual = $app->readOne($id);
        
        $data = [
            'nombre' => $app->sanitizar($_POST['nombre']),
            'descripcion' => $app->sanitizar($_POST['descripcion']),
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'imagen' => $categoria_actual['imagen']
        ];
        
        $nueva_imagen = $app->cargarImagen('imagen', 'categorias');
        if($nueva_imagen) {
            if($categoria_actual['imagen']) {
                $app->eliminarImagen('categorias', $categoria_actual['imagen']);
            }
            $data['imagen'] = $nueva_imagen;
        }
        
        $app->update($data, $id);
        $mensaje = 'Categoría actualizada';
        $tipo_mensaje = 'success';
        $action = 'list';
    } elseif(isset($_POST['eliminar'])) {
        $id = (int)$_POST['id_categoria'];
        $categoria = $app->readOne($id);
        
        if($categoria['imagen']) {
            $app->eliminarImagen('categorias', $categoria['imagen']);
        }
        
        $app->delete($id);
        $mensaje = 'Categoría eliminada';
        $tipo_mensaje = 'success';
    }
}

$categorias = $app->readAll();
$pageTitle = 'Gestión de Categorías';
include_once "views/header.php";
?>

<?php if($action == 'list'): ?>
    <h1 class="h3 mb-2 text-gray-800">Gestión de Categorías</h1>
    <p class="mb-4">Administra las categorías de productos de la plataforma</p>

    <?php if($mensaje): echo mostrarAlerta($mensaje, $tipo_mensaje); endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Categorías</h6>
            <a href="?action=create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nueva Categoría
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categorias as $categoria): ?>
                            <tr>
                                <td><?= $categoria['id_categoria'] ?></td>
                                <td>
                                    <?php if($categoria['imagen']): ?>
                                        <img src="../img/categorias/<?= $categoria['imagen'] ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($categoria['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($categoria['descripcion'], 0, 50)) ?>...</td>
                                <td>
                                    <?php if($categoria['activo']): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    require_once "../models/producto.php";
                                    $appProd = new Producto();
                                    $productos = $appProd->readByCategoria($categoria['id_categoria']);
                                    echo count($productos);
                                    ?>
                                </td>
                                <td>
                                    <a href="?action=edit&id=<?= $categoria['id_categoria'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="../productos.php?categoria=<?= $categoria['id_categoria'] ?>" 
                                       class="btn btn-sm btn-info" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" style="display:inline;" 
                                          onsubmit="return confirm('¿Eliminar? Los productos de esta categoría quedarán sin categoría.');">
                                        <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
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
    $categoria = null;
    if($action == 'edit' && isset($_GET['id'])) {
        $categoria = $app->readOne((int)$_GET['id']);
    }
    ?>
    
    <h1 class="h3 mb-2 text-gray-800">
        <?= $action == 'create' ? 'Nueva Categoría' : 'Editar Categoría' ?>
    </h1>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if($categoria): ?>
                    <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" required
                                   value="<?= $categoria ? htmlspecialchars($categoria['nombre']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="4"><?= $categoria ? htmlspecialchars($categoria['descripcion']) : '' ?></textarea>
                        </div>
                        
                        <?php if($action == 'edit'): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="activo" id="activo"
                                       <?= $categoria && $categoria['activo'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="activo">
                                    Categoría activa
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Imagen</label>
                            <?php if($categoria && $categoria['imagen']): ?>
                                <div class="mb-2">
                                    <img src="../img/categorias/<?= $categoria['imagen'] ?>" 
                                         class="img-fluid rounded" alt="Imagen actual">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                            <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <a href="categoria.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="<?= $action == 'create' ? 'crear' : 'actualizar' ?>" 
                            class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include_once "views/footer.php"; ?>