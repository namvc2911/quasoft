<?php
class Static_M420Controller extends Qss_Lib_Controller
{
    public function init ()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction ()
    {
        $this->html->factories   = $this->getFactories();
        $this->html->departments = $this->getDepartments();
    }

    public function departmentsAction()
    {
        $model  = new Qss_Model_Maintenance_Employee();
        $nhaMay = $this->params->requests->getParam('factory', '');
        $data   = $model->getDepartments($nhaMay);

        $json = array('data'=>$data, 'message'=>'', 'error'=>false, 'status'=>null, 'redirect'=> null);
        echo Qss_Json::encode($json);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function showAction ()
    {
        $employee    = $this->params->requests->getParam('emp', 0);
        $employeeTag = $this->params->requests->getParam('emp_tag', '');
        $all        = $this->params->requests->getParam('all', 0);
        $employee    = (trim($employeeTag) != '')?$employee:0;
        $asset       = $this->params->requests->getParam('asset', 0);
        $assetTag    = $this->params->requests->getParam('asset_tag', '');
        $asset       = (trim($assetTag) != '')?$asset:0;
        $date     = $this->params->requests->getParam('date', '');
        $boPhan   = $this->params->requests->getParam('department', '');
        $boPhan   = $boPhan === 0?'':$boPhan;
        $nhaMay   = $this->params->requests->getParam('factory', '');
        $nhaMay   = $nhaMay === 0?'':$nhaMay;
        $model    = new Qss_Model_Maintenance_Asset();

        $this->html->tools      = $model->getAssetsOfEmployees($nhaMay, $boPhan, $employee, 0, $asset,'','',$all);
        $this->html->date       = Qss_Lib_Date::mysqltodisplay($date);
        $mCommon         = new Qss_Model_Extra_Extra();
        $phongBanBoPhan  = $mCommon->getTableFetchOne('OBoPhan', array('IOID'=>$boPhan));
        $this->html->factory    = $phongBanBoPhan?$phongBanBoPhan->TenPhongBan:'';
        $this->html->department = $phongBanBoPhan?$phongBanBoPhan->TenBoPhan:'';
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Báo Cáo Kiểm Kê TSCN.xlsx\"");
        $excel = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M420/bao_cao_kiem_ke_tai_san.xlsx',true);

        $oldEmp      = '';
        $row         = 12;
        $main        = new stdClass();
        $employee    = $this->params->requests->getParam('emp', 0);
        $employeeTag = $this->params->requests->getParam('emp_tag', '');
        $all         = $this->params->requests->getParam('all', 0);
        $employee    = (trim($employeeTag) != '')?$employee:0;
        $asset       = $this->params->requests->getParam('asset', 0);
        $assetTag    = $this->params->requests->getParam('asset_tag', '');
        $asset       = (trim($assetTag) != '')?$asset:0;
        $date        = $this->params->requests->getParam('date', '');
        $boPhan      = $this->params->requests->getParam('department', '');
        $boPhan      = $boPhan === 0?'':$boPhan;
        $nhaMay      = $this->params->requests->getParam('factory', '');
        $nhaMay      = $nhaMay === 0?'':$nhaMay;
        $model       = new Qss_Model_Maintenance_Asset();
        $assets      = $model->getAssetsOfEmployees($nhaMay, $boPhan, $employee, 0, $asset,'','',$all);


        $oldFactory       = '';
        $oldDept          = '';
        $level            = 0;

        $levelNhaMay      = 0;
        $levelBoPhan      = (trim($this->factory) == '')?1:0;
        $levelNhanVien    = $levelBoPhan + 1;

        $mCommon         = new Qss_Model_Extra_Extra();
        $phongBanBoPhan  = $mCommon->getTableFetchOne('OBoPhan', array('IOID'=>$boPhan));

        $main->NgayIn       = Qss_Lib_Date::mysqltodisplay($date);
        $main->NhaMay       = $phongBanBoPhan?$phongBanBoPhan->TenPhongBan:'';
        $main->BoPhan       = $phongBanBoPhan?$phongBanBoPhan->TenBoPhan:'';
        $data               = array('main'=>$main);
        $excel->init($data);

        foreach($assets as $item)
        {
            if($oldFactory != $item->NhaMayHienTai && (trim($nhaMay) == ''))
            {
                $data         = new stdClass();
                $data->TieuDe = "Nhà máy: {$item->NhaMayHienTai}";
                $excel->newGridRow(array('sub'=>$data), $row, 8);
                $row++;
            }

            if( ($oldFactory != $item->NhaMayHienTai || $oldDept != $item->BoPhanHienTai) && (trim($boPhan) == ''))
            {
                $data         = new stdClass();
                $data->TieuDe2 = "Bộ phận: {$item->BoPhanHienTai}";
                $excel->newGridRow(array('sub'=>$data), $row, 9);
                $row++;
            }

            if($oldFactory != $item->NhaMayHienTai || $oldDept != $item->BoPhanHienTai || $oldEmp != $item->Ref_MaNhanVien)
            {
                $groupTitle   = "{$item->MaNhanVien} - {$item->TenNhanVien} - CMT: {$item->SoCMND}";
                $data         = new stdClass();
                $data->TieuDe3 = $groupTitle;
                $excel->newGridRow(array('sub'=>$data), $row, 10);
                $row++;
            }

            $oldDept    = $item->BoPhanHienTai;
            $oldFactory = $item->NhaMayHienTai;
            $oldEmp     = $item->Ref_MaNhanVien;

            $data            = new stdClass();
            $data->MaVatTu   = $item->MaTaiSan;
            $data->TenVatTu  = $item->TenTaiSan;
            $data->TonCuoi   = Qss_Lib_Util::formatNumber($item->SoLuongConLaiCuoi);
            $data->ThucTe    = '';
            $data->ChenhLech = '';
            $data->GhiChu    = '';
            $excel->newGridRow(array('sub'=>$data), $row, 11);
            $row++;
        }

        $excel->removeRow(11);
        $excel->removeRow(10);
        $excel->removeRow(9);
        $excel->removeRow(8);
        //$m->removeRow(9);
        $excel->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function employeeAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('ODanhSachNhanVien', array('MaNhanVien', 'TenNhanVien'), $tag, array('MaNhanVien'));
        $retval   = array();

        foreach($request as $item)
        {
            $titleBoPhan  = $item->TenBoPhan;
            $titleBoPhan .= $item->TenBoPhan && $item->TenPhongBan?' - ':'';
            $titleBoPhan .= $item->TenPhongBan;

            $display  = "{$item->MaNhanVien} - {$item->TenNhanVien} ({$titleBoPhan})";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function assetAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('ODanhMucCongCuDungCu', array('Ma', 'Ten'), $tag, array('Ma'));
        $retval   = array();

        foreach($request as $item)
        {
            $display  = "{$item->Ma} - {$item->Ten}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getFactories()
    {
        $model = new Qss_Model_Maintenance_Employee();
        $data  = $model->getFactories();
        $ret   = array();

        foreach($data as $item)
        {
            $ret[trim($item->NhaMay)] = ($item->NhaMay);
        }
        return $ret;
    }

    public function getDepartments($nhaMay = false)
    {
        $model = new Qss_Model_Maintenance_Employee();
        $data  = $model->getDepartments($nhaMay);
        $ret   = array();

        foreach($data as $item)
        {
            $ret[$item->BoPhan] = "{$item->BoPhan}";
        }
        return $ret;
    }
}