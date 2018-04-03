<?php
/**
 * update lookup cho 1 trường
 * @param domain, objectcode, fieldcode
 */
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
$objid = 0;
$fieldid = 0;
if(isset($argv[2]))
{
	$objid = $argv[2];
}
if(isset($argv[3]))
{
	$fieldid = $argv[3];
}
if(!$objid || !$fieldid)
{
	echo 'params are: domain, objectcode, fieldcode';
	return ;
}
updateLookup($objid,$fieldid);
function updateLookup($ObjectCode,$FieldCode)
{
	global $db;
	$sqlLookup = sprintf('select distinct FieldCode,qsfields.ObjectCode
										from qsfields 
										inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
										inner join qsfobjects on qsfobjects.ObjectCode = qsobjects.ObjectCode
										inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
										where qsforms.Effected = 1 and RefObjectCode = %2$s and RefFieldCode = %1$s'
			,$db->quote($FieldCode)
			,$db->quote($ObjectCode));
	$dataLookup = $db->fetchAll($sqlLookup);
	foreach ($dataLookup as $lookup)
	{
		if($db->tableExists($lookup->ObjectCode))
		{
			if($lookup->ObjectCode == $ObjectCode) 
			{
				//create temp
				$temp = sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS %1$s_tmp AS (select * from %1$s)',
						$ObjectCode);
				$db->execute($temp);
				$sqlUpdateLookup = sprintf('update 
						%1$s as v
						inner join %2$s_tmp as t on t.IOID = v.Ref_%3$s 
						set v.%3$s = t.%4$s'
						,$lookup->ObjectCode
						,$ObjectCode
						,$lookup->FieldCode
						,$FieldCode);
				$db->execute($sqlUpdateLookup);

                //DROP TABLE IF NOT EXISTS %1$s_tmp
				$temp = sprintf('DROP TABLE IF EXISTS %1$s_tmp',
						$ObjectCode);
				$db->execute($temp);
			}
			else
			{
				$sqlUpdateLookup = sprintf('update 
						%1$s as v
						inner join %2$s as t on t.IOID = v.Ref_%3$s 
						set v.%3$s = t.%4$s'
						,$lookup->ObjectCode
						,$ObjectCode
						,$lookup->FieldCode
						,$FieldCode);
				$db->execute($sqlUpdateLookup);
			}
			updateLookUp($lookup->ObjectCode,$lookup->FieldCode);
		}
	}
}

?>