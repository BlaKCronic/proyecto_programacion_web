document.addEventListener('DOMContentLoaded', function() {
    const mostrarDireccion = document.getElementById('mostrar_direccion');
    const formRegistro = document.querySelector('form');
    const telefonoInput = document.getElementById('telefono');
    const codigoPostalInput = document.getElementById('codigo_postal');
    
    if(mostrarDireccion) {
        mostrarDireccion.addEventListener('change', function() {
            const direccionFields = document.getElementById('direccion_fields');
            if(this.checked) {
                direccionFields.style.display = 'block';
            } else {
                direccionFields.style.display = 'none';
            }
        });
    }
    
    if(formRegistro) {
        formRegistro.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const password_confirm = document.getElementById('password_confirm').value;
            
            if(password !== password_confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
        });
    }
    
    if(telefonoInput) {
        telefonoInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    if(codigoPostalInput) {
        codigoPostalInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
