<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'source_file_path',
        'questions_count',
        'practice_feedback_enabled',
        'practice_order_mode',
        'practice_repeat_until_correct',
    ];

    protected function casts(): array
    {
        return [
            'practice_feedback_enabled' => 'boolean',
            'practice_repeat_until_correct' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
