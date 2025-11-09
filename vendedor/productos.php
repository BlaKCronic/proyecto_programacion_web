<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";
require_once "../models/producto.php";
require_once "../models/categoria.php";
require_once "../models/Validator.php";

if(!esVendedor()) {
    redirect('login.php');
}

$appVendedor = new Vendedor();
$appProducto = new Producto();
$appCategoria = new Categoria();

$vendedor_id = $_SESSION['vendedor_id'];
$vendedor = $appVendedor->readOne($vendedor_id);
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

$mensaje = '';
$tipo_mensaje = '';

$categorias = $appCategoria->read();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['crear_producto'])) {
        $data = [];
        $data['id_vendedor'] = $vendedor_id;
        $data['id_categoria'] = (int)$_POST['id_categoria'];
        $data['nombre'] = $appProducto->sanitizar($_POST['nombre']);
        $data['descripcion'] = $appProducto->sanitizar($_POST['descripcion']);
        $data['precio'] = (float)$_POST['precio'];
        $data['precio_descuento'] = !empty($_POST['precio_descuento']) ? (float)$_POST['precio_descuento'] : null;
        $data['stock'] = (int)$_POST['stock'];
        $data['sku'] = $appProducto->sanitizar($_POST['sku']);
        $data['marca'] = $appProducto->sanitizar($_POST['marca']);
        $data['peso'] = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;
        $data['dimensiones'] = $appProducto->sanitizar($_POST['dimensiones']);
        $validacion = ValidatorHelper::validarProducto($data);

        if(!$validacion['valido']) {
            $mensaje = ValidatorHelper::formatearErrores($validacion['errores']);
            $tipo_mensaje = 'danger';
            $action = 'create';
        } else {
            $data['imagen_principal'] = $appProducto->cargarImagen('imagen_principal', 'productos');
            $data['imagenes_adicionales'] = $appProducto->cargarImagenesAdicionales('productos');

            if($data['imagen_principal']) {
                $id_producto = $appProducto->create($data);
                if($id_producto) {
                    $mensaje = 'Producto creado exitosamente';
                    $tipo_mensaje = 'success';
                    $action = 'list';
                } else {
                    $mensaje = 'Error al crear el producto';
                    $tipo_mensaje = 'danger';
                }
            } else {
                $mensaje = 'Debes cargar al menos una imagen principal';
                $tipo_mensaje = 'warning';
                $action = 'create';
            }
        }
    } elseif(isset($_POST['actualizar_producto'])) {
        $id_producto = (int)$_POST['id_producto'];
        $producto_actual = $appProducto->readOne($id_producto);
        
        if($producto_actual['id_vendedor'] == $vendedor_id) {
            $data = [];
            $data['id_categoria'] = (int)$_POST['id_categoria'];
            $data['nombre'] = $appProducto->sanitizar($_POST['nombre']);
            $data['descripcion'] = $appProducto->sanitizar($_POST['descripcion']);
            $data['precio'] = (float)$_POST['precio'];
            $data['precio_descuento'] = !empty($_POST['precio_descuento']) ? (float)$_POST['precio_descuento'] : null;
            $data['stock'] = (int)$_POST['stock'];
            $data['sku'] = $appProducto->sanitizar($_POST['sku']);
            $data['marca'] = $appProducto->sanitizar($_POST['marca']);
            $data['peso'] = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;
            $data['dimensiones'] = $appProducto->sanitizar($_POST['dimensiones']);
            $data['activo'] = isset($_POST['activo']) ? 1 : 0;

            $validacion = ValidatorHelper::validarProducto($data);

            if(!$validacion['valido']) {
                $mensaje = ValidatorHelper::formatearErrores($validacion['errores']);
                $tipo_mensaje = 'danger';
                $action = 'edit';
            } else {
                $nueva_imagen = $appProducto->cargarImagen('imagen_principal', 'productos');
                $data['imagen_principal'] = $nueva_imagen ? $nueva_imagen : $producto_actual['imagen_principal'];

                $nuevas_imagenes = $appProducto->cargarImagenesAdicionales('productos');
                $data['imagenes_adicionales'] = $nuevas_imagenes ? $nuevas_imagenes : $producto_actual['imagenes_adicionales'];

                $filas = $appProducto->update($data, $id_producto);
                $mensaje = 'Producto actualizado exitosamente';
                $tipo_mensaje = 'success';
                $action = 'list';
            }
        }
    } elseif(isset($_POST['eliminar_producto'])) {
        $id_producto = (int)$_POST['id_producto'];
        $producto = $appProducto->readOne($id_producto);
        
        if($producto['id_vendedor'] == $vendedor_id) {
            $appProducto->delete($id_producto);
            $mensaje = 'Producto eliminado exitosamente';
            $tipo_mensaje = 'success';
        }
    }
}

$productos = $appProducto->readByVendedor($vendedor_id);

if(isset($_GET['sin_stock']) && $_GET['sin_stock'] == 1) {
    $productos = array_filter($productos, function($p) { return $p['stock'] == 0; });
}

$pageTitle = 'Mis Productos - ' . $vendedor['nombre_tienda'];
include_once "views/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "views/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if($action == 'list'): ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-box-seam text-warning"></i> Mis Productos
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="?action=create" class="btn btn-warning">
                            <i class="bi bi-plus-circle"></i> Nuevo Producto
                        </a>
                    </div>
                </div>

                <?php if($mensaje): ?>
                    <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary"><?= count($productos) ?></h3>
                                <p class="text-muted mb-0">Total Productos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?= count(array_filter($productos, fn($p) => $p['activo'])) ?></h3>
                                <p class="text-muted mb-0">Activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning"><?= count(array_filter($productos, fn($p) => $p['stock'] < 10 && $p['stock'] > 0)) ?></h3>
                                <p class="text-muted mb-0">Stock Bajo</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-danger"><?= count(array_filter($productos, fn($p) => $p['stock'] == 0)) ?></h3>
                                <p class="text-muted mb-0">Sin Stock</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
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
                                                <td>
                                                    <?php if($producto['precio_descuento']): ?>
                                                        <span class="text-danger fw-bold"><?= formatearPrecio($producto['precio_descuento']) ?></span><br>
                                                        <small class="text-muted text-decoration-line-through"><?= formatearPrecio($producto['precio']) ?></small>
                                                    <?php else: ?>
                                                        <?= formatearPrecio($producto['precio']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($producto['stock'] == 0): ?>
                                                        <span class="badge bg-danger">Sin stock</span>
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
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="../producto_detalle.php?id=<?= $producto['id_producto'] ?>" 
                                                       class="btn btn-sm btn-outline-info" title="Ver" target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('¿Eliminar este producto?');">
                                                        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                                                        <button type="submit" name="eliminar_producto" 
                                                                class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                                <p class="text-muted">No tienes productos registrados</p>
                                                <a href="?action=create" class="btn btn-warning">
                                                    <i class="bi bi-plus-circle"></i> Crear primer producto
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif($action == 'create' || $action == 'edit'): ?>
                <?php
                $producto = null;
                if($action == 'edit' && isset($_GET['id'])) {
                    $producto = $appProducto->readOne((int)$_GET['id']);
                    if(!$producto || $producto['id_vendedor'] != $vendedor_id) {
                        redirect('productos.php');
                    }
                }
                ?>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-<?= $action == 'create' ? 'plus-circle' : 'pencil' ?> text-warning"></i>
                        <?= $action == 'create' ? 'Nuevo Producto' : 'Editar Producto' ?>
                    </h1>
                    <a href="productos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?php if($action == 'edit'): ?>
                                <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-3">Información básica</h5>
                                    
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre del producto <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required
                                               value="<?= $producto ? htmlspecialchars($producto['nombre']) : '' ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?= $producto ? htmlspecialchars($producto['descripcion']) : '' ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                                <option value="">Selecciona una categoría</option>
                                                <?php foreach($categorias as $cat): ?>
                                                    <option value="<?= $cat['id_categoria'] ?>" 
                                                            <?= ($producto && $producto['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cat['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="marca" class="form-label">Marca</label>
                                            <input type="text" class="form-control" id="marca" name="marca"
                                                   value="<?= $producto ? htmlspecialchars($producto['marca']) : '' ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="precio" name="precio" 
                                                       step="0.01" min="0" required
                                                       value="<?= $producto ? $producto['precio'] : '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="precio_descuento" class="form-label">Precio con descuento</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="precio_descuento" name="precio_descuento" 
                                                       step="0.01" min="0"
                                                       value="<?= $producto ? $producto['precio_descuento'] : '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stock" name="stock" 
                                                   min="0" required
                                                   value="<?= $producto ? $producto['stock'] : '' ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sku" name="sku" required
                                                   value="<?= $producto ? htmlspecialchars($producto['sku']) : '' ?>">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="peso" class="form-label">Peso (kg)</label>
                                            <input type="number" class="form-control" id="peso" name="peso" 
                                                   step="0.01" min="0"
                                                   value="<?= $producto ? $producto['peso'] : '' ?>">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="dimensiones" class="form-label">Dimensiones (cm)</label>
                                            <input type="text" class="form-control" id="dimensiones" name="dimensiones" 
                                                   placeholder="Largo x Ancho x Alto"
                                                   value="<?= $producto ? htmlspecialchars($producto['dimensiones']) : '' ?>">
                                        </div>
                                    </div>

                                    <?php if($action == 'edit'): ?>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                                   <?= $producto['activo'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="activo">
                                                Producto activo (visible en la tienda)
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="mb-3">Imágenes</h5>
                                    
                                    <div class="mb-3">
                                        <label for="imagen_principal" class="form-label">
                                            Imagen principal <span class="text-danger">*</span>
                                        </label>
                                        <?php if($producto && $producto['imagen_principal']): ?>
                                            <div class="mb-2">
                                                <img src="../img/productos/<?= $producto['imagen_principal'] ?>" 
                                                     class="img-fluid rounded" alt="Imagen actual">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                               accept="image/*" <?= $action == 'create' ? 'required' : '' ?>>
                                        <small class="text-muted">Formatos: JPG, PNG, GIF (Max 5MB)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="imagenes_adicionales" class="form-label">Imágenes adicionales</label>
                                        <input type="file" class="form-control" id="imagenes_adicionales" 
                                               name="imagenes_adicionales[]" accept="image/*" multiple>
                                        <small class="text-muted">Puedes seleccionar varias imágenes</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="text-end">
                                <a href="productos.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" name="<?= $action == 'create' ? 'crear_producto' : 'actualizar_producto' ?>" 
                                        class="btn btn-warning">
                                    <i class="bi bi-check-circle"></i> 
                                    <?= $action == 'create' ? 'Crear Producto' : 'Guardar Cambios' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once "views/footer.php"; ?>