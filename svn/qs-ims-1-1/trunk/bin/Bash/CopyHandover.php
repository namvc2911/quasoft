<?php
/**
 * @author Thinh Tuan
 * @date 20/10/2014
 * @description nhân đôi phiếu bàn giao (Không bao gồm chi tiết)
 */
class Qss_Bin_Bash_CopyHandover extends Qss_Lib_Bin {
	public function __doExecute() {
		$common   = new Qss_Model_Extra_Extra();
		$insert   = array();
		$main     = $common->getTableFetchOne('OPhieuBanGiaoTaiSan', array('IFID_M182'=>$this->_params->IFID_M182));
        $object   = new Qss_Model_Object(); $object->v_fInit('OPhieuBanGiaoTaiSan', 'M182');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth(5);
        $document->setDocField('SoPhieu');;
        $document->setPrefix($main->NhaMay.'BG.');

		$insert['OPhieuBanGiaoTaiSan'][0]['NhaMay']      = $main->NhaMay;
        $insert['OPhieuBanGiaoTaiSan'][0]['SoPhieu']     = $document->getDocumentNo();
        $insert['OPhieuBanGiaoTaiSan'][0]['Ngay']        = $main->Ngay;
        $insert['OPhieuBanGiaoTaiSan'][0]['PhieuThuHoi'] = $main->PhieuThuHoi;
        $insert['OPhieuBanGiaoTaiSan'][0]['DienGiai']    = $main->DienGiai;

		$service = $this->services->Form->Manual('M182' , 0, $insert, false);

		if($service->isError()) {
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}

		if(!$this->isError()) {
			$service->setRedirect('/user/form/edit?ifid='.$service->getData().'&deptid=1');
		}
	}
	
}