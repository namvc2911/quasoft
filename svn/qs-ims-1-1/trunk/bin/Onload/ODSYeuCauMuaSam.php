<?php
class Qss_Bin_Onload_ODSYeuCauMuaSam extends Qss_Lib_Onload
{
    /**
     * onInsert
     */
    public function __doExecute()
    {
        parent::__doExecute();

        // Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $item = (int)$this->_object->getFieldByCode('MaSP')->getRefIOID();

            if(!$item)
            {
                $mTable = Qss_Model_Db::Table('ODSYeuCauMuaSam');
                $mTable->where(sprintf('IOID = %1$d', $this->_object->i_IOID));
                $data = $mTable->fetchOne();

                if($data)
                {
                    $item = (int)$data->Ref_MaSP;
                }
            }

            $this->_object->getFieldByCode('TenSP')->bReadOnly = true;

            $mItem = Qss_Model_Db::Table('OSanPham');
            $mItem->where(sprintf('IOID = %1$d', $item));
            $oItem = $mItem->fetchOne();

            if($oItem)
            {
                if($oItem->MaTam)
                {
                    $this->_object->getFieldByCode('TenSP')->bReadOnly = false;
                }
                else
                {
                    $this->_object->getFieldByCode('TenSP')->setValue($oItem->TenSanPham);
                }
            }
        }
    }
}