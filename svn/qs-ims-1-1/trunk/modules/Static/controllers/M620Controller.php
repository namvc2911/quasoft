<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M620Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Project();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	/**
	 * Module: Tổng hợp vật tư theo dự án
	 */
	public function indexAction()
	{

	}
	/**
	 * Luu y: Du an co the chia thanh nhieu phase tuy nhien hien tai
	 * dang tinh chi co mot phase (giai doan, hang muc)
	 * Phuong an 1: Ko su dung nhat trinh (tam thoi tinh theo cach nay
	 * So luong yeu cau
	 * So luong da cap = so luong da xuat ra
	 * So luong da su dung = so luong da cap - so luong nhap lai
	 * So luong con lai = so luong nhap lai
	 * Phuong an 2: Tinh theo nhat trinh neu su dung nhat trinh
	 * So luong yeu cau
	 * So luong da cap = so luong da xuat ra
	 * So luong da su dung = so luong ghi nhan trong nhat trinh
	 * So luong da su dung = So luong da cap - So luong da su dung
	 */
	public function showAction()
	{
		$common      = new Qss_Model_Extra_Extra();
		$start       = $this->params->requests->getParam('start_date', '');
		$end         = $this->params->requests->getParam('end_date', '');
		$projectIOID = $this->params->requests->getParam('project_ioid', 0);

		$this->html->project = $common->getTableFetchOne('ODuAn', array('IOID'=>$projectIOID));
		$this->html->report  = $this->sumItemByProject(
		Qss_Lib_Date::displaytomysql($start),
		Qss_Lib_Date::displaytomysql($end),
		$projectIOID
		);
	}

	/**
	 * Luu y: can phai xem loai xuat nhap kho de lay theo du an trong sql
	 * @param type $start
	 * @param type $end
	 * @param type $projectIOID
	 */
	public function sumItemByProject($start, $end, $projectIOID)
	{
		$warehouse = new Qss_Model_Extra_Warehouse();
		$req       = $warehouse->sumReqItemByProject($start, $end, $projectIOID);
		$in        = $warehouse->sumInItemByProject($start, $end, $projectIOID);
		$out       = $warehouse->sumOutItemByProject($start, $end, $projectIOID);
		$retval    = new stdClass();


		foreach($req as $item)
		{
			$retval->{$item->IIOID}->TenVatTu       = $item->TenSanPham;
			$retval->{$item->IIOID}->DonViTinh      = $item->DonViTinh;
			$retval->{$item->IIOID}->DacTinhKyThuat = $item->DacTinhKyThuat;
			$retval->{$item->IIOID}->SoLuongYeuCau  = $item->SoLuongYeuCau;
			$retval->{$item->IIOID}->VatTuTieuHao   = $item->VatTuTieuHao;
		}

		foreach($out as $item)
		{
			$retval->{$item->IIOID}->TenVatTu       = $item->TenSanPham;
			$retval->{$item->IIOID}->DonViTinh      = $item->DonViTinh;
			$retval->{$item->IIOID}->DacTinhKyThuat = $item->DacTinhKyThuat;
			$retval->{$item->IIOID}->SoLuongDaCap   = $item->SoLuongXuatKho;
			$retval->{$item->IIOID}->VatTuTieuHao   = $item->VatTuTieuHao;
		}

		foreach($in as $item)
		{
			$retval->{$item->IIOID}->TenVatTu       = $item->TenSanPham;
			$retval->{$item->IIOID}->DonViTinh      = $item->DonViTinh;
			$retval->{$item->IIOID}->DacTinhKyThuat = $item->DacTinhKyThuat;
			$retval->{$item->IIOID}->SoLuongConLai  = $item->SoLuongNhapKho;
			$retval->{$item->IIOID}->VatTuTieuHao   = $item->VatTuTieuHao;
		}
		return $retval;
	}
}

?>