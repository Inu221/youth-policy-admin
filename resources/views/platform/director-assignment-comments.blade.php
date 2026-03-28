<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">Комментарии и обсуждение</h5>
    </div>
    <div class="card-body">
        @if($comments->count() > 0)
            <div class="list-group list-group-flush mb-3">
                @foreach($comments as $comment)
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">{{ $comment->user->full_name }}</h6>
                        <small class="text-muted">{{ $comment->created_at->format('d.m.Y H:i') }}</small>
                    </div>
                    <p class="mb-1">{{ $comment->comment }}</p>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">Комментариев пока нет</p>
        @endif

        @if($canComment)
        <form action="{{ route('platform.director-assignments.add-comment', $assignment) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="comment" class="form-label">Добавить комментарий</label>
                <textarea
                    class="form-control @error('comment') is-invalid @enderror"
                    id="comment"
                    name="comment"
                    rows="3"
                    placeholder="Введите ваш комментарий..."
                    required
                ></textarea>
                @error('comment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="me-1">💬</i> Отправить комментарий
            </button>
        </form>
        @endif
    </div>
</div>
