<?php
class Qss_Bin_Calculate_ODanhSachCongViec_SoPhieu extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $object = new Qss_Model_Object();
        $object->v_fInit('ODanhSachCongViec', 'M852');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth(1);
        $document->setDocField('SoPhieu');
        $document->setPrefix('#');
        return $document->getDocumentNo();
    }
}
?>