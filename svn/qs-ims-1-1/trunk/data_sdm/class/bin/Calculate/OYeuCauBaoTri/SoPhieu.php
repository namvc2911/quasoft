<?php
class Qss_Bin_Calculate_OYeuCauBaoTri_SoPhieu extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(2);
        $document->setDocField('SoPhieu');
        $document->setPrefix('{Y}/{m}/{d}_');

        return $document->getDocumentNo();
    }
}
?>