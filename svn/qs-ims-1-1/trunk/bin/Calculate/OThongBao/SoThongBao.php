<?php
class Qss_Bin_Calculate_OThongBao_SoThongBao extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(1);
        $document->setDocField('SoThongBao');
        $document->setPrefix('#');
        return $document->getDocumentNo();
    }
}
?>