<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Observation;
use App\Models\PersonRooms;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function clean(Request $request, $id)
    {
        DB::beginTransaction();
        try {
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
                        DB::commit();
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
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function lock(Request $request, $id)
    {
        DB::beginTransaction();
        try {
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
                        DB::commit();
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
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function enable($id)
    {
        DB::beginTransaction();
        try {
            $personRoom = PersonRooms::with("room", "person", "status")->where("room_id", $id)->first();
            if ($personRoom) {
                $status = Status::where("name", "ENABLED")->first();
                if ($status) {
                    $personRoom->room->status_id = $status->id;
                    $personRoom->room->update();
                    $personRoom->updated_at = Carbon::now();
                    $observations = $this->updateObservations($personRoom);

                    $personRoom->update();
                    $personRoom->observations =  $observations;
                    $personRoom->room->status = $status;
                    $personRoom->room->building = Building::find($personRoom->room->building_id);
                    DB::commit();
                    return $this->getResponse201('room', 'enabled', $personRoom);
                } else {
                    return $this->getResponse500(["Status not founded"]);
                }
            } else {
                return $this->getResponse403();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function updateObservations(PersonRooms $personRoom)
    {
        DB::beginTransaction();
        try {
            $pendient = Status::where("name", "PENDIENT")->first();
            $done = Status::where("name", "DONE")->first();
            $observations = Observation::join('person_rooms', 'person_rooms.room_id', 'observations.person_room_id')
                ->where("observations.status_id", $pendient->id)
                ->where("person_rooms.id", $personRoom->id)->update([
                    'observations.status_id' => $done->id
                ]);


            DB::commit();
            return  $observations;
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function setObservations(PersonRooms $personRoom)
    {
        DB::beginTransaction();
        try {
            $personRoom->room->makeHidden(["status_id", "building_id"]);
            $personRoom->makeHidden(["obs", "room_id", "status_id", "person_id"]);
            if ($personRoom) {
                if ($personRoom->obs) {
                    $status = Status::where("name", "PENDIENT")->first();
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
                        DB::commit();
                        return  $personRoom;
                    } else {
                        return $this->getResponse500(["Status not founded"]);
                    }
                }
            }
            return  $personRoom;
        } catch (Exception $e) {
            DB::rollBack();
        }
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
                ->orderBy('person_rooms.updated_at', "desc")->get()
                ->makeHidden(["building_id", "status_id", "room_id", "observations"]);
            $result = [];
            $size = sizeof($personrooms) - 1;
            for ($i = 0; $i <= $size; $i++) {
                $personrooms[$i]->building = Building::find($personrooms[$i]->room->building_id);
                array_push($result, $personrooms[$i]);
            }
            return $this->getResponse201('rooms', 'founded', $result);
        } else {
            return $this->getResponse500(["User not founded"]);
        }
    }

    public function updateObservationRoom(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'photo' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $observation = Observation::find($id);
                if ($observation) {
                    $observation->description =  $request->description;
                    $observation->photo = $request->photo;
                    $observation->update();
                    DB::commit();
                    return $this->getResponse201('room', 'enabled', $observation);
                } else {
                    return $this->getResponse403();
                }
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        }
    }
}
