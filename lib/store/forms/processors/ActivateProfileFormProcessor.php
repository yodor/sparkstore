<?php
include_once("forms/processors/FormProcessor.php");
include_once("forms/InputForm.php");
include_once("beans/UsersBean.php");
include_once("auth/Authenticator.php");
include_once("store/mailers/RegisterCustomerActivationMailer.php");

class ActivateProfileFormProcessor extends FormProcessor
{

    public function setEditID(int $editID)
    {
        throw new Exception("Unsupported function");
    }

    /**
     * @param InputForm $form
     * @throws Exception
     */
    protected function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);
        if ($this->status != IFormProcessor::STATUS_OK) return;

        $this->status = IFormProcessor::STATUS_NOT_PROCESSED;

        $email = $form->getInput("email")->getValue();
        $confirm_code = $form->getInput("confirm_code")->getValue();

        $users = new UsersBean();


        try {
            $affected_rows = $users->activate($email, $confirm_code);
            if ($affected_rows == 0) {
                throw new Exception(tr("Грешка при активация на профил. Проверете активационния код и опитайте отново."));
            }
            else if ($affected_rows == 1) {
                //OK
            }
            else if ($affected_rows > 1) {
                throw new Exception(tr("Този профил е вече активиран"));
            }
        }
        catch (Exception $e) {

            sleep(3);
            throw $e;
        }

    }
}

?>
