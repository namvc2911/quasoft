<?php
/**
 * Nhân đôi phiếu thu hồi tài sản (Không bao gồm chi tiết phiếu)
 */
class Qss_Bin_Bash_CopyClawback extends Qss_Lib_Bin {
	public function __doExecute() {
        $user    = Qss_Register::get('userinfo');
        $common  = new Qss_Model_Extra_Extra();
        $mAsset  = new Qss_Model_Maintenance_Asset();
        $mImport = new Qss_Model_Import_Form('M183',false, false);
        $main    = $common->getTableFetchOne('OPhieuThuHoiTaiSan', array('IFID_M183'=>$this->_params->IFID_M183));
        $insert  = array();

        if($main) {
            $insert['OPhieuThuHoiTaiSan'][0]['NhaMay']   = $main->Ref_NhaMay;
            $insert['OPhieuThuHoiTaiSan'][0]['SoPhieu']  = $mAsset->getClawbackDocNo($main->NhaMay);
            $insert['OPhieuThuHoiTaiSan'][0]['Ngay']     = $main->Ngay;
            $insert['OPhieuThuHoiTaiSan'][0]['DienGiai'] = $main->DienGiai;

            $mImport->setData($insert);
            $mImport->generateSQL();
            $error = $mImport->countFormError() + $mImport->countObjectError();

            if($error) {
                $this->setError();
                $this->setMessage($this->_translate(1));
            }
            else {
                $ifids = $mImport->getIFIDs();
                $ifid  = 0;

                foreach ($ifids as $item) { $ifid = $item->oldIFID; }

                if($ifid) {
                    Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$ifid.'&deptid='.$user->user_dept_id;
                }
                else {
                    $this->setError();
                    $this->setMessage($this->_translate(1));
                }
            }
        }
        else {
            $this->setError();
            $this->setMessage($this->_translate(1));
        }
	}
	
}