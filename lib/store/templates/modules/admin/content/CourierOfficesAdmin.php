<?php
include_once("templates/TemplateContent.php");
include_once("store/courier/CourierOffices.php");
include_once("store/utils/cart/Delivery.php");
include_once("components/Container.php");
include_once("components/Component.php");
include_once("components/TextComponent.php");

/**
 * The office lists of the couriers (Orders > Delivery > Courier Offices): per courier with an office list
 * (CourierOffices::Source) the count, the last refresh and 'Refresh' - a POST that reads the list from the courier
 * (CourierOffices::Sync; a failed answer keeps the old list). The checkout office step chooses from these lists.
 */
class CourierOfficesAdmin extends TemplateContent
{
    const string KEY_SYNC = "sync_courier";

    /**
     * The couriers with an office list
     * @return array<int, DeliveryCourier>
     */
    protected function couriers() : array
    {
        //every courier of the store, enabled or not (the delivery config switches them)
        $delivery = new Delivery();
        $result = array();
        foreach ($delivery->getCouriers() as $id => $courier) {
            if ($courier instanceof DeliveryCourier && !is_null(CourierOffices::Source((int)$id))) $result[(int)$id] = $courier;
        }
        return $result;
    }

    public function processInput(): void
    {
        if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST" || !isset($_POST[CourierOfficesAdmin::KEY_SYNC])) return;

        $couriers = $this->couriers();
        $id = (int)$_POST[CourierOfficesAdmin::KEY_SYNC];
        if (isset($couriers[$id])) {
            try {
                $count = CourierOffices::Sync($id);
                Session::SetAlert($couriers[$id]->getTitle() . ": " . tr("заредени офиси") . " " . $count);
            }
            catch (Exception $e) {
                Debug::ErrorLog("Courier offices sync $id: " . $e->getMessage());
                Session::SetAlert($couriers[$id]->getTitle() . ": " . tr("списъкът не беше обновен") . " - " . $e->getMessage());
            }
        }
        header("Location: " . Module::PathURL("", URL::Current()));
        exit;
    }

    public function initialize(): void
    {
        $this->cmp = new Container(false);
        $this->cmp->setComponentClass("CourierOffices");

        foreach ($this->couriers() as $id => $courier) {
            $status = CourierOffices::Status($id);

            $form = new Container(false);
            $form->setTagName("form");
            $form->setComponentClass("courier");
            $form->setAttribute("method", "post");

            $title = new Component(false);
            $title->setTagName("h3");
            $title->setText($courier->getTitle());
            $form->items()->append($title);

            $text = ($status["count"] > 0)
                ? tr("Офиси") . ": " . $status["count"] . ", " . tr("обновени") . ": " . $status["updated"]
                : tr("Няма зареден списък");
            $info = new TextComponent(htmlspecialchars($text));
            $info->setTagName("p");
            $form->items()->append($info);

            $button = new Component(false);
            $button->setTagName("button");
            $button->setComponentClass("ColorButton");
            $button->setAttribute("type", "submit");
            $button->setAttribute("name", CourierOfficesAdmin::KEY_SYNC);
            $button->setAttribute("value", (string)$id);
            $button->setText(tr("Обнови офисите"));
            $form->items()->append($button);

            $this->cmp->items()->append($form);
        }
    }

    protected function getContentTitle(): string
    {
        return tr("Courier Offices");
    }
}
