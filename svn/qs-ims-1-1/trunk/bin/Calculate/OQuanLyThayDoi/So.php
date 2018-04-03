<?php
class Qss_Bin_Calculate_OQuanLyThayDoi_So extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
//        $mWorkorder = new Qss_Model_Maintenance_Workorder();
//        return $mWorkorder->getDocNo();
        $document = new Qss_Model_Extra_Document($this->_object);
//        $document->setLenth(3);
//        $document->setDocField('SoPhieu');
//        $document->setPrefix('#');

        return $document->getDocumentNo();
	}
}
?>