<?php
include "sysbase.php";
$objid = 0;
if(isset($argv[2]))
{
	$objid = $argv[2];
}
$object = new Qss_Model_System_Object();
if($objid)
{
	if($object->v_fInit($objid))
	{
		if($object->createView())
		{
			echo 'You have create view!';
		}
	}
	
}
else 
{
	$objects = $object->a_fGetAll();
	foreach($objects as $data)
	{
		$object->v_fInit($data->ObjectCode);
		if($object->createView())
		{
			echo 'You have create view!';
		}
	}
}
?>