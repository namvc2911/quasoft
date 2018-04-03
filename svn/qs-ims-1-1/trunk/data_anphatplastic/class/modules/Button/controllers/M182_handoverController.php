<?php
class Button_M182_handoverController extends Qss_Lib_Controller
{
    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();
    }

    public function indexAction() {
        $ifid         = $this->params->requests->getParam('ifid', 0);
        $deptid       = $this->params->requests->getParam('deptid', 0);
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';

        $this->html->emps   = $this->_getEmployees($ifid);
        $this->html->ifid   = $ifid;
        $this->html->deptid = $deptid;
    }

    public function showAction() {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
        $employee     = $this->params->requests->getParam('eid', 0);
        $ifid         = $this->params->requests->getParam('ifid', 0);
        $deptid       = $this->params->requests->getParam('deptid', 0);

        $mPhieuBanGiao = Qss_Model_Db::Table('OPhieuBanGiaoTaiSan');
        $mPhieuBanGiao->where(sprintf('IFID_M182 = %1$d', $ifid));

        $mBanGiao      = Qss_Model_Db::Table('OChiTietBanGiaoTaiSan');
        $mBanGiao->where(sprintf('IFNULL(Ref_MaNhanVien, 0) = %1$d', $employee));
        $mBanGiao->where(sprintf('IFNULL(IFID_M182, 0) = %1$d', $ifid));


        $mNhanVien      = Qss_Model_Db::Table('ODanhSachNhanVien');
        $mNhanVien->where(sprintf('IFNULL(IOID, 0) = %1$d', $employee));

        $this->html->objPhieuBanGiao = $mPhieuBanGiao->fetchOne();
        $this->html->objBanGiao      = $mBanGiao->fetchAll();
        $this->html->objNhanVien     = $mNhanVien->fetchOne();
        $this->html->ifid     = $ifid;
        $this->html->deptid   = $deptid;
        $this->html->eid      = $employee;


    }

    public function excelAction() {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản bàn giao.xlsx\"");

        $employee     = $this->params->requests->getParam('eid', 0);
        $ifid         = $this->params->requests->getParam('ifid', 0);
        $deptid       = $this->params->requests->getParam('deptid', 0);

        $mPhieuBanGiao = Qss_Model_Db::Table('OPhieuBanGiaoTaiSan');
        $mPhieuBanGiao->where(sprintf('IFID_M182 = %1$d', $ifid));

        $mBanGiao      = Qss_Model_Db::Table('OChiTietBanGiaoTaiSan');
        $mBanGiao->where(sprintf('IFNULL(Ref_MaNhanVien, 0) = %1$d', $employee));
        $mBanGiao->where(sprintf('IFNULL(IFID_M182, 0) = %1$d', $ifid));

        $mNhanVien      = Qss_Model_Db::Table('ODanhSachNhanVien');
        $mNhanVien->where(sprintf('IFNULL(IOID, 0) = %1$d', $employee));

        $objPhieuBanGiao = $mPhieuBanGiao->fetchOne();
        $objBanGiao      = $mBanGiao->fetchAll();
        $objNhanVien     = $mNhanVien->fetchOne();
        $stt             = 0;
        $row             = 21;

        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M182', 'ANPHAT_BienBanBanGiao.xlsx'));
        $main     = new stdClass();

        $main->SoPhieu = @$objPhieuBanGiao->SoPhieu;
        $main->d       = date('d');
        $main->m       = date('m');
        $main->y       = date('Y');
        $main->NhaMayBenNhan       = @$objPhieuBanGiao->NhaMay;
        $main->NhanVienBanGiao     = @$objNhanVien->TenNhanVien;
        $main->BoPhanBanGiao       = @$objNhanVien->BoPhan;
        $main->NhaMayNguoiLamChung = @$objPhieuBanGiao->NhaMay;

        $file->init(array('m'=>$main));

        foreach ($objBanGiao as $item)
        {
            $data     = new stdClass();
            $data->a  = ++$stt;
            $data->b  = $item->TenTaiSan;
            $data->c  = $item->DonViTinh;
            $data->d  = Qss_Lib_Util::formatNumber($item->SoLuong);
            $data->e  = '';
            $data->f  = '';
            $data->g  = '';

            $file->newGridRow(array('s'=>$data), $row, 20);
            $row++;
        }

        $file->removeRow(20);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    private function _getEmployees($ifid)
    {
        $mTable  = Qss_Model_Db::Table('OChiTietBanGiaoTaiSan');
        $mTable->where(sprintf('IFID_M182 = %1$d', $ifid));
        $mTable->groupby('IFNULL(Ref_MaNhanVien, 0)');
        $emps    = $mTable->fetchAll();
        $ret     = array();

        foreach($emps as $item) {
            $ret[(int)$item->Ref_MaNhanVien] = $item->TenNhanVien." - ".$item->MaNhanVien;
        }

        return $ret;
    }
}