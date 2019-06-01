<?php
//Criado por Anderson Ismael
//11 de fevereiro de 2019

require 'vendor/autoload.php';

function auth($db=false){
    $configFilename=ROOT.'config/db.php';
    $dbFilename=ROOT.'basic/getbasic/db/db.php';
    if(file_exists($dbFilename)){
        require_once $dbFilename;
        if(!$db){
            $db=db();
        }
        return new Basic\Auth($db);       
    }else{
        die("basic install db");
    }
}

function isAuth(){
    return auth()->isAuth();
}

function logout(){
    return auth()->logout();
}

function signin(){
    return auth()->signin();
}

function signup($user=false){
    return auth()->signup($user);
}
