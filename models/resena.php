<?php
require_once "sistema.php";

class Resena extends Sistema {
    
    function create($data) {
        $this->conect();
        $sql = "INSERT INTO resenas (id_producto, id_usuario, calificacion, titulo, comentario) 
                VALUES (:id_producto, :id_usuario, :calificacion, :titulo, :comentario)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_producto", $data['id_producto'], PDO::PARAM_INT);
        $sth->bindParam(":id_usuario", $data['id_usuario'], PDO::PARAM_INT);
        $sth->bindParam(":calificacion", $data['calificacion'], PDO::PARAM_INT);
        $sth->bindParam(":titulo", $data['titulo'], PDO::PARAM_STR);
        $sth->bindParam(":comentario", $data['comentario'], PDO::PARAM_STR);
        $sth->execute();
        return $sth->rowCount();
    }

    function readByProducto($id_producto) {
        $this->conect();
        $sql = "SELECT r.*, u.nombre, u.apellido
                FROM resenas r
                INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                WHERE r.id_producto = :id_producto
                ORDER BY r.fecha_resena DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readByUsuario($id_usuario) {
        $this->conect();
        $sql = "SELECT r.*, p.nombre as producto, p.imagen_principal
                FROM resenas r
                INNER JOIN productos p ON r.id_producto = p.id_producto
                WHERE r.id_usuario = :id_usuario
                ORDER BY r.fecha_resena DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function obtenerPromedioCalificacion($id_producto) {
        $this->conect();
        $sql = "SELECT AVG(calificacion) as promedio, COUNT(*) as total
                FROM resenas
                WHERE id_producto = :id_producto";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function usuarioPuedeResenar($id_usuario, $id_producto) {
        $this->conect();
        
        $sql = "SELECT COUNT(*) as compro
                FROM pedidos p
                INNER JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
                WHERE p.id_usuario = :id_usuario 
                AND dp.id_producto = :id_producto
                AND p.estado = 'entregado'";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $sth->execute();
        $compro = $sth->fetch(PDO::FETCH_ASSOC);
        
        if($compro['compro'] == 0) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) as ya_reseno
                FROM resenas
                WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $sth->execute();
        $ya_reseno = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $ya_reseno['ya_reseno'] == 0;
    }

    function marcarVerificado($id) {
        $this->conect();
        $sql = "UPDATE resenas SET verificado = 1 WHERE id_resena = :id_resena";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_resena", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function delete($id) {
        if(is_numeric($id)) {
            $this->conect();
            $sql = "DELETE FROM resenas WHERE id_resena = :id_resena";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_resena", $id, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        }
        return null;
    }

    function obtenerDistribucionCalificaciones($id_producto) {
        $this->conect();
        $sql = "SELECT 
                calificacion,
                COUNT(*) as cantidad,
                (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM resenas WHERE id_producto = :id_producto)) as porcentaje
                FROM resenas
                WHERE id_producto = :id_producto
                GROUP BY calificacion
                ORDER BY calificacion DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
}

class CalificacionVendedor extends Sistema {
    
    function create($data) {
        $this->conect();
        $sql = "INSERT INTO calificaciones_vendedor (id_vendedor, id_usuario, id_pedido, calificacion, comentario) 
                VALUES (:id_vendedor, :id_usuario, :id_pedido, :calificacion, :comentario)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $data['id_vendedor'], PDO::PARAM_INT);
        $sth->bindParam(":id_usuario", $data['id_usuario'], PDO::PARAM_INT);
        $sth->bindParam(":id_pedido", $data['id_pedido'], PDO::PARAM_INT);
        $sth->bindParam(":calificacion", $data['calificacion'], PDO::PARAM_INT);
        $sth->bindParam(":comentario", $data['comentario'], PDO::PARAM_STR);
        $sth->execute();
        return $sth->rowCount();
    }

    function readByVendedor($id_vendedor) {
        $this->conect();
        $sql = "SELECT cv.*, u.nombre, u.apellido
                FROM calificaciones_vendedor cv
                INNER JOIN usuarios u ON cv.id_usuario = u.id_usuario
                WHERE cv.id_vendedor = :id_vendedor
                ORDER BY cv.fecha_calificacion DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function usuarioPuedeCalificar($id_usuario, $id_vendedor, $id_pedido) {
        $this->conect();
        
        $sql = "SELECT COUNT(*) as ya_califico
                FROM calificaciones_vendedor
                WHERE id_usuario = :id_usuario 
                AND id_vendedor = :id_vendedor 
                AND id_pedido = :id_pedido";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $result['ya_califico'] == 0;
    }
}
?>