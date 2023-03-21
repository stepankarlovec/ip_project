<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Employee;
use App\Model\Room;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class EmployeePresenter extends Nette\Application\UI\Presenter
{
    private $id;

    public function __construct(
        private Employee $employee,
        private Room $room,
    ) {}

    public function renderDefault(){
        $this->template->employees = $this->employee->getEmployees();
    }

    public function renderEmployee($id){
        $tmp = $this->employee->getEmployee($id);
        $this->template->employee = $tmp[0];
        $this->template->rooms = $tmp;
    }

    public function actionEdit($id){
        $this->id = $id;
        $this->setView("edit-employee");
    }


    protected function createComponentEditEmployee(): Form{
        $currentEmployee = $this->employee->getEmployee($this->id)[0];
        $currentEmployeeKeys = $this->employee->getEmployeeKeys($this->id);
        $employeeKeys = [];
        foreach ($currentEmployeeKeys as $key){
            $employeeKeys[] = $key->room;
        }

        //bdump($currentEmployeeKeys);
        $val = [];
        foreach ($this->employee->getEmployee($this->id) as $emp){
            array_push($val, $emp->room_id, $emp->room_name);
        }
        //bdump($currentEmployee);
        $rooms = $this->room->getRoomsPairs();

        $form = new Form();
        $form->addText("name", "Name:")
            ->setDefaultValue($currentEmployee->krestni);
        $form->addText("surname", "Surname:")
        ->setDefaultValue($currentEmployee->prijmeni);
        $form->addText("job", "Position:")
            ->setDefaultValue($currentEmployee->job);
        $form->addSelect("room", "Room:", $rooms)
            ->setDefaultValue($currentEmployee->room_idd);
        $form->addInteger("wage", "Wage:")
            ->setDefaultValue($currentEmployee->wage);
        $form->addCheckboxList("keys", "Keys", $rooms)
        ->setDefaultValue($employeeKeys);
        $form->addSubmit("submit", "Edit");

        $form->onSuccess[] = [$this, 'handleEditEmployee'];
        return $form;
    }

    public function handleEditEmployee(Form $form, $data): void
    {
        if (isset($data) && $data) {
            bdump($data);
            bdump($this->id);
            try {
                $this->employee->editEmployee($data, $this->id);
                $this->employee->changeEmployeeKeys($data->keys, $this->id);
                $this->flashMessage("Úspěšně změněno", "warning");
            }catch (\ErrorException $e){
                $this->flashMessage("Něco se pokazilo :(", "danger");
                Debugger::log($e);
            }
        }
    }
}
