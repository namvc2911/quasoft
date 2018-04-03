<?php
class Button_M866Controller extends Qss_Lib_Controller
{
    public function init() {
        parent::init();
    }

    public function mailIndexAction(){
        $mUser   = new Qss_Model_M005_User();
        $ifid    = $this->params->requests->getParam('ifid', 0);

        $this->html->employee = $mUser->getInfoOfActiveUser();
        $this->html->customer = array();
        $this->html->ifid     = $ifid;
    }

    public function mailCustomerAction(){
        $mCommon = new Qss_Model_Extra_Extra();
        $ChuaPhanLoai  = $this->params->requests->getParam('ChuaPhanLoai', 0);
        $KhachHang     = $this->params->requests->getParam('KhachHang', 0);
        $NhaCungCap    = $this->params->requests->getParam('NhaCungCap', 0);
        $Lead          = $this->params->requests->getParam('Lead', 0);
        $where         = array();
        if($Lead) {
            $where[]       = sprintf(' IFNULL(Lead, 0) = 1');
        }

        if($KhachHang) {
            $where[]       = sprintf(' IFNULL(KhacHang, 0) = 1');
        }

        if($NhaCungCap) {
            $where[]       = sprintf(' IFNULL(NhaCungCap, 0) = 1');
        }

        if($ChuaPhanLoai) {
            $where[]       = sprintf(' (IFNULL(Lead, 0) = 0 AND IFNULL(NhaCungCap, 0) = 0 AND IFNULL(KhacHang, 0) = 0)');
        }

        $where = count($where)?implode(' or ', $where):'';

        $this->html->customer = $mCommon->getTableFetchAll('ODoiTac', $where);
    }

    public function mailSendAction() {
        if ($this->params->requests->isAjax())
        {
            $mPartner  = new Qss_Model_M118_Partner();
            $mUser     = new Qss_Model_M005_User();
            $ifid      = $this->params->requests->getParam('ifid', 0);
            $customer  = $this->params->requests->getParam('Customer', array());
            $employee  = $this->params->requests->getParam('Employee', array());
            $reCus     = $this->params->requests->getParam('reSendForCustomer', 0);
            $reEmp     = $this->params->requests->getParam('reSendForEmployee', 0);
            $extEmail  = $this->params->requests->getParam('extendEmail', '');
            $arrExtE   = $extEmail?explode(',', $extEmail):array();
            $mCommon   = new Qss_Model_Extra_Extra();
            $main      = $mCommon->getTableFetchOne('OQuanLyThayDoi', array('IFID_M866'=>$ifid));
            $logs      = $mCommon->getTableFetchAll('OLogMailQuanLyThayDoi', array('IFID_M866'=>$ifid));
            $sent      = array();
            $mail      = new Qss_Model_Mail();
            $sent['NhanVien']  = array();
            $sent['KhachHang'] = array();
            $sent['GuiThem']   = array();
            $bccAdress = array();
            $bcc       = '';
            $insert    = array(); // ghi lai log
            $i         = 0;
            $import    = new Qss_Model_Import_Form('M866',false, false);
            $fEmployees = array(); // init
            $fCustomers = array(); // init

            // Lấy danh sách nhân viên đã gửi mail
            foreach($logs as $item) {
                if($item->NhanVien) {
                    $sent['NhanVien'][] = $item->Ref_NhanVien;
                }

                if($item->KhachHang) {
                    $sent['KhachHang'][] = $item->Ref_KhachHang;
                }

                if($item->NhanVien) {
                    $sent['NhanVien'][] = $item->Ref_NhanVien;
                }

                if(!$item->NhanVien && !$item->KhachHang) {
                    $sent['GuiThem'][] = $item->Email;
                }
            }

            // Không gửi lại khách hàng đã gửi trước đó nếu không tick vào gửi lại
            if($reCus == 0) {
                foreach ($customer as $key=>$cus) {
                    if(in_array($cus, $sent['KhachHang'])) {
                        unset($customer[$key]);
                    }
                }
            }

            // Không gửi lại nhân viên đã gửi trước đó nếu không tick vào gửi lại
            if($reEmp == 0) {
                foreach ($employee as $key=>$emp) {
                    if(in_array($emp, $sent['NhanVien'])) {
                        unset($employee[$key]);
                    }
                }
            }

            // Lấy danh sách mail của nhân viên
            if(count($employee)) {
                $fEmployees = $mUser->getUserByEmployees($employee);

                foreach($fEmployees as $key=>$emp) {
                    if($emp->EMail) {
                        $bccAdress[$emp->EMail] = $emp->TitleName;
                    }
                    else {
                        unset($fEmployees[$key]);
                    }
                }
            }

            // Lấy danh sách mail của khách hàng
            if(count($customer)) {
                $fCustomers = $mPartner->getContactsByPartnerIOIDs($customer);

                foreach($fCustomers as $key=>$cus) {
                    if($cus->Email) {
                        $bccAdress[$cus->Email] = $cus->HoTen;
                    }
                    else {
                        unset($fCustomers[$key]);
                    }
                }
            }

            // Lấy danh sách mail gửi thêm
            if(count($arrExtE)) {
                foreach ($arrExtE as $email) {
                    if(!in_array(trim($email), $sent['GuiThem'])) {
                        $bccAdress[trim($email)] = trim($email);
                    }
                }
            }

            // Viết nội dung mail, gửi mail đồng thời lưu lại log vào module
            if($main && count($bccAdress)) {
                // Viết nội dung mail
                $subject = $main->TieuDe;
                $body    = Qss_Lib_Util::textToHtml($main->NoiDung);

                foreach($bccAdress as $email=>$name) {
                    if($bcc) {
                        $bcc .= ',';
                    }
                    $bcc .= $email;
                }

                // Gửi mail
                $mail->logMail($subject, $body, '', '', $bcc, '');


                // Ghi lại log
                $insert['OQuanLyThayDoi'][0]['So']   = $main->So;

                // Gửi cho nhân viên nào
                foreach ($fEmployees as $emp) {
                    $insert['OLogMailQuanLyThayDoi'][$i]['Ngay']      = date('Y-m-d');
                    $insert['OLogMailQuanLyThayDoi'][$i]['Gio']       = date('H:i:s');
                    //$insert['OLogMailQuanLyThayDoi'][$i]['TieuDe']    = $subject;
                    //$insert['OLogMailQuanLyThayDoi'][$i]['NoiDung']   = Qss_Lib_Util::textToHtml($body);
                    $insert['OLogMailQuanLyThayDoi'][$i]['Email']     = $emp->EMail;
                    $insert['OLogMailQuanLyThayDoi'][$i]['NhanVien']  = (int)$emp->IOID;
                    $i++;
                }

                // Gửi cho khách hàng nào
                foreach ($fCustomers as $emp) {
                    $insert['OLogMailQuanLyThayDoi'][$i]['Ngay']      = date('Y-m-d');
                    $insert['OLogMailQuanLyThayDoi'][$i]['Gio']       = date('H:i:s');
                    //$insert['OLogMailQuanLyThayDoi'][$i]['TieuDe']    = $subject;
                    //$insert['OLogMailQuanLyThayDoi'][$i]['NoiDung']   = Qss_Lib_Util::textToHtml($body);
                    $insert['OLogMailQuanLyThayDoi'][$i]['Email']     = $emp->Email;
                    $insert['OLogMailQuanLyThayDoi'][$i]['KhachHang'] = (int)$emp->DoiTacIOID;
                    $i++;
                }

                // Gửi thêm cho ai
                foreach ($arrExtE as $email) {
                    $insert['OLogMailQuanLyThayDoi'][$i]['Ngay']      = date('Y-m-d');
                    $insert['OLogMailQuanLyThayDoi'][$i]['Gio']       = date('H:i:s');
                    //$insert['OLogMailQuanLyThayDoi'][$i]['TieuDe']    = $subject;
                    //$insert['OLogMailQuanLyThayDoi'][$i]['NoiDung']   = Qss_Lib_Util::textToHtml($body);
                    $insert['OLogMailQuanLyThayDoi'][$i]['Email']     = trim($email);
                    $i++;
                }


                $import->setData($insert);
                $import->generateSQL();
            }

            echo Qss_Json::encode(array('error' => false, 'message' => '', 'redirect' => null, 'status' => $ifid));
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);


    }
}