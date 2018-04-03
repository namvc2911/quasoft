<?php
class Qss_Bin_Calculate_OYeuCauBaoTri_SoPhieu extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(3);
        $document->setDocField('SoPhieu');
        $document->setPrefix('RFM.{Y}.{m}.{d}.');

        return $document->getDocumentNo();
    }
}
?>