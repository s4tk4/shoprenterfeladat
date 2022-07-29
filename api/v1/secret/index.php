<?php

$droot = $_SERVER['DOCUMENT_ROOT'];
require_once $droot.'/api/controllers/secretcontroller.php';
require_once $droot.'/api/config/database.php';
require_once $droot.'/api/models/secret.php';

if($_SERVER["REQUEST_METHOD"]=="GET")
{
    $controller = secretcontroller::getController();
    
    $controller->doGetRequest($_GET);

}
else if ($_SERVER["REQUEST_METHOD"]=="POST")
{
    $controller = secretcontroller::getController();
    
    $controller->doPostRequest($_POST);
}