    <?php
/**
 * Class Static_M405Controller
 * Xử lý kế hoạch mua sắm
 * Purchase request process
 */
class Static_M780Controller extends Qss_Lib_Controller {
	// Model
	public $_common;
	public $_model;

	// Loai NODE dung cho module "Thong tin thiet bi"
	const NODE_TYPE_NONE = 'NONE';
	const NODE_TYPE_EQUIP_ONLY = 'EQ_ONLY';
	const NODE_TYPE_LOCATION = 'LOCATION';
	const NODE_TYPE_EQUIP_GROUP = 'EQ_GROUP';
	const NODE_TYPE_EQUIP_TYPE = 'EQ_TYPE';
	const NODE_TYPE_EQUIP = 'EQUIP';
	const NODE_TYPE_COMPONENT = 'COMPONENT';
	const NODE_TYPE_PROJECT = 'PROJECT';
	const FULL_VIEW = 2;
	const LESS_VIEW = 1;

	public function init() {
		//$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/wide.php';
		parent::init();
		// Model
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_model = new Qss_Model_Extra_Maintenance();

		// Load script (Co ve khong hoat dong)
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		//$this->headScript($this->params->requests->getBasePath() . '/js/jquery.scannerdetection.js');
		if ($this->_mobile) {
			$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mwide.php';
		}
	}

	public function indexAction() {
		$this->html->deptid = $this->_user->user_dept_id;

	}

	// ============================ HIỂN THỊ CÂY THIẾT BỊ ============================

	/**
	 * Hien thi tree thiet bi
	 */
	public function SearchAction() {
		// PARAMS
		$nodeType = $this->params->requests->getParam('nodeType', self::NODE_TYPE_NONE);
		$nodeID = $this->params->requests->getParam('nodeID', 0);
		$nodeIFID = $this->params->requests->getParam('nodeIFID', 0);
		$nodeIOID = $this->params->requests->getParam('nodeIOID', 0);
		$viewtype = $this->params->requests->getParam('viewtype', 0);

		// @thinh-2015-04-08-A1-1: bo bien $groupEq, bo ba hang so GROUP_EQ_
		$groupEq = $this->params->requests->getParam('groupEq', Qss_Model_Extra_Equip::GROUP_EQ_NONE);

		// @thinh-2015-04-08-A1-2: bo bien $groupEq
		echo Qss_Json::encode($this->getNodeData($nodeType, $nodeID, $nodeIFID, $nodeIOID, $groupEq, $viewtype));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Lay cac node con cua mot node bat ky
	 * @param type $nodeType
	 * @param type $nodeID
	 * @param type $nodeIFID
	 * @param type $nodeIOID
	 * @param type $groupEq
	 * @return type
	 */
	// @thinh-2015-04-08-A1-3: bo bien $groupEq, khong nhom ma nhom mac dinh theo loai thiet bi
	private function getNodeData($nodeType, $nodeID, $nodeIFID, $nodeIOID, $groupEq = Qss_Model_Extra_Equip::GROUP_EQ_BY_TYPE, $viewType = self::FULL_VIEW) {
		$returnData = array();
		// Hien thi danh sach khu vuc co level thap nhat (Cac khu vuc root)
		// Khi moi vao module chua chon tree menu
		if ($nodeType == self::NODE_TYPE_NONE) {
			$returnData = $this->getDataOfNodeTypeNone($viewType);
		}
		// Hien thi thanh phan con cua mot khu vuc bao gom khu vuc va thiet bi
		elseif ($nodeType == self::NODE_TYPE_LOCATION) {
			// @thinh-2015-04-08-A1-10: bo bien $groupEq (OK) ($nodeType, $nodeID, $nodeIOID, $groupEq)
			$returnData = $this->getDataOfNodeTypeLocation($nodeType, $nodeID, $nodeIOID);
		}
		// Hien thi nhom thiet bi
		elseif ($nodeType == self::NODE_TYPE_EQUIP_GROUP) {
			$returnData = $this->getDataOfNodeTypeEquipGroup($nodeType, $nodeIOID);
		}
		// Hien thi loai thiet bi
		elseif ($nodeType == self::NODE_TYPE_EQUIP_TYPE) {
			$returnData = $this->getDataOfNodeTypeEquipType($nodeType, $nodeIOID);
		}
		// Hien thi du an
		elseif ($nodeType == self::NODE_TYPE_PROJECT) {
			$returnData = $this->getDataOfNodeTypeProject($nodeType, $nodeIOID, $nodeIFID);
		}
		// Hien thi thanh phan cau truc cua mot thiet bi (Bac1)
		elseif ($nodeType == self::NODE_TYPE_EQUIP) {
			$returnData = $this->getDataOfNodeTypeEquip($nodeType, $nodeID, $nodeIFID, $nodeIOID);

		}
		// Hien thi thanh phan ben trong cau truc (Hien thi thanh phan ben trong mot cau truc)
		elseif ($nodeType == self::NODE_TYPE_COMPONENT) {
			$returnData = $this->getDataOfNodeTypeComponent($nodeType, $nodeID, $nodeIFID, $nodeIOID);
		} elseif ($nodeType == self::NODE_TYPE_EQUIP_ONLY) {
			$search = $this->params->requests->getParam('search');
			$returnData = $this->getDataOfEquipOnly($search);
		}
		return $returnData;
	}

	/**
	 * Lay cay thu muc goc hien thi khu vuc goc + cac thiet bi khong thuoc khu vuc nao (hien thi loai thiet bi cua tb)
	 * @param type $groupEq
	 * @return type
	 */
	private function getDataOfNodeTypeNone($viewType) {
		// @thinh-2015-04-08-A1-4: bo bien $groupEq
		//$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$returnData = array();
		$i = 0;

		if ($viewType == self::FULL_VIEW) {
			$equipModel = new Qss_Model_Extra_Equip();
			$locIOID = array();
			$rootLoc = $this->getRootLoc();

			foreach ($rootLoc as $l) {
				$locIOID[] = $l->IOID;
			}

			$locStop = $this->checkLocationsStopOrRun($locIOID);

			foreach ($rootLoc as $l) {
				$text = "{$l->MaKhuVuc} - {$l->Ten}";

				if (isset($locStop[$l->IOID]) && $locStop[$l->IOID]) {
					$text = "<del class=\"light-grey\">{$text}</text>";
				}

				$returnData[$i]['id'] = $l->NodeType . $l->IOID;
				$returnData[$i]['parent'] = '#';
				$returnData[$i]['icon'] = '/images/jstree/location.png';
				$returnData[$i]['text'] = "{$text} " . $this->displayNumOfEquipInNode($l->NodeType, $l->IOID);
				$returnData[$i]['attr']['nodeid'] = $l->IOID;
				$returnData[$i]['attr']['nodetype'] = $l->NodeType;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M720;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['loccode'] = $l->MaKhuVuc;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = $l->Child ? true : false;
				$i++;
			}

			// Khi cau hinh loai thiet bi theo nhom thiet bi thi hien thi them ca nhom thiet bi ra
			if (Qss_Lib_System::fieldActive('OLoaiThietBi', 'NhomThietBi')) {

				$rootEq = $equipModel->getEquipGroupOfEquipNotInAnyWhere(); // Hien thi loai thiet bi cua tb

				foreach ($rootEq as $l) {
					//Chưa phân loại TB
					$returnData[$i]['id'] = self::NODE_TYPE_EQUIP_GROUP . @(int) $l->IOID . '0';
					$returnData[$i]['parent'] = '#';
					$returnData[$i]['icon'] = '/images/jstree/eq_group.png';
					$returnData[$i]['text'] = $l->IOID ? $l->Name : 'Uncategory';
					$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(
						self::NODE_TYPE_EQUIP_GROUP, 0, $l->IOID);
					$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID;
					$returnData[$i]['attr']['locioid'] = 0;
					$returnData[$i]['attr']['eqtype'] = @$l->Name;
					$returnData[$i]['attr']['loccode'] = '';
					$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP_GROUP;
					$returnData[$i]['attr']['eqtypelevel'] = 1;
					$returnData[$i]['state']['opened'] = false;
					$returnData[$i]['children'] = true;
					$i++;
				}
			} else {
				$rootEq = $equipModel->getEquipTypeOfEquipNotInAnyWhere(); // Hien thi loai thiet bi cua tb

				foreach ($rootEq as $l) {
					//Chưa phân loại TB
					$returnData[$i]['id'] = self::NODE_TYPE_EQUIP_TYPE . @(int) $l->IOID . '0';
					$returnData[$i]['parent'] = '#';
					$returnData[$i]['icon'] = '/images/jstree/eq_type.png';
					$returnData[$i]['text'] = $l->IOID ? $l->Name : 'Uncategory';
					$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(
						self::NODE_TYPE_EQUIP_TYPE, 0, $l->IOID
					);
					$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID;
					$returnData[$i]['attr']['locioid'] = 0;
					$returnData[$i]['attr']['eqtype'] = @$l->Name;
					$returnData[$i]['attr']['loccode'] = '';
					$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP_TYPE;
					$returnData[$i]['attr']['eqtypelevel'] = 1;
					$returnData[$i]['state']['opened'] = false;
					$returnData[$i]['children'] = true;
					$i++;
				}
			}

		} else {
			$eqIOIDArr = array(0);
			$mEquip = new Qss_Model_Extra_Equip();
			$equips = $mEquip->getHightestLevelEquips($this->_user);

			foreach ($equips as $l) {
				$eqIOIDArr[] = $l->IOID;
			}

			$wCalendarLib = new Qss_Lib_Extra_WCalendar();
			$wCalendarLib->initEquipCals($eqIOIDArr, array(), date('Y-m-d'), date('Y-m-d'));
			$pause = $wCalendarLib->checkEquipPauseOrRun(date('h:i:s'));
			$stop = $wCalendarLib->checkEquipStopOrRun();

			// Cac thiet bi truc thuoc loai thiet bi khong thuoc du an nao
			foreach ($equips as $l) {
				if (isset($l->IOID) && $l->IOID) {
					$text = $l->MaThietBi . ' - ' . $l->TenThietBi;
					if (isset($stop[$l->IOID]) && $stop[$l->IOID]) {
						$text = '<del class="light-grey">' . $text . '</del>';
					}
//                    if(!isset($pause[$l->IOID]) || $pause[$l->IOID])
					//                    {
					//                        $text = '<span class="red">'.$text.' (Off)</span>';
					//                    }

					$returnData[$i]['id'] = self::NODE_TYPE_EQUIP . $l->IFID_M705;
					$returnData[$i]['parent'] = '#';
					$returnData[$i]['icon'] = '/images/jstree/eq.png';
					$returnData[$i]['text'] = $text;
					$returnData[$i]['attr']['nodeid'] = $l->IFID_M705;
					$returnData[$i]['attr']['nodeioid'] = $l->IOID;
					$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
					$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP;
					$returnData[$i]['attr']['locioid'] = 0;
					$returnData[$i]['attr']['eqlevel'] = 1;
					$returnData[$i]['state']['opened'] = false;
					$returnData[$i]['children'] = ($l->HasComponent || $l->HasChild) ? true : false;
					$i++;
				}
			}
		}

		return $returnData;
	}

	/**
	 * Lay cay con cua node khu vuc, tra ve co the la khu vuc, nhom tb, loai tb,
	 * thiet bi
	 * @param type $nodeType
	 * @param type $nodeID
	 * @param type $nodeIOID
	 * @param type $groupEq
	 * @return boolean
	 */
	private function getDataOfNodeTypeLocation($nodeType, $nodeID, $nodeIOID) {
		$locModel = new Qss_Model_Extra_Location();
		$equipModel = new Qss_Model_Extra_Equip();

		// Lay khu vuc con ngay duoi khu vuc hien tai
		$nextLoc = $locModel->getNextChild($nodeIOID);
		$i = 0;
		$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$locModel = new Qss_Model_Maintenance_Location();
		$locIOID = array();
		$returnData = array();

		// Khu vuc con cua khu vuc
		if (isset($nextLoc)) {
			foreach ($nextLoc as $l) {
				$locIOID[] = $l->IOID;
			}
			$locStop = $this->checkLocationsStopOrRun($locIOID);

			foreach ($nextLoc as $l) {
				$text = $l->MaKhuVuc . ' - ' . $l->Ten;
				if (isset($locStop[$l->IOID]) && $locStop[$l->IOID]) {
					$text = '<del class="light-grey">' . $text . '</text>';
				}

				$returnData[$i]['id'] = self::NODE_TYPE_LOCATION . $l->IOID;
				$returnData[$i]['parent'] = $nodeType . $nodeID;
				$returnData[$i]['icon'] = '/images/jstree/location.png';
				$returnData[$i]['text'] = $text . ' ' . $this->displayNumOfEquipInNode(self::NODE_TYPE_LOCATION, $l->IOID);
				$returnData[$i]['attr']['nodeid'] = $l->IOID;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M720;
				$returnData[$i]['attr']['locioid'] = $l->IOID;
				$returnData[$i]['attr']['loccode'] = $l->MaKhuVuc;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_LOCATION;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = $this->checkLocHasChild($l->IOID);
				$returnData[$i]['position'] = 100;
				$i++;
			}
		}

		// Khi cau hinh loai thiet bi theo nhom thiet bi thi hien thi them ca nhom thiet bi ra
		if (Qss_Lib_System::fieldActive('OLoaiThietBi', 'NhomThietBi')) {
			// Lay loai thiet bi cua thiet bi truc thuoc khu vuc hien tai  (ko bao gom khu vuc con)
			$equipInLoc = $equipModel->getEquipGroupByEquipsOfLoc($nodeIOID);

			// Loai thiet bi cua thiet bi truc thuoc khu vuc
			if ($equipInLoc) {
				foreach ($equipInLoc as $l) {
					//Chưa phân loại TB
					$returnData[$i]['id'] = self::NODE_TYPE_EQUIP_GROUP . @(int) $l->IOID . @(int) $l->LocationIOID;
					$returnData[$i]['parent'] = $nodeType . $nodeID;
					$returnData[$i]['icon'] = '/images/jstree/eq_group.png';
					$returnData[$i]['text'] = @(int) $l->IOID ? $l->Name : 'Uncategory';
					$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(self::NODE_TYPE_EQUIP_GROUP, $l->LocationIOID, $l->IOID);
					$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID;
					$returnData[$i]['attr']['locioid'] = @(int) $l->LocationIOID;
					$returnData[$i]['attr']['eqtype'] = @$l->Name;
					$returnData[$i]['attr']['loccode'] = @$l->Location;
					$returnData[$i]['attr']['eqtypelevel'] = 1;
					$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP_GROUP;
					$returnData[$i]['state']['opened'] = false;
					$returnData[$i]['children'] = true;
					$i++;
				}
			}
		} else {
			// Lay loai thiet bi cua thiet bi truc thuoc khu vuc hien tai  (ko bao gom khu vuc con)
			$equipInLoc = $equipModel->getEquipTypeByEquipsOfLoc($nodeIOID);

			// Loai thiet bi cua thiet bi truc thuoc khu vuc
			if ($equipInLoc) {
				foreach ($equipInLoc as $l) {
					//Chưa phân loại TB
					$returnData[$i]['id'] = self::NODE_TYPE_EQUIP_TYPE . @(int) $l->IOID . @(int) $l->LocationIOID;
					$returnData[$i]['parent'] = $nodeType . $nodeID;
					$returnData[$i]['icon'] = '/images/jstree/eq_type.png';
					$returnData[$i]['text'] = @(int) $l->IOID ? $l->Name : 'Uncategory';
					$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(self::NODE_TYPE_EQUIP_TYPE, $l->LocationIOID, $l->IOID);
					$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
					$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID;
					$returnData[$i]['attr']['locioid'] = @(int) $l->LocationIOID;
					$returnData[$i]['attr']['eqtype'] = @$l->Name;
					$returnData[$i]['attr']['loccode'] = @$l->Location;
					$returnData[$i]['attr']['eqtypelevel'] = 1;
					$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP_TYPE;
					$returnData[$i]['state']['opened'] = false;
					$returnData[$i]['children'] = true;
					$i++;
				}
			}
		}

		return $returnData;
	}

	private function getDataOfNodeTypeEquipGroup($nodeType, $nodeID) {
		$returnData = array();
		$locIOID = $this->params->requests->getParam('locioid', 0);
		$equipModel = new Qss_Model_Extra_Equip();
		$equipInLoc = $equipModel->getEquipTypeByGroupAndLoc($nodeID, $locIOID);
		$i = 0;

		// Loai thiet bi cua thiet bi truc thuoc khu vuc
		if ($equipInLoc) {
			foreach ($equipInLoc as $l) {
				//Chưa phân loại TB
				$returnData[$i]['id'] = self::NODE_TYPE_EQUIP_TYPE . @(int) $l->IOID . @(int) $l->LocationIOID;
				$returnData[$i]['parent'] = $nodeType . $nodeID . @(int) $l->LocationIOID;
				$returnData[$i]['icon'] = '/images/jstree/eq_type.png';
				$returnData[$i]['text'] = @(int) $l->IOID ? $l->Name : 'Uncategory';
				$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(self::NODE_TYPE_EQUIP_TYPE, $l->LocationIOID, $l->IOID);
				$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
				$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID;
				$returnData[$i]['attr']['locioid'] = @(int) $l->LocationIOID;
				$returnData[$i]['attr']['eqtype'] = @$l->Name;
				$returnData[$i]['attr']['loccode'] = @$l->Location;
				$returnData[$i]['attr']['eqtypelevel'] = 1;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP_TYPE;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = true;
				$i++;
			}
		}

		return $returnData;
	}

	/**
	 * Lay node con cua node loai thiet bi, tra ve danh sach thiet bi ko thuoc du an va du an
	 * @param type $nodeType
	 * @param type $nodeID
	 * @param type $groupEq
	 * @return boolean
	 */
	private function getDataOfNodeTypeEquipType($nodeType, $nodeID) {
		$equipModel = new Qss_Model_Extra_Equip();
		$returnData = array();
		$locIOID = $this->params->requests->getParam('locioid', 0);
		$eqTypeLevel = $this->params->requests->getParam('eqtypelevel', 1);

		// @thinh-2015-04-08-A1-11: bo ham  getEquipByLoc va bien $groupEq
		// $equips  = $this->getEquipByLoc($locIOID, $nodeID, $groupEq);
		$equips = $this->getEquipByEquipTypeAndLocationNotInProject($nodeID, $locIOID); // Chi lay equp lv1
		$projects = $this->getProjectByEquipTypeAndLocation($nodeID, $locIOID);
		$childs = $equipModel->getChildEquipType($locIOID, $nodeID, $eqTypeLevel);

// 		echo '<pre> $equips'; print_r($equips);
		// 		echo '<pre> $projects'; print_r($projects);
		// 		echo '<pre> $childs'; print_r($childs); die;

		$i = 0;

		$eqIOIDArr = array(0);
		foreach ($equips as $l) {
			$eqIOIDArr[] = $l->IOID;
		}

		// 		$stop = $this->checkEquipsStop($eqIOIDArr);
		$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$wCalendarLib->initEquipCals($eqIOIDArr, array(), date('Y-m-d'), date('Y-m-d'));
		$pause = $wCalendarLib->checkEquipPauseOrRun(date('h:i:s'));
		$stop = $wCalendarLib->checkEquipStopOrRun();

		// Du an
		foreach ($projects as $l) {
			$returnData[$i]['id'] = self::NODE_TYPE_PROJECT . @(int) $l->IOID . $locIOID . $nodeID;
			$returnData[$i]['parent'] = $nodeType . $nodeID . $locIOID;
			$returnData[$i]['icon'] = '/images/jstree/tree_icon.png';
			$returnData[$i]['text'] = $l->MaDuAn . ' - ' . $l->TenDuAn;
			$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(self::NODE_TYPE_PROJECT, $locIOID, $nodeID, (int) $l->IOID);
			$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
			$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
			$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID_M803;
			$returnData[$i]['attr']['locioid'] = $locIOID;
			$returnData[$i]['attr']['eqtypeioid'] = $nodeID;
			$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_PROJECT;
			$returnData[$i]['state']['opened'] = false;
			$returnData[$i]['children'] = TRUE;
			$i++;
		}

		// Cac loai thiet bi con cua loai thiet bi
		foreach ($childs as $l) {
			//if($l->LEVEL == ($eqTypeLevel + 1))
			{
				//Chưa phân loại TB
				$returnData[$i]['id'] = self::NODE_TYPE_EQUIP_TYPE . @(int) $l->IOID . $locIOID;
				$returnData[$i]['parent'] = $nodeType . $nodeID . $locIOID;
				$returnData[$i]['icon'] = '/images/jstree/eq_type.png';
				$returnData[$i]['text'] = @(int) $l->IOID ? @$l->Name : 'Uncategory';
				$returnData[$i]['text'] .= ' ' . $this->displayNumOfEquipInNode(self::NODE_TYPE_EQUIP_TYPE, $locIOID, @(int) $l->IOID);
				$returnData[$i]['attr']['nodeid'] = @(int) $l->IOID;
				$returnData[$i]['attr']['nodeioid'] = @(int) $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = @(int) $l->IFID;
				$returnData[$i]['attr']['locioid'] = $locIOID;
				$returnData[$i]['attr']['eqtype'] = @$l->Name;
				$returnData[$i]['attr']['loccode'] = '';
				// $returnData[$i]['attr']['eqtypelevel'] = $l->LEVEL;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP_TYPE;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = true;
				$i++;
			}

		}

		// Cac thiet bi truc thuoc loai thiet bi khong thuoc du an nao
		foreach ($equips as $l) {
			if (isset($l->IOID) && $l->IOID) {
				$text = $l->MaThietBi . ' - ' . $l->TenThietBi;
				if (isset($stop[$l->IOID]) && $stop[$l->IOID]) {
					$text = '<del class="light-grey">' . $text . '</del>';
				}
//                if(!isset($pause[$l->IOID]) || $pause[$l->IOID])
				//                {
				//                    $text = '<span class="red">'.$text.' (Off)</span>';
				//                }

				$returnData[$i]['id'] = self::NODE_TYPE_EQUIP . $l->IFID_M705;
				$returnData[$i]['parent'] = $nodeType . $nodeID . $locIOID;
				$returnData[$i]['icon'] = '/images/jstree/eq.png';
				$returnData[$i]['text'] = $text;
				$returnData[$i]['attr']['nodeid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP;
				$returnData[$i]['attr']['locioid'] = $locIOID;
				$returnData[$i]['attr']['eqlevel'] = 1;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = ($l->HasComponent || $l->HasChild) ? true : false;
				$i++;
			}
		}
		//echo '<pre>'; print_r($returnData); die;
		return $returnData;
	}

	/**
	 * Lay node con cua node du an: tra ve thiet bi
	 * @param unknown $nodeType
	 * @param unknown $nodeID
	 * @param unknown $nodeIFID
	 */
	private function getDataOfNodeTypeProject($nodeType, $nodeID, $nodeIFID) {
		$locationIOID = $this->params->requests->getParam('locioid', 0);
		$equipTypeIOID = $this->params->requests->getParam('eqtypeioid', 0);

		$equipModel = new Qss_Model_Extra_Equip();
		$equips = $equipModel->getEquipByProject($nodeID, $locationIOID, $equipTypeIOID); // Chi lay equip lv 1
		$i = 0;

		$eqIOIDArr = array(0);
		foreach ($equips as $l) {
			$eqIOIDArr[] = $l->IOID;
		}

		// 	    $stop = $this->checkEquipsStop($eqIOIDArr);
		$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$wCalendarLib->initEquipCals($eqIOIDArr, array(), date('Y-m-d'), date('Y-m-d'));
		$pause = $wCalendarLib->checkEquipPauseOrRun(date('h:i:s'));
		$stop = $wCalendarLib->checkEquipStopOrRun();

		foreach ($equips as $l) {
			if (isset($l->IOID) && $l->IOID) {
				$text = $l->MaThietBi . ' - ' . $l->TenThietBi;
				if (isset($stop[$l->IOID]) && $stop[$l->IOID]) {
					$text = '<del class="light-grey">' . $text . '</del>';
				}
//                if(!isset($pause[$l->IOID]) || $pause[$l->IOID])
				//                {
				//                    $text = '<span class="red">'.$text.' (Off)</span>';
				//                }

				$returnData[$i]['id'] = self::NODE_TYPE_EQUIP . $l->IFID_M705;
				$returnData[$i]['parent'] = $nodeType . $nodeID . $locationIOID . $equipTypeIOID;
				$returnData[$i]['icon'] = '/images/jstree/eq.png';
				$returnData[$i]['text'] = $text;
				$returnData[$i]['attr']['nodeid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP;
				$returnData[$i]['attr']['locioid'] = $locationIOID;
				$returnData[$i]['attr']['eqlevel'] = 1;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = ($l->HasComponent || $l->HasChild) ? true : false;
				$i++;
			}
		}

		return $returnData;
	}

	/**
	 * Lay node con cua node thiet bi, tra ve cau truc bo phan cua tb
	 * @param type $nodeType
	 * @param type $nodeID
	 * @param type $nodeIFID
	 * @return type
	 */
	private function getDataOfNodeTypeEquip($nodeType, $nodeID, $nodeIFID, $nodeIOID = 0, $equipLevel = 1) {
		$returnData = array();
		$equipModel = new Qss_Model_Extra_Equip();
		$rootComponent = $equipModel->getRootComponent($nodeIFID);
		$i = 0;
		$equipLevel = $this->params->requests->getParam('eqlevel', 1);
		$comlevel = $this->params->requests->getParam('comlevel', 1);
		$childEquips = $equipModel->getChildEquips($nodeIFID, $equipLevel, $nodeIOID);

		foreach ($childEquips as $l) {
			//if( $l->LEVEL == ($equipLevel + 1) )
			{
				$returnData[$i]['id'] = self::NODE_TYPE_EQUIP . $l->IFID_M705;
				$returnData[$i]['parent'] = $nodeType . $nodeID;
				$returnData[$i]['icon'] = '/images/jstree/eq.png';
				$returnData[$i]['text'] = $l->MaThietBi . ' - ' . $l->TenThietBi;
				$returnData[$i]['attr']['nodeid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP;
				$returnData[$i]['attr']['locioid'] = $l->Ref_MaKhuVuc;
				//$returnData[$i]['attr']['eqlevel']  = $l->LEVEL;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = ($l->HasComponent || $l->HasChild) ? true : false;
				$i++;
			}
		}

		foreach ($rootComponent as $l) {
			$returnData[$i]['id'] = self::NODE_TYPE_COMPONENT . $l->IOID;
			$returnData[$i]['parent'] = $nodeType . $nodeID;
			$returnData[$i]['icon'] = '/images/jstree/component.png';
			$returnData[$i]['text'] = $l->BoPhan . ' - ' . $l->ViTri;
			$returnData[$i]['attr']['nodeid'] = $l->IOID;
			$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
			$returnData[$i]['attr']['nodeioid'] = $l->IOID;
			$returnData[$i]['attr']['com'] = $l->BoPhan;
			$returnData[$i]['attr']['position'] = $l->ViTri;
			$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_COMPONENT;
			$returnData[$i]['attr']['comlevel'] = 1;
			$returnData[$i]['state']['opened'] = false;
			$returnData[$i]['children'] = ($l->HasChild) ? true : false;
			$i++;
		}

		return $returnData;
	}

	/**
	 * Lay node con cua node bo phan, tra ve danh sach bo phan con
	 * @param type $nodeType
	 * @param type $nodeID
	 * @param type $nodeIFID
	 * @param type $nodeIOID
	 * @return type
	 */
	private function getDataOfNodeTypeComponent($nodeType, $nodeID, $nodeIFID, $nodeIOID) {
		$equipModel = new Qss_Model_Extra_Equip();
		$comlevel = $this->params->requests->getParam('comlevel', 1);
		$childComponent = $equipModel->getChildComponent($nodeIFID, $comlevel, $nodeIOID);
		$i = 0;

		foreach ($childComponent as $l) {
			//if( $l->LEVEL == ($comlevel + 1) )
			{
				$returnData[$i]['id'] = self::NODE_TYPE_COMPONENT . $l->IOID;
				$returnData[$i]['parent'] = $nodeType . $nodeID;
				$returnData[$i]['icon'] = '/images/jstree/component.png';
				$returnData[$i]['text'] = $l->BoPhan . ' - ' . $l->ViTri;
				$returnData[$i]['attr']['nodeid'] = $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_COMPONENT;
				// $returnData[$i]['attr']['comlevel'] = $l->LEVEL;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = ($l->HasChild) ? true : false;
				$i++;
			}
		}
		return $returnData;
	}

	private function getDataOfEquipOnly($search) {
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$returnData = array();
		$locIOID = $this->params->requests->getParam('locioid', 0);
		$eqTypeLevel = $this->params->requests->getParam('eqtypelevel', 1);

		$equips = $equipModel->searchEquipmentByCodeAndName($search);

		$i = 0;

		$eqIOIDArr = array(0);
		foreach ($equips as $l) {
			$eqIOIDArr[] = $l->IOID;
		}

		// 		$stop = $this->checkEquipsStop($eqIOIDArr);
		$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$wCalendarLib->initEquipCals($eqIOIDArr, array(), date('Y-m-d'), date('Y-m-d'));
		$pause = $wCalendarLib->checkEquipPauseOrRun(date('h:i:s'));
		$stop = $wCalendarLib->checkEquipStopOrRun();

		// Cac thiet bi truc thuoc loai thiet bi khong thuoc du an nao
		foreach ($equips as $l) {
			if (isset($l->IOID) && $l->IOID) {
				$text = $l->MaThietBi . ' - ' . $l->TenThietBi;
				if (isset($stop[$l->IOID]) && $stop[$l->IOID]) {
					$text = '<del class="light-grey">' . $text . '</del>';
				}
//                if(!isset($pause[$l->IOID]) || $pause[$l->IOID])
				//                {
				//                    $text = '<span class="red">'.$text.' (Off)</span>';
				//                }

				$returnData[$i]['id'] = self::NODE_TYPE_EQUIP . $l->IFID_M705;
				$returnData[$i]['parent'] = '#';
				$returnData[$i]['icon'] = '/images/jstree/eq.png';
				$returnData[$i]['text'] = $text;
				$returnData[$i]['attr']['nodeid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodeioid'] = $l->IOID;
				$returnData[$i]['attr']['nodeifid'] = $l->IFID_M705;
				$returnData[$i]['attr']['nodetype'] = self::NODE_TYPE_EQUIP;
				$returnData[$i]['attr']['locioid'] = $locIOID;
				$returnData[$i]['state']['opened'] = false;
				$returnData[$i]['children'] = ($l->HasComponent) ? true : false;
				$i++;
			}
		}
		//echo '<pre>'; print_r($returnData); die;
		return $returnData;
	}

	// ============================ HIỂN THỊ THÔNG TIN NODE ============================

	// NHOM1: THONG TIN KHU VUC
	public function LocationdetailIndexAction() {
		$loccode = $this->params->requests->getParam('loccode', '');
		$nodeioid = $this->params->requests->getParam('nodeioid', 0);
		$nodeifid = $this->params->requests->getParam('nodeifid', 0);
		$data = $this->_common->getTable(
			array('*')
			, 'OKhuVuc'
			, array('IFID_M720' => $nodeifid)
			, array()
			, 1, 1);

		$this->html->data = $data ? $data : array();
		$this->html->nodeioid = $nodeioid;
		$this->html->nodeifid = $nodeifid;
		$this->html->loccode = $loccode;
	}

	// NHOM3: NHOM THIET BI
	public function GroupdetailIndexAction() {
		$mCommon = new Qss_Model_Extra_Extra();
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$nodeifid = $this->params->requests->getParam('nodeifid', 0);
		$nodeioid = $this->params->requests->getParam('nodeioid', 0);
		$loccode = $this->params->requests->getParam('loccode', '');
		$eqtype = $this->params->requests->getParam('eqtype', '');
		$data = $mCommon->getTableFetchOne('ONhomThietBi', array('IOID' => $nodeioid));

		$this->html->nodeioid = $nodeioid;
		$this->html->loccode = $loccode;
		$this->html->eqtype = $eqtype;
		$this->html->data = $data ? $data : array();
	}

	// NHOM3: LOAI THIET BI
	public function TypedetailIndexAction() {
		$mCommon = new Qss_Model_Extra_Extra();
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$nodeifid = $this->params->requests->getParam('nodeifid', 0);
		$nodeioid = $this->params->requests->getParam('nodeioid', 0);
		$loccode = $this->params->requests->getParam('loccode', '');
		$eqtype = $this->params->requests->getParam('eqtype', '');
		$data = $mCommon->getTableFetchOne('OLoaiThietBi', array('IOID' => $nodeioid));

		$this->html->nodeioid = $nodeioid;
		$this->html->loccode = $loccode;
		$this->html->eqtype = $eqtype;
		$this->html->data = $data ? $data : array();
	}

	// NHOM3: Du an
	public function ProjectdetailIndexAction() {
		$nodeioid = $this->params->requests->getParam('nodeioid', 0);
		$nodeifid = $this->params->requests->getParam('nodeifid', 0);
		$data = $this->_common->getTable(
			array('*')
			, 'ODuAn'
			, array('IFID_M803' => $nodeifid)
			, array()
			, 1, 1);

		$this->html->data = $data ? $data : array();
		$this->html->nodeioid = $nodeioid;
		$this->html->nodeifid = $nodeifid;
	}

	// NHOM4: THIET BI
	public function EquipdetailIndexAction() {
		$this->html->eqID = $this->params->requests->getParam('eqioid', 0);
	}

	//
	/**
	 * NHOM4 - TAB THONG TIN CHUNG: THIET BI
	 * TAB nay gom
	 * - Thong tin co ban cua thiet bi
	 * - Bieu do thong ke loai bao tri theo thiet bi
	 * - Bieu do thong ke thoi gian lam viec cua thiet bi
	 */
<<<<<<< .mine
	public function EquipdetailGeneralAction() {
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$tab = $this->params->requests->getParam('tab', 0);
		$eqIOID = $this->params->requests->getParam('eqID', 0);
		$eqIFID = $this->params->requests->getParam('eqIFID', 0);
||||||| .r9624
	public function EquipdetailGeneralAction()
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$tab         = $this->params->requests->getParam('tab', 0);
		$eqIOID      = $this->params->requests->getParam('eqID', 0);
		$eqIFID      = $this->params->requests->getParam('eqIFID', 0);
=======
	public function EquipdetailGeneralAction()
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$mM780       = new Qss_Model_M780_Main();
		$tab         = $this->params->requests->getParam('tab', 0);
		$eqIOID      = $this->params->requests->getParam('eqID', 0);
		$eqIFID      = $this->params->requests->getParam('eqIFID', 0);
>>>>>>> .r9774
		$movingModel = new Qss_Model_Maintenance_Equip_Moving();
		$moving = $movingModel->getLastMoving($eqIOID);
		//echo '<pre>'; print_r($moving); die;

		// Thong tin thiet bi
		$eq = $this->_common->getTable(array('*')
			, 'ODanhSachThietBi'
			, array('IOID' => $eqIOID)
			, array()
			, 1, 1);
		$eqInfo = $eq ? $eq : new stdClass();

		// Danh sach loai bao tri
		$mtypes = $this->_common->getTable(array('*'), 'OPhanLoaiBaoTri');

		// Thoi gian lam viec cua thiet bi
		//$time = $this->getWorkingTimeOfEquip($eqIOID);

		//$this->html->lichdieudong   = $equipModel->getMoveCalByDateOfEquip(date('Y-m-d'), $eqIOID);
		$this->html->eq = $eqInfo;
		$this->html->moving = $moving ? $moving : array();
//        $this->html->relax          = $time['relax'];
		//        $this->html->work           = $time['work'];
		//        $this->html->down           = $time['down'];
		$this->html->mtypes = $mtypes;
		$this->html->numOfWOByMtype = $this->getTotalMaintTypeByEquip($eqIOID);
<<<<<<< .mine
		$this->html->workingTime = $this->getActiveTimeOfEquip($eqIOID);
		$this->html->deptid = $this->_user->user_dept_id;
||||||| .r9624
		$this->html->workingTime    = $this->getActiveTimeOfEquip($eqIOID);
        $this->html->deptid = $this->_user->user_dept_id;
=======
		$this->html->workingTime    = $mM780->getActiveTimeOfEquip($eqIOID);
        $this->html->resetDate      = $mM780->getLastResetDateOfEquip($eqIOID);
        $this->html->countOn        = $mM780->countSoLanKhoiDongOfEquip($eqIOID);
        $this->html->deptid = $this->_user->user_dept_id;
>>>>>>> .r9774
	}

	public function EquipdetailMonitorsAction() {
		$tab = $this->params->requests->getParam('tab', 0);
		$eqIOID = $this->params->requests->getParam('eqID', 0);
		$eqIFID = $this->params->requests->getParam('eqIFID', 0);
		$hasObjDiemDo = Qss_Lib_System::objectInForm('M705', 'ODanhSachDiemDo');
		$hasObjVanHanh = Qss_Lib_System::objectInForm('M705', 'OThaoTacVanHanh');
		$eq = $this->_common->getTable(array('*')
			, 'ODanhSachThietBi'
			, array('IOID' => $eqIOID)
			, array()
			, 1, 1);

		$this->html->eq = $eq ? $eq : array();
		$this->html->eqIFID = $eqIFID;
		$this->html->hasObjDiemDo = $hasObjDiemDo;
		$this->html->hasObjVanHanh = $hasObjVanHanh;

		if ($hasObjDiemDo) {
			$this->html->monitors = $this->_common->getTable(array('*')
				, 'ODanhSachDiemDo'
				, array('IFID_M705' => $eqIFID)
				, array(), 1000);
		}

		if ($hasObjVanHanh) {
			$this->html->operate = $this->_common->getTable(array('*')
				, 'OThaoTacVanHanh'
				, array('IFID_M705' => $eqIFID)
				, array(), 1000);
		}
	}

	/**
	 * NHOM4 - TAB THONG SO KY THUAT: THIET BI
	 */
	public function EquipdetailTechnoteAction() {
		$tab = $this->params->requests->getParam('tab', 0);
		$eqIOID = $this->params->requests->getParam('eqID', 0);
		$eqIFID = $this->params->requests->getParam('eqIFID', 0);
		$dactinh = Qss_Lib_System::objectInForm('M705', 'ODacTinhThietBi');
		$thongso = Qss_Lib_System::objectInForm('M705', 'OThongSoKyThuatTB');
		$eq = $this->_common->getTable(array('*')
			, 'ODanhSachThietBi'
			, array('IOID' => $eqIOID)
			, array()
			, 1, 1);

		$this->html->eq = $eq ? $eq : array();
		$this->html->eqIFID = $eqIFID;
		$this->html->dactinh = $dactinh;
		$this->html->thongso = $thongso;

		// Thong tin thiet bi
		if ($dactinh) {

		}
		if ($thongso) {
			$this->html->tech = $this->_common->getTable(array('*')
				, 'OThongSoKyThuatTB'
				, array('IFID_M705' => $eqIFID)
				, array(), 1000);
		}
	}

	/**
	 * NHOM4 - TAB TAI LIEU: THIET BI
	 */
	public function EquipdetailDocumentIndexAction() {
		$data = NULL;
		$ifid = $this->params->requests->getParam('eqIFID', 0);
		$dtid = $this->params->requests->getParam('dtid', 0);
		$type = $this->params->requests->getParam('type', 0);

		if ($ifid) {
			$data = $this->_common->getDocuments($ifid, $type, $dtid);
		}
		$this->html->data = $data;
	}

	/**
	 * NHOM4 - TAB TAI LIEU: Hien Thi Tai Lieu
	 */
	public function EquipdetailDocumentShowAction() {
		$data = NULL;
		$ifid = $this->params->requests->getParam('eqIFID', 0);
		$dtid = $this->params->requests->getParam('dtid', 0);
		$type = $this->params->requests->getParam('type', 0);

		if ($ifid) {
			$data = $this->_common->getDocuments($ifid, $type, $dtid);
		}
		$this->html->data = $data;
	}

	/**
	 * NHOM4 - TAB SuCo
	 */
	public function EquipdetailBreakdownIndexAction() {

	}

	public function EquipdetailBreakdownTypesAction() {
		/*/$page = $this->params->requests->getParam('page', 0);
			$display = $this->params->requests->getParam('display', 0);
			$eq = $this->params->requests->getParam('eqID', 0);
			// @todo: Khong nen dung ham getTable nhu vay can tach rieng cau querry nay ra
			$limit = array('display' => $display, 'page' => $page);
			$limit = 'NO_LIMIT';

			$model = new Qss_Model_Maintenance_Breakdown();
			$this->html->history = $model->getBreakDownHistoryByEquipment($eq);
		*/

		$mBreak = new Qss_Model_Maintenance_Breakdown();
		$start = $this->params->requests->getParam('m780_equip_break_index_start', date('d-m-Y'));
		$end = $this->params->requests->getParam('m780_equip_break_index_end', date('d-m-Y'));
		$equipIOID = $this->params->requests->getParam('eqID', 0);

		// Danh sach dung may
		$data = $mBreak->getBreakDown(
			Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
			, 0
			, 0
			, 0
			, 0
			, $equipIOID
			, 0
			, 0
		);

		$breakdowns = $mBreak->getBreakdownByReason(
			Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
			, $equipIOID
			, 0
			, 0
			, 0
		);

		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->history = $data;
		$this->html->breakdowns = $breakdowns;
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: THIET BI
	 */
	public function EquipdetailPlanIndexAction() {
		$eqIOID = $this->params->requests->getParam('eqID', 0);
		$this->html->period = $this->_model->getMaintainPlanByEquipment($eqIOID);
		$this->html->activeViTri = Qss_Lib_System::fieldActive('OCauTrucThietBi', 'ViTri');
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: Con Viec Bao Tri
	 */
	public function EquipdetailPlanWorkAction() {
		$ifid = $this->params->requests->getParam('plan', 0);

		$this->html->data = $this->_common->getTable(array('*')
			, 'OCongViecBT'
			, array('IFID_M724' => $ifid)
			, array(), 1000);
		$this->html->activeViTri = Qss_Lib_System::fieldActive('OCauTrucThietBi', 'ViTri');
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: Dung day chuyen
	 */
	public function EquipdetailPlanStoplineAction() {
		$data = NULL;
		$ifid = $this->params->requests->getParam('plan', 0);

		$this->html->data = new stdClass();
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: Dung day chuyen
	 */
	public function EquipdetailPlanMaterialAction() {
		$ifid = $this->params->requests->getParam('plan', 0);
		$planModel = new Qss_Model_Maintenance_Plan();

		$this->html->data = $this->_common->getTable(array('*')
			, 'OVatTu'
			, array('IFID_M724' => $ifid)
			, array(), 1000);
		$this->html->activeViTri = Qss_Lib_System::fieldActive('OCauTrucThietBi', 'ViTri');
	}
	/**
	 * Lấy chu kỳ
	 */
	public function EquipdetailPlanPeriodAction() {
		$ifid = $this->params->requests->getParam('plan', 0);
		$mChuKy = Qss_Model_Db::Table('OChuKyBaoTri');
		$mChuKy->where(sprintf('IFID_M724 = %1$d', $ifid));
		$mChuKy->orderby('IOID DESC');
		$mChuKy->display(1);
		$lastChuKy = $mChuKy->fetchOne();

		if ($lastChuKy) {
			$mObject = new Qss_Model_Object();
			$mObject->v_fInit('OChuKyBaoTri', 'M724');
			$mObject->initData($ifid, $lastChuKy->DeptID, $lastChuKy->IOID);
		} else {
			$mObject = new Qss_Model_Object();
			$mObject->v_fInit('OChuKyBaoTri', 'M724');
		}

		$this->html->lastChuKy = $lastChuKy ? $lastChuKy : array();
		$this->html->user = $this->_user;
		$this->html->object = $mObject;
		$this->html->canCu = Qss_Lib_System::getFieldRegx('OChuKyBaoTri', 'CanCu');
		$this->html->ifid = $ifid;
		$this->html->data = $this->_common->getTable(array('*')
			, 'OChuKyBaoTri'
			, array('IFID_M724' => $ifid)
			, array(), 1000);
		$this->html->activeViTri = Qss_Lib_System::fieldActive('OCauTrucThietBi', 'ViTri');
	}

	public function EquipdetailPlanPeriod2Action() {
		$params = $this->params->requests->getParams();
		$mChuKy = Qss_Model_Db::Table('OChuKyBaoTri');
		$mChuKy->where(sprintf('IOID = %1$d', $params['OChuKyBaoTri_IOID']));
		$lastChuKy = $mChuKy->fetchOne();

		if ($lastChuKy) {
			$mObject = new Qss_Model_Object();
			$mObject->v_fInit('OChuKyBaoTri', 'M724');
			$mObject->initData($lastChuKy->IFID_M724, $lastChuKy->DeptID, $lastChuKy->IOID);
		} else {
			$mObject = new Qss_Model_Object();
			$mObject->v_fInit('OChuKyBaoTri', 'M724');
		}

		$lastKy = $lastChuKy ? $lastChuKy->KyBaoDuong : '';
		$ky = $params['OChuKyBaoTri_KyBaoDuong'];

		if ($lastKy != '' && $ky == '') {
			$ky = $lastKy;
		}

		$this->html->ky = $ky;
		$this->html->canCu = $params['OChuKyBaoTri_CanCu'];
		$this->html->lastChuKy = $lastChuKy ? $lastChuKy : array();
		$this->html->user = $this->_user;
		$this->html->object = $mObject;
		$this->html->KyBaoDuong = Qss_Lib_System::getFieldRegx('OChuKyBaoTri', 'KyBaoDuong');
	}

	public function EquipdetailPlanPeriod3Action() {
		$common = new Qss_Model_Extra_Extra();
		$params = $this->params->requests->getParams();
		$error = 0;

		if ($this->params->requests->isAjax()) {
			$file = new Qss_Model_Import_Form('M724', false, false);
			$insert = array();
			$iCanCu = $params['OChuKyBaoTri_CanCu'];
			$ifid = $params['OChuKyBaoTri_IFID'];
			$lastID = $common->getTableFetchOne('OChuKyBaoTri', array('IFID_M724' => $ifid), 'MAX(ID) AS LastID');
			$nextID = $lastID ? ((int) $lastID->LastID + 1) : 1;

			if ($iCanCu == 0) {
				if (Qss_Lib_System::fieldActive('OChuKyBaoTri', 'ID')) {
					$insert["OChuKyBaoTri"][0]["ID"] = $params['OChuKyBaoTri_ID'] ? $params['OChuKyBaoTri_ID'] : $nextID;
				}

				$insert["OChuKyBaoTri"][0]["CanCu"] = $iCanCu;
				$insert["OChuKyBaoTri"][0]["KyBaoDuong"] = ($params['OChuKyBaoTri_KyBaoDuong'] != -1) ? $params['OChuKyBaoTri_KyBaoDuong'] : '';
				$insert["OChuKyBaoTri"][0]["LapLai"] = $params['OChuKyBaoTri_LapLai'];
				$insert["OChuKyBaoTri"][0]["Thu"] = @$params['OChuKyBaoTri_Thu'];
				$insert["OChuKyBaoTri"][0]["Ngay"] = @$params['OChuKyBaoTri_Ngay'];
				$insert["OChuKyBaoTri"][0]["Thang"] = @$params['OChuKyBaoTri_Thang'];
				$insert["OChuKyBaoTri"][0]["DieuChinhTheoPBT"] = @(int) $params['OChuKyBaoTri_DieuChinhTheoPBT'] ? (int) $params['OChuKyBaoTri_DieuChinhTheoPBT'] : 0;
				$insert["OChuKyBaoTri"][0]["ifid"] = $params['OChuKyBaoTri_IFID'];
				$insert["OChuKyBaoTri"][0]["ioid"] = $params['OChuKyBaoTri_IOID'];
			} else if ($iCanCu == 1) {
				if (Qss_Lib_System::fieldActive('OChuKyBaoTri', 'ID')) {
					$insert["OChuKyBaoTri"][0]["ID"] = $params['OChuKyBaoTri_ID'] ? $params['OChuKyBaoTri_ID'] : $nextID;
				}

				$insert["OChuKyBaoTri"][0]["CanCu"] = $iCanCu;
				$insert["OChuKyBaoTri"][0]["ChiSo"] = @$params['OChuKyBaoTri_ChiSo'];
				$insert["OChuKyBaoTri"][0]["GiaTri"] = @$params['OChuKyBaoTri_GiaTri'];
				$insert["OChuKyBaoTri"][0]["DieuChinhTheoPBT"] = @(int) $params['OChuKyBaoTri_DieuChinhTheoPBT'] ? (int) $params['OChuKyBaoTri_DieuChinhTheoPBT'] : 0;
				$insert["OChuKyBaoTri"][0]["KyBaoDuong"] = '';
				$insert["OChuKyBaoTri"][0]["LapLai"] = '';
				$insert["OChuKyBaoTri"][0]["Thu"] = '';
				$insert["OChuKyBaoTri"][0]["Ngay"] = '';
				$insert["OChuKyBaoTri"][0]["Thang"] = '';
				$insert["OChuKyBaoTri"][0]["ifid"] = $params['OChuKyBaoTri_IFID'];
				$insert["OChuKyBaoTri"][0]["ioid"] = $params['OChuKyBaoTri_IOID'];
			}

			/*
				            $insert["OChuKyBaoTri"][0]["CanCu"]            = ($params['OChuKyBaoTri_CanCu'] != -1)?$params['OChuKyBaoTri_CanCu']:'';
				            $insert["OChuKyBaoTri"][0]["KyBaoDuong"]       = ($params['OChuKyBaoTri_KyBaoDuong'] != -1)?$params['OChuKyBaoTri_KyBaoDuong']:'';
				            $insert["OChuKyBaoTri"][0]["LapLai"]           = $params['OChuKyBaoTri_LapLai'];
				            $insert["OChuKyBaoTri"][0]["Thu"]              = @$params['OChuKyBaoTri_Thu'];
				            $insert["OChuKyBaoTri"][0]["Ngay"]             = @$params['OChuKyBaoTri_Ngay'];
				            $insert["OChuKyBaoTri"][0]["Thang"]            = @$params['OChuKyBaoTri_Thang'];
				            $insert["OChuKyBaoTri"][0]["ChiSo"]            = @$params['OChuKyBaoTri_ChiSo'];
				            $insert["OChuKyBaoTri"][0]["GiaTri"]           = @$params['OChuKyBaoTri_GiaTri'];
				            $insert["OChuKyBaoTri"][0]["DieuChinhTheoPBT"] = @(int)$params['OChuKyBaoTri_DieuChinhTheoPBT']?(int)$params['OChuKyBaoTri_DieuChinhTheoPBT']:0;
				            $insert["OChuKyBaoTri"][0]["ifid"]             = $params['OChuKyBaoTri_IFID'];
				            $insert["OChuKyBaoTri"][0]["ioid"]             = $params['OChuKyBaoTri_IOID'];
			*/

			// echo '<pre>'; print_r($insert); die;

			if (count($insert)) {
				$file->setData($insert);
				$file->generateSQL();

				$error = $file->countObjectError();

				$mChuKy = new Qss_Bin_Import_M724(null);
				$mChuKy->__doExecute((int) $params['OChuKyBaoTri_IOID']);
			} else {
				$error = 1;
			}
//            $service = $this->services->Maintenance->Equip->Install->Insert($params);
			//            echo $service->getMessage();
		}

		if ((int) $error > 0) {
			// echo '<pre>'; print_r($file->getErrorRows()); die;
			echo Qss_Json::encode(array('error' => 1, 'message' => 'Cập nhật không thành công!'));
		} else {
			echo Qss_Json::encode(array('error' => 0, 'message' => ''));
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: Bao Tri Theo Ngay
	 */
	public function EquipdetailPlanDayAction() {
		$data = NULL;
		$ifid = $this->params->requests->getParam('plan', 0);

		/*
			 $this->html->data = $this->_common->getTable(array('*')
			 , 'OBaoTriTheoNgay'
			 , array('IFID_M724' => $ifid)
			 , array(), 1000);
		*/
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: Thong So Thiet Bi
	 */
	public function EquipdetailPlanTechnoteAction() {
		$data = NULL;
		$ifid = $this->params->requests->getParam('plan', 0);

		$this->html->data = $this->_common->getTable(array('*')
			, 'OKiemTraThongSo'
			, array('IFID_M724' => $ifid)
			, array(), 1000);
	}

	/**
	 * NHOM4 - TAB BAO TRI DINH KY: Cai Dat
	 */
	public function EquipdetailPlanInstallAction() {
		$data = NULL;
		$ifid = $this->params->requests->getParam('plan', 0);

		$this->html->data = $this->_common->getTable(array('*')
			, 'OCaiDatKHBT'
			, array('IFID_M724' => $ifid)
			, array('STT'), 1000);
	}

	/**
	 * NHOM4 - TAB LICH SU BAO TRI: THIET BI
	 */
	public function EquipdetailHistoryIndexAction() {
		$page = $this->params->requests->getParam('page', 1);
		$display = $this->params->requests->getParam('display', 20);
		$eq = $this->params->requests->getParam('eqID', 0);
		// @todo: Khong nen dung ham getTable nhu vay can tach rieng cau querry nay ra
		$page = $page ? $page : 1;
		$display = $display ? $display : 20;
		$limit = array('display' => $display, 'page' => $page);
		$total = $this->_common->getTable(1, 'OPhieuBaoTri', array('Ref_MaThietBi' => $eq), array('Ngay DESC'), 'NO_LIMIT');
		$cpage = ceil($total / $display);

		$this->html->history = $this->_common->getTable(array('*'), 'OPhieuBaoTri', array('Ref_MaThietBi' => $eq), array('Ngay DESC'), $limit);
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->page = $page;
		$this->html->display = $display;
		$this->html->totalPage = $cpage ? $cpage : 1;
		$this->html->next = ($page < $cpage) ? ($page + 1) : $cpage;
		$this->html->back = ($page > 1) ? ($page - 1) : 1;
	}

	/**
	 * NHOM4 - TAB LICH SU BAO TRI: THIET BI
	 */
	public function EquipdetailHistoryShowAction() {

	}

	/**
	 * NHOM4 - Tab class hu hong
	 */
	public function EquipdetailClassIndexAction() {

	}

	public function EquipdetailClassTypesAction() {
		$eq = $this->params->requests->getParam('eqID', 0);
		$start = $this->params->requests->getParam('m780_equip_class_index_start', '');
		$end = $this->params->requests->getParam('m780_equip_class_index_end', '');
		$mStart = Qss_Lib_Date::displaytomysql($start);
		$mEnd = Qss_Lib_Date::displaytomysql($end);
		$mBreakdown = new Qss_Model_Maintenance_Breakdown();
		$existType = array(); // Chi lay loai hu hong mot lan, loai loai hu hong trung nhau
		$failureList = new stdClass(); // Danh muc loai hu hong
		$i = 0;

		$failureListOfEquip = $mBreakdown->getFailureListByEquip($eq, $mStart, $mEnd);
		$failureListOfCom = $mBreakdown->getFailureListOfComponents($eq, 0, $mStart, $mEnd);

		// @note: Uu tien tan suat cua thiet bi truoc, neu co thi ko can tan suat cua bo phan
		foreach ($failureListOfEquip as $item) {
			// Neu chua co loai hu hong thi cho loai hu hhong vao mang
			// de chi lay mot loai hu hong mot lan
			if (!in_array($item->IOID, $existType)) {
				$existType[] = $item->IOID;
				$failureList->{$i} = $item;
				$i++;
			}
		}

		foreach ($failureListOfCom as $item) {
			// Neu chua co loai hu hong thi cho loai hu hhong vao mang
			// de chi lay mot loai hu hong mot lan
			if (!in_array($item->IOID, $existType)) {
				$existType[] = $item->IOID;
				$failureList->{$i} = $item;
				$i++;
			}
		}
		$this->html->start = $start;
		$this->html->end = $end;
		$this->html->failureList = $failureList;
	}

	public function EquipdetailClassReasonsAction() {
		$eq = $this->params->requests->getParam('eq', 0);
		$failureIOID = $this->params->requests->getParam('type', 0);
		$start = $this->params->requests->getParam('m780_equip_class_index_start', '');
		$end = $this->params->requests->getParam('m780_equip_class_index_end', '');
		$mStart = Qss_Lib_Date::displaytomysql($start);
		$mEnd = Qss_Lib_Date::displaytomysql($end);
		$mBreakdown = new Qss_Model_Maintenance_Breakdown();
		$existReason = array(); // Chi lay loai hu hong mot lan, loai loai hu hong trung nhau
		$reasonList = new stdClass(); // Danh muc loai hu hong
		$i = 0;

		$reasonListOfEquip = $mBreakdown->getReasonListByEquip($eq, $failureIOID, $mStart, $mEnd);
		$reasonListOfCom = $mBreakdown->getReasonListOfComponents($eq, $failureIOID, 0, $mStart, $mEnd);

		// @note: Uu tien tan suat cua thiet bi truoc, neu co thi ko can tan suat cua bo phan
		foreach ($reasonListOfEquip as $item) {
			// Neu chua co nguyen nhan thi cho vao mang
			// de chi lay nguyen nhan mot lan duy nhat <uu tien lay cua thiet bi>
			if (!in_array($item->IOID, $existReason)) {
				$existReason[] = $item->IOID;
				$reasonList->{$i} = $item;
				$i++;
			}
		}

		foreach ($reasonListOfCom as $item) {
			// Neu chua co nguyen nhan thi cho vao mang
			// de chi lay nguyen nhan mot lan duy nhat <uu tien lay cua thiet bi>
			if (!in_array($item->IOID, $existReason)) {
				$existReason[] = $item->IOID;
				$reasonList->{$i} = $item;
				$i++;
			}
		}

		$this->html->reasonList = $reasonList;
	}

	public function EquipdetailClassRemediesAction() {
		$eq = $this->params->requests->getParam('eq', 0);
		$reasonIOID = $this->params->requests->getParam('reason', 0);
		$start = $this->params->requests->getParam('m780_equip_class_index_start', '');
		$end = $this->params->requests->getParam('m780_equip_class_index_end', '');
		$mStart = Qss_Lib_Date::displaytomysql($start);
		$mEnd = Qss_Lib_Date::displaytomysql($end);
		$mBreakdown = new Qss_Model_Maintenance_Breakdown();
		$existRemedy = array(); // Chi lay loai hu hong mot lan, loai loai hu hong trung nhau
		$remedyList = new stdClass(); // Danh muc loai hu hong
		$i = 0;

		$remediesListOfEquip = $mBreakdown->getRemedyListByEquip($eq, $reasonIOID, $mStart, $mEnd);
		$remediesListOfCom = $mBreakdown->getRemedyListOfComponents($eq, $reasonIOID, 0, $mStart, $mEnd);

		// @note: Uu tien tan suat cua thiet bi truoc, neu co thi ko can tan suat cua bo phan
		foreach ($remediesListOfEquip as $item) {
			// Neu chua co nguyen nhan thi cho vao mang
			// de chi lay nguyen nhan mot lan duy nhat <uu tien lay cua thiet bi>
			if (!in_array($item->IOID, $existRemedy)) {
				$existRemedy[] = $item->IOID;
				$remedyList->{$i} = $item;
				$i++;
			}
		}

		foreach ($remediesListOfCom as $item) {
			// Neu chua co nguyen nhan thi cho vao mang
			// de chi lay nguyen nhan mot lan duy nhat <uu tien lay cua thiet bi>
			if (!in_array($item->IOID, $existRemedy)) {
				$existRemedy[] = $item->IOID;
				$remedyList->{$i} = $item;
				$i++;
			}
		}

		$this->html->remedyList = $remedyList;
	}

	public function equipdetailInstallIndexAction() {
		$equipIOID = $this->params->requests->getParam('equip', 0);
		$mInstall = new Qss_Model_Maintenance_Equip_Install();
		$mEqList = new Qss_Model_Maintenance_Equip_List();
		$history = $mInstall->getInstallHistory($equipIOID);
		$i = 0;
		$total = count($history);
		$eles = $total - 1;

		// Neu chi bao gom 1 phan tu va khong co cai dat thi lay theo thiet bi
		if ($eles == 0 && !$history[0]->IOID) {
			$history[0]->Ngay = $history[0]->NgayDuaVaoSuDung;

			$history[0]->Ref_DayChuyen = $history[0]->Ref_DayChuyenTB;
			$history[0]->MaDayChuyen = $history[0]->MaDayChuyenTB;
			$history[0]->TenDayChuyen = $history[0]->TenDayChuyenTB;

			$history[0]->Ref_KhuVuc = $history[0]->Ref_KhuVucTB;
			$history[0]->MaKhuVuc = $history[0]->MaKhuVucTB;
			$history[0]->TenKhuVuc = $history[0]->TenKhuVucTB;

			$history[0]->Ref_TrungTamChiPhi = $history[0]->Ref_TrungTamChiPhiTB;
			$history[0]->MaTrungTamChiPhi = $history[0]->MaTrungTamChiPhiTB;
			$history[0]->TenTrungTamChiPhi = $history[0]->TenTrungTamChiPhiTB;

			$history[0]->Ref_NhanVien = $history[0]->Ref_NhanVienTB;
			$history[0]->MaNhanVien = $history[0]->MaNhanVienTB;
			$history[0]->TenNhanVien = $history[0]->TenNhanVienTB;

			$history[0]->ThayDayChuyen = 0;
			$history[0]->ThayKhuVuc = 0;
			$history[0]->ThayTrungTamChiPhi = 0;
			$history[0]->ThayNhanVien = 0;
		} else {
			for ($k = $eles; $k >= 0; $k--) {
				$j = $k + 1;
				// Xac dinh thay the cai gi
				$history[$k]->ThayDayChuyen = ($history[$k]->Ref_DayChuyen) ? 1 : 0;
				$history[$k]->ThayKhuVuc = ($history[$k]->Ref_KhuVuc) ? 1 : 0;
				$history[$k]->ThayTrungTamChiPhi = ($history[$k]->Ref_TrungTamChiPhi) ? 1 : 0;
				$history[$k]->ThayNhanVien = ($history[$k]->Ref_NhanVien) ? 1 : 0;

				// Lay gia tri truoc do neu khong thay doi && Xem truong nao thay doi
				if ($j <= $eles) {
					$history[$k]->ThayDayChuyen = ($history[$k]->Ref_DayChuyen == $history[$j]->Ref_DayChuyen) ? 0 : 1;
					$history[$k]->ThayKhuVuc = ($history[$k]->Ref_KhuVuc == $history[$j]->Ref_KhuVuc) ? 0 : 1;
					$history[$k]->ThayTrungTamChiPhi = ($history[$k]->Ref_TrungTamChiPhi == $history[$j]->Ref_TrungTamChiPhi) ? 0 : 1;
					$history[$k]->ThayNhanVien = ($history[$k]->Ref_NhanVien == $history[$j]->Ref_NhanVien) ? 0 : 1;

//					// Lay gia tri truoc do neu khong thay doi
					//
					//					// Day chuyen
					//					if(!$history[$k]->Ref_DayChuyen)
					//					{
					//						$history[$k]->Ref_DayChuyen  = $history[$j]->Ref_DayChuyen;
					//						$history[$k]->MaDayChuyen    = $history[$j]->MaDayChuyen;
					//						$history[$k]->TenDayChuyen   = $history[$j]->TenDayChuyen;
					//					}
					//
					//					// Khu vuc
					//					if(!$history[$k]->Ref_KhuVuc)
					//					{
					//						$history[$k]->Ref_KhuVuc  = $history[$j]->Ref_KhuVuc;
					//						$history[$k]->MaKhuVuc    = $history[$j]->MaKhuVuc;
					//						$history[$k]->TenKhuVuc   = $history[$j]->TenKhuVuc;
					//					}
					//
					//					// Trung tam chi phi
					//					if(!$history[$k]->Ref_TrungTamChiPhi)
					//					{
					//						$history[$k]->Ref_TrungTamChiPhi  = $history[$j]->Ref_TrungTamChiPhi;
					//						$history[$k]->MaTrungTamChiPhi    = $history[$j]->MaTrungTamChiPhi;
					//						$history[$k]->TenTrungTamChiPhi   = $history[$j]->TenTrungTamChiPhi;
					//					}
					//
					//					// Trung tam chi phi
					//					if(!$history[$k]->Ref_NhanVien)
					//					{
					//						$history[$k]->Ref_NhanVien  = $history[$j]->Ref_NhanVien;
					//						$history[$k]->MaNhanVien    = $history[$j]->MaNhanVien;
					//						$history[$k]->TenNhanVien   = $history[$j]->TenNhanVien;
					//					}
				}
			}
		}

		//echo '<pre>'; print_r($history); die;

		$this->html->equip = $mEqList->getEquipByIOID($equipIOID);
		$this->html->history = $history;
		$this->html->last = ($eles >= 0) ? $history[0] : new stdClass();
	}

	public function equipdetailInstallInsertAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Maintenance->Equip->Install->Insert($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function equipdetailInstallRemoveAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Maintenance->Equip->Install->Remove($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * NHOM 5 - BO PHAN
	 */
	public function ComponentdetailIndexAction() {

	}

	/**
	 * NHOM 5 - BO PHAN - Tab 1
	 */
	public function ComponentdetailGeneralAction() {
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);
		$model = new Qss_Model_Extra_Maintenance();
		$common = new Qss_Model_Extra_Extra();
		$com = $common->getTable(
			array('*')
			, 'OCauTrucThietBi'
			, array('IFID_M705' => $nodeifid, 'IOID' => $nodeioid), array()
			, 1
			, 1
		);

		$this->html->component = $com ? $com : array();
		$this->html->data = $model->getSparepartOfItem($nodeifid, $nodeioid);
		$this->html->nodeioid = $nodeioid;
		$this->html->nodeifid = $nodeifid;
	}

	/**
	 * NHOM 5 - BO PHAN - Ke hoach
	 */
	public function ComponentdetailPlanIndexAction() {
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		$this->html->period = $this->_model->getMaintainPlanByComponent($nodeifid, $nodeioid);
	}

	/**
	 * NHOM 5 - BO PHAN - Ke hoach - vat tu
	 */
	public function ComponentdetailPlanMaterialAction() {
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		$this->html->data = $this->_model->getMaterialOfMaintainPlanByComponent($nodeifid, $nodeioid);
	}

	/**
	 * NHOM 5 - BO PHAN - Ke hoach - cong viec
	 */
	public function ComponentdetailPlanWorkAction() {
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		$this->html->data = $this->_model->getWorkOfMaintainPlanByComponent($nodeifid, $nodeioid);
	}

	/**
	 * NHOM 5 - BO PHAN - Lich su
	 */
	public function ComponentdetailHistoryIndexAction() {

		$data = NULL;
		$page = $this->params->requests->getParam('page', 1);
		$display = $this->params->requests->getParam('display', 20);
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		// @todo: Khong nen dung ham getTable nhu vay can tach rieng cau querry nay ra
		$page = $page ? $page : 1;
		$display = $display ? $display : 20;
		$total = $this->_model->countMaintainOrderByComponent($nodeifid, $nodeioid);
		$cpage = ceil($total / $display);

		$this->html->history = $this->_model->getMaintainOrderByComponent($nodeifid, $nodeioid, $display, $page);
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->page = $page;
		$this->html->display = $display;
		$this->html->totalPage = $cpage ? $cpage : 1;
		$this->html->next = ($page < $cpage) ? ($page + 1) : $cpage;
		$this->html->back = ($page > 1) ? ($page - 1) : 1;
	}

	/**
	 * NHOM 5 - BO PHAN - Lich su - Page
	 */
	public function ComponentdetailHistoryPageAction() {
		$data = NULL;
		$page = $this->params->requests->getParam('page', 0);
		$display = $this->params->requests->getParam('display', 0);
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		$limit = 'NO_LIMIT';
		$total = $this->_model->countMaintainOrderByComponent($nodeifid, $nodeioid);

		$cpage = ceil($total / $display);

		echo Qss_Json::encode(array('error' => 0, 'total' => $cpage, 'page' => $page));
		//echo Qss_Lib_Extra::formatUnSerialize(Qss_Json::encode(array('error' => 0, 'total' => $cpage, 'page' => $page)));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * NHOM 5 - BO PHAN - Lich su - Show
	 */
	public function ComponentdetailHistoryShowAction() {
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);
		$page = $this->params->requests->getParam('page', 0);
		$display = $this->params->requests->getParam('display', 0);

		$this->html->history = $this->_model->getMaintainOrderByComponent($nodeifid, $nodeioid, $display, $page);
		$this->html->deptid = $this->_user->user_dept_id;
	}

	/**
	 * NHOM 5 - BO PHAN - Dac tinh ky thuat
	 */
	public function ComponentdetailTechnoteIndexAction() {
		$common = new Qss_Model_Extra_Extra();
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		$this->html->data = $common->getTable(array('*'), 'OCauTrucThietBi', array('IOID' => $nodeioid), array(), 'NO_LIMIT', 1);
		$this->html->deptid = $this->_user->user_dept_id;
	}

	/**
	 * NHOM 5 -  BoPhan - Class hu hong
	 */
	public function ComponentdetailClassIndexAction() {

	}
	public function ComponentdetailClassTypesAction() {
		$eq = $this->params->requests->getParam('eqID', 0);
		$com = $this->params->requests->getParam('comID', 0);
		$start = $this->params->requests->getParam('m780_equip_class_index_start', '');
		$end = $this->params->requests->getParam('m780_equip_class_index_end', '');
		$mStart = Qss_Lib_Date::displaytomysql($start);
		$mEnd = Qss_Lib_Date::displaytomysql($end);
		$mBreakdown = new Qss_Model_Maintenance_Breakdown();

		$this->html->failureList = $mBreakdown->getFailureListOfComponents($eq, $com, $mStart, $mEnd);
	}

	public function ComponentdetailClassReasonsAction() {
		$eq = $this->params->requests->getParam('eq', 0);
		$com = $this->params->requests->getParam('com', 0);
		$failureIOID = $this->params->requests->getParam('type', 0);
		$start = $this->params->requests->getParam('m780_equip_class_index_start', '');
		$end = $this->params->requests->getParam('m780_equip_class_index_end', '');
		$mStart = Qss_Lib_Date::displaytomysql($start);
		$mEnd = Qss_Lib_Date::displaytomysql($end);
		$mBreakdown = new Qss_Model_Maintenance_Breakdown();

		$this->html->reasonList = $mBreakdown->getReasonListOfComponents($eq, $failureIOID, $com, $mStart, $mEnd);
	}

	public function ComponentdetailClassRemediesAction() {
		$eq = $this->params->requests->getParam('eq', 0);
		$com = $this->params->requests->getParam('com', 0);
		$reasonIOID = $this->params->requests->getParam('reason', 0);
		$start = $this->params->requests->getParam('m780_equip_class_index_start', '');
		$end = $this->params->requests->getParam('m780_equip_class_index_end', '');
		$mStart = Qss_Lib_Date::displaytomysql($start);
		$mEnd = Qss_Lib_Date::displaytomysql($end);
		$mBreakdown = new Qss_Model_Maintenance_Breakdown();

		$this->html->remedyList = $mBreakdown->getRemedyListOfComponents($eq, $reasonIOID, $com, $mStart, $mEnd);
	}

	/**
	 * | -------------------------------------------------------------------------------------------------------------------------------------
	 * | Thuat giai lay so luong phieu bao tri theo khu vuc
	 * | -------------------------------------------------------------------------------------------------------------------------------------
	 * | - Input: Mot object bao gom cac khu vuc, left, right, so luong phieu gan voi moi khu vuc o moi buoc va duoc sap
	 * | xep theo chieu left tang dan. (Nested set model)
	 * | - Action: Lap qua object, ta gan node hien tai vao mot mang. Ta loai bo node khoi object de giam so lan lap do cac
	 * | node duoc sap xep theo left tang dan nen viec loai bo se ko anh huong den nhung node sau. Neu
	 * | right tru di left cua node bang 1 tuc la node khong co thanh phan con va nguoc lai. Ta lap lien tuc lai object dau
	 * | vao da duoc tru di  node hien tai de cong so luong phieu o moi buoc cua cac node con voi dieu kien left cua node
	 * | phai nho hon left cuanode tiep theo va right phai lon hon right cua node do. Khi right cua node tru cho right hien
	 * | tai trong vong lap nho hon ko break ra khoi vong lap vi luc nay no da qua phan tu cuoi cung cua no.
	 * | >>> Tien hanh dong thoi tinh tong so phieu bao tri theo moi step
	 * | - Output: tra ve mang khu vuc da xap xep theo level va da cong don so luong phieu bao tri cho lv cao
	 * | -------------------------------------------------------------------------------------------------------------------------------------
	 *
	 * @param array $filter filter
	 * @return array $retval tra ve mang khu vuc da xap xep theo level va da cong don so luong phieu bao tri cho lv cao
	 */
	private function getNumberOfWorkOrderByLocation($analytics, $over, $locations) {
		$retval = array();
		$j = 1;

		$retval['step'][1] = 0;
		$retval['step'][2] = 0;
		$retval['step'][3] = 0;
		$retval['step'][4] = 0;
		$retval['step'][5] = 0;
		$temp = array();

		foreach ($locations as $loc) {
			$retval['loc'][$loc->LocIOID] = $loc;
			$retval['loc'][$loc->LocIOID]->CountStep1 = 0;
			$retval['loc'][$loc->LocIOID]->CountStep2 = 0;
			$retval['loc'][$loc->LocIOID]->CountStep3 = 0;
			$retval['loc'][$loc->LocIOID]->CountStep4 = 0;
			$retval['loc'][$loc->LocIOID]->CountStep5 = 0;
			$retval['loc'][$loc->LocIOID]->Breakdown = 0;
			$retval['loc'][$loc->LocIOID]->Over = 0;
		}

		foreach ($analytics as $index => $a) {
			$retval['step'][1] += @(int) $a->CountStep1;
			$retval['step'][2] += @(int) $a->CountStep2;
			$retval['step'][3] += @(int) $a->CountStep3;
			$retval['step'][4] += @(int) $a->CountStep4;
			$retval['step'][5] += @(int) $a->CountStep5;

			// Dem theo khu vuc
			if (isset($retval['loc'][$a->LocIOID])) {
				$retval['loc'][$a->LocIOID]->CountStep1 += @(int) $a->CountStep1;
				$retval['loc'][$a->LocIOID]->CountStep2 += @(int) $a->CountStep2;
				$retval['loc'][$a->LocIOID]->CountStep3 += @(int) $a->CountStep3;
				$retval['loc'][$a->LocIOID]->CountStep4 += @(int) $a->CountStep4;
				$retval['loc'][$a->LocIOID]->CountStep5 += @(int) $a->CountStep5;
			}
		}

		foreach ($over as $index => $a) {
			// Dem theo khu vuc
			if (isset($retval['loc'][$a->LocIOID])) {
				$retval['loc'][$a->LocIOID]->Over += @(int) $a->Over;
			}
		}

		return $retval;

	}

	/**
	 * PRIVATE FUNCTION: THONG TIN THIET BI
	 */

	// @thinh-2015-04-08-A1-6-1:  Lay loai thiet bi thuoc khu vuc (co the ket hop lay loai tb)
	/**
	 * Lay loai thiet bi cua tb truc thuoc khu vuc (khong bao gom loai thiet bi cua tb truc thuoc khu vuc con)
	 * Luu y: thiet bi co the khong duoc phan loai thiet bi
	 * @param number $location
	 */
	private function getEquipTypeOfLocation($locationIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		return $equipModel->getEquipTypeByEquipsOfLoc($locationIOID);
	}

	/**
	 * Dem loai thiet bi cua tb truc thuoc khu vuc (khong bao gom loai thiet bi cua tb truc thuoc khu vuc con)
	 * Luu y: thiet bi co the khong duoc phan loai thiet bi
	 * @param number $location
	 */
	private function countEquipTypeOfLocation($locationIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		$dataSql = $equipModel->countEquipTypeByEquipsOfLoc($locationIOID);
		return $dataSql ? $dataSql->Total : 0;
	}

	// @thinh-2015-04-08-A1-6-2: ham thu 2 xu ly lay thiet bi thuoc loai thiet bi (co the co hoac khong co khu vuc)
	/**
	 * Lay thiet bi truc thuoc loai thiet bi
	 * Luu y: thiet bi co the khong duoc phan loai thiet bi
	 * @param number $equipTypeIOID
	 * @param number $locationIOID
	 */
	private function getEquipByEquipTypeAndLocation($equipTypeIOID = 0, $locationIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		return $equipModel->getEquipByEquipTypeAndLocation($equipTypeIOID, $locationIOID);
	}

	/**
	 * Lay thiet bi truc thuoc loai thiet bi khong thuoc du an nao
	 * Luu y: thiet bi co the khong duoc phan loai thiet bi
	 * @param number $equipTypeIOID
	 * @param number $locationIOID
	 */
	private function getEquipByEquipTypeAndLocationNotInProject($equipTypeIOID = 0, $locationIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		return $equipModel->getEquipByEquipTypeAndLocationNotInProject($equipTypeIOID, $locationIOID);
	}

	private function getProjectByEquipTypeAndLocation($equipTypeIOID = 0, $locationIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		return $equipModel->getProjectByEquipTypeAndLocation($equipTypeIOID, $locationIOID);
	}

	/**
	 * Dem thiet bi truc thuoc loai thiet bi (co the bao gom khu vuc)
	 * Luu y: thiet bi co the khong duoc phan loai thiet bi
	 * @param number $equipTypeIOID
	 * @param number $locationIOID
	 */
	private function countEquipByEquipTypeAndLocation($equipTypeIOID = 0, $locationIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		$dataSql = $equipModel->countEquipByEquipTypeAndLocation($equipTypeIOID, $locationIOID);
		return $dataSql ? $dataSql->Total : 0;
	}

	// @thinh-2015-08-04-A1-8-1: ham dem phan tu con cua khu vuc
	/**
	 * Dem so khu vuc con cua khu vuc (Chi dem khu vuc cap ke tiep)
	 * @param number $locationIOID
	 * @return number
	 */
	private function countNextChildOfLoc($locationIOID = 0) {
		$locModel = new Qss_Model_Extra_Location();
		$nextChild = $locModel->countNextChild($locationIOID);
		return $nextChild ? $nextChild->Total : 0;
	}

	/**
	 * Dem so luong con cua khu vuc (dem bao gom khu vuc va loai thiet bi)
	 * @param unknown $locIOID
	 * @return number
	 */
	private function countChildOfLoc($locIOID) {
		$locModel = new Qss_Model_Extra_Location();

		// Lay khu vuc con ngay duoi khu vuc hien tai
		$nextChildOfLoc = $this->countNextChildOfLoc($locIOID);

		// Lay loai thiet bi cua thiet bi truc thuoc khu vuc hien tai(ko bao gom khu vuc con)
		$equipTypeInLoc = $this->countEquipTypeOfLocation($locIOID);

		return (@(int) $nextChildOfLoc+@(int) $equipTypeInLoc);
	}

	private function countNextLevelOfEquip($equipIOID) {
		$mEquip = new Qss_Model_Extra_Equip();

		$nextChildOfEquip = $mEquip->countChildOfEquip($equipIOID);

		return $nextChildOfEquip;
	}

	/**
	 * Kiem tra khu vuc co thanh phan con hay khong (bao gom khu vuc va loai thiet bi)
	 * @param unknown $locIOID
	 * @return boolean
	 */
	private function checkLocHasChild($locIOID) {
		$num = $this->countChildOfLoc($locIOID);
		return ($num > 0) ? true : false;
	}

	/**
	 * Kiem tra xem thiet bi co thiet bi con hay khong
	 * @param unknown $locIOID
	 * @return boolean
	 */
	private function checkEquipHasChild($equipIOID) {
		$num = $this->countNextLevelOfEquip($equipIOID);
		return ($num > 0) ? true : false;
	}

	private function checkEquipsStop($eqIOIDArr) {
		$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$wCalendarLib->initEquipCals($eqIOIDArr, array(), date('Y-m-d'), date('Y-m-d'));
		return $wCalendarLib->checkEquipStopOrRun();
	}

	/**
	 * Dem so thiet bi tren mot node
	 * - Ko dem thiet bi con
	 * @param unknown $locIOID
	 * @param number $equipTypeIOID
	 * @return string
	 */
	public function displayNumOfEquipInNode($nodeType, $locIOID, $equipTypeIOID = 0, $projectIOID = 0) {
		$model = new Qss_Model_Extra_Equip();
		$countEquipDisplay = '(0/0)';

		if ($nodeType == self::NODE_TYPE_LOCATION) {
			$countEquip = $model->countEquipByLocation($locIOID);
			$equipModel = new Qss_Model_Extra_Equip();
			$dataSql = $equipModel->countActiveEquipNotInProjectForLocationNode($locIOID, $equipTypeIOID);
			$activeEquipNotInProject = $dataSql ? $dataSql->Total : 0;

			if ($countEquip) {
				$total = (int) $countEquip->NotActive + (int) $countEquip->Active;
				$countEquipDisplay = '(' . (int) $activeEquipNotInProject . '/' . (int) $countEquip->Active . '/' . $total . ')';
			}
		} elseif ($nodeType == self::NODE_TYPE_EQUIP_TYPE) {
			$countEquip = $model->countEquipByEquipType($equipTypeIOID, $locIOID);
			$equipModel = new Qss_Model_Extra_Equip();
			$dataSql = $equipModel->countActiveEquipNotInProjectForEquipTypeNode($locIOID, $equipTypeIOID);
			$activeEquipNotInProject = $dataSql ? $dataSql->Total : 0;

			if ($countEquip) {
				$total = (int) $countEquip->NotActive + (int) $countEquip->Active;
				$countEquipDisplay = '(' . (int) $activeEquipNotInProject . '/' . (int) $countEquip->Active . '/' . $total . ')';
			}
		} elseif ($nodeType == self::NODE_TYPE_EQUIP_GROUP) {
			$countEquip = $model->countEquipByEquipGroup($equipTypeIOID, $locIOID);
			$equipModel = new Qss_Model_Extra_Equip();
			$dataSql = $equipModel->countActiveEquipNotInProjectForEquipGroupNode($locIOID, $equipTypeIOID);
			$activeEquipNotInProject = $dataSql ? $dataSql->Total : 0;

			if ($countEquip) {
				$total = (int) $countEquip->NotActive + (int) $countEquip->Active;
				$countEquipDisplay = '(' . (int) $activeEquipNotInProject . '/' . (int) $countEquip->Active . '/' . $total . ')';
			}
		} elseif ($nodeType == self::NODE_TYPE_PROJECT) {
			$countEquipDisplay = '(' . $this->countEquipInProject($projectIOID, $locIOID, $equipTypeIOID) . ')';
		}

		return $countEquipDisplay;
	}

	// @thinh-2015-08-04-A1-7: bo bien $groupEq
	// @thinh-2015-08-04-A1-8: tach ham nay thanh hai ham => mot ham lay child cua location
	// + mot ham kiem tra xem location co child
	//$locIOID
	//	, $groupEq = Qss_Model_Extra_Equip::GROUP_EQ_NONE
	//	, $checkHasChild = false
	/**
	 * Lay toan bo thiet bi truc thuoc khong bao gom tb o khu vuc con
	 *  va khu vuc duoi 1 cap cua khu vuc hien tai
	 * @param type $locIOID
	 */
	private function getAllChildOfLocation($locIOID) {
		// MODEL
		$locModel = new Qss_Model_Extra_Location();

		// Lay khu vuc con ngay duoi khu vuc hien tai
		$nextChild = $locModel->getNextChild($locIOID);

		// Lay loai thiet bi cua thiet bi truc thuoc khu vuc hien tai  (ko bao gom khu vuc con)
		$equipTypeInLoc = $this->getEquipTypeOfLocation($locIOID);

		return array('eq' => $equipTypeInLoc, 'loc' => $nextChild);
	}

	/**
	 * Lay khu vuc goc (lv = 1)
	 * @param type $groupEq
	 * @return type
	 */
	// @thinh-2015-04-08-A1-5: bo bien $groupEq (OK)
	private function getRootLoc() {
		$locModel = new Qss_Model_Extra_Location();
		$root = $locModel->getRootLoc();

		foreach ($root as $r) {
			$r->Child = $this->checkLocHasChild($r->IOID);
			$r->NodeType = self::NODE_TYPE_LOCATION;
		}
		return $root;
	}

	private function getEquipTypeOfEquipNotInAnyWhere() {
		$equipModel = new Qss_Model_Extra_Equip();
		return $equipModel->getEquipTypeOfEquipNotInAnyWhere(); // Hien thi loai thiet bi cua tb
	}

	/**
	 * Kiem tra cac khu vuc co dang dung may hay ko?
	 * @param unknown $locationIOIDArr
	 */
	private function checkLocationsStopOrRun($locationIOIDArr) {
		$wCalendarLib = new Qss_Lib_Extra_WCalendar();
		$wCalendarLib->setLocIOIDArr($locationIOIDArr);
		return $wCalendarLib->checkLocationStopOrRun();
	}

	private function countEquipInProject($projectIOID = 0, $locationIOID = 0, $equipTypeIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		$dataSql = $equipModel->countEquipInProject($projectIOID, $locationIOID, $equipTypeIOID);
		return $dataSql ? $dataSql->Total : 0;
	}

	private function countActiveEquipNotInProject($locationIOID = 0, $equipTypeIOID = 0) {
		$equipModel = new Qss_Model_Extra_Equip();
		$dataSql = $equipModel->countActiveEquipNotInProject($locationIOID, $equipTypeIOID);
		return $dataSql ? $dataSql->Total : 0;
	}

	/**
	 * Thong ke so luong phieu bao tri theo tung loai bao tri cua thiet bi
	 * @param type $eqIOID
	 * @return type
	 */
	private function getTotalMaintTypeByEquip($eqIOID) {
		// Dem so luong phieu theo loai bao tri
		$numOfWOByMtype = $this->_model->countMaintTypeOfEquip($eqIOID);
		$numOfWOByMtypeArr = array();

		foreach ($numOfWOByMtype as $co) {
			$numOfWOByMtypeArr[$co->Ref_LoaiBaoTri] = $co->Total;
		}
		return $numOfWOByMtypeArr;
	}

	/**
	 * Lay thoi gian lam viec, thoi gian nghi, thoi gian dung may tu truoc den
	 * nay cua thiet bi
	 * @param type $eqIOID
	 * @return type
	 */
	private function getWorkingTimeOfEquip($eqIOID) {
		$MaintCommon = new Qss_Lib_Maintenance_Common();

		// Thong tin thiet bi
		$eq = $this->_common->getTable(array('*')
			, 'ODanhSachThietBi'
			, array('IOID' => $eqIOID)
			, array()
			, 1, 1);
		$eqInfo = $eq ? $eq : new stdClass();

		// Lay thoi gian lam viec cua thiet bi
		$refWorkCal = @(int) $eqInfo->Ref_LichLamViec;

		$startDateUseEq = @(string) $eqInfo->NgayDuaVaoSuDung ?
		Qss_Lib_Date::displaytomysql(@(string) $eqInfo->NgayDuaVaoSuDung) :
		date('Y-m-d');
		$endDateUseEq = date('Y-m-d');

		$totalWorkCalArr = Qss_Lib_Extra::getTotalWCal($refWorkCal, $startDateUseEq, $endDateUseEq);
		$totalHoursWorkCal = isset($totalWorkCalArr[$refWorkCal]) ? $totalWorkCalArr[$refWorkCal] : 0; // tra ve gio
		$totalHours = Qss_Lib_Date::divDate($startDateUseEq, $endDateUseEq, 'H'); // tra ve gio
		$getDownTime = $MaintCommon->getTotalDowntime($startDateUseEq, $endDateUseEq, 'D', $eqIOID, 'H'); // tra ve phut

		$relax = $totalHours - $totalHoursWorkCal;
		$work = $totalHoursWorkCal - $getDownTime;

		return array('relax' => $relax, 'work' => $work, 'down' => $getDownTime);
	}

	public function getActiveTimeOfEquip($equipIOID) {
		$model = new Qss_Model_Maintenance_Equip_Daily();

		return $model->getActiveTimeOfEquip($equipIOID);
		//$auto   = $model->getActiveTimeOfEquipFromAuto($equipIOID);
		//return ($manual + $auto);
	}

	//làm cho khu vực
	public function locationdetailGeneralAction() {
		$loccode = $this->params->requests->getParam('locationCode', '');
		$nodeioid = $this->params->requests->getParam('locationIOID', 0);
		$nodeifid = $this->params->requests->getParam('locationIFID', 0);
		$data = $this->_common->getTable(
			array('*')
			, 'OKhuVuc'
			, array('IFID_M720' => $nodeifid)
			, array()
			, 1, 1);
		$this->html->data = $data ? $data : array();
		$this->html->nodeioid = $nodeioid;
		$this->html->nodeifid = $nodeifid;
		$this->html->loccode = $loccode;
	}

	/**
	 * NHOM 5 - BO PHAN - Ke hoach
	 */
	public function locationdetailPlanIndexAction() {
		$nodeioid = $this->params->requests->getParam('locationIOID', 0);
		$nodeifid = $this->params->requests->getParam('locationIFID', 0);
		$model = new Qss_Model_Maintenance_Plan();
		$this->html->period = $model->getMaintainPlanByLocation($nodeifid, $nodeioid);
	}

	/**
	 * NHOM 5 - BO PHAN - Ke hoach - vat tu
	 */
	public function locationdetailPlanMaterialAction() {
		$plan = $this->params->requests->getParam('plan', 0);
		$model = new Qss_Model_Extra_Extra();
		$this->html->data = $model->getTableFetchAll('OVatTu', array('IFID_M724' => $plan));
	}

	/**
	 * NHOM 5 - BO PHAN - Ke hoach - cong viec
	 */
	public function locationdetailPlanWorkAction() {
		$plan = $this->params->requests->getParam('plan', 0);
		$model = new Qss_Model_Extra_Extra();
		$this->html->data = $model->getTableFetchAll('OCongViecBT', array('IFID_M724' => $plan));
	}

	/**
	 * NHOM 5 - BO PHAN - Lich su
	 */
	public function locationdetailHistoryIndexAction() {

	}

	/**
	 * NHOM 5 - BO PHAN - Lich su - Page
	 */
	public function locationdetailHistoryPageAction() {
		$data = NULL;
		$page = $this->params->requests->getParam('page', 0);
		$display = $this->params->requests->getParam('display', 0);
		$nodeioid = $this->params->requests->getParam('comIOID', 0);
		$nodeifid = $this->params->requests->getParam('comIFID', 0);

		$limit = 'NO_LIMIT';
		$total = $this->_model->countMaintainOrderByComponent($nodeifid, $nodeioid);

		$cpage = ceil($total / $display);

		echo Qss_Json::encode(array('error' => 0, 'total' => $cpage, 'page' => $page));
		//echo Qss_Lib_Extra::formatUnSerialize(Qss_Json::encode(array('error' => 0, 'total' => $cpage, 'page' => $page)));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function reorderAction() {
		$up = $this->params->requests->getParam('up');
		$type = $this->params->requests->getParam('type');
		$parent_type = $this->params->requests->getParam('parent_type');
		$ifid = $this->params->requests->getParam('ifid');
		$ioid = $this->params->requests->getParam('ioid');
		$parent_ifid = $this->params->requests->getParam('parent_ifid');
		$parent_ioid = $this->params->requests->getParam('parent_ioid');
		$old_parent_ifid = $this->params->requests->getParam('old_parent_ifid');
		$old_parent_ioid = $this->params->requests->getParam('old_parent_ioid');
		$after_ifid = $this->params->requests->getParam('after_ifid');
		$after_ioid = $this->params->requests->getParam('after_ioid');
		$arrUpdate = array();
		switch ($type) {
		case self::NODE_TYPE_LOCATION:
			if ($parent_type == self::NODE_TYPE_LOCATION && $parent_ioid != $old_parent_ioid) {
				$module = 'M720';
				$arrUpdate['OKhuVuc'][0]['TrucThuoc'] = $parent_ioid;
				$service = $this->services->Form->Manual($module, $ifid, $arrUpdate);
			}
			break;
			/*case self::NODE_TYPE_EQUIP:
					if($parent_ioid != $old_parent_ioid)
					{
						$module = 'M705';
						$arrUpdate['ODanhSachThietBi'][0]['TrucThuoc'] = $parent_ioid;
						$service = $this->services->Form->Manual($module,$ifid,$arrUpdate);
					}
					break;
				case self::NODE_TYPE_COMPONENT:
					$module = 'M705';

			*/
		}
		if ((!isset($service) || !$service->isError()) && $after_ioid != $ioid) {
			if ($type == self::NODE_TYPE_COMPONENT) {
				$service = $this->services->Object->Reorder($ifid, 1, 'OCauTrucThietBi', $ioid, $after_ioid, $up);
			} else {
				$service = $this->services->Form->Reorder($ifid, 1, $after_ioid, $up);
			}
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 * NHOM4 - TAB phutung
	 */
	public function equipdetailSparepartIndexAction() {
		$model = new Qss_Model_Maintenance_Equip_Sparepart();
		$mEquip = Qss_Model_Db::Table('ODanhSachThietBi');
		$page = $this->params->requests->getParam('page', 0);
		$search = $this->params->requests->getParam('search');
		$eq = $this->params->requests->getParam('eqIFID', 0);
		$mEquip->where(sprintf('IFID_M705 = %1$d', $eq));

		$this->html->eq = $mEquip->fetchOne();
		$this->html->data = $model->getByEquipment($eq, $search);
		$this->html->search = $search;
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->activeViTri = Qss_Lib_System::fieldActive('OCauTrucThietBi', 'ViTri');
		$this->html->activePhuTung = Qss_Lib_System::fieldActive('OCauTrucThietBi', 'PhuTung');
	}

	public function equipdetailSparepartHistoryAction() {
		$refThietBi = $this->params->requests->getParam('refThietBi', 0);
		$refBoPhan = $this->params->requests->getParam('refBoPhan', 0);
		$refVatTu = $this->params->requests->getParam('refVatTu', 0);
		$filter = $this->params->requests->getParam('filter', 1);
		$mOrder = new Qss_Model_Maintenance_Workorder();

		switch ($filter) {
		// Thiet bi bo phan hien tai
		case 1:
			$this->html->data = $mOrder->getMaterialRecycle(
				''
				, ''
				, 0
				, 0
				, $refThietBi
				, $refVatTu
				, $refBoPhan
			);
			break;

		// Thiet bi hien tai
		case 2:
			$this->html->data = $mOrder->getMaterialRecycle(
				''
				, ''
				, 0
				, 0
				, $refThietBi
				, 0
				, 0
			);
			break;

		// Toan bo thiet bi
		case 3:
			$this->html->data = $mOrder->getMaterialRecycle(
				''
				, ''
				, 0
				, 0
				, 0
				, 0
				, 0
			);
			break;
		}

		$this->html->RefThietBi = $refThietBi;
		$this->html->RefBoPhan = $refBoPhan;
		$this->html->RefVatTu = $refVatTu;
		$this->html->filter = $filter;

	}

}