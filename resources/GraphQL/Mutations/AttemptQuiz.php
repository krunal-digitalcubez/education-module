<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GenericException;
use App\Listeners\SendRequestCreatedNotification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\Request;
use App\Models\Worker;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AttemptQuiz
{
  /**
   * @param  null  $_
   * @param  array<string, mixed>  $args
   */
  public function __invoke($_, array $args)
  {

    try {
      $quiz_attempt_id = $args['input']['quiz_attempt_id'];
      $questionAnswers = $args['input']['question_answers'];
      // logger([$questionAnswers]);

      if(!Auth::guard('worker')->check()){
        return [
          'status'  => 'fail',
          'message' => __('messages.unauthenticated'),
        ];
      }

      $quizAttemptExists = QuizAttempt::where('id', $quiz_attempt_id)->exists();

      if(!$quizAttemptExists){
        return [
          'status'  => 'fail',
          'message' => __('Quiz Attempt not found, please create new quiz attempt'),
        ];
      }

      if (Auth::guard('worker')->check()) {
        return DB::transaction(function () use ($questionAnswers, $quiz_attempt_id) {
          if(count($questionAnswers) >= 0){

            $attemptExist = QuizAttemptAnswer::where('quiz_attempt_id', $quiz_attempt_id)->exists();
            if($attemptExist){
              QuizAttemptAnswer::where('quiz_attempt_id', $quiz_attempt_id)->delete();
            }

            foreach($questionAnswers as $k => $v){
              $que = $v->que;
              $opts = explode(',', (string) $v->opts);
              
              if(count($opts) >= 1){
                foreach($opts as $opt){
                  $newAnswer = new QuizAttemptAnswer;
                  $newAnswer->quiz_attempt_id = $quiz_attempt_id;
                  $newAnswer->quiz_question_id = $que;
                  $newAnswer->question_option_id = $opt;
                  $newAnswer->save();
                }
              }
            }

            $quizAttempt = QuizAttempt::find($quiz_attempt_id);
            $quiz = $quizAttempt->quiz;
            $score = $quizAttempt->calculate_score();
            $scoreOutOf = $quiz->total_marks;
            $questionsAttempted = QuizAttemptAnswer::where('quiz_attempt_id', $quiz_attempt_id)->get();
            $questionsAttempted= $questionsAttempted->groupBy('quiz_question_id')->count();
            $correctAnswers = $quizAttempt->correct_count();
            $incorrectAnswers = $quiz->questions()->count() - $correctAnswers;

            return [
              'status'  => 'success',
              'message' => __('Quiz answers submitted succesfully'),
              'score' => $score,
              'scoreOutOf' => $scoreOutOf,
              'questionsAttempted' => $questionsAttempted,
              'correctAnswers' => $correctAnswers,
              'incorrectAnswers' => $incorrectAnswers,
              'quiz' => $quizAttempt->quiz
            ];
          }
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
