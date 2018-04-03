<?php

/**
 * @author: ThinhTuan
 * @component: Production
 */
class Qss_Model_Production_Task extends Qss_Model_Abstract {

    public function __construct() {
        parent::__construct();
    }
    
    /// * Function: mergeLinesWithShifts - trong day chuyen voi ca
    public function getTaskByBarcode($barcode)
    {
        $sql = sprintf('select *
					from OPhieuGiaoViec
					inner join qsiforms on qsiforms.IFID = OPhieuGiaoViec.IFID_M712
					where Barcode = %1$s',
        		$this->_o_DB->quote($barcode));
        return $this->_o_DB->fetchOne($sql);
    }
}

?>