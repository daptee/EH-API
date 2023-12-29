<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $table = 'users_types';

    const SUPERADMIN = 1;
    const ADMIN_EH = 2;
    const ADMIN_SUKHA = 3;
    const ADMIN_CAFETERIA = 4;

    protected $hidden = ['created_at', 'updated_at'];
}
