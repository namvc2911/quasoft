<?php
class Qss_Service_Purchase_Order_CreateOrderFromQuotes extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
        // echo '<pre>'; print_r($params); die;
	    // Var
	    $j = 0;
	    $k = 0;
	    $donGia   = array();
	    $doiTac   = array();
        $ngayGiao = array();
	    $loaiTien = array();
	    $inserts  = array();
		$insertBaoGia = array();
		$indexBaoGia  = array();
		$indexBaoGia2 = 0;
	    
	    // Validate
	    if(!isset($params['itemioid2']) || !count($params['itemioid2']))
	    {
	        $this->setError();
	        $this->setMessage('Bạn cần chọn ít nhất một mặt hàng để tạo đơn hàng!');
	        return;
	    }
        
	    if(!isset($params['itemioid']) || !count($params['itemioid']))
	    {
	        $this->setError();
	        $this->setMessage('Chưa có kế hoạch mua sắm!');
	        return;
	    }

	    
	    // Lay don gia
	    foreach($params['itemioid2'] as $itemioid2)
	    {
	        $donGia[$itemioid2][$params['uomioid2'][$j]]   = $params['unitprice2'][$j];
	        $doiTac[$itemioid2][$params['uomioid2'][$j]]   = $params['partnerioid2'][$j];
            $ngayGiao[$itemioid2][$params['uomioid2'][$j]] = $params['delivery2'][$j];
	        $loaiTien[$params['partnerioid2'][$j]]         = $params['currency2'][$j];
	        $j++;
	    }
	    
	    foreach($params['itemioid'] as $itemioid)
	    {
	        $donGiaTemp   = isset($donGia[$itemioid][$params['uomioid'][$k]])?$donGia[$itemioid][$params['uomioid'][$k]]:0;
	        $doiTacTemp   = isset($doiTac[$itemioid][$params['uomioid'][$k]])?$doiTac[$itemioid][$params['uomioid'][$k]]:0;
            $ngayGiaoTemp = isset($ngayGiao[$itemioid][$params['uomioid'][$k]])?$ngayGiao[$itemioid][$params['uomioid'][$k]]:0;
	        $loaiTienTemp = isset($loaiTien[$doiTacTemp])?$loaiTien[$doiTacTemp]:0;
	        
	        if($doiTacTemp)
	        {
	            if(!isset($inserts[$doiTacTemp]))
	            {
	                $i = 0;
	                $inserts[$doiTacTemp]['ODonMuaHang'][0]['MaNCC']       = @(int)$doiTacTemp;
	                $inserts[$doiTacTemp]['ODonMuaHang'][0]['SoKeHoach']   = @(int)$params['planioid'];
	                $inserts[$doiTacTemp]['ODonMuaHang'][0]['LoaiTien']    = $loaiTienTemp;
	                $inserts[$doiTacTemp]['ODonMuaHang'][0]['NVMuaHang']   = $params['UserName'];
	                $inserts[$doiTacTemp]['ODonMuaHang'][0]['NgayDatHang'] = date('Y-m-d');
	                $inserts[$doiTacTemp]['ODonMuaHang'][0]['NgayYCNH']    = date('Y-m-d');	                
	            }
	            
	            $inserts[$doiTacTemp]['ODSDonMuaHang'][$i]['SoYeuCau']     = (int)$params['requestioid'][$k];
	            $inserts[$doiTacTemp]['ODSDonMuaHang'][$i]['MaSP']         = (int)$itemioid;
	            $inserts[$doiTacTemp]['ODSDonMuaHang'][$i]['DonViTinh']    = (int)$params['uomioid'][$k];
	            $inserts[$doiTacTemp]['ODSDonMuaHang'][$i]['DonGia']       = $donGiaTemp;
	            $inserts[$doiTacTemp]['ODSDonMuaHang'][$i]['SoLuong']      = $params['qty'][$k];
                $inserts[$doiTacTemp]['ODSDonMuaHang'][$i]['NgayGiaoHang'] = $ngayGiaoTemp?date('Y-m-d', strtotime("+{$ngayGiaoTemp} days")):date('Y-m-d');
                $i++;
	        }
	        $k++;
	    }

		// echo '<pre>'; print_r($inserts); die;
	    
	    foreach($inserts as $insert)
	    {
	        if(count($insert) && !$this->isError())
	        {
	            $service = $this->services->Form->Manual('M401',  0,  $insert, false);
	            if ($service->isError())
	            {
	                $this->setError();
	                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	            }
	        }
	    }


	    
	}
}