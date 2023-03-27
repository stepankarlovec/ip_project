<?php
namespace App\Model;

use Nette;

final class Room
{
    public function __construct(private Nette\Database\Explorer $database) {
    }

    public function getRooms()
    {
        return $this->database
            ->table('room')
            ->fetchAll();
    }

    public function getRoomsPairs()
    {
        return $this->database
            ->table('room')
            ->fetchPairs('room_id', 'name');
    }

    public function getRoom($id){
        return $this->database
            ->table('room')
            ->where('room_id', $id)
            ->fetch();
    }

    public function updateRoom($data, $id){
        return $this->database->table('room')
            ->where('room_id', $id)
            ->update([
                'no' => $data->no,
                'name' => $data->name,
                'phone' => $data->phone
            ]);
    }

    public function createRoom($data){
        return $this->database->table('room')
            ->insert([
                'no' => $data->no,
                'name' => $data->name,
                'phone' => $data->phone
            ]);
    }

    // working with employees
    public function getRoomAverageSalary($id){
        return $this->database
            ->table('employee')
            ->where('room', $id)
            ->aggregation("ROUND(AVG(wage),2)");
    }

    public function getRoomEmployees($id){
        return $this->database
            ->table('employee')
            ->where('room', $id)
            ->fetchAll();
    }

    public function getRoomKeys($id){
        return $this->database
            ->table('key')
            ->where('room', $id)
            ->fetchAll();
    }

    /*
     * $res = $db->query("SELECT room_id,r.no as roomNumber,  r.name as roomName, r.phone as roomPhone, e.name as jmeno, e.surname as prijmeni, e.employee_id
FROM `room` AS r
INNER JOIN `key` as k ON r.room_id = k.room
INNER JOIN `employee` as e ON k.employee = e.employee_id
WHERE r.room_id = :id; ", [":id" => $currentID]);
     */
    public function random($id){
        return $this->database
            ->query("SELECT room_id,r.no as roomNumber,  r.name as roomName, r.phone as roomPhone, e.name as jmeno, e.surname as prijmeni, e.employee_id
FROM `room` AS r 
INNER JOIN `key` as k ON r.room_id = k.room 
INNER JOIN `employee` as e ON k.employee = e.employee_id 
WHERE r.room_id = ?;", $id)
            ->fetchAll();
    }
}