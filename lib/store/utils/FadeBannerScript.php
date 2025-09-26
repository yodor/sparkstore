<?php
include_once("components/PageScript.php");

class FadeBannerScript extends PageScript
{
    protected string $cssSelector = ".section .banners";

    public function __construct()
    {
        parent::__construct();

    }
    public function setFadeSelector(string $cssSelector)
    {
        $this->cssSelector = $cssSelector;
    }
    public function code() : string
    {
        return <<<JS
        function getTimeout()
        {
            return (((Math.random()+1) * 30)) * 100;
        }
        /**
        * 
        * @param section {HTMLElement}
        */
        function fadeBanner(section)
        {
        
            let first = section.childNodes.item(0);
            if (first instanceof HTMLElement) {
                section.appendChild(section.removeChild(first));
                setTimeout(()=>fadeBanner(section),getTimeout());
            }

        }
        onPageLoad(function() {
            document.querySelectorAll("$this->cssSelector").forEach((section)=>{
                setTimeout(()=>fadeBanner(section), getTimeout());
            });
        });
JS;

    }
}
?>