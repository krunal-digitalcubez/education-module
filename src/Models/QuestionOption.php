<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['translation', 'th_option', 'locale_option'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function getThOptionAttribute(){
      return $this->translations()->exists() ? $this->translations()->latest()->first()->value: '';
    }

    public function getTranslationAttribute(){
      $translations = $this->exists ? $this->translations()->select('key', 'value')->get() : collect([]);
      $translations = $translations->mapWithKeys(function ($item) {
              return [$item['key'] => $item['value']];
      });
      return [
        'en' => [
          "option" => $this->option,
        ],
        'th' => $translations->toArray(),
      ];
    }

    public function getLocaleOptionAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['option'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'option')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'option')->first();
        return $translation->value;
      }

      return $this->attributes['option'];
    }
}
