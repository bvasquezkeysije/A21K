<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AiController extends Controller
{
    private const REQUIRED_EXCEL_HEADERS = [
        'pregunta',
        'tipo',
        'opcion_a',
        'opcion_b',
        'opcion_c',
        'opcion_d',
        'respuesta_correcta',
        'explicacion',
        'puntaje',
    ];

    private const OPTIONAL_EXCEL_HEADERS = [
        'temporizador_segundos',
        'tiempo_segundos',
        'cronometro_segundos',
        'temporizador',
    ];

    private const EXAMS_PER_PAGE_OPTIONS = [20, 30, 40, 50];

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('pages.ia', [
            'chats' => AiChat::query()
                ->where('user_id', $user->id)
                ->withCount('messages')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function examsIndex(Request $request): View
    {
        $user = $request->user();
        $activeManualExam = null;
        $manualExamId = (int) $request->query('manual_exam', 0);
        $searchQuery = trim((string) $request->query('q', ''));
        $fromDate = trim((string) $request->query('from_date', ''));
        $toDate = trim((string) $request->query('to_date', ''));
        $perPage = (int) $request->query('per_page', self::EXAMS_PER_PAGE_OPTIONS[0]);

        if (! in_array($perPage, self::EXAMS_PER_PAGE_OPTIONS, true)) {
            $perPage = self::EXAMS_PER_PAGE_OPTIONS[0];
        }

        if ($manualExamId > 0) {
            $activeManualExam = Exam::query()
                ->where('user_id', $user->id)
                ->whereKey($manualExamId)
                ->with([
                    'questions' => fn ($query) => $query
                        ->withCount('options')
                        ->latest('id')
                        ->limit(8),
                ])
                ->first();
        }

        $examsQuery = Exam::query()
            ->where('user_id', $user->id)
            ->withCount(['questions', 'attempts']);

        if ($searchQuery !== '') {
            $searchToken = strtolower($searchQuery);
            $examsQuery->whereRaw('LOWER(name) LIKE ?', ['%'.$searchToken.'%']);
        }

        if ($fromDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) === 1) {
            $examsQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate) === 1) {
            $examsQuery->whereDate('created_at', '<=', $toDate);
        }

        $exams = $examsQuery
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $openAttemptExamIds = [];
        if ($exams->count() > 0) {
            $openAttemptExamIds = ExamAttempt::query()
                ->where('user_id', $user->id)
                ->whereNull('finished_at')
                ->whereHas('answers')
                ->whereIn('exam_id', $exams->pluck('id')->all())
                ->pluck('exam_id')
                ->map(static fn ($examId): int => (int) $examId)
                ->unique()
                ->values()
                ->all();
        }

        return view('pages.formularios', [
            'exams' => $exams,
            'activeManualExam' => $activeManualExam,
            'openAttemptExamIds' => $openAttemptExamIds,
            'searchQuery' => $searchQuery,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'perPage' => $perPage,
            'perPageOptions' => self::EXAMS_PER_PAGE_OPTIONS,
            'showFilters' => $fromDate !== '' || $toDate !== '',
        ]);
    }

    public function storeManualExam(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('manualExam', [
            'manual_exam_name' => ['required', 'string', 'max:120'],
        ]);

        $exam = Exam::create([
            'user_id' => $request->user()->id,
            'name' => trim($validated['manual_exam_name']),
            'source_file_path' => 'manual://'.Str::uuid(),
            'questions_count' => 0,
        ]);

        return redirect()
            ->route('portal.forms', ['manual_exam' => $exam->id])
            ->with('message', 'Examen manual creado. Agrega preguntas una por una.');
    }

    public function updateManualExamName(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureExamOwnership($request, $exam);

        $validated = $request->validateWithBag('manualExamRename', [
            'exam_name' => ['required', 'string', 'max:120'],
            'rename_exam_id' => ['nullable', 'integer'],
        ]);

        $exam->update([
            'name' => trim($validated['exam_name']),
        ]);

        $queryParams = array_filter([
            'q' => trim((string) $request->query('q', '')),
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date' => trim((string) $request->query('to_date', '')),
            'per_page' => (int) $request->query('per_page', self::EXAMS_PER_PAGE_OPTIONS[0]),
            'page' => (int) $request->query('page', 1) > 1 ? (int) $request->query('page') : null,
            'manual_exam' => (int) $request->query('manual_exam', 0),
        ], static fn ($value) => $value !== '' && $value !== 0 && $value !== null);

        return redirect()
            ->route('portal.forms', $queryParams)
            ->with('message', 'Nombre del examen actualizado.');
    }

    public function showManualExamBuilder(Request $request, Exam $exam): View
    {
        $this->ensureExamOwnership($request, $exam);

        $exam->load([
            'questions' => fn ($query) => $query
                ->withCount('options')
                ->orderBy('id'),
        ]);

        $realQuestionCount = $exam->questions->count();

        if ((int) $exam->questions_count !== $realQuestionCount) {
            $exam->update([
                'questions_count' => $realQuestionCount,
            ]);
            $exam->refresh();
            $exam->load([
                'questions' => fn ($query) => $query
                    ->withCount('options')
                    ->orderBy('id'),
            ]);
        }

        return view('pages.exam-manual-builder', [
            'exam' => $exam,
        ]);
    }

    public function storeManualExamQuestion(Request $request, Exam $exam): RedirectResponse|JsonResponse
    {
        $this->ensureExamOwnership($request, $exam);

        $validated = $request->validateWithBag('manualQuestion', [
            'question_text' => ['required', 'string', 'max:5000'],
            'question_type' => ['required', 'in:multiple_choice,written'],
            'correct_answer' => ['nullable', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'points' => ['required', 'integer', 'min:1', 'max:1000'],
            'temporizador_segundos' => ['required', 'integer', 'min:1', 'max:86400'],
            'timer_enabled' => ['nullable', 'boolean'],
            'option_a' => ['nullable', 'string', 'max:1000'],
            'option_b' => ['nullable', 'string', 'max:1000'],
            'option_c' => ['nullable', 'string', 'max:1000'],
            'option_d' => ['nullable', 'string', 'max:1000'],
            'correct_option' => ['nullable', 'in:a,b,c,d'],
        ]);

        $questionType = $validated['question_type'];
        $optionsByKey = collect(['a', 'b', 'c', 'd'])
            ->mapWithKeys(fn (string $key): array => [$key => trim((string) ($validated["option_{$key}"] ?? ''))])
            ->filter(fn (string $optionText): bool => $optionText !== '')
            ->all();

        $correctOption = $validated['correct_option'] ?? null;
        $correctAnswer = trim((string) ($validated['correct_answer'] ?? ''));

        if ($questionType === 'multiple_choice') {
            if (count($optionsByKey) < 2) {
                throw ValidationException::withMessages([
                    'option_a' => 'Debes ingresar al menos 2 opciones para preguntas de seleccion.',
                ])->errorBag('manualQuestion');
            }

            if ($correctOption === null || ! array_key_exists($correctOption, $optionsByKey)) {
                throw ValidationException::withMessages([
                    'correct_option' => 'Selecciona una opcion correcta que exista en las opciones ingresadas.',
                ])->errorBag('manualQuestion');
            }

            $correctAnswer = $optionsByKey[$correctOption];
        } elseif ($correctAnswer === '') {
            throw ValidationException::withMessages([
                'correct_answer' => 'La respuesta correcta es obligatoria para preguntas escritas.',
            ])->errorBag('manualQuestion');
        }

        $createdQuestion = null;
        $updatedQuestionsCount = 0;

        DB::transaction(function () use ($exam, $validated, $questionType, $correctAnswer, $optionsByKey, $correctOption, &$createdQuestion, &$updatedQuestionsCount): void {
            $createdQuestion = Question::create([
                'exam_id' => $exam->id,
                'question_text' => trim($validated['question_text']),
                'question_type' => $questionType,
                'correct_answer' => $correctAnswer,
                'explanation' => trim((string) ($validated['explanation'] ?? '')) !== '' ? trim((string) $validated['explanation']) : null,
                'points' => (int) $validated['points'],
                'time_limit' => (int) $validated['temporizador_segundos'],
                'temporizador_segundos' => (int) $validated['temporizador_segundos'],
                'timer_enabled' => (bool) ($validated['timer_enabled'] ?? false),
            ]);

            if ($questionType === 'multiple_choice') {
                foreach ($optionsByKey as $key => $optionText) {
                    Option::create([
                        'question_id' => $createdQuestion->id,
                        'option_text' => $optionText,
                        'is_correct' => $key === $correctOption,
                    ]);
                }
            }

            $updatedQuestionsCount = $exam->questions()->count();
            $exam->update([
                'questions_count' => $updatedQuestionsCount,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pregunta agregada correctamente.',
                'questions_count' => $updatedQuestionsCount,
                'question' => [
                    'id' => $createdQuestion?->id,
                    'question_text' => $createdQuestion?->question_text,
                    'question_type' => $createdQuestion?->question_type,
                    'question_type_label' => $createdQuestion?->question_type === 'multiple_choice' ? 'Seleccion' : 'Escrita',
                    'points' => (int) ($createdQuestion?->points ?? 0),
                    'temporizador_segundos' => (int) ($createdQuestion?->temporizador_segundos ?? $createdQuestion?->time_limit ?? 0),
                ],
            ], 201);
        }

        return redirect()
            ->route('portal.forms', ['manual_exam' => $exam->id])
            ->with('message', 'Pregunta agregada correctamente.');
    }

    public function storeChat(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('chat', [
            'first_message' => ['required', 'string', 'max:4000'],
            'chat_attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $firstMessage = trim($validated['first_message']);
        $chatName = (string) Str::of($firstMessage)->limit(90, '...');
        $attachmentLabel = null;

        if ($request->hasFile('chat_attachment')) {
            $attachmentFile = $request->file('chat_attachment');
            $attachmentFile->store('chat_uploads');
            $attachmentLabel = $attachmentFile->getClientOriginalName();
        }

        $chat = AiChat::create([
            'user_id' => $request->user()->id,
            'name' => $chatName,
        ]);

        $messageContent = $firstMessage;

        if ($attachmentLabel !== null) {
            $messageContent .= "\n\n[Archivo adjunto: {$attachmentLabel}]";
        }

        $chat->messages()->create([
            'role' => 'user',
            'content' => $messageContent,
        ]);

        $chat->messages()->create([
            'role' => 'assistant',
            'content' => 'Chat creado. Puedes comenzar la conversacion cuando quieras.',
        ]);

        return redirect()
            ->route('portal.ai.chats.show', $chat)
            ->with('message', 'Nuevo chat IA creado correctamente.');
    }

    public function showChat(Request $request, AiChat $chat): View
    {
        $this->ensureChatOwnership($request, $chat);

        $chat->load([
            'messages' => fn ($query) => $query->orderBy('id'),
        ]);

        return view('pages.ia-chat', [
            'chat' => $chat,
        ]);
    }

    public function storeMessage(Request $request, AiChat $chat): RedirectResponse
    {
        $this->ensureChatOwnership($request, $chat);

        $validated = $request->validateWithBag('message', [
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $chat->messages()->create([
            'role' => 'user',
            'content' => trim($validated['message']),
        ]);

        $chat->messages()->create([
            'role' => 'assistant',
            'content' => 'Mensaje recibido. Aqui se conectara el motor de IA para responder.',
        ]);

        return redirect()
            ->route('portal.ai.chats.show', $chat)
            ->with('message', 'Mensaje enviado.');
    }

    public function storeExam(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('exam', [
            'exam_name' => ['required', 'string', 'max:120'],
            'exam_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $storedPath = $validated['exam_file']->store('exam_uploads');
        $importedCount = 0;

        try {
            DB::transaction(function () use ($request, $validated, $storedPath, &$importedCount): void {
                $exam = Exam::create([
                    'user_id' => $request->user()->id,
                    'name' => trim($validated['exam_name']),
                    'source_file_path' => $storedPath,
                ]);

                $spreadsheet = IOFactory::load(Storage::path($storedPath));
                $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

                if (count($rows) === 0) {
                    throw $this->examValidationException([
                        'exam_file' => 'El archivo Excel no contiene datos.',
                    ]);
                }

                $headerMap = $this->buildHeaderMap($rows[0]);
                $dataRows = array_slice($rows, 1);

                foreach ($dataRows as $offset => $row) {
                    $excelRow = $offset + 2;
                    $questionText = trim((string) ($row[$headerMap['pregunta']] ?? ''));

                    if ($questionText === '') {
                        continue;
                    }

                    $questionType = $this->mapQuestionType((string) ($row[$headerMap['tipo']] ?? ''));

                    if ($questionType === null) {
                        throw $this->examValidationException([
                            'exam_file' => "Fila {$excelRow}: el campo tipo debe ser 'seleccion' o 'escrita'.",
                        ]);
                    }

                    $correctAnswerRaw = trim((string) ($row[$headerMap['respuesta_correcta']] ?? ''));
                    $explanation = trim((string) ($row[$headerMap['explicacion']] ?? ''));
                    $points = (int) ($row[$headerMap['puntaje']] ?? 0);
                    $timeLimit = $this->extractTemporizadorSeconds($row, $headerMap, $excelRow);
                    $timerEnabled = $this->extractTimerEnabled($row, $headerMap, $excelRow);
                    $options = [];
                    $correctAnswer = $correctAnswerRaw;

                    if ($points <= 0) {
                        throw $this->examValidationException([
                            'exam_file' => "Fila {$excelRow}: puntaje debe ser mayor a 0.",
                        ]);
                    }

                    if ($timeLimit <= 0) {
                        throw $this->examValidationException([
                            'exam_file' => "Fila {$excelRow}: temporizador_segundos debe ser mayor a 0.",
                        ]);
                    }

                    $question = Question::create([
                        'exam_id' => $exam->id,
                        'question_text' => $questionText,
                        'question_type' => $questionType,
                        'correct_answer' => $correctAnswer,
                        'explanation' => $explanation !== '' ? $explanation : null,
                        'points' => $points,
                        'time_limit' => $timeLimit,
                        'temporizador_segundos' => $timeLimit,
                        'timer_enabled' => $timerEnabled,
                    ]);

                    if ($questionType === 'multiple_choice') {
                        $options = $this->extractMultipleChoiceOptions($row, $headerMap);

                        if (count($options) < 2) {
                            throw $this->examValidationException([
                                'exam_file' => "Fila {$excelRow}: se requieren al menos 2 opciones para tipo seleccion.",
                            ]);
                        }

                        $correctAnswer = $this->resolveCorrectAnswerForMultipleChoice(
                            $correctAnswerRaw,
                            $options,
                            $excelRow,
                        );

                        $question->update([
                            'correct_answer' => $correctAnswer,
                        ]);

                        $hasCorrectOption = false;

                        foreach ($options as $optionText) {
                            $isCorrect = $this->isCorrectAnswer($optionText, $correctAnswer);
                            $hasCorrectOption = $hasCorrectOption || $isCorrect;

                            Option::create([
                                'question_id' => $question->id,
                                'option_text' => $optionText,
                                'is_correct' => $isCorrect,
                            ]);
                        }

                        if (! $hasCorrectOption) {
                            throw $this->examValidationException([
                                'exam_file' => "Fila {$excelRow}: respuesta_correcta no coincide con opcion_a/b/c/d.",
                            ]);
                        }
                    } elseif ($correctAnswer === '') {
                        throw $this->examValidationException([
                            'exam_file' => "Fila {$excelRow}: respuesta_correcta es obligatoria.",
                        ]);
                    }

                    $importedCount++;
                }

                if ($importedCount === 0) {
                    throw $this->examValidationException([
                        'exam_file' => 'No se encontraron preguntas validas para importar.',
                    ]);
                }

                $exam->update([
                    'questions_count' => $importedCount,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::delete($storedPath);

            throw $exception;
        }

        return redirect()
            ->route('portal.forms')
            ->with('message', "Examen cargado correctamente con {$importedCount} preguntas.");
    }

    public function destroyExam(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureExamOwnership($request, $exam);

        $examName = $exam->name;
        $examId = (int) $exam->id;
        $exam->delete();

        $queryParams = array_filter([
            'q' => trim((string) $request->query('q', '')),
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date' => trim((string) $request->query('to_date', '')),
            'per_page' => (int) $request->query('per_page', self::EXAMS_PER_PAGE_OPTIONS[0]),
            'manual_exam' => (int) $request->query('manual_exam', 0),
        ], static fn ($value) => $value !== '' && $value !== 0);

        if (($queryParams['manual_exam'] ?? null) === $examId) {
            unset($queryParams['manual_exam']);
        }

        return redirect()
            ->route('portal.forms', $queryParams)
            ->with('message', "Examen '{$examName}' inactivado correctamente (eliminacion logica).");
    }

    public function downloadExamFormat(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $templateHeaders = [
            'pregunta',
            'tipo',
            'opcion_a',
            'opcion_b',
            'opcion_c',
            'opcion_d',
            'respuesta_correcta',
            'explicacion',
            'puntaje',
            'temporizador_segundos',
            'cronometro_segundos',
            'temporizador',
        ];

        $sheet->fromArray([
            $templateHeaders,
            [
                'Capital de Peru?',
                'seleccion',
                'Lima',
                'Cusco',
                'Piura',
                'Arequipa',
                'Lima',
                'Lima es la capital del Peru',
                5,
                30,
                0,
                'si',
            ],
            [
                'Define algoritmo',
                'escrita',
                '',
                '',
                '',
                '',
                'Conjunto de pasos para resolver un problema',
                'Un algoritmo es una serie de pasos ordenados',
                10,
                120,
                0,
                'si',
            ],
        ], null, 'A1');

        foreach (range(1, count($templateHeaders)) as $columnIndex) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            try {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, 'formato_examen_a21k.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function startExamPractice(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureExamOwnership($request, $exam);

        $totalQuestions = $exam->questions()->count();
        $restartRequested = $request->boolean('restart');

        if ($totalQuestions === 0) {
            return redirect()
                ->route('portal.forms')
                ->with('error', 'Este examen no tiene preguntas para repasar.');
        }

        $openAttempts = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('finished_at')
            ->withCount('answers')
            ->latest('id')
            ->get();

        if ($restartRequested) {
            if ($openAttempts->isNotEmpty()) {
                foreach ($openAttempts as $openAttempt) {
                    if ((int) ($openAttempt->answers_count ?? 0) > 0) {
                        $this->finalizeAttemptMetrics($openAttempt->fresh(['exam.questions', 'answers']));
                        continue;
                    }

                    $openAttempt->delete();
                }
            }

            $queryParams = array_filter([
                'q' => trim((string) $request->query('q', '')),
                'from_date' => trim((string) $request->query('from_date', '')),
                'to_date' => trim((string) $request->query('to_date', '')),
                'per_page' => (int) $request->query('per_page', self::EXAMS_PER_PAGE_OPTIONS[0]),
                'page' => (int) $request->query('page', 1) > 1 ? (int) $request->query('page') : null,
            ], static fn ($value) => $value !== '' && $value !== 0 && $value !== null);

            return redirect()
                ->route('portal.forms', $queryParams)
                ->with('message', 'Repaso reiniciado. Ahora puedes iniciar desde 0.');
        }

        if ($openAttempts->isNotEmpty()) {
            $openAttempt = $openAttempts->first(
                static fn (ExamAttempt $attempt): bool => (int) ($attempt->answers_count ?? 0) > 0
            );

            if (! $openAttempt) {
                foreach ($openAttempts as $attemptWithoutProgress) {
                    if ((int) ($attemptWithoutProgress->answers_count ?? 0) === 0) {
                        $attemptWithoutProgress->delete();
                    }
                }
            }

            if ($openAttempt) {
                $nextPosition = $this->resolveNextPracticePosition($exam, $openAttempt);

                if ($nextPosition > $totalQuestions && $openAttempt !== null) {
                    $this->finalizeAttemptMetrics($openAttempt->fresh(['exam.questions', 'answers']));

                return redirect()->route('portal.ai.exams.practice.result', [
                    'exam' => $exam,
                    'attempt' => $openAttempt,
                ])->with('message', 'Repaso retomado. Ya estaba completo y se mostro el resultado.');
            }

            return redirect()->route('portal.ai.exams.practice.question', [
                'exam' => $exam,
                'attempt' => $openAttempt,
                'position' => $nextPosition,
            ])->with('message', 'Repaso retomado desde tu ultimo avance.');
            }
        }

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $request->user()->id,
            'total_questions' => $totalQuestions,
            'started_at' => now(),
        ]);

        return redirect()->route('portal.ai.exams.practice.question', [
            'exam' => $exam,
            'attempt' => $attempt,
            'position' => 1,
        ])->with('message', 'Nuevo repaso iniciado.');
    }

    public function showExamPracticeQuestion(Request $request, Exam $exam, ExamAttempt $attempt, int $position): View|RedirectResponse
    {
        $this->ensureAttemptOwnership($request, $exam, $attempt);

        $questions = $exam->questions()
            ->with('options')
            ->orderBy('id')
            ->get();

        $totalQuestions = $questions->count();

        if ($totalQuestions === 0) {
            return redirect()
                ->route('portal.forms')
                ->with('error', 'Este examen no tiene preguntas para repasar.');
        }

        if ($position < 1 || $position > $totalQuestions) {
            return redirect()->route('portal.ai.exams.practice.result', [
                'exam' => $exam,
                'attempt' => $attempt,
            ]);
        }

        $question = $questions[$position - 1];

        return view('pages.exam-practice-question', [
            'exam' => $exam,
            'attempt' => $attempt,
            'question' => $question,
            'position' => $position,
            'totalQuestions' => $totalQuestions,
            'nextPosition' => $position + 1,
            'existingAnswer' => $attempt->answers()
                ->where('question_id', $question->id)
                ->first(),
        ]);
    }

    public function submitExamPracticeQuestion(Request $request, Exam $exam, ExamAttempt $attempt, int $position): RedirectResponse
    {
        $this->ensureAttemptOwnership($request, $exam, $attempt);

        $validated = $request->validate([
            'answer' => ['nullable', 'string', 'max:5000'],
            'timed_out' => ['nullable', 'boolean'],
            'exit_after_save' => ['nullable', 'boolean'],
            'cronometro_segundos' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'time_spent_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ]);

        $questions = $exam->questions()->orderBy('id')->get();
        $totalQuestions = $questions->count();

        if ($position < 1 || $position > $totalQuestions) {
            return redirect()->route('portal.ai.exams.practice.result', [
                'exam' => $exam,
                'attempt' => $attempt,
            ]);
        }

        $question = $questions[$position - 1];
        $isTimedOut = (bool) ($validated['timed_out'] ?? false);
        $exitAfterSave = (bool) ($validated['exit_after_save'] ?? false);
        $answer = trim((string) ($validated['answer'] ?? ''));
        $elapsedSeconds = $validated['cronometro_segundos'] ?? $validated['time_spent_seconds'] ?? null;
        $isUnanswered = $isTimedOut || $answer === '';
        $isCorrect = null;
        $selectedAnswer = null;

        if (! $isUnanswered) {
            $selectedAnswer = $answer;
            $isCorrect = $this->isCorrectAnswer($answer, (string) $question->correct_answer);
        }

        $shouldPersistCurrentQuestion = $isTimedOut || $answer !== '' || ! $exitAfterSave;

        if ($shouldPersistCurrentQuestion) {
            ExamAttemptAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'selected_answer' => $selectedAnswer,
                    'is_correct' => $isCorrect,
                    'is_unanswered' => $isUnanswered,
                    'time_spent_seconds' => $elapsedSeconds,
                    'cronometro_segundos' => $elapsedSeconds,
                    'answered_at' => now(),
                ],
            );
        }

        if ($exitAfterSave) {
            return redirect()
                ->route('portal.forms')
                ->with('message', 'Progreso guardado. Puedes continuar el repaso cuando quieras.');
        }

        if ($position >= $totalQuestions) {
            $this->finalizeAttemptMetrics($attempt->fresh(['exam.questions', 'answers']));

            return redirect()->route('portal.ai.exams.practice.result', [
                'exam' => $exam,
                'attempt' => $attempt,
            ]);
        }

        return redirect()->route('portal.ai.exams.practice.question', [
            'exam' => $exam,
            'attempt' => $attempt,
            'position' => $position + 1,
        ]);
    }

    public function showExamPracticeResult(Request $request, Exam $exam, ExamAttempt $attempt): View
    {
        $this->ensureAttemptOwnership($request, $exam, $attempt);

        $attempt->loadMissing(['exam.questions', 'answers.question']);
        $this->finalizeAttemptMetrics($attempt);
        $attempt->refresh()->load(['exam.questions', 'answers.question']);

        return view('pages.exam-practice-result', [
            'exam' => $exam,
            'attempt' => $attempt,
            'answersByQuestion' => $attempt->answers->keyBy('question_id'),
        ]);
    }

    private function ensureChatOwnership(Request $request, AiChat $chat): void
    {
        abort_unless((int) $chat->user_id === (int) $request->user()->id, 403);
    }

    private function ensureExamOwnership(Request $request, Exam $exam): void
    {
        abort_unless((int) $exam->user_id === (int) $request->user()->id, 403);
    }

    private function ensureAttemptOwnership(Request $request, Exam $exam, ExamAttempt $attempt): void
    {
        abort_unless((int) $exam->id === (int) $attempt->exam_id, 404);
        abort_unless((int) $attempt->user_id === (int) $request->user()->id, 403);
    }

    private function resolveNextPracticePosition(Exam $exam, ExamAttempt $attempt): int
    {
        $questionIds = $exam->questions()
            ->orderBy('id')
            ->pluck('id')
            ->values();

        if ($questionIds->isEmpty()) {
            return 1;
        }

        $answeredLookup = [];
        foreach ($attempt->answers()->pluck('question_id') as $questionId) {
            $answeredLookup[(int) $questionId] = true;
        }

        foreach ($questionIds as $index => $questionId) {
            if (! isset($answeredLookup[(int) $questionId])) {
                return $index + 1;
            }
        }

        return $questionIds->count() + 1;
    }

    private function finalizeAttemptMetrics(ExamAttempt $attempt): void
    {
        $attempt->loadMissing(['exam.questions:id,exam_id,points', 'answers:id,exam_attempt_id,question_id,is_correct,is_unanswered']);

        $questionPoints = $attempt->exam->questions->pluck('points', 'id');
        $totalQuestions = $attempt->exam->questions->count();
        $totalPoints = (int) $attempt->exam->questions->sum('points');
        $correctAnswers = $attempt->answers->filter(fn (ExamAttemptAnswer $answer) => $answer->is_correct === true);
        $answeredCount = $attempt->answers->filter(fn (ExamAttemptAnswer $answer) => $answer->is_unanswered === false)->count();
        $correctCount = $correctAnswers->count();
        $unansweredCount = max($totalQuestions - $answeredCount, 0);

        $scoredPoints = (int) $correctAnswers->reduce(
            fn (int $carry, ExamAttemptAnswer $answer): int => $carry + (int) ($questionPoints[$answer->question_id] ?? 0),
            0,
        );

        $attempt->update([
            'total_questions' => $totalQuestions,
            'answered_count' => $answeredCount,
            'unanswered_count' => $unansweredCount,
            'correct_count' => $correctCount,
            'total_points' => $totalPoints,
            'scored_points' => $scoredPoints,
            'finished_at' => $attempt->finished_at ?? now(),
        ]);
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $headerMap = [];

        foreach ($headerRow as $index => $headerCell) {
            $normalized = $this->normalizeHeader((string) $headerCell);

            if ($normalized !== '' && ! array_key_exists($normalized, $headerMap)) {
                $headerMap[$normalized] = $index;
            }
        }

        $missingHeaders = array_values(array_diff(self::REQUIRED_EXCEL_HEADERS, array_keys($headerMap)));

        if ($missingHeaders !== []) {
            throw $this->examValidationException([
                'exam_file' => 'Faltan columnas en el Excel: '.implode(', ', $missingHeaders).'.',
            ]);
        }

        if (! array_key_exists('temporizador_segundos', $headerMap) && ! array_key_exists('tiempo_segundos', $headerMap)) {
            throw $this->examValidationException([
                'exam_file' => 'El Excel debe incluir la columna temporizador_segundos (o tiempo_segundos).',
            ]);
        }

        return $headerMap;
    }

    private function extractTemporizadorSeconds(array $row, array $headerMap, int $excelRow): int
    {
        $timeColumn = array_key_exists('temporizador_segundos', $headerMap)
            ? 'temporizador_segundos'
            : 'tiempo_segundos';

        $rawValue = trim((string) ($row[$headerMap[$timeColumn]] ?? ''));
        $value = (int) $rawValue;

        if ($value <= 0) {
            throw $this->examValidationException([
                'exam_file' => "Fila {$excelRow}: {$timeColumn} debe ser mayor a 0.",
            ]);
        }

        return $value;
    }

    private function extractTimerEnabled(array $row, array $headerMap, int $excelRow): bool
    {
        if (! array_key_exists('temporizador', $headerMap)) {
            return true;
        }

        $rawValue = trim((string) ($row[$headerMap['temporizador']] ?? ''));

        if ($rawValue === '') {
            return true;
        }

        $normalized = $this->normalizeHeader($rawValue);

        if (in_array($normalized, ['1', 'si', 's', 'yes', 'true', 'on', 'activo', 'activado'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'no', 'n', 'false', 'off', 'inactivo', 'desactivado'], true)) {
            return false;
        }

        throw $this->examValidationException([
            'exam_file' => "Fila {$excelRow}: temporizador debe ser si/no, true/false o 1/0.",
        ]);
    }

    private function normalizeHeader(string $value): string
    {
        return (string) Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    private function mapQuestionType(string $value): ?string
    {
        $normalized = $this->normalizeHeader($value);

        if (in_array($normalized, ['seleccion', 'multiple', 'multiple_choice', 'multiplechoice', 'opcion_multiple', 'opcionmultiple'], true)) {
            return 'multiple_choice';
        }

        if (in_array($normalized, ['escrita', 'written', 'abierta', 'texto'], true)) {
            return 'written';
        }

        return null;
    }

    private function extractMultipleChoiceOptions(array $row, array $headerMap): array
    {
        $options = [];

        foreach (['opcion_a', 'opcion_b', 'opcion_c', 'opcion_d'] as $columnName) {
            $optionText = trim((string) ($row[$headerMap[$columnName]] ?? ''));

            if ($optionText !== '') {
                $options[] = $optionText;
            }
        }

        return $options;
    }

    private function resolveCorrectAnswerForMultipleChoice(string $rawAnswer, array $options, int $excelRow): string
    {
        $answer = trim($rawAnswer);

        if ($answer === '') {
            throw $this->examValidationException([
                'exam_file' => "Fila {$excelRow}: respuesta_correcta es obligatoria para preguntas de seleccion.",
            ]);
        }

        $normalized = $this->normalizeHeader($answer);
        $letterMap = [
            'a' => 0,
            'b' => 1,
            'c' => 2,
            'd' => 3,
            '1' => 0,
            '2' => 1,
            '3' => 2,
            '4' => 3,
        ];

        if (array_key_exists($normalized, $letterMap)) {
            $index = $letterMap[$normalized];

            if (! array_key_exists($index, $options)) {
                throw $this->examValidationException([
                    'exam_file' => "Fila {$excelRow}: respuesta_correcta '{$answer}' no tiene una opcion valida asociada.",
                ]);
            }

            return $options[$index];
        }

        foreach ($options as $optionText) {
            if ($this->isCorrectAnswer($answer, $optionText)) {
                return $optionText;
            }
        }

        throw $this->examValidationException([
            'exam_file' => "Fila {$excelRow}: respuesta_correcta '{$answer}' no coincide con opcion_a/b/c/d.",
        ]);
    }

    private function examValidationException(array $messages): ValidationException
    {
        return ValidationException::withMessages($messages)->errorBag('exam');
    }

    private function isCorrectAnswer(string $givenAnswer, string $correctAnswer): bool
    {
        return $this->normalizeAnswerForComparison($givenAnswer) === $this->normalizeAnswerForComparison($correctAnswer);
    }

    private function normalizeAnswerForComparison(string $value): string
    {
        return (string) Str::of($value)
            ->ascii()
            ->lower()
            ->squish();
    }
}
