<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\User;

class UserController extends Controller
{

    public $successStatus = 200;

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        $pmaps_id = request('pmapsId');

        try {
            $response = Http::post('https://hris.usep.edu.ph/hris/api/auth/login', [
                "token" => env("HRIS_API_TOKEN"),
                "pmaps_id" => $pmaps_id,
                "password" => request('password')
            ]);

            $obj = json_decode($response->body());

            if (isset($obj->id)) {

                $user = User::updateOrCreate(
                    ['id' => $obj->id ],
                    [
                        'pmaps_id' => $pmaps_id,
                        'firstName' => $obj->FirstName,
                        'middleName' => $obj->MiddleName,
                        'lastName' => $obj->LastName,
                        'email' => $obj->Email,
                        'avatar' => $obj->Avatar
                    ]
                );

                Auth::login($user);

                $loggedInUser = Auth::user();

                $success['accessToken'] =  $loggedInUser->createToken('MyApp')->accessToken;
                $token = $loggedInUser->createToken('MyApp')->accessToken;

                $user->remember_token = $success['accessToken'];
                $user->save();

                return response()->json(["accessToken" => $token], $this->successStatus);
            }else{
                return response()->json("Invalid login credentials", 400);
            }

        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function account()
    {
        $user = Auth::user();

        if($user) {

            return response()->json([
                'id' => $user->id,
                'lastName' => $user->lastName,
                'firstName' => $user->firstName,
                'pmapsId' => $user->pmaps_id,
                'avatar' => $user->avatar,
                'role' => '',
                'accessToken' => $user->remember_token
            ], $this->successStatus);
        }else{
            return response()->json("Error", 400);
        }
    }

}
