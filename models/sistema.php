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

    function enviarCorreo($para, $asunto, $mensaje, $nombre = null) {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth = true;
        
        $mail->Username = '22030935@itcelaya.edu.mx';
        $mail->Password = '3H2ULu9Z5a3FLsT7Q23ijg';
        
        $mail->setFrom('22030935@itcelaya.edu.mx', 'Amazon Lite');
        $mail->addAddress($para, $nombre ? $nombre : 'Cliente');
        $mail->Subject = $asunto;
        $mail->msgHTML($mensaje);
        $mail->CharSet = 'UTF-8';
        
        if (!$mail->send()) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
        
        return true;
    }

    function solicitarRecuperacion($email, $tipo = 'usuario') {
        if(!$this->validarEmail($email)) {
            return false;
        }
        
        $this->conect();
        
        $token = bin2hex(random_bytes(32));
        $token_hash = md5($token . 'AmazonLiteSecret2025');
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
    $tabla = $tipo === 'vendedor' ? 'vendedores' : 'usuarios';
        
        $sql = "SELECT * FROM {$tabla} WHERE email = :email";
        $stmt = $this->_BD->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if($stmt->rowCount() == 0) {
            return false;
        }
        
        try {
            $colCheck = $this->_BD->query("SHOW COLUMNS FROM {$tabla} LIKE 'token_recuperacion'")->fetch();

            if($colCheck) {
                $sql = "UPDATE {$tabla} SET token_recuperacion = :token, 
                        token_expiracion = :expiracion WHERE email = :email";
                $stmt = $this->_BD->prepare($sql);
                $stmt->bindParam(':token', $token_hash, PDO::PARAM_STR);
                $stmt->bindParam(':expiracion', $expiracion, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                $ok = $stmt->rowCount() > 0;
            } else {
                $this->_BD->exec("CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expiracion DATETIME NOT NULL,
                    tipo VARCHAR(50) DEFAULT 'usuario',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX(email),
                    INDEX(token)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $sql = "SELECT id FROM password_resets WHERE email = :email AND tipo = :tipo";
                $stmt = $this->_BD->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
                $stmt->execute();

                if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sql = "UPDATE password_resets SET token = :token, expiracion = :expiracion WHERE id = :id";
                    $stmt = $this->_BD->prepare($sql);
                    $stmt->bindParam(':token', $token_hash, PDO::PARAM_STR);
                    $stmt->bindParam(':expiracion', $expiracion, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $ok = $stmt->rowCount() >= 0;
                } else {
                    $sql = "INSERT INTO password_resets (email, token, expiracion, tipo) VALUES (:email, :token, :expiracion, :tipo)";
                    $stmt = $this->_BD->prepare($sql);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':token', $token_hash, PDO::PARAM_STR);
                    $stmt->bindParam(':expiracion', $expiracion, PDO::PARAM_STR);
                    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
                    $stmt->execute();
                    $ok = $stmt->rowCount() > 0;
                }
            }
        } catch(PDOException $e) {
            error_log('Error en solicitarRecuperacion: ' . $e->getMessage());
            return false;
        }

        if(!empty($ok)) {
            $url_base = $tipo === 'vendedor' ? 'vendedor/recuperar_password.php' : 'recuperar_password.php';
            $url_recuperacion = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . 
                               "/{$url_base}?token={$token_hash}&email=" . urlencode($email);
            
            $asunto = "Recuperación de contraseña - Amazon Lite";
            $mensaje = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #232F3E;'>Recuperación de contraseña</h2>
                        <p>Hola,</p>
                        <p>Recibimos una solicitud para restablecer tu contraseña de Amazon Lite.</p>
                        <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='{$url_recuperacion}' 
                               style='background-color: #FF9900; color: white; padding: 12px 30px; 
                                      text-decoration: none; border-radius: 3px; display: inline-block;'>
                                Restablecer contraseña
                            </a>
                        </p>
                        <p>O copia y pega este enlace en tu navegador:</p>
                        <p style='word-break: break-all; color: #0066c0;'>{$url_recuperacion}</p>
                        <p><strong>Este enlace expirará en 1 hora.</strong></p>
                        <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este correo.</p>
                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                        <p style='color: #666; font-size: 12px;'>
                            Este es un correo automático, por favor no respondas a este mensaje.
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            return $this->enviarCorreo($email, $asunto, $mensaje);
        }
        
        return false;
    }

    function verificarToken($email, $token, $tipo = 'usuario') {
        $this->conect();

        $tabla = $tipo === 'vendedor' ? 'vendedores' : 'usuarios';

        try {
            $colCheck = $this->_BD->query("SHOW COLUMNS FROM {$tabla} LIKE 'token_recuperacion'")->fetch();

            if($colCheck) {
                $sql = "SELECT * FROM {$tabla} 
                        WHERE email = :email 
                        AND token_recuperacion = :token 
                        AND token_expiracion > NOW()";
                $stmt = $this->_BD->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->rowCount() > 0;
            }

            $sql = "SELECT * FROM password_resets WHERE email = :email AND token = :token AND expiracion > NOW() AND tipo = :tipo";
            $stmt = $this->_BD->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            error_log('Error en verificarToken: ' . $e->getMessage());
            return false;
        }
    }

    function restablecerPassword($email, $token, $nueva_password, $tipo = 'usuario') {
        if(!$this->verificarToken($email, $token, $tipo)) {
            return false;
        }
        
        $this->conect();
        
        $tabla = $tipo === 'vendedor' ? 'vendedores' : 'usuarios';
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

        try {
            $colCheck = $this->_BD->query("SHOW COLUMNS FROM {$tabla} LIKE 'token_recuperacion'")->fetch();

            if($colCheck) {
                $sql = "UPDATE {$tabla} 
                        SET password = :password, 
                            token_recuperacion = NULL, 
                            token_expiracion = NULL 
                        WHERE email = :email 
                        AND token_recuperacion = :token";

                $stmt = $this->_BD->prepare($sql);
                $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->execute();

                return $stmt->rowCount() > 0;
            }

            $sql = "UPDATE {$tabla} SET password = :password WHERE email = :email";
            $stmt = $this->_BD->prepare($sql);
            $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // Eliminar token usado
            $sql = "DELETE FROM password_resets WHERE email = :email AND token = :token AND tipo = :tipo";
            $stmt = $this->_BD->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } catch(PDOException $e) {
            error_log('Error en restablecerPassword: ' . $e->getMessage());
            return false;
        }
    }
}
?>