<?php

namespace Digitalcubez\EducationModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Illuminate\Support\Str;
use Orchid\Attachment\Attachable;

class QuizSlide extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $guarded = ['id'];

    protected $appends = ['translation', 'locale_long_desc'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
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
          "long_desc" => $this->long_desc,
        ],
        'th' => $translations->toArray(),
      ];
    }

    public function getLocaleLongDescAttribute($value){
      if(app()->getLocale() == 'en'){
        return $this->attributes['long_desc'];
      }

      $checkTranslationExists = $this->translations()->where('language_key', app()->getLocale())->where('key', 'long_desc')->exists();
      if($checkTranslationExists){
        $translation = $this->translations()->where('language_key', app()->getLocale())->where('key', 'long_desc')->first();
        return $translation->value;
      }

      return $this->attributes['long_desc'];
    }
}