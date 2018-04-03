<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
//Update số lượng hiện có trong kho
$sql = "update OKho
left join (select Ref_Kho,MaSanPham as MaSP, DonViTinh, sum(SoLuong) as SoLuongNhap from ODanhSachNhapKho
		inner join ONhapKho on ONhapKho.IFID_M402 =  ODanhSachNhapKho.IFID_M402
		inner join qsiforms on qsiforms.IFID = ODanhSachNhapKho.IFID_M402 where qsiforms.Status=2
		group by Ref_Kho,MaSanPham, DonViTinh) as t1 on t1.Ref_Kho = OKho.Ref_Kho and t1.MaSP = OKho.MaSP and OKho.DonViTinh = t1.DonViTinh
left join (select Ref_Kho,MaSP, DonViTinh, sum(SoLuong) as SoLuongXuat from ODanhSachXuatKho
		inner join OXuatKho on OXuatKho.IFID_M506 =  ODanhSachXuatKho.IFID_M506 
		inner join qsiforms on qsiforms.IFID = ODanhSachXuatKho.IFID_M506 where qsiforms.Status=2
		group by Ref_Kho,MaSP, DonViTinh) as t2 on t2.Ref_Kho = OKho.Ref_Kho and t2.MaSP = OKho.MaSP and OKho.DonViTinh = t2.DonViTinh
set SoLuongHC = ifnull(SoLuongKhoiTao,0) + ifnull(SoLuongNhap,0) - ifnull(SoLuongXuat,0)";
$db->execute($sql);

$sql = "update OThuocTinhChiTiet
left join 
	(select Ref_Kho,Ref_Bin,Ref_MaSanPham, Ref_DonViTinh, sum(SoLuong) as SoLuongNhap from OThuocTinhChiTiet 
	inner join qsiforms on qsiforms.IFID = OThuocTinhChiTiet.IFID_M402 where qsiforms.Status=2
	group by Ref_Kho,Ref_Bin,Ref_MaSanPham, Ref_DonViTinh) 
	as t1 on t1.Ref_Kho = OThuocTinhChiTiet.Ref_Kho and t1.Ref_Bin = OThuocTinhChiTiet.Ref_Bin and t1.Ref_MaSanPham = OThuocTinhChiTiet.Ref_MaSanPham and OThuocTinhChiTiet.Ref_DonViTinh = t1.Ref_DonViTinh
left join 
	(select Ref_Kho,Ref_Bin,Ref_MaSanPham, Ref_DonViTinh, sum(SoLuong) as SoLuongXuat from OThuocTinhChiTiet 
	inner join qsiforms on qsiforms.IFID = OThuocTinhChiTiet.IFID_M506 where qsiforms.Status=2
	group by Ref_Kho,Ref_Bin,Ref_MaSanPham, Ref_DonViTinh) 
	as t2 on t2.Ref_Kho = OThuocTinhChiTiet.Ref_Kho and t2.Ref_Bin = OThuocTinhChiTiet.Ref_Bin and t2.Ref_MaSanPham = OThuocTinhChiTiet.Ref_MaSanPham and OThuocTinhChiTiet.Ref_DonViTinh = t2.Ref_DonViTinh
set SoLuong = ifnull(SoLuongNhap,0) - ifnull(SoLuongXuat,0)
where ifnull(IFID_M602,0) <> 0";
$db->execute($sql);


?>