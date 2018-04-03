<?php
class Static_M187Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start = $this->params->requests->getParam('start', 0);
        $end   = $this->params->requests->getParam('end', 0);
        $year  = $this->params->requests->getParam('year', 0);

        if($end == 0)
        {
            $end = $start;
        }

        $this->html->start = $start;
        $this->html->end   = $end;
        $this->html->year  = $year;
        $this->html->month = ($start == $end)?$start:"{$start}-{$end}";
        $this->html->report = $this->getReportData(@(int)$start, @(int)$end, @(int)$year);
        // echo '<pre>'; print_r($this->html->report); die;
    }

    public function excelAction()
    {
        $start = $this->params->requests->getParam('start', 0);
        $end   = $this->params->requests->getParam('end', 0);
        $year  = $this->params->requests->getParam('year', 0);
        $month = ($start == $end)?$start:"{$start}-{$end}";

        if($end == 0)
        {
            $end = $start;
        }

        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Theo dõi tiêu thụ điện năng tháng {$month} năm {$year}.xlsx\"");

        $row   = 11;
        $file  = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M187/TheoDoiDienNang.xlsx');
        $main  = new stdClass();
        $dat   = $this->getReportData(@(int)$start, @(int)$end, @(int)$year);

        $main->month = $month;
        $main->year  = $year;
        $file->init(array('main'=>$main));

        for($i = 1; $i <= 31; $i++)
        {
            $data                = new stdClass();
            $data->day           = $i;
            $data->quantity_tt1  = Qss_Lib_Util::formatNumber(@$dat[$i]['TT1']);
            $data->total_tt1     = Qss_Lib_Util::formatNumber(@$dat[$i]['LuyKe_TT1']);
            $data->quantity_tt2  = Qss_Lib_Util::formatNumber(@$dat[$i]['TT2']);
            $data->total_tt2     = Qss_Lib_Util::formatNumber(@$dat[$i]['LuyKe_TT2']);
            $data->quantity_tt3  = Qss_Lib_Util::formatNumber(@$dat[$i]['TT3']);
            $data->total_tt3     = Qss_Lib_Util::formatNumber(@$dat[$i]['LuyKe_TT3']);
            $data->quantity_letb = Qss_Lib_Util::formatNumber(@$dat[$i]['PMT']);
            $data->total_letb    = Qss_Lib_Util::formatNumber(@$dat[$i]['LuyKe_PMT']);
            $data->quantity_b2   = Qss_Lib_Util::formatNumber(@$dat[$i]['KB2']);
            $data->total_b2      = Qss_Lib_Util::formatNumber(@$dat[$i]['LuyKe_KB2']);
            $data->quantity_l2a  = Qss_Lib_Util::formatNumber(@$dat[$i]['2A']);
            $data->quantity_l4b  = Qss_Lib_Util::formatNumber(@$dat[$i]['4B']);
            $data->quantity_l6b  = Qss_Lib_Util::formatNumber(@$dat[$i]['6B']);
            $data->quantity_cty  = Qss_Lib_Util::formatNumber(@$dat[$i]['CTY']);
            $data->cosf          = '';

            $file->newGridRow(array('sub'=>$data), $row, 10);
            $row++;
        }

        $file->removeRow(10);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    private function getReportData($start, $end, $year)
    {
        $startMonth = @(int)$start;
        $endMonth   = @(int)$end;
        $year       = @(int)$year;
        $startDate  = date("{$year}-{$startMonth}-01");
        $endDate    = date("{$year}-{$endMonth}-t");


        $mElec    = new Qss_Model_Extra_Manufacturing();
        $luyKe    = $mElec->getLuyKeDienNangHangNgay($startDate);
        $hangNgay = $mElec->getTongDienNangHangNgay($startDate, $endDate);
        $retval   = array();
        $luykeDV  = array();

        if($luyKe)
        {
            $luykeDV['TT1'] = $luyKe->Total_tt1;
            $luykeDV['TT2'] = $luyKe->Total_tt2;
            $luykeDV['TT3'] = $luyKe->Total_tt3;
            $luykeDV['PMT'] = $luyKe->Total_letb;
            $luykeDV['KB2'] = $luyKe->Total_b2;
            $luykeDV['2A']  = $luyKe->Total_l2a;
            $luykeDV['4B']  = $luyKe->Total_l4b;
            $luykeDV['6B']  = $luyKe->Total_l6b;
            $luykeDV['CTY'] = $luyKe->Total_cty;
        }
        else
        {
            $luykeDV['TT1'] = 0;
            $luykeDV['TT2'] = 0;
            $luykeDV['TT3'] = 0;
            $luykeDV['PMT'] = 0;
            $luykeDV['KB2'] = 0;
            $luykeDV['2A']  = 0;
            $luykeDV['4B']  = 0;
            $luykeDV['6B']  = 0;
            $luykeDV['CTY'] = 0;
        }

        foreach ($hangNgay as $item)
        {
            $retval[(int)$item->Ngay]['TT1'] = $item->Quantity_tt1;
            $retval[(int)$item->Ngay]['TT2'] = $item->Quantity_tt2;
            $retval[(int)$item->Ngay]['TT3'] = $item->Quantity_tt3;
            $retval[(int)$item->Ngay]['PMT'] = $item->Quantity_letb;
            $retval[(int)$item->Ngay]['KB2'] = $item->Quantity_b2;
            $retval[(int)$item->Ngay]['2A']  = $item->Quantity_l2a;
            $retval[(int)$item->Ngay]['4B']  = $item->Quantity_l4b;
            $retval[(int)$item->Ngay]['6B']  = $item->Quantity_l6b;
            $retval[(int)$item->Ngay]['CTY'] = $item->Quantity_cty;

            $retval[(int)$item->Ngay]['LuyKe_TT1'] = $luykeDV['TT1'] + $item->Quantity_tt1;
            $retval[(int)$item->Ngay]['LuyKe_TT2'] = $luykeDV['TT2'] + $item->Quantity_tt2;
            $retval[(int)$item->Ngay]['LuyKe_TT3'] = $luykeDV['TT3'] + $item->Quantity_tt3;
            $retval[(int)$item->Ngay]['LuyKe_PMT'] = $luykeDV['PMT'] + $item->Quantity_letb;
            $retval[(int)$item->Ngay]['LuyKe_KB2'] = $luykeDV['KB2'] + $item->Quantity_b2;
            $retval[(int)$item->Ngay]['LuyKe_2A']  = $luykeDV['2A'] + $item->Quantity_l2a;
            $retval[(int)$item->Ngay]['LuyKe_4B']  = $luykeDV['4B'] + $item->Quantity_l4b;
            $retval[(int)$item->Ngay]['LuyKe_6B']  = $luykeDV['6B'] + $item->Quantity_l6b;
            $retval[(int)$item->Ngay]['LuyKe_CTY'] = $luykeDV['CTY'] + $item->Quantity_cty;

            $luykeDV['TT1'] += $item->Quantity_tt1;
            $luykeDV['TT2'] += $item->Quantity_tt2;
            $luykeDV['TT3'] += $item->Quantity_tt3;
            $luykeDV['PMT'] += $item->Quantity_letb;
            $luykeDV['KB2'] += $item->Quantity_b2;
            $luykeDV['2A']  += $item->Quantity_l2a;
            $luykeDV['4B']  += $item->Quantity_l4b;
            $luykeDV['6B']  += $item->Quantity_l6b;
            $luykeDV['CTY'] += $item->Quantity_cty;
        }


        return $retval;
    }
}