<?php
include_once("beans/ConfigBean.php");

include_once("auth/UserAuthenticator.php");
include_once("beans/UsersBean.php");

include_once("utils/MainMenu.php");
include_once("utils/MenuItem.php");

class AccountPage extends StorePage
{
    protected $account_menu = NULL;
    protected $authorized_access = TRUE;

    public function __construct($authorized_access = TRUE)
    {
        $this->authorized_access = $authorized_access;

        parent::__construct();

        $this->account_menu = new MainMenu();

        $items = array();

        $items[] = new MenuItem("История на поръчките", "orders.php");
        $items[] = new MenuItem("Регистриран адрес", "registered_address.php");
        $items[] = new MenuItem("Детайли за фактуриране", "invoice_details.php");
        $items[] = new MenuItem("Редакция на профил", "profile.php");
        $items[] = new MenuItem("Изход", "logout.php");

        $this->account_menu->setMenuItems($items);

        $this->addCSS(STORE_LOCAL . "/css/account.css");
    }

    public function startRender()
    {

        parent::startRender();

        echo "<div class='columns'>";



        //render menu items
        if ($this->context) {
            echo "<div class='column account_menu'>";

            echo "<div class='menu_links'>";
            $menu_items = $this->account_menu->getMenuItems();
            foreach ($menu_items as $idx => $item) {
                echo "<a class='item' href='" . $item->getHref() . "'>";
                echo $item->getTitle();
                echo "</a>";
            }
            echo "</div>";

            echo "</div>"; //column account
        }




    }

    public function finishRender()
    {

        echo "</div>";//columns
        parent::finishRender();
    }

}

?>
