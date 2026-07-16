@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">System Health</h4>
    <a href="<?= url('/admin/system-health') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">PHP Version</div><h5 class="fw-bold mb-0"><?= e($phpVersion) ?></h5></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Server</div><h5 class="fw-bold mb-0 small"><?= e($serverSoftware) ?></h5></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">MySQL Version</div><h5 class="fw-bold mb-0"><?= e($mysqlVersion) ?></h5></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Database Size</div><h5 class="fw-bold mb-0"><?= e($dbSize) ?></h5></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Disk Total</div><h5 class="fw-bold mb-0"><?= e($diskTotal) ?></h5></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Disk Free</div><h5 class="fw-bold text-success mb-0"><?= e($diskFree) ?></h5></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Disk Used</div><h5 class="fw-bold text-warning mb-0"><?= e($diskUsed) ?></h5></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3"><div class="text-muted small mb-1">Tables</div><h5 class="fw-bold mb-0"><?= number_format($tableCount) ?></h5></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small mb-1">Memory Limit</div><h6 class="fw-bold mb-0"><?= e($memoryLimit) ?></h6></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small mb-1">Max Execution Time</div><h6 class="fw-bold mb-0"><?= e($maxExecutionTime) ?>s</h6></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small mb-1">Upload Max Size</div><h6 class="fw-bold mb-0"><?= e($uploadMaxSize) ?></h6></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small mb-1">Post Max Size</div><h6 class="fw-bold mb-0"><?= e($postMaxSize) ?></h6></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">FFmpeg Status</h6></div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    @if($ffmpegStatus === 'Available')
                        <span class="badge bg-success me-2 p-2"><i class="bi bi-check-lg"></i></span>
                    @else
                        <span class="badge bg-danger me-2 p-2"><i class="bi bi-x-lg"></i></span>
                    @endif
                    <div><div class="fw-bold"><?= e($ffmpegStatus) ?></div><div class="text-muted small"><?= e($ffmpegVersion) ?></div></div>
                </div>
                <div class="d-flex align-items-center">
                    @if($ffprobeStatus === 'Available')
                        <span class="badge bg-success me-2 p-2"><i class="bi bi-check-lg"></i></span>
                    @else
                        <span class="badge bg-danger me-2 p-2"><i class="bi bi-x-lg"></i></span>
                    @endif
                    <div><div class="fw-bold">FFprobe: <?= e($ffprobeStatus) ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">Services</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Queue</span><span class="badge bg-info badge-status"><?= e($queueStatus) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Cache Driver</span><span class="badge bg-primary badge-status"><?= e($cacheDriver) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Cache Status</span><span class="badge bg-secondary badge-status"><?= e($cacheStatus) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-transparent border-bottom"><h6 class="fw-bold mb-0">PHP Extensions</h6></div>
    <div class="card-body">
        <div class="row">
            @foreach($extensions as $name => $loaded)
            <div class="col-md-3 col-6 mb-2">
                <div class="d-flex align-items-center">
                    @if($loaded)
                        <span class="badge bg-success me-2"><i class="bi bi-check-lg"></i></span>
                    @else
                        <span class="badge bg-danger me-2"><i class="bi bi-x-lg"></i></span>
                    @endif
                    <span class="small"><?= e($name) ?></span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
