<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RegisterController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 404,
                'message' => $validator->errors()
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => 200,
                'username' => $user->name,
                'message' => 'Uspešno ste se registrovali!'
            ]);
        }
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 404,
                'message' => $validator->errors()
            ]);
        } else {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Pogrešan email ili lozinka! Pokušajte ponovo!'
                ]);
            } else {

                if ($user->role == 1) {
                    $role = 'admin';
                    $token = $user->createToken($user->email . '_AdminAuthTkn', ['server:admin'])->plainTextToken;
                } else {
                    $role = 'user';
                    $token = $user->createToken($user->email . '_AuthTkn', [''])->plainTextToken;
                }


                return response()->json([
                    'status' => 200,
                    'username' => $user->name,
                    'token' => $token,
                    'message' => 'Uspešno ste se ulogovali!',
                    'role' => $role
                ]);
            }
        }
    }


    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Uspešan logout!'
        ]);
    }
}
