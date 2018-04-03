<?php
include "../sysbase.php";
$db              = Qss_Db::getAdapter('main');

$maThietBiCanXoa = '"MT001K1", "MT011k1", "MT009k1_591031dbba6b8_copy", "MT", "MT001K1_599a8dd7d8272_copy", "MT0duoc", "MT001"';

// Lấy danh sách các object đang lookup đến mã thiết bị
$sqlLookupObjects = sprintf('
	select qsfields.*, qsforms.FormCode, qsfobjects.Main
	from qsfields
	inner join qsfobjects ON qsfields.ObjectCode = qsfobjects .ObjectCode
	inner join qsforms ON qsfobjects.FormCode = qsforms.FormCode
	where qsfields.RefFormCode = "M705" 
	    AND qsfields.RefObjectCode = "ODanhSachThietBi" 
	    AND (qsfields.RefFieldCode = "MaThietBi") 
');
$lookupObjects = $db->fetchAll($sqlLookupObjects);

// Xoa lookup.
foreach($lookupObjects as $luObject) {
    // Với các object là object chính ta cần xóa cả ở những object phụ của Form đó.
    if($luObject->Main) {
        // Lấy IFID ở các form Theo Mã thiết bị truyền vào.
        $sqlIFIDCanXoa = sprintf('SELECT IFID_%4$s AS IFID FROM %1$s WHERE %2$s IN (%3$s)
	    ', $luObject->ObjectCode, $luObject->FieldCode, $maThietBiCanXoa, $luObject->FormCode);

        $arrIFIDCanXoa = $db->fetchAll($sqlIFIDCanXoa);
        $strIFIDCanXoa = '';

        foreach($arrIFIDCanXoa as $ifidCanXoa) {
            $strIFIDCanXoa .= $strIFIDCanXoa?',':'';
            $strIFIDCanXoa .= $ifidCanXoa->IFID;
        }

        if($strIFIDCanXoa) {
            // Lay ra cac object lien quan
            $sqlObjectLienQuan = sprintf('SELECT * FROM qsfobjects WHERE FormCode = "%1$s"', $luObject->FormCode);
            $arrObjectLienQuan = $db->fetchAll($sqlObjectLienQuan);

            // Xóa dữ liệu trong các bảng liên quan
            foreach ($arrObjectLienQuan as $objectLienQuan) {
                $sql = sprintf('
                DELETE FROM %1$s WHERE IFID_%2$s IN (%3$s)
            ', $objectLienQuan->ObjectCode, $luObject->FormCode, $strIFIDCanXoa);
                $db->execute($sql);
            }

            // Xóa trong bảng qsiforms
            $sql = sprintf('DELETE FROM qsiforms WHERE IFID IN (%1$s)', $strIFIDCanXoa);
            $db->execute($sql);
        }
    }

    // Xoa trong bang
    $sql = sprintf('DELETE FROM %1$s WHERE %2$s IN (%3$s)', $luObject->ObjectCode, $luObject->FieldCode, $maThietBiCanXoa);
    $db->execute($sql);
}

// Xoa trong danh sach thiet bi
$sqlIFIDCanXoa = sprintf('SELECT ODanhSachThietBi.IFID_M705 AS IFID FROM ODanhSachThietBi WHERE MaThietBi IN (%1$s)', $maThietBiCanXoa);
$arrIFIDCanXoa = $db->fetchAll($sqlIFIDCanXoa);
$strIFIDCanXoa = '';

foreach($arrIFIDCanXoa as $ifidCanXoa) {
    $strIFIDCanXoa .= $strIFIDCanXoa?',':'';
    $strIFIDCanXoa .= $ifidCanXoa->IFID;
}

if($strIFIDCanXoa) {
    // Lay ra cac object lien quan
    $sqlObjectLienQuan = sprintf('SELECT * FROM qsfobjects WHERE FormCode = "M705"');
    $arrObjectLienQuan = $db->fetchAll($sqlObjectLienQuan);

    // Xóa dữ liệu trong các bảng liên quan
    foreach ($arrObjectLienQuan as $objectLienQuan) {
        $sql = sprintf('
                DELETE FROM %1$s WHERE IFID_M705 IN (%2$s)
            ', $objectLienQuan->ObjectCode, $strIFIDCanXoa);
        $db->execute($sql);
    }

    $sql = sprintf('DELETE FROM qsiforms WHERE IFID IN (%1$s)', $strIFIDCanXoa);
    $db->execute($sql);
}


?>