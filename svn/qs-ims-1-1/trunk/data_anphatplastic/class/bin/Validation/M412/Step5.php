<?php
class Qss_Bin_Validation_M412_Step5 extends Qss_Lib_Warehouse_WValidation
{
    public function onNext()
    {
        parent::init();

        $mKiemTraMaTam = Qss_Model_Db::Table('ODSYeuCauMuaSam');
        $mKiemTraMaTam->join('INNER JOIN OSanPham ON ODSYeuCauMuaSam.Ref_MaSP = OSanPham.IOID');
        $mKiemTraMaTam->where($mKiemTraMaTam->ifnullNumber('OSanPham.MaTam', 1));
        $mKiemTraMaTam->display(1);
        $kiemTraMaTam = $mKiemTraMaTam->fetchOne();

        if($kiemTraMaTam) {
            $this->setError();
            $this->setMessage('Chưa thay đổi hết mã tạm!');
        }
    }

}
