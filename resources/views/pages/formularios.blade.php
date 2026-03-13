<x-app-layout>
    <div class="container-fluid">
        @php
            $hasActiveManualExam = $activeManualExam !== null;
        @endphp

        @if (session('message'))
            <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h4 mb-2">Examenes</h1>
                <p class="text-muted mb-0">
                    Importa tus examenes desde Excel y luego inicia el repaso por preguntas.
                </p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-4">
                        <h2 class="h5 mb-2">Crear examen manual</h2>
                        <p class="text-muted mb-3">
                            Escribe el nombre del examen y crea el examen para empezar a agregar preguntas una por una.
                        </p>

                        <form method="POST" action="{{ route('portal.ai.exams.manual.store') }}" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-12 col-xl-8">
                                <label for="manual_exam_name" class="form-label">Nombre del examen</label>
                                <input
                                    type="text"
                                    id="manual_exam_name"
                                    name="manual_exam_name"
                                    value="{{ old('manual_exam_name') }}"
                                    class="form-control @if($errors->manualExam->has('manual_exam_name')) is-invalid @endif"
                                    placeholder="Ejemplo: Simulacro semanal"
                                    required
                                >
                                @if ($errors->manualExam->has('manual_exam_name'))
                                    <div class="invalid-feedback">{{ $errors->manualExam->first('manual_exam_name') }}</div>
                                @endif
                            </div>
                            <div class="col-12 col-xl-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    Crear examen manual
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-4">
                        <h2 class="h5 mb-2">Repasar examen con Excel</h2>
                        <p class="text-muted mb-3">
                            Coloca un nombre al examen y sube el archivo Excel con los campos requeridos.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadExamModal">
                                Subir examen
                            </button>
                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#examFormatPreviewModal">
                                Ver formato
                            </button>
                            <a href="{{ route('portal.ai.exams.format') }}" class="btn btn-outline-secondary" download>
                                Descargar formato
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top border-secondary-subtle my-4"></div>

        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h3 class="h6 mb-0">Examenes importados</h3>
            </div>
            <div class="card-body pt-3 px-4 pb-4">
                @php
                    $listQuery = array_filter([
                        'q' => $searchQuery,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                        'per_page' => $perPage,
                        'page' => $exams->currentPage() > 1 ? $exams->currentPage() : null,
                    ], fn ($value) => $value !== '' && $value !== null);
                @endphp

                <form method="GET" action="{{ route('portal.forms') }}" class="border rounded p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12">
                            <label for="q" class="form-label mb-1">Buscar examen</label>
                            <div class="d-flex flex-wrap flex-lg-nowrap gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <input
                                        type="text"
                                        id="q"
                                        name="q"
                                        value="{{ $searchQuery }}"
                                        class="form-control"
                                        placeholder="Nombre del examen..."
                                    >
                                </div>

                                <div class="exam-per-page-card d-flex align-items-center gap-2 flex-shrink-0">
                                    <label for="per_page" class="small mb-0">Mostrar</label>
                                    <select id="per_page" name="per_page" class="form-select form-select-sm exam-per-page-select">
                                        @foreach ($perPageOptions as $option)
                                            <option value="{{ $option }}" @selected($perPage === $option)>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                    <button
                                        type="button"
                                        id="examFiltersToggle"
                                        class="btn btn-outline-secondary exam-toolbar-btn"
                                        aria-expanded="{{ $showFilters ? 'true' : 'false' }}"
                                        aria-controls="examFiltersPanel"
                                        aria-label="Filtros"
                                        title="Filtros"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polygon points="22 3 2 3 10 12.5 10 19 14 21 14 12.5 22 3"></polygon>
                                        </svg>
                                        <span class="visually-hidden">Filtros</span>
                                    </button>
                                    <button type="submit" class="btn btn-primary exam-toolbar-btn" aria-label="Buscar" title="Buscar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
                                        </svg>
                                        <span class="visually-hidden">Buscar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="examFiltersPanel" class="mt-3 @if(!$showFilters) d-none @endif">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <label for="from_date" class="form-label mb-1">Desde</label>
                                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="to_date" class="form-label mb-1">Hasta</label>
                                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>

                @forelse ($exams as $exam)
                    @php
                        $hasOpenAttempt = in_array((int) $exam->id, $openAttemptExamIds ?? [], true);
                    @endphp
                    <div class="rounded border p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="fw-semibold">{{ $exam->name }}</div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary exam-name-edit-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editExamNameModal-{{ $exam->id }}"
                                        title="Editar nombre"
                                        aria-label="Editar nombre del examen"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                                        </svg>
                                        <span class="visually-hidden">Editar nombre</span>
                                    </button>
                                </div>
                                <small class="text-muted d-block">
                                    Cargado: {{ $exam->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge text-bg-primary" data-exam-questions-badge="{{ $exam->id }}">{{ $exam->questions_count }} preguntas</span>
                                <span class="badge text-bg-secondary">
                                    {{ $exam->attempts_count }} {{ $exam->attempts_count === 1 ? 'intento' : 'intentos' }}
                                </span>
                                @if ($hasOpenAttempt)
                                    <span class="badge text-bg-warning">repaso en curso</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex flex-wrap gap-2 exam-card-actions">
                                <a href="{{ route('portal.forms', array_merge($listQuery, ['manual_exam' => $exam->id])) }}" class="btn btn-sm btn-outline-secondary exam-card-action-btn">
                                    Gestionar preguntas
                                </a>
                                <form method="POST" action="{{ route('portal.ai.exams.practice.start', $exam) }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary exam-card-action-btn">
                                        {{ $hasOpenAttempt ? 'Continuar repaso' : 'Iniciar repaso' }}
                                    </button>
                                </form>
                                @if (! $hasOpenAttempt)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary exam-card-action-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#practiceSettingsModal-{{ $exam->id }}"
                                    >
                                        Configuracion
                                    </button>
                                @endif
                                @if ($hasOpenAttempt)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-warning exam-card-action-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#restartExamModal-{{ $exam->id }}"
                                    >
                                        Reiniciar
                                    </button>
                                @endif
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger exam-card-action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deactivateExamModal-{{ $exam->id }}"
                                >
                                    Inactivar
                                </button>
                            </div>
                        </div>
                    </div>

                    @if (! $hasOpenAttempt)
                        @php
                            $configuredFeedbackMode = ($exam->practice_feedback_enabled ?? true) ? 'with_feedback' : 'without_feedback';
                            $configuredOrderMode = (string) ($exam->practice_order_mode ?? 'ordered') === 'random' ? 'random' : 'ordered';
                            $configuredProgressMode = ($exam->practice_repeat_until_correct ?? false) ? 'repeat_until_correct' : 'allow_incorrect_pass';
                        @endphp
                        <div class="modal fade" id="practiceSettingsModal-{{ $exam->id }}" tabindex="-1" aria-labelledby="practiceSettingsModalLabel-{{ $exam->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form method="POST" action="{{ route('portal.ai.exams.practice.settings.update', array_merge(['exam' => $exam], array_filter($listQuery + ['manual_exam' => request('manual_exam')], fn ($value) => $value !== null && $value !== ''))) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="practiceSettingsModalLabel-{{ $exam->id }}">Configuracion de repaso</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted mb-3">
                                                Guarda tus preferencias. Se aplicaran cuando inicies el repaso.
                                            </p>

                                            <div class="mb-4">
                                                <div class="fw-semibold mb-2">Verificacion y explicacion</div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="practice_feedback_mode" id="practice_feedback_mode_with_{{ $exam->id }}" value="with_feedback" @checked($configuredFeedbackMode === 'with_feedback')>
                                                    <label class="form-check-label" for="practice_feedback_mode_with_{{ $exam->id }}">
                                                        Mostrar verificacion y explicacion en cada pregunta
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="practice_feedback_mode" id="practice_feedback_mode_without_{{ $exam->id }}" value="without_feedback" @checked($configuredFeedbackMode === 'without_feedback')>
                                                    <label class="form-check-label" for="practice_feedback_mode_without_{{ $exam->id }}">
                                                        Avanzar directo sin verificacion inmediata
                                                    </label>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="fw-semibold mb-2">Orden de preguntas</div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="practice_order_mode" id="practice_order_mode_ordered_{{ $exam->id }}" value="ordered" @checked($configuredOrderMode === 'ordered')>
                                                    <label class="form-check-label" for="practice_order_mode_ordered_{{ $exam->id }}">
                                                        En orden
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="practice_order_mode" id="practice_order_mode_random_{{ $exam->id }}" value="random" @checked($configuredOrderMode === 'random')>
                                                    <label class="form-check-label" for="practice_order_mode_random_{{ $exam->id }}">
                                                        Aleatorio
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <div class="fw-semibold mb-2">Avance cuando la respuesta es incorrecta</div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="practice_progress_mode" id="practice_progress_mode_repeat_{{ $exam->id }}" value="repeat_until_correct" @checked($configuredProgressMode === 'repeat_until_correct')>
                                                    <label class="form-check-label" for="practice_progress_mode_repeat_{{ $exam->id }}">
                                                        Repetir la pregunta hasta responder correctamente
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="practice_progress_mode" id="practice_progress_mode_allow_{{ $exam->id }}" value="allow_incorrect_pass" @checked($configuredProgressMode === 'allow_incorrect_pass')>
                                                    <label class="form-check-label" for="practice_progress_mode_allow_{{ $exam->id }}">
                                                        Permitir pasar aunque la respuesta este mal
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal fade" id="editExamNameModal-{{ $exam->id }}" tabindex="-1" aria-labelledby="editExamNameModalLabel-{{ $exam->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form method="POST" action="{{ route('portal.ai.exams.update-name', array_merge(['exam' => $exam], array_filter($listQuery + ['manual_exam' => request('manual_exam')], fn ($value) => $value !== null && $value !== ''))) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="rename_exam_id" value="{{ $exam->id }}">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editExamNameModalLabel-{{ $exam->id }}">Editar nombre del examen</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label for="exam_name_{{ $exam->id }}" class="form-label">Nombre del examen</label>
                                        <input
                                            type="text"
                                            id="exam_name_{{ $exam->id }}"
                                            name="exam_name"
                                            value="@if((int) old('rename_exam_id') === $exam->id){{ old('exam_name', $exam->name) }}@else{{ $exam->name }}@endif"
                                            class="form-control @if($errors->manualExamRename->has('exam_name') && (int) old('rename_exam_id') === $exam->id) is-invalid @endif"
                                            required
                                        >
                                        @if ($errors->manualExamRename->has('exam_name') && (int) old('rename_exam_id') === $exam->id)
                                            <div class="invalid-feedback">{{ $errors->manualExamRename->first('exam_name') }}</div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if ($hasOpenAttempt)
                        <div class="modal fade" id="restartExamModal-{{ $exam->id }}" tabindex="-1" aria-labelledby="restartExamModalLabel-{{ $exam->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="restartExamModalLabel-{{ $exam->id }}">Reiniciar repaso</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="restart-modal-icon" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="1 4 1 10 7 10"></polyline>
                                                    <path d="M3.5 15a9 9 0 1 0 .4-5"></path>
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="mb-1 fw-semibold">Se cerrara el repaso pendiente.</p>
                                                <p class="mb-0 text-muted">
                                                    Luego podras iniciar un nuevo repaso desde la primera pregunta.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form method="POST" action="{{ route('portal.ai.exams.practice.start', $exam) }}" class="m-0">
                                            @csrf
                                            <input type="hidden" name="restart" value="1">
                                            <button type="submit" class="btn btn-warning">Reiniciar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal fade" id="deactivateExamModal-{{ $exam->id }}" tabindex="-1" aria-labelledby="deactivateExamModalLabel-{{ $exam->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deactivateExamModalLabel-{{ $exam->id }}">Inactivar examen</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="delete-modal-icon" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M10.3 3.1 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.1a2 2 0 0 0-3.4 0z"></path>
                                                <path d="M12 9v5"></path>
                                                <circle cx="12" cy="17" r=".8" fill="currentColor"></circle>
                                            </svg>
                                        </span>
                                        <div>
                                            <p class="mb-1 fw-semibold">Se aplicara eliminacion logica.</p>
                                            <p class="mb-0 text-muted">
                                                El examen <strong>{{ $exam->name }}</strong> quedara inactivo y no aparecera en listados.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form method="POST" action="{{ route('portal.ai.exams.destroy', array_merge(['exam' => $exam], $listQuery, ['manual_exam' => request('manual_exam')])) }}" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Inactivar examen</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Todavia no tienes examenes cargados.</p>
                @endforelse

                @php
                    $firstItem = $exams->firstItem() ?? 0;
                    $lastItem = $exams->lastItem() ?? 0;
                    $totalPages = max($exams->lastPage(), 1);
                    $currentPage = max($exams->currentPage(), 1);
                @endphp

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                    <small class="text-muted">
                        Mostrando {{ $firstItem }} - {{ $lastItem }} de {{ $exams->total() }} examenes | Pagina {{ $currentPage }} de {{ $totalPages }}
                    </small>
                    <div class="btn-group" role="group" aria-label="Paginacion de examenes">
                        <a
                            href="{{ $exams->onFirstPage() ? '#' : $exams->previousPageUrl() }}"
                            class="btn btn-outline-secondary @if($exams->onFirstPage()) disabled @endif"
                            @if($exams->onFirstPage()) aria-disabled="true" @endif
                        >
                            Anterior
                        </a>
                        <a
                            href="{{ $exams->hasMorePages() ? $exams->nextPageUrl() : '#' }}"
                            class="btn btn-outline-secondary @if(!$exams->hasMorePages()) disabled @endif"
                            @if(!$exams->hasMorePages()) aria-disabled="true" @endif
                        >
                            Siguiente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manualQuestionModal" tabindex="-1" aria-labelledby="manualQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="manualQuestionModalLabel">Crear preguntas manualmente</h5>
                        @if ($hasActiveManualExam)
                            <small class="text-muted d-block mt-1">
                                Examen: {{ $activeManualExam->name }} | <span id="manualQuestionsCount">{{ $activeManualExam->questions_count }}</span> preguntas
                            </small>
                        @endif
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    @if ($hasActiveManualExam)
                        <div id="manualQuestionFeedback" class="alert d-none" role="alert"></div>

                        @if ($errors->manualQuestion->any())
                            <div class="alert alert-danger">{{ $errors->manualQuestion->first() }}</div>
                        @endif

                        <form id="manualQuestionForm" data-exam-id="{{ $activeManualExam->id }}" method="POST" action="{{ route('portal.ai.exams.manual.questions.store', $activeManualExam) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
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

                                <div class="col-12 col-lg-4">
                                    <label for="manual_question_type" class="form-label">Tipo</label>
                                    <select
                                        id="manual_question_type"
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

                                <div class="col-12 col-lg-4">
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

                                <div class="col-12 col-lg-4">
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

                            <div id="manualMultipleChoiceFields" class="mt-3">
                                <label class="form-label">Opciones</label>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="option_a" value="{{ old('option_a') }}" class="form-control @if($errors->manualQuestion->has('option_a')) is-invalid @endif" placeholder="Opcion A">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="option_b" value="{{ old('option_b') }}" class="form-control @if($errors->manualQuestion->has('option_b')) is-invalid @endif" placeholder="Opcion B">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="option_c" value="{{ old('option_c') }}" class="form-control @if($errors->manualQuestion->has('option_c')) is-invalid @endif" placeholder="Opcion C (opcional)">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="option_d" value="{{ old('option_d') }}" class="form-control @if($errors->manualQuestion->has('option_d')) is-invalid @endif" placeholder="Opcion D (opcional)">
                                    </div>
                                </div>

                                <div class="mt-2">
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

                            <div id="manualWrittenFields" class="d-none mt-3">
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

                            <div class="mt-3">
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

                            <div class="form-check mt-3">
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
                                    Activar temporizador
                                </label>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Guardar pregunta
                                </button>
                                <a href="{{ route('portal.forms') }}" class="btn btn-outline-secondary">
                                    Finalizar
                                </a>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <h6 class="mb-0">Preguntas agregadas</h6>
                            <div class="d-flex align-items-center gap-2">
                                <label for="manualQuestionsOrder" class="small text-muted mb-0">Ordenar</label>
                                <select id="manualQuestionsOrder" class="form-select form-select-sm">
                                    <option value="desc">Ultimo a 1</option>
                                    <option value="asc">1 a ultimo</option>
                                </select>
                            </div>
                        </div>
                        <div
                            id="manualQuestionsList"
                            class="@if($activeManualExam->questions->isEmpty()) d-none @endif overflow-auto pe-1"
                            style="max-height: 420px;"
                        >
                            @foreach ($activeManualExam->questions as $question)
                                @php
                                    $questionOptions = $question->options->sortBy('id')->values();
                                    $optionA = (string) ($questionOptions->get(0)?->option_text ?? '');
                                    $optionB = (string) ($questionOptions->get(1)?->option_text ?? '');
                                    $optionC = (string) ($questionOptions->get(2)?->option_text ?? '');
                                    $optionD = (string) ($questionOptions->get(3)?->option_text ?? '');
                                    $correctOption = '';
                                    foreach ($questionOptions as $optionIndex => $optionModel) {
                                        if ($optionModel->is_correct) {
                                            $correctOption = ['a', 'b', 'c', 'd'][$optionIndex] ?? '';
                                            break;
                                        }
                                    }
                                    $questionPayload = [
                                        'id' => (int) $question->id,
                                        'question_text' => (string) $question->question_text,
                                        'question_type' => (string) $question->question_type,
                                        'correct_answer' => (string) ($question->correct_answer ?? ''),
                                        'explanation' => (string) ($question->explanation ?? ''),
                                        'points' => (int) $question->points,
                                        'temporizador_segundos' => (int) ($question->temporizador_segundos ?? $question->time_limit),
                                        'timer_enabled' => (bool) $question->timer_enabled,
                                        'option_a' => $optionA,
                                        'option_b' => $optionB,
                                        'option_c' => $optionC,
                                        'option_d' => $optionD,
                                        'correct_option' => $correctOption,
                                    ];
                                @endphp
                                <div class="border rounded p-3 mb-2" data-question-item data-question-id="{{ $question->id }}">
                                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-bg-secondary">#{{ $question->id }}</span>
                                            <div class="fw-semibold" data-question-title>{{ $question->question_text }}</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-bg-light text-uppercase" data-question-type>
                                                {{ $question->question_type === 'multiple_choice' ? 'Seleccion' : 'Escrita' }}
                                            </span>
                                            <button
                                                type="button"
                                                id="editQuestionBtn-{{ $question->id }}"
                                                class="btn btn-sm btn-outline-primary js-open-edit-question"
                                                data-question='@json($questionPayload)'
                                            >
                                                Editar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1" data-question-meta>
                                        Puntaje: {{ $question->points }} | Temporizador: {{ (int) ($question->temporizador_segundos ?? $question->time_limit) }} s
                                    </small>
                                </div>
                            @endforeach
                        </div>
                        <p id="manualQuestionsEmpty" class="text-muted mb-0 @if($activeManualExam->questions->isNotEmpty()) d-none @endif">Todavia no agregaste preguntas en este examen.</p>
                    @else
                        <p class="text-muted mb-0">Primero crea o selecciona un examen para agregar preguntas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($hasActiveManualExam)
        <div class="modal fade" id="editManualQuestionModal" tabindex="-1" aria-labelledby="editManualQuestionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <form
                        id="editManualQuestionForm"
                        method="POST"
                        data-action-template="{{ route('portal.ai.exams.manual.questions.update', ['exam' => $activeManualExam, 'question' => '__QUESTION__']) }}"
                    >
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="edit_question_id" name="edit_question_id" value="">

                        <div class="modal-header">
                            <h5 class="modal-title" id="editManualQuestionModalLabel">Editar pregunta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            @if ($errors->manualQuestionEdit->any())
                                <div class="alert alert-danger">
                                    {{ $errors->manualQuestionEdit->first() }}
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="edit_question_text" class="form-label">Pregunta</label>
                                <textarea id="edit_question_text" name="edit_question_text" rows="3" class="form-control" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="edit_question_type" class="form-label">Tipo de pregunta</label>
                                <select id="edit_question_type" name="edit_question_type" class="form-select" required>
                                    <option value="multiple_choice">Seleccion multiple</option>
                                    <option value="written">Escrita</option>
                                </select>
                            </div>

                            <div id="editManualMultipleChoiceFields">
                                <label class="form-label">Opciones</label>
                                <div class="mb-2">
                                    <input type="text" id="edit_option_a" name="edit_option_a" class="form-control" placeholder="Opcion A">
                                </div>
                                <div class="mb-2">
                                    <input type="text" id="edit_option_b" name="edit_option_b" class="form-control" placeholder="Opcion B">
                                </div>
                                <div class="mb-2">
                                    <input type="text" id="edit_option_c" name="edit_option_c" class="form-control" placeholder="Opcion C (opcional)">
                                </div>
                                <div class="mb-3">
                                    <input type="text" id="edit_option_d" name="edit_option_d" class="form-control" placeholder="Opcion D (opcional)">
                                </div>

                                <div class="mb-3">
                                    <label for="edit_correct_option" class="form-label">Opcion correcta</label>
                                    <select id="edit_correct_option" name="edit_correct_option" class="form-select">
                                        <option value="">Selecciona la correcta</option>
                                        <option value="a">A</option>
                                        <option value="b">B</option>
                                        <option value="c">C</option>
                                        <option value="d">D</option>
                                    </select>
                                </div>
                            </div>

                            <div id="editManualWrittenFields" class="d-none">
                                <div class="mb-3">
                                    <label for="edit_correct_answer" class="form-label">Respuesta correcta</label>
                                    <textarea id="edit_correct_answer" name="edit_correct_answer" rows="2" class="form-control" placeholder="Respuesta esperada..."></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_explanation" class="form-label">Explicacion (opcional)</label>
                                <textarea id="edit_explanation" name="edit_explanation" rows="2" class="form-control" placeholder="Explicacion para repaso..."></textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="edit_points" class="form-label">Puntaje</label>
                                    <input type="number" id="edit_points" name="edit_points" min="1" max="1000" class="form-control" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="edit_temporizador_segundos" class="form-label">Temporizador (segundos)</label>
                                    <input type="number" id="edit_temporizador_segundos" name="edit_temporizador_segundos" min="1" max="86400" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input type="hidden" name="edit_timer_enabled" value="0">
                                <input class="form-check-input" type="checkbox" value="1" id="edit_timer_enabled" name="edit_timer_enabled">
                                <label class="form-check-label" for="edit_timer_enabled">
                                    Activar temporizador
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="uploadExamModal" tabindex="-1" aria-labelledby="uploadExamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('portal.ai.exams.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadExamModalLabel">Repasar examen con Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="exam_name" class="form-label">Nombre del examen</label>
                            <input
                                type="text"
                                id="exam_name"
                                name="exam_name"
                                value="{{ old('exam_name') }}"
                                class="form-control @if($errors->exam->has('exam_name')) is-invalid @endif"
                                placeholder="Ejemplo: Simulacro final 01"
                                required
                            >
                            @if ($errors->exam->has('exam_name'))
                                <div class="invalid-feedback">{{ $errors->exam->first('exam_name') }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="exam_file" class="form-label">Archivo Excel</label>
                            <input
                                type="file"
                                id="exam_file"
                                name="exam_file"
                                accept=".xlsx,.xls,.csv"
                                class="form-control @if($errors->exam->has('exam_file')) is-invalid @endif"
                                required
                            >
                            @if ($errors->exam->has('exam_file'))
                                <div class="invalid-feedback">{{ $errors->exam->first('exam_file') }}</div>
                            @endif
                        </div>

                        <small class="text-muted d-block">
                            Campos requeridos: pregunta, tipo, opcion_a, opcion_b, opcion_c, opcion_d,
                            respuesta_correcta, explicacion, puntaje, temporizador_segundos.
                            Campos opcionales: cronometro_segundos, temporizador (si/no).
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Importar examen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="examFormatPreviewModal" tabindex="-1" aria-labelledby="examFormatPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="examFormatPreviewModalLabel">Formato esperado del Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Columnas requeridas y ejemplo de como debe verse cada fila.
                    </p>

                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>pregunta</th>
                                    <th>tipo</th>
                                    <th>opcion_a</th>
                                    <th>opcion_b</th>
                                    <th>opcion_c</th>
                                    <th>opcion_d</th>
                                    <th>respuesta_correcta</th>
                                    <th>explicacion</th>
                                    <th>puntaje</th>
                                    <th>temporizador_segundos</th>
                                    <th>cronometro_segundos</th>
                                    <th>temporizador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Capital de Peru?</td>
                                    <td>seleccion</td>
                                    <td>Lima</td>
                                    <td>Cusco</td>
                                    <td>Piura</td>
                                    <td>Arequipa</td>
                                    <td>Lima</td>
                                    <td>Lima es la capital del Peru</td>
                                    <td>5</td>
                                    <td>30</td>
                                    <td>0</td>
                                    <td>si</td>
                                </tr>
                                <tr>
                                    <td>Define algoritmo</td>
                                    <td>escrita</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>Conjunto de pasos para resolver un problema</td>
                                    <td>Un algoritmo es una serie de pasos ordenados</td>
                                    <td>10</td>
                                    <td>120</td>
                                    <td>0</td>
                                    <td>si</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <ul class="small text-muted mt-3 mb-0">
                        <li>`tipo` acepta: `seleccion` o `escrita`.</li>
                        <li>En preguntas `escrita`, `opcion_a` a `opcion_d` pueden ir vacias.</li>
                        <li>`temporizador` acepta: `si/no`, `1/0`, `true/false`.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('portal.ai.exams.format') }}" class="btn btn-primary" download>Descargar formato</a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const manualQuestionTypeSelect = document.getElementById('manual_question_type');
                const manualMultipleChoiceFields = document.getElementById('manualMultipleChoiceFields');
                const manualWrittenFields = document.getElementById('manualWrittenFields');
                const manualCorrectOption = document.getElementById('correct_option');
                const manualCorrectAnswer = document.getElementById('correct_answer');
                const manualOptionA = document.querySelector('input[name="option_a"]');
                const manualOptionB = document.querySelector('input[name="option_b"]');
                const manualQuestionForm = document.getElementById('manualQuestionForm');
                const manualQuestionFeedback = document.getElementById('manualQuestionFeedback');
                const manualQuestionsList = document.getElementById('manualQuestionsList');
                const manualQuestionsEmpty = document.getElementById('manualQuestionsEmpty');
                const manualQuestionsCount = document.getElementById('manualQuestionsCount');
                const manualQuestionsOrder = document.getElementById('manualQuestionsOrder');
                const editManualQuestionModalElement = document.getElementById('editManualQuestionModal');
                const editManualQuestionForm = document.getElementById('editManualQuestionForm');
                const editQuestionId = document.getElementById('edit_question_id');
                const editQuestionText = document.getElementById('edit_question_text');
                const editQuestionType = document.getElementById('edit_question_type');
                const editPoints = document.getElementById('edit_points');
                const editTemporizador = document.getElementById('edit_temporizador_segundos');
                const editExplanation = document.getElementById('edit_explanation');
                const editTimerEnabled = document.getElementById('edit_timer_enabled');
                const editCorrectAnswer = document.getElementById('edit_correct_answer');
                const editCorrectOption = document.getElementById('edit_correct_option');
                const editOptionA = document.getElementById('edit_option_a');
                const editOptionB = document.getElementById('edit_option_b');
                const editOptionC = document.getElementById('edit_option_c');
                const editOptionD = document.getElementById('edit_option_d');
                const editManualMultipleChoiceFields = document.getElementById('editManualMultipleChoiceFields');
                const editManualWrittenFields = document.getElementById('editManualWrittenFields');
                const manualQuestionModalElement = document.getElementById('manualQuestionModal');
                const shouldOpenManualQuestionModal = @json($hasActiveManualExam || $errors->manualQuestion->any());
                const stackedEditModalZIndex = 1085;
                const stackedEditBackdropZIndex = 1080;

                const syncManualQuestionType = () => {
                    if (!manualQuestionTypeSelect || !manualMultipleChoiceFields || !manualWrittenFields) {
                        return;
                    }

                    const isMultipleChoice = manualQuestionTypeSelect.value === 'multiple_choice';
                    manualMultipleChoiceFields.classList.toggle('d-none', !isMultipleChoice);
                    manualWrittenFields.classList.toggle('d-none', isMultipleChoice);

                    if (manualOptionA) manualOptionA.required = isMultipleChoice;
                    if (manualOptionB) manualOptionB.required = isMultipleChoice;
                    if (manualCorrectOption) manualCorrectOption.required = isMultipleChoice;
                    if (manualCorrectAnswer) manualCorrectAnswer.required = !isMultipleChoice;
                };

                if (manualQuestionTypeSelect) {
                    manualQuestionTypeSelect.addEventListener('change', syncManualQuestionType);
                    syncManualQuestionType();
                }

                const showManualQuestionFeedback = (message, type = 'success') => {
                    if (!manualQuestionFeedback) {
                        return;
                    }

                    manualQuestionFeedback.classList.remove('d-none', 'alert-success', 'alert-danger');
                    manualQuestionFeedback.classList.add(type === 'danger' ? 'alert-danger' : 'alert-success');
                    manualQuestionFeedback.textContent = message;
                };

                const sortManualQuestionCards = () => {
                    if (!manualQuestionsList) {
                        return;
                    }

                    const currentOrder = manualQuestionsOrder ? manualQuestionsOrder.value : 'desc';
                    const cards = Array.from(manualQuestionsList.querySelectorAll('[data-question-item]'));

                    cards.sort((firstCard, secondCard) => {
                        const firstId = Number(firstCard.dataset.questionId || 0);
                        const secondId = Number(secondCard.dataset.questionId || 0);
                        return currentOrder === 'asc' ? firstId - secondId : secondId - firstId;
                    });

                    cards.forEach((card) => manualQuestionsList.appendChild(card));
                };

                const getQuestionTypeLabel = (questionType, fallbackLabel = 'Pregunta') => {
                    if (questionType === 'multiple_choice') {
                        return 'Seleccion';
                    }

                    if (questionType === 'written') {
                        return 'Escrita';
                    }

                    return fallbackLabel;
                };

                const buildQuestionDataset = (question) => JSON.stringify({
                    id: Number(question.id ?? 0),
                    question_text: question.question_text ?? '',
                    question_type: question.question_type ?? 'multiple_choice',
                    correct_answer: question.correct_answer ?? '',
                    explanation: question.explanation ?? '',
                    points: Number(question.points ?? 1),
                    temporizador_segundos: Number(question.temporizador_segundos ?? 30),
                    timer_enabled: Boolean(question.timer_enabled),
                    option_a: question.option_a ?? '',
                    option_b: question.option_b ?? '',
                    option_c: question.option_c ?? '',
                    option_d: question.option_d ?? '',
                    correct_option: question.correct_option ?? '',
                });

                const syncEditManualQuestionType = () => {
                    if (!editQuestionType || !editManualMultipleChoiceFields || !editManualWrittenFields) {
                        return;
                    }

                    const isMultipleChoice = editQuestionType.value === 'multiple_choice';
                    editManualMultipleChoiceFields.classList.toggle('d-none', !isMultipleChoice);
                    editManualWrittenFields.classList.toggle('d-none', isMultipleChoice);

                    if (editOptionA) editOptionA.required = isMultipleChoice;
                    if (editOptionB) editOptionB.required = isMultipleChoice;
                    if (editCorrectOption) editCorrectOption.required = isMultipleChoice;
                    if (editCorrectAnswer) editCorrectAnswer.required = !isMultipleChoice;
                };

                const fillEditManualQuestionForm = (question) => {
                    if (!question || !editManualQuestionForm) {
                        return;
                    }

                    const actionTemplate = editManualQuestionForm.dataset.actionTemplate || '';
                    if (actionTemplate && question.id) {
                        editManualQuestionForm.action = actionTemplate.replace('__QUESTION__', String(question.id));
                    }

                    if (editQuestionId) editQuestionId.value = String(question.id ?? '');
                    if (editQuestionText) editQuestionText.value = question.question_text ?? '';
                    if (editQuestionType) editQuestionType.value = question.question_type ?? 'multiple_choice';
                    if (editPoints) editPoints.value = String(question.points ?? 1);
                    if (editTemporizador) editTemporizador.value = String(question.temporizador_segundos ?? 30);
                    if (editExplanation) editExplanation.value = question.explanation ?? '';
                    if (editTimerEnabled) editTimerEnabled.checked = Boolean(question.timer_enabled);
                    if (editCorrectAnswer) editCorrectAnswer.value = question.correct_answer ?? '';
                    if (editCorrectOption) editCorrectOption.value = question.correct_option ?? '';
                    if (editOptionA) editOptionA.value = question.option_a ?? '';
                    if (editOptionB) editOptionB.value = question.option_b ?? '';
                    if (editOptionC) editOptionC.value = question.option_c ?? '';
                    if (editOptionD) editOptionD.value = question.option_d ?? '';

                    syncEditManualQuestionType();
                };

                const addManualQuestionCard = (question) => {
                    if (!manualQuestionsList || !question) {
                        return;
                    }

                    const card = document.createElement('div');
                    card.className = 'border rounded p-3 mb-2';
                    card.dataset.questionItem = '1';
                    card.dataset.questionId = String(question.id ?? 0);

                    const header = document.createElement('div');
                    header.className = 'd-flex flex-wrap gap-2 justify-content-between align-items-start';

                    const headerLeft = document.createElement('div');
                    headerLeft.className = 'd-flex align-items-center gap-2';

                    const idBadge = document.createElement('span');
                    idBadge.className = 'badge text-bg-secondary';
                    idBadge.textContent = `#${question.id ?? ''}`;

                    const title = document.createElement('div');
                    title.className = 'fw-semibold';
                    title.textContent = question.question_text ?? '';
                    title.setAttribute('data-question-title', '1');

                    headerLeft.appendChild(idBadge);
                    headerLeft.appendChild(title);

                    const badge = document.createElement('span');
                    badge.className = 'badge text-bg-light text-uppercase';
                    badge.textContent = getQuestionTypeLabel(question.question_type, question.question_type_label ?? 'Pregunta');
                    badge.setAttribute('data-question-type', '1');

                    const headerRight = document.createElement('div');
                    headerRight.className = 'd-flex align-items-center gap-2';
                    headerRight.appendChild(badge);

                    const editButton = document.createElement('button');
                    editButton.type = 'button';
                    editButton.className = 'btn btn-sm btn-outline-primary js-open-edit-question';
                    editButton.textContent = 'Editar';
                    editButton.dataset.question = buildQuestionDataset(question);
                    headerRight.appendChild(editButton);

                    header.appendChild(headerLeft);
                    header.appendChild(headerRight);

                    const meta = document.createElement('small');
                    meta.className = 'text-muted d-block mt-1';
                    meta.textContent = `Puntaje: ${question.points ?? 0} | Temporizador: ${question.temporizador_segundos ?? 0} s`;
                    meta.setAttribute('data-question-meta', '1');

                    card.appendChild(header);
                    card.appendChild(meta);

                    manualQuestionsList.appendChild(card);
                    manualQuestionsList.classList.remove('d-none');
                    sortManualQuestionCards();
                };

                const updateManualQuestionCard = (question) => {
                    if (!manualQuestionsList || !question || !question.id) {
                        return;
                    }

                    const questionId = String(question.id);
                    const card = manualQuestionsList.querySelector(`[data-question-item][data-question-id="${questionId}"]`);

                    if (!card) {
                        addManualQuestionCard(question);
                        return;
                    }

                    const title = card.querySelector('[data-question-title]');
                    const typeBadge = card.querySelector('[data-question-type]');
                    const meta = card.querySelector('[data-question-meta]');
                    const editButton = card.querySelector('[data-question]');

                    if (title) {
                        title.textContent = question.question_text ?? '';
                    }

                    if (typeBadge) {
                        typeBadge.textContent = getQuestionTypeLabel(question.question_type, question.question_type_label ?? 'Pregunta');
                    }

                    if (meta) {
                        meta.textContent = `Puntaje: ${question.points ?? 0} | Temporizador: ${question.temporizador_segundos ?? 0} s`;
                    }

                    if (editButton) {
                        editButton.dataset.question = buildQuestionDataset(question);
                    }

                    sortManualQuestionCards();
                };

                if (manualQuestionsOrder) {
                    manualQuestionsOrder.addEventListener('change', sortManualQuestionCards);
                }
                sortManualQuestionCards();

                if (editQuestionType) {
                    editQuestionType.addEventListener('change', syncEditManualQuestionType);
                    syncEditManualQuestionType();
                }

                if (manualQuestionsList && editManualQuestionModalElement && window.bootstrap) {
                    const applyEditModalLayering = () => {
                        editManualQuestionModalElement.style.zIndex = String(stackedEditModalZIndex);
                        const backdrops = Array.from(document.querySelectorAll('.modal-backdrop'));
                        const topBackdrop = backdrops[backdrops.length - 1];

                        if (topBackdrop) {
                            topBackdrop.dataset.editLayer = '1';
                            topBackdrop.style.zIndex = String(stackedEditBackdropZIndex);
                        }
                    };

                    const resetEditModalLayering = () => {
                        editManualQuestionModalElement.style.removeProperty('z-index');
                        document.querySelectorAll('.modal-backdrop[data-edit-layer="1"]').forEach((backdrop) => {
                            backdrop.style.removeProperty('z-index');
                            delete backdrop.dataset.editLayer;
                        });
                    };

                    manualQuestionsList.addEventListener('click', (event) => {
                        const trigger = event.target.closest('.js-open-edit-question');
                        if (!trigger) {
                            return;
                        }

                        const rawPayload = trigger.getAttribute('data-question');
                        if (!rawPayload) {
                            return;
                        }

                        try {
                            fillEditManualQuestionForm(JSON.parse(rawPayload));
                        } catch (error) {
                            return;
                        }

                        const editModal = window.bootstrap.Modal.getOrCreateInstance(editManualQuestionModalElement);
                        editModal.show();
                    });

                    editManualQuestionModalElement.addEventListener('show.bs.modal', () => {
                        applyEditModalLayering();
                    });

                    editManualQuestionModalElement.addEventListener('shown.bs.modal', () => {
                        applyEditModalLayering();
                    });

                    editManualQuestionModalElement.addEventListener('hidden.bs.modal', () => {
                        resetEditModalLayering();
                        if (manualQuestionModalElement && manualQuestionModalElement.classList.contains('show')) {
                            document.body.classList.add('modal-open');
                        }
                    });
                }

                if (manualQuestionForm && window.fetch) {
                    manualQuestionForm.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        manualQuestionForm.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
                        const submitButton = manualQuestionForm.querySelector('button[type="submit"]');
                        const originalButtonText = submitButton ? submitButton.textContent : '';

                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent = 'Guardando...';
                        }

                        try {
                            const response = await fetch(manualQuestionForm.action, {
                                method: 'POST',
                                body: new FormData(manualQuestionForm),
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            const payload = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                if (response.status === 422 && payload.errors) {
                                    const [firstField] = Object.keys(payload.errors);
                                    const firstMessage = Object.values(payload.errors).flat()[0] ?? 'Revisa los datos de la pregunta.';

                                    if (firstField) {
                                        const invalidField = manualQuestionForm.querySelector(`[name="${firstField}"]`);
                                        if (invalidField) {
                                            invalidField.classList.add('is-invalid');
                                            invalidField.focus();
                                        }
                                    }

                                    showManualQuestionFeedback(firstMessage, 'danger');
                                } else {
                                    showManualQuestionFeedback(payload.message ?? 'No se pudo guardar la pregunta.', 'danger');
                                }
                                return;
                            }

                            showManualQuestionFeedback(payload.message ?? 'Pregunta agregada correctamente.');
                            addManualQuestionCard(payload.question ?? null);

                            if (manualQuestionsEmpty) {
                                manualQuestionsEmpty.classList.add('d-none');
                            }

                            if (typeof payload.questions_count === 'number') {
                                if (manualQuestionsCount) {
                                    manualQuestionsCount.textContent = String(payload.questions_count);
                                }

                                const examId = manualQuestionForm.dataset.examId;
                                if (examId) {
                                    const listBadge = document.querySelector(`[data-exam-questions-badge="${examId}"]`);
                                    if (listBadge) {
                                        listBadge.textContent = `${payload.questions_count} preguntas`;
                                    }
                                }
                            }

                            manualQuestionForm.reset();

                            if (manualQuestionTypeSelect) {
                                manualQuestionTypeSelect.value = 'multiple_choice';
                                syncManualQuestionType();
                            }

                            const timerEnabledInput = document.getElementById('timer_enabled');
                            const pointsInput = document.getElementById('points');
                            const timerInput = document.getElementById('temporizador_segundos');
                            const questionInput = document.getElementById('question_text');

                            if (timerEnabledInput) timerEnabledInput.checked = true;
                            if (pointsInput) pointsInput.value = '1';
                            if (timerInput) timerInput.value = '30';
                            if (questionInput) questionInput.focus();
                        } catch (error) {
                            showManualQuestionFeedback('Error de conexion. Intenta nuevamente.', 'danger');
                        } finally {
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalButtonText;
                            }
                        }
                    });
                }

                if (editManualQuestionForm && window.fetch) {
                    editManualQuestionForm.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        editManualQuestionForm.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
                        const submitButton = editManualQuestionForm.querySelector('button[type="submit"]');
                        const originalButtonText = submitButton ? submitButton.textContent : '';

                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent = 'Guardando...';
                        }

                        try {
                            const response = await fetch(editManualQuestionForm.action, {
                                method: 'POST',
                                body: new FormData(editManualQuestionForm),
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            const payload = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                if (response.status === 422 && payload.errors) {
                                    const [firstField] = Object.keys(payload.errors);
                                    const firstMessage = Object.values(payload.errors).flat()[0] ?? 'Revisa los datos de la pregunta.';

                                    if (firstField) {
                                        const invalidField = editManualQuestionForm.querySelector(`[name="${firstField}"]`);
                                        if (invalidField) {
                                            invalidField.classList.add('is-invalid');
                                            invalidField.focus();
                                        }
                                    }

                                    showManualQuestionFeedback(firstMessage, 'danger');
                                } else {
                                    showManualQuestionFeedback(payload.message ?? 'No se pudo actualizar la pregunta.', 'danger');
                                }
                                return;
                            }

                            updateManualQuestionCard(payload.question ?? null);

                            if (typeof payload.questions_count === 'number' && manualQuestionsCount) {
                                manualQuestionsCount.textContent = String(payload.questions_count);
                            }

                            showManualQuestionFeedback(payload.message ?? 'Pregunta actualizada correctamente.');

                            if (window.bootstrap && editManualQuestionModalElement) {
                                const editModal = window.bootstrap.Modal.getOrCreateInstance(editManualQuestionModalElement);
                                editModal.hide();
                            }
                        } catch (error) {
                            showManualQuestionFeedback('Error de conexion. Intenta nuevamente.', 'danger');
                        } finally {
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalButtonText;
                            }
                        }
                    });
                }

                const examFiltersToggle = document.getElementById('examFiltersToggle');
                const examFiltersPanel = document.getElementById('examFiltersPanel');

                if (examFiltersToggle && examFiltersPanel) {
                    examFiltersToggle.addEventListener('click', () => {
                        const willShow = examFiltersPanel.classList.contains('d-none');
                        examFiltersPanel.classList.toggle('d-none', !willShow);
                        examFiltersToggle.setAttribute('aria-expanded', willShow ? 'true' : 'false');
                    });
                }

                if (manualQuestionModalElement && shouldOpenManualQuestionModal) {
                    const manualQuestionModal = new window.bootstrap.Modal(manualQuestionModalElement);
                    manualQuestionModal.show();
                }

                if (@json($errors->manualQuestionEdit->any())) {
                    const editModalElement = document.getElementById('editManualQuestionModal');
                    if (editModalElement) {
                        const editModal = new window.bootstrap.Modal(editModalElement);
                        editModal.show();
                    }

                    fillEditManualQuestionForm({
                        id: @json(old('edit_question_id')),
                        question_text: @json(old('edit_question_text')),
                        question_type: @json(old('edit_question_type', 'multiple_choice')),
                        correct_answer: @json(old('edit_correct_answer')),
                        explanation: @json(old('edit_explanation')),
                        points: @json(old('edit_points', 1)),
                        temporizador_segundos: @json(old('edit_temporizador_segundos', 30)),
                        timer_enabled: @json((string) old('edit_timer_enabled', '0') === '1'),
                        option_a: @json(old('edit_option_a')),
                        option_b: @json(old('edit_option_b')),
                        option_c: @json(old('edit_option_c')),
                        option_d: @json(old('edit_option_d')),
                        correct_option: @json(old('edit_correct_option')),
                    });
                }

                if (@json($errors->manualExamRename->any())) {
                    const renameExamId = @json(old('rename_exam_id'));
                    if (renameExamId) {
                        const renameModalElement = document.getElementById(`editExamNameModal-${renameExamId}`);
                        if (renameModalElement) {
                            const renameModal = new window.bootstrap.Modal(renameModalElement);
                            renameModal.show();
                        }
                    }
                }

                if (@json($errors->exam->any())) {
                    const examModalElement = document.getElementById('uploadExamModal');
                    if (examModalElement) {
                        const examModal = new window.bootstrap.Modal(examModalElement);
                        examModal.show();
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
