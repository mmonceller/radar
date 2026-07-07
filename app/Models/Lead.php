<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    // Explicitly allow mass assignment for all your new structured fields
    protected $fillable = [
        'question_id', 
        'user_id', 
        'store_name', 
        'price', 
        'is_online', 
        'latitude', 
        'longitude', 
        'address', 
        'description', 
        'source_link', 
        'upvotes_count', 
        'downvotes_count', 
        'last_verified_at'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_verified_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function votes()
    {
        return $this->hasMany(LeadVote::class);
    }
}