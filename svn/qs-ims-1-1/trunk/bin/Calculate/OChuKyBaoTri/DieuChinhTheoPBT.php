<?php
class Qss_Bin_Calculate_OChuKyBaoTri_DieuChinhTheoPBT extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        // Tick sẵn điều chỉnh theo phiếu bảo trì
        $retval = 1;

        if(!$this->_object->i_IOID) {
            $mM724 = new Qss_Model_M724_Period();
            $count = $mM724->countPeriodByIFID($this->_object->i_IFID);

            if($count >= 1) {
                $retval = 0;
            }
        }

        return $retval;
    }
}

