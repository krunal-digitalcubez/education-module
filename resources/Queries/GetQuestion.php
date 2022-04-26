<?php

namespace App\GraphQL\Queries;

use Digitalcubez\EducationModule\Models\Question;
use Digitalcubez\EducationModule\Models\Quiz;
use Digitalcubez\EducationModule\Models\QuizAttempt;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class getQuestion {
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
        $id    = isset($args["id"])  ? $args["id"] : null;
        $quizExists = Question::where('id', $id)->exists();
        if(!$quizExists){
          $response = [];
          $response['question'] = NULL;
          $response['status'] = false;
          $response['message'] = 'Question does not exists';
        }

        $question = Question::where('id', $id)->first();
        $response = [];
        $response['question'] = $question;
        $response['status'] = true;

        return $response;
    }
}
