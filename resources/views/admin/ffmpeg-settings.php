@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">FFmpeg Settings</h4>
</div>

<?php $s = $settings ?? []; ?>

<div class="card table-card">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('/admin/ffmpeg-settings/update') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">FFmpeg Path</label><input type="text" class="form-control font-monospace" name="ffmpeg_path" value="<?= e($s['ffmpeg_path'] ?? '/usr/bin/ffmpeg') ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">FFprobe Path</label><input type="text" class="form-control font-monospace" name="ffprobe_path" value="<?= e($s['ffprobe_path'] ?? '/usr/bin/ffprobe') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Default Video Codec</label><input type="text" class="form-control" name="default_video_codec" value="<?= e($s['default_video_codec'] ?? 'libx264') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Default Audio Codec</label><input type="text" class="form-control" name="default_audio_codec" value="<?= e($s['default_audio_codec'] ?? 'aac') ?>"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Thumbnail Timestamp</label><input type="text" class="form-control" name="thumbnail_timestamp" value="<?= e($s['thumbnail_timestamp'] ?? '00:00:01') ?>"></div>
                <div class="col-12"><label class="form-label fw-semibold">Transcoding Presets</label><input type="text" class="form-control" name="transcoding_presets" value="<?= e($s['transcoding_presets'] ?? 'ultrafast,superfast,veryfast,faster,fast,medium') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save FFmpeg Settings</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
