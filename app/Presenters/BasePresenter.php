<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

abstract class BasePresenter extends Nette\Application\UI\Presenter{
    public function startup()
    {

        parent::startup();

        if(!$this->user->isLoggedIn()){
            $this->redirect('Auth:login');
        }
    }

    public function isAdminOrError():void{
        if(!$this->user->roles[0]===1){
            $this->flashMessage("For this event, you need to be an administrator");
            $this->redirect("Homepage:default");
        }
    }
}