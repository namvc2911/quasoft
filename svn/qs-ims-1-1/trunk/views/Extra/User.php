<?php
class Qss_View_Extra_User extends Qss_View_Abstract
{
    public function __doExecute ($form,$selected)
    {
        $user        = Qss_Register::get('userinfo');
        $modelUser   = new Qss_Model_Admin_User();
        $userIOID    = $selected;

        $this->html->htmlUser     = $user;
        $this->html->htmlUsers    = $modelUser->a_fGetAllNormal($user->user_id);
    }
}
?>