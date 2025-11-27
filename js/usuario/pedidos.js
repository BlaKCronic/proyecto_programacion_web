function filtrarPedidos(estado) {
    window.location.href = 'pedidos.php?estado=' + estado;
}

function cancelarPedido(id) {
    if(confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
        var btn = event && event.target ? event.target : null;
        if(btn) {
            btn.disabled = true;
        }

        var formData = new FormData();
        formData.append('id', id);

        fetch('api/cancelar_pedido.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) { return response.json().then(function(body) { return { status: response.status, body: body }; }); })
        .then(function(res) {
            if(res.body && res.body.success) {
                alert(res.body.message || 'Pedido cancelado');
                window.location.reload();
            } else {
                var msg = (res.body && res.body.message) ? res.body.message : 'Error al cancelar';
                alert(msg);
                if(btn) btn.disabled = false;
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Error de comunicación con el servidor');
            if(btn) btn.disabled = false;
        });
    }
}
