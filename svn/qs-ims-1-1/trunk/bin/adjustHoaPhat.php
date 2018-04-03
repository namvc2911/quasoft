<?php
include "sysbase.php";
include "syslogin.php";
$db = Qss_Db::getAdapter('main');
$sql = "select * from 
		OCongViecBT
		order by IFID_M724";
$bashes = $db->fetchAll($sql);
$process = new Qss_Model_Process();
$bophan = '';
foreach ($bashes as $item)
{
	$content = Qss_Lib_Util::htmlToText($item->MoTa);
	if(substr_count( $content, "\n" ) > 1)
	{
		$sao = false;
		$lines = explode("\n", $content);
		$ioid = $item->IOID;
		foreach ($lines as $string)
		{
			$string = trim($string);
			if(substr($string, 0,1) == '*')
			{
				$bophan = substr($string,1);
				$bophan = trim($bophan);
				$bophan = rtrim($bophan,':');
				$sao = true;
			}
			elseif(!$sao && substr($string, 0,1) == '-')
			{
				$bophan = substr($string,1);
				$bophan = trim($bophan);
				$bophan = rtrim($bophan,':');
			}
			elseif(trim($string)) 
			{
				$newcontent = sprintf('%1$s: %2$s',$bophan,ltrim(ltrim($string,'+'),'-'));
				//insert to db
				$arr = array();
				$arr['OCongViecBT'][0]['Ten'] = $item->Ten;
				$arr['OCongViecBT'][0]['ThoiGian'] = $item->ThoiGian;
				$arr['OCongViecBT'][0]['MoTa'] = $newcontent;
				$arr['OCongViecBT'][0]['ioid'] = $ioid;
				$service = new Qss_Service();
				$data = $service->Form->Manual('M724' ,$item->IFID_M724,$arr,false);
				if($data->isError())
				{
					file_put_contents("D:\log111.txt", $data->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
				$ioid = 0;
			}
		}
	}
}
include "syslogout.php";
?>