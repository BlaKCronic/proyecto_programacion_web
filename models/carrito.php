<?php
require_once "sistema.php";

class Carrito extends Sistema {
    
    function agregar($id_usuario, $id_producto, $cantidad = 1) {
        $this->conect();
        
        $sql_check = "SELECT * FROM carrito WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
        $sth = $this->_BD->prepare($sql_check);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $sth->execute();
        $existe = $sth->fetch(PDO::FETCH_ASSOC);
        
        if($existe) {
            $sql = "UPDATE carrito SET cantidad = cantidad + :cantidad 
                    WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        } else {
            $sql = "INSERT INTO carrito (id_usuario, id_producto, cantidad) 
                    VALUES (:id_usuario, :id_producto, :cantidad)";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $sth->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
            $sth->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        }
    }

    function obtenerCarrito($id_usuario) {
        $this->conect();
        $sql = "SELECT c.*, p.nombre, p.precio, p.precio_descuento, p.imagen_principal, 
                p.stock, v.nombre_tienda, v.id_vendedor
                FROM carrito c
                INNER JOIN productos p ON c.id_producto = p.id_producto
                INNER JOIN vendedores v ON p.id_vendedor = v.id_vendedor
                WHERE c.id_usuario = :id_usuario AND p.activo = 1
                ORDER BY c.fecha_agregado DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function actualizarCantidad($id_carrito, $cantidad) {
        $this->conect();
        $sql = "UPDATE carrito SET cantidad = :cantidad WHERE id_carrito = :id_carrito";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $sth->bindParam(":id_carrito", $id_carrito, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function eliminar($id_carrito) {
        $this->conect();
        $sql = "DELETE FROM carrito WHERE id_carrito = :id_carrito";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_carrito", $id_carrito, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function vaciarCarrito($id_usuario) {
        $this->conect();
        $sql = "DELETE FROM carrito WHERE id_usuario = :id_usuario";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function contarItems($id_usuario) {
        $this->conect();
        $sql = "SELECT COUNT(*) as total FROM carrito WHERE id_usuario = :id_usuario";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    function calcularTotal($id_usuario) {
        $this->conect();
        $sql = "SELECT SUM(
                    c.cantidad * COALESCE(p.precio_descuento, p.precio)
                ) as total
                FROM carrito c
                INNER JOIN productos p ON c.id_producto = p.id_producto
                WHERE c.id_usuario = :id_usuario AND p.activo = 1";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>