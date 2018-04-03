<?php
/**
 * Class Static_M158Controller
 * Kết quả tiêu thụ điện năng hàng tháng
 */
class Static_M184Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
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

    public function showAction()
    {
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', date('d-m-Y'));
        $all        = $this->params->requests->getParam('all', 0);
        $rest       = $this->params->requests->getParam('rest', 0);
        $boPhan     = $this->params->requests->getParam('department', '');
        $nhaMay     = $this->params->requests->getParam('factory', '');
        $employee   = $this->params->requests->getParam('employee', 0);
        $assetType  = $this->params->requests->getParam('asset_type', 0);
        $asset      = $this->params->requests->getParam('asset', 0);
        $page       = $this->params->requests->getParam('page', 1);
        $display    = $this->params->requests->getParam('display', 20);
        $model      = new Qss_Model_Maintenance_Asset();
        //$this->html->report = $model->getTransaction($employee, $asset, $nhaMay, $boPhan);
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);

        $total = $model->countLinesByAssetsOfEmployees(
            $nhaMay, $boPhan, $employee, $assetType, $asset, $mStart, $mEnd, $all, $rest);
        $cpage = $display?ceil($total/$display):1;
        $page  = ($page <= $cpage)?$page:1;


        $this->html->deptid  = $this->_user->user_dept_id;
        $this->html->report  = $model->getAssetsOfEmployees(
            $nhaMay, $boPhan, $employee, $assetType, $asset, $mStart, $mEnd, $all, $rest, $page, $display);
        $this->html->cpage   = $cpage;
        $this->html->display = $display;
        $this->html->next    = (($page + 1) > $cpage)?$cpage:($page + 1);
        $this->html->prev    = (($page - 1) < 1)?1:($page - 1);
        $this->html->page    = $page;
        $this->html->all     = $all;
        $this->html->start   = $start;
        $this->html->end     = $end;
    }

    public function detailAction()
    {
        $factory    = $this->params->requests->getParam('factory', '');
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', date('d-m-Y'));
        $all        = $this->params->requests->getParam('all', 0);
        $employee   = $this->params->requests->getParam('employee', 0);
        $asset      = $this->params->requests->getParam('asset', 0);
        $model      = new Qss_Model_Maintenance_Asset();
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);

        $this->html->report  = $model->getDetailAssetOfEmployee($employee, $asset, $mStart, $mEnd, $all, $factory);
        $this->html->deptid  = $this->_user->user_dept_id;
    }

    public function historyAction()
    {
        $handoverIOID     = $this->params->requests->getParam('handover', 0);
        $employee         = $this->params->requests->getParam('employee', 0);
        $asset            = $this->params->requests->getParam('asset', 0);
        $model            = new Qss_Model_Maintenance_Asset();
        $mCommon          = new Qss_Model_Extra_Extra();

        $this->html->history = $model->getAssetsHistory($handoverIOID, $employee, $asset);
        $this->html->asset   = $mCommon->getTableFetchOne('ODanhMucCongCuDungCu', array('IOID'=>$asset));
        $this->html->rType   = Qss_Lib_System::getFieldRegx('OChiTietThuHoiTaiSan', 'Loai');
        $this->html->deptid  = $this->_user->user_dept_id;

    }

    public function getFactories()
    {
        $model = new Qss_Model_Maintenance_Employee();
        $data  = $model->getFactories();
        $ret   = array();

        foreach($data as $item)
        {
            $ret[$item->NhaMay] = "{$item->NhaMay}";
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

//    public function getAssetTypes()
//    {
//        $mCommon  = new Qss_Model_Extra_Extra();
//        $data     = $mCommon->getTable(array('*'), 'OLoaiCongCuDungCu');
//        $ret      = array();
//
//        foreach($data as $item)
//        {
//            $ret[$item->IOID] = "{$item->Loai}";
//        }
//        return $ret;
//    }


    public function employeesAction()
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

    public function assetsAction()
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
}