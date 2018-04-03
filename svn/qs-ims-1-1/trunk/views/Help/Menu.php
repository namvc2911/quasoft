<?php
class Qss_View_Help_Menu extends Qss_View_Abstract
{
    public function __doExecute ($o_User)
    {
        $menuID = $o_User->user_menu_id;
        $mMenu  = new Qss_Model_Menu();
        $menu   = $mMenu->getMenuTree($menuID);

        $this->html->menu = $menu;
        //echo '<pre>'; print_r($menu); die;
    }
}
?>