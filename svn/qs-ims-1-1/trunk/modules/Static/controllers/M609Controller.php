<?php
class Static_M609Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $model     = new Qss_Model_Extra_Warehouse();
        $params    = $this->params->requests->getParams();
        $start     = Qss_Lib_Date::displaytomysql($params['start']);
        $end       = Qss_Lib_Date::displaytomysql($params['end']);
        $end       = Qss_Lib_Extra::getEndDate($start, $end, $params['period']);
        $aTime     = Qss_Lib_Extra::displayRangeDate($start, $end, $params['period']); // Range time

        $before     = $model->getInventoryByDate($start, $params['warehouse'], $params['item'] );
        $maxMin     = $model->getMaxMinInventoryByPeriod($start, $end, $params['period'], $params['item'], $params['warehouse']);
        $lastest    = $model->getLastestInventoryByPeriod($start, $end, $params['period'], $params['item'], $params['warehouse']);

        $aHistory   = array();
        $tmpHistory = array();

        foreach ($maxMin as $item)
        {
            $tmpHistory[$item->Khoa]['Max'] = $item->MaxTonKho ? $item->MaxTonKho : 0;
            $tmpHistory[$item->Khoa]['Min'] = $item->MinTonKho ? $item->MinTonKho : 0;
        }

        foreach ($lastest as $item)
        {
            $tmpHistory[$item->Khoa]['Last'] = $item->CuoiNgay ? $item->CuoiNgay : 0;
        }

        $before->TonKhoDauKy = $before;
        $max                 = $before;
        $min                 = $before;
        $last                = $before;

        foreach ($aTime as $key => $time)
        {
            if (isset($tmpHistory[$key]))
            {
                $max  = $tmpHistory[$key]['Max'];
                $min  = $tmpHistory[$key]['Min'];
                $last = $tmpHistory[$key]['Last'];
            }

            $aHistory[$key]['Max']  = $max;
            $aHistory[$key]['Min']  = $min;
            $aHistory[$key]['Last'] = $last;
        }

        $this->html->history   = $aHistory;
        $this->html->rangeTime = $aTime;
        $this->html->start     = $params['start'];
        $this->html->end       = Qss_Lib_Date::mysqltodisplay($end);
    }

    public function show2Action()
    {
        $model                 = new Qss_Model_Extra_Extra();
        $params                = $this->params->requests->getParams();
        $itemFilter            = $params['refWarehouse'] ? array('Ref_Kho' => $params['refWarehouse']) : array();
        $this->html->warehouse = $model->getTable(
            array('distinct Ref_MaSP', 'MaSP', 'TenSP', 'Kho'), 'OKho', $itemFilter, array(' MaSP '), 'NO_LIMIT'
        );
        $this->setLayoutRender(false);

    }
}
?>