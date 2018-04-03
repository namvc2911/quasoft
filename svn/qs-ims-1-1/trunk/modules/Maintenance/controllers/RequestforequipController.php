<?php
class Maintenance_RequestforequipController extends Qss_Lib_Controller
{
    public function init ()
    {
        parent::init();
    }

    public function createeqworkinIndexAction()
    {
        $commonModel = new Qss_Model_Extra_Extra();
        $ifid        = $this->params->requests->getParam('ifid', 0);
        $line        = $commonModel->getTableFetchOne('OLichThietBi', array('IFID_M706'=>$ifid));
        //$request     = $commonModel->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IOID'=>@(int)$line->Ref_PhieuYeuCau));

//		$this->html->eqtypeLevel = $this->getEquipTypesLevel();
        $this->html->report    = $this->geRequestForEquipDetailByIFID(@(int)$line->IFID_M706, @(int)$line->Ref_PhieuYeuCau);
//		$this->html->locations = $commonModel->getNestedSetTable('OKhuVuc');
//		$this->html->wcals     = $commonModel->getTable(array('*'), 'OLichLamViec', array(), array(), 'NO_LIMIT');
        $this->html->ifid      = $ifid;
        $this->html->deptid    = $this->_user->user_dept_id;

        //$this->setHtmlRender(false);
        $this->setLayoutRender(false);

        //$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';

    }

    public function createeqworkinSaveAction()
    {
        $params = $this->params->requests->getParams();
        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Extra->Maintenance->Requestforequip->Saveequipworking($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Lay danh sach yeu cau dieu dong thiet bi
     * @param unknown $requestForEquipIFID
     * @return multitype:
     */
    public function geRequestForEquipDetailByIFID($movingIFID, $requestForEquipIOID)
    {
        $requestForEquipModel = new Qss_Model_Maintenance_Equipmentworking();
        $data   = $requestForEquipModel->getDetailOfRequestForEquipModuleByIFID($movingIFID, $requestForEquipIOID);
        $return = array();
        $daChonTheoLoai= array();
        $i      = -1;
        $j      = 0;
        $oldRFEDIOID   = '';
        $oldEqTypeIOID = '';




        foreach ($data as $dat)
        {
            // khi chuyen sang dong yc dieu dong moi hoac loai thiet bi moi thi moi lay mang
            // thiet bi truc thuoc thiet bi moi
            if($oldRFEDIOID != $dat->RFEDIOID || $oldEqTypeIOID != $dat->EquipTypeIOID)
            {
                $i++;
                $j = 0;
                $return[$i]['RFEDIOID']   = $dat->RFEDIOID;
                $return[$i]['EqType']     = $dat->EquipType;
                $return[$i]['EqTypeIOID'] = $dat->EquipTypeIOID;
                $return[$i]['Qty']        = $dat->Qty;
                $return[$i]['FirstQty']   = $dat->Qty;
                $return[$i]['UOM']        = $dat->UOM;
                $return[$i]['Start']      = Qss_Lib_Date::mysqltodisplay($dat->Start);
                $return[$i]['End']        = Qss_Lib_Date::mysqltodisplay($dat->End);
                $return[$i]['Equips']     = array();
            }


            if($dat->SameRequestButInOther)
            {
                $return[$i]['Qty']--;
            }

            $return[$i]['Equips'][$j]['IsChild']        = $dat->IsChild;
            $return[$i]['Equips'][$j]['InOther']        = $dat->SameRequestButInOther;

            $return[$i]['Equips'][$j]['Transferred']    = $dat->Transferred;
            $return[$i]['Equips'][$j]['Disable']        = ($dat->EWStatus > 1)?TRUE:FALSE;

            $return[$i]['Equips'][$j]['Disabled']       = (!$dat->Transferred || $return[$i]['Equips'][$j]['Disable'] || $dat->IsChild)?' disabled ':' ';
            $return[$i]['Equips'][$j]['DisabledTick']   = ($return[$i]['Equips'][$j]['Disable'])?' disabled ':' ';
            $return[$i]['Equips'][$j]['Checked']        = $dat->Transferred?' checked ':'';
            $return[$i]['Equips'][$j]['CheckVal']       = $dat->Transferred?1:0;


            $return[$i]['Equips'][$j]['EWIOID']         = $dat->EWIOID;
            $return[$i]['Equips'][$j]['EWIFID']         = $dat->EWIFID;
            $return[$i]['Equips'][$j]['EWStatus']       = $dat->EWStatus;
            $return[$i]['Equips'][$j]['Code']           = $dat->MaThietBiCon;
            $return[$i]['Equips'][$j]['Name']           = $dat->TenThietBiCon;
            $return[$i]['Equips'][$j]['Serial']         = $dat->SerialThietBiCon;
            $return[$i]['Equips'][$j]['IOID']           = $dat->EqIOID;

            $return[$i]['Equips'][$j]['Start']          = Qss_Lib_Date::mysqltodisplay($dat->EqStartDate);
            $return[$i]['Equips'][$j]['End']            = Qss_Lib_Date::mysqltodisplay($dat->EqEndDate);
            $return[$i]['Equips'][$j]['DocNo']          = $dat->DocNo;
            $return[$i]['Equips'][$j]['HanHCKD']        = $dat->HanHCKD;
            $return[$i]['Equips'][$j]['ProjectCode']    = $dat->DuAn;


            $j++;

            $oldRFEDIOID   = $dat->RFEDIOID;
            $oldEqTypeIOID = $dat->EquipTypeIOID;
        }

        // echo '<pre>'; print_r($data); die;

        return $return;
    }

//	private function getEquipTypesLevel()
//	{
//		$commonModel = new Qss_Model_Extra_Extra();
//		$eqtypes     = $commonModel->getNestedSetTable('OLoaiThietBi');
//		$retval      = array();
//
//		foreach ($eqtypes AS $item)
//		{
//			$retval[$item->IOID]['Name']  = $item->TenLoai;
//			$retval[$item->IOID]['Level'] = $item->LEVEL;
//		}
//		return $retval;
//	}
}