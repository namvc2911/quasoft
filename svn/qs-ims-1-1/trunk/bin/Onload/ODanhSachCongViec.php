<?php
class Qss_Bin_Onload_ODanhSachCongViec extends Qss_Lib_Onload
{
    public function __doExecute()
    {
		parent::__doExecute();
        $user = Qss_Register::get('userinfo');

        $mTable = Qss_Model_Db::Table('ODanhSachNhanVien');
        $mTable->select('ODanhSachNhanVien.*');
        $mTable->join('INNER JOIN qsusers ON qsusers.UID = IFNULL(ODanhSachNhanVien.Ref_TenTruyCap, 0)');
        $mTable->where(sprintf('qsusers.UID = %1$d', @(int)$user->user_id));
        $user2 = $mTable->fetchOne();

        $this->_object->getFieldByCode('SoLuong')->bReadOnly   = true;
        $this->_object->getFieldByCode('SoLuong')->bRequired   = false;
        $this->_object->getFieldByCode('DonViTinh')->bReadOnly   = true;
        $this->_object->getFieldByCode('DonViTinh')->bRequired   = false;

        if((int)$this->_object->getFieldByCode('KieuBaoCao')->getValue() == 3)
        {
            $this->_object->getFieldByCode('SoLuong')->bReadOnly   = false;
            $this->_object->getFieldByCode('SoLuong')->bRequired   = true;
            $this->_object->getFieldByCode('DonViTinh')->bReadOnly = false;
            $this->_object->getFieldByCode('DonViTinh')->bRequired = true;
        }

        if(!$this->_object->getFieldByCode('NguoiTao')->getValue() && $user2 && !$this->_object->i_IFID)
        {
            $this->_object->getFieldByCode('NguoiTao')->setValue("{$user2->TenNhanVien} ({$user2->MaNhanVien})");
            $this->_object->getFieldByCode('NguoiTao')->setRefIOID($user2->IOID);
        }
    }

    public function DuAn()
    {
		if(Qss_Lib_System::formSecure('M803'))
		{
			$user   = Qss_Register::get('userinfo');
			$this->_object->getFieldByCode('DuAn')->arrFilters[] = sprintf(' v.IFID_M803 in (SELECT IFID FROM 
						qsrecordrights WHERE FormCode = "M803" and UID = %1$d)'
				,$user->user_id);
		}
	}
	public function GiaoCho()
    {
		$this->_object->getFieldByCode('GiaoCho')->arrFilters[] = sprintf(' ifnull(v.ThoiViec,0) = 0');
	}

}