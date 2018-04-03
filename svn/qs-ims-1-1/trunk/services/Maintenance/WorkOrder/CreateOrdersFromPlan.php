<?php

class Qss_Service_Maintenance_WorkOrder_CreateOrdersFromPlan extends Qss_Service_Abstract
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