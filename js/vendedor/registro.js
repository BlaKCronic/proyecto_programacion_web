document.addEventListener('DOMContentLoaded', function() {
    const telefonoInput = document.getElementById('telefono');
    const rfcInput = document.getElementById('rfc');
    const formRegistro = document.querySelector('form');
    
    if(telefonoInput) {
        telefonoInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    if(rfcInput) {
        rfcInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
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
});
