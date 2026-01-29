<?php
include_once("components/LabelSpan.php");

class CurrencyLabel extends LabelSpan {

    protected string $symbol = "";
    protected ?float $value = null;

    /**
     * Component to show amounts using currency symbol
     */
    public function __construct()
    {
        parent::__construct();
        $this->setComponentClass("price");
        $this->label()->setComponentClass("currency");
    }

    /**
     * Set the value to show as amount if null clears the contents of the label and the value
     * @param float|null $amount Amount to show
     * @return void
     */
    public function setAmount(?float $amount) : void
    {
        $this->value = $amount;

        if ($amount==null) {
            $this->label()->setContents("");
            $this->span()->setContents("");
            return;
        }
        $this->span()->setContents(sprintf("%0.2f", $amount));
        $this->label()->setContents($this->symbol);
    }

    public function getAmount() : ?float
    {
        return $this->value;
    }

    public function setSymbol(string $currency) : void
    {
        $this->symbol = $currency;
    }

    public function getSymbol() : string
    {
        return $this->symbol;
    }
}

?>