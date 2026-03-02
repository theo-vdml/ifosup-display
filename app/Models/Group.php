<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = ['name', 'size'];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }
}
