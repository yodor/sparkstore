<?php
include_once("store/utils/CreditPaymentButton.php");
include_once("store/utils/unicr/UniCreditProductFormResponder.php");
include_once("components/PageScript.php");

class UniCreditDialogScript extends PageScript
{
    public function code() : string
    {
        return <<<JS
            const uniDialog = new JSONFormDialog();

            function showUniCreditForm()
            {
                uniDialog.setResponder("UniCreditProductFormResponder");
                uniDialog.setTitle("Kупи на кредит");
                uniDialog.processSubmitResult = processSubmit;
                uniDialog.processRenderResult = processRender;
                uniDialog.buttons.querySelector("[action='confirm']").innerText = "Продължи";
                uniDialog.show();

            }

            function calculateMonthly()
            {
                let req = new JSONRequest();
                req.setResponder(uniDialog.getJSONRequest().getResponder());
                //console.log("Submitting form");
                req.setFunction("calculateMonthly");

                let installmentCount = uniDialog.element.querySelector("SELECT[name='installmentCount']").value;
 
                let initialPayment = uniDialog.element.querySelector("INPUT[name='initialPayment']").value;

                req.setParameter("installmentCount", installmentCount);
                req.setParameter("initialPayment", initialPayment);

                req.onSuccess = function(request_result) {
                    let result = request_result.json_result;
                    if (result.contents) {
                        uniDialog.element.querySelector(".notice").innerHTML = result.contents;
                        loadResultValues(result);
                    }
                    else {
                        showAlert(result.message);
                    }
                };
                uniDialog.element.querySelector(".notice").innerHTML = uniDialog.loader;

                req.start();
            }

            function loadResultValues(result)
            {
                const form = uniDialog.element.querySelector("FORM");

                form.monthlyPayment.value = result.monthlyPayment;
                form.installmentCount.value = result.installmentCount;
                form.initialPayment.value = result.initialPayment;
            }

            function processRender(request_result) {
                
                let result = request_result.json_result;
                uniDialog.loadContent(result.contents);
                
                //loadResultValues(result);

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
JS;

    }
}
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

        new UniCreditDialogScript();

    }

    public function renderButton()
    {
        echo "<a class='button' onClick='javascript:showUniCreditForm()'>";
        echo "<span class='icon'></span>";
        echo "<label>"."Купи на кредит"."</label>";
        echo "</a>";

    } //renderButton
}
