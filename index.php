<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.cdnfonts.com/css/amazon-ember" rel="stylesheet">
    <link rel="stylesheet" href="styles/main.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script/main.js"></script>
</head>

<body>
    <header>
        <nav class="navbar d-flex align-items-center px-3">
            <!-- Logo -->
            <div class="logo me-3">
                <img src="img/logo/logo.png" width="120" alt="Amazon Logo">
            </div>

            <!-- Ubicacion -->
            <div class="nav-location text-white me-3">
                <span class="small-text d-block">Enviar a</span>
                <span class="bold-text">México</span>
            </div>

            <!-- Barra de busqueda -->
            <div class="search-bar d-flex flex-grow-1 me-3">
                <select class="form-select w-auto">
                    <option>Todos</option>
                    <option>Electrónicos</option>
                    <option>Libros</option>
                    <option>Ropa</option>
                </select>
                <input type="text" class="form-control" placeholder="Buscar en Amazon">
                <button class="btn btn-warning">
                    <img src="img/icons/search.png" width="14">
                </button>
            </div>

            <!-- Idioma -->
            <div class="nav-lang d-flex align-items-center text-white me-3">
                <img src="img/icons/mx-flag.png" width="20" alt="Idioma" class="me-1">
                <span>ES</span>
            </div>

            <!-- Cuenta -->
            <div class="nav-account text-white me-3">
                <span class="small-text d-block">Hola, identifícate</span>
                <span class="bold-text">Cuenta y Listas</span>
            </div>

            <!-- Pedidos -->
            <div class="nav-orders text-white me-3">
                <span class="small-text d-block">Devoluciones</span>
                <span class="bold-text">y Pedidos</span>
            </div>

            <!-- Carrito -->
            <div class="nav-cart d-flex align-items-center text-white">
                <img src="img/icons/shopping-cart.png" width="22" alt="Carrito" class="me-1">
                <span class="bold-text">Carrito</span>
            </div>
        </nav>
    </header>
    <main>

    </main>
    <footer>

    </footer>
</body>

</html>