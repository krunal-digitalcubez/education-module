<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GenericException;
use Digitalcubez\EducationModule\Models\Quiz;
use Digitalcubez\EducationModule\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class StartQuiz
{
  /**
   * @param  null  $_
   * @param  array<string, mixed>  $args
   */
  public function __invoke($_, array $args)
  {

    try {
      $quiz_id = $args['quiz_id'];

      if(!Auth::guard('worker')->check()){
        return [
          'status'  => 'fail',
          'message' => __('messages.unauthenticated'),
        ];
      }

      $quizExists = Quiz::where('id', $quiz_id)->exists();

      if(!$quizExists){
        return [
          'status'  => 'fail',
          'message' => __('Quiz not found'),
        ];
      }
      
      $quiz = Quiz::find($quiz_id);
      // check if user has attempted this before and it is incomplete
      $attemptExists = QuizAttempt::where('quiz_id', $quiz_id)->where('participant_id', Auth::guard('worker')->user()->id)->exists();
      if($attemptExists){
        $isAttemptInComplete = QuizAttempt::where('quiz_id', $quiz_id)->where('participant_id', Auth::guard('worker')->user()->id)->latest()->first()->is_complete;
        if(!$isAttemptInComplete){
          return [
            'status'  => 'success',
            'message' => __('Incomplete quiz attempt exist'),
            'quiz' => $quiz
          ]; 
        }
      }

      $attemptsByWorker = QuizAttempt::where('quiz_id', $quiz_id)->where('participant_id', Auth::guard('worker')->user()->id)->count();

      if($attemptsByWorker >= 1 && $quiz->is_read_only){
        return [
          'status'  => 'success',
          'message' => __('Quiz attempt created'),
          'quiz' => $quiz
        ]; 
      }
      
      if($attemptsByWorker >= $quiz->max_attempts){
        return [
          'status'  => 'fail',
          'message' => __('Max attempts reached'),
        ]; 
      }

      if (Auth::guard('worker')->check()) {
        return DB::transaction(function () use ($quiz_id, $quiz) {
          $quiz_attempt = new QuizAttempt;
  
          $quiz_attempt->quiz_id = $quiz_id;
          $quiz_attempt->participant_id = Auth::guard('worker')->user()->id;
          $quiz_attempt->participant_type = get_class(Auth::guard('worker')->user());
          $quiz_attempt->save();

          return [
            'status'  => 'success',
            'message' => __('Quiz attempt created'),
            'quiz' => $quiz
          ]; 
        });
      } 

    } catch (GenericException $e) {
      return [
        'status'  => 'fail',
        'message' => __('messages.generic_error')
      ];
    }
  }
}
