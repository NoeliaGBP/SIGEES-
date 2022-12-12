<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'surname' => 'required',
            'role' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $person = new Person();
                $person->name = $request->name;
                $person->surname = $request->surname;
                $person->second_surname = $request->second_surname;
                $person->status = true;
                $person->save();

                $user = new User();
                $user->role_id = $request->role["id"];
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->status = true;
                $user->person_id = $person->id;
                $user->save();

                DB::commit();
                return $this->getResponse201('user account', 'created', $person);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validator->fails()) {
            $user = User::where('email', '=', $request->email)->first();
            if (isset($user->id)) {
                if (Hash::check($request->password, $user->password)) {
                    foreach ($user->tokens as $token) {
                        if ($token->last_used_at === null) {
                            $token->delete();
                        }
                    }
                    $token = $user->createToken('AT:' . date("jFYh:i:s"))->plainTextToken;
                    return response()->json([
                        'message' => "Successful authentication",
                        'access_token' => $token,
                        "user" => $user
                    ], 200);
                } else { //Invalid credentials
                    return $this->getResponse401();
                }
            } else { //User not found
                return $this->getResponse401();
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); //Revoke all tokens
        return response()->json([
            'message' => "Logout successful"
        ], 200);
    }
}
