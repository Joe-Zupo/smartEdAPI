<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, BroadcastsEvents;

    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone_number'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [

            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    //User Relationships
    public function school(){
        $this->belongsTo(School::class);
    }

    public function submissions(){
        $this->hasMany(Submission::class);
    }

    public function comments(){
        $this->hasMany(Comment::class);
    }
}
