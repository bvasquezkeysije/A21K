<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'correct_answer',
        'explanation',
        'points',
        'time_limit',
        'temporizador_segundos',
        'timer_enabled',
    ];

    protected function casts(): array
    {
        return [
            'temporizador_segundos' => 'integer',
            'timer_enabled' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }
}
