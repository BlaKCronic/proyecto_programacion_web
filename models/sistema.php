<?php
if(file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
}

class Sistema {
    var $_DNS;
    var $_USER;
    var $_PASSWORD;
    var $_BD = null;
    
    function __construct() {
        if(defined('DB_HOST') && defined('DB_NAME')) {
            $this->_DNS = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        } else {
            $this->_DNS = "mysql:host=mariadb;dbname=database";
        }
        
        $this->_USER = defined('DB_USER') ? DB_USER : 'user';
        $this->_PASSWORD = defined('DB_PASS') ? DB_PASS : 'password';
    }
    
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
                if($_FILES[$campo]['size'] > 5242880) {
                    return null;
                }
                
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

    function cargarMultiplesImagenes($campo, $carpeta) {
        $imagenes = [];
        
        if(isset($_FILES[$campo]) && is_array($_FILES[$campo]['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $count = count($_FILES[$campo]['name']);
            
            for($i = 0; $i < $count; $i++) {
                if($_FILES[$campo]['error'][$i] == 0) {
                    $filename = $_FILES[$campo]['name'][$i];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if(in_array($ext, $allowed) && $_FILES[$campo]['size'][$i] <= 5242880) {
                        $newname = uniqid() . '.' . $ext;
                        $ruta = __DIR__ . '/../img/' . $carpeta . '/';
                        
                        if(!is_dir($ruta)) {
                            mkdir($ruta, 0777, true);
                        }
                        
                        if(move_uploaded_file($_FILES[$campo]['tmp_name'][$i], $ruta . $newname)) {
                            $imagenes[] = $newname;
                        }
                    }
                }
            }
        }
        
        return !empty($imagenes) ? implode(',', $imagenes) : null;
    }

    function eliminarImagen($carpeta, $nombre_archivo) {
        if($nombre_archivo) {
            $ruta = __DIR__ . '/../img/' . $carpeta . '/' . $nombre_archivo;
            if(file_exists($ruta)) {
                return unlink($ruta);
            }
        }
        return false;
    }

    function eliminarMultiplesImagenes($carpeta, $nombres_imagenes) {
        if($nombres_imagenes) {
            $imagenes = explode(',', $nombres_imagenes);
            foreach($imagenes as $imagen) {
                $this->eliminarImagen($carpeta, trim($imagen));
            }
            return true;
        }
        return false;
    }

    function sanitizar($data) {
        if(is_array($data)) {
            return array_map([$this, 'sanitizar'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function validarTelefono($telefono) {
        return preg_match('/^[0-9]{10}$|^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $telefono);
    }

    function generarToken($longitud = 32) {
        return bin2hex(random_bytes($longitud));
    }

    function formatearPrecio($precio) {
        return '$' . number_format($precio, 2, '.', ',');
    }

    function formatearFecha($fecha, $formato = 'largo') {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $timestamp = strtotime($fecha);
        
        if($formato == 'corto') {
            return date('d/m/Y', $timestamp);
        } elseif($formato == 'largo') {
            $dia = date('d', $timestamp);
            $mes = $meses[date('n', $timestamp)];
            $anio = date('Y', $timestamp);
            return "$dia de $mes de $anio";
        } elseif($formato == 'completo') {
            $dia = date('d', $timestamp);
            $mes = $meses[date('n', $timestamp)];
            $anio = date('Y', $timestamp);
            $hora = date('H:i', $timestamp);
            return "$dia de $mes de $anio a las $hora";
        }
        
        return date('d/m/Y H:i', $timestamp);
    }

    function calcularPorcentajeDescuento($precio_original, $precio_descuento) {
        if($precio_descuento && $precio_descuento < $precio_original) {
            $descuento = (($precio_original - $precio_descuento) / $precio_original) * 100;
            return round($descuento);
        }
        return 0;
    }

    function generarSlug($texto) {
        $texto = strtolower($texto);
        
        $caracteres = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u'
        ];
        $texto = strtr($texto, $caracteres);
        
        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
        
        $texto = trim($texto, '-');
        
        return $texto;
    }

    function registrarLog($accion, $tabla = null, $id_registro = null, $detalles = null) {
        $this->conect();
        
        $usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['vendedor_id'] ?? $_SESSION['admin_id'] ?? null;
        $tipo_usuario = isset($_SESSION['usuario_id']) ? 'usuario' : (isset($_SESSION['vendedor_id']) ? 'vendedor' : 'admin');

        return true;
    }

    function paginar($total_registros, $registros_por_pagina = 12, $pagina_actual = 1) {
        $total_paginas = ceil($total_registros / $registros_por_pagina);
        $pagina_actual = max(1, min($pagina_actual, $total_paginas));
        $offset = ($pagina_actual - 1) * $registros_por_pagina;
        
        return [
            'total_registros' => $total_registros,
            'total_paginas' => $total_paginas,
            'pagina_actual' => $pagina_actual,
            'registros_por_pagina' => $registros_por_pagina,
            'offset' => $offset,
            'tiene_anterior' => $pagina_actual > 1,
            'tiene_siguiente' => $pagina_actual < $total_paginas
        ];
    }

    function generarPaginacion($paginacion, $url_base) {
        $html = '<nav aria-label="Navegación de páginas">';
        $html .= '<ul class="pagination justify-content-center">';
        
        if($paginacion['tiene_anterior']) {
            $prev = $paginacion['pagina_actual'] - 1;
            $html .= "<li class='page-item'><a class='page-link' href='{$url_base}?pagina={$prev}'>Anterior</a></li>";
        } else {
            $html .= "<li class='page-item disabled'><span class='page-link'>Anterior</span></li>";
        }
        
        for($i = 1; $i <= $paginacion['total_paginas']; $i++) {
            $active = $i == $paginacion['pagina_actual'] ? 'active' : '';
            $html .= "<li class='page-item {$active}'><a class='page-link' href='{$url_base}?pagina={$i}'>{$i}</a></li>";
        }
        
        if($paginacion['tiene_siguiente']) {
            $next = $paginacion['pagina_actual'] + 1;
            $html .= "<li class='page-item'><a class='page-link' href='{$url_base}?pagina={$next}'>Siguiente</a></li>";
        } else {
            $html .= "<li class='page-item disabled'><span class='page-link'>Siguiente</span></li>";
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
}
?>