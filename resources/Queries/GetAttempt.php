<?php

namespace App\GraphQL\Queries;

use Digitalcubez\EducationModule\Models\Quiz;
use Digitalcubez\EducationModule\Models\QuizAttempt;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class getAttempt {
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
        $id    = isset($args["id"])  ? $args["id"] : null;
        $quizExists = QuizAttempt::where('id', $id)->where('participant_id', Auth::guard('worker')->user()->id)->exists();
        if(!$quizExists){
          $response = [];
          $response['quiz_attempt'] = NULL;
          $response['status'] = false;
          $response['message'] = 'Attempt does not exists';
        }

        $attempt = QuizAttempt::where('id', $id)->first();
        $response = [];
        $response['quiz_attempt'] = $attempt;
        $response['status'] = true;

        return $response;
    }
}
