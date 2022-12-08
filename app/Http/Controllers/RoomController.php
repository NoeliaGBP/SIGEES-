<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Observation;
use App\Models\PersonRooms;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function clean(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            $personRoom = PersonRooms::with("room", "person", "status")
                ->where("person_id", $user->person_id)
                ->where("room_id", $id)->first();
            if ($personRoom) {
                $status = Status::where("name", "CLEANED")->first();
                if ($status) {
                    $personRoom->room->status_id = $status->id;
                    $personRoom->room->update();
                    $personRoom->updated_at = now();;
                    $personRoom->update();
                    $personRoom->room->building = Building::find($personRoom->room->building_id);
                    $personRoom->obs = $request->observations;
                    $personRoom->room->status = $status;
                    $personRoom = $this->setObservations($personRoom);
                    return $this->getResponse201('room', 'cleaned', $personRoom);
                } else {
                    return $this->getResponse500(["Status not founded"]);
                }
            } else {
                return $this->getResponse403();
            }
        } else {
            return $this->getResponse500(["User not founded"]);
        }
    }

    public function lock(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            $personRoom = PersonRooms::with("room", "person", "status")
                ->where("person_id", $user->person_id)
                ->where("room_id", $id)->first();
            if ($personRoom) {
                $status = Status::where("name", "LOCKED")->first();
                if ($status) {
                    $personRoom->room->status_id = $status->id;
                    $personRoom->room->update();
                    $personRoom->updated_at = Carbon::now();;
                    $personRoom->update();
                    $personRoom->room->status = $status;
                    $personRoom->room->building = Building::find($personRoom->room->building_id);
                    $personRoom->obs = $request->observations;
                    $personRoom = $this->setObservations($personRoom);
                    return $this->getResponse201('room', 'locked', $personRoom);
                } else {
                    return $this->getResponse500(["Status not founded"]);
                }
            } else {
                return $this->getResponse403();
            }
        } else {
            return $this->getResponse500(["User not founded"]);
        }
    }

    public function enable(Request $request, $id)
    {
        $personRoom = PersonRooms::with("room", "person", "status")->where("room_id", $id)->first();
        if ($personRoom) {
            $status = Status::where("name", "ENABLED")->first();
            if ($status) {
                $personRoom->room->status_id = $status->id;
                $personRoom->room->update();
                $personRoom->updated_at = Carbon::now();;
                $personRoom->update();
                $personRoom->room->status = $status;
                $personRoom->room->building = Building::find($personRoom->room->building_id);
                return $this->getResponse201('room', 'enabled', $personRoom);
            } else {
                return $this->getResponse500(["Status not founded"]);
            }
        } else {
            return $this->getResponse403();
        }
    }

    public function setObservations(PersonRooms $personRoom)
    {
        $personRoom->room->makeHidden(["status_id", "building_id"]);
        $personRoom->makeHidden(["obs", "room_id", "status_id", "person_id"]);
        if ($personRoom) {
            if ($personRoom->obs) {
                $status = Status::where("name", "ENABLED")->first();
                if ($status) {
                    $observations = [];
                    foreach ($personRoom->obs as $obs) {
                        $observation = new Observation();
                        $observation->person_room_id = $personRoom->id;
                        $observation->description = $obs["description"];
                        $observation->photo = $obs["photo"];
                        $observation->status_id = $status->id;
                        $observation->save();
                        $observation->status = $status;
                        array_push($observations, $observation);
                        $observation->makeHidden(["person_room_id", "status_id"]);
                    }
                    $personRoom->person->status = $status;
                    $personRoom->observations = $observations;
                    return  $personRoom;
                } else {
                    return $this->getResponse500(["Status not founded"]);
                }
            }
        }
        return  $personRoom;
    }

    public function getRooms()
    {
        $status = Status::where("name", "ENABLED")->first();
        $pendient = Status::where("name", "PENDIENT")->first();
        if ($status && $pendient) {
            $personrooms = PersonRooms::with("room", "status")
                ->join('status', 'person_rooms.status_id', 'status.id')
                ->join('rooms', 'person_rooms.room_id', 'rooms.id')
                ->orderBy('rooms.building_id')
                ->where("rooms.status_id", "<>", $status->id)
                ->where("rooms.status_id", "<>", $pendient->id)
                ->orderBy('rooms.status_id')->get()
                ->makeHidden(["building_id", "status_id", "room_id"]);
            $result = [];
            foreach ($personrooms as $personroom) {
                $personroom->building = Building::find($personroom->room->building_id);
                array_push($result, $personroom);
            }
        } else {
            return $this->getResponse500(["Status not founded"]);
        }
        return $this->getResponse201('rooms', 'founded', $result);
    }

    public function getPendientRoomsByPersonId()
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            $status = Status::where("name", "PENDIENT")->first();
            if ($status) {
                $personrooms = PersonRooms::with("room", "status")
                    ->join('status', 'person_rooms.status_id', 'status.id')
                    ->join('rooms', 'person_rooms.room_id', 'rooms.id')
                    ->where("person_id", $user->person_id)
                    ->where("rooms.status_id", $status->id)
                    ->orderBy('rooms.building_id')->get()
                    ->makeHidden(["building_id", "status_id", "room_id"]);

                $result = [];
                foreach ($personrooms as $personroom) {
                    $personroom->building = Building::find($personroom->room->building_id);
                    array_push($result, $personroom);
                }

                return $this->getResponse201('rooms', 'founded', $result);
            } else {
                return $this->getResponse500(["Status not founded"]);
            }
        } else {
            return $this->getResponse500(["User not founded"]);
        }
    }

    public function getRoomsByPersonId()
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            $personrooms = PersonRooms::with("room", "status")
                ->join('status', 'person_rooms.status_id', 'status.id')
                ->join('rooms', 'person_rooms.room_id', 'rooms.id')
                ->where("person_id", $user->person_id)
                ->orderBy('rooms.building_id')
                ->orderBy('rooms.status_id')->get()
                ->makeHidden(["building_id", "status_id", "room_id"]);

            $result = [];
            foreach ($personrooms as $personroom) {
                $personroom->building = Building::find($personroom->room->building_id);
                array_push($result, $personroom);
            }

            return $this->getResponse201('rooms', 'founded', $result);
        } else {
            return $this->getResponse500(["User not founded"]);
        }
    }

    public function getIncidencesByPersonId()
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            $personrooms = PersonRooms::with("room", "status")
                ->join('status', 'person_rooms.status_id', 'status.id')
                ->join('rooms', 'person_rooms.room_id', 'rooms.id')
                ->join('observations', 'person_rooms.id', 'observations.person_room_id')
                ->where("person_id", $user->person_id)
                ->orderBy('rooms.building_id')
                ->orderBy('person_rooms.id')->get()
                ->makeHidden(["building_id", "status_id", "room_id", "observations"]);
            $personrooms2 = $personrooms;
            $result = [];
            $observations = [];
            $size = sizeof($personrooms) - 1;
            $size2 = sizeof($personrooms) - 1;
            for ($i = 0; $i <= $size; $i++) {
                $personrooms[$i]->building = Building::find($personrooms[$i]->room->building_id);
                array_push($observations, $personrooms2[$i]->observations);
                for ($j = 0; $j <= $size2; $j++) {
                    if ($personrooms[$i]->person_room_id == $personrooms2[$j]->person_room_id) {
                        array_push($observations, $personrooms2[$j]->observations);
                        $personrooms2->splice($j, 1);
                        $size = sizeof($personrooms) - 1;
                        $size2 = sizeof($personrooms2) - 1;
                    }
                }
                $personrooms[$i]->observationsAll = $observations;
                array_push($result, $personrooms[$i]);
                $observations = [];
            }
            return $this->getResponse201('rooms', 'founded', $result);
        } else {
            return $this->getResponse500(["User not founded"]);
        }
    }
}
