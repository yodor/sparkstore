<?php
include_once("session.php");
include_once("input/DataInputFactory.php");
include_once("pages/AdminLoginPage.php");

include_once("input/validators/EmailValidator.php");
include_once("beans/AdminUsersBean.php");

include_once("mailers/ForgotPasswordMailer.php");
include_once("auth/Authenticator.php");
include_once("components/InputComponent.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");
include_once("components/TextComponent.php");

class ForgotPasswordProcessor extends FormProcessor
{
    protected function processImpl(InputForm $form)
    {
        parent::processImpl($form);

        $ub = new AdminUsersBean();

        $email = $form->getInput("email")->getValue();

        if (!$ub->emailExists($email)) {
            throw new Exception(tr("This email is not registered with us"));
        }

        $users = new AdminUsersBean();

        $random_pass = Authenticator::RandomToken(8);
        $fpm = new ForgotPasswordMailer($email, $random_pass, fullURL(ADMIN_LOCAL . "/admin/login.php"));
        $db = DBConnections::Factory();
        try {
            $db->transaction();

            $userID = $users->email2id($email);
            $update_row["password"] = md5($random_pass);
            if (!$users->update($userID, $update_row, $db)) throw new Exception("Unable to update records: " . $db->getError());

            $fpm->send();

            $db->commit();

        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }

    }
}

$page = new AdminLoginPage();
$page->addCSS(SPARK_LOCAL . "/css/LoginForm.css");

$form = new InputForm();
$form->addInput(DataInputFactory::Create(DataInputFactory::EMAIL, "email", "Input your registered email", 1));

$frend = new FormRenderer($form);

$frend->getSubmitButton()->setContents("Send");
$frend->addClassName("LoginFormRenderer");

$proc = new ForgotPasswordProcessor();

$proc->process($form);

if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
    Session::SetAlert(tr("Your new password was sent to your email") . ": " . $form->getInput("email")->getValue());
    header("Location: login.php");
    exit;
}
else {
    Session::setAlert($proc->getMessage());
}
$page->startRender();

$page->setTitle(tr("Forgot Password"));

$frend->setCaption(SITE_TITLE . "<BR><small>" . tr("Administration") . "</small>");

$frend->render();

$page->finishRender();
?>