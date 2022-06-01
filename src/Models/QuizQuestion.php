<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class QuizQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['worker_answers', 'correct_answers', 'is_correct_option', 'is_question_attempted'];


    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function options()
    {
        return $this->belongsTo(QuestionOption::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    public function quiz_attempt_answers(){
      return $this->hasMany(QuizAttemptAnswer::class, 'quiz_question_id', 'id');
    }

    public function getCorrectAnswersAttribute(){
      if(!Auth::user()){
        return [];
      }
      return $this->question->correct_options()->pluck('id')->toArray();
    }

    public function getWorkerAnswersAttribute(){
      if(!Auth::user()){
        return [];
      }

      $tempArr = [];

      $quizQuestionExists = QuizQuestion::where('question_id', $this->question->id)->exists();
      if(!$quizQuestionExists){
        return [];
      }

      $quizQuestion = QuizQuestion::where('question_id', $this->question->id)->latest()->first();
      $quiz = $quizQuestion->quiz_id;

      $attemptExists = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->exists();
      if(!$attemptExists){
        return [];
      }

      $attempt = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->latest()->first();
      return $this->quiz_attempt_answers()->where('quiz_attempt_id', $attempt->id)->pluck('question_option_id')->toArray();
    }

    public function getIsCorrectOptionAttribute(){
      if(!Auth::user()){
        return false;
      }

      if($this->question->question_type_id == QuestionType::QUESTION_TYPE_SURVEY){
        return true;
      }
      
      $correctAnswers = $this->getCorrectAnswersAttribute();
      $workerAnswers =$this->getWorkerAnswersAttribute();

      sort($correctAnswers);
      sort($workerAnswers);

      // logger('options-answes', [$correctAnswers, $workerAnswers]);
      return $correctAnswers == $workerAnswers;
      // return $this->getCorrectAnswersAttribute() == $this->getWorkerAnswersAttribute();
      // return isArrayEqual($this->getCorrectAnswersAttribute(), $this->getWorkerAnswersAttribute()) ? true : false;
    }

    public function getIsQuestionAttemptedAttribute(){
      if(!Auth::user()){
        return false;
      }

      $quizQuestionExists = QuizQuestion::where('question_id', $this->question->id)->exists();
      if(!$quizQuestionExists){
        return false;
      }

      $quizQuestion = QuizQuestion::where('question_id', $this->question->id)->latest()->first();
      $quiz = $quizQuestion->quiz_id;

      $attemptExists = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->exists();
      if(!$attemptExists){
        return false;
      }
      $attempt = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->latest()->first();

      $questionAttempt = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('quiz_question_id', $this->id)->exists();
      return $questionAttempt;
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
    
}
