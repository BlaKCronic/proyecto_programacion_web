<?php
class Sistema {
    var $_DNS = "mysql:host=mariadb;dbname=database";
    var $_USER = "user";
    var $_PASSWORD = "password";
    var $_BD = null;
    
    function conect() {
        try {
            $this->_BD = new PDO($this->_DNS, $this->_USER, $this->_PASSWORD);
            $this->_BD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_BD->exec("SET NAMES utf8");
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    function cargarImagen($campo, $carpeta) {
        if(isset($_FILES[$campo]) && $_FILES[$campo]['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES[$campo]['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '.' . $ext;
                $ruta = __DIR__ . '/../img/' . $carpeta . '/';
                
                if(!is_dir($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                
                if(move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta . $newname)) {
                    return $newname;
                }
            }
        }
        return null;
    }

    function eliminarImagen($carpeta, $nombre_archivo) {
        if($nombre_archivo && file_exists(__DIR__ . '/../img/' . $carpeta . '/' . $nombre_archivo)) {
            unlink(__DIR__ . '/../img/' . $carpeta . '/' . $nombre_archivo);
            return true;
        }
        return false;
    }

    function sanitizar($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function formatearPrecio($precio) {
        return '$' . number_format($precio, 2);
    }

    function formatearFecha($fecha) {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $timestamp = strtotime($fecha);
        $dia = date('d', $timestamp);
        $mes = $meses[date('n', $timestamp)];
        $anio = date('Y', $timestamp);
        
        return "$dia de $mes de $anio";
    }
}
?>