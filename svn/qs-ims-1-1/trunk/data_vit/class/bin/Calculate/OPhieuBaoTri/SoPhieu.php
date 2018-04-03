<?php
class Qss_Bin_Calculate_OPhieuBaoTri_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document = new Qss_Model_Extra_Document($this->_object);
        if(count($this->_object->a_Fields))
        {
        	$document->setPrefix($this->_object->getFieldByCode('MaDVBT')->getValue().'.#');
        }
        return $document->getDocumentNo();
	}
}
?>