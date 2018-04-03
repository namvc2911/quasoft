<?php
class Static_M841Controller extends Qss_Lib_Controller
{
    protected $user;

    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        $this->user = Qss_Register::get('userinfo');
    }

    public function indexAction()
    {
        $this->html->depts = $this->getDepartments();
    }

    protected function getEquips($dept)
    {
        $retval   = array();
        $model    = new Qss_Model_Maintenance_Equipmentworking();
        $ordered  = $model->getNumOfOrderedEquipsByDepartment($dept);
        $required = $model->getNumOfRequiredEquipsByDepartment($dept);
        $total    = $model->getNumOfEquipsByDepartment($dept);

        foreach ($total as $item)
        {
            $key = "{$item->Ref_LoaiThietBi}-{$item->DepartmentID}";

            if(!isset($retval[$key]))
            {
                $retval[$key] = new stdClass();
            }

            $retval[$key]->thietBi           = $item->LoaiThietBi;
            $retval[$key]->ma                = $item->MaLoaiThietBi;
            $retval[$key]->donViTinh         = 'Cái';
            $retval[$key]->soLuong           = $item->SoLuong;
            $retval[$key]->soLuongHong       = $item->SoLuongHong;
            $retval[$key]->donViQuanLy       = $item->DonViQuanLy;
            $retval[$key]->soLuongDaDieuDong = 0;
            $retval[$key]->soLuongDangYeuCau = 0;
        }

        foreach ($ordered as $item)
        {
            $key = "{$item->Ref_LoaiThietBi}-{$item->DepartmentID}";

            if(isset($retval[$key]))
            {
                $retval[$key]->soLuongDaDieuDong = $item->SoLuongDaDieuDong;
            }

        }

        foreach ($required as $item)
        {
            $key = "{$item->Ref_LoaiThietBi}-{$item->DepartmentID}";

            if(isset($retval[$key]))
            {
                $retval[$key]->donViTinh         = $item->DonViTinh;
                $retval[$key]->soLuongDangYeuCau = $item->SoLuongDangYeuCau;
            }


        }

        foreach ($retval as $key=>$item)
        {
            $item->soLuongDaDieuDong = (isset($item->soLuongDaDieuDong) && $item->soLuongDaDieuDong)?$item->soLuongDaDieuDong:0;
            $item->soLuongDangYeuCau = (isset($item->soLuongDangYeuCau) && $item->soLuongDangYeuCau)?$item->soLuongDangYeuCau:0;
            $item->soLuongHong       = (isset($item->soLuongHong) && $item->soLuongHong)?$item->soLuongHong:0;

            $retval[$key]->soLuongCoTheDieuDong = $item->soLuong - ($item->soLuongDaDieuDong + $item->soLuongDangYeuCau + $item->soLuongHong);
        }

        return $retval;
    }


    protected function getTools($dept)
    {
        $retval   = array();
        $model    = new Qss_Model_Maintenance_Equipmentworking();
        $ordered  = $model->getNumOfOrderedToolsByDepartment($dept);
        $required = $model->getNumOfRequiredToolsByDepartment($dept);
        $total    = $model->getNumOfToolsByDepartment($dept);

        foreach ($total as $item)
        {
            $key = "{$item->Ref_CongCuDungCu}-{$item->DepartmentID}";

            if(!isset($retval[$key]))
            {
                $retval[$key] = new stdClass();
            }

            $retval[$key]->thietBi           = $item->TenCongCuDungCu;
            $retval[$key]->ma                = $item->MaCongCuDungCu;
            $retval[$key]->donViTinh         = $item->DonViTinh;
            $retval[$key]->soLuong           = $item->SoLuong;
            $retval[$key]->donViQuanLy       = $item->DonViQuanLy;
            $retval[$key]->soLuongDaDieuDong = 0;
            $retval[$key]->soLuongDangYeuCau = 0;
            $retval[$key]->soLuongHong       = '';
        }

        foreach ($ordered as $item)
        {
            $key = "{$item->Ref_CongCuDungCu}-{$item->DepartmentID}";

            if(isset($retval[$key]))
            {
                $retval[$key]->soLuongDaDieuDong = $item->SoLuongDaDieuDong;
            }
        }

        foreach ($required as $item)
        {
            $key = "{$item->Ref_CongCuDungCu}-{$item->DepartmentID}";

            if(isset($retval[$key]))
            {
                $retval[$key]->soLuongDangYeuCau = $item->SoLuongDangYeuCau;
            }
        }

        foreach ($retval as $key=>$item)
        {
            $item->soLuongDaDieuDong = (isset($item->soLuongDaDieuDong) && $item->soLuongDaDieuDong)?$item->soLuongDaDieuDong:0;
            $item->soLuongDangYeuCau = (isset($item->soLuongDangYeuCau) && $item->soLuongDangYeuCau)?$item->soLuongDangYeuCau:0;
            $retval[$key]->soLuongCoTheDieuDong = $item->soLuong - ($item->soLuongDaDieuDong + $item->soLuongDangYeuCau);
        }

        return $retval;
    }

    public function showAction()
    {
        $dept                     = $this->params->requests->getParam('department', '');
        $this->html->equipsMoving = $this->getEquips($dept);
        $this->html->toolsMoving  = $this->getTools($dept);
    }


    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Báo Cáo Thiết Bị Điều Động.xlsx\"");

        $dept           = $this->params->requests->getParam('department', '');
        $noOfEquipList  = 0;
        $noOfToolList   = 0;
        $startPrintEq   = 9;
        $startPrintTool = 11;

        $equipsMoving = $this->getEquips($dept);
        $toolsMoving  = $this->getTools($dept);

        $path  = Qss_Lib_System::getTemplateFile('M841', 'BaoCaoThietBiDieuDong.xlsx');
        $excel = new Qss_Model_Excel($path, true);
        $excel->init();

        // Print điều động thiêt bị
        foreach ($equipsMoving as $item)
        {
            $data = new stdClass();
            $data->stt     = ++$noOfEquipList;
            $data->thietBi = $item->thietBi;
            $data->ma      = $item->ma;
            $data->dvt     = $item->donViTinh;
            $data->sl      = $item->soLuong;
            $data->sldyc   = $item->soLuongDangYeuCau;
            $data->slddd   = $item->soLuongDaDieuDong;
            $data->slh     = $item->soLuongHong;
            $data->slctdd  = $item->soLuongCoTheDieuDong;
            $data->dvql    = $item->donViQuanLy;

            $excel->newGridRow(array('s'=>$data), $startPrintEq, 8);
            $startPrintEq++;
        }

        $startPrintTool = $startPrintEq + 2;
        // In điều động công cụ dụng cụ
        foreach ($toolsMoving as $item)
        {
            $data          = new stdClass();
            $data->stt     = ++$noOfToolList;
            $data->thietBi = $item->thietBi;
            $data->ma      = $item->ma;
            $data->dvt     = $item->donViTinh;
            $data->sl      = $item->soLuong;
            $data->sldyc   = $item->soLuongDangYeuCau;
            $data->slddd   = $item->soLuongDaDieuDong;
            $data->slh     = $item->soLuongHong;
            $data->slctdd  = $item->soLuongCoTheDieuDong;
            $data->dvql    = $item->donViQuanLy;

            $excel->newGridRow(array('s'=>$data), $startPrintTool, 10);
            $startPrintTool++;
        }

        $excel->removeRow(8);
        if(!count($equipsMoving)) {$excel->removeRow(7);}
        $excel->removeRow($startPrintEq);
        if(!count($toolsMoving)) {$excel->removeRow($startPrintEq+1);}

        $excel->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getDepartments()
    {
        $mDep  = new Qss_Model_Extra_Extra();
        $depts = $mDep->getNestedSetTable('OKhuVuc', '', false);
        $ret    = array();

        foreach($depts as $item)
        {
            $ret[$item->DeptID] = str_repeat('&nbsp;', (($item->LEVEL -1)*3)) . "{$item->MaKhuVuc} - {$item->Ten}";
        }

        return $ret;
    }
}