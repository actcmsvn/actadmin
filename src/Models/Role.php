<?php

namespace ACT\Actadmin\Models;

use Illuminate\Database\Eloquent\Model;
use ACT\Actadmin\Facades\Actadmin;

class Role extends Model
{
    protected $guarded = [];

    public function users()
    {
        $userModel = Actadmin::modelClass('User');

        return $this->belongsToMany($userModel, 'user_roles')
                    ->select(app($userModel)->getTable().'.*')
                    ->union($this->hasMany($userModel))->getQuery();
    }

    public function permissions()
    {
        return $this->belongsToMany(Actadmin::modelClass('Permission'));
    }
}
