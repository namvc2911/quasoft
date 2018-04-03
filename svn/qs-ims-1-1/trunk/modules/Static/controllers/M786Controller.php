<?php

class Static_M786Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';

        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mMaterial  = new Qss_Model_Maintenance_Workorder_Material();
        $start      = $this->params->requests->getParam('start', 0);
        $group      = $this->params->requests->getParam('group', 0);
        $type       = $this->params->requests->getParam('type', 0);
        $eq         = $this->params->requests->getParam('eq', 0);
        $item       = $this->params->requests->getParam('material', 0);
        $alert      = $this->params->requests->getParam('alert', array());
        $startMysql = Qss_Lib_Date::displaytomysql($start);
        $data       = $mMaterial->getLifeCycle($startMysql, $group, $type, $eq, $item, $alert);
        foreach ($data as $key=>$dat)
        {
            $add = 0;
            //$AvgDuration = ceil($dat->AvgDuration);
            //$AvgDuration = (int)$AvgDuration;
            $ts = $dat->ts;
            $AvgDuration = 0;
            if($ts)
            {
            	$arr = explode(',',$ts);
            	$AvgDuration = Qss_Lib_Util::tsf($arr);
            	$AvgDuration = ceil($AvgDuration);
            	$AvgDuration = (int)$AvgDuration;
            }
            $dat->SoNgayCanhBao = (int)$dat->SoNgayCanhBao;

            if($dat->SoNgayCanhBao && $AvgDuration)
            {
                if($dat->SoNgayCanhBao > $AvgDuration)
                {
                    $add = $AvgDuration;
                }
                else
                {
                    $add = $dat->SoNgayCanhBao;
                }
            }
            elseif($dat->SoNgayCanhBao)
            {
                $add = $dat->SoNgayCanhBao;
            }
            elseif($AvgDuration)
            {
                $add = $AvgDuration;
            }

            $NextDate = ($add)?date('Y-m-d', strtotime($dat->NgayThayTheCuoi . " +{$add} days")):'';

            $dat->NgayThayTheDuDoan = $NextDate;
            $dat->class = '' ;

            if($dat->NgayThayTheDuDoan)
            {
                // Ngày thay thế dự đoán > ngày hiện tại
                if(Qss_Lib_Date::compareTwoDate($dat->NgayThayTheDuDoan, date('Y-m-d'))  == 1)
                {
                    $diff = Qss_Lib_Date::diffTime($dat->NgayThayTheDuDoan, date('Y-m-d'), 'D');

                    // Ngày thay thế - ngày hiện tại < thoi gian thay the thi vang else do
                    if($diff < $add)
                    {
                        $dat->class = 'bgyellow bold';
                        $dat->type  = 1;
                    }
                    else
                    {
                        $dat->class = 'bgpink bold';
                        $dat->type  = 2;
                    }
                }
                else
                {
                    $dat->class = 'bgpink bold';
                    $dat->type  = 2;
                }
            }



            if(count($alert) && !in_array($dat->type, $alert))
            {
                unset($data[$key]);
            }
        }

        $this->html->report = $data;

    }
}

?>