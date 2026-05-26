<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class AuthController extends Controller
{
    /**
     * Login
     * 
     * List of usernames (password is same as username):
     * 
     * 
     * School Accounts:
     * atlubolaES,calumpangES,monicayoIS,inesIS,
     * camachilesHS,mabalacatHS,sapangSHS,phisciHS,
     * 
     * 
     * 
     * Division Admin: super_intendent,
     * 
     * 
     * 
     * System Admins: it_officer,developers
     * 
     * 
     * 
     * Use case usernames:
     * 
     * usecaseOne - no school ID, but is school account
     * 
     * usecaseTwo - no role
     * 
     * usecaseThree - user is not set as active
     */
    public function login(Request $request)
    {
        $env = strtolower($request->header('Environment', 'backend'));
        $useCookies = $env === 'frontend' || $request->hasHeader('X-XSRF-TOKEN');

        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        // Check if user exists first
        if (!$user) {
            return $this->error('Invalid credentials.');
        }

        // Custom checks
        if (!$user->is_active) {
            return $this->error('User is inactive!');
        }

        if (!$user->roles()->exists()) {
            return $this->error(
                'No role assigned to this account. Please contact the administrator.'
            );
        } else if ($user->hasRole('School Account') && !$user->school_id) {
            return $this->error(
                'No school assigned to this account. Please contact the administrator.'
            );
        }

        // Check password
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials.');
        }

        $user = auth()->user();

        if(!$useCookies){
        $token = $user->createToken('api-token')->plainTextToken;

            activity('Logged In')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'datetime' => now()->format('Y-m-d h:i:s A'),
            ])
            ->log($user->name . ' has successfully logged in.');
            
            return $this->success('User Logged in successfully', [
                'User' => new UserResource($user),
                'token' => $token,
            ]);
        }

        if ($request->hasSession()) {
                    $request->session()->regenerate();
                }

        //RateLimiter::clear($key); For Future Use

        $user = User::where('username', $request->username)->first();

         activity('Logged In')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'datetime' => now()->format('Y-m-d h:i:s A'),
            ])
            ->log($user->name . ' has successfully logged in.');
        
        return $this->success('Logged in successfully', ['user' => new UserResource($user)]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $env = strtolower($request->header('Environment', 'backend'));
        $useCookies = $env === 'frontend' || $request->hasHeader('X-XSRF-TOKEN');
        
        $user = User::where('username', $request->username)->first();

        if ($useCookies) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            activity("Logged Out")
                    ->causedBy($request->user())
                    ->performedOn($request->user())
                    ->withProperties([
                        'datetime' => now()->format('Y-m-d h:i:s A'),
                    ])
                    ->log($request->user->name . ' logged out');
            return $this->success('Logout successful');
        }

        $user = $request->user();

        if ($user->tokens()->count() > 0) {
            $user->tokens()->delete();
        }
        activity("Logged Out")
                    ->causedBy($request->user())
                    ->performedOn($request->user())
                    ->log($user->name . ' logged out');

        return $this->success('Logout successful', 200);

        // if ($request->user()) {
        //     $token = $request->user()->currentAccessToken();
        //     return $token;
        //     if ($token) {
        //         $token->delete();
        //         activity()
        //             ->causedBy($request->user())
        //             ->performedOn($request->user())
        //             ->log('User logged out');
        //         return $this->success('User Logged Out Successfully');
        //     }
        //     return $this->error('Invalid token used');
        // }
    }
}
