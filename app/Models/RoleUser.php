<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleUser extends Model
{
    protected $table = 'role_user';

    protected $fillable = ['user_id', 'role'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
