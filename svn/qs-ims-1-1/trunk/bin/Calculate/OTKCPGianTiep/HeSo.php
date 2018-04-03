
<?php
class Qss_Bin_Calculate_OThuTien_HeSo extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$name = $this->TenChiPhi;
		$masp = $this->MaSP;
		$thuoctinh = $this->ThuocTinh;
		$sql = sprintf('select * from OCauThanhSanPham as ct
						inner join OChiPhiGianTiep gt on ct.IFID_M114 = gt.IFID_M114
						where ct.MaSanPham = %1$s and ct.ThuocTinh = %2$s and gt.TenChiPhi = %3$s',
						$this->_db->quote($masp),
						$this->_db->quote($thuoctinh),
						$this->_db->quote($name));
		$dataSQL = $this->_db->fetchOne($sql);
		if($dataSQL)
		{
			return $dataSQL->HeSo; 
		}
		return 1;
	}
}
?>