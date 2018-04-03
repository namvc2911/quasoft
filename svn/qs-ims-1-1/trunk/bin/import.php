<?php
include "sysbase.php";
error_log($domain);
$user = $argv[2];
$user = unserialize(base64_decode($user));
Qss_Register::set('userinfo',$user);
//error_log(var_dump($user));
$msg = $argv[3];
$msg = base64_decode($msg);
$service = new Qss_Service();
$service->Socket->Import($user,$msg);
?>