<?php
class Qss_Bin_Calculate_MNotify_SoThongBao extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(3);
        $document->setDocField('SoThongBao');
        $document->setPrefix('NOTIFY.'.date('Y').'.'.date('m').'.'.date('d').'.');
        return $document->getDocumentNo();
    }
}
?>