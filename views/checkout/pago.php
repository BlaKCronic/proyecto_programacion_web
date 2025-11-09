<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="bi bi-credit-card-fill text-warning"></i> Método de pago
        </h4>
    </div>
    <div class="card-body">
        <form method="POST" action="checkout.php?step=2" id="formPago">
            <div class="alert alert-success mb-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong><i class="bi bi-check-circle-fill"></i> Dirección de envío confirmada</strong>
                        <p class="mb-0 mt-2 small">
                            <?= htmlspecialchars($_SESSION['checkout_direccion']['direccion']) ?><br>
                            <?= htmlspecialchars($_SESSION['checkout_direccion']['ciudad']) ?>, 
                            <?= htmlspecialchars($_SESSION['checkout_direccion']['estado']) ?> 
                            <?= htmlspecialchars($_SESSION['checkout_direccion']['codigo_postal']) ?><br>
                            Tel: <?= htmlspecialchars($_SESSION['checkout_direccion']['telefono']) ?>
                        </p>
                    </div>
                    <a href="checkout.php?step=1" class="btn btn-sm btn-outline-secondary">
                        Cambiar
                    </a>
                </div>
            </div>
            <h5 class="mb-3">Selecciona tu método de pago</h5>
            <div class="card mb-3 metodo-pago">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodo_pago" 
                               id="tarjeta" value="tarjeta" checked>
                        <label class="form-check-label w-100" for="tarjeta">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-credit-card fs-4 text-primary"></i>
                                    <strong class="ms-2">Tarjeta de crédito o débito</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="bi bi-credit-card-2-front fs-5 text-muted"></i>
                                    <i class="bi bi-credit-card-2-back fs-5 text-muted"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div id="datosTargeta" class="mt-3">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Número de tarjeta</label>
                                <input type="text" class="form-control" placeholder="1234 5678 9012 3456" 
                                       maxlength="19" id="numeroTarjeta">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nombre en la tarjeta</label>
                                <input type="text" class="form-control" placeholder="JUAN PEREZ">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" placeholder="123" maxlength="4">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de vencimiento</label>
                                <div class="row">
                                    <div class="col-6">
                                        <select class="form-select">
                                            <option value="">Mes</option>
                                            <?php for($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?= sprintf('%02d', $i) ?>">
                                                    <?= sprintf('%02d', $i) ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select class="form-select">
                                            <option value="">Año</option>
                                            <?php for($i = date('Y'); $i <= date('Y') + 15; $i++): ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info small">
                            <i class="bi bi-shield-lock-fill"></i>
                            Tu información está protegida con encriptación SSL
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 metodo-pago">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodo_pago" 
                               id="transferencia" value="transferencia">
                        <label class="form-check-label w-100" for="transferencia">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-bank fs-4 text-success"></i>
                                    <strong class="ms-2">Transferencia o depósito bancario</strong>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div id="datosTransferencia" class="mt-3" style="display: none;">
                        <p class="mb-2">Realiza tu transferencia a la siguiente cuenta:</p>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-1"><strong>Banco:</strong> Banco Nacional</p>
                            <p class="mb-1"><strong>Cuenta:</strong> 1234567890</p>
                            <p class="mb-1"><strong>CLABE:</strong> 012345678901234567</p>
                            <p class="mb-0"><strong>Beneficiario:</strong> Amazon Lite S.A. de C.V.</p>
                        </div>
                        <div class="alert alert-warning small mt-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Tu pedido se procesará una vez confirmado el pago (24-48 hrs)
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 metodo-pago">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodo_pago" 
                               id="paypal" value="paypal">
                        <label class="form-check-label w-100" for="paypal">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-paypal fs-4 text-info"></i>
                                    <strong class="ms-2">PayPal</strong>
                                </div>
                                <div>
                                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg" 
                                         alt="PayPal" height="30">
                                </div>
                            </div>
                        </label>
                    </div>
                    <div id="datosPayPal" class="mt-3" style="display: none;">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle-fill"></i>
                            Serás redirigido a PayPal para completar el pago de forma segura.
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4 metodo-pago">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodo_pago" 
                               id="efectivo" value="efectivo">
                        <label class="form-check-label w-100" for="efectivo">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-cash fs-4 text-warning"></i>
                                    <strong class="ms-2">Pago contra entrega (efectivo)</strong>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div id="datosEfectivo" class="mt-3" style="display: none;">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle-fill"></i>
                            Paga en efectivo al recibir tu pedido. Prepara el monto exacto.
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="aceptoTerminos" required>
                <label class="form-check-label" for="aceptoTerminos">
                    Acepto los <a href="#" target="_blank">términos y condiciones</a> 
                    y la <a href="#" target="_blank">política de privacidad</a>
                </label>
            </div>

            <div class="d-flex justify-content-between">
                <a href="checkout.php?step=1" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <button type="submit" name="confirmar_pago" class="btn btn-warning btn-lg">
                    Revisar pedido <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('datosTargeta').style.display = 'none';
        document.getElementById('datosTransferencia').style.display = 'none';
        document.getElementById('datosEfectivo').style.display = 'none';
        
        if(this.value === 'tarjeta') {
            document.getElementById('datosTargeta').style.display = 'block';
        } else if(this.value === 'transferencia') {
            document.getElementById('datosTransferencia').style.display = 'block';
        } else if(this.value === 'efectivo') {
            document.getElementById('datosEfectivo').style.display = 'block';
        }
    });
});

document.getElementById('numeroTarjeta').addEventListener('input', function(e) {
    let value = this.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    this.value = formattedValue;
});

document.getElementById('formPago').addEventListener('submit', function(e) {
    const metodo = document.querySelector('input[name="metodo_pago"]:checked').value;
    
    if(metodo === 'tarjeta') {
        const numero = document.getElementById('numeroTarjeta').value.replace(/\s/g, '');
        if(numero.length < 16) {
            e.preventDefault();
            alert('Número de tarjeta inválido');
            return false;
        }
    }
});

.metodo-pago {
    cursor: pointer;
    transition: all 0.3s;
}

.metodo-pago:hover {
    border-color: #febd69;
    box-shadow: 0 0 10px rgba(254, 189, 105, 0.3);
}

.form-check-input:checked ~ .form-check-label {
    color: #111;
}
</script>