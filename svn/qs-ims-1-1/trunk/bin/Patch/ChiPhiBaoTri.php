<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
$model = new Qss_Model_Maintenance_Workorder();

$sql = "select * from OPhieuBaoTri";
$dataSQL = $db->fetchAll($sql);
foreach($dataSQL as $item)
{
	$model->updateCost($item->IFID_M759);
}
?>