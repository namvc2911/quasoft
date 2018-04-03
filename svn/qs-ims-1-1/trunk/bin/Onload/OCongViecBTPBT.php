<?php
class Qss_Bin_Onload_OCongViecBTPBT extends Qss_Lib_Onload
{
	public function __doExecute()
	{
		parent::__doExecute();
		//$thue_ngoai = $this->_object->getFieldByCode('ThueNgoai')->getValue();
		//$this->_object->getFieldByCode('MaNCC')->bReadOnly = true;
		//$this->_object->getFieldByCode('ChiPhi')->bReadOnly = true;
		
		/*if($thue_ngoai && $this->_object->intStatus < 3 && $this->_object->FormCode == 'M759')
		{
			$this->_object->getFieldByCode('MaNCC')->bReadOnly  = false;
			$this->_object->getFieldByCode('MaNCC')->bRequired  = true;
			$this->_object->getFieldByCode('ChiPhi')->bReadOnly = false;
			$this->_object->getFieldByCode('ChiPhi')->bRequired = true;
		}*/
		$thuchien = $this->_object->getFieldByCode('ThucHien')->getValue();
		if($thuchien == 1)
		{
			if(!$this->_object->getFieldByCode('ThoiGian')->getValue())
			{
				$this->_object->getFieldByCode('ThoiGian')->setValue($this->_object->getFieldByCode('ThoiGianDuKien')->getValue());
			}
			if(!$this->_object->getFieldByCode('NhanCong')->getValue())
			{
				$this->_object->getFieldByCode('NhanCong')->setValue($this->_object->getFieldByCode('NhanCongDuKien')->getValue());
			}
			if(!$this->_object->getFieldByCode('Ngay')->getValue())
			{
				$this->_object->getFieldByCode('Ngay')->setValue($this->_object->getFieldByCode('NgayDuKien')->getValue());
			}
			if(!$this->_object->getFieldByCode('GioBatDau')->getValue())
			{
				$this->_object->getFieldByCode('GioBatDau')->setValue($this->_object->getFieldByCode('GioBatDauDuKien')->getValue());
			}
			if(!$this->_object->getFieldByCode('GioKetThuc')->getValue())
			{
				$this->_object->getFieldByCode('GioKetThuc')->setValue($this->_object->getFieldByCode('GioKetThucDuKien')->getValue());
			}
		}
	}
 	public function ViTri()
    {
    	$this->_doFilter($this->_object->getFieldByCode('ViTri'));
    	$sql = sprintf('
            select OCauTrucThietBi.lft, OCauTrucThietBi.rgt, ODanhSachThietBi.IFID_M705 AS IFID
            from OPhieuBaoTri 
            inner join ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
            inner join OCauTrucThietBi ON ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                and ifnull(OPhieuBaoTri.Ref_BoPhan, 0) = OCauTrucThietBi.IOID
            WHERE OPhieuBaoTri.IFID_M759 = %1$d
        ', @(int)$this->_object->i_IFID);

			$dataSQL = $this->_db->fetchOne($sql);

			if($dataSQL)
			{
				$this->_object->getFieldByCode('ViTri')->arrFilters[] = sprintf(' v.IOID in (select IOID from OCauTrucThietBi where IFID_M705 = %1$d and lft >= %2$d and rgt <= %3$d )', $dataSQL->IFID, $dataSQL->lft, $dataSQL->rgt);
			}
    	
    }
}