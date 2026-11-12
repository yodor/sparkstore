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
        parent::__construct();

        $this->bean = new ProductsBean();

        $this->need_confirm = TRUE;
        $this->confirm_dialog_title = "Потвърдете копиране";
        $this->confirm_dialog_text = "Потвърдете копиране на този продукт включително атрибути и снимки?";
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

    public function getParameterNames(): array
    {
        $result = parent::getParameterNames();
        $result[] = "item_id";
        return $result;
    }

    public function createAction(string $title = "Copy Product") : ?Action
    {
        $action = parent::createAction($title);
        $action->getURL()->add(new DataParameter("item_id", $this->bean->key()));
        return $action;
    }

    protected function processImpl() : void
    {

        $db = DBManager::Driver();

        try {
            $db->transaction();

            $cbrow = $this->bean->getByID($this->item_id);

            //copy the product
            unset($cbrow[$this->bean->key()]);
            $lastID = $this->bean->insert($cbrow, $db);
            if ($lastID < 1) throw new Exception("Unable to copy the product: " . $db->getError());

            $this->copyRows(new ProductFeaturesBean(), $lastID, $db, "Unable to copy features");
            $this->copyRows(new ProductPhotosBean(), $lastID, $db, "Unable to copy photo");
            $this->copyRows(new ClassAttributeValuesBean(), $lastID, $db, "Unable to copy class attributes");

            $db->commit();
        }
        catch (Exception $e) {

            $db->rollback();
            throw $e;
        }

        Session::SetAlert(tr("Продуктът е копиран успешно.") . tr("Кликнете") . " <a href='add.php?editID=$lastID&catID={$cbrow["catID"]}'>" . tr("тук") . "</a> " . tr("за редактиране"));

        header("Location: {$this->cancel_url}");
        exit;
    }

    /**
     * Copy all rows of '$bean' referencing the source product to product '$prodID'.
     * All columns are selected (queryField alone selects only the key and prodID) and the rows are
     * fetched completely before inserting: the query result is unbuffered and the inserts need the
     * same (transaction) connection.
     * @param DBTableBean $bean
     * @param int $prodID
     * @param DBDriver $db
     * @param string $error
     * @return void
     * @throws Exception
     */
    protected function copyRows(DBTableBean $bean, int $prodID, DBDriver $db, string $error) : void
    {
        $qry = $bean->queryField("prodID", (string)$this->item_id, 0, ...$bean->columnNames());
        $qry->exec();

        $rows = array();
        while ($row = $qry->next()) {
            unset($row[$bean->key()]);
            $row["prodID"] = $prodID;
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            if ($bean->insert($row, $db) < 1) throw new Exception($error . ": " . $db->getError());
        }
    }

}