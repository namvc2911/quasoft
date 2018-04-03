<?php
class Qss_Model_Extra_Employees extends Qss_Model_Abstract
{
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}
	public function getLastEmployee()
	{
		$sql = 'select max(MaNhanVien)as MaNhanVien from ODanhSachNhanVien';
		return $this->_o_DB->fetchOne($sql);
	}
	public function getKyNang($ifid)
	{
		$sql = sprintf('select kn.KyNang, kn.SoNamKinhNghiem, uvtd.IOID from ODSUVDotTuyenDung as uvtd
						inner join ODanhSachUngVien as uv
						on uvtd.Ref_MaUngVien = uv.IOID
						inner join OKyNang as kn
						on uv.IFID_M331 = kn.IFID_M331
						where IFID_M332 = %1$d 
						
		', $ifid);
		return $this->_o_DB->fetchAll($sql);
		
	}
}