<?php
class Qss_Bin_Validation_M602 extends Qss_Lib_Validation
{
	public function onValidated()
	{
		parent::init();
		/*if(($this->_params->SoLuongTT) && ($this->_params->SoLuongHC < $this->_params->SoLuongTT) )
		{
			$this->setError();
			$this->setMessage(sprintf('Số lượng hiện có của sản phẩm thấp hơn %1$d %2$s so với số lượng tối thiểu của kho !'
							,($this->_params->SoLuongTT - $this->_params->SoLuongHC)
							, $this->_params->DonViTinh));
		}

		if(($this->_params->SoLuongTD) && ($this->_params->SoLuongHC > $this->_params->SoLuongTD) )
		{
			$this->setError();
				$this->setMessage(sprintf('Số lượng hiện có của sản phẩm nhiều hơn %1$d %2$s so với số lượng tối đa của kho !'
							,($this->_params->SoLuongHC - $this->_params->SoLuongTD)
							, $this->_params->DonViTinh));
		}
		*/

//        $sql = sprintf('
//            SELECT
//                MatHang.MaSanPham
//                , MatHang.TenSanPham
//                , (HanMuc.SoLuongThoiThieu * DonViTinh.HeSoQuyDoi) AS SoLuongThoiThieu
//                , (HanMuc.SoLuongToiDa * DonViTinh.HeSoQuyDoi) AS SoLuongToiDa
//                , (TonKho.SoLuongHC * DonViTinh.HeSoQuyDoi) AS SoLuongHC
//                , MatHang.DonViTinh
//            FROM OKho AS TonKho
//            INNER JOIN ODanhSachKho AS DSKho ON TonKho.Ref_Kho = DSKho.IOID
//            INNER JOIN OSanPham AS MatHang ON TonKho.Ref_MaSP = MatHang.IOID
//            INNER JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 AND TonKho.Ref_DonViTinh = DonViTinh.IOID
//            INNER JOIN OHanMucLuuTru AS HanMuc ON MatHang.IFID_M113 = HanMuc.IFID_M113 AND HanMuc.Ref_MaKho = TonKho.Ref_Kho
//            WHERE TonKho.IFID_M602 = %1$d
//        ', $this->_params->IFID_M602);
//
//        $tonKho = $this->_db->fetchOne($sql);
//
//        if($tonKho)
//        {
//            if(($tonKho->SoLuongThoiThieu) && ($tonKho->SoLuongHC < $tonKho->SoLuongThoiThieu) )
//            {
//                $this->setError();
//                $this->setMessage(sprintf('Số lượng hiện có của sản phẩm trong kho thấp hơn %1$d %2$s so với số lượng tối thiểu của sản phẩm !'
//                    ,($tonKho->SoLuongThoiThieu - $tonKho->SoLuongHC)
//                    , $tonKho->DonViTinh));
//            }
//
//            if(($tonKho->SoLuongToiDa) && ($tonKho->SoLuongHC > $tonKho->SoLuongToiDa) )
//            {
//                $this->setError();
//                $this->setMessage(sprintf('Số lượng hiện có của sản phẩm trong kho nhiều hơn %1$d %2$s so với số lượng tối đa của sản phẩm !'
//                    ,($tonKho->SoLuongHC - $tonKho->SoLuongToiDa)
//                    , $tonKho->DonViTinh));
//            }
//        }
	}
}
