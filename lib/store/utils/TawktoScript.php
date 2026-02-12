<?php
include_once("components/Script.php");

class TawktoScript extends Script
{
    protected string $pageID = "";

    public function __construct(string $pageID)
    {
        parent::__construct();
        $this->setPageID($pageID);
    }

    public function setPageID(string $pageID) : void
    {
        if (strlen(trim($pageID)) == 0) throw new Exception("PageID can not be empty");

        $this->pageID = $pageID;

        $contents = <<<JS
        //Start of Tawk.to Script PageID:{$this->pageID}
        let chatPlugin = function()
        {
            var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
            (function () {
                var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/{$this->pageID}';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();

        }
        setTimeout(chatPlugin, 3000);
        //End of Tawk.to Script PageID: {$this->pageID}
JS;
        $this->setContents($contents);

    }


}