document.addEventListener('DOMContentLoaded', function() {
    const ordenSelect = document.getElementById('ordenSelect');
    
    if(ordenSelect) {
        ordenSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('orden', this.value);
            window.location.href = url.toString();
        });
    }
});
