function cambiarImagen(src) {
    document.querySelector('#imagenPrincipal img').src = src;
    
    document.querySelectorAll('.miniatura-img').forEach(min => min.classList.remove('active'));
    event.target.closest('.miniatura-img').classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const btnAgregarCarrito = document.getElementById('btnAgregarCarrito');
    
    if(btnAgregarCarrito) {
        btnAgregarCarrito.addEventListener('click', function() {
            const cantidadElement = document.getElementById('cantidad');
            const cantidad = cantidadElement ? parseInt(cantidadElement.value) : 1;
            const productoId = parseInt(this.dataset.productoId);
            
            let rutaApi = '/proyecto/api/carrito_add.php';
            
            if(!productoId || productoId <= 0) {
                alert('Error: ID de producto inválido');
                return;
            }
            
            fetch(rutaApi, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ producto_id: productoId, cantidad: cantidad })
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    alert('Producto agregado al carrito');
                    location.reload();
                } else {
                    alert('Error al agregar al carrito: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al agregar al carrito: ' + error.message);
            });
        });
    }
});
