<?php
// @todo : Lỗi không save được trường hợp chỉ có thuộc tính và không có thuộc tinh
// Có lẽ do unique
Class Qss_Service_Extra_Mrp_Primary_Save extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		if($this->validate($params))
		{
			$this->save($params);
			$this->setMessage('Cập nhật thành công');
		}
		else
		{
			$this->setError();
		}
	}
	
	private function save($params)
	{
		$line  = isset($params['lineNo'])?$params['lineNo']:array();
		$data  = array();
		$model = new Qss_Model_Extra_Mrp();
		$index = 0;
		$lastestDate = '';
		$delete = array();
		$common = new Qss_Model_Extra_Extra();
		
		foreach ($common->getTable(array('*'), 'OKeHoachSanPham', array('IFID_M901'=>$params['ifid']), array(), 'NO_LIMIT') as $item)
		{
			$delete['OKeHoachSanPham'][] = $item->IOID;
		}
		
		
		for($no = 0; $no < count($line); $no++)
		{
	
				if(isset($params['itemCode'][$no]))
				{
					$data['OKeHoachSanPham'][$index]['MaSP']      = $params['itemCode'][$no];
                                        $data['OKeHoachSanPham'][$index]['DonViTinh'] = $params['uom'][$no];
					$data['OKeHoachSanPham'][$index]['ThuocTinh'] = $params['attributes'][$no];
					$data['OKeHoachSanPham'][$index]['SoLuongSX'] = $params['productionQty'][$no];
					$data['OKeHoachSanPham'][$index]['SoLuongMH'] = $params['purchaseQty'][$no];
					$data['OKeHoachSanPham'][$index]['NgayBatDau']  = $params['sDate'][$no];
					$data['OKeHoachSanPham'][$index]['NgayKetThuc'] = $params['eDate'][$no];
					$data['OKeHoachSanPham'][$index]['KhauTruKho'] = $params['stockUsedQty'][$no];
					$data['OKeHoachSanPham'][$index]['BOM']       = $params['bom'][$no];
					$data['OKeHoachSanPham'][$index]['Level']     = $params['level'][$no];
					$data['OKeHoachSanPham'][$index]['No'] = $params['no'][$no];
					$data['OKeHoachSanPham'][$index]['ioidlink'] = $params['khioid'][$no];
					$lastestDate = $params['issueDate'][$no];
					$index++;
				}
				//MaSP SanPham ThuocTinh DonViTinh SoLuongSX SoLuongMH NgayBatDau NgayKetThuc BOM
				//itemName itemCode refItem attributes refAttributesReal bom refBom productionQty purchaseQty
		}
		
		
		if(count($data))
		{
			if(count($delete))
			{
				$remove = $this->services->Form->Remove($params['module'], $params['ifid'], $delete);	
				if($remove->isError())
				{
					$this->setError();
					$this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			$service = $this->services->Form->Manual($params['module'], $params['ifid'], $data,false);			
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			
			// update ngay ket thuc
			//$insert['OKeHoachCungUng'][0]['NgayKetThuc'] = $lastestDate;
			//$service = $this->services->Form->Manual($params['module'] , $params['ifid'], $insert, false);
			//if($service->isError())
			//{
			//	$this->setError();
			//	$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			//}
		}
			
	}
	
	// @todo : có phải kiểm tra xem có thuộc tính bắt buộc hay không ?
	private function validate($params)
	{
		$return = true;
		$model  = new Qss_Model_Extra_Mrp();
		return $return;
	}
}