<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class MasterPrivacyPolicy extends Model
{
    //
    protected $table = 'master_privacypolicies';
    protected $fillable = ['title', 'content', 'slug', 'is_active'];
}
