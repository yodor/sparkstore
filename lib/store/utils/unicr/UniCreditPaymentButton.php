<?php
include_once("store/utils/CreditPaymentButton.php");
include_once("store/utils/unicr/UniCreditProductFormResponder.php");

class UniCreditPaymentButton extends CreditPaymentButton
{

    protected $handler;

    public function __construct(SellableItem $item)
    {
        parent::__construct($item);

        try {
            $this->handler = new UniCreditProductFormResponder($item);
            $this->enabled = true;
        }
        catch (Exception $e) {
            $this->enabled = false;
            $this->handler = null;
            debug("Unable to initialize UniCredit payment module: ".$e->getMessage());
        }

    }

    public function renderButton()
    {
        echo "<a class='button' onClick='javascript:showUniCreditForm()'>";
        echo "<span class='icon'></span>";
        echo "<label>"."Купи на кредит"."</label>";
        echo "</a>";

?>
        <script>
            let uniDialog = new JSONFormDialog();
            function showUniCreditForm()
            {
                uniDialog.setResponder("UniCreditProductFormResponder");
                uniDialog.caption="Kупи на кредит";
                uniDialog.processSubmitResult = processSubmit;
                uniDialog.processRenderResult = processRender;
                uniDialog.show();
                $(uniDialog.visibleSelector() + " .Buttons button[action='confirm']").html("Продължи");
            }
            function calculateMonthly()
            {
                let req = new JSONRequest();
                req.setResponder(uniDialog.getJSONRequest().getResponder());
                //console.log("Submitting form");
                req.setFunction("calculateMonthly");

                let installmentCount = $(uniDialog.visibleSelector()+" SELECT[name='installmentCount']").find(":selected").val();
                //let form = $(uniDialog.visibleSelector()+" FORM").get(0);
                // let formData = new FormData(form);
                // let installmentCount = formData.get("installmentCount");

                req.setParameter("installmentCount", installmentCount);
                req.onSuccess = function(request_result) {
                    let result = request_result.json_result;
                    if (result.contents) {
                        $(uniDialog.visibleSelector() + " .notice").replaceWith(result.contents);
                        let form = $(uniDialog.visibleSelector()+" FORM").get(0);
                        form.elements["monthlyPayment"].value = result.monthlyPayment;
                        form.elements["installmentCount"].value = result.installmentCount;
                    }
                    else {
                        showAlert(result.message);
                    }
                };
                $(uniDialog.visibleSelector() + " .notice").html(uniDialog.loader);

                req.start();
            }

            function processRender(request_result) {
                let result = request_result.json_result;
                uniDialog.loadContent(result.contents);

                let form = $(uniDialog.visibleSelector()+" FORM").get(0);
                form.elements["monthlyPayment"].value = result.monthlyPayment;
                form.elements["installmentCount"].value = result.installmentCount;


            }
            function processSubmit(request_result, form_name) {
                let result = request_result.json_result;

                if (result.redirect) {

                    let form = document.createElement("form");
                    form.setAttribute("id", "redirectForm");
                    form.setAttribute("method", "post");
                    form.setAttribute("action", result.redirect);

                    // Create an input element for Full Name
                    let data = document.createElement("input");
                    data.setAttribute("type", "hidden");
                    data.setAttribute("name", "suosId");
                    data.setAttribute("value", result.suosId);

                    form.appendChild(data);

                    document.body.appendChild(form);
                    form.submit();

                }
                else if (result.contents) {
                    uniDialog.loadContent(result.contents);
                    showAlert(result.message);
                }
                else {
                    uniDialog.remove();
                    showAlert(result.message);
                }
            }

        </script>
<?php

    } //renderButton
}
