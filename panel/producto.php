<?php
require_once "../config/config.php";
require_once "../models/producto.php";
require_once "../models/categoria.php";
require_once "../models/vendedor.php";

if(!esAdmin()) redirect('login.php');

$appProducto = new Producto();
$appCategoria = new Categoria();
$appVendedor = new Vendedor();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$mensaje = '';
$tipo_mensaje = '';

$categorias = $appCategoria->read();
$vendedores = $appVendedor->readAprobados();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['actualizar'])) {
        $id_producto = (int)$_POST['id_producto'];
        $producto_actual = $appProducto->readOne($id_producto);
        
        $data = [
            'id_categoria' => (int)$_POST['id_categoria'],
            'nombre' => $appProducto->sanitizar($_POST['nombre']),
            'descripcion' => $appProducto->sanitizar($_POST['descripcion']),
            'precio' => (float)$_POST['precio'],
            'precio_descuento' => !empty($_POST['precio_descuento']) ? (float)$_POST['precio_descuento'] : null,
            'stock' => (int)$_POST['stock'],
            'sku' => $appProducto->sanitizar($_POST['sku']),
            'marca' => $appProducto->sanitizar($_POST['marca']),
            'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
            'dimensiones' => $appProducto->sanitizar($_POST['dimensiones']),
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'imagen_principal' => $producto_actual['imagen_principal'],
            'imagenes_adicionales' => $producto_actual['imagenes_adicionales']
        ];
        
        $appProducto->update($data, $id_producto);
        $mensaje = 'Producto actualizado';
        $tipo_mensaje = 'success';
        $action = 'list';
    } elseif(isset($_POST['eliminar'])) {
        $id_producto = (int)$_POST['id_producto'];
        $producto = $appProducto->readOne($id_producto);
        
        if($producto['imagen_principal']) {
            $appProducto->eliminarImagen('productos', $producto['imagen_principal']);
        }
        if($producto['imagenes_adicionales']) {
            $appProducto->eliminarMultiplesImagenes('productos', $producto['imagenes_adicionales']);
        }
        
        $appProducto->delete($id_producto);
        $mensaje = 'Producto eliminado';
        $tipo_mensaje = 'success';
    }
}

$productos = $appProducto->read();

$filtro_categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$filtro_vendedor = isset($_GET['vendedor']) ? (int)$_GET['vendedor'] : null;
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

if($filtro_categoria) {
    $productos = array_filter($productos, fn($p) => $p['id_categoria'] == $filtro_categoria);
}
if($filtro_vendedor) {
    $productos = array_filter($productos, fn($p) => $p['id_vendedor'] == $filtro_vendedor);
}
if($filtro_estado == 'activos') {
    $productos = array_filter($productos, fn($p) => $p['activo'] == 1);
} elseif($filtro_estado == 'inactivos') {
    $productos = array_filter($productos, fn($p) => $p['activo'] == 0);
} elseif($filtro_estado == 'sin_stock') {
    $productos = array_filter($productos, fn($p) => $p['stock'] == 0);
}

$pageTitle = 'Gestión de Productos';
include_once "views/header.php";
?>

<?php if($action == 'list'): ?>
    <h1 class="h3 mb-2 text-gray-800">Gestión de Productos</h1>
    <p class="mb-4">Administra todos los productos de la plataforma</p>

    <?php if($mensaje): echo mostrarAlerta($mensaje, $tipo_mensaje); endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" 
                                    <?= $filtro_categoria == $cat['id_categoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Vendedor</label>
                    <select name="vendedor" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <?php foreach($vendedores as $vend): ?>
                            <option value="<?= $vend['id_vendedor'] ?>" 
                                    <?= $filtro_vendedor == $vend['id_vendedor'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vend['nombre_tienda']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="activos" <?= $filtro_estado == 'activos' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivos" <?= $filtro_estado == 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                        <option value="sin_stock" <?= $filtro_estado == 'sin_stock' ? 'selected' : '' ?>>Sin stock</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="producto.php" class="btn btn-secondary w-100">Limpiar filtros</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($appProducto->read()) ?></h3>
                    <p class="text-muted mb-0">Total Productos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">
                        <?= count(array_filter($appProducto->read(), fn($p) => $p['activo'])) ?>
                    </h3>
                    <p class="text-muted mb-0">Activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?= count(array_filter($appProducto->read(), fn($p) => $p['stock'] < 10 && $p['stock'] > 0)) ?>
                    </h3>
                    <p class="text-muted mb-0">Stock Bajo</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">
                        <?= count(array_filter($appProducto->read(), fn($p) => $p['stock'] == 0)) ?>
                    </h3>
                    <p class="text-muted mb-0">Sin Stock</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Productos (<?= count($productos) ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Vendedor</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($productos)): ?>
                            <?php foreach($productos as $producto): ?>
                                <tr>
                                    <td><?= $producto['id_producto'] ?></td>
                                    <td>
                                        <?php if($producto['imagen_principal']): ?>
                                            <img src="../img/productos/<?= $producto['imagen_principal'] ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($producto['nombre']) ?></strong><br>
                                        <small class="text-muted">SKU: <?= htmlspecialchars($producto['sku']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($producto['categoria']) ?></td>
                                    <td><?= htmlspecialchars($producto['nombre_tienda']) ?></td>
                                    <td>
                                        <?php if($producto['precio_descuento']): ?>
                                            <span class="text-danger fw-bold">
                                                <?= formatearPrecio($producto['precio_descuento']) ?>
                                            </span><br>
                                            <small class="text-muted text-decoration-line-through">
                                                <?= formatearPrecio($producto['precio']) ?>
                                            </small>
                                        <?php else: ?>
                                            <?= formatearPrecio($producto['precio']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($producto['stock'] == 0): ?>
                                            <span class="badge bg-danger">0</span>
                                        <?php elseif($producto['stock'] < 10): ?>
                                            <span class="badge bg-warning"><?= $producto['stock'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?= $producto['stock'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($producto['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?action=edit&id=<?= $producto['id_producto'] ?>" 
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="../producto_detalle.php?id=<?= $producto['id_producto'] ?>" 
                                           class="btn btn-sm btn-info" title="Ver" target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('¿Eliminar este producto?');">
                                            <input type="hidden" name="id_producto" 
                                                   value="<?= $producto['id_producto'] ?>">
                                            <button type="submit" name="eliminar" 
                                                    class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    No hay productos con los filtros seleccionados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif($action == 'edit'): ?>
    <?php
    $producto = null;
    if(isset($_GET['id'])) {
        $producto = $appProducto->readOne((int)$_GET['id']);
    }
    if(!$producto) redirect('producto.php');
    ?>
    
    <h1 class="h3 mb-2 text-gray-800">Editar Producto</h1>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" required
                               value="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Categoría *</label>
                        <select class="form-select" name="id_categoria" required>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" 
                                        <?= $producto['id_categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">SKU *</label>
                        <input type="text" class="form-control" name="sku" required
                               value="<?= htmlspecialchars($producto['sku']) ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Precio *</label>
                        <input type="number" class="form-control" name="precio" step="0.01" required
                               value="<?= $producto['precio'] ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Precio descuento</label>
                        <input type="number" class="form-control" name="precio_descuento" step="0.01"
                               value="<?= $producto['precio_descuento'] ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock *</label>
                        <input type="number" class="form-control" name="stock" required
                               value="<?= $producto['stock'] ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Marca</label>
                        <input type="text" class="form-control" name="marca"
                               value="<?= htmlspecialchars($producto['marca']) ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Peso (kg)</label>
                        <input type="number" class="form-control" name="peso" step="0.01"
                               value="<?= $producto['peso'] ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dimensiones</label>
                        <input type="text" class="form-control" name="dimensiones"
                               value="<?= htmlspecialchars($producto['dimensiones']) ?>">
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo"
                           <?= $producto['activo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">
                        Producto activo
                    </label>
                </div>
                
                <div class="text-end">
                    <a href="producto.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="actualizar" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include_once "views/footer.php"; ?>