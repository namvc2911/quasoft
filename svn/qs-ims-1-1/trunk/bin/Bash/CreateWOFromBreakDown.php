<?php
/**
 * Class Qss_Bin_Bash_CreateWOFromBreakDown
 */
class Qss_Bin_Bash_CreateWOFromBreakDown extends Qss_Lib_Bin
{
	public function __doExecute()
	{
//		$sql = sprintf('select * from OPhieuBaoTri where Ref_PhieuSuCo = %1$d',$this->_params->IOID);
//		$dataSQL = $this->_db->fetchOne($sql);
//		$popup = $this->requests->getParam('popup',0);
//		if(!$dataSQL)
//		{
//			$sql = sprintf('select * FROM OPhanLoaiBaoTri WHERE LoaiBaoTri = "%1$s"', Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN);
//			$dataSQL = $this->_db->fetchOne($sql);
//
//			$insert = array();
//
//			// Main Obj
//			$insert['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int) $dataSQL->IOID;
//			$insert['OPhieuBaoTri'][0]['NgayYeuCau'] 	      = $this->_params->NgayYeuCau;
//			$insert['OPhieuBaoTri'][0]['NgayBatDauDuKien']    = $this->_params->NgayBatDau;
//			$insert['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $this->_params->Ngay;
//			$insert['OPhieuBaoTri'][0]['KhuVuc']  		      = $this->_params->KhuVuc;
//			$insert['OPhieuBaoTri'][0]['MaThietBi']  	      = (int)$this->_params->Ref_MaThietBi;
//			$insert['OPhieuBaoTri'][0]['BoPhan']  	          = (int)$this->_params->Ref_BoPhan;
//			$insert['OPhieuBaoTri'][0]['MaKhuVuc']  	      = (int)$this->_params->Ref_MaKhuVuc;
//			$insert['OPhieuBaoTri'][0]['MucDoUuTien']         = $this->_params->MucDoUuTien;
//			$insert['OPhieuBaoTri'][0]['MaDVBT']              = (int)$this->_params->Ref_MaDVBT;
//			$insert['OPhieuBaoTri'][0]['PhieuSuCo']           = (int)$this->_params->IOID;
//			$insert['OPhieuBaoTri'][0]['PhieuYeuCau']         = (int)$this->_params->Ref_SoYeuCau;
//			$insert['OPhieuBaoTri'][0]['MoTa']                = $this->_params->MoTa;
//
//			$service = $this->services->Form->Manual('M759' ,0,$insert,false);
//
//			if($service->isError())
//			{
//				$this->setError();
//				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//			}
//			else
//			{
//				if($popup)
//				{
//					Qss_Service_Abstract::$_redirect = '/user/form/popup?ifid='.$service->getData().'&deptid=1&popup=1';
//				}
//				else
//				{
//					Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$service->getData().'&deptid=1';
//				}
//			}
//		}
//		else
//		{
//			if($popup)
//			{
//				Qss_Service_Abstract::$_redirect = '/user/form/popup?ifid='.$dataSQL->IFID_M759.'&deptid=1&popup=1';
//			}
//			else
//			{
//				Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$dataSQL->IFID_M759.'&deptid=1';
//			}
//		}
	}
}
?>
