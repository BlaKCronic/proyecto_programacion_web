<?php
session_start();
$isLoggedIn = isset($_SESSION['usuario_id']);
$userName = $isLoggedIn ? $_SESSION['usuario_nombre'] : 'Invitado';
$cartCount = $isLoggedIn ? ($_SESSION['cart_count'] ?? 0) : 0;
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
    <link rel="stylesheet" href="../styles/main.css">
</head>
<body>
    <header class="header-main">
        <nav class="navbar navbar-dark bg-amazon-dark px-3 py-2">
            <div class="container-fluid">
                <div class="d-flex align-items-center w-100">
                    <a href="../index.php" class="logo me-3">
                        <img src="../img/logo/logo.png" width="100" alt="Amazon Lite">
                    </a>
                    <div class="nav-location text-white me-3 hover-border">
                        <i class="bi bi-geo-alt"></i>
                        <div class="d-inline-block ms-1">
                            <span class="small-text d-block">Enviar a</span>
                            <span class="bold-text">México</span>
                        </div>
                    </div>
                    <form action="../busqueda.php" method="GET" class="search-bar d-flex flex-grow-1 me-3">
                        <select name="categoria" class="form-select search-select">
                            <option value="">Todos</option>
                            <option value="1">Electrónicos</option>
                            <option value="2">Libros</option>
                            <option value="3">Ropa</option>
                            <option value="4">Hogar</option>
                            <option value="5">Deportes</option>
                        </select>
                        <input type="text" name="q" class="form-control search-input" placeholder="Buscar en Amazon Lite">
                        <button type="submit" class="btn btn-warning search-btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    <div class="nav-lang d-flex align-items-center text-white me-3 hover-border">
                        <img src="../img/icons/mx-flag.png" width="20" alt="MX" class="me-1">
                        <span class="bold-text">ES</span>
                    </div>
                    <div class="nav-account text-white me-3 hover-border" onclick="location.href='<?= $isLoggedIn ? '/perfil.php' : '/login.php' ?>'">
                        <span class="small-text d-block">Hola, <?= htmlspecialchars($userName) ?></span>
                        <span class="bold-text">Cuenta y Listas <i class="bi bi-chevron-down"></i></span>
                    </div>
                    <div class="nav-orders text-white me-3 hover-border" onclick="location.href='/pedidos.php'">
                        <span class="small-text d-block">Devoluciones</span>
                        <span class="bold-text">y Pedidos</span>
                    </div>
                    <div class="nav-cart d-flex align-items-center text-white hover-border position-relative" onclick="location.href='/carrito.php'">
                        <i class="bi bi-cart3 fs-4"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                        <span class="bold-text ms-2">Carrito</span>
                    </div>
                </div>
            </div>
        </nav>
        <nav class="navbar navbar-dark bg-amazon-light px-3 py-2">
            <div class="container-fluid">
                <div class="d-flex align-items-center w-100">
                    <a href="#" class="text-white text-decoration-none me-3 hover-underline">
                        <i class="bi bi-list fs-4"></i> Todas las categorías
                    </a>
                    <a href="../productos.php?destacados=1" class="text-white text-decoration-none me-3 hover-underline">Ofertas del día</a>
                    <a href="../productos.php?categoria=1" class="text-white text-decoration-none me-3 hover-underline">Electrónicos</a>
                    <a href="../productos.php?categoria=2" class="text-white text-decoration-none me-3 hover-underline">Libros</a>
                    <a href="../productos.php?categoria=3" class="text-white text-decoration-none me-3 hover-underline">Moda</a>
                    <a href="../productos.php?nuevos=1" class="text-white text-decoration-none me-3 hover-underline">Nuevos lanzamientos</a>
                    <a href="../vendedor/registro.php" class="text-white text-decoration-none hover-underline">
                        <i class="bi bi-shop"></i> Vende con nosotros
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">