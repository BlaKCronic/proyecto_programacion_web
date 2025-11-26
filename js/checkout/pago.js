document.addEventListener('DOMContentLoaded', function() {
    const metodoPagoInputs = document.querySelectorAll('input[name="metodo_pago"]');
    const numeroTarjetaInput = document.getElementById('numeroTarjeta');
    const formPago = document.getElementById('formPago');
    
    metodoPagoInputs.forEach(radio => {
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
    
    if(numeroTarjetaInput) {
        numeroTarjetaInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formattedValue;
        });
    }
    
    if(formPago) {
        formPago.addEventListener('submit', function(e) {
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
    }
});
