<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M727Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */
	protected $_params; /* Remove */
	protected $_common; /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_params    = $this->params->requests->getParams();
		$this->_common    = new Qss_Model_Extra_Extra();
		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	public function indexAction()
	{
		//	$this->v_fCheckRightsOnForm(155);

	}

	public function showAction()
	{
		// GET FILTER DATA
		// DIEU KIEN LOC THEO THOI GIAN
		$start          = $this->params->requests->getParam('start', '');
		$end            = $this->params->requests->getParam('end', '');
		// DIEU KIEN LOC THEO THIET BI
		$eqIOID         = $this->params->requests->getParam('equipment', 0);
		$eq             = $this->params->requests->getParam('equipmentStr', 0);
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
		$all            = $this->params->requests->getParam('all', 0);
		$projectIOID    = $this->params->requests->getParam('project', 0);
		$employeeIOID   = $this->params->requests->getParam('employee', 0);

		$equipModel     = new Qss_Model_Maintenance_Equip_Moving();


		if ($start && $end)
		{
			// LAY LICH SU CAI DAT THIET BI THEO CAC DIEU KIEN LOC
			$equipments= $equipModel->getInstallHistoryOfEquipmentByLocation(
			Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
			, $eqIOID
			, $locationIOID
			, $eqGroupIOID
			, $eqTypeIOID
			, $projectIOID
			, $employeeIOID
			, $all);
				
			// DANH SACH LICH SU CAI DAT CAC THIET BI
			$this->html->eqs          = $this->getEquipmentForIntallHistoryReport($equipments, $start, $end);
			// THONG SO CUA CAC THIET BI
			//$this->html->param        = $this->getParamOfEquip($equipments);
			// LOC THEO KHU VUC
			$this->html->locationIOID = $locationIOID;
			$this->html->location     = $location;
			// LOC THEO NHOM THIET BI
			$this->html->eqGroupIOID  = $eqGroupIOID;
			$this->html->eqGroup      = $eqGroup;
			// LOC THEO LOAI THIET BI
			$this->html->eqTypeIOID   = $eqTypeIOID;
			$this->html->eqType       = $eqType;
			// LOC THEO THIET BI
			$this->html->eqIOID       = $eqIOID;
			$this->html->eq           = $eq;
			// LOC THEO THOI GIAN
			$this->html->start        = $start;
			$this->html->end          = $end;
		}
		else
		{
			$this->setHtml('Date is required!');
		}
	}
/**
	 * @note: Dung rieng cho in lich su cai dat thiet bi
	 * @param object $equipments
	 * @param date $start Ngay bat dau tim kiem lich su cai dat (dd-mm-YYYY)
	 * @param date $end Ngay ket thuc tim kiem lich su cai dat (dd-mm-YYYY)
	 * @retun array tra ve mang cai dat di chuyen thiet bi da duoc sap xep ngay thang
	 * @use-in: equipmentLocation1Action()
	 */
	private function getEquipmentForIntallHistoryReport($equipments, $start, $end)
	{

		if(!count((array)$equipments)) return array();
		$eqArray = array(); // Mang thiet bi da sap xep
		$i       = 0;

		foreach($equipments as $item)
		{
			$item->NgayBatDau  = $item->NgayBatDau?$item->NgayBatDau:$start;
			$item->NgayKetThuc = $item->NgayKetThuc?$item->NgayKetThuc:$end;
			$eqArray[$i]['MaThietBi']        = $item->MaThietBi;
			$eqArray[$i]['TenThietBi']        = $item->TenThietBi;
			$eqArray[$i]['Ref_MaThietBi']    = $item->Ref_MaThietBi;
			$eqArray[$i]['LoaiThietBi']      = $item->LoaiThietBi;
			$eqArray[$i]['LichLamViec']      = $item->CDLich;
			$eqArray[$i]['KhuVuc']           = $item->MaKVMoi;
			$eqArray[$i]['TuNgay']           = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
			$eqArray[$i]['DenNgay']          = Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc);
			//$eqArray[$i]['ThoiGian']         = $item->ThoiGian;
			$eqArray[$i]['Model']            = $item->Model;
			$eqArray[$i]['NhomThietBi']      = $item->NhomThietBi;
			$eqArray[$i]['HanBaoHanh']       = Qss_Lib_Date::mysqltodisplay($item->HanBaoHanh);
			$eqArray[$i]['HangBaoHanh']      = $item->HangBaoHanh;
			$eqArray[$i]['NgayDuaVaoSuDung'] = $NgayDuaVaoSuDung;
			$eqArray[$i]['NgayMua']          = $item->NgayMua;
			$eqArray[$i]['XuatXu']           = $item->XuatXu;
			$eqArray[$i]['NamSanXuat']       = $item->NamSanXuat;
			$eqArray[$i]['TBIOID']           = $item->TBIOID;
			$eqArray[$i]['ThoVanHanh']       = $item->ThoVanHanh;
			$eqArray[$i]['DuAn']             = $item->DuAn;
			$eqArray[$i]['SoGioHoatDong']    = $item->SoGioHoatDong;
			$i++;
		}
		return $eqArray;
	}
}

?>