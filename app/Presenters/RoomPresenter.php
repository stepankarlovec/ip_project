<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Employee;
use App\Model\Room;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class RoomPresenter extends BasePresenter
{
    private $id;

    public function __construct(
        private Room $room,
        private Employee $employee,
    )
    {
    }

    public function renderDefault()
    {
        $this->template->rooms = $this->room->getRooms();
    }

    public function renderRoom($id)
    {
        if (!$this->room->getRoom($id)) {
            $this->redirect("Room:default");
        }
        $this->template->room = $this->room->getRoom($id);
        $this->template->employees = $this->room->getRoomEmployees($id);
        $this->template->avgSalary = $this->room->getRoomAverageSalary($id);
        $this->template->keys = $this->room->getRoomKeys($id);
        $this->template->random = $this->room->random($id);
    }

    public function actionEdit($id)
    {
        $this->isAdminOrError();
        $this->id = $id;
        $this->setView("edit-room");
    }

    public function actionDelete($id)
    {
        $this->isAdminOrError();
        $this->id = $id;
        $this->deleteRoomDetail($id);
        if ($this->user->identity->getData()['employee_id'] === $id) {
            $this->user->logout();
        }
        $this->redirect('Homepage:default');
    }

    private function deleteRoomDetail($id)
    {
        $this->isAdminOrError();
        $employees = $this->employee->getEmployees();
        $isDeletable = true;
        foreach ($employees as $employee) {
            if ($employee['room_id'] == $id) {
                $isDeletable = false;
                $this->flashMessage("Room is assigned to an employee " . $employee['name'] . " " . $employee['surname'] . ", please change employees room", "warning");
            }
        }

        if ($isDeletable) {
            $this->room->deleteRoom($id);
            $this->flashMessage("Successfully deleted", "warning");
            $this->redirect('Room:default');
        } else {
            $this->redirect('Room:default');
        }
    }

    public function actionCreate($id)
    {
        $this->id = $id;
        $this->setView("new-room");
    }

    protected function createComponentCreateRoom(): Form
    {
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
            } catch (\ErrorException $e) {
                $this->flashMessage("Something went wrong :(", "danger");
                Debugger::log($e);
            }
        }
    }

    protected function createComponentEditRoom(): Form
    {
        if (!$this->room->getRoom($this->id)) {
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
            } catch (\ErrorException $e) {
                $this->flashMessage("Something went wrong :(", "danger");
                Debugger::log($e);
            }
        }
    }
}
