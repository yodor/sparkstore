<?php
include_once("utils/output/OutputScript.php");

class TawktoScript extends OutputScript
{
    protected string $pageID = "";

    public function __construct(string $pageID)
    {
        parent::__construct();
        $this->pageID = $pageID;
    }

    public function setPageID(string $pageID) : void
    {
        $this->pageID = $pageID;
    }

    public function fillBuffer(): void
    {
        if (strlen(trim($this->pageID))<1) return;
        ?>
                <!--Start of Tawk.to Script-->
                <script type="text/javascript">
                    let chatPlugin = function()
                    {
        
                        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
                        (function () {
                            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                            s1.async = true;
                            s1.src = 'https://embed.tawk.to/<?php echo $this->pageID;?>';
                            s1.charset = 'UTF-8';
                            s1.setAttribute('crossorigin', '*');
                            s0.parentNode.insertBefore(s1, s0);
                        })();
        
                    }
                    setTimeout(chatPlugin, 3000);
                </script>
                <!--End of Tawk.to Script-->
        <?php
    }


}
?>
