<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Valitron\Validator;

class ValidatorHelper {

    public static function configurarMensajesEspanol() {
        try {
            $langDir = __DIR__ . '/lang';
            if(is_dir($langDir)) {
                Validator::langDir($langDir);
                if(file_exists($langDir . '/es.php')) {
                    Validator::lang('es');
                }
            }
        } catch(\Exception $e) {
            error_log("Warning: no se pudo configurar mensajes en español para Valitron: " . $e->getMessage());
        }
        
    }
    
    public static function validarRegistroUsuario($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['nombre', 'apellido', 'email', 'password']);
        $v->rule('email', 'email');
        $v->rule('lengthMin', 'password', 6);
        $v->rule('lengthMin', 'nombre', 2);
        $v->rule('lengthMin', 'apellido', 2);
        $v->rule('lengthMax', 'nombre', 50);
        $v->rule('lengthMax', 'apellido', 50);
        $v->rule('optional', 'telefono');
        $v->rule('regex', 'telefono', '/^[0-9]{10}$/')->message('El teléfono debe tener 10 dígitos');
        
        if(!empty($datos['codigo_postal'])) {
            $v->rule('regex', 'codigo_postal', '/^[0-9]{5}$/')->message('El código postal debe tener 5 dígitos');
        }
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function validarRegistroVendedor($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['nombre_tienda', 'email', 'password', 'nombre_contacto', 'telefono', 'direccion']);
        $v->rule('email', 'email');
        $v->rule('lengthMin', 'password', 6);
        $v->rule('lengthMin', 'nombre_tienda', 3);
        $v->rule('lengthMax', 'nombre_tienda', 100);
        $v->rule('lengthMin', 'nombre_contacto', 3);
        $v->rule('regex', 'telefono', '/^[0-9]{10}$/')->message('El teléfono debe tener 10 dígitos');
        
        if(!empty($datos['rfc'])) {
            $v->rule('regex', 'rfc', '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/i')
              ->message('El RFC no tiene el formato correcto');
        }
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function validarProducto($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['nombre', 'id_categoria', 'precio', 'stock', 'sku']);
        $v->rule('lengthMin', 'nombre', 3);
        $v->rule('lengthMax', 'nombre', 200);
        $v->rule('numeric', 'precio')->message('El precio debe ser un número válido');
        $v->rule('min', 'precio', 0)->message('El precio no puede ser negativo');
        $v->rule('integer', 'stock')->message('El stock debe ser un número entero');
        $v->rule('min', 'stock', 0)->message('El stock no puede ser negativo');
        $v->rule('lengthMin', 'sku', 3);
        $v->rule('lengthMax', 'sku', 50);
        
        if(!empty($datos['precio_descuento'])) {
            $v->rule('numeric', 'precio_descuento');
            if(!empty($datos['precio']) && !empty($datos['precio_descuento'])) {
                if($datos['precio_descuento'] >= $datos['precio']) {
                    $v->error('precio_descuento', 'El precio con descuento debe ser menor al precio normal');
                }
            }
        }
        
        if(!empty($datos['peso'])) {
            $v->rule('numeric', 'peso')->message('El peso debe ser un número válido');
            $v->rule('min', 'peso', 0.01)->message('El peso debe ser mayor a 0');
        }
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }
    
    public static function validarDireccionEnvio($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['direccion', 'ciudad', 'estado', 'codigo_postal', 'telefono']);
        $v->rule('lengthMin', 'direccion', 10);
        $v->rule('lengthMin', 'ciudad', 3);
        $v->rule('regex', 'codigo_postal', '/^[0-9]{5}$/')->message('El código postal debe tener 5 dígitos');
        $v->rule('regex', 'telefono', '/^[0-9]{10}$/')->message('El teléfono debe tener 10 dígitos');
        $v->rule('in', 'estado', [
            'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas',
            'Chihuahua', 'Coahuila', 'Colima', 'Durango', 'Guanajuato', 'Guerrero', 'Hidalgo',
            'Jalisco', 'México', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca',
            'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora',
            'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas', 'Ciudad de México'
        ])->message('Selecciona un estado válido');
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function validarLogin($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);

        $v->labels([
            'email' => 'Correo electrónico',
            'password' => 'Contraseña'
        ]);
        
        $v->rule('required', ['email', 'password']);
        $v->rule('email', 'email');
        $v->rule('lengthMin', 'password', 6);
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function validarCambioPassword($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['password_actual', 'password_nueva', 'password_confirmar']);
        $v->rule('lengthMin', 'password_nueva', 6);
        $v->rule('equals', 'password_confirmar', 'password_nueva')
          ->message('Las contraseñas nuevas no coinciden');
        $v->rule('different', 'password_nueva', 'password_actual')
          ->message('La nueva contraseña debe ser diferente a la actual');
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function validarResena($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['calificacion']);
        $v->rule('integer', 'calificacion');
        $v->rule('min', 'calificacion', 1)->message('La calificación mínima es 1');
        $v->rule('max', 'calificacion', 5)->message('La calificación máxima es 5');
        
        if(!empty($datos['titulo'])) {
            $v->rule('lengthMax', 'titulo', 100);
        }
        
        if(!empty($datos['comentario'])) {
            $v->rule('lengthMax', 'comentario', 1000);
        }
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function validarTarjeta($datos) {
        self::configurarMensajesEspanol();
        
        $v = new Validator($datos);
        
        $v->rule('required', ['numero', 'nombre', 'mes', 'anio', 'cvv']);
        $v->rule('creditCard', 'numero');
        $v->rule('lengthMin', 'nombre', 3);
        $v->rule('integer', 'mes');
        $v->rule('min', 'mes', 1);
        $v->rule('max', 'mes', 12);
        $v->rule('integer', 'anio');
        $v->rule('min', 'anio', date('Y'));
        $v->rule('regex', 'cvv', '/^[0-9]{3,4}$/')->message('El CVV debe tener 3 o 4 dígitos');
        
        return [
            'valido' => $v->validate(),
            'errores' => $v->errors()
        ];
    }

    public static function formatearErrores($errores) {
        if(empty($errores)) {
            return '';
        }
        
        $html = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '<h6><i class="bi bi-exclamation-triangle-fill"></i> Por favor corrige los siguientes errores:</h6>';
        $html .= '<ul class="mb-0">';
        
        foreach($errores as $campo => $mensajes) {
            foreach($mensajes as $mensaje) {
                $html .= '<li>' . htmlspecialchars($mensaje) . '</li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }

    public static function obtenerPrimerError($errores, $campo) {
        if(isset($errores[$campo]) && !empty($errores[$campo])) {
            return $errores[$campo][0];
        }
        return '';
    }

    public static function tieneError($errores, $campo) {
        return isset($errores[$campo]) && !empty($errores[$campo]);
    }

    public static function claseError($errores, $campo) {
        return self::tieneError($errores, $campo) ? 'is-invalid' : '';
    }
}