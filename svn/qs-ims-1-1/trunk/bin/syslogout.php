<?php
//include "sysbase.php";
/*Login*/
$params = Qss_Params::getInstance();
$params->registers->set('userinfo', null);
$params->sessions->destroy();
?>