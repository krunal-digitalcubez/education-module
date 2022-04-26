<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translatable()
    {
        return $this->morphTo();
    }
}
