<?php
class Qss_Bin_Calculate_OPhieuThuHoiTaiSan_SoPhieu extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $document = new Qss_Model_Extra_Document($this->_object);
        $document->setLenth(5);
        $document->setDocField('SoPhieu');

        $value = null;

        foreach ($this->_object->a_Fields as $field)
        {
            if ( $field->FieldCode == 'NhaMay' )
            {
                $value = $field->getValue();
                break;
            }
        }

        if($value)
        {
            $document->setPrefix($value.'TH.');
        }
        else
        {
            $document->setPrefix('TH.');
        }

        return $document->getDocumentNo();
    }
}
?>