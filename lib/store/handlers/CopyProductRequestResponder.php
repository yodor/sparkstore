<?php
include_once("responders/RequestResponder.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ClassAttributeValuesBean.php");

class CopyProductRequestResponder extends RequestResponder
{

    protected int $item_id = -1;
    protected DBTableBean $bean;

    public function __construct()
    {
        parent::__construct("copy_product");

        $this->bean = new ProductsBean();

        $this->need_confirm = TRUE;
    }

    public function getItemID() : int
    {
        return $this->item_id;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function parseParams() : void
    {
        if (!$this->url->contains("item_id"))  throw new Exception("Item ID not passed");
        $this->item_id = (int)$this->url->get("item_id")->value();
    }

    /**
     * @return void
     */
    public function getParameterNames(): array
    {
        return parent::getParameterNames() + array("item_id");
    }

    public function createAction(string $title = "Copy", string $href = "", Closure $check_code = NULL, array $parameters = array()) : ?Action
    {
        $parameters[] = new DataParameter("item_id", $this->bean->key());
        return new Action($title, "?cmd={$this->cmd}&$href", $parameters, $check_code);
    }

    protected function processConfirmation() : void
    {
        $this->setupConfirmDialog("Потвърдете копиране", "Потвърдете копиране на този продукт включително атрибути и снимки?");
    }

    protected function processImpl() : void
    {

        $db = DBConnections::Open();

        try {
            $cbrow = $this->bean->getByID($this->item_id);

            //copy the product
            unset($cbrow["prodID"]);
            $lastID = $this->bean->insert($cbrow, $db);
            if ($lastID < 1) throw new Exception("Unable to copy the product: " . $db->getError());

            //copy attributes
            $pa = new ProductFeaturesBean();
            $qry = $pa->queryField("prodID", $this->item_id);
            $qry->exec();
            while ($parow = $qry->next()) {
                unset($parow[$pa->key()]);
                $parow["prodID"] = $lastID;
                if (!$pa->insert($parow, $db)) throw new Exception("Unable to copy features: " . $db->getError());
            }
            //copy photos
            $pp = new ProductPhotosBean();
            $qry = $pp->queryField("prodID", $this->item_id);
            $qry->exec();
            while ($pprow = $qry->next()) {
                unset($pprow[$pp->key()]);
                $pprow["prodID"] = $lastID;
                $pprow["photo"] = $db->escape($pprow["photo"]);
                // var_dump($pprow);
                $lastppID = $pp->insert($pprow, $db);
                if ($lastppID < 1) throw new Exception("Unable to copy photo: " . $db->getError());
            }

            $ca = new ClassAttributeValuesBean();
            $qry = $ca->queryField("prodID", $this->item_id);
            $qry->exec();
            while ($carow = $qry->next()) {
                unset($carow[$ca->key()]);
                $carow["prodID"] = $lastID;

                $lastcaID = $ca->insert($carow, $db);
                if ($lastcaID < 1) throw new Exception("Unable to copy class attibutes: " . $db->getError());
            }

            $db->commit();
            $success = TRUE;
            Session::SetAlert(tr("Продуктът е копиран успешно.") . tr("Кликнете") . " <a href='add.php?editID=$lastID&catID={$cbrow["catID"]}'>" . tr("тук") . "</a> " . tr("за редактиране"));

            header("Location: {$this->cancel_url}");
            exit;
        }
        catch (Exception $e) {

            $db->rollback();
            throw $e;
        }

    }

}

?>
