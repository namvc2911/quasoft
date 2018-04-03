<?php
class Qss_Bin_Onload_ODanhSachDiemDo extends Qss_Lib_Onload
{
    /**
     * Dua vao can cu an hien chi so va ky bao duong
     */
    public function __doExecute()
    {
        parent::__doExecute();
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