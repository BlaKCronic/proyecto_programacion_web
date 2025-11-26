<?php
require_once "config/config.php";
require_once "models/usuario.php";
require_once "models/sistema.php";

echo "Verificando configuración...\n";
echo "Host BD: " . DB_HOST . "\n";
echo "Nombre BD: " . DB_NAME . "\n";
echo "Usuario BD: " . DB_USER . "\n";
echo "Verificando extensiones PHP instaladas...\n";
echo "PDO instalado: " . (extension_loaded('pdo') ? 'Sí' : 'No') . "\n";
echo "PDO MySQL instalado: " . (extension_loaded('pdo_mysql') ? 'Sí' : 'No') . "\n";
echo "\n";

$app = new Usuario();
$email = "poncegonzalez849@gmail.com";
$password = "123456";

echo "Iniciando prueba de login...\n";
echo "Email: " . $email . "\n";
echo "\nProbando login directo...\n";

$usuario = $app->login($email, $password);

echo "Resultado de login: \n";
var_dump($usuario);

if($usuario) {
    echo "\nLogin exitoso! Datos del usuario:\n";
    echo "ID: " . $usuario['id_usuario'] . "\n";
    echo "Nombre: " . $usuario['nombre'] . " " . $usuario['apellido'] . "\n";
    echo "Email: " . $usuario['email'] . "\n";
} else {
    echo "\nError de login: Email o contraseña incorrectos\n";
}
?>