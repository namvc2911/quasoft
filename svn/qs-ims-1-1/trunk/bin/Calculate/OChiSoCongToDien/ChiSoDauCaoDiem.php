<?php
class Qss_Bin_Calculate_OChiSoCongToDien_ChiSoDauCaoDiem extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $metterCode = $this->MaCongTo(1)?$this->MaCongTo(1):'';

        $sql = sprintf('
        SELECT *
        FROM OChiSoCongToDien
        INNER JOIN OCongToDien ON OChiSoCongToDien.Ref_MaCongTo = OCongToDien.IOID
        WHERE OCongToDien.Ma = %1$s
        ORDER BY OChiSoCongToDien.Nam DESC, OChiSoCongToDien.Thang DESC, OChiSoCongToDien.Ky DESC
        LIMIT 1
        ', $this->_db->quote($metterCode));

        $dataSql = $this->_db->fetchOne($sql);

        return $dataSql?$dataSql->ChiSoCuoiCaoDiem:0;
    }
}
?>