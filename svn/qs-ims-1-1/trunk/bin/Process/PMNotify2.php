<?php

class Qss_Bin_Process_PMNotify2 extends Qss_Lib_Bin
{

    public function __doExecute($start, $end)
    {
        $model = new Qss_Model_Maintenance_Workorder();
        $start = Qss_Lib_Date::displaytomysql($start);
        $end   = Qss_Lib_Date::displaytomysql($end);
        $error = $model->createWorkOrdersFromPlans($start, $end);

        if($error > 0)
        {
            $this->setError();
            $this->setMessage('Có '.$error .' dòng lỗi!');
        }
    }
}
?>