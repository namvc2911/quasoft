<?php
class Qss_Bin_Calculate_OChiSoCongToDienNoiBo_ChiSoDau extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $metterCode = $this->MaCongTo(1)?$this->MaCongTo(1):'';

        $sql = sprintf('
        SELECT *
        FROM OChiSoCongToDienNoiBo
        INNER JOIN OCongToDien ON OChiSoCongToDienNoiBo.Ref_MaCongTo = OCongToDien.IOID
        WHERE OCongToDien.Ma = %1$s
        ORDER BY OChiSoCongToDienNoiBo.Nam DESC, OChiSoCongToDienNoiBo.Thang DESC
        LIMIT 1
        ', $this->_db->quote($metterCode));
        

        $dataSql = $this->_db->fetchOne($sql);

        return $dataSql?$dataSql->ChiSoCuoi:0;
    }
}
?>