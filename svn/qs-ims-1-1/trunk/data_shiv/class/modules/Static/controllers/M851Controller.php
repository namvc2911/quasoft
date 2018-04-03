<?php

/**
 * M843Controller.php
 *
 * Báo cáo hiệu chuẩn kiểm định
 *
 * @category   Static
 * @author     Thinh Tuan
 */

class Static_M851Controller extends Qss_Lib_Controller
{
    private $rejectFields;
    private $titleFields;
    private $data;

    public function init()
    {
        $this->rejectFields = array('EMail', 'TinhTrangXuLy');
        $this->titleFields  = array();

        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start   = $this->params->requests->getParam('start', date('d-m-Y'));
        $end     = $this->params->requests->getParam('end', date('d-m-Y'));

        $mOrder  = new Qss_Model_Maintenance_Workorder();
        $this->html->data  = $mOrder->getTrackBreakdown(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end));
        $this->html->start  = $start;
        $this->html->end    = $end;
        $this->html->deptid = $this->_user->user_dept_id;
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Yêu cầu bảo trì.xlsx\"");


        $start   = $this->params->requests->getParam('start', date('d-m-Y'));
        $end     = $this->params->requests->getParam('end', date('d-m-Y'));

        $main    = array();
        $row     = 7;
        $mOrder  = new Qss_Model_Maintenance_Workorder();
        $report  = $mOrder->getTrackBreakdown(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end));

        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M851', 'InYeuCauBaoTri.xlsx'));

        $file->init(array('m' => $main));

        foreach($report as $item)
        {
            $data        = new stdClass();
            $tenThietBi  = $item->TenThietBi.' '.($item->TenKhac?"({$item->TenKhac})":"");
            $thoiGian    = ($item->ThoiGian?date('H:i', strtotime($item->ThoiGian)):'').' '.($item->Ngay?date('d/m/y', strtotime($item->Ngay)):'');
            $nguoiYeuCau = '';
            $duKienHoanThanh = ($item->ThoiGianKetThucDuKien?date('H:i', strtotime($item->ThoiGianKetThucDuKien)):'').' '.($item->NgayDuKienHoanThanh?date('d/m/y', strtotime($item->NgayDuKienHoanThanh)):'');
            $damNhiem = '';

            if($item->NguoiChiuTranhNhiem)
            {
                $temp        = explode(' ', trim($item->NguoiChiuTranhNhiem));
                $temp2       = end($temp);
                $titleName   = ($item->GioiTinh == 1)?'Mr.':'Ms.';
                $damNhiem    = $titleName .' '. $temp2;
            }


            if($item->NguoiYeuCau)
            {
                $temp        = explode(' ', trim($item->NguoiYeuCau));
                $temp2       = end($temp);
                $titleName   = ($item->GioiTinh == 1)?'Mr.':'Ms.';
                $nguoiYeuCau = $titleName .' '. $temp2;
            }

            $data->a   = $tenThietBi;
            $data->b   = $item->TenKhuVuc;
            $data->c   = $thoiGian;
            $data->d   = $nguoiYeuCau;
            $data->e   = $item->MoTa;
            $data->f   = $item->TinhTrang;
            $data->g   = $duKienHoanThanh;
            $data->h   = $damNhiem;
            $data->i   = @$item->MucDoUuTien;

            $file->newGridRow(array('s'=>$data), $row, 6);
            $row++;
        }

        $file->removeRow(6);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function setTitle()
    {
        $fields = Qss_Lib_System::getFieldsByObject('M747', 'OYeuCauBaoTri');
        $fields = $fields->loadFields();
        $dat    = array();

        foreach ($fields as $item)
        {
            if($item->bEffect
                && ( ( ($item->bGrid&1) && !$this->_user->user_mobile) || ( ($item->bGrid&2) && $this->_user->user_mobile ))
                && !in_array($item->FieldCode, $this->rejectFields)
            )
            {
                $dat[$item->FieldCode] = $item->szFieldName;
            }
        }

        $dat['Name'] = '';

        $this->titleFields = $dat;
    }

    public function setData()
    {
        $retval = array();
        $i      = 0;
        $nameLang = ($this->_user->user_lang == 'vn')?'':'_'.$this->_user->user_lang;

        $mYeuCau = Qss_Model_Db::Table('OYeuCauBaoTri');
        $mYeuCau->select(sprintf('OYeuCauBaoTri.*, qsw.Name%1$s AS Name', $nameLang));
        $mYeuCau->join('INNER JOIN qsiforms AS iform ON OYeuCauBaoTri.IFID_M747 = iform.IFID');
        $mYeuCau->join('INNER JOIN qsworkflows AS qsw ON qsw.FormCode = iform.FormCode');
        $mYeuCau->join('INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND iform.Status = qsws.StepNo');
        $dat = $mYeuCau->fetchAll();

        foreach ($dat as $item)
        {
            $retval[$i] = new stdClass();

            foreach ($item as $key=>$sitem)
            {
                if(isset($this->titleFields[$key]))
                {
                    $retval[$i]->{$key} = $sitem;
                }
            }
        }

        $this->data = $retval;
    }
}

?>