<?php
class Qss_Model_Extra_BOM extends Qss_Model_Abstract
{
	//-----------------------------------------------------------------------
	/**
	 * Build constructor
	 *'
	 * @return void
	 */
	public $_common;
	public function __construct ()
	{
		parent::__construct();
		$this->_common = new Qss_Model_Extra_Extra;
	}


	public function getBomById($ioid) 
	{
		$sql = sprintf('select *
						from OCauThanhSanPham
						where IOID = %1$d',$ioid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getOperations($ioid)
	{
		$sql = sprintf('select * from OCongDoanBOM
					inner join OCauThanhSanPham on OCauThanhSanPham.IFID_M114 = OCongDoanBOM.IFID_M114
					where OCauThanhSanPham.IOID = %1$d',
				$ioid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getLabors($ioid)
	{
		$sql = sprintf('select OChiPhiNhanCong.* from OChiPhiNhanCong
					inner join OCauThanhSanPham on OCauThanhSanPham.IFID_M114 = OChiPhiNhanCong.IFID_M114
					where OCauThanhSanPham.IOID = %1$d',
				$ioid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getMachines($ioid)
	{
		$sql = sprintf('select * from OChiPhiMayMoc
					inner join OCauThanhSanPham on OCauThanhSanPham.IFID_M114 = OChiPhiMayMoc.IFID_M114
					where OCauThanhSanPham.IOID = %1$d',
				$ioid);
		return $this->_o_DB->fetchAll($sql);
	}

}
?>