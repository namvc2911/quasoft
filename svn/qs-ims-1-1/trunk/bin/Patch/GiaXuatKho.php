<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');

//Update giá xuất kho
$sql = "update ODanhSachXuatKho set DonGia = 0, ThanhTien=0";
$db->execute($sql);

// Lấy danh sách xuất kho sắp xếp theo ngày chứng từ
$sql = "select 
    u.*
    , ifnull(sp.GiaMua, 0) AS GiaMua
    , ifnull(kho.SoLuongKhoiTao, 0) AS SoLuongKhoiTao
    , ifnull(kho.GiaTriKhoiTao, 0) AS GiaTriKhoiTao
    , v.NgayChungTu
from ODanhSachXuatKho as u
inner join OXuatKho as v on u.IFID_M506 = v.IFID_M506 
inner join OSanPham as sp on sp.IOID = u.Ref_MaSP
inner join OKho as kho on kho.Ref_MaSP = u.Ref_MaSP and kho.Ref_Kho = v.Ref_Kho
order by v.NgayChungTu";
$danhsachxuatkho = $db->fetchAll($sql);

//echo '<pre>'; print_r($danhsachxuatkho); die;

foreach($danhsachxuatkho as $xuatkho)
{
    $sql = sprintf('
            SELECT
                SUM(ifnull(giaodich.SoLuong, 0)) AS TongSoLuongNhap,
                SUM(ifnull(giaodich.SoLuong, 0) * ifnull(giaodich.DonGia, 0)) AS TongGiaNhap
            FROM ODanhSachNhapKho AS giaodich
            INNER JOIN ONhapKho as nk on nk.IFID_M402 = giaodich.IFID_M402
            INNER JOIN qsiforms on qsiforms.IFID = nk.IFID_M402
            WHERE qsiforms.Status = 2
            	AND giaodich.Ref_MaSanPham = %1$d
                AND ifnull(giaodich.Ref_ThuocTinh,0) = %2$d
                AND nk.NgayChungTu <= %3$s
            GROUP BY giaodich.Ref_MaSanPham, giaodich.Ref_ThuocTinh'
        , $xuatkho->Ref_MaSP
        , $xuatkho->Ref_ThuocTinh
        , $db->quote($xuatkho->NgayChungTu));
    $trans = $db->fetchOne($sql);    
    
	//tính đơn giá
	if( ($xuatkho->SoLuongKhoiTao > 0) && ($xuatkho->GiaTriKhoiTao > 0))
	{
	    
	    $giamua  = round($xuatkho->GiaTriKhoiTao/$xuatkho->SoLuongKhoiTao,0);
	}
	else
	{
	    $giamua  = $xuatkho->GiaMua;
	}	
	
	
	$tongsoluongnhap = $trans?$trans->TongSoLuongNhap:0;
	$tonggianhap     = $trans?$trans->TongGiaNhap:0;
	$tongsoluong     = $tongsoluongnhap + $xuatkho->SoLuongKhoiTao;
	$giabinhquan     = $tongsoluong?(($xuatkho->SoLuongKhoiTao * $giamua) + $tonggianhap)/$tongsoluong:0;
	$giabinhquan     = $giabinhquan?$giabinhquan:$giamua;
	$currentItem     = $refIOID;
    $giabinhquan     = $giabinhquan/1000;
    $giabinhquan     = (round($giabinhquan,0)) * 1000;
    $thanhTien       = round(($xuatkho->SoLuong * $giabinhquan), 0);
    
//     echo '<pre>DonGia: '; print_r($giabinhquan);
//     echo "\t\r";
//     echo '<pre>SoLuong: '; print_r($xuatkho->SoLuong);
//     echo "\t\r";
//     echo '<pre>ThanhTien: '; print_r($thanhTien);
//     echo "\t\r";
    
    
    // Update danh sach xuat kho
    $updateDS = sprintf('UPDATE ODanhSachXuatKho SET DonGia = %1$s , ThanhTien = %2$s WHERE IOID = %3$d'
        , $giabinhquan, $thanhTien, $xuatkho->IOID);
    $db->execute($updateDS);
//     echo $updateDS;
//     echo "\t\r";
//     echo $updateGD;
//    echo '<pre>--------------------------------';;
    
}

?>