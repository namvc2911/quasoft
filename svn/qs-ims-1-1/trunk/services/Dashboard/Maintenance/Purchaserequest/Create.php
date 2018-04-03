<?php

class Qss_Service_Dashboard_Maintenance_Purchaserequest_Create extends Qss_Service_Abstract
{
/**/
	public function __doExecute ($params)
	{
		if(!$this->isError())
		{
			$i = 0;
			$ifid   = $params['ifid']; // ifid cua nhu cau vat tu
			$common = new Qss_Model_Extra_Extra();
			$request = new Qss_Model_Maintenance_Request();
			$main   = $common->getTable(array('*'), 'ONhuCauVatTu', array('IFID_M709'=>$ifid), array(), 'NO_LIMIT', 1);
			$sub    = $request->getMaterialRequestDetailByIFID($ifid);
			$insert = array();
			
			$insert['OKeHoachMuaSam'][0]['NgayYeuCau']    = date('d-m-Y');
			$insert['OKeHoachMuaSam'][0]['NoiDung'] = $main->SoPhieu;
			$insert['OKeHoachMuaSam'][0]['ioidlink'] = $main->IOID;
			
			foreach($sub as $dat)
			{
				$insert['ODSKeHoachMuaSam'][$i]['MaSP'] = $dat->MaSP;
				$insert['ODSKeHoachMuaSam'][$i]['DonViTinh'] = $dat->DonViTinh;
				$insert['ODSKeHoachMuaSam'][$i]['NhuCauPhatSinh'] = $dat->SoLuong;
				$insert['ODSKeHoachMuaSam'][$i]['MucDich'] = $dat->MucDich;
				$insert['ODSKeHoachMuaSam'][$i]['DiemDo']  = $dat->DiemDo;
				$insert['ODSKeHoachMuaSam'][$i]['TonKho']  = $dat->TonKho;
				$insert['ODSKeHoachMuaSam'][$i]['SoLuongYeuCau'] = $dat->SoLuongCanMua;
				$i++;
			}
			if(count($insert))
			{
				$service = $this->services->Form->Manual('M716', 0 , $insert ,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
				else
				{
					$service->setRedirect('/user/form/edit?ifid='.$service->getData().'&deptid='.$params['deptid']);
				}
			}
		}

	}

}
?>