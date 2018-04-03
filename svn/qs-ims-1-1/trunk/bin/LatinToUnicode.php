<?php

include "sysbase.php";
$db    = Qss_Db::getAdapter('main');

$content = file_get_contents('D:\latin.sql');
$sql = sprintf('drop table if exists utf8');
$db->execute($sql);
$sql = sprintf('CREATE TABLE `utf8` (`Content` LONGTEXT NULL)ENGINE=InnoDB');
$db->execute($sql);
$sql = sprintf('insert into utf8(`Content`) value(%1$s)',$db->quote($content));
$db->execute($sql);
$sql = sprintf('select 	convert(cast(convert(Content using latin1) as binary) using utf8) as Content from utf8');
$data = $db->fetchOne($sql);
$from = array('DEFAULT CHARSET=latin1','MyISAM');
$to = array('','InnoDB');
$utf8  = str_replace($from,$to,$data->Content);
file_put_contents('D:\utf8.sql',$utf8);
?>