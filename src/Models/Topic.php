<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Topic extends Model
{
    use HasFactory, Filterable, AsSource, Attachable;

    protected $guarded = ['id'];

    protected $appends = ['img', 'translation', 'question_count', 'attempt_count', 'is_new'];

    public function children()
    {
      return $this->hasMany(Topic::class, 'parent_id');
    }

    public function parent()
    {
      return $this->belongsTo(Topic::class, 'parent_id', 'id');
    }

    public function questions()
    {
      return $this->morphedByMany(Question::class, 'topicable');
    }

    public function quizzes()
    {
      return $this->morphedByMany(Quiz::class, 'topicable')->orderBy('order', 'desc');
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

      $locales = config('app.available_locales');

      $keys = ['topic', 'description'];

      foreach($locales as $title => $locale){
        $translations = $this->exists ? $this->translations()->where('language_key', $locale)->select('key', 'value')->get() : collect([]);
        $translations = $translations->mapWithKeys(function ($item) {
          return [$item['key'] => $item['value']];
        });
        foreach($keys as $key){
          $trans[$locale] = $translations->toArray();
        }

        // for english
        foreach($keys as $key){
          $trans['en'][$key] = $this->attributes[$key];
        }

        // check if empty result add english to them
        foreach($trans as $tran => $val){
          foreach($keys as $key){
            if(!isset($val[$key])){
              $trans[$tran][$key] = $this->attributes[$key];
            }
          }
        }
      }
      return $trans;
    }

    public function getImgAttribute()
    {
      if ($this->attachment('topcimg_'.app()->getLocale())->first()) {
        return $this->attachment('topcimg_'.app()->getLocale())->first()->url;
      }
  
      return "";
    }

    public function getQuestionCountAttribute(){
      return $this->quizzes()->count();
    }

    public function getAttemptCountAttribute(){
      return count(array_filter($this->quizzes()->get()->pluck('is_attempted')->toArray()));
    }

    public function getIsNewAttribute($value){
      if(is_null($value)){
        return false;
      }

      return $value;
    }
}
