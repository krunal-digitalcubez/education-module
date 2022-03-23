<?php

namespace Digitalcubez\EducationModule\Database\Factories;

use Digitalcubez\EducationModule\ModelsQuestion;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    public function definition()
    {

        return [
            'quiz_id' => null,
            'question_id' => null,
            'marks' => 0,
            'is_optional' => false,
        ];
    }
}
