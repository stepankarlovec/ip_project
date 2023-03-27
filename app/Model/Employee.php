<?php
namespace App\Model;

use Nette;

final class Employee
{
    public function __construct(private Nette\Database\Explorer $database) {
    }

    public function editEmployee($data, $id){
            return $this->database
                ->table('employee')
                ->where('employee_id', $id)
                ->update([
                    'name' => $data->name,
                    'surname' => $data->surname,
                    'job' => $data->job,
                    'room' => $data->room,
                    'wage' => $data->wage,
                    'login' => $data->login,
                    'admin' => $data->admin,
                ]);
    }

    public function createEmployee($data){
        return $this->database
            ->table('employee')
            ->insert([
                'name' => $data->name,
                'surname' => $data->surname,
                'job' => $data->job,
                'room' => $data->room,
                'wage' => $data->wage,
                'login' => $data->login,
                'admin' => $data->admin
            ]);
    }

    public function getEmployeeKeys($id){
        return $this->database->table('key')
            ->where('employee', $id)
            ->fetchAll();
    }

    public function changeEmployeeKeys($data, $id){
            $this->database
                ->table('key')
                ->where('employee', $id)
                ->delete();

            foreach ($data as $room) {
                $this->database->table('key')
                    ->insert([
                        'employee' => $id,
                        'room' => $room,
                    ]);
            }
    }

    public function changePassword(string $password, $id){
        $this->database->table('employee')
            ->where('employee_id', $id)
            ->update([
                'password' => $password
            ]);
    }

    public function getEmployees(){
        return $this->database
            ->query("SELECT e.employee_id, e.name,e.surname, r.name as 'room_name', r.phone, r.room_id, e.job FROM `employee` e INNER JOIN room r ON e.room = r.room_id")
            ->fetchAll();
    }

    public function getEmployee($id){
        return $this->database
            ->query("SELECT e.name as krestni, e.surname as prijmeni, e.admin as admin, e.job, e.wage, e.login as login, r.name as `room_name`, r.room_id as `room_idd`, r2.name as `jmeno_klice`, r2.room_id 
FROM employee AS `e` 
Inner join room as `r` on (e.room = r.room_id) 
Inner join `key` as `k` on (k.employee = e.employee_id) 
Inner join room as r2 on (k.room = r2.room_id) 
WHERE e.employee_id = ?;", $id)->fetchAll();
    }

}