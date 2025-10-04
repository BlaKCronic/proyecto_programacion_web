<?php
class Sistema{
    var $_DNS = "mysql:host=mariadb;dbname=database";
    var $_USER = "user";
    var $_PASSWORD = "password";
    var $_BD = null;
    function conect(){
        $this -> _BD = new PDO($this -> _DNS, $this -> _USER, $this -> _PASSWORD);
    }
}
?>