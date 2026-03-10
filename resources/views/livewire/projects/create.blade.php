<div>
    <button type="button" class="btn btn-primary" wire:click="openModal">
        Nuevo proyecto
    </button>

    @if ($showModal)
        <div
            class="position-fixed top-0 start-0 w-100 h-100 modal-static-backdrop d-flex align-items-center justify-content-center"
            style="z-index: 1050;"
            wire:click.self="closeModal"
        >
            <div class="card shadow border-0" style="width: min(560px, 95vw);">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Crear proyecto</strong>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripcion</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="4" wire:model.defer="description"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
