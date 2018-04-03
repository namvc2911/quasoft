<?php

/**
 * M843Controller.php
 *
 * Báo cáo hiệu chuẩn kiểm định
 *
 * @category   Static
 * @author     Thinh Tuan
 */

class Static_M843Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start    = $this->params->requests->getParam('start', '');
        $end      = $this->params->requests->getParam('end', '');
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $eq       = $this->params->requests->getParam('eq', 0);

        $this->html->report = $this->getData(
            $start
            , $end
            , $location
            , $type
            , $group
            , $eq
        );
        $this->html->start = $start;
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Danh mục thiết bị kiểm định.xlsx\"");

        $start    = $this->params->requests->getParam('start', '');
        $end      = $this->params->requests->getParam('end', '');
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $eq       = $this->params->requests->getParam('eq', 0);

        $report   = $this->getData(
            $start
            , $end
            , $location
            , $type
            , $group
            , $eq
        );
        $stt      = 0;
        $row      = 9;
        $merge    = array();
        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M843', 'HFH_DanhMucThietBiKiemDinh.xlsx'));
        $main     = new stdClass();

        $main->m1 = $start?date('Y', strtotime($start)):'';

        $file->init(array('m'=>$main));

        foreach($report as $key=>$items)
        {
            $first   = true;
            $count   = count($items);
            $rowspan = '';

            if($count > 1){
                $rowspan = ($row + $count) - 1;
                $merge[] = "G{$row}:G{$rowspan}";
                $merge[] = "H{$row}:H{$rowspan}";
            }

            foreach ($items as $item)
            {
                $data      = new stdClass();
                $data->a   = ++$stt;
                $data->b   = $item->TenThietBi;
                $data->c   = $item->MaThietBi;
                $data->d   = $item->TenKhuVuc;
                $data->e   = $item->SoHopDong;
                $data->f   = $item->TenDoiTac;

                if($first)
                {
                    $data->g   = Qss_Lib_Date::mysqltodisplay($item->Ngay);
                    $data->h   = Qss_Lib_Date::mysqltodisplay($item->NgayKiemDinhTiepTheo);
                }
                else
                {
                    $data->g   = '';
                    $data->h   = '';
                }

                $first = false;

                $file->newGridRow(array('s'=>$data), $row, 8);
                $row++;
            }
        }

        foreach ($merge as $item)
        {
            $file->mergeCells($item);
        }

        $file->removeRow(8);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    private function getData(
        $start
        , $end
        , $location
        , $type
        , $group
        , $eq)
    {
        $retval   = array();
        $model    = new Qss_Model_Maintenance_Equip_Calibration();
        $report   = $model->getCalibrations(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $type
            , $group
            , $eq);

        foreach ($report as $item)
        {
            $key = $item->Ngay.'-'.$item->NgayKiemDinhTiepTheo;
            $retval[$key][] = $item;
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }
}

?>