<?php
class Qss_Bin_Trigger_OKeHoachGiaoHang extends Qss_Lib_Trigger
{
	/**
	 * Ngay xuat va giao hang phai trong khoang tu ngay dat hang den ngay yeu cau
	 */
	public function onUpdate($object)
	{
		parent::init();
		$this->checkDateAvailable($object);
	}
	/**
	 * Ngay xuat va giao hang phai trong khoang tu ngay dat hang den ngay yeu cau
	 */
	public function onInsert($object)
	{
		parent::init();
		$this->checkDateAvailable($object);
	}
	
	/**
	 * Ngay xuat va giao hang phai trong khoang tu ngay dat hang den ngay yeu cau
	 */
	private function checkDateAvailable($object)
	{
		if($object->FormCode == 'M505')
		{
			$issueDateAlias    = 'NgayXuatHang';
			$deliveryDateAlias = 'NgayGiaoHang';
			$orderDateAlias    = 'NgayDatHang';
			$requiredDateAlias = 'NgayYCNH';
			
		}
		elseif($object->FormCode == 'M403')
		{
			$issueDateAlias    = 'NgayXuatHang';
			$deliveryDateAlias = 'NgayGiaoHang';
			$orderDateAlias    = 'NgayYeuCau';
			$requiredDateAlias = 'NgayTraHang';		
		}
		
		// Ngay xuat hang bang so co the so sanh
		$issueDate    = Qss_Lib_Date::i_fString2Time($object->getFieldByCode($issueDateAlias)->getValue());
		// Ngay giao hang bang so co the so sanh
		$deliveryDate = Qss_Lib_Date::i_fString2Time($object->getFieldByCode($deliveryDateAlias)->getValue());
		$orderDate    = Qss_Lib_Date::i_fMysql2Time($this->_params->$orderDateAlias);// Ngay dat hang bang so co the so sanh
		$requiredDate = Qss_Lib_Date::i_fMysql2Time($this->_params->$requiredDateAlias);// Ngay yeu cau bang so co the so sanh
		
		// Ngay xuat hang nam trong khoang tu ngay dat hang den ngay yeu cau
		if( $issueDate < $orderDate || $issueDate > $requiredDate )
		{
			$this->setMessage($this->_translate(1));
			$this->setError();
		}
		
		// Ngay giao hang nam trong khoang tu ngay dat hang den ngay yeu cau
		if( $deliveryDate < $orderDate || $deliveryDate > $requiredDate )
		{
			$this->setMessage($this->_translate(2));
			$this->setError();
		}
		
		// Ngay giao hang phai lon hon hoac bang ngay xuat hang
		if( $deliveryDate < $issueDate )
		{
			$this->setMessage($this->_translate(3));
			$this->setError();
		}
			
	}
}