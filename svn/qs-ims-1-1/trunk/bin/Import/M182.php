<?php
class Qss_Bin_Import_M182 extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$ifid = $this->_form->i_IFID;
		if($ifid)
		{
			$sql = sprintf('update OChiTietBanGiaoTaiSan as v 
					inner join ODanhSachNhanVien as nv on nv.IOID = v.Ref_MaNhanVien
					set v.TenNhanVien = nv.TenNhanVien
					, v.Ref_TenNhanVien = nv.IOID
					, v.NhaMay = nv.TenPhongBan
					, v.BoPhan = nv.TenBoPhan
                    , v.Ref_NhaMay = nv.Ref_MaPhongBan
					, v.Ref_BoPhan = nv.Ref_MaBoPhan
					where v.IFID_M182=%1$d',$ifid);
			$this->_db->execute($sql);

			$sql = sprintf('update OChiTietBanGiaoTaiSan as v 
					inner join ODanhMucCongCuDungCu as nv on nv.IOID = v.Ref_MaTaiSan
					set v.DonViTinh = nv.DonViTinh,v.Ref_DonViTinh = nv.Ref_DonViTinh, v.TenTaiSan = nv.Ten, v.Ref_TenTaiSan = nv.IOID
					where v.IFID_M182=%1$d',$ifid);
			$this->_db->execute($sql);


            $sql = sprintf('update OChiTietBanGiaoTaiSan as v 
					inner join ODanhMucCongCuDungCu as nv on nv.IOID = v.Ref_MaTaiSan
					set v.DonGia = nv.NguyenGia
					where v.IFID_M182=%1$d AND IFNULL(v.DonGia, "") = ""',$ifid);
            $this->_db->execute($sql);

			$sql = sprintf('update OChiTietBanGiaoTaiSan as v 
					set v.ThanhTien = SoLuong*DonGia
					where v.IFID_M182=%1$d',$ifid);
			$this->_db->execute($sql);
		}
	}
	
}