document.addEventListener('DOMContentLoaded', function() {
    const formRestablecer = document.querySelector('form');
    
    if(formRestablecer) {
        formRestablecer.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const password_confirm = document.getElementById('password_confirm');
            
            if(password && password_confirm) {
                if(password.value !== password_confirm.value) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return false;
                }
            }
        });
    }
});
