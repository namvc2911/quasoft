<?php
class Qss_Bin_Onload_OThaoTacVanHanh extends Qss_Lib_Onload
{
    /**
     * Dua vao can cu an hien chi so va ky bao duong
     */
    public function __doExecute()
    {
        parent::__doExecute();

        // Load nguoi giao viec khi an tao moi
        $mEmp  = new Qss_Model_Maintenance_Employee();
        $user  = Qss_Register::get('userinfo');
        $emp   = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đăng nhập

        if($emp
            && $emp->IOID
            && (int)$this->_object->i_IOID == 0
            && (int)$this->_object->getFieldByCode('NguoiGiaoViec')->getRefIOID() == 0) {
            $this->_object->getFieldByCode('NguoiGiao')->setValue($emp->MaNhanVien.' ('.$emp->TenNhanVien.')');
            $this->_object->getFieldByCode('NguoiGiao')->setRefIOID($emp->IOID);
        }

        // Load thu ngay thang theo chu ky
		$this->_object->getFieldByCode('Thu')->bReadOnly        = true;
        $this->_object->getFieldByCode('Ngay')->bReadOnly       = true;
        $this->_object->getFieldByCode('Thang')->bReadOnly      = true;

        $ky  = $this->_object->getFieldByCode('Ky')->getValue();

		switch ($ky)
		{
	        case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
	        	// Khong lam gi
	            break;
	
			case Qss_Lib_Extra_Const::PERIOD_TYPE_NON_RE_CURRING:
	        case '':
                    // Khong lam gi @todo: Cần bỏ kỳ S, kỳ đặc biệt đi vì trong kế hoạch sẽ là định kỳ hoặc chỉ số
                break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
            	$this->_object->getFieldByCode('Thu')->bReadOnly = false;
                $this->_object->getFieldByCode('Thu')->bRequired = true;
                break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
            	$this->_object->getFieldByCode('Ngay')->bReadOnly = false;
                $this->_object->getFieldByCode('Ngay')->bRequired = true;
                break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
				$this->_object->getFieldByCode('Ngay')->bReadOnly  = false;
                $this->_object->getFieldByCode('Thang')->bReadOnly = false;
                $this->_object->getFieldByCode('Ngay')->bRequired  = true;
                $this->_object->getFieldByCode('Thang')->bRequired = true;
                break;
		}
    }
}