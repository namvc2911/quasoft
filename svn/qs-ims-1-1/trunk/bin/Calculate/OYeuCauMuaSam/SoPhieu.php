<?php
class Qss_Bin_Calculate_OYeuCauMuaSam_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(3);
        $document->setDocField('SoPhieu');
        $document->setPrefix('RFP.{Y}.{m}.{d}.');

        return $document->getDocumentNo();
	}
}
?>