<?php
class Qss_Bin_Onload_OYeuCauTrangThietBi extends Qss_Lib_Onload {
    public function __doExecute() {
        parent::__doExecute();

        $mCommon = new Qss_Model_Extra_Extra();
        $ifid    = $this->_object->i_IFID;
        $main    = $mCommon->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IFID_M751'=>$ifid));

        // Khi chưa save lại thì load ra ngày bắt đầu và kết thúc theo đối tượng chính
        if(!$this->_object->i_IOID && $main) {
            $this->_object->getFieldByCode('NgayBatDau')->setValue($main->NgayBatDau);
            $this->_object->getFieldByCode('NgayKetThuc')->setValue($main->NgayKetThuc);
        }

        // Khi tick vào không có mã thì nhập loại thiết bị, đơn vị tính tự do
        $khongCoMa = @(int)$this->_object->getFieldByCode('KhongCoMa')->getValue();

        if($khongCoMa) {
            $this->_object->getFieldByCode('LoaiThietBi')->intInputType = 1; // Listbox
            $this->_object->getFieldByCode('LoaiThietBi')->bEditStatus = true;

            $this->_object->getFieldByCode('DonViTinh')->intInputType = 1; // Listbox
            $this->_object->getFieldByCode('DonViTinh')->bEditStatus  = true;
            $this->_object->getFieldByCode('DonViTinh')->bReadOnly    = false;
        }
        else {
            $this->_object->getFieldByCode('LoaiThietBi')->intInputType = 4; // Listbox
            $this->_object->getFieldByCode('LoaiThietBi')->bEditStatus = true;

            $this->_object->getFieldByCode('DonViTinh')->intInputType = 4; // Listbox
            $this->_object->getFieldByCode('DonViTinh')->bEditStatus  = true;
            $this->_object->getFieldByCode('DonViTinh')->bReadOnly    = true;
        }
    }
}