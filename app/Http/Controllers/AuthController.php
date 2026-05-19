<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

use function Spatie\Activitylog\activity;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request){
        $credentials = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('username', $request->username)->first();

        // Check if user exists first
        if (!$user) {
            return $this->error('Invalid credentials');
        }

        // Custom checks
        if (!$user->is_active) {
            return $this->error('User is inactive!');
        }

        if(!$user->roles()->exists()){
            return $this->error(
                'No role assigned to this account. Please contact the administrator.'
            );
        }else if ($user->hasRole('School Account') && !$user->school_id) {
            return $this->error(
                'No school assigned to this account. Please contact the administrator.'
            );
        }

        // Check password
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials. Please contact the administrator.');
        }

    $token = $user->createToken('api-token')->plainTextToken;
    
    activity()
        ->causedBy($user)
        ->performedOn($user)
        ->log('User logged in');


        return $this->success('User Logged in successfully', [
            'User' => new UserResource($user),
            'Token' => $token,
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request){
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
                activity()
                    ->causedBy($request->user())
                    ->performedOn($request->user())
                    ->log('User logged out');
                return $this->success('User Logged Out Successfully');
            }
            return $this->error('Invalid token used');
        }
    }
}
