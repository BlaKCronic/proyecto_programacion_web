<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="bi bi-geo-alt-fill text-warning"></i> Dirección de envío
        </h4>
    </div>
    <div class="card-body">
        <form method="POST" action="checkout.php?step=1">
            <div class="mb-3">
                <label for="direccion" class="form-label">
                    Calle y número <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="direccion" name="direccion" 
                       placeholder="Ej: Av. Principal #123, Col. Centro"
                       value="<?= isset($_SESSION['checkout_direccion']) ? htmlspecialchars($_SESSION['checkout_direccion']['direccion']) : htmlspecialchars($usuario['direccion'] ?? '') ?>"
                       required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ciudad" class="form-label">
                        Ciudad <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                           placeholder="Ej: Celaya"
                           value="<?= isset($_SESSION['checkout_direccion']) ? htmlspecialchars($_SESSION['checkout_direccion']['ciudad']) : htmlspecialchars($usuario['ciudad'] ?? '') ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label">
                        Estado <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="estado" name="estado" required>
                        <?php 
                        $estado_actual = isset($_SESSION['checkout_direccion']) ? $_SESSION['checkout_direccion']['estado'] : ($usuario['estado'] ?? 'Guanajuato');
                        $estados = [
                            'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas',
                            'Chihuahua', 'Coahuila', 'Colima', 'Durango', 'Guanajuato', 'Guerrero', 'Hidalgo',
                            'Jalisco', 'México', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca',
                            'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora',
                            'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas', 'Ciudad de México'
                        ];
                        foreach($estados as $estado):
                        ?>
                            <option value="<?= $estado ?>" <?= $estado == $estado_actual ? 'selected' : '' ?>>
                                <?= $estado ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo_postal" class="form-label">
                        Código Postal <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                           placeholder="38000" maxlength="5" pattern="[0-9]{5}"
                           value="<?= isset($_SESSION['checkout_direccion']) ? htmlspecialchars($_SESSION['checkout_direccion']['codigo_postal']) : htmlspecialchars($usuario['codigo_postal'] ?? '') ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">
                        Teléfono de contacto <span class="text-danger">*</span>
                    </label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                           placeholder="4611234567" maxlength="10" pattern="[0-9]{10}"
                           value="<?= isset($_SESSION['checkout_direccion']) ? htmlspecialchars($_SESSION['checkout_direccion']['telefono']) : htmlspecialchars($usuario['telefono'] ?? '') ?>"
                           required>
                    <small class="text-muted">Para coordinar la entrega</small>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Nota:</strong> Asegúrate de que los datos sean correctos para evitar problemas con la entrega.
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="carrito.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al carrito
                </a>
                <button type="submit" name="confirmar_direccion" class="btn btn-warning btn-lg">
                    Continuar al pago <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('codigo_postal').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>