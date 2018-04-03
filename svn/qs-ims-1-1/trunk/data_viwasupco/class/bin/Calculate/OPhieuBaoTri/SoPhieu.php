<?php
class Qss_Bin_Calculate_OPhieuBaoTri_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document   = new Qss_Model_Extra_Document($this->_object);
        $loaiBaoTri = @(int)$this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID();

        $mTable = Qss_Model_Db::Table('OPhanLoaiBaoTri');
        $mTable->where(sprintf('IOID = %1$d', $loaiBaoTri));
        $objLoaiBaoTri = $mTable->fetchOne();

        if(count($this->_object->a_Fields) && $objLoaiBaoTri)
        {
        	$date  = date('m');
        	$year  = date('Y');
        	if($objLoaiBaoTri->LoaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
        		$document->setPrefix("bd.{$date}.{$year}.");
        	else
        		$document->setPrefix("sc.{$date}.{$year}.");
        }
        return $document->getDocumentNo();
	}
}
?>