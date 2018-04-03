<?php
class Qss_Service_Maintenance_WorkOrder_Task_Complete extends Qss_Lib_Service
{
    public function __doExecute($params)
    {
        $insert      = array();
        $insertID    = 0;
        $mCommon     = new Qss_Model_Extra_Extra();
        $tasks       = $mCommon->getTableFetchAll('OCongViecBTPBT', array('IFID_M759'=>$params['ifid']));

        if(Qss_Lib_System::fieldActive('OCongViecBTPBT', 'ThucHien'))
        {
            foreach($tasks as $item)
            {
                $insert['OCongViecBTPBT'][$insertID]['ThucHien'] = 1;
                $insert['OCongViecBTPBT'][$insertID]['ioid']     = $item->IOID;
                $insertID++;
            }

            if(count($insert))
            {
                $services =  $this->services->Form->Manual('M759'
                    , $params['ifid']
                    , $insert
                    , false);

                if($services->isError())
                {
                    $this->setError();
                    $this->setMessage($services->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            if(!$this->isError())
            {
                $form = new Qss_Model_Form();
                $form->initData($params['ifid'], $params['user']->user_dept_id);
                $service2 = $this->services->Form->Request($form, 3, $params['user'] , '');

                if ($service2->isError())
                {
                    $this->setError();
                    $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }
        }
    }
}