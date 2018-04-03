<?php
class Qss_Bin_Onload_OYeuCauVatTu extends Qss_Lib_Onload
{
    /**
     * onInsert
     */
    public function __doExecute()
    {
        parent::__doExecute();

        if(Qss_Lib_System::fieldActive('OYeuCauVatTu', 'KhongCoMa')) {
            $khongCoMa = @(int)$this->_object->getFieldByCode('KhongCoMa')->getValue();

            if($khongCoMa) {
                $this->_object->getFieldByCode('MaVatTu')->intInputType = 1; // Listbox
                $this->_object->getFieldByCode('MaVatTu')->bEditStatus  = true;

                $this->_object->getFieldByCode('TenVatTu')->intInputType = 1; // Listbox
                $this->_object->getFieldByCode('TenVatTu')->bEditStatus  = true;
                $this->_object->getFieldByCode('TenVatTu')->bReadOnly    = false;

                $this->_object->getFieldByCode('DonViTinh')->intInputType = 1; // Listbox
                $this->_object->getFieldByCode('DonViTinh')->bEditStatus  = true;
            }
            else {
                $this->_object->getFieldByCode('MaVatTu')->intInputType = 4; // Listbox
                $this->_object->getFieldByCode('MaVatTu')->bEditStatus  = true;

                $this->_object->getFieldByCode('TenVatTu')->intInputType = 3; // Listbox
                $this->_object->getFieldByCode('TenVatTu')->bEditStatus  = true;
                $this->_object->getFieldByCode('TenVatTu')->bReadOnly    = true;

                $this->_object->getFieldByCode('DonViTinh')->intInputType = 4; // Listbox
                $this->_object->getFieldByCode('DonViTinh')->bEditStatus  = true;
            }
        }
    }
}