<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;
use App\Model\Room;
use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Room $room,
    ) {}

    public function renderDefault(){
        $this->template->rooms = $this->room->getRooms();
    }

    public function renderRooms(){

    }
}
