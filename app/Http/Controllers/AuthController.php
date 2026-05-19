<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request){
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $query = User::query();
        $user = $query->where('username', 'like', '%' . $request->query('username') . "%")->first();

        if(!$user->is_active){
            return $this->error('User is inactive!');
        }

        if($user->hasRole('School Account') && !$user->school_code){
            return $this->error('No school assigned to this account. Please contact the administrator.');
        }

        if (Auth::attempt($credentials)){
            $user = Auth::user();

            $token = $user->createToken('api-token')->plainTextToken;
            return $this->success('User Logged in successfuly',[
            'User' => new UserResource($user),
            'Token' => $token
            ]);
        }

        return $this->error('Invalid Credentials');
    }

    /**
     * Logout
     */
    public function logout(Request $request){
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
                return $this->success('User Logged Out Successfully');
            }
            return $this->error('Invalid token used');
        }
    }
}
