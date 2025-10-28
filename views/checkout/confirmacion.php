<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="bi bi-check-circle-fill text-warning"></i> Revisar y confirmar
        </h4>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5><i class="bi bi-geo-alt-fill text-muted"></i> Dirección de envío</h5>
                <a href="checkout.php?step=1" class="btn btn-sm btn-outline-secondary">Editar</a>
            </div>
            <div class="bg-light p-3 rounded">
                <p class="mb-1"><strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong></p>
                <p class="mb-1"><?= htmlspecialchars($_SESSION['checkout_direccion']['direccion']) ?></p>
                <p class="mb-1">
                    <?= htmlspecialchars($_SESSION['checkout_direccion']['ciudad']) ?>, 
                    <?= htmlspecialchars($_SESSION['checkout_direccion']['estado']) ?> 
                    <?= htmlspecialchars($_SESSION['checkout_direccion']['codigo_postal']) ?>
                </p>
                <p class="mb-0">Tel: <?= htmlspecialchars($_SESSION['checkout_direccion']['telefono']) ?></p>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5><i class="bi bi-credit-card-fill text-muted"></i> Método de pago</h5>
                <a href="checkout.php?step=2" class="btn btn-sm btn-outline-secondary">Editar</a>
            </div>
            <div class="bg-light p-3 rounded">
                <?php
                $metodos = [
                    'tarjeta' => '<i class="bi bi-credit-card"></i> Tarjeta de crédito/débito',
                    'transferencia' => '<i class="bi bi-bank"></i> Transferencia bancaria',
                    'paypal' => '<i class="bi bi-paypal"></i> PayPal',
                    'efectivo' => '<i class="bi bi-cash"></i> Pago contra entrega'
                ];
                $metodo = $_SESSION['checkout_pago']['metodo'];
                ?>
                <p class="mb-0 fw-bold"><?= $metodos[$metodo] ?></p>
            </div>
        </div>

        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-bag-check-fill text-muted"></i> Productos (<?= count($items_carrito) ?>)</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items_carrito as $item): ?>
                            <?php 
                            $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
                            $subtotal_item = $precio_unitario * $item['cantidad'];
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="img/productos/<?= $item['imagen_principal'] ?>" 
                                             class="me-2" style="width: 50px; height: 50px; object-fit: contain;"
                                             alt="<?= htmlspecialchars($item['nombre']) ?>">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($item['nombre']) ?></div>
                                            <small class="text-muted">
                                                <i class="bi bi-shop"></i> <?= htmlspecialchars($item['nombre_tienda']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle"><?= $item['cantidad'] ?></td>
                                <td class="text-end align-middle">
                                    <?php if($item['precio_descuento']): ?>
                                        <div class="text-danger fw-bold"><?= formatearPrecio($precio_unitario) ?></div>
                                        <small class="text-muted text-decoration-line-through">
                                            <?= formatearPrecio($item['precio']) ?>
                                        </small>
                                    <?php else: ?>
                                        <?= formatearPrecio($precio_unitario) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end align-middle fw-bold"><?= formatearPrecio($subtotal_item) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-4">
            <h5 class="mb-3">Resumen</h5>
            <div class="row">
                <div class="col-md-6 ms-auto">
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?= formatearPrecio($subtotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Envío:</span>
                            <span class="<?= $envio == 0 ? 'text-success fw-bold' : '' ?>">
                                <?= $envio == 0 ? 'GRATIS' : formatearPrecio($envio) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (<?= (IVA * 100) ?>%):</span>
                            <span><?= formatearPrecio($impuestos) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="fs-4">Total:</strong>
                            <strong class="fs-4 text-danger"><?= formatearPrecio($total) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle-fill"></i> Información importante</h6>
            <ul class="mb-0 small">
                <li>El tiempo de entrega estimado es de 3-5 días hábiles</li>
                <li>Recibirás un correo de confirmación con los detalles de tu pedido</li>
                <li>Podrás rastrear tu pedido desde tu cuenta</li>
                <li>Tienes 30 días para devolver productos en caso de insatisfacción</li>
            </ul>
        </div>

        <form method="POST" action="checkout.php?step=3">
            <div class="d-flex justify-content-between">
                <a href="checkout.php?step=2" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <button type="submit" name="finalizar_pedido" class="btn btn-success btn-lg">
                    <i class="bi bi-lock-fill"></i> Confirmar y pagar <?= formatearPrecio($total) ?>
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-shield-check"></i> Compra 100% segura y protegida
            </small>
        </div>
    </div>
</div>