<?php
class Qss_Bin_Calculate_ONhapKho_SoChungTu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(3);
        $document->setDocField('SoChungTu');
        $document->setPrefix('NK.{Y}{m}{d}.');

        return $document->getDocumentNo();
	}
}
?>