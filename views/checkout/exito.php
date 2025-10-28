<?php
$id_pedido = isset($_SESSION['ultimo_pedido']) ? $_SESSION['ultimo_pedido'] : null;

if($id_pedido) {
    $pedido = $appPedido->readOne($id_pedido);
    $detalles = $appPedido->readDetalle($id_pedido);
} else {
    redirect('index.php');
}
?>

<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                 style="width: 100px; height: 100px;">
                <i class="bi bi-check-lg text-white" style="font-size: 60px;"></i>
            </div>
        </div>

        <h1 class="text-success mb-3">¡Pedido confirmado!</h1>
        <p class="lead mb-4">
            Gracias por tu compra, <?= htmlspecialchars($usuario['nombre']) ?>
        </p>

        <div class="bg-light p-4 rounded mb-4">
            <p class="text-muted mb-2">Número de pedido</p>
            <h3 class="mb-0 fw-bold"><?= htmlspecialchars($pedido['numero_pedido']) ?></h3>
        </div>

        <div class="row text-start mb-4">
            <div class="col-md-6">
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-geo-alt-fill text-warning"></i> Se enviará a:
                        </h6>
                        <p class="card-text small mb-0">
                            <?= htmlspecialchars($pedido['direccion_envio']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-calendar-check text-warning"></i> Entrega estimada:
                        </h6>
                        <p class="card-text small mb-0">
                            <?php
                            $fecha_entrega = date('d/m/Y', strtotime('+5 days'));
                            echo $fecha_entrega;
                            ?>
                            <br>
                            <span class="text-muted">(3-5 días hábiles)</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <h5 class="mb-3 text-start">Resumen del pedido</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach($detalles as $detalle): ?>
                                <tr>
                                    <td class="text-start">
                                        <?= htmlspecialchars($detalle['producto']) ?>
                                        <br>
                                        <small class="text-muted">Cantidad: <?= $detalle['cantidad'] ?></small>
                                    </td>
                                    <td class="text-end"><?= formatearPrecio($detalle['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td class="text-start">Envío</td>
                                <td class="text-end">
                                    <?= $pedido['envio'] == 0 ? 'GRATIS' : formatearPrecio($pedido['envio']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start">IVA</td>
                                <td class="text-end"><?= formatearPrecio($pedido['impuestos']) ?></td>
                            </tr>
                            <tr class="table-light">
                                <td class="text-start"><strong>Total pagado:</strong></td>
                                <td class="text-end"><strong class="text-success fs-5"><?= formatearPrecio($pedido['total']) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info text-start">
            <h6><i class="bi bi-envelope-fill"></i> ¿Qué sigue?</h6>
            <ul class="mb-0 small">
                <li>Hemos enviado un correo de confirmación a <strong><?= htmlspecialchars($usuario['email']) ?></strong></li>
                <li>Puedes rastrear tu pedido desde la sección "Mis pedidos"</li>
                <li>Los vendedores procesarán tu pedido en las próximas horas</li>
                <li>Recibirás notificaciones sobre el estado de tu envío</li>
            </ul>
        </div>

        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mt-4">
            <a href="pedidos.php" class="btn btn-warning btn-lg">
                <i class="bi bi-box-seam"></i> Ver mis pedidos
            </a>
            <a href="productos.php" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Seguir comprando
            </a>
        </div>

        <div class="mt-4">
            <p class="text-muted small mb-2">Comparte tu experiencia:</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="#" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-twitter"></i>
                </a>
                <a href="#" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-whatsapp"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Te podría interesar</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php
            $productos_recomendados = $appProducto->read();
            $productos_recomendados = array_slice($productos_recomendados, 0, 4);
            
            foreach($productos_recomendados as $prod):
            ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border hover-lift">
                        <a href="producto_detalle.php?id=<?= $prod['id_producto'] ?>">
                            <img src="img/productos/<?= $prod['imagen_principal'] ?>" 
                                 class="card-img-top p-3" alt="<?= htmlspecialchars($prod['nombre']) ?>"
                                 style="height: 150px; object-fit: contain;">
                        </a>
                        <div class="card-body">
                            <h6 class="card-title" style="height: 40px; overflow: hidden;">
                                <a href="producto_detalle.php?id=<?= $prod['id_producto'] ?>" 
                                   class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($prod['nombre']) ?>
                                </a>
                            </h6>
                            <div class="fw-bold text-danger">
                                <?= formatearPrecio($prod['precio_descuento'] ?? $prod['precio']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
}

@keyframes checkmark {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.bi-check-lg {
    animation: checkmark 0.5s ease-in-out;
}
</style>

<?php
unset($_SESSION['ultimo_pedido']);
?>