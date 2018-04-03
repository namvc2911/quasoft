<?php
class Qss_Service_Purchase_Order_ResetSession extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    $valid       = true;
	    $sessionIFID = @(int)$params['sessionifid'];
	    $db          = Qss_Db::getAdapter('main');
	    $db->beginTransaction(); // Khoi tao mot transaction cua db
	    
	    // Kiem tra ke hoach da duoc duyet het chua
	    $mPlan = new Qss_Model_Purchase_Plan();
		$mReq  = new Qss_Model_Purchase_Request();
	    $plan  = $mPlan->getPlanBySession($sessionIFID);
		$req   = $mReq->getRequestsInfoBySession($sessionIFID);
	    
        if($plan && $plan->Status == 2) // da duoc phe duyet
        {
            $this->setMessage('Kế hoạch '.$plan->SoPhieu.' đã được phê duyệt bạn không thể làm lại!');
            $this->setError();
        }
        
	    // Kiem tra don hang da duoc duyet het chua
	    $mOrder = new Qss_Model_Purchase_Order();
	    $orders = $mOrder->getOrdersBySession($sessionIFID);
	    
	    foreach($mOrder as $item)
	    {
	        if($item->Status == 2)
	        {
	            $this->setMessage('Đơn hàng '.$item->SoDonHang.' đã đặt hàng bạn không thể làm lại!');
	            $this->setError();	            
	        }
	        elseif($item->Status == 4)
	        {
	            $this->setMessage('Đơn hàng '.$item->SoDonHang.' đã kết thúc bạn không thể làm lại!');
	            $this->setError();	            
	        }
	    }
	    
	    // Kiem tra bao gia da duoc duyet het chua
	    $mQuotation = new Qss_Model_Purchase_Quotation();
	    $quotes     = $mQuotation->getQuotationBySession($sessionIFID);
	    
	    foreach($quotes as $item)
	    {
	        if($item->Status == 2)
	        {
	            $this->setMessage('Báo giá '.$item->SoChungTu.' đã được phê duyệt bạn không thể làm lại!');
	            $this->setError();	            
	        }
	    }
	    
	    
	    if(!$this->isError())
	    {
			// Chuyen tinh trang yeu cau ve soan thao
			foreach ($req as $item)
			{
				$form = new Qss_Model_Form();
				$form->initData($item->IFID_M412, $params['DeptID']);
				$updateStep = $this->services->Form->Request($form, 1, $params['User'], '');

				if ($updateStep->isError())
				{
					$this->setError();
					$this->setMessage($updateStep->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}


	        // Xoa don hang chua duoc duyet <can kiem tra truoc> = Xoa ke hoach o buoc duoi + Xoa don hang
	        if(count($orders))
	        {
	            // Xoa don hang <He thong se canh bao neu don hang bao gom cac yeu cau ko nam trong phien>
	            foreach($orders as $item)
	            {
	                if(!$this->isError())
	                {
	                    $deleteOrder = $this->services->Form->Remove('M401', $item->IFID_M401, false);
	                     
	                    if($deleteOrder->isError())
	                    {
	                        $this->setError();
	                        $this->setMessage($deleteOrder->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	                    }
	                }
	            }
	        }	     

	        // Xoa bao gia chua duoc duyet <can kiem tra trươc>	= Xoa ke hoach obuoc duoi + Xoa bao gia
	        if(count($quotes))
	        {
	            foreach ($quotes as $item)
	            {
	                if(!$this->isError())
	                {
	                    $deleteQuote = $this->services->Form->Remove('M406', $item->IFID_M406, false);
	        
	                    if($deleteQuote->isError())
	                    {
	                        $this->setError();
	                        $this->setMessage($deleteQuote->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	                    }
	                }
	            }
	        }	        
	        
	        // Xoa ke hoach chua duoc duyet <can kiem tra truoc> = Update trang ke hoach trong phien + xoa ke hoach
	        if($plan)
	        {
	            if(!$this->isError() && $sessionIFID)
	            {
	                // Update ke hoach trong phien ve tragn <Loai bo lookup>
	                $update['OPhienXuLyMuaHang'][0]['SoKeHoach'] = (int)0;
                    $update['OPhienXuLyMuaHang'][0]['Buoc']      = 1;
                    $update['OPhienXuLyMuaHang'][0]['BuocXuLy']  = 1;
	                $updateSessionPlan  = $this->services->Form->Manual('M415',  $sessionIFID,  $update, false);
	                
	                if ($updateSessionPlan->isError())
	                {
	                    $this->setError();
	                    $this->setMessage($updateSessionPlan->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	                }	                
	            }

	            // Xoa ke hoach
	            if(!$this->isError())
	            {
        	        $deletePlan = $this->services->Form->Remove('M716', $plan->IFID_M716, false);
        	        
        	        if($deletePlan->isError())
        	        {
        	            $this->setError();
        	            $this->setMessage($deletePlan->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        	        }		                
	            }
	        }
	    }
	    
	    // Rollback or commit
	    if(!$this->isError())
	    {
	        $db->commit();
	    }
	    else
	    {
	        $db->rollback();
	    }
	}
}