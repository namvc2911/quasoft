<?php
class Qss_Bin_Calculate_OLichThietBi_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document = new Qss_Model_Extra_Document($this->_object);
        return $document->getDocumentNo();
	}
}
?>