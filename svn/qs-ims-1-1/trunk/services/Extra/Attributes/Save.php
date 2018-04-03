<?php
Class Qss_Service_Extra_Attributes_Save extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		$validate = $this->validatex($params);
		if($validate === true)
		{
			$model      = new Qss_Model_Extra_Products();
			$extra      = new Qss_Model_Extra_Extra();
			$attrConfig = $model->getItemAttributesConfig($params['product']);
			$insert     = array();
			$attr       = isset($params['attr'])?$params['attr']:array();
			$subline    = 0;
			
			$insert['OBangThuocTinh'][0]['MaSP']        = $params['product'];
			$insert['OBangThuocTinh'][0]['MaThuocTinh'] = $params['attrCode'];
                        $insert['OBangThuocTinh'][0]['DonViTinh']   = @$params['uom'];
                        $insert['OBangThuocTinh'][0]['SoLuong']     = @$params['qty'];
			
			foreach ($attr as $key=>$val) 
			{
				$insert['OGiaTriBTT'][$subline]['MaThuocTinh'] = $key;
				$insert['OGiaTriBTT'][$subline]['GiaTri']      = $val;
				$subline++;
			}
			
			$service = $this->services->Form->Manual('M119',0,$insert,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			
			
			$ifid     = $service->getData();
			$ioid     = $extra->getTable(array('IOID'), 'OBangThuocTinh', 
							             array('IFID_M119'=>$ifid), array(),1, 1);
			$ioidF    = isset($ioid->IOID)?$ioid->IOID:0;				             
							
			$recordID = $extra->getTable(array('RecordID'), 'qsrecobjects', 
							             array('FieldCode'=>877, 'IOID'=> $ioidF),
							             array(), 1 , 1);
							             
			$valueF = isset($recordID->RecordID)?$recordID->RecordID:0;
			$status   = array('value'=> $valueF.','.$ioidF, 
							  'display'=>$params['attrCode'],
							  'confirm'=> false);//value display
			$this->setStatus($status);
			
		}
		elseif($validate === false) 
		{
			$this->setError();
		}
		else 
		{
			$this->setStatus($validate);
		}
	}
	
	private function validatex($params)
	{
		$retval     = true;
		$model      = new Qss_Model_Extra_Products();
		$extra      = new Qss_Model_Extra_Extra();
		$attrConfig = $model->getItemAttributesConfig($params['product']); /* Mang cau hinh thuoc tinh san pham*/
		$config     = $this->sortConfigAttrOfOneProduct($attrConfig); /* Mang cau hinh duoc sap xep theo ma thuoc tinh */
		$attr       = isset($params['attr'])?$params['attr']:array();
		$break      = true; /* Tach bao loi ve ma thuoc tinh */
		$pattern    = '/^[a-zA-Z0-9]+$/';/* Ma thuoc tinh ko chua cac ky tu dac biet*/
                
                
                /* So luong yeu cau bat buoc */
                if(isset($params['qty']) && (!$params['qty'] || $params['qty'] < 0 || !is_numeric($params['qty'])) )
                {
                    $this->setMessage($this->_translate(13)); /*" Mã thuộc tính yêu cầu bắt buộc."*/
                    $break  = false;
                    $retval = false;                   
                }
                
		/* Ma thuoc tinh yeu cau bat buoc */
		if( !$params['attrCode'])
		{
			$this->setMessage($this->_translate(1)); /*" Mã thuộc tính yêu cầu bắt buộc."*/
			$break  = false;
			$retval = false;
		}
		
//		/* Ma thuoc tinh da ton tai hay chua */
//		$exists = $extra->getTable(array('1'), 'OBangThuocTinh',
//								   array('MaSP'=>$params['product'],'MaThuocTinh'=>$params['attrCode']),
//							 	   array(), 1
//							 );
//							 
//		if( $break &&  $exists )
//		{
//			$this->setMessage($this->_translate(2)); /* " Mã thuộc tính đã tồn tại. */
//			$break  = false;
//			$retval = false;
//		}
		
		/* Ma thuoc tinh chua ky tu dac biet 
		if($break && !preg_match($pattern, $params['attrCode']))
		{
			$this->setMessage($this->_translate(3)); 
			$break  = false;
			$retval = false;			
		}
		*/

		/* Gia tri thuoc tinh da ton tai */
//		$existsVal = $model->checkAttrValueExists($attr, $params['product']);
//		if($break  && $existsVal )
//		{
//			/*" Đã tồn tại mã thuộc tính với các giá trị này. Bạn có thể chọn mã*/
//			$ifid     = $existsVal->IFID_M119;
//			$ioid     = $existsVal->BTTIOID;
//							
//			$recordID = $extra->getTable(array('RecordID'), 'qsrecobjects', 
//										 array('sFieldID'=>877, 'IOID'=> $ioid),
//							             array(), 1 );
//			$status   = array('value'=> $recordID->{'0'}->RecordID.','.$ioid, 
//							  'display'=> $existsVal->MaThuocTinh,
//							  'confirm'=> true);//value display
//			$retval   = $status;
//		}	
				
		foreach ($attr as $key=>$val) {
			$val = ($val == '')?0:$val; /* reset val */
			/* Thuoc tinh nao bat buoc chua co gia tri hoac bang khong */
			if( $config[$key]['Required'] && !$config[$key]['Checkbox'] &&  !$val)
			{
				$this->setMessage(" {$this->_translate(6)} \"{$config[$key]['Name']}\" {$this->_translate(7)}");
				$retval = false;
			}
			
			/* Thuoc tinh nao kieu so nhung lai chu ky tu khac */
			if( $config[$key]['Number'] &&  !is_numeric($val))
			{
				$this->setMessage(" {$this->_translate(8)} \"{$config[$key]['Name']}\" {$this->_translate(9)}");
				$retval = false;
			}			
			
			/* Co cong thuc nao chua tinh ra ket qua 
			if( $config[$key]['Number'] && $config[$key]['Formula'] &&  !is_numeric($val) )
			{
				$this->setMessage(" {$this->_translate(10)} \"{$config[$key]['Name']}\" {$this->_translate(11)}");
				$retval = false;
			}	
			*/
		}
		
		/* Cap nhat thanh cong */
		if($retval)
		{
			$this->setMessage($this->_translate(12));
		}

		return $retval;
	}
	
	/**
	 * Function: Sap xep cau hinh thuoc tinh cua san pham.
	 */
	private function sortConfigAttrOfOneProduct($attr)
	{
		$retval = array();
		foreach ($attr as $val) 
		{
			$retval[$val->MaThuocTinh]['Code']     = $val->MaThuocTinh;	
			$retval[$val->MaThuocTinh]['Name']     = $val->ThuocTinh;	
			$retval[$val->MaThuocTinh]['Required'] = $val->BatBuoc;	
			$retval[$val->MaThuocTinh]['Number']   = $val->KieuSo;	
			$retval[$val->MaThuocTinh]['Active']   = $val->HoatDong;	
			$retval[$val->MaThuocTinh]['Formula']  = $val->CongThuc;	
			$retval[$val->MaThuocTinh]['Checkbox'] = $val->Checkbox;
		}
		return $retval;
	}
	/* End Function: Sap xep cau hinh thuoc tinh cua san pham.*/
}