<?php
class Print_M759_workingtestController extends Qss_Lib_PrintController
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    public function indexAction()
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