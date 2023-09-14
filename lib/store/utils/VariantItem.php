<?php
include_once("storage/StorageItem.php");
include_once("store/utils/PriceInfo.php");

class VariantItem implements JsonSerializable
{
    protected $option_name;
    protected $option_values = array();
    protected $gallery_items = array();
    protected $prices = array();

    protected $selectedIndex = -1;
    protected $instock = true;

    public function __construct(string $name)
    {
        $this->option_name = $name;
    }

    public function getName() : string
    {
        return $this->option_name;
    }

    public function addParameter(string $value)
    {
        $this->option_values[] = $value;
    }

    public function getParameters() : array
    {
        return $this->option_values;
    }

    public function setSelected(string $value)
    {
        $found = array_search($value, $this->option_values);
        if ($found === FALSE) throw new Exception("Variant parameter not found");
        $this->selectedIndex = (int)$found;

    }

    public function getSelected() : string
    {
        if ($this->selectedIndex<0) throw new Exception("Variant parameter not found");
        return $this->option_values[$this->selectedIndex];
    }

    public function getSelectedIndex() : int
    {
        return $this->selectedIndex;
    }

    public function haveParameter(string $value) : bool
    {
        return in_array($value, $this->option_values);
    }

    public function addGalleryItem(string $value, StorageItem $si)
    {
        $this->gallery_items[$value][] = $si;
    }

    public function setVariantPrice(string $value, PriceInfo $pinfo)
    {
        $this->prices[$value] = $pinfo;
    }

    public function getVariantPrice(string $value)
    {
        return $this->prices[$value];
    }

    public function setInstock(bool $mode)
    {
        return $this->instock = $mode;
    }

    public function isInstock() : bool
    {
        return $this->instock;
    }

    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }
}
?>