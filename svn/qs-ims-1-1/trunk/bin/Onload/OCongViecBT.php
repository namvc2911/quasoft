<?php
class Qss_Bin_Onload_OCongViecBT extends Qss_Lib_Onload
{
	public function __doExecute()
	{
		parent::__doExecute();
		$thue_ngoai = $this->_object->getFieldByCode('ThueNgoai')->getValue();
		$this->_object->getFieldByCode('MaNCC')->bReadOnly = true;

		if($thue_ngoai)
		{
			$this->_object->getFieldByCode('MaNCC')->bReadOnly = false;
		}
	}
	public function ViTri()
	{
		$this->_doFilter($this->_object->getFieldByCode('ViTri'));
			$sql     = sprintf('
            select OCauTrucThietBi.lft, OCauTrucThietBi.rgt, ODanhSachThietBi.IFID_M705 AS IFID
            from OBaoTriDinhKy
            inner join ODanhSachThietBi ON OBaoTriDinhKy.Ref_MaThietBi = ODanhSachThietBi.IOID
            inner join OCauTrucThietBi ON ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                and ifnull(OBaoTriDinhKy.Ref_BoPhan, 0) = OCauTrucThietBi.IOID
            WHERE OBaoTriDinhKy.IFID_M724 = %1$d
        ', @(int)$this->_object->i_IFID);

		$dataSQL = $this->_db->fetchOne($sql);
		if($dataSQL)
		{
			$this->_object->getFieldByCode('ViTri')->arrFilters[] = sprintf(' v.IOID in (select IOID from OCauTrucThietBi where IFID_M705 = %1$d and lft >= %2$d and rgt <= %3$d) ', $dataSQL->IFID, $dataSQL->lft, $dataSQL->rgt);
		}
	}
}