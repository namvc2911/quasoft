<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
$objid = 0;
if(isset($argv[2]))
{
	$objid = $argv[2];
}
if($objid)
{
	$sql = "select 
			   *
			from qsfields
			where FieldType = 5 and ifnull(Regx,'') != '' and ObjectCode = '" . $objid . "'";
}
else
{
	$sql = "select 
			   *
			from qsfields
			where FieldType = 5 and ifnull(Regx,'') != ''";
}
$fields = $db->fetchAll($sql);
foreach ($fields as $item)
{
	$json = (array)Qss_Json::decode($item->Regx);
	if($json)
	{
		foreach ($json as $key=>$value)
		{
			$sql = sprintf('update %1$s set Ref_%2$s = %3$s where %2$s = %4$s'
					,$item->ObjectCode
					,$item->FieldCode
					,$db->quote($value)
					,$db->quote($key));
			$db->execute($sql);
		}
	}
}

?>