<?php

namespace Digitalcubez\EducationModule\Database\Factories;

use Digitalcubez\EducationModule\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    public function definition()
    {

        return [
            'question_id' => null,
            'option' => $this->faker->word,
            'media_url' => $this->faker->url,
            'is_correct' => $this->faker->numberBetween(0, 1),
            'media_type' => 'image',
        ];
    }
}
