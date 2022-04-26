<?php

namespace App\GraphQL\Queries;

use Digitalcubez\EducationModule\Models\Quiz;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class getQuiz {
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
        $id    = isset($args["id"])  ? $args["id"] : null;
        $quizExists = Quiz::where('id', $id)->exists();
        if(!$quizExists){
          $response = [];
          $response['quiz'] = NULL;
          $response['status'] = false;
          $response['message'] = 'Quiz does not exists';
        }

        $quiz = Quiz::where('id', $id)->first();
        $response = [];
        $response['quiz'] = $quiz;
        $response['status'] = true;

        return $response;
    }
}
