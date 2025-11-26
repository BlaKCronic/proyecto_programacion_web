function filtrarPedidos(estado) {
    window.location.href = 'pedidos.php?estado=' + estado;
}

function cancelarPedido(id) {
    if(confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
        alert('Funcionalidad de cancelar pendiente de implementar');
    }
}
