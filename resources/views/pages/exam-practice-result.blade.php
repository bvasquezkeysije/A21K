<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h4 mb-1">Resultado del repaso</h1>
                <p class="text-muted mb-0">{{ $exam->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.forms') }}" class="btn btn-outline-secondary">Volver a Examenes</a>
                <a href="{{ route('portal.ai.exams.practice.download', ['exam' => $exam, 'attempt' => $attempt]) }}" class="btn btn-outline-success">
                    Descargar Excel
                </a>
                <form method="POST" action="{{ route('portal.ai.exams.practice.start', $exam) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Reintentar</button>
                </form>
                @if ($failedQuestionsCount > 0)
                    <form method="POST" action="{{ route('portal.ai.exams.practice.retry-incorrect', ['exam' => $exam, 'attempt' => $attempt]) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">Repetir falladas</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Puntaje obtenido</div>
                        <div class="h5 mb-0">{{ $attempt->scored_points }} / {{ $attempt->total_points }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Preguntas correctas</div>
                        <div class="h5 mb-0">{{ $attempt->correct_count }} / {{ $attempt->total_questions }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Respondidas</div>
                        <div class="h5 mb-0">{{ $attempt->answered_count }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">No respondio</div>
                        <div class="h5 mb-0">{{ $attempt->unanswered_count }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Pregunta</th>
                                <th>Estado</th>
                                <th>Tu respuesta</th>
                                <th>Correcta</th>
                                <th>Cronometro</th>
                                <th>Temporizador</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questionsForAttempt as $index => $question)
                                @php
                                    $answer = $answersByQuestion->get($question->id);
                                    $isUnanswered = ! $answer || $answer->is_unanswered;
                                    $isCorrect = $answer && $answer->is_correct === true;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $question->question_text }}</td>
                                    <td>
                                        @if ($isUnanswered)
                                            <span class="badge text-bg-secondary">No respondio</span>
                                        @elseif ($isCorrect)
                                            <span class="badge text-bg-success">Correcta</span>
                                        @else
                                            <span class="badge text-bg-danger">Incorrecta</span>
                                        @endif
                                    </td>
                                    <td>{{ $answer?->selected_answer ?? '-' }}</td>
                                    <td>{{ $question->correct_answer }}</td>
                                    <td>{{ $answer?->cronometro_segundos ?? $answer?->time_spent_seconds ?? 0 }}s</td>
                                    <td>{{ (int) ($question->temporizador_segundos ?? $question->time_limit) }}s</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
