<?php

namespace Digitalcubez\EducationModule\Tests\Models;

use Digitalcubez\EducationModule\Traits\QuizParticipant;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use QuizParticipant;
    protected $guarded = ['id'];
    protected $table = 'authors';
}
