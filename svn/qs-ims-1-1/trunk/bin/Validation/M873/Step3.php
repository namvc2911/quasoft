<?php

class Qss_Bin_Validation_M873_Step3 extends Qss_Lib_WValidation
{
    /**
     * Cập nhật lại dự án vào trong danh sách thiết bị
     */
    public function next()
    {
        parent::init();
        $insert  = array();
        $mImport = new Qss_Model_Import_Form('M705',false, false);

        foreach($this->_params->OThietBiDieuDongVe AS $item) {

            if($item->Ref_MaThietBi)
            {
                $insert = array();
                $insert['ODanhSachThietBi'][0]['MaThietBi']    = $item->MaThietBi;
                $insert['ODanhSachThietBi'][0]['DuAn']         = '';
                $insert['ODanhSachThietBi'][0]['Ref_DuAn']     = (int)0;
                $insert['ODanhSachThietBi'][0]['Loai']         = 3;

                $mImport->setData($insert);
            }
        }

        if(count($insert)) {
            $mImport->generateSQL();
        }
    }
}