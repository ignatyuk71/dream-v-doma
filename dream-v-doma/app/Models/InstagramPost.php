<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramPost extends Model
{
    protected $fillable = ['image', 'alt', 'link', 'active', 'position'];
}
