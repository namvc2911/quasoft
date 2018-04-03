<?php
class Qss_Bin_Calculate_ONhapKho_SoChungTu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
//		$kho = $this->Kho(1);
//		if($kho != '' && $this->Kho(1) !== 'CTY')
//		{
//			return Qss_Lib_Extra::getDocumentNo($this->_object, sprintf(' and Kho = %1$s',$this->_db->quote($kho)));
//		}

        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(5);
        $document->setDocField('SoChungTu');
        $document->setPrefix('NK');

        return $document->getDocumentNo();
	}
}
?>