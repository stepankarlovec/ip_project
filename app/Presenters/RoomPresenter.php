<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Room;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class RoomPresenter extends Nette\Application\UI\Presenter
{
    private $id;

    public function __construct(
        private Room $room,
    ) {}

    public function renderDefault(){
        $this->template->rooms = $this->room->getRooms();
    }

    public function renderRoom($id){

        $this->template->room = $this->room->getRoom($id);
        $this->template->employees = $this->room->getRoomEmployees($id);
        $this->template->avgSalary = $this->room->getRoomAverageSalary($id);
        $this->template->keys = $this->room->getRoomKeys($id);
        $this->template->random = $this->room->random($id);
    }

    public function actionEdit($id){
        $this->id = $id;
        $this->setView("edit-room");
    }

    protected function createComponentEditRoom(): Form{
        $room = $this->room->getRoom($this->id);

        $form = new Form();
        $form->addText("name", "Name:")
            ->setDefaultValue($room->name);
        $form->addText("no", "Room's number:")
            ->setDefaultValue($room->no);
        $form->addText("phone", "Phone:")
            ->setDefaultValue($room->phone);
        $form->addSubmit("submit", "Edit");

        $form->onSuccess[] = [$this, 'handleEditRoom'];
        return $form;
    }

    public function handleEditRoom(Form $form, $data): void
    {
        if (isset($data) && $data) {
            bdump($data);
            try {
                $this->room->updateRoom($data, $this->id);
                $this->flashMessage("Úspěšně změněno", "warning");
            }catch (\ErrorException $e){
                $this->flashMessage("Něco se pokazilo :(", "danger");
                Debugger::log($e);
            }
        }
    }
}
