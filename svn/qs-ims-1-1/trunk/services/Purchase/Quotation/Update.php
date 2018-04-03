<?php

class Qss_Service_Purchase_Quotation_Update extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
		$inserts = array();
		$i       = 0;
		$k       = 0;

		if(isset($params['invalid']) && count($params['invalid']))
		{
			foreach($params['invalid'] as $invalid)
			{
				$inserts[$params['quoteifidformain'][$k]]['OBaoGiaMuaHang'][0]['KhongHopLe'] = $invalid;
				$k++;
			}
		}

	    if(isset($params['price']) && count($params['price']))
	    {
	        foreach($params['price'] as $price)
	        {
	            $quoteIFID     = isset($params['quoteifid'][$i])?$params['quoteifid'][$i]:0;
	            $quoteLineIOID = isset($params['quotelineioid'][$i])?$params['quotelineioid'][$i]:0;
	            
	            if(!isset($j[$quoteIFID]))
	            {
	                $j[$quoteIFID] = 0;
	            }
	            
	            if($quoteIFID)
	            {
                    $khongChaoGia = ($price == 0);
	                $inserts[$quoteIFID]['ODSBGMuaHang'][$j[$quoteIFID]]['DonGia']   = $price;
					$inserts[$quoteIFID]['ODSBGMuaHang'][$j[$quoteIFID]]['ThoiGian'] = $params['days'][$i];
	                $inserts[$quoteIFID]['ODSBGMuaHang'][$j[$quoteIFID]]['ioid']     = $quoteLineIOID;
					$inserts[$quoteIFID]['ODSBGMuaHang'][$j[$quoteIFID]]['KhongChaoGia'] = $khongChaoGia;
                    $inserts[$quoteIFID]['ODSBGMuaHang'][$j[$quoteIFID]]['KyThuat']  = $khongChaoGia?0:$params['pass'][$i];
	                $j[$quoteIFID]++;
	            }
	            
	            $i++;
	        }
	    }

		if(count($inserts))
		{
//			echo '<pre>'; print_r($inserts); die;
			foreach($inserts as $quoteIFID=>$insert)
			{
				if(!$this->isError())
				{
					$service = $this->services->Form->Manual('M406',  $quoteIFID,  $insert, false);

					if ($service->isError())
					{
						$this->setError();
						$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
					}
				}
			}
		}
	}
}