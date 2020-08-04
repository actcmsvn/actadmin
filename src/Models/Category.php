<?php

namespace ACT\Actadmin\Models;

use Illuminate\Database\Eloquent\Model;
use ACT\Actadmin\Facades\Actadmin;
use ACT\Actadmin\Traits\Translatable;

class Category extends Model
{
    use Translatable;

    protected $translatable = ['slug', 'name'];

    protected $table = 'categories';

    protected $fillable = ['slug', 'name'];

    public function posts()
    {
        return $this->hasMany(Actadmin::modelClass('Post'))
            ->published()
            ->orderBy('created_at', 'DESC');
    }

    public function parentId()
    {
        return $this->belongsTo(self::class);
    }
}
