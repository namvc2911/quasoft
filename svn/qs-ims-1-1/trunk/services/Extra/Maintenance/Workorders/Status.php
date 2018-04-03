<?php
Class Qss_Service_Extra_Maintenance_Workorders_Status extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {

        $ifids   = isset($params['workorder'])?$params['workorder']:array();
        $stepno  = isset($params['stepno'])?$params['stepno']:0;
        $user    = Qss_Register::get('userinfo');
        $comment = '';

        $deptids = array();

        foreach($ifids as $ifid)
        {
            $deptids[] = $user->user_dept_id;
        }

        foreach ($ifids as $key=>$ifid)
        {
            $deptid = $deptids[$key];
            $form   = new Qss_Model_Form();
            $form->initData($ifid, $deptid);
            $check  = $this->b_fCheckRightsOnForm($form,4);

//            if($check)
//            {
                $service = $this->services->Form->Request($form, $stepno, $user, $comment);
//            }
            //echo '<pre>'; var_dump($check);
        }

//        die;
//        if($check && count($ifids) > 1)
//        {
//            $service->setError(false);
//        }
//        if($check)
//        {
//            echo $service->getMessage();
//        }
    }
}