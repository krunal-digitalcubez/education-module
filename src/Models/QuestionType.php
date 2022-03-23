<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    const QUESTION_TYPE_MCSA = 1; //'multiple_choice_single_answer';
    const QUESTION_TYPE_MCMA = 2; //'multiple_choice_multiple_answer';
    const QUESTION_TYPE_FILL = 3; //'fill_the_blank';

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
