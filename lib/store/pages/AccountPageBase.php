<?php
include_once("class/pages/StorePage.php");
include_once("beans/ConfigBean.php");

include_once("auth/UserAuthenticator.php");
include_once("beans/UsersBean.php");

include_once("utils/menu/MenuItemList.php");
include_once("utils/menu/MenuItem.php");

class AccountPageBase extends StorePage
{
    protected $account_menu = NULL;

    public function __construct(bool $authorized_access = TRUE)
    {
        $this->authorized_access = $authorized_access;

        parent::__construct();

        $this->account_menu = new MenuItemList();

        $this->account_menu->append(new MenuItem("История на поръчките", "orders.php"));
        $this->account_menu->append(new MenuItem("Регистриран адрес", "registered_address.php"));
        $this->account_menu->append(new MenuItem("Детайли за фактуриране", "invoice_details.php"));
        $this->account_menu->append(new MenuItem("Редакция на профил", "profile.php"));
        $this->account_menu->append(new MenuItem("Изход", "logout.php"));

        $this->head()->addCSS(STORE_LOCAL . "/css/account.css");
    }

    public function startRender()
    {

        parent::startRender();

        echo "<div class='columns'>";



        //render menu items
        if ($this->context) {
            echo "<div class='column account_menu'>";

            echo "<div class='menu_links'>";

            $iterator = $this->account_menu->iterator();
            while ($item = $iterator->next()) {
                if (! ($item instanceof MenuItem)) continue;
                echo "<a class='item' href='" . $item->getHref() . "'>";
                echo $item->getName();
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
