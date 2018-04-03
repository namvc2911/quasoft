<?php
class Qss_Bin_Calculate_ODanhSachThietBi_LyLich extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $tenThietBi = $this->_object->getFieldByCode('TenThietBi')->getValue();
        $maThietBi  = $this->_object->getFieldByCode('MaThietBi')->getValue();

        if(!$tenThietBi && !$maThietBi)
        {
            $sql = $this->_db->fetchOne(sprintf('SELECT * FROM ODanhSachThietBi WHERE IFID_M705 = %1$d ', $this->_object->i_IFID));

            if($sql) {
                $tenThietBi = $sql->TenThietBi;
                $maThietBi = $sql->MaThietBi;
            }

        }

        $ioid  = $this->_object->i_IOID;
        $name  = "{$tenThietBi} - {$maThietBi}";
        $start = date('01-01-Y');
        $end   = date('31-12-Y');

        return "<a href='/static/m778?eq={$ioid}&start={$start}&end={$end}&location=0&type=0&group=0&costcenter=0&eq_tag={$name}'> Xem LL </a>";
    }
}
?>