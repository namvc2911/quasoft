<?php

class Qss_Service_Purchase_Order_CreateOrder extends Qss_Lib_Service
{

	public function __doExecute ($params)
	{
		if(!isset($params['itemioid']) || !count($params['itemioid']))
		{
			$this->setError();
			$this->setMessage('Bạn cần chọn ít nhất một mặt hàng để tạo đơn hàng!');
			return;
		}

		if(isset($params['qty']))
		{
			$AllQtyZero = 1;

			foreach($params['qty'] as $qty)
			{
				if($qty > 0)
				{
					$AllQtyZero = 0;
				}
			}

			if($AllQtyZero == 1)
			{
				$this->setError();
				$this->setMessage('Bạn cần có ít nhất mặt hàng có số lượng lớn hơn 0 để tạo đơn hàng!');
				return;
			}
		}

		$mCommon = new Qss_Model_Extra_Extra();
		$partner = $params['partnerioid'];
		$insert  = array();
		$i       = 0;


		$insert['ODonMuaHang'][0]['MaNCC']       = @(int)$params['partnerioid'];
		$insert['ODonMuaHang'][0]['SoKeHoach']   = @(int)$params['planioid'];
		$insert['ODonMuaHang'][0]['NVMuaHang']   = $params['UserName'];
		$insert['ODonMuaHang'][0]['NgayDatHang'] = date('Y-m-d');
		$insert['ODonMuaHang'][0]['NgayYCNH']    = date('Y-m-d');

		foreach ($params['itemioid'] as $itemioid)
		{
			if($params['qty'][$i] > 0)
			{
				$insert['ODSDonMuaHang'][$i]['SoYeuCau']  = (int)$params['requestioid'][$i];
				$insert['ODSDonMuaHang'][$i]['MaSP']      = (int)$itemioid;
				$insert['ODSDonMuaHang'][$i]['DonViTinh'] = (int)$params['uomioid'][$i];
				$insert['ODSDonMuaHang'][$i]['DonGia']    = $params['unitprice'][$i]/1000;
				$insert['ODSDonMuaHang'][$i]['SoLuong']   = $params['qty'][$i];
			}
			$i++;
		}

		if(count($insert))
		{
			$service = $this->services->Form->Manual('M401',  0,  $insert, false);
			if ($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}

			if(!$this->isError())
			{
				$ifid    = $service->getData();
				$donHang = $mCommon->getTableFetchOne('ODonMuaHang', array('IFID_M401'=>$ifid));

				if($donHang)
				{
					$this->setMessage(
						'Đơn mua hàng "'
						.$donHang->SoDonHang
						.'" với nhà cung cấp "'
						. $donHang->MaNCC
						. '" đã được tạo! '
					);
				}

			}
		}
	}
}
?>