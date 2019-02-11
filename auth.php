<?php
//Criado por Anderson Ismael
//11 de fevereiro de 2019

require 'vendor/autoload.php';

function auth($db=false){
    $configFilename=ROOT.'config/db.php';
    $dbFilename=ROOT.'basic/db/db.php';
    if(file_exists($dbFilename)){
        require_once $dbFilename;
    }else{
        die("basic install db");
    }
    if(!$db){
        if(file_exists($configFilename)){
            $cfg=require $configFilename;
        }else{
            die("crie o config/db.php");
        }
    }
    $db=db($cfg);
    return new Auth($db);
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

function signup(){
    return auth()->signup();
}
