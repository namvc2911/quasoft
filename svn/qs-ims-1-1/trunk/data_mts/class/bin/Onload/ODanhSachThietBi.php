<?php
class Qss_Bin_Onload_ODanhSachThietBi extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
        $this->_object->getFieldByCode('NgayNgung')->bRequired = false;
        $this->_object->getFieldByCode('NgayNgung')->bReadOnly = true;

		$trangThai = $this->_object->getFieldByCode('TrangThai')->getValue();

		if(in_array($trangThai, Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP))
		{
            $this->_object->getFieldByCode('NgayNgung')->bReadOnly = false;
			$this->_object->getFieldByCode('NgayNgung')->bRequired = true;
		}
        else
        {
            $this->_object->getFieldByCode('NgayNgung')->setValue('');
        }
	}
	public function MaKhuVuc()
	{
		$user = Qss_Register::get('userinfo');
		$this->_object->getFieldByCode('MaKhuVuc')->arrFilters[] = sprintf(' 
						v.IFID_M720 in(select IFID  from qsrecordrights  
			WHERE UID = %1$d)',$user->user_id);
	}	
	public function LoaiThietBi()
	{
		$nhom = $this->_object->getFieldByCode('NhomThietBi')->getRefIOID();
		$this->_object->getFieldByCode('LoaiThietBi')->arrFilters[] = sprintf(' 
						v.Ref_NhomThietBi = %1$d',$nhom);
	}
}