<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Illuminate\Support\Str;
use Orchid\Attachment\Attachable;

class Quiz extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable, Attachable;

    protected $guarded = ['id'];

    protected $appends = ['img', 'is_attempted'];

    public function topics()
    {
        return $this->morphToMany(Topic::class, 'topicable');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function slides(){
      return $this->hasMany(QuizSlide::class);
    }

    public function quizAttempts(){
      return $this->hasMany(QuizAttempt::class);
    }

    // attributes
    public function setSlugAttribute($value){
        $this->attributes['slug'] = Str::slug($this->attributes['title'], '-');
    }

    public function getImgAttribute()
    {
      if ($this->attachment()->first()) {
        return $this->attachment()->first()->url;
      }
  
      return "";
    }

    public function getIsAttemptedAttribute(){
      if(!Auth::user()){
        return false;
      }
      $attempts = $this->quizAttempts()->where('participant_id', Auth::user()->id)->exists();
      return $attempts;
    }
}
