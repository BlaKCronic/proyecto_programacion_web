document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if(togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if(type === 'text') {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
    }
    
    const formLogin = document.getElementById('formLogin');
    if(formLogin) {
        const emailInput = document.getElementById('email');
        const passwordInputForm = document.getElementById('password');
        const btnLogin = document.getElementById('btnLogin');
        
        if(emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if(email === '') {
                    this.classList.remove('is-valid', 'is-invalid');
                } else if(!emailRegex.test(email)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
            
            emailInput.addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        }
        
        if(passwordInputForm) {
            passwordInputForm.addEventListener('blur', function() {
                const password = this.value;
                
                if(password === '') {
                    this.classList.remove('is-valid', 'is-invalid');
                } else if(password.length < 6) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
            
            passwordInputForm.addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        }
        
        formLogin.addEventListener('submit', function(e) {
            btnLogin.disabled = true;
            btnLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando sesión...';
        });
    }
});
