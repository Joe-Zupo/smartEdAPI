<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public $fillable = [
        'user_id',
        'submission_id',
        'comment',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'submission_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
