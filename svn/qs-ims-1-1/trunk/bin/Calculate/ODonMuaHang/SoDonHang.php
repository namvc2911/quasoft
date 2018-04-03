<?php
class Qss_Bin_Calculate_ODonMuaHang_SoDonHang extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(6);
        $document->setDocField('SoDonHang');
        $document->setPrefix('PO');

		return $document->getDocumentNo();
	}
}
?>