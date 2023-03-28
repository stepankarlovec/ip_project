<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Employee;
use App\Model\Room;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class EmployeePresenter extends BasePresenter
{
    private $id;

    public function __construct(
        private Employee $employee,
        private Room $room,
        private Nette\Security\Passwords $passwords
    ) {
    }

    public function renderDefault(){
        $this->template->employees = $this->employee->getEmployees();
    }

    public function renderEmployee($id){
        if(!isset($this->employee->getEmployee($id)[0])) {
            $this->redirect("Employee:default");
        }
        $tmp = $this->employee->getEmployee($id);
        $this->template->employee = $tmp[0];
        $this->template->rooms = $tmp;
    }

    public function actionDelete($id){
        $this->id = $id;
        $this->deleteEmployeeDetail($id);
        if($this->user->identity->getData()['employee_id']===$id) {
            $this->user->logout();
            $this->redirect('Homepage:default');
        }else{
            $this->flashMessage("Successfully deleted user", "warning");
            $this->redirect('Employee:default');
        }
    }

    private function deleteEmployeeDetail($id){
        $this->isAdminOrError();
        $this->employee->deleteEmployee($id);
    }

    public function actionCreate(){
        $this->setView("new-employee");
    }

    public function actionEdit($id){
        if($this->user->roles[0]===1 || $this->user->identity->getData()['employee_id']==$id) {
            $this->id = $id;
            $this->template->employee_id = $id;
            $this->setView("edit-employee");
        }else{
            $this->flashMessage("For this event, you need to be an administrator or logged as the user");
            $this->redirect("Homepage:default");
        }
    }

    protected function createComponentEditEmployee(): Form{
        if(!isset($this->employee->getEmployee($this->id)[0])) {
            die("employee doesn't exist..");
        }
        $currentEmployee = $this->employee->getEmployee($this->id)[0];
        $currentEmployeeKeys = $this->employee->getEmployeeKeys($this->id);
        $employeeKeys = [];
        foreach ($currentEmployeeKeys as $key){
            $employeeKeys[] = $key->room;
        }

        $val = [];
        foreach ($this->employee->getEmployee($this->id) as $emp){
            array_push($val, $emp->room_id, $emp->room_name);
        }
        $rooms = $this->room->getRoomsPairs();

        $form = new Form();
        $form->addText("name", "Name:")
            ->setDefaultValue($currentEmployee->krestni)
            ->setRequired('Please fill name');
        $form->addText("surname", "Surname:")
        ->setDefaultValue($currentEmployee->prijmeni)
            ->setRequired('Please fill surname');
        $form->addText("job", "Position:")
            ->setDefaultValue($currentEmployee->job)
            ->setRequired('Please fill position');
        $form->addSelect("room", "Room:", $rooms)
            ->setDefaultValue($currentEmployee->room_idd)
        ->setRequired('Please fill room');
        $form->addInteger("wage", "Wage:")
            ->addRule($form::INTEGER, "Musí být číslo")
            ->setDefaultValue($currentEmployee->wage)
            ->setRequired('Please fill wage');
        $form->addCheckboxList("keys", "Keys", $rooms)
        ->setDefaultValue($employeeKeys);

        $form->addText("login", "Login:")
            ->setDefaultValue($currentEmployee->login);
        $form->addPassword("password", "Password:");

        $form->addCheckbox("admin", "Admin")
            ->setDefaultValue($currentEmployee->admin);


        $form->addSubmit("submit", "Edit");

        $form->onSuccess[] = [$this, 'handleEditEmployee'];
        return $form;
    }


    public function handleEditEmployee(Form $form, $data): void
    {
        $this->isAdminOrError();

        if (isset($data) && $data) {
            try {
                $this->employee->editEmployee($data, $this->id);
                $this->employee->changeEmployeeKeys($data->keys, $this->id);
                if(isset($data->password) && $data->password){
                    $this->employee->changePassword($this->passwords->hash($data->password), $this->id);
                }
                $this->flashMessage("Successfully edited :)", "warning");
            }catch (\ErrorException $e){
                $this->flashMessage("Something went wrong :(", "danger");
                Debugger::log($e);
            }
        }
    }

    protected function createComponentEditEmployeeSelf(): Form{
        $currentEmployee = $this->employee->getEmployee($this->id)[0];
        if(!$currentEmployee){
            $this->error("This employee doesn't exist, stop trying to break this app please",404);
        }
        $currentEmployeeKeys = $this->employee->getEmployeeKeys($this->id);
        $employeeKeys = [];
        foreach ($currentEmployeeKeys as $key){
            $employeeKeys[] = $key->room;
        }

        $val = [];
        foreach ($this->employee->getEmployee($this->id) as $emp){
            array_push($val, $emp->room_id, $emp->room_name);
        }
        $rooms = $this->room->getRoomsPairs();

        $form = new Form();
        $form->addText("name", "Name:")
            ->setDisabled()
            ->setDefaultValue($currentEmployee->krestni);
        $form->addText("surname", "Surname:")
            ->setDisabled()
            ->setDefaultValue($currentEmployee->prijmeni);
        $form->addText("job", "Position:")
            ->setDisabled()
            ->setDefaultValue($currentEmployee->job);
        $form->addSelect("room", "Room:", $rooms)
            ->setDisabled()
            ->setDefaultValue($currentEmployee->room_idd);
        $form->addInteger("wage", "Wage:")
            ->setDisabled()
            ->setDefaultValue($currentEmployee->wage);
        $form->addCheckboxList("keys", "Keys", $rooms)
            ->setDisabled()
            ->setDefaultValue($employeeKeys);

        $form->addText("login", "Login:")
            ->setDisabled()
            ->setDefaultValue($currentEmployee->login);
        $form->addPassword("password", "Password:");
        $form->addSubmit("submit", "Edit");

        $form->onSuccess[] = [$this, 'handleEditEmployeeSelf'];
        return $form;
    }

    public function handleEditEmployeeSelf(Form $form, $data): void
    {
        if($this->user->identity->getData()['employee_id']==$this->id) {
            if (isset($data) && $data) {
                try {
                    if (isset($data->password) && $data->password) {
                        $this->employee->changePassword($this->passwords->hash($data->password), $this->id);
                    }
                    $this->flashMessage("Successfully edited password :)", "warning");
                } catch (\ErrorException $e) {
                    $this->flashMessage("Something went wrong :(", "danger");
                    Debugger::log($e);
                }
            }
        }else{
            $this->flashMessage("For this event you need to be logged as the user", "warning");
            $this->redirect("Homepage:default");
        }
    }

    protected function createComponentCreateEmployee(): Form{
        $rooms = $this->room->getRoomsPairs();

        $form = new Form();
        $form->addText("name", "Name:")
            ->setRequired('Please fill name');
        $form->addText("surname", "Surname:")
            ->setRequired('Please fill surname');
        $form->addText("job", "Position:")
            ->setRequired('Please fill position');
        $form->addSelect("room", "Room:", $rooms)
            ->setRequired('Please fill room');
        $form->addInteger("wage", "Wage:")
            ->addRule($form::INTEGER, "Musí být číslo")
            ->setRequired('Please fill wage');
        $form->addCheckboxList("keys", "Keys", $rooms);

        $form->addText("login", "Login:")
            ->setRequired('Please fill login');

        $form->addPassword("password", "Password:");

        $form->addCheckbox("admin", "Admin");

        $form->addSubmit("submit", "Create");

        $form->onSuccess[] = [$this, 'handleCreateEmployee'];
        return $form;
    }

    public function handleCreateEmployee(Form $form, $data): void
    {
        $this->isAdminOrError();
        if (isset($data) && $data) {
            try {
                $employee = $this->employee->createEmployee($data);
                $this->employee->changeEmployeeKeys($data->keys, $employee->employee_id);
                if(isset($data->password) && $data->password){
                    $this->employee->changePassword($this->passwords->hash($data->password), $employee->employee_id);
                }
                $this->flashMessage("Successfully created :)", "warning");
            }catch (\ErrorException $e){
                $this->flashMessage("Something went wrong :(", "danger");
                Debugger::log($e);
            }
        }
    }

}
