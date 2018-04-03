<?php
class Qss_Service_Static_M188_CreateWorkorders extends Qss_Lib_Service
{
    function __doExecute($params)
    {
        $mPlan = new Qss_Model_Maintenance_Workorder();

        $error = $mPlan->createWorkOrdersFromPlansArray($params);

        if($error)
        {
            $this->setError();
            $this->setMessage('Có '. $error . ' dòng lỗi!');
        }
    }
}