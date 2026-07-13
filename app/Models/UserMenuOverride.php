<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMenuOverride extends Model
{
    public $timestamps  = false;
    protected $table    = 'user_menu_override';
    protected $fillable = ['user_id', 'menu_id', 'permission'];
    protected $casts    = ['permission' => 'integer'];
}
