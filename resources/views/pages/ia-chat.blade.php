<x-app-layout>
    <div class="container-fluid ia-chat-page">
        @if (session('message'))
            <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h4 mb-1">{{ $chat->name }}</h1>
                <p class="text-muted mb-0">Conversacion del chat IA</p>
            </div>
            <a href="{{ route('portal.ai') }}" class="btn btn-outline-secondary">Volver a IA</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="ia-chat-thread p-4">
                    @forelse ($chat->messages as $message)
                        <div class="ia-message-row {{ $message->role === 'user' ? 'is-user' : 'is-assistant' }}">
                            <div class="ia-message-bubble">
                                <div class="ia-message-role">
                                    {{ $message->role === 'user' ? 'Tu' : 'IA' }}
                                </div>
                                <p class="mb-0">{{ $message->content }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">El chat esta vacio. Escribe tu primer mensaje.</p>
                    @endforelse
                </div>

                <div class="border-top p-4">
                    <form method="POST" action="{{ route('portal.ai.chats.messages.store', $chat) }}">
                        @csrf
                        <label for="message" class="form-label">Nuevo mensaje</label>
                        <textarea
                            id="message"
                            name="message"
                            rows="3"
                            class="form-control @if($errors->message->has('message')) is-invalid @endif"
                            placeholder="Escribe tu mensaje..."
                            required
                        >{{ old('message') }}</textarea>
                        @if ($errors->message->has('message'))
                            <div class="invalid-feedback">{{ $errors->message->first('message') }}</div>
                        @endif
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
