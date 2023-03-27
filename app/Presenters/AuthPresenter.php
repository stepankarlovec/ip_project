<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class AuthPresenter extends Nette\Application\UI\Presenter
{
    public function __construct()
    {
    }

    public function renderLogin()
    {
        $this->setView('login');
    }

    public function actionLogout(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('You are logged out.');
        $this->redirect('Homepage:default');
    }

    protected function createComponentLogin(): Form
    {
        $form = new Form();

        $form->addText("username", "Username:");
        $form->addPassword("password", "Password:");
        $form->addSubmit("submit", "Login");

        $form->onSuccess[] = [$this, 'handleLogin'];
        return $form;
    }

    public function handleLogin(Form $form, $data){
        try{
            $this->user->login($data->username, $data->password);
            $this->flashMessage("Successfully logged in!", "success");
            return $this->forward('Homepage:default');
        }catch (Nette\Security\AuthenticationException $e){
            $this->flashMessage($e->getMessage(), "danger");
        }
    }
}