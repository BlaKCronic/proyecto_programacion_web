function agregarAlCarrito(productoId) {
    let rutaApi = '/proyecto/api/carrito_add.php';
    
    productoId = parseInt(productoId);
    
    if(!productoId || productoId <= 0) {
        alert('Error: ID de producto inválido');
        return;
    }
    
    fetch(rutaApi, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ producto_id: productoId, cantidad: 1 })
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
}

document.addEventListener('DOMContentLoaded', function() {
    const selectsCantidad = document.querySelectorAll('select[name^="cantidad"]');
    selectsCantidad.forEach(select => {
        select.addEventListener('change', function() {
            if(this.value == '0') {
                if(confirm('¿Eliminar este producto del carrito?')) {
                    this.form.submit();
                }
            }
        });
    });

    const botonesAgregarCarrito = document.querySelectorAll('.btn-add-cart');
    botonesAgregarCarrito.forEach(btn => {
        btn.addEventListener('click', function() {
            const productoId = this.dataset.productoId;
            agregarAlCarrito(productoId);
        });
    });
});
