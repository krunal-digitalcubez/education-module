<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GenericException;
use Digitalcubez\EducationModule\Models\QuizAttempt;
use Digitalcubez\EducationModule\Models\QuizAttemptAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class AttemptSingleQuestion
{
  /**
   * @param  null  $_
   * @param  array<string, mixed>  $args
   */
  public function __invoke($_, array $args)
  {

    try {
      $quiz_attempt_id = $args['input']['quiz_attempt_id'];
      $question_id = $args['input']['question_id'];
      $answers = $args['input']['answers'];
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
        return DB::transaction(function () use ($question_id, $quiz_attempt_id, $answers) {
          $answers = explode(',',$answers[0]->answers);
          if(count($answers) >= 0){

          // check if that question exists in attempted answers 
            $questionExists = QuizAttemptAnswer::where('quiz_attempt_id', $quiz_attempt_id)->where('quiz_question_id', $question_id)->exists();

            if($questionExists){
              $oldQuestion = QuizAttemptAnswer::where('quiz_attempt_id', $quiz_attempt_id)->where('quiz_question_id', $question_id)->delete();
            }
            foreach($answers as $answer){
              $newAnswer = new QuizAttemptAnswer;
              $newAnswer->quiz_attempt_id = $quiz_attempt_id;
              $newAnswer->quiz_question_id = $question_id;
              $newAnswer->question_option_id = $answer;
              $newAnswer->save();
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
