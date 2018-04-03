<?php
class Qss_Bin_Calculate_OChiSoCongToMuaVao_ChiSoDauCaoDiem extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $metterCode = $this->MaCongTo(1)?$this->MaCongTo(1):'';

        $sql = sprintf('
        SELECT *
        FROM OChiSoCongToMuaVao
        INNER JOIN OQuanLyCongToMuaVao ON OChiSoCongToMuaVao.Ref_MaCongTo = OQuanLyCongToMuaVao.IOID
        WHERE OQuanLyCongToMuaVao.MaCongTo = %1$s
        ORDER BY OChiSoCongToMuaVao.Nam DESC, OChiSoCongToMuaVao.Thang DESC, OChiSoCongToMuaVao.Ky DESC
        LIMIT 1
        ', $this->_db->quote($metterCode));

        $dataSql = $this->_db->fetchOne($sql);

        return $dataSql?$dataSql->ChiSoCuoiCaoDiem:0;
    }
}
?>