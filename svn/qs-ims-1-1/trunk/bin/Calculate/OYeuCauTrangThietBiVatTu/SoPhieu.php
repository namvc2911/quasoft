<?php
class Qss_Bin_Calculate_OYeuCauTrangThietBiVatTu_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(6);
        $document->setDocField('SoPhieu');

        $value = null;
        $pre   = 'YCCC/{y}/';

        foreach ($this->_object->a_Fields as $field)
        {
            if ( $field->FieldCode == 'DonViYeuCau' )
            {
                $value = $field->getValue();
                break;
            }
        }

        if($value)
        {
            $document->setPrefix($value.'.'.$pre.'.');
        }
        else
        {
            $document->setPrefix($pre.'.');
        }

        return $document->getDocumentNo();
	}
}
?>