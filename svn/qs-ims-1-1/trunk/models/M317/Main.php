<?php
class Qss_Model_M317_Main extends Qss_Model_Abstract
{
  
  	public function getLoaiTangCa()
    {
  		$sqlLoaiTangCa = sprintf('
          SELECT * FROM OLoaiTangCa
         ');

    	return $this->_o_DB->fetchAll($sqlLoaiTangCa);	
  	}
  	public function getLoaiNgayNghi()
    {
  		$sqlLoaiNgayNghi = sprintf('
        SELECT OPhanLoaiNghi.TenLoaiNghi,
        Sum(OTongHopNgayNghi.SoGioNghi) as TongNgayNghi
         FROM OTongHopNgayNghi
         INNER JOIN OPhanLoaiNghi ON OTongHopNgayNghi.Ref_LoaiNgayNghi = OPhanLoaiNghi.IOID
          WHERE  IFNULL(OPhanLoaiNghi.LoaiNghi,0)=0 In(1,4)
          Group By OPhanLoaiNghi.MaLoaiNghi
 			');
  		return $this->_o_DB->fetchAll($sqlLoaiNgayNghi);
  	}
       public function getBangKyCong($kyCongIoid = 0, $nhanVienIoid = 0, $phongBanIoid = 0){
       	$where = '';
    	
    	 if( $nhanVienIoid)
    	{
    		$where .= sprintf( ' AND OBangCongTheoKy.Ref_MaNhanVien = %1$d',$nhanVienIoid);
    	}

    	if($phongBanIoid){
        $sqlPhongBan = sprintf('SELECT * From OPhongBan WHERE OPhongBan.IOID = %1$d',$phongBanIoid);
        $phongBan = $this->_o_DB->fetchOne($sqlPhongBan);

    		$where .= sprintf('  AND OPhongBan.lft >= %1$d AND OPhongBan.rgt<=%2$d ',$phongBan->lft,$phongBan->rgt);
    	}
    	   
    	 	$sql = sprintf('SELECT OBangCongTheoKy.*,OPhongBan.* 
    	 		FROM OBangCongTheoKy
    	 		INNER JOIN OPhongBan ON OBangCongTheoKy.Ref_PhongBanHienTai = OPhongBan.IOID
    	 		INNER JOIN ODanhSachNhanVien ON OBangCongTheoKy.Ref_MaNhanVien = ODanhSachNhanVien.IOID
          INNER JOIN OKyCong ON OBangCongTheoKy.Ref_KyCong = OKyCong.IOID
	    	 	WHERE OBangCongTheoKy.IOID = %1$d  %2$s 
          Group By OBangCongTheoKy.Ref_MaNhanVien
 	 		',$kyCongIoid,$where);
    	 	return $this->_o_DB->fetchAll($sql);
       }

    }



?>