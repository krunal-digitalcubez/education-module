<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Builder;
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

    protected $appends = ['img', 'is_attempted', 'translation', 'locale_title', 'locale_long_description', 'locale_description'];

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
        return $this->hasMany(QuizAttempt::class)->where('participant_id', Auth::user()->id);
    }

    public function attempt()
    {
      return $this->hasOne(QuizAttempt::class)->where('participant_id', Auth::user()->id)->latest();
    }

    public function slides(){
      return $this->hasMany(QuizSlide::class);
    }

    public function quizAttempts(){
      return $this->hasMany(QuizAttempt::class)->where('participant_id', Auth::user()->id);
    }

    // attributes
    public function setSlugAttribute($value){
        $this->attributes['slug'] = Str::slug($this->attributes['title'], '-');
    }

    public function getImgAttribute()
    {
      if ($this->attachment('quizimg_'.app()->getLocale())->first()) {
        return $this->attachment('quizimg_'.app()->getLocale())->first()->url;
      }
  
      return "";
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function getIsAttemptedAttribute(){
      if(!Auth::user()){
        return false;
      }
      $attempts = $this->quizAttempts()->where('participant_id', Auth::user()->id)->exists();
      return $attempts;
    }

    public function getTranslationAttribute(){
      $translations = $this->exists ? $this->translations()->select('key', 'value')->get() : collect([]);
      $translations = $translations->mapWithKeys(function ($item) {
              return [$item['key'] => $item['value']];
      });
      return [
        'en' => [
          "title" => $this->title,
          "description" => $this->description,
          "long_description" => $this->long_description,
        ],
        'th' => $translations->toArray(),
      ];
    }

    public function setTitleAttribute($value){
      if($value == null){
        $this->attributes['title'] = ' ';
      }
      else{
        $this->attributes['title'] = $value;
      }
    }

    public function setDescriptionAttribute($value){
      if($value == null){
        $this->attributes['description'] = ' ';
      }
      else{
        $this->attributes['description'] = $value;
      }
    }

    public function setLongDescriptionAttribute($value){
      if($value == null){
        $this->attributes['long_description'] = ' ';
      }
      else{
        $this->attributes['long_description'] = $value;
      }
    }

    public function scopeActive($query)
    {
      return $query->where('is_published', 1);
    }
  
    public function getLocaleTitleAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['title'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'title')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'title')->first();
        return $translation->value;
      }

      return $this->attributes['title'];
    }

    public function getLocaleDescriptionAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['description'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'description')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'description')->first();
        return $translation->value;
      }

      return $this->attributes['description'];
    }

    public function getLocaleLongDescriptionAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['long_description'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'long_description')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'long_description')->first();
        return $translation->value;
      }

      return $this->attributes['long_description'];
    }
}
