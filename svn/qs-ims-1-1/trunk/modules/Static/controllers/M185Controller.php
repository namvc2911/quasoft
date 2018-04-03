<?php
/**
 * Class Static_M158Controller
 * Kết quả tiêu thụ điện năng hàng tháng
 */
class Static_M185Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {
        $this->html->factories   = $this->getFactories();
        $this->html->departments = $this->getDepartments();
        $this->html->employees   = $this->getEmployees();
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
        $boPhan     = $this->params->requests->getParam('department', '');
        $nhaMay     = $this->params->requests->getParam('factory', '');
        $employees  = $this->params->requests->getParam('employees', array());
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $model      = new Qss_Model_Maintenance_Asset();
        $data       = $model->getCompensation(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $employees
            , $nhaMay
            , $boPhan);
        $count      = array();

        foreach ($data as $item)
        {
            $item->SoTienBoiThuongTinhLai = 0;
            $item->SoTienBoiThuongTinhLai = ($item->ThanhTien * $item->PhanTramKhauHao/100);

            if(!isset($count[(int)$item->Ref_MaNhanVien]))
            {
                $count[(int)$item->Ref_MaNhanVien]['count']  = 0;
                $count[(int)$item->Ref_MaNhanVien]['amount'] = 0;
            }

            $count[(int)$item->Ref_MaNhanVien]['amount'] += ($item->ThanhTien * $item->PhanTramKhauHao/100);
            ++$count[(int)$item->Ref_MaNhanVien]['count'];
        }

        $this->html->report = $data;
        $this->html->count  = $count;
    }

    public function getEmployees()
    {
        $common     = new Qss_Model_Extra_Extra();
        $retval     = array();
        $employees  = $common->getTable(array('*'), 'ODanhSachNhanVien', array(), array('MaNhanVien'), 'NO_LIMIT');
        $i          = 0;

        foreach($employees as $dat)
        {
            $titleBoPhan  = $dat->TenBoPhan;
            $titleBoPhan .= $dat->TenBoPhan && $dat->TenPhongBan?' - ':'';
            $titleBoPhan .= $dat->TenPhongBan;

            $retval[0]['Dat'][$i]['ID']      = $dat->IOID;
            $retval[0]['Dat'][$i]['Display'] = "{$dat->MaNhanVien} - {$dat->TenNhanVien} ({$titleBoPhan})";
            $i++;
        }

        return $retval;
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
}