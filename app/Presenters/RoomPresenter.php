<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Room;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class RoomPresenter extends BasePresenter
{
    private $id;

    public function __construct(
        private Room $room,
    ) {}

    public function renderDefault(){
        $this->template->rooms = $this->room->getRooms();
    }

    public function renderRoom($id){
        if(!$this->room->getRoom($id)){
            $this->redirect("Room:default");
        }
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

    public function actionCreate($id){
        $this->id = $id;
        $this->setView("new-room");
    }

    protected function createComponentCreateRoom(): Form{
        $form = new Form();
        $form->addText("name", "Name:")
            ->setRequired('Please fill name of the room');
        $form->addInteger("no", "Room's number:")
            ->addRule($form::INTEGER, "Musí být číslo")
            ->setRequired('Please fill rooms number');
        $form->addText("phone", "Phone:")
            ->setRequired('Please fill phone number');
        $form->addSubmit("submit", "Create");

        $form->onSuccess[] = [$this, 'handleCreateRoom'];
        return $form;
    }

    public function handleCreateRoom(Form $form, $data): void
    {
        if (isset($data) && $data) {
            try {
                $this->room->createRoom($data, $this->id);
                $this->flashMessage("Successfully created :)", "warning");
            }catch (\ErrorException $e){
                $this->flashMessage("Something went wrong :(", "danger");
                Debugger::log($e);
            }
        }
    }

    protected function createComponentEditRoom(): Form{
        if(!$this->room->getRoom($this->id)){
            $this->redirect("Room:default");
        }
        $room = $this->room->getRoom($this->id);

        $form = new Form();
        $form->addText("name", "Name:")
            ->setDefaultValue($room->name);
        $form->addInteger("no", "Room's number:")
            ->addRule($form::INTEGER, "Musí být číslo")
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
            try {
                $this->room->updateRoom($data, $this->id);
                $this->flashMessage("Successfully edited :)", "warning");
            }catch (\ErrorException $e){
                $this->flashMessage("Something went wrong :(", "danger");
                Debugger::log($e);
            }
        }
    }
}
