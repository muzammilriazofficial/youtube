@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">FAQ Management</h4>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card table-card mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Question</th><th>Order</th><th>Actions</th></tr></thead>
                        <tbody>
                            @if(count($faqs['data']) > 0)
                                @foreach($faqs['data'] as $faq)
                                <tr>
                                    <td><?= $faq['id'] ?></td>
                                    <td class="fw-semibold"><?= e($faq['question'] ?? '') ?></td>
                                    <td class="text-muted small"><?= $faq['sort_order'] ?? 0 ?></td>
                                    <td>
                                        <form method="POST" action="<?= url('/admin/faqs/delete/' . $faq['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this FAQ?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="text-center text-muted py-4">No FAQs yet</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Add FAQ</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/faqs/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Question *</label>
                        <input type="text" class="form-control" name="question" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Answer *</label>
                        <textarea class="form-control" name="answer" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add FAQ</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
