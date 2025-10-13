<?php
require_once "sistema.php";

class Categoria extends Sistema {
    
    function create($data) {
        $this->conect();
        $sql = "INSERT INTO categorias (nombre, descripcion, imagen) 
                VALUES (:nombre, :descripcion, :imagen)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
        $sth->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $sth->bindParam(":imagen", $data['imagen'], PDO::PARAM_STR);
        $sth->execute();
        return $sth->rowCount();
    }

    function read() {
        $this->conect();
        $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readAll() {
        $this->conect();
        $sql = "SELECT * FROM categorias ORDER BY nombre";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readOne($id) {
        $this->conect();
        $sql = "SELECT * FROM categorias WHERE id_categoria = :id_categoria";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_categoria", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function update($data, $id) {
        $this->conect();
        $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, 
                imagen = :imagen, activo = :activo 
                WHERE id_categoria = :id_categoria";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
        $sth->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $sth->bindParam(":imagen", $data['imagen'], PDO::PARAM_STR);
        $sth->bindParam(":activo", $data['activo'], PDO::PARAM_BOOL);
        $sth->bindParam(":id_categoria", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function delete($id) {
        if(is_numeric($id)) {
            $this->conect();
            $sql = "DELETE FROM categorias WHERE id_categoria = :id_categoria";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_categoria", $id, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        }
        return null;
    }

    function cargarImagen($carpeta) {
        if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagen']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['imagen']['tmp_name'], '../img/' . $carpeta . '/' . $newname)) {
                    return $newname;
                }
            }
        }
        return null;
    }
}
?>