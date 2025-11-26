document.addEventListener('DOMContentLoaded', function() {
    const codigoPostalInput = document.getElementById('codigo_postal');
    const telefonoInput = document.getElementById('telefono');
    
    if(codigoPostalInput) {
        codigoPostalInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    if(telefonoInput) {
        telefonoInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
