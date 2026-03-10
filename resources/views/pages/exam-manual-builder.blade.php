<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h4 mb-1">{{ $exam->name }}</h1>
                <p class="text-muted mb-0">Agrega preguntas manuales una por una.</p>
            </div>
            <a href="{{ route('portal.forms') }}" class="btn btn-outline-secondary">Volver a examenes</a>
        </div>

        @if (session('message'))
            <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h2 class="h6 mb-0">Nueva pregunta</h2>
                    </div>
                    <div class="card-body pt-3 px-4 pb-4">
                        @if ($errors->manualQuestion->any())
                            <div class="alert alert-danger">
                                {{ $errors->manualQuestion->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('portal.ai.exams.manual.questions.store', $exam) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="question_text" class="form-label">Pregunta</label>
                                <textarea
                                    id="question_text"
                                    name="question_text"
                                    rows="3"
                                    class="form-control @if($errors->manualQuestion->has('question_text')) is-invalid @endif"
                                    placeholder="Escribe aqui la pregunta..."
                                    required
                                >{{ old('question_text') }}</textarea>
                                @if ($errors->manualQuestion->has('question_text'))
                                    <div class="invalid-feedback">{{ $errors->manualQuestion->first('question_text') }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="question_type" class="form-label">Tipo de pregunta</label>
                                <select
                                    id="question_type"
                                    name="question_type"
                                    class="form-select @if($errors->manualQuestion->has('question_type')) is-invalid @endif"
                                    required
                                >
                                    <option value="multiple_choice" @selected(old('question_type', 'multiple_choice') === 'multiple_choice')>Seleccion multiple</option>
                                    <option value="written" @selected(old('question_type') === 'written')>Escrita</option>
                                </select>
                                @if ($errors->manualQuestion->has('question_type'))
                                    <div class="invalid-feedback">{{ $errors->manualQuestion->first('question_type') }}</div>
                                @endif
                            </div>

                            <div id="multiple_choice_fields">
                                <label class="form-label">Opciones</label>
                                <div class="mb-2">
                                    <input type="text" name="option_a" value="{{ old('option_a') }}" class="form-control @if($errors->manualQuestion->has('option_a')) is-invalid @endif" placeholder="Opcion A">
                                </div>
                                <div class="mb-2">
                                    <input type="text" name="option_b" value="{{ old('option_b') }}" class="form-control @if($errors->manualQuestion->has('option_b')) is-invalid @endif" placeholder="Opcion B">
                                </div>
                                <div class="mb-2">
                                    <input type="text" name="option_c" value="{{ old('option_c') }}" class="form-control @if($errors->manualQuestion->has('option_c')) is-invalid @endif" placeholder="Opcion C (opcional)">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="option_d" value="{{ old('option_d') }}" class="form-control @if($errors->manualQuestion->has('option_d')) is-invalid @endif" placeholder="Opcion D (opcional)">
                                </div>
                                @if ($errors->manualQuestion->has('option_a'))
                                    <div class="text-danger small mb-2">{{ $errors->manualQuestion->first('option_a') }}</div>
                                @endif

                                <div class="mb-3">
                                    <label for="correct_option" class="form-label">Opcion correcta</label>
                                    <select
                                        id="correct_option"
                                        name="correct_option"
                                        class="form-select @if($errors->manualQuestion->has('correct_option')) is-invalid @endif"
                                    >
                                        <option value="">Selecciona la correcta</option>
                                        <option value="a" @selected(old('correct_option') === 'a')>A</option>
                                        <option value="b" @selected(old('correct_option') === 'b')>B</option>
                                        <option value="c" @selected(old('correct_option') === 'c')>C</option>
                                        <option value="d" @selected(old('correct_option') === 'd')>D</option>
                                    </select>
                                    @if ($errors->manualQuestion->has('correct_option'))
                                        <div class="invalid-feedback">{{ $errors->manualQuestion->first('correct_option') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div id="written_fields" class="d-none">
                                <div class="mb-3">
                                    <label for="correct_answer" class="form-label">Respuesta correcta</label>
                                    <textarea
                                        id="correct_answer"
                                        name="correct_answer"
                                        rows="2"
                                        class="form-control @if($errors->manualQuestion->has('correct_answer')) is-invalid @endif"
                                        placeholder="Respuesta esperada..."
                                    >{{ old('correct_answer') }}</textarea>
                                    @if ($errors->manualQuestion->has('correct_answer'))
                                        <div class="invalid-feedback">{{ $errors->manualQuestion->first('correct_answer') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="explanation" class="form-label">Explicacion (opcional)</label>
                                <textarea
                                    id="explanation"
                                    name="explanation"
                                    rows="2"
                                    class="form-control @if($errors->manualQuestion->has('explanation')) is-invalid @endif"
                                    placeholder="Explicacion para repaso..."
                                >{{ old('explanation') }}</textarea>
                                @if ($errors->manualQuestion->has('explanation'))
                                    <div class="invalid-feedback">{{ $errors->manualQuestion->first('explanation') }}</div>
                                @endif
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="points" class="form-label">Puntaje</label>
                                    <input
                                        type="number"
                                        id="points"
                                        name="points"
                                        min="1"
                                        max="1000"
                                        value="{{ old('points', 1) }}"
                                        class="form-control @if($errors->manualQuestion->has('points')) is-invalid @endif"
                                        required
                                    >
                                    @if ($errors->manualQuestion->has('points'))
                                        <div class="invalid-feedback">{{ $errors->manualQuestion->first('points') }}</div>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label for="temporizador_segundos" class="form-label">Temporizador (segundos)</label>
                                    <input
                                        type="number"
                                        id="temporizador_segundos"
                                        name="temporizador_segundos"
                                        min="1"
                                        max="86400"
                                        value="{{ old('temporizador_segundos', 30) }}"
                                        class="form-control @if($errors->manualQuestion->has('temporizador_segundos')) is-invalid @endif"
                                        required
                                    >
                                    @if ($errors->manualQuestion->has('temporizador_segundos'))
                                        <div class="invalid-feedback">{{ $errors->manualQuestion->first('temporizador_segundos') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-check mt-3 mb-4">
                                <input type="hidden" name="timer_enabled" value="0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    value="1"
                                    id="timer_enabled"
                                    name="timer_enabled"
                                    @checked((string) old('timer_enabled', '1') === '1')
                                >
                                <label class="form-check-label" for="timer_enabled">
                                    Activar temporizador en esta pregunta
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Guardar pregunta</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0">Preguntas registradas</h2>
                        <span class="badge text-bg-primary">{{ $exam->questions_count }} total</span>
                    </div>
                    <div class="card-body pt-3 px-4 pb-4">
                        @forelse ($exam->questions as $index => $question)
                            <div class="rounded border p-3 mb-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <span class="fw-semibold">#{{ $index + 1 }}</span>
                                    <span class="badge text-bg-light text-uppercase">
                                        {{ $question->question_type === 'multiple_choice' ? 'Seleccion' : 'Escrita' }}
                                    </span>
                                </div>
                                <div class="fw-medium mb-1">{{ $question->question_text }}</div>
                                <small class="text-muted d-block">
                                    Puntaje: {{ $question->points }} | Temporizador: {{ (int) ($question->temporizador_segundos ?? $question->time_limit) }} s |
                                    Estado: {{ $question->timer_enabled ? 'Activo' : 'Inactivo' }}
                                </small>
                                <small class="text-muted d-block">
                                    @if ($question->question_type === 'multiple_choice')
                                        Opciones: {{ $question->options_count }} | Respuesta correcta: {{ $question->correct_answer }}
                                    @else
                                        Respuesta esperada: {{ \Illuminate\Support\Str::limit((string) $question->correct_answer, 120) }}
                                    @endif
                                </small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Todavia no agregaste preguntas en este examen.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const typeSelect = document.getElementById('question_type');
                const multipleFields = document.getElementById('multiple_choice_fields');
                const writtenFields = document.getElementById('written_fields');
                const correctOptionInput = document.getElementById('correct_option');
                const correctAnswerInput = document.getElementById('correct_answer');
                const optionA = document.querySelector('input[name="option_a"]');
                const optionB = document.querySelector('input[name="option_b"]');

                if (!typeSelect || !multipleFields || !writtenFields) {
                    return;
                }

                const syncFields = () => {
                    const isMultiple = typeSelect.value === 'multiple_choice';

                    multipleFields.classList.toggle('d-none', !isMultiple);
                    writtenFields.classList.toggle('d-none', isMultiple);

                    if (optionA) optionA.required = isMultiple;
                    if (optionB) optionB.required = isMultiple;
                    if (correctOptionInput) correctOptionInput.required = isMultiple;
                    if (correctAnswerInput) correctAnswerInput.required = !isMultiple;
                };

                typeSelect.addEventListener('change', syncFields);
                syncFields();
            });
        </script>
    @endpush
</x-app-layout>
