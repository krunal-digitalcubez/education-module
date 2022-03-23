<?php

namespace Digitalcubez\EducationModule\Database\Factories;

use Digitalcubez\EducationModule\Models\Question;
use Digitalcubez\EducationModule\Models\QuestionType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        $question_type = QuestionType::create(
            [
                'question_type' => 'fill_the_blank',
            ]
        );
        return [
            'question' => $this->faker->words(4, true),
            'question_type_id' => $question_type->id,
            'media_url' => $this->faker->url,
            'is_active' => $this->faker->numberBetween(0, 1),
            'media_type' => 'image',
        ];
    }
}
