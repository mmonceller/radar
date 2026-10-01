<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'image_path', 'category_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(QuestionFollowUp::class);
    }

    public function awardedLead(): HasOne
    {
        return $this->hasOne(Lead::class)->whereNotNull('awarded_at');
    }
}
