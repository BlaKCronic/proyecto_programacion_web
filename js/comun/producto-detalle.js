function cambiarImagen(src) {
    document.querySelector('#imagenPrincipal img').src = src;
    
    document.querySelectorAll('.miniatura-img').forEach(min => min.classList.remove('active'));
}

function cambiarImagenDesdeMini(event, src) {
    cambiarImagen(src);
    const target = event.target.closest('.miniatura-img');
    if(target) target.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const btnAgregarCarrito = document.getElementById('btnAgregarCarrito');
    
    if(btnAgregarCarrito) {
        btnAgregarCarrito.addEventListener('click', function() {
            const cantidadElement = document.getElementById('cantidad');
            const cantidad = cantidadElement ? parseInt(cantidadElement.value) : 1;
            const productoId = parseInt(this.dataset.productoId);
            
            let rutaApi = 'api/carrito_add.php';
            
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

document.addEventListener('DOMContentLoaded', function() {
    const formResena = document.getElementById('formResena');
    if(formResena) {
        formResena.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(formResena);
            const payload = {
                id_producto: parseInt(fd.get('id_producto')),
                calificacion: parseInt(fd.get('calificacion')),
                titulo: fd.get('titulo') ? fd.get('titulo').trim() : '',
                comentario: fd.get('comentario') ? fd.get('comentario').trim() : ''
            };

            if(!payload.calificacion || payload.calificacion < 1) {
                alert('Selecciona una calificación válida.');
                return;
            }

            if(!payload.comentario) {
                alert('Escribe un comentario para la reseña.');
                return;
            }

            const rutaApi = 'api/resena_add.php';

            fetch(rutaApi, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Reseña enviada correctamente.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo enviar la reseña'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al enviar la reseña. Intenta más tarde.');
            });
        });
    }
});
