<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class QuizAttempt extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = [
      'score',
      'scoreOutOf',
      'questionsAttempted',
      'correctAnswers',
      'incorrectAnswers',
      'worker_answers'
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participant()
    {
        return $this->morphTo(__FUNCTION__, 'participant_type', 'participant_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    public function calculate_score(): float
    {
        $score = 0;
        $quiz_questions_collection = $this->quiz->questions()->with('question')->get();
        $quiz_questions = [];
        $quiz_attempt_answers = [];
        foreach ($quiz_questions_collection as $key => $quiz_question) {
            $question = $quiz_question->question;
            $correct_answer = null;
            if ($question->question_type_id == QuestionType::QUESTION_TYPE_MCSA) {
                $correct_answer =  ($question->correct_options())->first()->id;
            } elseif ($question->question_type_id == QuestionType::QUESTION_TYPE_MCMA) {
                $correct_answer =  ($question->correct_options())->pluck('id');
            } elseif ($question->question_type_id == QuestionType::QUESTION_TYPE_FILL) {
                $correct_answer = ($question->correct_options())->first()->option;
            } else {
                $correct_answer = null;
            }
            $quiz_questions[$quiz_question->id] = [
                'question_type_id' => $question->question_type_id,
                'is_optional' => $quiz_question->is_optional,
                'marks' => $quiz_question->marks,
                'negative_marks' => $quiz_question->negative_marks,
                'correct_answer' => $correct_answer
            ];
        }
        foreach ($this->answers as $key => $quiz_attempt_answer) {
            $quiz_attempt_answers[$quiz_attempt_answer->quiz_question_id][] = ['option_id' => $quiz_attempt_answer->question_option_id, 'answer' => $quiz_attempt_answer->answer];
        }
        foreach ($quiz_questions as $quiz_question_id => $quiz_question) {
            if ($quiz_question['question_type_id'] == QuestionType::QUESTION_TYPE_MCSA) {
              if (!empty($quiz_question['correct_answer'])) {
                if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        if ($quiz_attempt_answers[$quiz_question_id][0]['option_id'] == $quiz_question['correct_answer']) {
                            $score += $quiz_question['marks'];
                        } else {
                            $score -= $quiz_question['negative_marks'];
                        }
                    } else {
                        if (!$quiz_question['is_optional']) {
                            $score -= $quiz_question['negative_marks'];
                        }
                    }
                } else {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $score += $quiz_question['marks'];
                    }
                }

            } elseif ($quiz_question['question_type_id'] == QuestionType::QUESTION_TYPE_MCMA) {
              if (!empty($quiz_question['correct_answer'])) {
                if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $temp_arr = [];
                        foreach ($quiz_attempt_answers[$quiz_question_id] as $key => $answer) {
                            $temp_arr[] = $answer['option_id'];
                        }
                        // if ($quiz_question['correct_answer']->toArray() == $temp_arr) {
                        if (isArrayEqual($quiz_question['correct_answer']->toArray(), $temp_arr)) {
                            $score += $quiz_question['marks'];
                        } else {
                            $score -= $quiz_question['negative_marks'];
                        }
                    } else {
                        if (!$quiz_question['is_optional']) {
                            $score -= $quiz_question['negative_marks'];
                        }
                    }
                } else {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $score += $quiz_question['marks'];
                    }
                }
            } elseif ($quiz_question['question_type_id'] == QuestionType::QUESTION_TYPE_FILL) {
                if (!empty($quiz_question['correct_answer'])) {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        if ($quiz_question['correct_answer'] == $quiz_attempt_answers[$quiz_question_id][0]['answer']) {
                            $score += $quiz_question['marks'];
                        } else {
                            $score -= $quiz_question['negative_marks'];
                        }
                    } else {
                        if (!$quiz_question['is_optional']) {
                            $score -= $quiz_question['negative_marks'];
                        }
                    }
                } else {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $score += $quiz_question['marks'];
                    }
                }
            } else {
                $score += $quiz_question['marks'];
            }
        }
        return $score;
    }

    public function correct_count()
    {
        $score = 0;
        $quiz_questions_collection = $this->quiz->questions()->with('question')->get();
        $quiz_questions = [];
        $quiz_attempt_answers = [];
        foreach ($quiz_questions_collection as $key => $quiz_question) {
            $question = $quiz_question->question;
            $correct_answer = null;
            if ($question->question_type_id == QuestionType::QUESTION_TYPE_MCSA) {
                $correct_answer =  ($question->correct_options())->first()->id;
            } elseif ($question->question_type_id == QuestionType::QUESTION_TYPE_MCMA) {
                $correct_answer =  ($question->correct_options())->pluck('id');
            } elseif ($question->question_type_id == QuestionType::QUESTION_TYPE_FILL) {
                $correct_answer = ($question->correct_options())->first()->option;
            } else {
                $correct_answer = null;
            }
            $quiz_questions[$quiz_question->id] = [
                'question_type_id' => $question->question_type_id,
                'is_optional' => $quiz_question->is_optional,
                'marks' => 1,
                'negative_marks' => 0,
                'correct_answer' => $correct_answer
            ];
        }
        foreach ($this->answers as $key => $quiz_attempt_answer) {
            $quiz_attempt_answers[$quiz_attempt_answer->quiz_question_id][] = ['option_id' => $quiz_attempt_answer->question_option_id, 'answer' => $quiz_attempt_answer->answer];
        }
        foreach ($quiz_questions as $quiz_question_id => $quiz_question) {
            if ($quiz_question['question_type_id'] == QuestionType::QUESTION_TYPE_MCSA) {
              if (!empty($quiz_question['correct_answer'])) {
                if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        if ($quiz_attempt_answers[$quiz_question_id][0]['option_id'] == $quiz_question['correct_answer']) {
                            $score += $quiz_question['marks'];
                        } else {
                            $score -= $quiz_question['negative_marks'];
                        }
                    } else {
                        if (!$quiz_question['is_optional']) {
                            $score -= $quiz_question['negative_marks'];
                        }
                    }
                } else {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $score += $quiz_question['marks'];
                    }
                }

            } elseif ($quiz_question['question_type_id'] == QuestionType::QUESTION_TYPE_MCMA) {
              if (!empty($quiz_question['correct_answer'])) {
                if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $temp_arr = [];
                        foreach ($quiz_attempt_answers[$quiz_question_id] as $key => $answer) {
                            $temp_arr[] = $answer['option_id'];
                        }
                        // if ($quiz_question['correct_answer']->toArray() == $temp_arr) {
                        if (isArrayEqual($quiz_question['correct_answer']->toArray(), $temp_arr)) {
                            $score += $quiz_question['marks'];
                        } else {
                            $score -= $quiz_question['negative_marks'];
                        }
                    } else {
                        if (!$quiz_question['is_optional']) {
                            $score -= $quiz_question['negative_marks'];
                        }
                    }
                } else {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $score += $quiz_question['marks'];
                    }
                }
            } elseif ($quiz_question['question_type_id'] == QuestionType::QUESTION_TYPE_FILL) {
                if (!empty($quiz_question['correct_answer'])) {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        if ($quiz_question['correct_answer'] == $quiz_attempt_answers[$quiz_question_id][0]['answer']) {
                            $score += $quiz_question['marks'];
                        } else {
                            $score -= $quiz_question['negative_marks'];
                        }
                    } else {
                        if (!$quiz_question['is_optional']) {
                            $score -= $quiz_question['negative_marks'];
                        }
                    }
                } else {
                    if (isset($quiz_attempt_answers[$quiz_question_id])) {
                        $score += $quiz_question['marks'];
                    }
                }
            } else {
                $score += $quiz_question['marks'];
            }
        }
        return $score;
    }

    public function getScoreAttribute()
    {
      return $this->calculate_score();
    }

    public function getScoreOutOfAttribute()
    {
      return $this->quiz->exists() ? $this->quiz->total_marks : 0.0;
    }

    public function getQuestionsAttemptedAttribute()
    {
      return $this->quiz->exists() ? $this->quiz->total_marks : 0.0;
    }

    public function getCorrectAnswersAttribute()
    {
      return $this->correct_count();
    }

    public function getIncorrectAnswersAttribute()
    {
      return $this->quiz->questions()->count() - $this->correct_count();
    }

    public function getworkerAnswersAttribute(){
      if(!Auth::user()){
        return false;
      }

      $answers = $this->answers;
      $tempArr = [];
      if(count($answers) >= 1){
        foreach($answers as $answer){
          $arr = [];
          $arr['question_id'] = $answer->quiz_question_id;
          $arr['user_answers'] = QuizAttemptAnswer::where('quiz_attempt_id', $this->id)->where('quiz_question_id', $answer->quiz_question_id)->pluck('question_option_id');
          array_push($tempArr, $arr);
        }
      }
      return $tempArr;
    }
}
