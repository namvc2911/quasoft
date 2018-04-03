<?php

/**
 * Class Qss_Bin_Bash_CheckFinishAllWorks
 * Tick thực hiện toàn bộ công việc trong phiếu bảo trì đang chọn
 */
class Qss_Bin_Bash_CheckFinishAllWorks extends Qss_Lib_Bin {
	public function __doExecute() {
		$insert   = array(); // Mảng công việc cần cập nhật
		$insertID = 0;       // Thứ tự mảng insert

        // Chỉ thực hiện việc tick khi có trường công việc trong công việc của phiếu bảo trì M759
		if(Qss_Lib_System::fieldActive('OCongViecBTPBT', 'ThucHien')) {
			foreach($this->_params->OCongViecBTPBT as $item) { // Lấy mảng cập nhật từ các công việc hiện tại
				$insert['OCongViecBTPBT'][$insertID]['ThucHien'] = 1;
				$insert['OCongViecBTPBT'][$insertID]['ioid']     = $item->IOID;
				$insertID++;
			}

			// Nếu có công việc cần cập nhật thì tiến hành cập nhật vào csdl
			if(count($insert)) {
				$services =  $this->services->Form->Manual('M759', $this->_params->IFID_M759, $insert, false);

				// Nếu quá trình insert xảy ra lỗi thì báo lỗi
                // Ngược lại refresh lại trang để hiển thị phần thực hiện trên grid
				if($services->isError()) {
					$this->setError();
					$this->setMessage($services->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}	
				else {
					$services->setRedirect('/user/form/edit?ifid='.$services->getData().'&deptid=1');
				}
			}
		}
	}
}