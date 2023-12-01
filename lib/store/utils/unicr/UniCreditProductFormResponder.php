<?php
include_once("responders/json/JSONFormResponder.php");
class UniCreditOperation {

    //service calling stub
    protected UniCreditServiceStub $stub;

    //service endpoint for this operation
    protected string $endpoint = "";

    //request data array
    protected array $data = array();

    //result response data array
    protected $result;

    //error text set from stub
    protected string $errorText="";

    public function __construct(UniCreditServiceStub $stub, string $name)
    {
        $this->data = array();
        $this->endpoint = $stub->getServiceURL().$name;
        $this->stub = $stub;
    }
    public function setData(string $key, mixed $value) : void
    {
        $this->data[$key] = $value;
    }
    public function getData(string $key) : mixed
    {
        return $this->data[$key];
    }
    public function setCredentials(string $otpUser, string $otpPass) : void
    {
        $this->setData("user", $otpUser);
        $this->setData("pass", $otpPass);
    }
    public function getDataArray() : array
    {
        return $this->data;
    }
    public function getEndpoint() : string
    {
        return $this->endpoint;
    }
    public function setResult($result) : void
    {
        $this->result = $result;
    }
    public function getResult() : mixed
    {
        return $this->result;
    }

    public function setError(string $errorText) : void
    {
        $this->errorText = $errorText;
    }
    public function getError(): string
    {
        return $this->errorText;
    }
    public function haveError() : bool
    {
        return strlen($this->errorText)>0;
    }

    public function doRequest() : void
    {
        $this->stub->doRequest($this);
    }

    public function setKOP(string $KOP) : void
    {
        $this->setData("onlineProductCode", $KOP);
    }
}
class OprGetCoeff extends UniCreditOperation
{
    public function __construct(UniCreditServiceStub $stub)
    {
        parent::__construct($stub, '/api/otp/getCoeff');
    }
    public function setInstallmentCount(int $months) : void
    {
        $this->setData("installmentCount", $months);
    }

}
class OprSucfOnlineSessionStart extends UniCreditOperation
{
    public function __construct(UniCreditServiceStub $stub)
    {
        parent::__construct($stub, '/api/otp/sucfOnlineSessionStart');
    }

    public function setClientData(string $firstName, string $lastName, string $phone) : void
    {
        $this->setData("clientFirstName", $firstName);
        $this->setData("clientLastName", $lastName);
        $this->setData("clientPhone", $phone);

    }
    public function setProductData(SellableItem $item)
    {
        $this->setData("totalPrice", $item->getPriceInfo()->getSellPrice());
        $this->setData("returnURL", SparkPage::Instance()->getPageURL());

        $this->setData("orderNo", time());

        $items = array(
            0 => array(
                "name" => $item->getName(),
                "code" => $item->getProductID(),
                "type" => implode(", ", $item->getCategoryPath()),
                "count" => 1,
                "singlePrice" => $item->getPriceInfo()->getSellPrice(),
            ),
        );
        $this->setData("items", $items);
    }
    public function setMonthlyPayment(string $payment, int $installmentCount)
    {
        $this->setData("monthlyPayment", $payment);
        $this->setData("installmentCount", $installmentCount);
    }
}
class UniCreditServiceStub {
    const ENV_TEST = 'https://onlinetest.ucfin.bg/suos';
    const ENV_PROD = 'https://online.ucfin.bg/suos';

    const APP_TEST = 'https://onlinetest.ucfin.bg/sucf-online/Request/Create';

    const APP_PROD = 'https://online.ucfin.bg/sucf-online/Request/Create';

    protected string $serviceURL;
    protected string $appURL;

    protected $test_mode = true;

    public function __construct(bool $test_mode)
    {
        if ($test_mode) {
            $this->serviceURL = self::ENV_TEST;
            $this->appURL = self::APP_TEST;
        }
        else {
            $this->serviceURL = self::ENV_PROD;
            $this->appURL = self::APP_PROD;
        }
        $this->test_mode = $test_mode;
    }

    public function getServiceURL() : string
    {
        return $this->serviceURL;
    }

    public function getAppURL() : string
    {
        return $this->appURL;
    }

    //return object decoded from JSON data returned
    public function doRequest(UniCreditOperation $opr)
    {

        $opr->setResult(array());
        $opr->setError("");

        // Init cURL with the address
        $request = curl_init($opr->getEndpoint());

        $json = json_encode($opr->getDataArray());


        // Името на файл съдържащ един или повече сертификати, които се използват за да се верифицира идващия сървърен сертификат
        // NOTE: Опцията не е нужна ако са зададени конфигурации curl.cainfo и openssl.cafile в php.ini
        //curl_setopt($request, CURLOPT_CAINFO, $caFile);

        if (!$this->test_mode) {
            // името на файл, съдържащ само личен SSL ключ в текстови формат (PEM)
            curl_setopt($request, CURLOPT_SSLKEY, UNICREDIT_KEY_FILE);

            // Парола, която се използва за отключване на файла
            //curl_setopt($request, CURLOPT_SSLKEYPASSWD, "");

            // името на файл, съдържащ само клиентския сартификат в текстови формат (PEM)
            curl_setopt($request, CURLOPT_SSLCERT, UNICREDIT_CERT_FILE);
        }

        // Парола, която се използва за отключване на файла
        //curl_setopt($request, CURLOPT_SSLCERTPASSWD, "");

        // задава се типът на заявката да е POST
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, "POST");

        // закачат се данните, преди това сериализирани до json
        curl_setopt($request, CURLOPT_POSTFIELDS, $json);

        // връща резултатът от заявката като стринг
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        /**
         * Опции, използвани единствено при отстраняване на грешки и за повече информация
         */
        //curl_setopt($request, CURLOPT_CERTINFO, true);             // извежда SSL информация за използваните сертификати

        // NOTE: Added in cURL 7.19.1. Available since PHP 5.3.2. Requires CURLOPT_VERBOSE to be on to have an effect.
        //curl_setopt($request, CURLOPT_FAILONERROR, true);          // извежда допълнителна информация ако върнатия статус код е по-голям или равен на 400
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);      // верификация на сървърен сертификат, само за тестови постановки
        //curl_setopt($request, CURLOPT_HEADER, true);               // извежда информация за изпратените header-и
        //curl_setopt($request, CURLOPT_VERBOSE, true);              // извежда допълнителна информация за заявката
        //curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);          // верификация на локален сертификат, само за тестови постановки
        //curl_setopt($request, CURLOPT_SSLVERSION, 5);              // указва версията на протоколоа използван за криптиране на комуникацията ръчно


        curl_setopt($request, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            )
        );

        $result = curl_exec($request);

        if($result !== false) {

            $response = null;

            try {
                //$jsonAsString = var_export($jsonOut, true);
                //echo "Response OK: ", $jsonAsString, "<br>";
                $response = json_decode($result);
                $opr->setResult($response);

            } catch (Exception $e) {
                //getRequestInfo()
                $opr->setError("Error: ".$e->getMessage());
                return;

            }

            if ($response->errorCode>0) {
                $opr->setError("Service Error(".$response->errorCode."): ".$response->errorText);
            }
            return;
        }
        //debug($this->getRequestInfo($request));

        $opr->setError('cUrl Error ('.curl_errno($request).'): '.curl_error($request));

    }

    public function getRequestInfo(CurlHandle $handle) : string
    {
        $info = curl_getinfo($handle);
        $infoString = var_export($info, true);
        ob_start();
        echo "Request information: " , "<br>";
        echo "<pre>$infoString</pre>";
        echo "<br>";
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

class UniCreditProductForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "firstName", "Име", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "lastName", "Фамилия", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Телефон", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "installmentCount", "InstallmentCount", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "monthlyPayment", "MonthlyPayment", 0);
        $this->addInput($field);
    }

}

class UniCreditProductFormResponder extends JSONFormResponder
{
    const MIN_PRICE = 150;
    const MAX_PRICE = 50000;

    protected string $otpUser;
    protected string $otpPass;
    protected string $kop;

    /**
     * @var SellableItem
     */
    protected $sellable;

    protected $serviceStub;

    protected $fldInstallment;

    public function __construct(SellableItem $sellable)
    {
        parent::__construct("UniCreditProductFormResponder");
        $this->sellable = $sellable;

        $config = ConfigBean::Factory();
        $config->setSection("store_config");
        $this->otpUser = $config->get("uncr_otp_user");
        $this->otpPass = $config->get("uncr_otp_pass");
        $this->kop = $config->get("uncr_kop");


        if (strlen($this->otpUser)<1 || strlen($this->otpPass)<1 || strlen($this->kop)<1) {
            throw new Exception("Not enabled in config");
        }

        if ($this->sellable->getPriceInfo()->getSellPrice()<self::MIN_PRICE)
        {
            throw new Exception("Price Min not in range");
        }
        if ($this->sellable->getPriceInfo()->getSellPrice()>self::MAX_PRICE)
        {
            throw new Exception("Price Max not in range");
        }

        $test_mode = $config->get("uncr_test", 0);

        if (!defined("UNICREDIT_CERT_FILE")) {
            if (!$test_mode) {
                throw new Exception("Certificate not installed");
            }
        }
        if (!defined("UNICREDIT_KEY_FILE")) {
            if (!$test_mode) {
                throw new Exception("Key not installed");
            }
        }

        $this->serviceStub = new UniCreditServiceStub($test_mode);


        $installments = array(
            3=>"3 Месеца", 6=>"6 Месеца", 9=>"9 Месеца", 12=>"12 Месеца",
            18=>"18 Месеца", 24=>"24 Месеца", 30=>"30 Месеца", 36=>"36 Месеца",
        );

        $itr = new ArrayDataIterator($installments);
        $field = DataInputFactory::Create(DataInputFactory::SELECT, "installmentCount", "Брой месеци", 1);
        $field->getRenderer()->setIterator($itr);
        $field->getRenderer()->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_ID);
        $field->getRenderer()->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);
        $field->getRenderer()->setAttribute("onChange", "calculateMonthly()");
        $field->setValue(12);
        //$this->addInput($field);

        $this->fldInstallment = $field;
    }

    protected function createForm(): InputForm
    {
        return new UniCreditProductForm();
    }
    protected function validateInstallmentCount(int $installmentCount) : int
    {
        $result = 12;

        if ($installmentCount >= 3 && $installmentCount <= 36) {
            $result = $installmentCount;
        }
        return $result;
    }

    protected function calculateMonthlyPayment(int $installmentCount, JSONResponse $resp)
    {
        $installmentCount = $this->validateInstallmentCount($installmentCount);

        $this->fldInstallment->setValue($installmentCount);

        $opr = new OprGetCoeff($this->serviceStub);
        $opr->setCredentials($this->otpUser, $this->otpPass);
        $opr->setInstallmentCount($installmentCount);
        $opr->setKOP($this->kop);

        echo "<div class='notice'>";
        $opr->doRequest();
        if ($opr->haveError()) {
            echo "<div class='error'>";
            echo $opr->getError();
            echo "</div>";
        }
        else {
            $result = $opr->getResult();
            $coeff = (float)$result->coeffList[0]->coeff;
            $interestPercent = (float)$result->coeffList[0]->interestPercent;
            $productPrice = $this->sellable->getPriceInfo()->getSellPrice();
            $monthlyPayment = $productPrice * $coeff;
            //$this->form->getInput("monthlyPayment")->setValue($monthlyPayment);
            //gpr(((1+$interestPercent/12)^12)-1)*100

            $gpr = 1+($interestPercent/100.00)/12;
            $gpr = pow($gpr, 12);
            $gpr-=1;
            $gpr*=100;

            $resp->monthlyPayment = sprintf("%0.2f",$monthlyPayment);
            $resp->installmentCount = $installmentCount;
            $resp->productPrice = sprintf("%0.2f",$productPrice);
            $resp->glp = sprintf("%0.2f",$interestPercent);
            $resp->gpr = sprintf("%0.2f",$gpr);

            //echo "<label>Ориентировъчна месечна вноска на изплащане за срок от $installmentCount месеца:</label>";
            echo "<label>Ориентировъчна месечна вноска на изплащане за срок от:</label>";
            echo "<BR>";
            $this->fldInstallment->getRenderer()->render();
            echo "<BR><BR>";
            echo "<div class='item monthly_payment'><label>Погасителна вноска: </label><span>".formatPrice($monthlyPayment)."</span></div>";

            echo "<div class='item product_price'><label>Цена на продукта: </label><span>".formatPrice($productPrice)."</span></div>";
            echo "<div class='item interest_percent'><label>ГЛП: </label><span>".sprintf("%0.2f",$interestPercent)."%"."</span></div>";
            echo "<div class='item interest_percent'><label>ГПР: </label><span>".sprintf("%0.2f",$gpr)."%"."</span></div>";
            echo "<BR>";
//            echo "<label>Срокът на изплащане се заявава в следваща стъпка!</label>";
//            echo "<BR><BR>";
        }
        echo "</div>";

    }

    public function _calculateMonthly(JSONResponse $resp)
    {
        if (isset($_GET["installmentCount"])) {
            $this->calculateMonthlyPayment((int)$_GET["installmentCount"], $resp);

        }
        else {
            $resp->message = "No installmentCount received";
        }

    }


    public function _render(JSONResponse $resp)
    {

        echo "<div class='unilogo'><img src='".STORE_LOCAL."/images/unicredit-logo.png'></div>";

        $installmentCount = $this->fldInstallment->getValue();

        $this->calculateMonthlyPayment($installmentCount, $resp);

        $this->form->getRenderer()->render();

    }

    protected function onProcessError(JSONResponse $resp)
    {
        $this->fldInstallment->setValue($this->form->getInput("installmentCount")->getValue());
        $this->_render($resp);
        $resp->message = $this->proc->getMessage();
    }

    //form processing complete success
    protected function onProcessSuccess(JSONResponse $resp)
    {

        $monthlyPayment = $this->form->getInput("monthlyPayment")->getValue();
        if (!$monthlyPayment) {
            $this->onProcessError($resp);
            $resp->message = "No monthlyPayment specified";
            return;
        }

        $installmentCount = $this->validateInstallmentCount($this->form->getInput("installmentCount")->getValue());
        if (!$installmentCount) {
            $this->onProcessError($resp);
            $resp->message = "No installmentCount specified";
            return;
        }

        $opr = new OprSucfOnlineSessionStart($this->serviceStub);
        $opr->setCredentials($this->otpUser, $this->otpPass);

        $firstName = $this->form->getInput("firstName")->getValue();
        $lastName = $this->form->getInput("lastName")->getValue();
        $phone = $this->form->getInput("phone")->getValue();
        $opr->setClientData($firstName, $lastName, $phone);

        $opr->setKOP($this->kop);

        $opr->setMonthlyPayment($monthlyPayment, $installmentCount);
        debug("UNI=> monthlyPayment: $monthlyPayment | installmentCount: $installmentCount");

        $opr->setProductData($this->sellable);

        $opr->doRequest();

        if ($opr->haveError()) {
            $this->onProcessError($resp);
            $resp->message = "Error: ".$opr->getError();
        }
        else {
            $resp->redirect = $this->serviceStub->getAppURL();
            $resp->suosId = $opr->getResult()->sucfOnlineSessionID;
        }

    }

}
?>
