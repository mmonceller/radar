<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadVote extends Model
{
    use HasFactory;

    // Add this fillable array to authorize mass assignment
    protected $fillable = [
        'lead_id',
        'user_id',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}