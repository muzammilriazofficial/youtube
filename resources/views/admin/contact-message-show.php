@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Message: <?= e($message['subject'] ?? '') ?></h4>
    <a href="<?= url('/admin/contact-messages') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card table-card mb-4">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Message Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">From</div><div class="fw-semibold"><?= e($message['name'] ?? '') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Email</div><div><?= e($message['email'] ?? '') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Date</div><div><?= e($message['created_at'] ?? '') ?></div></div>
                    <div class="col-12"><div class="text-muted small">Subject</div><div class="fw-semibold"><?= e($message['subject'] ?? '') ?></div></div>
                    <div class="col-12"><div class="text-muted small">Message</div><div class="mt-1 p-3 bg-body-secondary rounded"><?= nl2br(e($message['message'] ?? '')) ?></div></div>
                </div>
            </div>
        </div>

        @if(isset($replies) && count($replies) > 0)
        <div class="card table-card mb-4">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Replies</h6></div>
            <div class="card-body">
                @foreach($replies as $reply)
                <div class="border-start border-primary border-3 ps-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold small">Admin Reply</span>
                        <span class="text-muted small"><?= date('M d, Y H:i', strtotime($reply['created_at'] ?? '')) ?></span>
                    </div>
                    <div class="mt-1"><?= nl2br(e($reply['reply'] ?? '')) ?></div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Reply</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/contact-messages/reply/' . $message['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Reply *</label>
                        <textarea class="form-control" name="reply" rows="6" required placeholder="Write your reply..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Send Reply</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
