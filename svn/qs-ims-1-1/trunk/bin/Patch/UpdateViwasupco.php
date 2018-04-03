<?php
include "../sysbase.php";
$db    = Qss_Db::getAdapter('main');

$insert   = array();
$bd       = 0;
$montYear = array();
$montYearBaoDuong = array();

$sqlThayDoiSoBaoDuong ="
    select * from viwasupco.OPhieuBaoTri
    where Ref_LoaiBaoTri != 1
    order by IFNULL(NgayYeuCau, NgayBatDau) ASC, IFID_M759 ASC 
";
$dataThayDoiSoBaoDuong = $db->fetchAll($sqlThayDoiSoBaoDuong);

foreach($dataThayDoiSoBaoDuong as $item)
{
    $ngay = $item->NgayYeuCau?$item->NgayYeuCau:$item->NgayBatDau;
    $month  = (int)date('m', strtotime($ngay)); $month = ($month<10)?'0'.$month:$month;
    $Year   = date('Y', strtotime($ngay));
    $key    = $month.'.'.$Year;

    if(!isset($montYearBaoDuong[$key])) {
        $montYearBaoDuong[$key] = 0;
    }

    ++$montYearBaoDuong[$key];
    $stt = str_pad($montYearBaoDuong[$key],3,"0", STR_PAD_LEFT);
    $sql = sprintf(' UPDATE OPhieuBaoTri SET SoPhieu = "bd.%1$s.%2$s" WHERE IFID_M759 = %3$d', $key, $stt, $item->IFID_M759);
    $db->execute($sql);
}

$sqlThayDoiSoSuCo = "
    select * from viwasupco.OPhieuBaoTri
    where Ref_LoaiBaoTri = 1
    order by IFNULL(NgayYeuCau, NgayBatDau) ASC, IFID_M759 ASC 
";

$dataThayDoiSoSuCo = $db->fetchAll($sqlThayDoiSoSuCo);


foreach($dataThayDoiSoSuCo as $item)
{
    $ngay = $item->NgayYeuCau?$item->NgayYeuCau:$item->NgayBatDau;
    $month  = (int)date('m', strtotime($ngay)); $month = ($month<10)?'0'.$month:$month;
    $Year   = date('Y', strtotime($ngay));
    $key    = $month.'.'.$Year;

    if(!isset($montYear[$key])) {
        $montYear[$key] = 0;
    }

    ++$montYear[$key];
    $stt = str_pad($montYear[$key],3,"0", STR_PAD_LEFT);
    $sql = sprintf(' UPDATE OPhieuBaoTri SET SoPhieu = "sc.%1$s.%2$s" WHERE IFID_M759 = %3$d', $key, $stt, $item->IFID_M759);
    $db->execute($sql);
}
