function formatearTarjeta(input) {
    let value = input.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    input.value = formattedValue;
}

function validarNumeroTarjeta(numero) {
    numero = numero.replace(/\s/g, '');
    return numero.length === 16 && /^\d+$/.test(numero);
}

function soloNumeros(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

function soloMayusculas(input) {
    input.value = input.value.toUpperCase();
}

function validarEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validarPassword(password) {
    return password.length >= 6;
}

function validarConfirmacion(password, confirmacion) {
    return password === confirmacion;
}

document.addEventListener('DOMContentLoaded', function() {
    const telefonoInputs = document.querySelectorAll('input[name="telefono"]');
    telefonoInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            soloNumeros(this);
        });
    });

    const codigoPostalInputs = document.querySelectorAll('input[name="codigo_postal"]');
    codigoPostalInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            soloNumeros(this);
        });
    });
});
