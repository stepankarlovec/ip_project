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

    public function getRoom($id){
        return $this->database
            ->table('room')
            ->where('room_id', $id)
            ->fetch();
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
}