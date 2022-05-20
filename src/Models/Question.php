<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['worker_answers', 'correct_answers', 'is_correct_option', 'is_question_attempted', 'translation', 'question_type', 'locale_question', 'locale_correct_reason'];

    public function question_type()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function topics()
    {
        return $this->morphToMany(Topic::class, 'topicable');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function quiz_questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function correct_options(): Collection
    {
        return $this->options()->where('is_correct', 1)->get();
    }

    public function quiz_attempt_answers(){
      return $this->hasMany(QuizAttemptAnswer::class, 'quiz_question_id', 'id');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function getTranslationAttribute(){
      $translations = $this->exists ? $this->translations()->select('key', 'value')->get() : collect([]);
      $translations = $translations->mapWithKeys(function ($item) {
              return [$item['key'] => $item['value']];
      });
      return [
        'en' => [
          "question" => $this->question,
          "correct_reason" => $this->correct_reason,
        ],
        'th' => $translations->toArray(),
      ];
    }

    public function getCorrectAnswersAttribute(){
      if(!Auth::user()){
        return [];
      }
      return $this->correct_options()->pluck('id');
    }

    public function getWorkerAnswersAttribute(){
      if(!Auth::user()){
        return [];
      }

      $tempArr = [];

      $quizQuestionExists = QuizQuestion::where('question_id', $this->id)->exists();
      if(!$quizQuestionExists){
        return [];
      }

      $quizQuestion = QuizQuestion::where('question_id', $this->id)->latest()->first();
      $quiz = $quizQuestion->quiz_id;

      $attemptExists = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->exists();
      if(!$attemptExists){
        return [];
      }
      $attempt = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->latest()->first();
      return $this->quiz_attempt_answers()->where('quiz_attempt_id', $attempt->id)->pluck('question_option_id');
    }

    public function getIsCorrectOptionAttribute(){
      if(!Auth::user()){
        return false;
      }
      return $this->getCorrectAnswersAttribute() == $this->getWorkerAnswersAttribute();
      // return isArrayEqual($this->getCorrectAnswersAttribute(), $this->getWorkerAnswersAttribute()) ? true : false;
    }

    public function getIsQuestionAttemptedAttribute(){
      if(!Auth::user()){
        return false;
      }

      $quizQuestionExists = QuizQuestion::where('question_id', $this->id)->exists();
      if(!$quizQuestionExists){
        return false;
      }

      $quizQuestion = QuizQuestion::where('question_id', $this->id)->latest()->first();
      $quiz = $quizQuestion->quiz_id;

      $attemptExists = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->exists();
      if(!$attemptExists){
        return false;
      }
      $attempt = QuizAttempt::where('participant_id', Auth::user()->id)->where('quiz_id', $quiz)->latest()->first();

      $questionAttempt = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('quiz_question_id', $this->id)->exists();
      return $questionAttempt;
    }

    public function getQuestionTypeAttribute(){
      if($this->question_type_id == 1){
        return 'single';
      }
      if($this->question_type_id == 2){
        return 'multiple';
      }
      return 'single';
    }

    public function getLocaleQuestionAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['question'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'question')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'question')->first();
        return $translation->value;
      }

      return $this->attributes['question'];
    }
    
    public function getLocaleCorrectReasonAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['correct_reason'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'correct_reason')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'correct_reason')->first();
        return $translation->value;
      }

      return $this->attributes['correct_reason'];
    }

    public function setQuestionAttribute($value){
      if($value == null){
        $this->attributes['question'] = ' ';
      }
      else{
        $this->attributes['question'] = $value;
      }
    }

    public function scopeActive($query)
    {
      return $query->where('is_active', 1);
    }
}
