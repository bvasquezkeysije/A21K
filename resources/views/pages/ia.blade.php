<x-app-layout>
    <div class="container-fluid ia-page">
        @if (session('message'))
            <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <h1 class="h4 mb-2">Modulo IA</h1>
                <p class="text-muted mb-0">
                    Escribe tu primer mensaje para crear un chat nuevo y comenzar la conversacion.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 ia-gpt-card">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-12 col-lg-3 ia-gpt-card-sidebar">
                        <div class="ia-gpt-side-title">Tus chats</div>
                        <div class="ia-gpt-side-list">
                            @forelse ($chats as $chat)
                                <a href="{{ route('portal.ai.chats.show', $chat) }}" class="ia-gpt-side-item">
                                    <span class="ia-gpt-side-item-title">{{ $chat->name }}</span>
                                    <small>{{ $chat->messages_count }} mensajes</small>
                                </a>
                            @empty
                                <div class="ia-gpt-side-empty">Aun no tienes chats creados.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-12 col-lg-9 ia-gpt-card-main">
                        <div class="ia-gpt-card-content">
                            <div class="ia-gpt-badge">Nuevo chat IA</div>
                            <h2 class="ia-gpt-title">Empieza una nueva conversacion</h2>
                            <p class="ia-gpt-subtitle">
                                El primer mensaje se toma automaticamente como nombre del chat.
                            </p>

                            <form method="POST" action="{{ route('portal.ai.chats.store') }}" class="ia-gpt-form" enctype="multipart/form-data">
                                @csrf
                                <label for="first_message" class="visually-hidden">Primer mensaje</label>
                                <div class="ia-gpt-input-btn">
                                    <button type="button" id="chatAttachTrigger" class="ia-gpt-upload-btn" aria-label="Adjuntar archivo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m21.4 11.2-9.2 9.2a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.3 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"></path>
                                        </svg>
                                    </button>
                                    <input id="chat_attachment" name="chat_attachment" type="file" class="d-none" accept=".pdf,.doc,.docx,.txt,.csv,.xlsx,.xls,.png,.jpg,.jpeg">
                                    <input
                                        id="first_message"
                                        name="first_message"
                                        type="text"
                                        class="ia-gpt-input-field @if($errors->chat->has('first_message')) is-invalid @endif"
                                        placeholder="Escribe tu mensaje para iniciar el chat..."
                                        value="{{ old('first_message') }}"
                                        required
                                    >
                                    <button type="submit" class="ia-gpt-send-btn" aria-label="Enviar mensaje">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m22 2-7 20-4-9-9-4z"></path>
                                            <path d="M22 2 11 13"></path>
                                        </svg>
                                    </button>
                                </div>
                                @if ($errors->chat->has('first_message'))
                                    <div class="text-danger text-start small mt-2">{{ $errors->chat->first('first_message') }}</div>
                                @endif
                                @if ($errors->chat->has('chat_attachment'))
                                    <div class="text-danger text-start small mt-2">{{ $errors->chat->first('chat_attachment') }}</div>
                                @endif
                                <div id="chatAttachmentName" class="text-muted text-start small mt-2 d-none"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const attachTrigger = document.getElementById('chatAttachTrigger');
                const attachInput = document.getElementById('chat_attachment');
                const attachName = document.getElementById('chatAttachmentName');

                if (!attachTrigger || !attachInput || !attachName) return;

                attachTrigger.addEventListener('click', () => {
                    attachInput.click();
                });

                attachInput.addEventListener('change', () => {
                    const file = attachInput.files && attachInput.files.length > 0
                        ? attachInput.files[0]
                        : null;

                    if (!file) {
                        attachName.textContent = '';
                        attachName.classList.add('d-none');
                        return;
                    }

                    attachName.textContent = `Archivo seleccionado: ${file.name}`;
                    attachName.classList.remove('d-none');
                });
            });
        </script>
    @endpush
</x-app-layout>
