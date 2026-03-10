<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
            <div>
                <h1 class="h4 mb-1">{{ $exam->name }}</h1>
                <p class="text-muted mb-0">Pregunta {{ $position }} de {{ $totalQuestions }}</p>
            </div>
            <a href="{{ route('portal.forms') }}" class="btn btn-outline-secondary">Salir sin guardar</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <span class="badge text-bg-light text-uppercase">{{ $question->question_type === 'multiple_choice' ? 'Seleccion' : 'Escrita' }}</span>
                    <span class="badge text-bg-info">
                        Cronometro: <span id="chronoValue">00:00</span>
                    </span>
                    @if ($question->timer_enabled)
                        <span class="badge text-bg-warning">
                            Temporizador: <span id="timerValue">{{ gmdate('i:s', (int) ($question->temporizador_segundos ?? $question->time_limit)) }}</span>
                        </span>
                    @else
                        <span class="badge text-bg-secondary">Temporizador desactivado</span>
                    @endif
                </div>

                <h2 class="h5 mb-2">{{ $question->question_text }}</h2>
                <p class="text-muted mb-4">
                    Puntaje: {{ $question->points }} | Tiempo maximo: {{ (int) ($question->temporizador_segundos ?? $question->time_limit) }} segundos
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    id="practice-question-form"
                    method="POST"
                    action="{{ route('portal.ai.exams.practice.answer', ['exam' => $exam, 'attempt' => $attempt, 'position' => $position]) }}"
                    data-timer-enabled="{{ $question->timer_enabled ? '1' : '0' }}"
                    data-time-limit="{{ (int) ($question->temporizador_segundos ?? $question->time_limit) }}"
                >
                    @csrf
                    <input type="hidden" id="timed_out" name="timed_out" value="0">
                    <input type="hidden" id="cronometro_segundos" name="cronometro_segundos" value="0">

                    @if ($question->question_type === 'multiple_choice')
                        <div class="d-grid gap-2 mb-4">
                            @foreach ($question->options as $option)
                                <label class="border rounded px-3 py-2 d-flex align-items-start gap-2">
                                    <input
                                        class="form-check-input mt-1"
                                        type="radio"
                                        name="answer"
                                        value="{{ $option->option_text }}"
                                        @checked(old('answer', $existingAnswer?->selected_answer) === $option->option_text)
                                    >
                                    <span>{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="mb-4">
                            <label for="answer" class="form-label">Tu respuesta</label>
                            <textarea id="answer" name="answer" rows="4" class="form-control">{{ old('answer', $existingAnswer?->selected_answer) }}</textarea>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <small class="text-muted">
                            Si el temporizador llega a 0, se pasa automaticamente y se marca como no respondio.
                        </small>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" name="exit_after_save" value="1" class="btn btn-outline-secondary">
                                Guardar y salir
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $position >= $totalQuestions ? 'Finalizar repaso' : 'Guardar y siguiente' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('practice-question-form');
                if (!form) return;

                const timerEnabled = form.dataset.timerEnabled === '1';
                const timeLimit = Number(form.dataset.timeLimit || 0);
                const timerValue = document.getElementById('timerValue');
                const chronoValue = document.getElementById('chronoValue');
                const timedOutInput = document.getElementById('timed_out');
                const timeSpentInput = document.getElementById('cronometro_segundos');
                const startedAt = Date.now();
                let submitted = false;
                let remaining = timeLimit;

                const formatSeconds = (seconds) => {
                    const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
                    const secs = String(seconds % 60).padStart(2, '0');
                    return `${mins}:${secs}`;
                };

                const syncElapsed = () => {
                    const elapsed = Math.max(Math.round((Date.now() - startedAt) / 1000), 0);
                    if (chronoValue) {
                        chronoValue.textContent = formatSeconds(elapsed);
                    }
                    if (timerEnabled && timeLimit > 0) {
                        timeSpentInput.value = Math.min(elapsed, timeLimit);
                    } else {
                        timeSpentInput.value = elapsed;
                    }
                };

                form.addEventListener('submit', () => {
                    if (submitted) return;
                    submitted = true;
                    syncElapsed();
                });

                if (!timerEnabled || timeLimit <= 0) {
                    if (timerValue) {
                        timerValue.textContent = 'Sin limite';
                    }
                    setInterval(() => {
                        if (submitted) return;
                        syncElapsed();
                    }, 1000);
                    return;
                }

                if (timerValue) {
                    timerValue.textContent = formatSeconds(remaining);
                }

                const interval = setInterval(() => {
                    if (submitted) {
                        clearInterval(interval);
                        return;
                    }

                    remaining -= 1;
                    syncElapsed();

                    if (timerValue) {
                        timerValue.textContent = formatSeconds(Math.max(remaining, 0));
                    }

                    if (remaining <= 0) {
                        clearInterval(interval);
                        timedOutInput.value = '1';
                        submitted = true;
                        form.submit();
                    }
                }, 1000);
            });
        </script>
    @endpush
</x-app-layout>
