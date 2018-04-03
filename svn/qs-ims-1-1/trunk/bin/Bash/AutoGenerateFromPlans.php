<?php

/**
 * Class Qss_Bin_Bash_AutoGenerateFromPlans
 * Tự động tạo kế hoạch bảo trì M724 ra phiếu bảo trì trong 7 ngày kế tiếp kể từ ngày hiện tại
 */
class Qss_Bin_Bash_AutoGenerateFromPlans extends Qss_Lib_Bin {
    public function __doExecute() {
        $start   = date('Y-m-d'); // Ngày bắt đầu = ngày hiện tại
        $end     = date('Y-m-d', strtotime('+7 days', strtotime($start))); // Ngày KT = Hiện tại + 7 ngày

        // Tiến hành tạo phiếu bảo trì theo ngày từ ngày hiện tại tới 7 ngày sau đó theo kế hoạch bảo trì M724
        $service = $this->services->Maintenance->WorkOrder->CreateOrdersFromPlan($start, $end);

        // Nếu quá trình cập nhật xảy ra lỗi thì báo lỗi, không thì tiến hành refresh lại module M759 để hiện thị PBT
        if($service->isError()) {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
        else {
            Qss_Service_Abstract::$_redirect = '/user/form?fid=M759';
        }
    }
}