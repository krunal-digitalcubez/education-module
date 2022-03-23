<?php

namespace Digitalcubez\EducationModule\Traits;

use Digitalcubez\EducationModule\ModelsAttempt;

trait QuizParticipant
{
    public function quiz_attempts()
    {
        return $this->morphMany(QuizAttempt::class, 'participant');
    }
}
