<?php
require_once "sistema.php";

class Vendedor extends Sistema {
    
    function create($data) {
        $this->conect();
        $sql = "INSERT INTO vendedores (nombre_tienda, email, password, nombre_contacto, telefono, 
                direccion, rfc, razon_social, descripcion) 
                VALUES (:nombre_tienda, :email, :password, :nombre_contacto, :telefono, 
                :direccion, :rfc, :razon_social, :descripcion)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":nombre_tienda", $data['nombre_tienda'], PDO::PARAM_STR);
        $sth->bindParam(":email", $data['email'], PDO::PARAM_STR);
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sth->bindParam(":password", $password_hash, PDO::PARAM_STR);
        $sth->bindParam(":nombre_contacto", $data['nombre_contacto'], PDO::PARAM_STR);
        $sth->bindParam(":telefono", $data['telefono'], PDO::PARAM_STR);
        $sth->bindParam(":direccion", $data['direccion'], PDO::PARAM_STR);
        $sth->bindParam(":rfc", $data['rfc'], PDO::PARAM_STR);
        $sth->bindParam(":razon_social", $data['razon_social'], PDO::PARAM_STR);
        $sth->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $sth->execute();
        return $this->_BD->lastInsertId();
    }

    function read() {
        $this->conect();
        $sql = "SELECT * FROM vendedores ORDER BY fecha_registro DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readAprobados() {
        $this->conect();
        $sql = "SELECT * FROM vendedores WHERE estado_aprobacion = 'aprobado' AND activo = 1";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readPendientes() {
        $this->conect();
        $sql = "SELECT * FROM vendedores WHERE estado_aprobacion = 'pendiente'";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readOne($id) {
        $this->conect();
        $sql = "SELECT * FROM vendedores WHERE id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function update($data, $id) {
        $this->conect();
        $sql = "UPDATE vendedores SET nombre_tienda = :nombre_tienda, email = :email, 
                nombre_contacto = :nombre_contacto, telefono = :telefono, direccion = :direccion, 
                rfc = :rfc, razon_social = :razon_social, descripcion = :descripcion, 
                logo = :logo, activo = :activo 
                WHERE id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":nombre_tienda", $data['nombre_tienda'], PDO::PARAM_STR);
        $sth->bindParam(":email", $data['email'], PDO::PARAM_STR);
        $sth->bindParam(":nombre_contacto", $data['nombre_contacto'], PDO::PARAM_STR);
        $sth->bindParam(":telefono", $data['telefono'], PDO::PARAM_STR);
        $sth->bindParam(":direccion", $data['direccion'], PDO::PARAM_STR);
        $sth->bindParam(":rfc", $data['rfc'], PDO::PARAM_STR);
        $sth->bindParam(":razon_social", $data['razon_social'], PDO::PARAM_STR);
        $sth->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $sth->bindParam(":logo", $data['logo'], PDO::PARAM_STR);
        $sth->bindParam(":activo", $data['activo'], PDO::PARAM_BOOL);
        $sth->bindParam(":id_vendedor", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function updateEstadoAprobacion($id, $estado) {
        $this->conect();
        $sql = "UPDATE vendedores SET estado_aprobacion = :estado WHERE id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":estado", $estado, PDO::PARAM_STR);
        $sth->bindParam(":id_vendedor", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function updateCalificacion($id) {
        $this->conect();
        $sql = "UPDATE vendedores v 
                SET calificacion_promedio = (
                    SELECT COALESCE(AVG(calificacion), 0) 
                    FROM calificaciones_vendedor 
                    WHERE id_vendedor = :id_vendedor
                )
                WHERE id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function delete($id) {
        if(is_numeric($id)) {
            $this->conect();
            $sql = "DELETE FROM vendedores WHERE id_vendedor = :id_vendedor";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_vendedor", $id, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        }
        return null;
    }

    function incrementarVentas($id_vendedor, $monto) {
        $this->conect();
        $sql = "UPDATE vendedores SET total_ventas = COALESCE(total_ventas, 0) + :monto WHERE id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":monto", $monto, PDO::PARAM_STR);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function decrementarVentas($id_vendedor, $monto) {
        $this->conect();
        $sql = "UPDATE vendedores SET total_ventas = GREATEST(COALESCE(total_ventas, 0) - :monto, 0) WHERE id_vendedor = :id_vendedor";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":monto", $monto, PDO::PARAM_STR);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function login($email, $password) {
        $this->conect();
        $sql = "SELECT * FROM vendedores WHERE email = :email AND activo = 1 AND estado_aprobacion = 'aprobado'";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":email", $email, PDO::PARAM_STR);
        $sth->execute();
        $vendedor = $sth->fetch(PDO::FETCH_ASSOC);
        
        if($vendedor && password_verify($password, $vendedor['password'])) {
            return $vendedor;
        }
        return false;
    }

    function emailExists($email) {
        $this->conect();
        $sql = "SELECT COUNT(*) as count FROM vendedores WHERE email = :email";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":email", $email, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    function cargarLogo($carpeta) {
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['logo']['tmp_name'], '../img/' . $carpeta . '/' . $newname)) {
                    return $newname;
                }
            }
        }
        return null;
    }
}
?>