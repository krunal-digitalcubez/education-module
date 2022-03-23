<?php

namespace Digitalcubez\EducationModule\Database\Factories;

use Digitalcubez\EducationModule\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition()
    {
        $title = $this->faker->title;
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph,
            'media_url' => $this->faker->url,
            'total_marks' => 0,
            'pass_marks' => 0,
            'max_attempts' => 0,
            'is_published' => 1,
            'media_url' => $this->faker->imageUrl(300, 300),
            'media_type' => 'image',
            'duration' => 0,
            'valid_from' => date('Y-m-d H:i:s'),
            'valid_upto' => null
        ];
    }
}
