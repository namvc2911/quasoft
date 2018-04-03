<?php
class Qss_Bin_Trigger_OChuKyBaoTri extends Qss_Lib_Trigger
{
    public function onInsert($object) {
        parent::init();
        $this->alertKhongHoTroDieuChinhTheoNhieuChuKyKhiThemMoi($object);
    }

    public function onUpdate($object) {
        parent::init();
        $this->alertKhongHoTroDieuChinhTheoNhieuChuKyKhiCapNhat($object);
    }

    public function onInserted($object) {
        parent::init();
        $this->updateDieuChinhTheoPhieuVeKhong($object);
    }

    public function onUpdated($object) {
        parent::init();
        $this->updateDieuChinhTheoPhieuVeKhong($object);
    }

    public function alertKhongHoTroDieuChinhTheoNhieuChuKyKhiThemMoi(Qss_Model_Object $object) {
        $ifid  = $object->i_IFID;
        $mM724 = new Qss_Model_M724_Period();
        $count = $mM724->countPeriodByIFID($ifid);
        $dieuChinhTheoPBT = (int)$object->getFieldByCode('DieuChinhTheoPBT')->getValue();

        if($count >= 1 && $dieuChinhTheoPBT) {
            $this->setError();
            $this->setMessage('Không hỗ trợ điều chỉnh theo phiếu bảo trì với trường hợp nhiều chu kỳ!');
        }
    }

    public function alertKhongHoTroDieuChinhTheoNhieuChuKyKhiCapNhat(Qss_Model_Object $object) {
        $ifid  = $object->i_IFID;
        $mM724 = new Qss_Model_M724_Period();
        $count = $mM724->countPeriodByIFID($ifid);
        $dieuChinhTheoPBT = (int)$object->getFieldByCode('DieuChinhTheoPBT')->getValue();

        if($count > 1 && $dieuChinhTheoPBT) {
            $this->setError();
            $this->setMessage('Không hỗ trợ điều chỉnh theo phiếu bảo trì với trường hợp nhiều chu kỳ!');
        }
    }

    public function updateDieuChinhTheoPhieuVeKhong(Qss_Model_Object $object) {
        $ifid  = $object->i_IFID;
        $ifids = array($ifid);
        $mM724 = new Qss_Model_M724_Period();
        $count = $mM724->countPeriodByIFID($ifid);

        if($count > 1) {
            $mM724->updateDieuChinhTheoPhieuVeKhongByIFIDs($ifids);
        }
    }
}