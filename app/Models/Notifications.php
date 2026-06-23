<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $fillable = [
        'submission_id',
        'title',
        'message',
    ];

    protected $casts = [
        'submission_id' => 'integer',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'notification_reads')
                    ->withPivot('read_at');
    }

    public function getIsReadAttribute()
    {
        return $this->readers->contains(Auth::id());
    }
}
