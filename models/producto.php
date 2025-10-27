<?php
require_once "sistema.php";

class Producto extends Sistema {
    
    function create($data) {
        $this->conect();
        $sql = "INSERT INTO productos (id_vendedor, id_categoria, nombre, descripcion, precio, 
                precio_descuento, stock, sku, marca, peso, dimensiones, imagen_principal, imagenes_adicionales) 
                VALUES (:id_vendedor, :id_categoria, :nombre, :descripcion, :precio, 
                :precio_descuento, :stock, :sku, :marca, :peso, :dimensiones, :imagen_principal, :imagenes_adicionales)";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $data['id_vendedor'], PDO::PARAM_INT);
        $sth->bindParam(":id_categoria", $data['id_categoria'], PDO::PARAM_INT);
        $sth->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
        $sth->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $sth->bindParam(":precio", $data['precio'], PDO::PARAM_STR);
        $sth->bindParam(":precio_descuento", $data['precio_descuento'], PDO::PARAM_STR);
        $sth->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
        $sth->bindParam(":sku", $data['sku'], PDO::PARAM_STR);
        $sth->bindParam(":marca", $data['marca'], PDO::PARAM_STR);
        $sth->bindParam(":peso", $data['peso'], PDO::PARAM_STR);
        $sth->bindParam(":dimensiones", $data['dimensiones'], PDO::PARAM_STR);
        $sth->bindParam(":imagen_principal", $data['imagen_principal'], PDO::PARAM_STR);
        $sth->bindParam(":imagenes_adicionales", $data['imagenes_adicionales'], PDO::PARAM_STR);
        $sth->execute();
        return $this->_BD->lastInsertId();
    }

    function read() {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria, v.nombre_tienda 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                WHERE p.activo = 1 
                ORDER BY p.fecha_creacion DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readByCategoria($id_categoria) {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria, v.nombre_tienda 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                WHERE p.id_categoria = :id_categoria AND p.activo = 1 
                ORDER BY p.fecha_creacion DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readByVendedor($id_vendedor) {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                WHERE p.id_vendedor = :id_vendedor 
                ORDER BY p.fecha_creacion DESC";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_vendedor", $id_vendedor, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readOne($id) {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria, v.nombre_tienda, v.id_vendedor, v.calificacion_promedio 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                WHERE p.id_producto = :id_producto";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_producto", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    function buscar($termino, $id_categoria = null) {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria, v.nombre_tienda 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                WHERE p.activo = 1 AND (
                    p.nombre LIKE :termino OR 
                    p.descripcion LIKE :termino OR 
                    p.marca LIKE :termino
                )";
        
        if($id_categoria) {
            $sql .= " AND p.id_categoria = :id_categoria";
        }
        
        $sql .= " ORDER BY p.fecha_creacion DESC";
        
        $sth = $this->_BD->prepare($sql);
        $termino_busqueda = "%{$termino}%";
        $sth->bindParam(":termino", $termino_busqueda, PDO::PARAM_STR);
        
        if($id_categoria) {
            $sth->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        }
        
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readDestacados($limite = 12) {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria, v.nombre_tienda 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                WHERE p.activo = 1 AND p.precio_descuento IS NOT NULL 
                ORDER BY (p.precio - p.precio_descuento) DESC 
                LIMIT :limite";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":limite", $limite, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function readNuevos($limite = 12) {
        $this->conect();
        $sql = "SELECT p.*, c.nombre as categoria, v.nombre_tienda 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                WHERE p.activo = 1 
                ORDER BY p.fecha_creacion DESC 
                LIMIT :limite";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":limite", $limite, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    function update($data, $id) {
        $this->conect();
        $sql = "UPDATE productos SET id_categoria = :id_categoria, nombre = :nombre, 
                descripcion = :descripcion, precio = :precio, precio_descuento = :precio_descuento, 
                stock = :stock, sku = :sku, marca = :marca, peso = :peso, dimensiones = :dimensiones, 
                imagen_principal = :imagen_principal, imagenes_adicionales = :imagenes_adicionales, 
                activo = :activo 
                WHERE id_producto = :id_producto";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":id_categoria", $data['id_categoria'], PDO::PARAM_INT);
        $sth->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
        $sth->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $sth->bindParam(":precio", $data['precio'], PDO::PARAM_STR);
        $sth->bindParam(":precio_descuento", $data['precio_descuento'], PDO::PARAM_STR);
        $sth->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
        $sth->bindParam(":sku", $data['sku'], PDO::PARAM_STR);
        $sth->bindParam(":marca", $data['marca'], PDO::PARAM_STR);
        $sth->bindParam(":peso", $data['peso'], PDO::PARAM_STR);
        $sth->bindParam(":dimensiones", $data['dimensiones'], PDO::PARAM_STR);
        $sth->bindParam(":imagen_principal", $data['imagen_principal'], PDO::PARAM_STR);
        $sth->bindParam(":imagenes_adicionales", $data['imagenes_adicionales'], PDO::PARAM_STR);
        $sth->bindParam(":activo", $data['activo'], PDO::PARAM_BOOL);
        $sth->bindParam(":id_producto", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function updateStock($id, $cantidad) {
        $this->conect();
        $sql = "UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto";
        $sth = $this->_BD->prepare($sql);
        $sth->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $sth->bindParam(":id_producto", $id, PDO::PARAM_INT);
        $sth->execute();
        return $sth->rowCount();
    }

    function delete($id) {
        if(is_numeric($id)) {
            $this->conect();
            $sql = "DELETE FROM productos WHERE id_producto = :id_producto";
            $sth = $this->_BD->prepare($sql);
            $sth->bindParam(":id_producto", $id, PDO::PARAM_INT);
            $sth->execute();
            return $sth->rowCount();
        }
        return null;
    }

    function cargarImagen($campo, $carpeta) {
        if(isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['imagen_principal']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['imagen_principal']['tmp_name'], '../img/' . $carpeta . '/' . $newname)) {
                    return $newname;
                }
            }
        }
        return null;
    }

    function cargarImagenesAdicionales($carpeta) {
        $imagenes = [];
        if(isset($_FILES['imagenes_adicionales'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $count = count($_FILES['imagenes_adicionales']['name']);
            
            for($i = 0; $i < $count; $i++) {
                if($_FILES['imagenes_adicionales']['error'][$i] == 0) {
                    $filename = $_FILES['imagenes_adicionales']['name'][$i];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if(in_array($ext, $allowed)) {
                        $newname = uniqid() . '.' . $ext;
                        if(move_uploaded_file($_FILES['imagenes_adicionales']['tmp_name'][$i], '../img/' . $carpeta . '/' . $newname)) {
                            $imagenes[] = $newname;
                        }
                    }
                }
            }
        }
        return !empty($imagenes) ? implode(',', $imagenes) : null;
    }
}
?>