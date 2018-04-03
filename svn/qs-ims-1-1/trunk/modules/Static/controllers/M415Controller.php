<?php
/**
 * Xử lý mua sắm
 * @author Thinh
 * @note: Su dung 2 session la M415SessionIFID va M415Step
 *
 */

/**
 * Class Static_M415Controller
 * Xử lý yêu cầu mua sắm
 * A. Hiển thị danh sách các phiên xử lý yêu cầu mua sắm
 * - indexAction: Hiển thị màn hình chính
 * - showAction: Hiển thị danh sách các phiên xử lý theo User đăng nhập và nút thêm phiên xử lý mua hàng.Danh sách
 * bao gồm Ngày tạo phiên,Yêu cầu, Kế hoạch, Đơn hàng, Sửa xóa phiên.
 * - saveAction: Tạo thêm một phiên xử lý yêu cầu mua sắm
 * - deleteAction: Xóa phiên, phiển chỉ được xóa khi chưa tạo và duyệt kế hoạch.
 * - editAction: Chuyển đến màn hình sửa phiên
 *
 * B. Sửa phiên
 * - controlAction: Hiển thị thanh điều khiển phiển, bao gồm tiến lùi, bỏ qua, xóa, tạo đơn hàng...
 * - resetAction:  Xóa dữ liệu phiên, chỉ thực hiện được khi kế hoạch của phiên chưa được duyệt
 * - updatestepAction: Cập nhật lại tinh trạng của phiên, xác định phiên đang ở bước thứ mấy
 *
 * B1. Quy trình
 *   Người dùng sau khi vào sửa một phiên. Ở màn hình nhập yêu cầu mua sắm, người dùng import dữ liệu từ excel
 * theo mẫu vào trong hệ thống. Sau khi import hệ thống sẽ lấy tất cả những yêu cầu chưa xử lý mà người dùng
 * đã tạo trước đó theo user đăng nhập hệ thống, bao gồm các yêu cầu import lẫn những yêu cầu chưa xử lý trước đó.
 * Tất nhiên người dùng cũng có thể tiến hành chọn và xóa đi yêu cầu ra khỏi phiên xử lý.
 * <Xem B2>
 *
 *   Sau Khi người dùng import xong, người dùng ấn tiếp tục để chuyển sang màn hình "Hợp đồng nguyên tắc". Trong màn
 * hình này sẽ hiện ra các hợp đồng nguyên tắc còn hiệu lực cho từng mặt hàng nếu có để người dùng có thể chọn để
 * đặt hàng. Nếu các các sản phẩm trong yêu cầu được đặt hàng hết sẽ chuyển sang màn hình "Đơn hàng". Nếu không
 * sẽ chuyển sang màn hình tiếp theo là màn hình "Kiểm tra đơn hàng"
 *
 * B2. Nhập yêu cầu mua sắm
 * - saverequestsAction : Lưu các yêu cầu mua sắm chưa lưu vào phiên hiện tại, chỉ lưu các yêu cầu được tạo bởi user
 * - step1IndexAction : Hiển thị danh sách yêu cầu cần xử ly của phiên
 * - step1DeleteAction : Xóa một yêu cầu ra khỏi phiên, yêu cầu chỉ được xóa khi đang ở bước nhập yêu cầu
 *
 * @todo: Ở bước này, nên tách việc lấy toàn bộ yêu cầu thành hai phần rõ rệt. Khi import bằng excel thì chỉ lấy
 * @todo: đúng phần import bằng excel thôi. Thêm một nút là lấy các yêu cầu chưa xử lý.
 *
 */
class Static_M415Controller extends Qss_Lib_Controller {
	public static $M415_STEP = array(
		1 => "Đang nhập yêu cầu",
		2 => "Đang kiểm tra hợp đồng nguyên tắc",
		3 => "Đang kiểm tra đơn hàng",
		4 => "Đang lập KHMS",
		5 => "Đang lập chào giá",
		6 => "Đang nhập báo giá",
		7 => "Đang So sánh báo giá",
		8 => "Đặt hàng",
	);

	public function init() {
		$this->i_SecurityLevel = 15;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/object-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');

		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/wide.php';
	}

	public function indexAction() {
		$sdate = $this->params->requests->getParam('start', date('01-m-Y'));
		$edate = $this->params->requests->getParam('end', date('t-m-Y'));

		$this->html->sdate = $sdate;
		$this->html->edate = $edate;
	}

	/**
	 * Hiển thị danh sách các phiên xử lý yêu cầu mua sắm
	 */
	public function showAction() {
		$sdate = $this->params->requests->getParam('start', date('01-m-Y'));
		$edate = $this->params->requests->getParam('end', date('t-m-Y'));
		$page = (int) $this->params->requests->getParam('page', 1);
		$perpage = (int) $this->params->requests->getParam('perpage', 10);

		$mSession = new Qss_Model_Purchase_Order();
		$total = $mSession->countSessionsByUser(
			$this->_user->user_id
			, Qss_Lib_Date::displaytomysql($sdate)
			, Qss_Lib_Date::displaytomysql($edate)
		);
		$totalPage = ceil($total / $perpage);
		$page = ($page > $totalPage) ? 1 : $page;

		$sessions = $mSession->getAllSessionByUser(
			$this->_user->user_id
			, Qss_Lib_Date::displaytomysql($sdate)
			, Qss_Lib_Date::displaytomysql($edate)
			, $page
			, $perpage
		);

		// totalPage

		$this->html->sessions = $sessions;
		$this->html->page = $page;
		$this->html->perpage = $perpage;
		$this->html->next = ($page < $totalPage) ? $page + 1 : $totalPage;
		$this->html->prev = ($page > 1) ? $page - 1 : 1;
		$this->html->totalPage = $totalPage;
	}

	/**
	 * Tạo thêm 1 phiên xử lý yêu cầu mua sắm
	 */
	public function saveAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->CreateSession($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Xóa phiên xử lý yêu cầu mua sắm. Phiên chỉ được xóa khi kế hoạch chưa được tạo và duyệt.
	 */
	public function deleteAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->DeleteSession($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Sửa phiên xử lý: Chuyển đến màn hình xử lý phiên
	 */
	public function editAction() {
		$mSession = new Qss_Model_Purchase_Order();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$session = $mSession->getSessionByIFID($sessionIFID);

		// Ghi lai vao session
		$_SESSION['M415Step'] = $session ? $session->Buoc : 1;
		$_SESSION['M415SessionIFID'] = $sessionIFID;

		$this->html->session = $mSession->getSessionByIFID($sessionIFID);
		$this->html->deptid = $this->_user->user_dept_id;
	}

	/**
	 * Hiển thị thanh điều khiển quá trình sửa phiên: Thanh dưới cùng gồm các nút điều hướng
	 */
	public function controlAction() {
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$step = $this->params->requests->getParam('step', 0); // Luu trong co so du lieu
		$stepto = $this->params->requests->getParam('stepto', 0); // Dang focus den
		$mPlan = new Qss_Model_Purchase_Plan();
		$mRequest = new Qss_Model_Purchase_Request();
		$plan = $mRequest->getRequestsBySession($sessionIFID);
		$nextToPlan = false;

		foreach ($plan as $item) {
			if ($item->Requested > 0 && ($item->Ordered == 0 || $item->Planed > 0)) {
				$nextToPlan = true;
			}
		}

		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->info = $mPlan->getPlanBySession($sessionIFID);
		$this->html->step = $step;
		$this->html->stepto = $stepto;
		$this->html->nextToPlan = $nextToPlan;
	}

	/**
	 * Xóa dữ liệu phiên, chỉ thực hiện được khi kế hoạch của phiên chưa được duyệt
	 */
	public function resetAction() {
		$params = $this->params->requests->getParams();

		$params['UID'] = $this->_user->user_id;
		$params['DeptID'] = $this->_user->user_dept_id;
		$params['User'] = $this->_user;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->ResetSession($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Cập nhật lại tinh trạng của phiên, xác định phiên đang ở bước thứ mấy
	 */
	public function updatestepAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->UpdateSessionStep($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Lưu các yêu cầu chưa lưu vào session hiện tại, chỉ những yêu cầu chưa được xử lý theo user
	 */
	public function saverequestsAction() {
		$params = $this->params->requests->getParams();
		$params['UID'] = $this->_user->user_id;
		$params['DeptID'] = $this->_user->user_dept_id;
		$params['User'] = $this->_user;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->SaveRequests($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// ===== STEP1: Nhập YÊU CẦU ===

	/**
	 * Hiển thị danh sách các yêu cầu đã được thêm vào phiên xử lý
	 */
	public function step1IndexAction() {
		$mRequest = new Qss_Model_Purchase_Request();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);

		// $this->html->requests = $mRequest->getRequestsBySession($sessionIFID);
		$this->html->requests = $mRequest->getDraftRequests1($this->_user->user_id, $sessionIFID);
		$this->html->deptid = $this->_user->user_dept_id;
	}

	/**
	 * Xóa yêu cầu xử lý trong phiên, chỉ thực hiên khi đang ở bước Nhập yêu cầu
	 */
	public function step1DeleteAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Request->Delete($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// ===== STEP2: Hợp đồng nguyên tắc

	// Sua mot session step2
	public function step2IndexAction() {
		$mAgreement = new Qss_Model_Purchase_Agreement();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);

		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->agreements = $mAgreement->getAgreementByRequestsOfSession($sessionIFID);
	}

	public function step2CreateorderAction() {
		$params = $this->params->requests->getParams();

		$params['UserName'] = $this->_user->user_desc;
		$params['Ordered'] = 1;
		$params['DeptID'] = $this->_user->user_dept_id;
		$params['User'] = $this->_user;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->CreateOrders($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// ===== STEP3: KIểm tra đơn hàng sau tháng

	// Sua mot session step3
	public function step3IndexAction() {
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$this->html->sessionIFID = $sessionIFID;
	}

	// Các đơn hàng giao hàng nhanh nhất
	public function step3FastestordersAction() {
		$mOrder = new Qss_Model_Purchase_Order();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$sessionDate = $this->params->requests->getParam('sessiondate', date('Y-m-d')); // YYYY-mm-dd
		$fromDate = $this->params->requests->getParam('from_date', date('d-m-Y', strtotime(date('d-m-Y') . ' -6 months')));

		// Don hang rẻ nhat trong 6 thang gan day
		$this->html->orders = $mOrder->getFastestOrdersBySession($sessionIFID, Qss_Lib_Date::displaytomysql($sessionDate), Qss_Lib_Date::displaytomysql($fromDate));
		$this->html->deptid = $this->_user->user_dept_id;
	}

	// Các đơn hàng gần nhất
	public function step3LastestordersAction() {
		$mOrder = new Qss_Model_Purchase_Order();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$sessionDate = $this->params->requests->getParam('sessiondate', date('Y-m-d')); // YYYY-mm-dd
		$fromDate = $this->params->requests->getParam('from_date', date('d-m-Y', strtotime(date('d-m-Y') . ' -6 months')));

		// Don hang rẻ nhat trong 6 thang gan day
		$this->html->orders = $mOrder->getLastestOrdersBySession($sessionIFID, Qss_Lib_Date::displaytomysql($sessionDate), Qss_Lib_Date::displaytomysql($fromDate));
		$this->html->deptid = $this->_user->user_dept_id;
	}

	// Các đơn hàng rẻ nhất trong 6 tháng gần đây
	public function step3CheapestordersAction() {
		$mOrder = new Qss_Model_Purchase_Order();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$sessionDate = $this->params->requests->getParam('sessiondate', date('Y-m-d')); // YYYY-mm-dd
		$fromDate = $this->params->requests->getParam('from_date', date('d-m-Y', strtotime(date('d-m-Y') . ' -6 months')));

		// Don hang rẻ nhat trong 6 thang gan day
		$this->html->orders = $mOrder->getCheapestOrdersBySession($sessionIFID, Qss_Lib_Date::displaytomysql($sessionDate), Qss_Lib_Date::displaytomysql($fromDate));
		$this->html->deptid = $this->_user->user_dept_id;
	}

	public function step3CreateorderAction() {
		$params = $this->params->requests->getParams();

		$params['UserName'] = $this->_user->user_desc;
		$params['Ordered'] = 0;
		$params['DeptID'] = $this->_user->user_dept_id;
		$params['User'] = $this->_user;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->CreateOrders($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// Sua mot session step4
	public function step4IndexAction() {
		$mRequest = new Qss_Model_Purchase_Request();
		$mPlan = new Qss_Model_Purchase_Plan();
		$mPartner = new Qss_Model_Master_Partner();
		$mQuotation = new Qss_Model_Purchase_Quotation();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$planIOID = $this->params->requests->getParam('planioid', 0);
		$oSuppliers = $mPartner->getSuppliers();
		$aSuppliers = array();
		$mObject = new Qss_Model_Object();
		$mObject->v_fInit('OKeHoachMuaSam', 'M716');

		foreach ($oSuppliers as $sup) {
			$aSuppliers[$sup->IOID] = "{$sup->TenDoiTac} - {$sup->MaDoiTac}";
		}

		$this->html->docno = Qss_Lib_Extra::getDocumentNo($mObject);
		$this->html->quotations = $mQuotation->getQuotationDetailBySession($sessionIFID);
		$this->html->info = $mPlan->getPlanBySession($sessionIFID);
		$this->html->plans = $mRequest->getRequestsBySession($sessionIFID);
		$this->html->suppliers = $aSuppliers;
		$this->html->deptid = $this->_user->user_dept_id;
	}

	public function step4SaveplanAction() {
		$params = $this->params->requests->getParams();

		$params['UserName'] = $this->_user->user_desc;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Plan->SavePlan($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function step4ApproveplanAction() {
		$params = $this->params->requests->getParams();
		$params['UID'] = $this->_user->user_id;
		$params['DeptID'] = $this->_user->user_dept_id;
		$params['User'] = $this->_user;
		$params['PlanIFID'] = 0;
		$planioid = @(int) $params['planioid'];

		if ($planioid) {
			$mPlan = new Qss_Model_Purchase_Plan();
			$plan = $mPlan->getPlanByIOID($planioid);
			$params['PlanIFID'] = ($plan) ? $plan->IFID_M716 : 0;
		}

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Plan->ApprovePlan($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function step4DeleteplanAction() {
		$params = $this->params->requests->getParams();
		$mPlan = new Qss_Model_Purchase_Plan();
		$sessionIFID = @(int) $params['sessionifid'];
		$plan = $mPlan->getPlanBySession($sessionIFID);
		$params['PlanIFID'] = 0;

		if ($plan) {
			$params['PlanIFID'] = $plan->IFID_M716;
		}

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Plan->DeletePlan($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function step4QuotationAction() {
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$mPlan = new Qss_Model_Purchase_Plan();
		$mQuotation = new Qss_Model_Purchase_Quotation();
		$mPartner = new Qss_Model_Master_Partner();
		$planInfo = $mPlan->getPlanBySession($sessionIFID);
		$planDetail = $mPlan->getPlanDetailBySession($sessionIFID);
		$oSuppliers = $mPartner->getSuppliers();
		$aSuppliers = array();

		foreach ($oSuppliers as $sup) {
			$aSuppliers[$sup->IOID] = "{$sup->TenDoiTac} - {$sup->MaDoiTac}";
		}

		$this->html->suppliers = $aSuppliers;
		$this->html->quotations = $mQuotation->getQuotationDetailBySession($sessionIFID);
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->quotations2 = $planDetail;
		$this->html->planioid = $planInfo ? $planInfo->PlanIOID : 0;
		$this->html->planNo = $planInfo ? $planInfo->SoPhieu : '';
	}

	public function step4Quotation1Action() {
		$mPlan = new Qss_Model_Purchase_Plan();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$planDetail = $mPlan->getPlanDetailBySession($sessionIFID);
		$planInfo = $mPlan->getPlanBySession($sessionIFID);
		$mPartner = new Qss_Model_Master_Partner();
		$oSuppliers = $mPartner->getSuppliers();
		$aSuppliers = array();

		foreach ($oSuppliers as $sup) {
			$aSuppliers[$sup->IOID] = "{$sup->TenDoiTac} - {$sup->MaDoiTac}";
		}

		$this->html->suppliers = $aSuppliers;
		$this->html->quotations = $planDetail;
		$this->html->planioid = $planInfo ? $planInfo->PlanIOID : 0;
		$this->html->planNo = $planInfo ? $planInfo->SoPhieu : '';
	}

	public function step4CreatequoationAction() {
		$params = $this->params->requests->getParams();
		$params['UserName'] = $this->_user->user_desc;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Quotation->Create($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// Sua mot session step5
	public function step5IndexAction() {
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$mPlan = new Qss_Model_Purchase_Plan();
		$mQuotation = new Qss_Model_Purchase_Quotation();
		$mPartner = new Qss_Model_Master_Partner();
		$planInfo = $mPlan->getPlanBySession($sessionIFID);
		$planDetail = $mPlan->getPlanDetailBySession($sessionIFID);
		$oSuppliers = $mPartner->getSuppliers();
		$aSuppliers = array();

		foreach ($oSuppliers as $sup) {
			$aSuppliers[$sup->IOID] = "{$sup->TenDoiTac} - {$sup->MaDoiTac}";
		}

		$this->html->suppliers = $aSuppliers;
		$this->html->quotations = $mQuotation->getQuotationDetailBySession($sessionIFID);
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->quotations2 = $planDetail;
		$this->html->planioid = $planInfo ? $planInfo->PlanIOID : 0;
		$this->html->planNo = $planInfo ? $planInfo->SoPhieu : '';
	}

	public function step5QuotationAction() {
		$mPlan = new Qss_Model_Purchase_Plan();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$planDetail = $mPlan->getPlanDetailBySession($sessionIFID);
		$planInfo = $mPlan->getPlanBySession($sessionIFID);
		$mPartner = new Qss_Model_Master_Partner();
		$oSuppliers = $mPartner->getSuppliers();
		$aSuppliers = array();

		foreach ($oSuppliers as $sup) {
			$aSuppliers[$sup->IOID] = "{$sup->TenDoiTac} - {$sup->MaDoiTac}";
		}

		$this->html->suppliers = $aSuppliers;
		$this->html->quotations = $planDetail;
		$this->html->planioid = $planInfo ? $planInfo->PlanIOID : 0;
		$this->html->planNo = $planInfo ? $planInfo->SoPhieu : '';
	}

	public function step5CreatequoationAction() {
		$params = $this->params->requests->getParams();
		$params['UserName'] = $this->_user->user_desc;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Quotation->Create($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// Sua mot session step6
	public function step6IndexAction() {
		$planIOID = $this->params->requests->getParam('planioid', 0);
		$this->html->suppliers = $this->getQuotationsByPlan($planIOID);
	}

	/**
	 * Lưu ý nếu không có kế hoạch thì sẽ không có báo giá.
	 * @param $planIOID
	 */
	public function getQuotationsByPlan($planIOID) {
		$mPlan = new Qss_Model_Purchase_Plan();
		$supp = $mPlan->getSuppliersForPlan($planIOID);
		$ret = array();

		foreach ($supp as $item) {
			$ret[$item->Ref_DoiTac] = "{$item->MaDoiTac} - {$item->TenDoiTac}";
		}

		return $ret;
	}

	public function step6ShowAction() {
		$mQuotation = new Qss_Model_Purchase_Quotation();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$partnerIOID = $this->params->requests->getParam('partnerioid', 0);
		$planIOID = $this->params->requests->getParam('planioid', 0);

		$this->html->quotations = $mQuotation->getQuotationDetailBySession($sessionIFID, $partnerIOID);
		$this->html->deptid = $this->_user->user_dept_id;
	}

	public function step6SaveAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Quotation->Update($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// Sua mot session step7
	public function step7IndexAction() {
		$planIOID = $this->params->requests->getParam('planioid', 0);
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);

		$mQuotation = new Qss_Model_Purchase_Quotation();
		$mCurrency = new Qss_Model_Currency();
		$mPlan = new Qss_Model_Purchase_Plan();

		$plan = $mPlan->getPlanByIOID($planIOID);
		$cList = $mPlan->getCurrencyList($planIOID);
		$cArr = array();

		foreach ($cList as $item) {
			$cArr[$item->LoaiTien] = $item->TyGia;
		}

		$this->html->currencies = $mQuotation->getQuotationCurrencyBySession($sessionIFID);
		$this->html->primary = $mCurrency->getPrimary();
		$this->html->plan = $plan;
		$this->html->clist = $cArr;
	}

	public function step7CompareAction() {
		$planIOID = $this->params->requests->getParam('planioid', 0);
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);
		$currencies = $this->params->requests->getParam('currencies', array());
		$type = $this->params->requests->getParam('type', 2);

		$mPlan = new Qss_Model_Purchase_Plan();
		$planDetail = $mPlan->getPlanDetailBySession($sessionIFID);

		$mQuotation = new Qss_Model_Purchase_Quotation();
//        $quotes      = $mQuotation->getCompareListBySession($sessionIFID);
		//        $lastQuotes  = $mQuotation->getLastQuotesBySession($sessionIFID);

		$supp = $mPlan->getSuppliersForPlan($planIOID);
		$quotes = $mQuotation->getLastQuotesOfPlan($planIOID);

//        echo '<pre>'; print_r($supp); die;

		foreach ($quotes as $item) {
			$findMin = '';
			$findMax = '';
			$select = '';
			$min = '';
			$max = '';
			$tygia = (isset($currencies[$item->CID]) && $currencies[$item->CID]) ? $currencies[$item->CID] : 1;

			foreach ($supp as $item2) {
				$dongia = isset($item->{'DonGia_' . $item2->Ref_MaNCC}) ? $item->{'DonGia_' . $item2->Ref_MaNCC} : 0;
				$giaThuongLuong = isset($item->{'GiaThuongLuong_' . $item2->Ref_MaNCC}) ? $item->{'GiaThuongLuong_' . $item2->Ref_MaNCC} : 0;
				$item->{'DonGia2_' . $item2->Ref_MaNCC} = $dongia * $tygia; // Gia hien thi

				if (!$item->{'KhongHopLe_' . $item2->Ref_MaNCC} && $item->{'KyThuat_' . $item2->Ref_MaNCC}) {
					if (isset($item->{'DonGia_' . $item2->Ref_MaNCC}) && $item->{'DonGia_' . $item2->Ref_MaNCC}) {
						// Lay don gia, mac dinh la gia thuong luong neu gia thuong luong lon hon 0
						if ($giaThuongLuong) {
							$dongia = $giaThuongLuong * $tygia;
						} else {
							$dongia = $dongia * $tygia;
						}

						if ($min == '') {
							$min = $dongia;
							$findMin = $item2->Ref_MaNCC;
						} else {
							if ($min > $dongia) {
								$min = $dongia;
								$findMin = $item2->Ref_MaNCC;
							}
						}

						if ($max == '') {
							$max = $dongia;
							$findMax = $item2->Ref_MaNCC;
						} else {
							if ($max < $dongia) {
								$max = $dongia;
								$findMax = $item2->Ref_MaNCC;
							}
						}
					}

					if ($item->ChonBaoGiaSanPham) {
						$select = $item->ChonBaoGiaSanPham;
					}
				}

			}

			if (!$select) {
				$select = $findMin;
			}

			$item->selectPartner = $select;
			$item->min = $min;
			$item->max = $max;
		}

		//echo '<Pre>'; print_r($quotes); die;
		//echo '<pre>'; print_r($quotes); die;
		$this->html->type = $type;
		$this->html->partners = $supp;
		$this->html->items = $quotes;
		$this->html->planioid = $planIOID;
		$this->html->plan = $planDetail;
	}

	/**
	 * Tạo đơn hàng khi chuyển bước
	 */
	public function step7CreateorderAction() {
		$params = $this->params->requests->getParams();

		$params['UserName'] = $this->_user->user_desc;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->CreateOrderFromQuotes($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Lưu so sánh báo giá
	 */
	public function step7SavecompareAction() {
		$params = $this->params->requests->getParams();

		$params['UserName'] = $this->_user->user_desc;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Quotation->SaveCompare($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// Sua mot session step8
	public function step8IndexAction() {
		$mOrder = new Qss_Model_Purchase_Order();
		$sessionIFID = $this->params->requests->getParam('sessionifid', 0);

		$this->html->orders = $mOrder->getOrdersBySession($sessionIFID);
		$this->html->deptid = $this->_user->user_dept_id;
	}

	public function step8ApproveAction() {
		$params = $this->params->requests->getParams();
		$params['UID'] = $this->_user->user_id;
		$params['DeptID'] = $this->_user->user_dept_id;
		$params['User'] = $this->_user;

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->Approve($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function step8UpdateorderAction() {
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Purchase->Order->UpdateOrder($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	private function getSessionByIFID($ifid) {
		$mOrder = new Qss_Model_Purchase_Order();
		$temp = new stdClass();
		$session = $mOrder->getSessionByIFID($ifid);

		return $session ? $session : $temp;
	}

	private function getQuotationCompareList($planIOID) {
		if (!$planIOID) {
			return array('Partner' => array(), 'Item' => array(), 'CountPartner' => 0);
		}

		$retval = array();
		$mQuotation = new Qss_Model_Purchase_Quotation();
		$quotation = $mQuotation->getQuotationCompareList($planIOID);

		$retval['Partner'] = array();
		$retval['Item'] = array();
		$retval['CountPartner'] = 0;
		$retval['HasReqQty'] = 0;

		foreach ($quotation as $item) {
			$itemKey = $item->ItemIOID . '-' . $item->UomIOID;

			if (!isset($retval['Partner'][$item->PartnerIOID])) {
				$retval['Partner'][$item->PartnerIOID]['PartnerIOID'] = $item->PartnerIOID;
				$retval['Partner'][$item->PartnerIOID]['PartnerName'] = $item->PartnerName;
				$retval['Partner'][$item->PartnerIOID]['DeliveryTo'] = $item->DiaDiemGiaoHang;
				$retval['Partner'][$item->PartnerIOID]['DeliveryTime'] = $item->ThoiGianGiaoHang;
				$retval['Partner'][$item->PartnerIOID]['Warranty'] = $item->ThoiGianBaoHanh;
				$retval['Partner'][$item->PartnerIOID]['PaymentMethod'] = $item->HinhThucThanhToan;
				$retval['CountPartner']++;
			}

			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['ItemCode'] = $item->ItemCode;
			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['ItemName'] = $item->ItemName;
			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['UnitPrice'] = $item->UnitPrice;
			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['QuoteQty'] = $item->Qty;
			//$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['OrderQty']    = $diff;
			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['Pass'] = $item->Pass;
			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['Quality'] = $item->Quality;
			$retval['Partner'][$item->PartnerIOID]['Item'][$itemKey]['RequestIOID'] = $item->RequestIOID;

			if (!isset($retval['Item'][$itemKey])) {
				$retval['Item'][$itemKey]['ItemIOID'] = $item->ItemIOID;
				$retval['Item'][$itemKey]['UomIOID'] = $item->UomIOID;
				$retval['Item'][$itemKey]['ItemCode'] = $item->ItemCode;
				$retval['Item'][$itemKey]['ItemName'] = $item->ItemName;
				$retval['Item'][$itemKey]['Uom'] = $item->UOM;
				//$retval['Item'][$itemKey]['ReqQty']    = $diff;
				$retval['Item'][$itemKey]['RequestIOID'] = $item->RequestIOID;
			}
		}

		return $retval;

	}

	/*
		     ************************************
		     * DANH SACH CAC MAU IN
		     ************************************
	*/

	/**
	 * Thu moi chao gia
	 */
	public function thumoichaogiaAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Thu moi chao gia.xlsx\"");

		$planIOID = $this->params->requests->getParam('planioid', 0);
		$partnerIOID = $this->params->requests->getParam('partnerioid', 0);
		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/ThuMoiChaoGia.xlsx');

		$mPlan = new Qss_Model_Purchase_Plan();
		$mPartner = new Qss_Model_Master_Partner();
		$main = new stdClass();
		$planDetail = $mPlan->getPlanByIOIDWithEmployeeInfo($planIOID);
		$partnerDetail = $mPartner->getPartnerByIOID($partnerIOID);
		$requires = $mPlan->getRequiresOfPlan($planIOID);
		$projects = array();

		//echo '<pre>'; print_r($partnerDetail); die;

		// Lay mang danh sach du an cua ke hoach tu cac yeu cau tao nen ke hoach
		foreach ($requires as $item) {
			if ($item->Ref_DuAn && !key_exists($item->Ref_DuAn, $projects)) {
				$projects[$item->Ref_DuAn] = $item->DuAn;
			}
		}

		// Chuyen mang danh sach du an thanh dang string truyen sang de in
		$main->DanhSachDuAn = '';

		if ($projects) {
			$main->DanhSachDuAn = implode(', ', $projects);
		}

		// Truyen thong tin ke hoach sang de in
		if ($planDetail) {

			$time = $planDetail->NgayKetThuc ? $planDetail->NgayKetThuc . ' ' . $planDetail->ThoiGianKetThuc : '';
			$time = ($time) ? date_create($time) : false;

			$thoiGianThuMoi = $planDetail->NgayYeuCau ? $planDetail->NgayYeuCau : '';
			$thoiGianThuMoi = $thoiGianThuMoi ? date_create($thoiGianThuMoi) : false;

			$nguoiNhan = '';

			if ($planDetail->NguoiNhan) {
				$nguoiNhan .= $planDetail->GioiTinh ? 'Ông ' : 'Bà ';
				$nguoiNhan .= $planDetail->TenNhanVien ? $planDetail->TenNhanVien : '';
				$nguoiNhan .= ($planDetail->TenNhanVien && $planDetail->ChucDanh) ? ' - ' : '';
				$nguoiNhan .= $planDetail->ChucDanh ? $planDetail->ChucDanh : '';
			}

			$main->SoKeHoach = $planDetail->SoPhieu;
			$main->DiaDiemGiaoHang = $planDetail->DiaDiemGiaoHang ? $planDetail->DiaDiemGiaoHang : str_repeat(' ', 100);
			$main->SoNgayDuKien = $planDetail->ThoiHanCungCap;
			$main->NguoiNhan = $nguoiNhan;
			$main->NoiDung = @$planDetail->NoiDung;

			$main->NgayThuMoi = '';
			$main->ThangThuMoi = '';
			$main->NamThuMoi = '';
			if ($thoiGianThuMoi !== false) {
				$main->NgayThuMoi = $thoiGianThuMoi->format('d');
				$main->ThangThuMoi = $thoiGianThuMoi->format('m');
				$main->NamThuMoi = $thoiGianThuMoi->format('Y');
			} else {
				$main->NgayThuMoi = str_repeat(' ', 10);
				$main->ThangThuMoi = str_repeat(' ', 10);
				$main->NamThuMoi = str_repeat(' ', 10);
			}

			if ($time !== false) {
				$main->Gio = $time->format('H:i');
				$main->Ngay = $time->format('d');
				$main->Thang = $time->format('m');
				$main->Nam = $time->format('Y');
			} else {
				$main->Gio = str_repeat(' ', 15);
				$main->Ngay = str_repeat(' ', 10);
				$main->Thang = str_repeat(' ', 10);
				$main->Nam = str_repeat(' ', 10);
			}
		}

		// Truyen thong tin doi tac de in
		if ($partnerDetail) {
			$main->TenCongTy = $partnerDetail->TenDoiTac;
			$main->DiaChi = $partnerDetail->DiaChi ? $partnerDetail->DiaChi : str_repeat(' ', 20);
			$main->DienThoai = $partnerDetail->DienThoai ? $partnerDetail->DienThoai : str_repeat(' ', 20);
			$main->Fax = $partnerDetail->Fax;
		}

		// Truyen du lieu sang file excel
		$data = array('main' => $main);

		$file->init($data);
		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Bao cao de xuat
	 */
	public function baocaodexuatAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Báo Cáo Đề Xuất.xlsx\"");

		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/BaoCaoDeXuat.xlsx');

		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Danh muc mua hang <Danh muc thiet bi>
	 */
	public function danhmucmuahangAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Danh muc thiet bi.xlsx\"");

		$planIOID = $this->params->requests->getParam('planioid', 0);
		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhMucThietBi.xlsx');
		$mPlan = new Qss_Model_Purchase_Plan();
		$items = $mPlan->getPlanItems($planIOID); // Mat hang cua ke hoach xep theo phieu yeu cau
		$planDetail = $mPlan->getPlanByIOID($planIOID);
		$main = new stdClass();
		$row = 5; // Dong bat dau in du lieu mat hang
		$stt = 0; // So thu tu
		$preTitle = '';
		$oldReq = '';

		$plan = $mPlan->getPlanByIOID($planIOID);

		$thoiGianThuMoi = $plan->NgayYeuCau ? $plan->NgayYeuCau : '';
		$thoiGianThuMoi = $thoiGianThuMoi ? date_create($thoiGianThuMoi) : false;

//        $main->Ngay  = $thoiGianThuMoi->format('d');
		//        $main->Thang = $thoiGianThuMoi->format('m');
		//        $main->Nam   = $thoiGianThuMoi->format('Y');

		$main->KeHoachMuaSam = $planDetail ? $planDetail->SoPhieu : '';

		if ($thoiGianThuMoi !== false) {
			$main->Ngay = $thoiGianThuMoi->format('d');
			$main->Thang = $thoiGianThuMoi->format('m');
			$main->Nam = $thoiGianThuMoi->format('Y');
		} else {
			$main->Ngay = str_repeat(' ', 10);
			$main->Thang = str_repeat(' ', 10);
			$main->Nam = str_repeat(' ', 10);
		}

		$data = array('main' => $main);

		// Init du lieu cua mang main
		$file->init($data);

		foreach ($items as $item) {
			// Nhom mat hang theo yeu cau
			if ($oldReq !== $item->Ref_SoYeuCau) {
				$data = new stdClass(); // reset
				$data->TitleSoYeuCau = $preTitle . $item->SoYeuCau;
				$file->newGridRow(array('sub' => $data), $row, 3);
				$row++;
			}

			$data = new stdClass();
			$data->STT = ++$stt;
			$data->TenMatHang = $item->TenSP;
			$data->DacTinhKyThuat = $item->DacTinhKyThuat;
			$data->DonViTinh = $item->DonViTinh;
			$data->SoLuong = $item->SoLuongYeuCau;
			$data->DonGia = '';
			$data->ThanhTien = '';
			$data->GhiChu = $item->GhiChu;
			$file->newGridRow(array('sub' => $data), $row, 4);
			$row++;

			$oldReq = $item->Ref_SoYeuCau;
		}

		$file->removeRow(4);
		$file->removeRow(3);

		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Ke hoach mua sam
	 * @todo: Thieu truong nguoi lien he
	 */
	public function kehoachmuasamAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Ke hoach mua sam.xlsx\"");

		$planIOID = $this->params->requests->getParam('planioid', 0);
		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/KeHoachMuaSam.xlsx');
		$mPlan = new Qss_Model_Purchase_Plan();
		$mRequest = new Qss_Model_Purchase_Request();
		$mQuote = new Qss_Model_Purchase_Quotation();
		$plan = $mPlan->getPlanByIOID($planIOID);
		$supp = $mPlan->getSuppliersForPlan($planIOID);
		$requests = $mRequest->getRequestsByPlan($planIOID);
		$main = new stdClass();

		// echo '<pre>'; print_r($plan); die;

		$thoiGianThuMoi = $plan->NgayYeuCau ? $plan->NgayYeuCau : '';
		$thoiGianThuMoi = $thoiGianThuMoi ? date_create($thoiGianThuMoi) : false;

//        $main->Ngay  = $thoiGianThuMoi->format('d');
		//        $main->Thang = $thoiGianThuMoi->format('m');
		//        $main->Nam   = $thoiGianThuMoi->format('Y');;

		if ($thoiGianThuMoi !== false) {
			$main->Ngay = $thoiGianThuMoi->format('d');
			$main->Thang = $thoiGianThuMoi->format('m');
			$main->Nam = $thoiGianThuMoi->format('Y');
		} else {
			$main->Ngay = str_repeat(' ', 10);
			$main->Thang = str_repeat(' ', 10);
			$main->Nam = str_repeat(' ', 10);
		}

		$main->SoKeHoach = $plan ? $plan->SoPhieu : '';

		//$main->NgayGuiThuMoi    = $plan?Qss_Lib_Date::mysqltodisplay($plan->NgayGuiThuMoi):'';
		$main->ThoiGianKetThuc = $plan ? date('H:i', strtotime($plan->ThoiGianKetThuc)) : '';
		$main->NgayKetThuc = $plan ? Qss_Lib_Date::mysqltodisplay($plan->NgayKetThuc) : '';
		$main->NgayMo = $plan ? Qss_Lib_Date::mysqltodisplay($plan->NgayMo) : '';
		$main->NgayTrinhDuyet = $plan ? Qss_Lib_Date::mysqltodisplay($plan->NgayTrinhDuyetKetQua) : '';
		$main->NgayGuiThuMoi = $plan ? Qss_Lib_Date::mysqltodisplay($plan->NgayGuiThuMoi) : '';
		$main->ThoiGianGiaoHang = ($plan && $plan->ThoiHanCungCap) ? $plan->ThoiHanCungCap . ' ngày' : '';
		$data = array('main' => $main);
		$rowCoSo = 9;
		$stt = 0;
		$duAn = array();

		$file->init($data);

		foreach ($requests as $item) {
			$data = new stdClass(); // reset
			$data->CoSoThucHien = "Căn cứ phiếu {$item->SoPhieu} của {$item->DonViYeuCau} đã được Ban Giám đốc phê duyệt ngày {$item->NgayPheDuyet} v/v phục vụ DA {$item->DuAn} cho khách hàng {$item->KhachHang} ";
			$file->newGridRow(array('basic' => $data), $rowCoSo, 8);
			//$file->mergeCells('A'.$rowCoSo.':G'.$rowCoSo);

			If ((int) $item->Ref_DuAn && !in_array((int) $item->Ref_DuAn, $duAn)) {
				$duAn[] = $item->DuAn;
			}

			$rowCoSo++;
		}

		$main1 = new stdClass();
		$main1->DuAn = implode(', ', $duAn);
		$data = array('main1' => $main1);
		$file->init($data);

		$rowNhaCungCapTem = 22 + ($rowCoSo - 9) + 1; // Cong len 1 cho o trong
		$rowNhaCungCap = 22 + ($rowCoSo - 9) + 1 + 1;

		foreach ($supp as $item) {
			$data = new stdClass(); // reset
			$data->STT = ++$stt;
			$data->TenNhaCungCap = $item->TenDoiTac;
			$data->DiaChi = $item->DiaChi;
			$data->TelFax = $item->DienThoai;
			$data->TelFax .= ($item->DienThoai && $item->Fax) ? '/' : '';
			$data->TelFax .= $item->Fax;
			$data->NguoiLienHe = '';

			$file->newGridRow(array('sub' => $data), $rowNhaCungCap, $rowNhaCungCapTem);
			//$file->mergeCells('B'.$rowNhaCungCap.':C'.$rowNhaCungCap);
			//$file->mergeCells('E'.$rowNhaCungCap.':F'.$rowNhaCungCap);

			$rowNhaCungCap++;
		}

//echo '<pre>'; print_r($rowNhaCungCapTem); die;
		//        echo '<pre>'; print_r($rowNhaCungCapTem);
		//        echo '<pre>'; print_r($rowNhaCungCap);die;

		$rowNhaCungCapBatDau = $rowNhaCungCapTem - 1;

		$file->removeRow($rowNhaCungCapTem);

		$file->removeRow($rowNhaCungCapBatDau);

		$file->removeRow(8);

		$ChiDinhThau = ($plan && $plan->HinhThuc == 1) ? 'þ' : 'o';
		$ChaoGiaCanhTranh = ($plan && $plan->HinhThuc == 2) ? 'þ' : 'o';
		$DauThau = ($plan && $plan->HinhThuc == 3) ? 'þ' : 'o';

		$file->setCellValue('B' . $rowCoSo, $ChiDinhThau);
		$file->setCellValue('E' . $rowCoSo, $ChaoGiaCanhTranh);
		$file->setCellValue('G' . $rowCoSo, $DauThau);

		$file->save();

		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function dondathangAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Don dat hang.xlsx\"");

		$orderIOID = $this->params->requests->getParam('orderioid', 0);
		$planIOID = $this->params->requests->getParam('planioid', 0);
		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DonDatHang.xlsx');
		$mOrder = new Qss_Model_Purchase_Order();
		$order = $mOrder->getOrderInfo($orderIOID, $planIOID);
		$mRequest = new Qss_Model_Purchase_Request();
		$requests = $mRequest->getRequestsByPlan($planIOID);
		$main = new stdClass();
		$tempDuAn = array();

		foreach ($requests as $item) {
			if ($item->DuAn && !in_array($item->Ref_DuAn, $tempDuAn)) {
				$tempDuAn[$item->Ref_DuAn] = $item->DuAn;
			}

		}

		$ngayDatHang = ($order && $order->NgayDatHang) ? $order->NgayDatHang : false;
		$ngayDatHang = ($ngayDatHang != false) ? date_create($ngayDatHang) : false;

//        $main->Ngay  = $ngayDatHang?$ngayDatHang->format('d'):'';
		//        $main->Thang = $ngayDatHang?$ngayDatHang->format('m'):'';
		//        $main->Nam   = $ngayDatHang?$ngayDatHang->format('Y'):'';

		if ($ngayDatHang !== false) {
			$main->Ngay = $ngayDatHang->format('d');
			$main->Thang = $ngayDatHang->format('m');
			$main->Nam = $ngayDatHang->format('Y');
		} else {
			$main->Ngay = str_repeat(' ', 10);
			$main->Thang = str_repeat(' ', 10);
			$main->Nam = str_repeat(' ', 10);
		}

		$main->MaDuAn = implode(', ', $tempDuAn);

		$main->SoDonHang = $order ? $order->SoDonHang : '';
		$main->TenDoiTac = $order ? $order->TenDoiTac : '';
		$main->DiaChi = $order ? $order->DiaChi : '';
		$main->DienThoai = $order ? $order->DienThoai : '';
		$main->Fax = $order ? $order->Fax : '';
		$main->SoBaoGia = $order ? $order->SoBaoGia : '';
		$main->NgayBaoGia = $order ? Qss_Lib_Date::mysqltodisplay($order->NgayBaoGia) : '';

		$data = array('main' => $main);

		$file->init($data);

		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Danh muc mua hang <Danh muc thiet bi>
	 * @todo: Thieu xuat su
	 */
	public function phuluchopdongAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Danh muc thiet bi_HHMN.xlsx\"");

		$orderIOID = $this->params->requests->getParam('orderioid', 0);
		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhMucThietBi_HHMN.xlsx');
		$mOrder = new Qss_Model_Purchase_Order();
		$order = $mOrder->getOrderByIOID($orderIOID);
		$items = $mOrder->getOrderLineGroupByRequest($orderIOID); // Mat hang cua ke hoach xep theo phieu yeu cau
		$main = new stdClass();
		$row = 5; // Dong bat dau in du lieu mat hang
		$stt = 0; // So thu tu
		$oldReq = '';
		$tongTien = 0;

		$main->Ngay = str_repeat(' ', 10);
		$main->Thang = str_repeat(' ', 10);
		$main->Nam = str_repeat(' ', 10);
		$main->SoDonHang = $order ? $order->SoDonHang : '';
		$main->NgayDatHang = $order ? Qss_Lib_Date::mysqltodisplay($order->NgayDatHang) : '';

		$data = array('main' => $main);

		// Init du lieu cua mang main
		$file->init($data);

//        echo '<pre>'; print_r($items); die;
		foreach ($items as $item) {
			// Nhom mat hang theo yeu cau
			if ($oldReq !== $item->Ref_SoYeuCau) {
				$data = new stdClass(); // reset
				$data->SoYeuCau = $item->SoYeuCau;
				$file->newGridRow(array('sub' => $data), $row, 3);
				$row++;
			}

			$data = new stdClass();
			$data->STT = ++$stt;
			$data->TenMatHang = $item->TenSanPham;
			$data->DacTinhKyThuat = $item->DacTinhKyThuat;
			$data->DonViTinh = $item->DonViTinh;
			$data->SoLuong = $item->SoLuong;
			$data->DonGia = $item->DonGia;
			$data->ThanhTien = $item->ThanhTien;
			$data->ThoiGianGiaoHang = $item->NgayGiaoHang;
			$data->XuatSu = '';
			$file->newGridRow(array('sub' => $data), $row, 4);
			$row++;

			$tongTien += $item->ThanhTien;
			$oldReq = $item->Ref_SoYeuCau;
		}

		$main = new stdClass();
		$main->TongTienBangSo = Qss_Lib_Util::formatMoney($tongTien);
		$main->TongTienBangChu = Qss_Lib_Util::VndText($tongTien / 1000);
		$data = array('total' => $main);
		$file->init($data);

		$file->removeRow(4);
		$file->removeRow(3);

		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Danh gia nha cung cap <Danh muc thiet bi>
	 * @todo: Thieu ngay phe duyet
	 * @todo: Thieu so luong ho so
	 * @todo: Thieu tinh trang <nhan fax>
	 * @todo: Thieu ghi chu <hop le>
	 * @todo: Thieu phan so sanh
	 */
	public function danhgianccAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Danh gia NCC.xlsx\"");

		$planIOID = $this->params->requests->getParam('planioid', 0);

		$mRequest = new Qss_Model_Purchase_Request();
		$mPlan = new Qss_Model_Purchase_Plan();
		$mQuotation = new Qss_Model_Purchase_Quotation();
		$main = new stdClass();
		$supp = $mPlan->getSuppliersForPlan($planIOID);
		$plan = $mPlan->getPlanByIOID($planIOID);
		$requests = $mRequest->getRequestsByPlan($planIOID);
		$customer = array();
		$stt = 0; // Danh cho vong lap danh sach nha cung cap
		$stt2 = 0; // Danh cho danh gia chat luong ky thuat
		$stt3 = 0;
		$row = 10;
		$temSupp = new stdClass();
		$temIDSupp = array();
		$iTemSupp = 0;
		$soNhaCungCapHopLe = 0;
		$lastQuotes = $mQuotation->getLastQuotesOfPlan($planIOID);
		$countItems = count((array) $lastQuotes);
		$excel_col = array_flip(Qss_Lib_Const::$EXCEL_COLUMN);
		$totalSupp = count($supp); // So luong nha cung cap thuc te
		$totalSuppValid = $totalSupp; // So luong nha cung cap hop le
		$totalItems = 0;

		foreach ($supp as $partner) {
			if ($partner->NhaCungCapKhongHopLe) {
				$totalSuppValid -= 1;
			}
		}

		switch ($totalSuppValid) {
		case 1:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_1NCC.xlsx');
			break;

		case 2:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_2NCC.xlsx');
			break;

		case 3:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_3NCC.xlsx');
			break;

		case 4:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_4NCC.xlsx');
			break;

		case 5:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_5NCC.xlsx');
			break;

		case 6:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_6NCC.xlsx');
			break;

		default:
			$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/DanhGiaNCC_6NCC.xlsx');
			break;
		}

		foreach ($requests as $item) {
			if ($item->KhachHang && !in_array($item->Ref_KhachHang, $customer)) {
				$customer[$item->Ref_MaNCC] = $item->KhachHang;
			}
		}

		$thoiGianThuMoi = $plan->NgayYeuCau ? $plan->NgayYeuCau : '';
		$thoiGianThuMoi = $thoiGianThuMoi ? date_create($thoiGianThuMoi) : false;

		if ($thoiGianThuMoi !== false) {
			$main->NgayPheDuyet = $thoiGianThuMoi->format('d');
			$main->ThangPheDuyet = $thoiGianThuMoi->format('m');
			$main->NamPheDuyet = $thoiGianThuMoi->format('Y');
		} else {
			$main->NgayPheDuyet = str_repeat(' ', 10);
			$main->ThangPheDuyet = str_repeat(' ', 10);
			$main->NamPheDuyet = str_repeat(' ', 10);
		}

		$main->SoKeHoach = $plan ? $plan->SoPhieu : '';
		$main->KhachHang = implode(', ', $customer);
		$main->HinhThuc = $plan->Ref_HinhThuc;
		$main->SoBaoGia = $totalSuppValid; // $mPlan->countQuotationsForPlan($planIOID);
		$main->SoNhaCungCap = $totalSupp; // $mPlan->countSuppliersForPlan($planIOID);
		$data = array('main' => $main);
		$file->init($data);

		// In danh sach nha cung cap
		foreach ($supp as $item) {
			// Ghi lai danh sach cac nha cung cap
			if ($item->NhaCungCapKhongHopLe == 0) {
				$temSupp->{'NhaCungCap' . ++$iTemSupp} = $item->TenDoiTac;
				$temIDSupp[] = $item->Ref_MaNCC;

				$soNhaCungCapHopLe++;
			}

			$data = new stdClass();
			$data->STT = ++$stt;
			$data->TenNhaCungCap = $item->TenDoiTac;
			$data->SoLuongHoSo = @$item->SoLuongHoSo;
			$data->TinhTrang = @$item->TinhTrang;
			$data->GhiChu = ($item->SoBaoGiaHopLe > 0) ? 'Hợp lệ' : 'Không hợp lệ';
			$file->newGridRow(array('sub' => $data), $row, 9);

			// merge
			//$file->mergeCells('B'.$row.':E'.$row);
			//$file->mergeCells('F'.$row.':G'.$row);
			//$file->mergeCells('H'.$row.':J'.$row);
			//$file->mergeCells('K'.$row.':M'.$row);

			$row++;
		}

		// So nha cung cap hop le
		$main = new stdClass();
		$main->SoNhaCungCapHopLe = $soNhaCungCapHopLe;
		$data = array('main' => $main);
		$file->init($data);

		if ($iTemSupp < $totalSuppValid) {
			for ($iTemp = $iTemSupp; $iTemp < $totalSuppValid; $iTemp++) {
				$temIDSupp[] = 0;
				$temSupp->{'NhaCungCap' . ($iTemp + 1)} = '';
			}
		}

		$data = array('supplier' => $temSupp);
		$file->init($data);

		$row2Copy = 17 + ($row - 9) - 1;
		$row2 = 17 + ($row - 9);
		$arrKyThuat = array();
		$datTheoNhaCungCap = array();
		$tatCaDat = true;
		$tempNhanXetSanPhamDat = '';

//        echo $row2Copy; die;

//        echo $row2Copy; die;

		// Danh gia chat luong
		foreach ($lastQuotes as $item) {
			$data = new stdClass();
			$data->STT = ++$stt2;
			$data->TenMatHang = $item->TenSP;
			$data->DacTinhKyThuat = $item->DacTinhKyThuat;
			$data->SoLuong = $item->SoLuongBaoGia;
			$data->DonViTinh = $item->DonViTinh;
			$iTemp = 1;

			foreach ($temIDSupp as $id) {
				if ($item->{'KhongHopLe_' . $id}) {continue;}

				$KyThuat = '';
				$iKyThuat = 0;

				if ((isset($item->{"RefKyThuat_{$id}"}))) {
					$KyThuat = $item->{"RefKyThuat_{$id}"};
					$iKyThuat = $item->{"KyThuat_{$id}"};

					if ((isset($item->{"KhongChaoGia_{$id}"})) && $item->{"KhongChaoGia_{$id}"}) {
						$KyThuat = 'Không chào giá';
					}
				}

				$arrKyThuat[(int) $id][(int) $item->Ref_MaSP] = (int) $iKyThuat;

				if (!isset($datTheoNhaCungCap[(int) $id])) {
					$datTheoNhaCungCap[(int) $id] = 0;
				}

				$datTheoNhaCungCap[(int) $id] += (int) $iKyThuat ? 1 : 0;

				$data->{"NhaCungCap{$iTemp}Dat"} = $KyThuat;
				$iTemp++;
			}

			// echo '<pre>'; print_r($data); die;

			$file->newGridRow(array('sub2' => $data), $row2, $row2Copy);

			// merge
			//$file->mergeCells('F'.$row2.':G'.$row2);
			//$file->mergeCells('H'.$row2.':I'.$row2);
			//$file->mergeCells('J'.$row2.':K'.$row2);
			//$file->mergeCells('L'.$row2.':M'.$row2);
			//$file->mergeCells('N'.$row2.':O'.$row2);
			//$file->mergeCells('P'.$row2.':Q'.$row2);

			$row2++;
		}

		foreach ($supp as $item) {
			$dat = isset($datTheoNhaCungCap[$item->Ref_MaNCC]) ? $datTheoNhaCungCap[$item->Ref_MaNCC] : 0;
			if ($dat != $countItems) {
				$tatCaDat = false;
			}

			if ($dat) {
				$tempNhanXetSanPhamDat .= "- {$item->TenNCC} chào giá và đạt yêu cầu kỹ thuật {$dat}/{$countItems} hạng mục hàng hóa. \n";
			}

		}

		$tempNhanXetSanPhamDat = $tatCaDat ? "- Cả {$soNhaCungCapHopLe} Công ty đều đạt yêu cầu kỹ thuật" : $tempNhanXetSanPhamDat;
		$tempNhanXetSanPhamDat = $tempNhanXetSanPhamDat ? $tempNhanXetSanPhamDat : 'Không có công ty nào đáp ứng yêu cầu kỹ thuật';
		$dat = new stdClass();
		$dat->NhanXetNhaCungCap = $tempNhanXetSanPhamDat;
		$data = array('gen' => $dat);
		$tongTien = array();

		$file->init($data);

		$row3Copy = $row2 + 6;
		$row3 = $row2 + 6 + 1;
		$selectedCell = array();
		$nhanXet = '';
		$itemDuocChon = array();
		$tongTienTheoItemDuocChon = array();
		$ketLuan = '';
		$deXuat = '';

		// So sanh don gia
		foreach ($lastQuotes as $item) {
			$data = new stdClass();
			$data->STT = ++$stt3;
			$data->TenMatHang = $item->TenSP;
			$data->DacTinhKyThuat = $item->DacTinhKyThuat;
			$data->SoLuong = $item->SoLuongYeuCau;
			$data->DonViTinh = $item->DonViTinh;
			$iTemp = 1;
			$selectedCell = array();
			$cell = 0;

			foreach ($temIDSupp as $id) {
				if ($item->{'KhongHopLe_' . $id}) {continue;}

				++$cell;

				if ($item->ChonBaoGiaSanPham && $item->ChonBaoGiaSanPham == $id) {
					$selectedCell[] = $cell;

					if (!isset($itemDuocChon[$id])) {
						$itemDuocChon[$id] = 0;
					}

					if (!isset($tongTienTheoItemDuocChon[$id])) {
						$tongTienTheoItemDuocChon[$id] = 0;
					}

					$tongTienTheoItemDuocChon[$id] += isset($item->{"ThanhTien_{$id}"}) ? $item->{"ThanhTien_{$id}"} : 0;
					$itemDuocChon[$id]++; // Count
				}

				$donGia = isset($item->{"DonGia_{$id}"}) ? $item->{"DonGia_{$id}"} : 0;
				$thanhTien = isset($item->{"ThanhTien_{$id}"}) ? $item->{"ThanhTien_{$id}"} : 0;

				if (!isset($tongTien[$id])) {
					$tongTien[$id] = 0;
				}

				$tongTien[$id] += $thanhTien;

				$donGia = $donGia;
				$thanhTien = $thanhTien;

				$data->{"DonGiaNCC{$iTemp}"} = $donGia / 1000;
				$data->{"ThanhTienNCC{$iTemp}"} = $thanhTien / 1000;
				$iTemp++;
			}

			// echo '<pre>'; print_r($data); die;

			$file->newGridRow(array('sub3' => $data), $row3, $row3Copy);

			foreach ($selectedCell as $cellTemp) {
				$cell1 = ($cellTemp * 2) + 5 - 1;
				$cell2 = ($cellTemp * 2) + 5;
				$temp1 = $excel_col[$cell1] . $row3;
				$temp2 = $excel_col[$cell2] . $row3;
				$file->setStyles($temp1, '', 'FFFF00', true);
				$file->setStyles($temp2, '', 'FFFF00', true);
			}

			$row3++;
		}

		$iTemp = 1;
		$gen = new stdClass();
		$duocChon = 0;

		foreach ($supp as $s) {
			$gen->{"TongThanhTienNCC{$iTemp}"} = isset($tongTien[$s->Ref_DoiTac]) ? $tongTien[$s->Ref_DoiTac] / 1000 : 0;
			$gen->{"DiaDiemGiaoHangNCC{$iTemp}"} = 'Xưởng CKBD';
			$gen->{"ThoiGianGiaoHangNCC{$iTemp}"} = $s->ThoiGianGiaoHang . ' kể từ ngày nhận đơn đặt hàng';
			$gen->{"HinhThucThanhToanNCC{$iTemp}"} = '45 ngày';

			if (isset($itemDuocChon[$s->Ref_DoiTac])) {
				$tongTienTemp = isset($tongTienTheoItemDuocChon[$s->Ref_DoiTac]) ? $tongTienTheoItemDuocChon[$s->Ref_DoiTac] : 0;
				$tongTienTemp2 = $tongTienTemp / 1000;

				$nhanXet .= "- " . $s->TenDoiTac . " có " . $itemDuocChon[$s->Ref_DoiTac] . " mục của đơn hàng có giá chào là cạnh tranh nhất và thời gian giao hàng đáp ứng yêu cầu của dự án.\n";

				$ketLuan .= "+ " . $s->TenDoiTac . " sẽ cung cấp " . $itemDuocChon[$s->Ref_DoiTac]
				. " hạng mục hàng hóa có giá chào cạnh tranh nhất nói trên cho Công ty LĐBD với tổng giá trị là:  "
				. Qss_Lib_Util::formatMoney($tongTienTemp) . " (" . Qss_Lib_Util::VndText($tongTienTemp2) . ") - chưa bao gồm thuế VAT 10%\n";

				$deXuat .= "- Lựa chọn và ký đơn đặt hàng với " . $itemDuocChon[$s->Ref_DoiTac] . " cung cấp " . $itemDuocChon[$s->Ref_DoiTac] . " mục là cạnh tranh nhất, với tổng giá trị đơn hàng là:" . Qss_Lib_Util::formatMoney($tongTienTemp) . " (" . Qss_Lib_Util::VndText($tongTienTemp2) . ") - chưa bao gồm thuế VAT 10%\n";

				$duocChon++;
			}

			$iTemp++;
		}

		for ($iTemp2 = $iTemp; $iTemp2 <= $totalSuppValid; $iTemp2++) {
			$gen->{"TongThanhTienNCC{$iTemp2}"} = 0;
			$gen->{"DiaDiemGiaoHangNCC{$iTemp2}"} = 'Xưởng CKBD';
			$gen->{"ThoiGianGiaoHangNCC{$iTemp2}"} = '';
			$gen->{"HinhThucThanhToanNCC{$iTemp2}"} = '45 ngày';
		}

		$gen->NhanXet = $nhanXet;
		$gen->KetLuan = $ketLuan;
		$gen->DeXuat = $deXuat;
		$gen->Ngay = date('d');
		$gen->Thang = date('m');
		$gen->Nam = date('Y');

		$data = array('gen1' => $gen);
		$file->init($data);

		$data = array('supplier1' => $temSupp);
		$file->init($data);

		$file->removeRow($row3Copy);
		$file->removeRow($row2Copy);
		$file->removeRow(9);

		$RowNhanXetNhaThau = $row2 - 1 - 1;
		$file->setRowHeight($RowNhanXetNhaThau, 30 * (count($supp)));

		$duocChon = $duocChon ? $duocChon : 1;
		$RowNhanXet = $row3 + 5 - 3;
		$file->setRowHeight($RowNhanXet, 30 * (($duocChon)));

		$RowKetLuan = $RowNhanXet + 2;
		$file->setRowHeight($RowKetLuan, 30 * (($duocChon + 1)));

		$RowDeXuat = $RowKetLuan + 1;
		$file->setRowHeight($RowDeXuat, 30 * (($duocChon + 1)));

		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function thongbaoxulyAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Thong bao giao hang.xlsx\"");

		$planIOID = $this->params->requests->getParam('planioid', 0);
		$session = $this->params->requests->getParam('session', 0);
		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M415/TBKetQuaXuLyYCCC.xlsx');
		$mOrder = new Qss_Model_Purchase_Order();
		$items = $mOrder->getOrderLinesBySession($session); // Mat hang cua ke hoach xep theo phieu yeu cau
		$main = new stdClass();
		$row = 5; // Dong bat dau in du lieu mat hang
		$stt = 0; // So thu tu
		$oldReq = '';
		$oldSupp = '';
		$suppStart = $row;
		$suppRange = 0;

		$main->Ngay = str_repeat(' ', 10);
		$main->Thang = str_repeat(' ', 10);
		$main->Nam = str_repeat(' ', 10);
		$data = array('main' => $main);

		// Init du lieu cua mang main
		$file->init($data);

		foreach ($items as $item) {
			// Nhom mat hang theo yeu cau
			if ($oldReq !== $item->Ref_SoYeuCau) {
				$data = new stdClass(); // reset
				$data->SoYeuCau = $item->SoYeuCau;
				$file->newGridRow(array('sub' => $data), $row, 3);
				//$file->mergeCells('A'.$row.':H'.$row);
				$row++;
			}

			if ($oldReq !== $item->Ref_SoYeuCau || $oldSupp != $item->Ref_MaNCC) {
				if ($oldSupp != '') {
					$suppEnd = $suppStart + ($suppRange - 1);
					//$file->mergeCells('H'.$suppStart.':H'.$suppEnd);
				}

				$suppStart = $row;
				$suppRange = 0;
			}

			$data2 = new stdClass();

			if ($oldReq !== $item->Ref_SoYeuCau || $oldSupp != $item->Ref_MaNCC) {
				$data2->DonViGiao = $item->TenNCC;
			} else {
				$data2->DonViGiao = '';
			}
			$suppRange++;

			$data2->STT = ++$stt;
			$data2->TenThietBi = $item->TenSanPham;
			$data2->DacTinhKyThuat = $item->DacTinhKyThuat;
			$data2->DonViTinh = $item->DonViTinh;
			$data2->SoLuong = $item->SoLuong;
			$data2->XuatXu = '';
			$data2->ThoiGianGiao = Qss_Lib_Date::mysqltodisplay($item->NgayGiaoHang);

			$file->newGridRow(array('sub2' => $data2), $row, 4);

			$oldSupp = $item->Ref_MaNCC;
			$oldReq = $item->Ref_SoYeuCau;
			$row++;
		}

		if ($oldSupp != '') {
			$suppEnd = $suppStart + ($suppRange - 1);
			$file->mergeCells('H' . $suppStart . ':H' . $suppEnd);
		}

		$file->removeRow(4);
		$file->removeRow(3);

		////$file->mergeCells('G4:H5');

		$file->save();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}