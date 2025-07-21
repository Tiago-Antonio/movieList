<?php 
session_start();
class Usuario{
    public $id, $nome, $email, $password;

    function __construct($id, $nome, $email, $password){
        $this-> id = $id;
        $this-> nome = $nome;
        $this-> email = $email;
        $this-> password = $password;
    }

    function ValidaUsuarioSenha($login, $password){
        if($login == $this->nome && $password == $this->password){
            return true;
        }
        return false;
    }

    
}

?>