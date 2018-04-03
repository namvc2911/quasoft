<?php
// Luu cac yeu cau chua luu vao phien cua user vao phien xu ly mua hang
// Sau khi luu chuyen sang tinh trang duyet
class Qss_Service_Purchase_Order_SaveRequests extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    $insert       = array();
        $i            = 0;
        $ifidArr      = array();
        $mOrder       = new Qss_Model_Purchase_Order();
        $line         = $mOrder->getSessionLineByIFID($params['sessionifid']);

        if($this->isError())
        {
            return;
        }

        if(count($line))
        {
            $removeArr = array();

            foreach($line as $item)
            {
                $removeArr['OYeuCauPhienXLMH'][] = $item->IOID;
            }


            $remove = $this->services->Form->Remove('M415', $params['sessionifid'], $removeArr);

            if ($remove->isError())
            {
                $this->setError();
                $this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }


        foreach($params['RequestIOID'] as $RequestIOID)
        {
            if($params['CheckBox'][$i])
            {
                $ifidArr[]                                 = $params['RequestIFID'][$i];
                $insert['OYeuCauPhienXLMH'][$i]['SoPhieu'] = (int)$RequestIOID;
                $insert['OYeuCauPhienXLMH'][$i]['Chon']    = (int)1;

                if($params['SessionLineIOID'][$i])
                {
                    $insert['OYeuCauPhienXLMH'][$i]['IOID'] = $params['SessionLineIOID'][$i];
                }
            }
            $i++;
        }


        //$this->setError();
        //echo '<pre>'; print_r($insert); die;

        if(count($insert))
        {
            $service = $this->services->Form->Manual('M415',  $params['sessionifid'],  $insert, false);
            
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
            
            foreach ($ifidArr as $ifid)
            {
                $form = new Qss_Model_Form();
                $form->initData($ifid, $params['DeptID']);
                $service2 = $this->services->Form->Request($form, 2, $params['User'], '');
                
                if ($service2->isError())
                {
                    $this->setError();
                    $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }                
            }
        }
	}
}