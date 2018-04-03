<?php
class Qss_Bin_Validation_M758_Step2 extends Qss_Lib_WValidation
{
	public function next()
	{
		parent::init();
//		$item    = new Qss_Model_Extra_Products();
//                $common  =new Qss_Model_Extra_Extra();
//		$eq      = $common->getTable(array('*'), 'ODanhSachThietBi'
//                                , array('MaThietBi'=>$this->_params->MaThietBi), array(), 1);
//                // @todo: $eq can phai co du lieu thi moi thuc hien
//                //SELECT * FROM ODanhSachThietBi WHERE MaThietBi = %1$s
//		$thaoLap = $this->_params->OThaoLap;
//                $BOM     = $common->getTable(array('*'), 'ODanhSachPhuTung'
//                                    , array('IFID_M705'=>$eq[0]->IFID_M705), array(), 'NO_LIMIT');
//                //$item->getCauThanhThietBi($eq[0]->IFID_M705);
//		
//		$params  = array();
//		$params['ODanhSachThietBi'][0]['MaKhuVuc']    = $this->_params->MaKVMoi; 
//		$params['ODanhSachThietBi'][0]['LichLamViec'] = $this->_params->LichLamViec;
//		$params['ODanhSachThietBi'][0]['ioid']        = $eq[0]->IOID;
//		
//		$i = 0;
//		foreach ($thaoLap as $item)
//		{
//                        $params['ODanhSachPhuTung'][$i]['BoPhan']    = $item->BoPhan;
//			$params['ODanhSachPhuTung'][$i]['ViTri']     = $item->ViTri;
//			$params['ODanhSachPhuTung'][$i]['DaLapGiap'] = $item->LapDat;
//			$params['ODanhSachPhuTung'][$i]['ioid']      = $item->IOID;
//			$i++;
//		} 
//		//print_r($params);die;
//		$service = $this->services->Form->Manual('M705',$eq[0]->IFID_M705,$params,false);
//		if($service->isError())
//		{
//			$this->setError();
//			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//		}
	}
}