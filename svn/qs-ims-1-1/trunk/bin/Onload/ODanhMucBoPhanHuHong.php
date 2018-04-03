<?php
class Qss_Bin_Onload_ODanhMucBoPhanHuHong extends Qss_Lib_Onload
{
	public function __doExecute()
	{
		parent::__doExecute();
	}
	public function LoaiHuHong()
	{
		$this->_object->getFieldByCode('LoaiHuHong')->arrFilters[] =
		sprintf('
                (
                    v.IOID in (
                        SELECT OLoaiHuHong.IOID
                        FROM OPhieuBaoTri
                        INNER JOIN ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                        INNER JOIN OClassHuHong AS ClassThietBi ON ODanhSachThietBi.Ref_ClassHuHong = ClassThietBi.IOID
                        INNER JOIN OLoaiHuHong ON ClassThietBi.IFID_M172 = OLoaiHuHong.IFID_M172
                        WHERE OPhieuBaoTri.IFID_M759 = %1$d AND %2$d = 0
                    )
                    OR
                    v.IOID in (
                        SELECT OLoaiHuHong.IOID
                        FROM OCauTrucThietBi 
                        INNER JOIN OClassHuHong AS ClassCauTru  ON OCauTrucThietBi.Ref_ClassHuHong = ClassCauTru.IOID
                        INNER JOIN OLoaiHuHong ON ClassCauTru.IFID_M172 = OLoaiHuHong.IFID_M172
                        WHERE OCauTrucThietBi.IOID = %2$d
                    )
                )
            ', $this->_object->i_IFID, $this->_object->getFieldByCode('BoPhan')->getRefIOID());
	}
	public function NguyenNhan()
	{
		$this->_object->getFieldByCode('NguyenNhan')->arrFilters[] =
		sprintf('
                v.IOID in (
                    SELECT IOID
                    FROM ONguyenNhanHuHong
                    WHERE Ref_MaLoai = %1$d
                )
            ', $this->_object->getFieldByCode('LoaiHuHong')->getRefIOID());
	}
	/*public function BienPhapKhacPhuc()
	{
		$this->_object->getFieldByCode('BienPhapKhacPhuc')->arrFilters[] =
		sprintf('
                v.IOID in (
                    SELECT IOID
                    FROM OBienPhapXuLy
                    WHERE Ref_MaNguyenNhan = %1$d
                )
            ', $this->_object->getFieldByCode('NguyenNhan')->getRefIOID());
	}*/
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
			$this->_object->getFieldByCode('ViTri')->arrFilters[] = sprintf(' (v.IFID_M705 = %1$d and lft >= %2$d and rgt <= %3$d )', $dataSQL->IFID, $dataSQL->lft, $dataSQL->rgt);
		}
	}
}