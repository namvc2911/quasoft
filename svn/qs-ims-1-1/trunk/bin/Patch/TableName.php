<?php

include "../sysbase.php";
$db    = Qss_Db::getAdapter('main');
$pack = 'pro'; // basic, advance, pro

if(isset($argv[2]))
{
    $pack = $argv[2];
}

// -- UPDATE qsforms SET Effected = 1;
// Inactive các module ko sử dụng

$dataSQL = $db->fetchAll(sprintf("select * from qsobjects"));
foreach($dataSQL as $item)
{
	$table = strtolower($item->ObjectCode);
	if($db->tableExists($table))
	{
		$sql=sprintf('RENAME TABLE %1$s TO %2$s',$table,$item->ObjectCode);
		$db->execute($sql);
	}
}
/*
$dataSQL = $db->fetchAll(sprintf('show tables like "o%%"'));
foreach($dataSQL as $item)
{
	foreach($item as $v)
	{
	$table = $v;
	}
	if($db->tableExists($table))
	{
		$sql=sprintf('DROP TABLE %1$s',$table);
		$db->execute($sql);
	}
}*/
?>