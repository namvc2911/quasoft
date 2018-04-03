<?php

class Qss_Bin_Validation_M706_Step3 extends Qss_Lib_WValidation
{
	/**
	 * Cập nhật lại dự án vào trong danh sách thiết bị
	 */
	public function next()
	{
		parent::init();
        $insert    = array();
        $common    = new Qss_Model_Extra_Extra();
        $refYeuCau = (int)$this->_params->Ref_PhieuYeuCau;
        $objYeuCau = $common->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IOID'=>$refYeuCau));
        $mImport   = new Qss_Model_Import_Form('M705',false, false);

        if($objYeuCau) {
            foreach($this->_params->ODanhSachDieuDongThietBi AS $item) {
                $insert = array();
                $insert['ODanhSachThietBi'][0]['MaThietBi']    = $item->MaThietBi;
                $insert['ODanhSachThietBi'][0]['DuAn']         = (int)$objYeuCau->Ref_DuAn;
                // $insert['ODanhSachThietBi'][0]['MaKhuVuc']     = (int)$this->_params->Ref_MaKhuVuc;
                // $insert['ODanhSachThietBi'][0]['ioid']         = (int)$item->Ref_MaThietBi;

                //echo '<pre>'; print_r($insert);
                $mImport->setData($insert);
            }
        }

        if(count($insert)) {
            $mImport->generateSQL();
        }
	}
}