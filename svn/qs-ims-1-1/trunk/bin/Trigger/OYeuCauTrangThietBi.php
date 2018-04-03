<?php
class Qss_Bin_Trigger_OYeuCauTrangThietBi extends Qss_Lib_Trigger
{
    public function onInsert($object)
    {
        parent::init();
        $this->compareQty($object);
    }

    public function onUpdate($object)
    {
        parent::init();
        $this->compareQty($object);
    }

    private function compareQty(Qss_Model_Object $object) {
        $soLuongYeuCau   = $object->getFieldByCode('SoLuong')->getValue();
        $soLuongYeuCau   = $soLuongYeuCau?$soLuongYeuCau:0;
        $soLuongDieuDong = $object->getFieldByCode('SoLuongDieuDong')->getValue();
        $soLuongDieuDong = $soLuongDieuDong?$soLuongDieuDong:0;
        $soLuongMua      = $object->getFieldByCode('SoLuongMua')->getValue();
        $soLuongMua      = $soLuongMua?$soLuongMua:0;
        $soLuongThue     = $object->getFieldByCode('SoLuongThue')->getValue();
        $soLuongThue     = $soLuongThue?$soLuongThue:0;

        $tongMuaThueDieuDong = $soLuongDieuDong + $soLuongMua + $soLuongThue;

        if($soLuongYeuCau < $tongMuaThueDieuDong) {
            $this->setError();
            $this->setMessage('Tổng số lượng điều động, mua và thuê lớn hơn số lượng yêu cầu');
        }
    }
}