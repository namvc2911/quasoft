<?php
/**
 * @author: Huy.Bui
 * @component: Cac mau in su co thiet bi
 */
class Print_M759Controller extends Qss_Lib_PrintController
{
	public function init()
	{
		parent::init();
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
	}

    /**
     * In bien ban su co
     */
    public function documentAction()
    {
        $this->html->data = $this->_params;
        $this->html->status = $this->_form->i_Status;
    }

    // --------------------------------- MẪU IN VIWASUPCO ---------------------------------
    /**
     * In bien ban su co
     */
    public function viwasupcoDocumentAction()
    {
        $this->html->data = $this->_params;
        $this->html->status = $this->_form->i_Status;
    }

    public function viwasupcoBreakdownAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $oYeuCau  = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>@(int)$this->_params->Ref_PhieuYeuCau));
        $oPhieuBT = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFID_M759'=>@(int)$this->_params->IFID_M759));

        $this->html->SoPhieu    = @$oPhieuBT->SoPhieu;
        $this->html->MoTa       = @$oYeuCau->MoTa;
        $this->html->NguyenNhan = @$oYeuCau->NguyenNhan;
        $this->html->KetLuan    = @$oYeuCau->KetLuan;
        $this->html->ifid       = @(int)$this->_params->IFID_M759;
        $this->html->MaThietBi    = @$oYeuCau->MaThietBi;
        $this->html->TenThietBi    = @$oYeuCau->TenThietBi;
        $this->html->MaKhuVuc    = @$oYeuCau->MaKhuVuc;
    }

    public function viwasupcoBreakdown2Action()
    {
        $mCommon = new Qss_Model_Extra_Extra();
        $oYeuCau = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>@(int)$this->_params->Ref_PhieuYeuCau));
        $oKhuVuc = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>@(int)$this->_params->Ref_MaKhuVuc));
        $iform   = $mCommon->getTableFetchOne('qsiforms', array('IFID'=>@(int)$this->_params->IFID_M759));
        $sDate   = $this->_params->NgayYeuCau?$this->_params->NgayYeuCau.' '.@$this->_params->GioYeuCau:'';

        $this->html->StartDate        = $sDate?date_create($sDate):'';
        $this->html->StartTime        = (@$this->_params->GioYeuCau && $this->_params->NgayYeuCau);
        $this->html->OKhuVuc          = $oKhuVuc;
        $this->html->OYeuCauBaoTri    = $oYeuCau;
        $this->html->OPhieuBaoTri     = $this->_params;
        $this->html->IForm            = $iform;
    }

    public function viwasupcoBreakdownexcelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản sự cố.xlsx\"");

        $mCommon = new Qss_Model_Extra_Extra();
        $oYeuCau = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>@(int)$this->_params->Ref_PhieuYeuCau));
        $oPhieuBT = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFID_M759'=>@(int)$this->_params->IFID_M759));

        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M759', 'Viwasupco_BienBanSuCo.xlsx'));
        $main = new stdClass();

        $main->SoPhieu    = @$oPhieuBT->SoPhieu;
        $main->MoTa       = @$oYeuCau->MoTa;
        $main->NguyenNhan = @$oYeuCau->NguyenNhan;
        $main->KetLuan    = @$oYeuCau->KetLuan;

        $file->init(array('m' => $main));

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function viwasupcoAcceptanceAction()
    {
        $mCommon = new Qss_Model_Extra_Extra();
        $oYeuCau = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFID_M759'=>@(int)$this->_params->IFID_M759));

        $this->html->SoPhieu          = @$oYeuCau->SoPhieu;
        $this->html->NoiDung          = @$oYeuCau->NoiDung;
        $this->html->NhanXetChatLuong = @$oYeuCau->NhanXetChatLuong;
        $this->html->KetLuan          = @$oYeuCau->KetLuan;
        $this->html->Nam              = date('Y');
        $this->html->ifid             = @(int)$this->_params->IFID_M759;
    }

    public function viwasupcoAcceptance2Action()
    {
        $mCommon = new Qss_Model_Extra_Extra();
        $iform   = $mCommon->getTableFetchOne('qsiforms', array('IFID'=>@(int)$this->_params->IFID_M759));
        $oKhuVuc = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>@(int)$this->_params->Ref_MaKhuVuc));
        $oYeuCau = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>@(int)$this->_params->Ref_PhieuYeuCau));
        $oVatTu  = $mCommon->getTableFetchAll('OVatTuPBT', array('IFID_M759'=>@(int)$this->_params->IFID_M759));
        $sDate   = $this->_params->NgayBatDau?$this->_params->NgayBatDau.' '.$this->_params->GioBatDau:'';
        $eDate   = $this->_params->Ngay?$this->_params->Ngay.' '.$this->_params->GioKetThuc:'';

        $this->html->StartDate        = $sDate?date_create($sDate):'';
        $this->html->StartTime        = ($this->_params->NgayBatDau && $this->_params->GioBatDau);
        $this->html->EndDate          = $eDate?date_create($eDate):'';
        $this->html->EndTime          = ($this->_params->Ngay && $this->_params->GioKetThuc);
        $this->html->OYeuCauBaoTri    = $oYeuCau;
        $this->html->OKhuVuc          = $oKhuVuc;
        $this->html->OPhieuBaoTri     = $this->_params;
        $this->html->OVatTu           = $oVatTu;
        $this->html->IForm            = $iform;
    }

    public function viwasupcoAcceptance3Action()
    {
        $mCommon = new Qss_Model_Extra_Extra();
        $oKhuVuc = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>@(int)$this->_params->Ref_MaKhuVuc));
        $oYeuCau = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>@(int)$this->_params->Ref_PhieuYeuCau));

        $this->html->OYeuCauBaoTri    = $oYeuCau;
        $this->html->OKhuVuc          = $oKhuVuc;
        $this->html->OPhieuBaoTri     = $this->_params;
    }

    public function viwasupcoAcceptanceexcelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản nghiệm thu.xlsx\"");

        $mCommon = new Qss_Model_Extra_Extra();
        $oYeuCau = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFID_M759'=>@(int)$this->_params->IFID_M759));

        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M759', 'Viwasupco_BienBanNghiemThu.xlsx'));
        $main = new stdClass();

        $main->SoPhieu          = @$oYeuCau->SoPhieu;
        $main->NoiDung          = @$oYeuCau->NoiDung;
        $main->NhanXetChatLuong = @$oYeuCau->NhanXetChatLuong;
        $main->KetLuan          = @$oYeuCau->KetLuan;
        $main->Nam              = date('Y');

        $file->init
        (array('m' => $main));

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function viwasupcoRequestAction()
    {
        $mCommon = new Qss_Model_Extra_Extra();
        $oKhuVuc = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>@(int)$this->_params->Ref_MaKhuVuc));
        $oYeuCau = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>@(int)$this->_params->Ref_PhieuYeuCau));

        $this->html->OKhuVuc          = $oKhuVuc;
        $this->html->OPhieuBaoTri     = $this->_params;
        $this->html->OYeuCauBaoTri    = $oYeuCau;
    }
}

?>