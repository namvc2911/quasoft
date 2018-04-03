<?php

/**
 *
 * @author Quang
 *
 */
class Chat_ChatController extends Qss_Lib_Controller {

    public function init() {
        parent::init();
    }
    public function ajaxGetConversationForFirstLoadAction() {
    	$a_Params = $this->params->requests->getParams();
        $html = '<ul class="chats" id="ulChats">';
        if (isset($a_Params['myData'])) {
            $conversations = $a_Params['myData'];
            
            $i = 0;
            $class = array();

            foreach ($conversations as &$taskComment) {
                $userModel = new Qss_Model_Admin_User();
                $userModel->init($taskComment['Sender']);
                if ($i > 0) {

                    if ($conversations[$i]['Sender'] == $conversations[$i - 1]['Sender']) {
                        if ($class[$i - 1] == 'out') {
                            $class[$i] = 'out';
                        }
                        if ($class[$i - 1] == 'in') {
                            $class[$i] = 'in';
                        }
                    } else {

                        if ($class[$i - 1] == 'out') {
                            $class[$i] = 'in';
                        }
                        if ($class[$i - 1] == 'in') {
                            $class[$i] = 'out';
                        }
                    }
                }
                $stringClass = '';
                if ($i == 0) {
                    $class[0] = 'out';
                    $stringClass = $class[0];
                } else {

                    $stringClass = $class[$i];
                }
                $tmpTimeSend = date('d-m-Y H:i', strtotime($taskComment['TimeSend']));
                $tmpOwn      = ($taskComment['Sender'] == $this->_user->user_id)?1:0;
                $tmpOwnClass = ($tmpOwn == 1)?'style="color:green; font-weight:bold;"':'style="color:blue; font-weight:bold;"';

                //$html .= '<div style="display: none" data-class-display="'.$stringClass.'" data-chat-user-id="'.$userComment->Id.'" />';
                $html .= '<li data-class-display="' . $stringClass . '" data-chat-user-id="' . $userModel->intUID. '" class="' . $stringClass . '">';
                //$html .= '<img class="avatar" alt="" src="' . QSS_PUBLIC_WEB_URL . '/user/picture/show/?id=' . $taskComment->sender . '"';
                $html .= "<div class='message '>";
                $html .= '<span class="arrow"></span>';
                $html .= "<a href=\"#\" class=\"name \" {$tmpOwnClass}>" . $userModel->szUserName . '</a>&nbsp;';
                $html .= "<span class=\"datetime \" {$tmpOwnClass}> ";
                $html .= "({$tmpTimeSend}): ";
                $html .= '</span>';
                $html .= '<span class="body">';
                $html .= $taskComment['Title'];
                $html .= '</span>';
                $html .= '</div>';
                $html .= '</li>';
                $i++;
            }
        }
        $html .= '</ul>';
		echo $html;
        $this->setHtmlRender(false);
		$this->setLayoutRender(false);
    }


    public function chatUpdateAction() {

        $a_Params = $this->params->requests->getParams();
       	$receiver = $paramArr['receiver'];
		$this->_user->readChatLogOfUser($receiver);
		$array = array('message' => 'Lưu thành công', 'error' => false);
        Qss_Lib_Util::printJson($array);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

}

?>