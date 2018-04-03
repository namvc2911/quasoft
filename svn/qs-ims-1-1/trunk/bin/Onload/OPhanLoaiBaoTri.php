<?php
class Qss_Bin_Onload_OPhanLoaiBaoTri extends Qss_Lib_Onload
{
    /**
     * Dua vao can cu an hien chi so va ky bao duong
     */
    public function __doExecute()
    {
        parent::__doExecute();
//        $this->_object->getFieldByCode('CanCu')->bReadOnly = true;
//        $this->_object->getFieldByCode('LapLai')->bReadOnly = true;
//		$this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = true;
//        $this->_object->getFieldByCode('ChiSo')->bReadOnly      = true;
//        $this->_object->getFieldByCode('GiaTri')->bReadOnly     = true;
//
//        if($this->_object->getFieldByCode('LoaiBaoTri')->getValue() == 'P')
//        {
//            // Tinh theo dinh KyBaoDuong
//            $this->_object->getFieldByCode('CanCu')->bReadOnly = false;
//            $this->_object->getFieldByCode('LapLai')->bReadOnly = false;
//            $canCu = $this->_object->getFieldByCode('CanCu')->getValue();
//            if($canCu == Qss_Lib_Extra_Const::CAUSE_PERIOD)
//            {
//                $this->_object->getFieldByCode('ChiSo')->bReadOnly      = true;
//                $this->_object->getFieldByCode('GiaTri')->bReadOnly     = true;
//                $this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = false;
//                $this->_object->getFieldByCode('KyBaoDuong')->bRequired = true;
//            }
//
//            // Tinh theo chi so
//            if($canCu == Qss_Lib_Extra_Const::CAUSE_PARAM)
//            {
//                $this->_object->getFieldByCode('ChiSo')->bReadOnly      = false;
//                $this->_object->getFieldByCode('ChiSo')->bRequired      = true;
//                $this->_object->getFieldByCode('GiaTri')->bReadOnly     = false;
//                $this->_object->getFieldByCode('GiaTri')->bRequired     = true;
//                $this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = true;
//                $this->_object->getFieldByCode('KyBaoDuong')->bRequired = false;
//            }
//
//            // Tinh theo ca chi so lan ky
//            if($canCu == Qss_Lib_Extra_Const::CAUSE_BOTH)
//            {
//                $this->_object->getFieldByCode('ChiSo')->bReadOnly      = false;
//                $this->_object->getFieldByCode('ChiSo')->bRequired      = true;
//                $this->_object->getFieldByCode('GiaTri')->bReadOnly     = false;
//                $this->_object->getFieldByCode('GiaTri')->bRequired     = true;
//                $this->_object->getFieldByCode('KyBaoDuong')->bReadOnly = false;
//                $this->_object->getFieldByCode('KyBaoDuong')->bRequired = true;
//            }
//        }
//        else
//        {
//        	if($this->_object->getFieldByCode('KyBaoDuong')->getRefIOID())
//			{
//	        	$this->_object->getFieldByCode('KyBaoDuong')->setValue('');
//	        	$this->_object->getFieldByCode('KyBaoDuong')->setRefIOID(0);
//			}
//        }
    }
}