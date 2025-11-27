<?php
require_once "sistema.php";

class Pedido extends Sistema {
    
    function create($data) {
        $this->conect();
        
        $numero_pedido = 'PED-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        $sql = "INSERT INTO pedidos (id_usuario, numero_pedido, total, subtotal, envio, 
                impuestos, direccion_envio, metodo_pago) 
                VALUES (:id_usuario, :numero_pedido, :total, :subtotal, :envio, 
                :impuestos, :direccion_envio, :metodo_pago)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $data['id_usuario'], PDO::PARAM_INT);
        $sth->bindParam(":numero_pedido", $numero_pedido, PDO::PARAM_STR);
        $sth->bindParam(":total", $data['total'], PDO::PARAM_STR);
        $sth->bindParam(":subtotal", $data['subtotal'], PDO::PARAM_STR);
        $sth->bindParam(":envio", $data['envio'], PDO::PARAM_STR);
        $sth->bindParam(":impuestos", $data['impuestos'], PDO::PARAM_STR);
        $sth->bindParam(":direccion_envio", $data['direccion_envio'], PDO::PARAM_STR);
        $sth->bindParam(":metodo_pago", $data['metodo_pago'], PDO::PARAM_STR);
        $sth->execute();
        return $this->_BD->lastInsertId();
    }

    function agregarDetalle($data) {
        $this->conect();
        
        $comision = $data['subtotal'] * 0.15;
        
        $sql = "INSERT INTO detalle_pedidos (id_pedido, id_producto, id_vendedor, cantidad, 
                precio_unitario, subtotal, comision_plataforma) 
                VALUES (:id_pedido, :id_producto, :id_vendedor, :cantidad, 
                :precio_unitario, :subtotal, :comision_plataforma)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_pedido", $data['id_pedido'], PDO::PARAM_INT);
        $sth->bindParam(":id_producto", $data['id_producto'], PDO::PARAM_INT);
        $sth->bindParam(":id_vendedor", $data['id_vendedor'], PDO::PARAM_INT);
        $sth->bindParam(":cantidad", $data['cantidad'], PDO::PARAM_INT);
        $sth->bindParam(":precio_unitario", $data['precio_unitario'], PDO::PARAM_STR);
        $sth->bindParam(":subtotal", $data['subtotal'], PDO::PARAM_STR);
        $sth->bindParam(":comision_plataforma", $comision, PDO::PARAM_STR);
        $sth->execute();
        return $sth->rowCount();
    }

    function readByUsuario($id_usuario) {
        $this->conect();
        $sql = "SELECT * FROM pedidos WHERE id_usuario = :id_usuario ORDER BY fecha_pedido DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readByVendedor($id_vendedor) {
        $this->conect();
        $sql = "SELECT DISTINCT p.*, u.nombre, u.apellido, u.email
                FROM pedidos p
                INNER JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE dp.id_vendedor = :id_vendedor
                ORDER BY p.fecha_pedido DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readOne($id) {
        $this->conect();
        $sql = "SELECT p.*, u.nombre, u.apellido, u.email, u.telefono
                FROM pedidos p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.id_pedido = :id_pedido";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_pedido", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function readDetalle($id_pedido) {
        $this->conect();
        $sql = "SELECT dp.*, p.nombre as producto, p.imagen_principal, v.nombre_tienda
                FROM detalle_pedidos dp
                INNER JOIN productos p ON dp.id_producto = p.id_producto
                INNER JOIN vendedores v ON dp.id_vendedor = v.id_vendedor
                WHERE dp.id_pedido = :id_pedido";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readDetalleByVendedor($id_pedido, $id_vendedor) {
        $this->conect();
        $sql = "SELECT dp.*, p.nombre as producto, p.imagen_principal
                FROM detalle_pedidos dp
                INNER JOIN productos p ON dp.id_producto = p.id_producto
                WHERE dp.id_pedido = :id_pedido AND dp.id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function updateEstado($id, $estado) {
        $this->conect();
        $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":estado", $estado, PDO::PARAM_STR);
        $sth->bindParam(":id_pedido", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function updateEstadoVendedor($id_detalle, $estado, $numero_seguimiento = null) {
        $this->conect();
        $sql = "UPDATE detalle_pedidos SET estado_vendedor = :estado";
        
        if($numero_seguimiento) {
            $sql .= ", numero_seguimiento = :numero_seguimiento";
        }
        
        $sql .= " WHERE id_detalle = :id_detalle";
        
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":estado", $estado, PDO::PARAM_STR);
        $sth->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);
        
        if($numero_seguimiento) {
            $sth->bindParam(":numero_seguimiento", $numero_seguimiento, PDO::PARAM_STR);
        }
        
        $sth->execute();
        return $sth->rowCount();
    }

    function updateFechaEntrega($id, $fecha) {
        $this->conect();
        $sql = "UPDATE pedidos SET fecha_entrega = :fecha WHERE id_pedido = :id_pedido";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":fecha", $fecha, PDO::PARAM_STR);
        $sth->bindParam(":id_pedido", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }
    function cancelarPedido($id) {
        $this->conect();
        try {
            $this->_BD->beginTransaction();

            $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
            $sth = $this->_BD->prepare($sql);
            $estado = 'cancelado';
            $sth->bindParam(":estado", $estado, PDO::PARAM_STR);
            $sth->bindParam(":id_pedido", $id, PDO::PARAM_INT);
            $sth->execute();

            $sql2 = "UPDATE detalle_pedidos SET estado_vendedor = :estado WHERE id_pedido = :id_pedido";
            $sth2 = $this->_BD->prepare($sql2);
            $sth2->bindParam(":estado", $estado, PDO::PARAM_STR);
            $sth2->bindParam(":id_pedido", $id, PDO::PARAM_INT);
            $sth2->execute();

            $this->_BD->commit();
            return true;
        } catch(Exception $e) {
            if($this->_BD->inTransaction()) {
                $this->_BD->rollBack();
            }
            error_log('Error al cancelar pedido: ' . $e->getMessage());
            return false;
        }
    }

    function read() {
        $this->conect();
        $sql = "SELECT p.*, u.nombre, u.apellido 
                FROM pedidos p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY p.fecha_pedido DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function obtenerEstadisticas($id_vendedor = null) {
        $this->conect();
        
        if($id_vendedor) {
            $sql = "SELECT 
                    COUNT(DISTINCT dp.id_pedido) as total_pedidos,
                    SUM(dp.subtotal) as ventas_totales,
                    SUM(dp.comision_plataforma) as comisiones_totales
                    FROM detalle_pedidos dp
                    INNER JOIN pedidos p ON dp.id_pedido = p.id_pedido
                    WHERE dp.id_vendedor = :id_vendedor";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        } else {
            $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(total) as ventas_totales,
                    SUM(subtotal * 0.15) as comisiones_totales
                    FROM pedidos";
            $sth = $this->_BD->prepare($sql);
        }
        
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
?>