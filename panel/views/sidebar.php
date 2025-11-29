<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin Panel</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
        <a class="nav-link" href="index.php">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Gestión</div>

    <li class="nav-item <?= $current_page == 'usuario.php' ? 'active' : '' ?>">
        <a class="nav-link" href="usuario.php">
            <i class="bi bi-people"></i>
            <span>Usuarios</span>
        </a>
    </li>

    <li class="nav-item <?= $current_page == 'vendedor.php' ? 'active' : '' ?>">
        <a class="nav-link" href="vendedor.php">
            <i class="bi bi-shop-window"></i>
            <span>Vendedores</span>
        </a>
    </li>

    <li class="nav-item <?= $current_page == 'categoria.php' ? 'active' : '' ?>">
        <a class="nav-link" href="categoria.php">
            <i class="bi bi-grid"></i>
            <span>Categorías</span>
        </a>
    </li>

    <li class="nav-item <?= $current_page == 'producto.php' ? 'active' : '' ?>">
        <a class="nav-link" href="producto.php">
            <i class="bi bi-box-seam"></i>
            <span>Productos</span>
        </a>
    </li>

    <li class="nav-item <?= $current_page == 'pedido.php' ? 'active' : '' ?>">
        <a class="nav-link" href="pedido.php">
            <i class="bi bi-cart-check"></i>
            <span>Pedidos</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Configuración</div>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="bi bi-gear"></i>
            <span>Ajustes</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="bi bi-file-earmark-text"></i>
            <span>Reportes</span>
        </a>
    </li>
    <hr class="sidebar-divider d-none d-md-block">
</ul>