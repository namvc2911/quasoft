<?php
include "../sysbase.php";
$db    = Qss_Db::getAdapter('main');


if($object->v_fInit('OPhieuBaoTri'))
{
	$object->createView();
}

if($object->v_fInit('O'))
{
	$object->createView();
}
?>