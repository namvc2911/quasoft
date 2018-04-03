<?php
class Qss_Bin_Onload_OChiTieuPhieuKiemDinh extends Qss_Lib_Onload
{
    public function PhepKiemTra() {
        $SoHieuMau         = $this->_object->getFieldByCode('SoHieuMau')->getValue();
        $ifid              = (int)$this->_object->i_IFID;
        $arrPhepKiemTra    = array();
        $arrPhepKiemTra[]  = 0;

        $sql  = sprintf('SELECT Ref_PhepKiemTra FROM OPhepKiemTraPhieuKiemDinh WHERE IFID_M870 = %1$d AND SoHieuMau = %2$s
        ', $ifid, $this->_db->quote($SoHieuMau));
        $data = $this->_db->fetchAll($sql);

        // echo '<pre>'; print_r($sql); die;

        foreach ($data as $item) {
            $arrPhepKiemTra[] = $item->Ref_PhepKiemTra;
        }

        $this->_object->getFieldByCode('PhepKiemTra')->arrFilters[] = sprintf(' 
            v.IOID in (select IOID from ODanhMucPhepKiemTra  WHERE IOID IN (%1$s))
        ', implode(',', $arrPhepKiemTra));
    }

    public function ChiTieu() {
        $PhepKiemTra   = (int)$this->_object->getFieldByCode('PhepKiemTra')->getRefIOID();
        $arrChiTieu    = array();
        $arrChiTieu[]  = 0;

        // Phep kiem tra join voi phep kiem tra
        $sql  = sprintf('
              SELECT OChiTieuTheoPhepKiemTra.IOID
              FROM ODanhMucPhepKiemTra
              INNER JOIN OChiTieuTheoPhepKiemTra ON ODanhMucPhepKiemTra.IFID_M867 = OChiTieuTheoPhepKiemTra.IFID_M867
              WHERE ODanhMucPhepKiemTra.IOID = %1$d              
        ', @(int)$PhepKiemTra);

        // echo '<pre>'; print_r($sql); die;

        $data = $this->_db->fetchAll($sql);

        foreach ($data as $item) {
            $arrChiTieu[] = $item->IOID;
        }

        $this->_object->getFieldByCode('ChiTieu')->arrFilters[] = sprintf(' 
            v.IOID in (%1$s)
        ', implode(',', $arrChiTieu));

        // echo '<pre>'; print_r($this->_object->getFieldByCode('ChiTieu')->arrFilters); die;
    }
}