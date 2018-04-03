<?php

/**
 * Class Qss_Service_Maintenance_GeneralPlan_Delete
 * Class giup xoa toan bo ke hoach tong the
 * Hoac xoa chi tiet cua ke hoach tong the, (Uu tien xoa ke hoach)
 */
class Qss_Service_Maintenance_GeneralPlan_Delete extends Qss_Service_Abstract
{
    public function __doExecute($generalIFIDArr = array(), $detailIFIDArr = array())
    {
        // Xoa ke hoạch tông the va chi tiet cua ke hoach
        $mGeneralPlan = new Qss_Model_Maintenance_GeneralPlans();

        if( (!is_array($generalIFIDArr) || !count($generalIFIDArr)) &&
            (!is_array($detailIFIDArr) || !count($detailIFIDArr)))
        {
            return;
        }

        if(is_array($generalIFIDArr) && count($generalIFIDArr))
        {
            foreach($generalIFIDArr as $subIFID)
            {
                if(!$this->isError())
                {
                    $detail  = $mGeneralPlan->getDetailPlanByGeneralIFID($subIFID);

                    foreach($detail as $item)
                    {
                        if(!$this->isError())
                        {
                            $service =  $this->services->Form->Remove('M837', $item->IFID_M837, array());

                            if($service->isError())
                            {
                                $this->setError();
                                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                            }
                        }
                    }


                    if(!$this->isError())
                    {
                        $service =  $this->services->Form->Remove('M838', $subIFID, array());

                        if($service->isError())
                        {
                            $this->setError();
                            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                    }
                }
            }
        }
        elseif(is_array($detailIFIDArr) && count($detailIFIDArr))
        {
            foreach($detailIFIDArr as $subIFID)
            {
                if(!$this->isError())
                {
                    $service =  $this->services->Form->Remove('M837', $subIFID, array());

                    if($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }

    }
}