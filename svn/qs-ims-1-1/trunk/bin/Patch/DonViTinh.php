<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
//Update số lượng hiện có trong kho


/*Tồn kho
Kiểm tra đúng ID bên đơn vị tính sản phẩm nhưng khác tên đơn vị tính
select a.MaSP,b.TenSanPham,a.Ref_DonViTinh as DVTKho,b.DVT as DVTSP,b.DonViTinh as DonViTinhSP,a.DonViTinh as DonViTinhKho 
from OKho as a
inner join (select d.TenSanPham,d.IOID, e.IOID as DVT, e.DonViTinh from OSanPham as d
inner join ODonViTinhSP as e on d.IFID_M113 = e.IFID_M113)
as b on a.Ref_MaSP = b.IOID and a.Ref_DonViTinh = b.DVT
where a.DonViTinh != b.DonViTinh
  */
$sql = "update OKho as a
inner join (select d.TenSanPham,d.IOID, e.IOID as DVT, e.DonViTinh from OSanPham as d
inner join ODonViTinhSP as e on d.IFID_M113 = e.IFID_M113)
as b on a.Ref_MaSP = b.IOID and a.Ref_DonViTinh = b.DVT
set a.DonViTinh = b.DonViTinh
where a.DonViTinh != b.DonViTinh";
$db->execute($sql);


/*DanhSachxuatkho
Kiểm tra đúng ID bên đơn vị tính sản phẩm nhưng khác tên đơn vị tính
select a.MaSP,b.TenSanPham,a.Ref_DonViTinh as DVTKho,b.DVT as DVTSP,b.DonViTinh as DonViTinhSP,a.DonViTinh as DonViTinhKho 
from ODanhSachXuatKho as a
inner join (select d.TenSanPham,d.IOID, e.IOID as DVT, e.DonViTinh from OSanPham as d
inner join ODonViTinhSP as e on d.IFID_M113 = e.IFID_M113)
as b on a.Ref_MaSP = b.IOID and a.Ref_DonViTinh = b.DVT
where a.DonViTinh != b.DonViTinh
  */
$sql = "update ODanhSachXuatKho as a
inner join (select d.TenSanPham,d.IOID, e.IOID as DVT, e.DonViTinh from OSanPham as d
inner join ODonViTinhSP as e on d.IFID_M113 = e.IFID_M113)
as b on a.Ref_MaSP = b.IOID and a.Ref_DonViTinh = b.DVT
set a.DonViTinh = b.DonViTinh
where a.DonViTinh != b.DonViTinh";
$db->execute($sql);

/*DanhSachNhapKho
Kiểm tra đúng ID bên đơn vị tính sản phẩm nhưng khác tên đơn vị tính
select a.MaSanPham,b.TenSanPham,a.Ref_DonViTinh as DVTKho,b.DVT as DVTSP,b.DonViTinh as DonViTinhSP,a.DonViTinh as DonViTinhKho 
from ODanhSachNhapKho as a
inner join (select d.TenSanPham,d.IOID, e.IOID as DVT, e.DonViTinh from OSanPham as d
inner join ODonViTinhSP as e on d.IFID_M113 = e.IFID_M113)
as b on a.Ref_MaSanPham = b.IOID and a.Ref_DonViTinh = b.DVT
where a.DonViTinh != b.DonViTinh
  */
$sql = "update ODanhSachNhapKho as a
inner join (select d.TenSanPham,d.IOID, e.IOID as DVT, e.DonViTinh from OSanPham as d
inner join ODonViTinhSP as e on d.IFID_M113 = e.IFID_M113)
as b on a.Ref_MaSanPham = b.IOID and a.Ref_DonViTinh = b.DVT
set a.DonViTinh = b.DonViTinh
where a.DonViTinh != b.DonViTinh";
$db->execute($sql);
?>