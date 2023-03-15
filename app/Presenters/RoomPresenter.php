<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Room;
use Nette;


final class RoomPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Room $room,
    ) {}

    public function renderDefault(){
        $this->template->rooms = $this->room->getRooms();
    }

    public function renderRoom($id){

        $this->template->room = $this->room->getRoom($id);
        $this->template->avgSalary = $this->room->getRoomAverageSalary($id);
    }

}
