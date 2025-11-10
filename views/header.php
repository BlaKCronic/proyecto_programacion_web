<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("Header - Verificación de sesión:");
error_log("Session ID: " . session_id());
error_log("Session Status: " . session_status());
error_log("Usuario ID en sesión: " . ($_SESSION['usuario_id'] ?? 'no establecido'));

$isLoggedIn = !empty($_SESSION['usuario_id']);
error_log("¿Está logueado?: " . ($isLoggedIn ? 'Sí' : 'No'));

$userName = $isLoggedIn ? (string) ($_SESSION['usuario_nombre'] ?? 'Usuario') : 'Invitado';
$cartCount = $isLoggedIn ? (int) ($_SESSION['cart_count'] ?? 0) : 0;

if(!isset($categorias)) {
    require_once __DIR__ . "/../models/categoria.php";
    $catApp = new Categoria();
    $categorias = $catApp->read();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Amazon Lite' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/amazon-ember" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <header class="header-main">
        <nav class="navbar navbar-dark bg-amazon-dark px-3 py-2">
            <div class="container-fluid">
                <div class="d-flex align-items-center w-100">
                    <a href="index.php" class="logo me-3">
                        <img src="img/logo/logo.png" width="100" alt="Amazon Lite">
                    </a>
                    
                    <div class="nav-location text-white me-3 hover-border d-none d-md-flex">
                        <i class="bi bi-geo-alt"></i>
                        <div class="d-inline-block ms-1">
                            <span class="small-text d-block">Enviar a</span>
                            <span class="bold-text">México</span>
                        </div>
                    </div>
                    
                    <form action="busqueda.php" method="GET" class="search-bar d-flex flex-grow-1 me-3">
                        <select name="categoria" class="form-select search-select d-none d-md-block">
                            <option value="">Todos</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>">
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="q" class="form-control search-input" 
                               placeholder="Buscar en Amazon Lite" 
                               value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                        <button type="submit" class="btn btn-warning search-btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    
                    <div class="nav-lang d-none d-md-flex align-items-center text-white me-3 hover-border">
                        <img src="img/icons/mx-flag.png" width="20" alt="MX" class="me-1" 
                             onerror="this.style.display='none'">
                        <span class="bold-text">ES</span>
                    </div>
                    
                    <div class="nav-account text-white me-3 hover-border" 
                         onclick="location.href='<?= $isLoggedIn ? 'perfil.php' : 'login.php' ?>'">
                        <span class="small-text d-block">Hola, <?= htmlspecialchars($userName) ?></span>
                        <span class="bold-text">
                            Cuenta y Listas <i class="bi bi-chevron-down"></i>
                        </span>
                    </div>
                    
                    <div class="nav-orders text-white me-3 hover-border d-none d-lg-block" 
                         onclick="location.href='pedidos.php'">
                        <span class="small-text d-block">Devoluciones</span>
                        <span class="bold-text">y Pedidos</span>
                    </div>
                    
                    <div class="nav-cart d-flex align-items-center text-white hover-border position-relative" 
                         onclick="location.href='carrito.php'">
                        <i class="bi bi-cart3 fs-4"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                        <span class="bold-text ms-2 d-none d-md-inline">Carrito</span>
                    </div>
                </div>
            </div>
        </nav>
        
        <nav class="navbar navbar-dark bg-amazon-light px-3 py-2">
            <div class="container-fluid">
                <div class="d-flex align-items-center w-100 flex-wrap">
                    <a href="#" class="text-white text-decoration-none me-3 hover-underline" 
                       data-bs-toggle="offcanvas" data-bs-target="#menuCategorias">
                        <i class="bi bi-list fs-4"></i> Todas las categorías
                    </a>
                    <a href="productos.php?destacados=1" class="text-white text-decoration-none me-3 hover-underline">
                        Ofertas del día
                    </a>
                    <?php foreach(array_slice($categorias, 0, 4) as $cat): ?>
                        <a href="productos.php?categoria=<?= $cat['id_categoria'] ?>" 
                           class="text-white text-decoration-none me-3 hover-underline d-none d-md-inline">
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="productos.php?nuevos=1" class="text-white text-decoration-none me-3 hover-underline">
                        Nuevos lanzamientos
                    </a>
                    <a href="vendedor/registro.php" class="text-white text-decoration-none hover-underline ms-auto">
                        <i class="bi bi-shop"></i> Vende con nosotros
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="menuCategorias">
        <div class="offcanvas-header bg-amazon-dark text-white">
            <h5 class="offcanvas-title">
                <i class="bi bi-person-circle me-2"></i>
                Hola, <?= htmlspecialchars($userName) ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="list-group list-group-flush">
                <div class="list-group-item bg-light fw-bold">
                    <i class="bi bi-star-fill text-warning me-2"></i>Tendencias
                </div>
                <a href="productos.php?destacados=1" class="list-group-item list-group-item-action">
                    Ofertas del día
                </a>
                <a href="productos.php?nuevos=1" class="list-group-item list-group-item-action">
                    Nuevos lanzamientos
                </a>
                
                <div class="list-group-item bg-light fw-bold">
                    <i class="bi bi-grid-fill text-warning me-2"></i>Comprar por categoría
                </div>
                <?php foreach($categorias as $cat): ?>
                    <a href="productos.php?categoria=<?= $cat['id_categoria'] ?>" 
                       class="list-group-item list-group-item-action">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </a>
                <?php endforeach; ?>
                
                <?php if($isLoggedIn): ?>
                    <div class="list-group-item bg-light fw-bold">
                        <i class="bi bi-person-fill text-warning me-2"></i>Tu cuenta
                    </div>
                    <a href="perfil.php" class="list-group-item list-group-item-action">
                        Tu perfil
                    </a>
                    <a href="pedidos.php" class="list-group-item list-group-item-action">
                        Tus pedidos
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                    </a>
                <?php else: ?>
                    <div class="list-group-item bg-light fw-bold">
                        <i class="bi bi-person-fill text-warning me-2"></i>Cuenta
                    </div>
                    <a href="login.php" class="list-group-item list-group-item-action">
                        Iniciar sesión
                    </a>
                    <a href="registro.php" class="list-group-item list-group-item-action">
                        Crear cuenta
                    </a>
                <?php endif; ?>
                
                <div class="list-group-item bg-light fw-bold">
                    <i class="bi bi-shop text-warning me-2"></i>Para vendedores
                </div>
                <a href="vendedor/registro.php" class="list-group-item list-group-item-action">
                    Vende con nosotros
                </a>
            </div>
        </div>
    </div>

    <main class="main-content">