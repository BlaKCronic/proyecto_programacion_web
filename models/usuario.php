<?php
require_once "sistema.php";

class Usuario extends Sistema {
    
    function create($data) {
        $this->conect();
        $sql = "INSERT INTO usuarios (nombre, apellido, email, password, telefono, direccion, ciudad, estado, codigo_postal) 
                VALUES (:nombre, :apellido, :email, :password, :telefono, :direccion, :ciudad, :estado, :codigo_postal)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
        $sth->bindParam(":apellido", $data['apellido'], PDO::PARAM_STR);
        $sth->bindParam(":email", $data['email'], PDO::PARAM_STR);
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sth->bindParam(":password", $password_hash, PDO::PARAM_STR);
        $sth->bindParam(":telefono", $data['telefono'], PDO::PARAM_STR);
        $sth->bindParam(":direccion", $data['direccion'], PDO::PARAM_STR);
        $sth->bindParam(":ciudad", $data['ciudad'], PDO::PARAM_STR);
        $sth->bindParam(":estado", $data['estado'], PDO::PARAM_STR);
        $sth->bindParam(":codigo_postal", $data['codigo_postal'], PDO::PARAM_STR);
        $sth->execute();
        return $sth->rowCount();
    }

    function read() {
        $this->conect();
        $sql = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readOne($id) {
        $this->conect();
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_usuario", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function update($data, $id) {
        $this->conect();
        $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, 
                telefono = :telefono, direccion = :direccion, ciudad = :ciudad, 
                estado = :estado, codigo_postal = :codigo_postal, activo = :activo 
                WHERE id_usuario = :id_usuario";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
        $sth->bindParam(":apellido", $data['apellido'], PDO::PARAM_STR);
        $sth->bindParam(":email", $data['email'], PDO::PARAM_STR);
        $sth->bindParam(":telefono", $data['telefono'], PDO::PARAM_STR);
        $sth->bindParam(":direccion", $data['direccion'], PDO::PARAM_STR);
        $sth->bindParam(":ciudad", $data['ciudad'], PDO::PARAM_STR);
        $sth->bindParam(":estado", $data['estado'], PDO::PARAM_STR);
        $sth->bindParam(":codigo_postal", $data['codigo_postal'], PDO::PARAM_STR);
        $sth->bindParam(":activo", $data['activo'], PDO::PARAM_BOOL);
        $sth->bindParam(":id_usuario", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function updatePassword($id, $new_password) {
        $this->conect();
        $sql = "UPDATE usuarios SET password = :password WHERE id_usuario = :id_usuario";
        $sth = $this->_BD->prepare($sql);
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sth->bindParam(":password", $password_hash, PDO::PARAM_STR);
        $sth->bindParam(":id_usuario", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function delete($id) {
        if(is_numeric($id)) {
            $this->conect();
            $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_usuario", $id, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        }
        return null;
    }

    function login($email, $password) {
        $this->conect();
        $sql = "SELECT * FROM usuarios WHERE email = :email AND activo = 1";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":email", $email, PDO::PARAM_STR);
        $sth->execute();
        $user = $sth->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    function emailExists($email) {
        $this->conect();
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":email", $email, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
?>