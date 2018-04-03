<?php
// Cap nhat so phieu trong cho viwasupco
include "../sysbase.php";
$db    = Qss_Db::getAdapter('main');
$monthYear = array();

$object    = new Qss_Model_Object();
$object->v_fInit ('OPhieuBaoTri', 'M759');
$model     = new Qss_Model_Extra_Document($object);

$sql = sprintf('
    SELECT *
    FROM OPhieuBaoTri
    WHERE IFNULL(SoPhieu, "") = ""
    ORDER BY NgayYeuCau ASC
');

$dat = $db->fetchAll($sql);

$oldPrefix = '';
$last      = 0;

foreach($dat as $item) {
    if(trim($item->LoaiBaoTri) == 'Định kỳ') {
        $prefix = 'bd.';
    }
    else {
        $prefix = 'sc.';
    }

    $month = (int)date('m', strtotime($item->NgayYeuCau));
    $month = ($month < 10)?'0'.$month:$month;

    $prefix .= $month.'.'.date('Y', strtotime($item->NgayYeuCau)).'.';

    if($oldPrefix != $prefix) {
        $last = 0;
        $model->setDocField('SoPhieu');
        $model->setLenth(3);
        $model->setPrefix($prefix);
        $last = $model->getLast();
    }

    $soPhieu = $model->writeDocumentNo(++$last);

    $db->execute(sprintf('UPDATE OPhieuBaoTri SET SoPhieu = "%1$s" WHERE IFID_M759 = %2$d ', $soPhieu, $item->IFID_M759));

    $oldPrefix = $prefix;
}