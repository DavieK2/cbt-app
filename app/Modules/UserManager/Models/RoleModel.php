<?php

namespace App\Modules\UserManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    use HasFactory;

    protected $table = "roles";

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(UserModel::class, 'role_id');
    }
}
