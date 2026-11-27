<?php
include_once("input/processors/InputProcessor.php");

/**
 * The target of a promotion (store_promos: target, targetID) from one of two inputs of the promotion form - the
 * category input sets target 'Category', the section input 'Section'. Loads its value only when the row's target is
 * its own, writes target and targetID only when a value is chosen (the form checks that exactly one is).
 */
class PromoTargetProcessor extends InputProcessor
{
    protected string $target;

    public function __construct(DataInput $input, string $target)
    {
        parent::__construct($input);
        $this->target = $target;
    }

    public function loadBeanData(int $editID, DBTableBean $bean, array $data) : void
    {
        if (($data["target"] ?? "") !== $this->target) return;
        $this->input->setValue((int)($data["targetID"] ?? 0));
    }

    public function transactValue(IValueTransactor $transactor) : void
    {
        if ($this->skip_transaction) return;
        $value = (int)$this->input->getValue();
        if ($value < 1) return;
        $transactor->appendValue("target", $this->target);
        $transactor->appendValue("targetID", $value);
    }
}
