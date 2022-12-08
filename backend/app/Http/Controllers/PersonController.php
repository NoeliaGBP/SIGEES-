<?php

namespace App\Http\Controllers;

use App\Models\User;

class PersonController extends Controller
{
    public function PersonController()
    {
    }

    //Profile
    public function profile()
    {
        $user = User::with("person", "role")->where("id", auth()->user()->id)
            ->first()
            ->makeHidden(["role_id", "person_id"]);
        if ($user) {
            return $this->getResponse201('person', 'founded', $user);
        } else {
            return $this->getResponse500(["Person not founded"]);
        }
    }
}
