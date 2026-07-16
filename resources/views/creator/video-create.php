<?php $__layout = 'layouts.app'; ?>

<style>
    .upload-page { max-width: 840px; margin: 0 auto; padding: 24px; }
    .upload-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .upload-header h2 { font-size: 16px; font-weight: 500; color: var(--yt-text-primary); margin: 0; }
    .upload-header a { font-size: 13px; color: var(--yt-text-secondary); text-decoration: none; display: flex; align-items: center; gap: 4px; }
    .upload-header a:hover { color: var(--yt-text-primary); }

    .upload-card {
        background: var(--yt-bg-elevated);
        border: 1px solid var(--yt-border);
        border-radius: 8px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .upload-card-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--yt-border);
        font-size: 14px;
        font-weight: 500;
        color: var(--yt-text-primary);
    }
    .upload-card-body { padding: 24px; }

    .upload-zone {
        border: 1px dashed var(--yt-border);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 24px;
        cursor: pointer;
        transition: all 0.2s;
        min-height: 220px;
        text-align: center;
    }
    .upload-zone:hover { border-color: var(--yt-text-secondary); background: rgba(255,255,255,0.03); }
    .upload-zone.dragging { border-color: #3ea6ff; background: rgba(62,166,255,0.05); }
    .upload-zone-icon {
        width: 64px; height: 64px;
        border: 2px solid var(--yt-text-secondary);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    }
    .upload-zone-icon svg { width: 28px; height: 28px; fill: var(--yt-text-secondary); }
    .upload-zone-text { font-size: 14px; color: var(--yt-text-secondary); margin-bottom: 16px; }
    .upload-zone-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 18px;
        background: transparent; border: none;
        font-size: 13px; font-weight: 500;
        color: #3ea6ff; cursor: pointer;
    }
    .upload-zone-btn:hover { background: rgba(62,166,255,0.1); }
    .upload-zone-filetypes { font-size: 12px; color: var(--yt-text-muted); margin-top: 12px; }

    .upload-zone.has-file {
        border-style: solid;
        border-color: var(--yt-success);
        background: rgba(42,166,64,0.05);
    }

    .upload-progress { margin-top: 20px; width: 100%; max-width: 400px; }
    .upload-progress-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
    .upload-progress-name { font-size: 13px; color: var(--yt-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%; }
    .upload-progress-pct { font-size: 13px; color: var(--yt-text-secondary); }
    .upload-progress-bar { width: 100%; height: 4px; background: var(--yt-border); border-radius: 2px; overflow: hidden; }
    .upload-progress-fill { height: 100%; background: #3ea6ff; border-radius: 2px; transition: width 0.3s; }
    .upload-progress-status { font-size: 12px; color: var(--yt-text-muted); margin-top: 4px; }

    .yt-field { margin-bottom: 20px; }
    .yt-field label {
        display: block; font-size: 14px; font-weight: 500;
        color: var(--yt-text-primary); margin-bottom: 6px;
    }
    .yt-field label .required { color: var(--yt-danger); }
    .yt-field input[type="text"],
    .yt-field textarea,
    .yt-field select {
        width: 100%; padding: 8px 12px;
        border: 1px solid var(--yt-border); border-radius: 4px;
        background: var(--yt-bg); color: var(--yt-text-primary);
        font-size: 14px; outline: none;
        transition: border-color 0.2s;
    }
    .yt-field input[type="text"]:focus,
    .yt-field textarea:focus,
    .yt-field select:focus { border-color: #3ea6ff; }
    .yt-field textarea { resize: vertical; min-height: 100px; font-family: inherit; }
    .yt-field select { appearance: auto; }
    .yt-field .hint { font-size: 12px; color: var(--yt-text-muted); margin-top: 4px; }
    .yt-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .thumb-upload {
        display: flex; gap: 16px; align-items: flex-start;
    }
    .thumb-drop {
        width: 200px; height: 112px;
        border: 1px dashed var(--yt-border); border-radius: 4px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s; flex-shrink: 0;
        overflow: hidden; position: relative;
    }
    .thumb-drop:hover { border-color: var(--yt-text-secondary); }
    .thumb-drop svg { width: 24px; height: 24px; fill: var(--yt-text-secondary); margin-bottom: 4px; }
    .thumb-drop span { font-size: 11px; color: var(--yt-text-muted); text-align: center; padding: 0 8px; }
    .thumb-drop input[type="file"] { display: none; }
    .thumb-drop img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    .thumb-info { font-size: 12px; color: var(--yt-text-muted); line-height: 1.6; }

    .yt-btn-primary {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 18px;
        background: #3ea6ff; color: #0f0f0f;
        border: none; font-size: 14px; font-weight: 500;
        cursor: pointer; transition: background 0.2s;
    }
    .yt-btn-primary:hover { background: #65b8ff; }
    .yt-btn-primary:disabled { background: var(--yt-border); color: var(--yt-text-muted); cursor: not-allowed; }
    .yt-btn-ghost {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 18px;
        background: transparent; color: var(--yt-text-primary);
        border: 1px solid var(--yt-border);
        font-size: 14px; font-weight: 500;
        cursor: pointer; text-decoration: none;
    }
    .yt-btn-ghost:hover { background: var(--yt-bg-hover); }

    .upload-actions { display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1px solid var(--yt-border); }

    .tips-list { list-style: none; padding: 0; margin: 0; }
    .tips-list li {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; color: var(--yt-text-secondary);
        padding: 6px 0;
    }
    .tips-list li svg { width: 16px; height: 16px; fill: var(--yt-success); flex-shrink: 0; }

    .yt-alert {
        padding: 12px 16px; border-radius: 8px;
        margin-bottom: 20px; font-size: 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .yt-alert-success { background: rgba(42,166,64,0.1); color: #2ba640; border: 1px solid rgba(42,166,64,0.2); }
    .yt-alert-error { background: rgba(220,53,69,0.1); color: #dc3545; border: 1px solid rgba(220,53,69,0.2); }
</style>

<div class="upload-page">
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="yt-alert yt-alert-success">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <?= e($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="yt-alert yt-alert-error">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <?= e($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="upload-header">
        <h2>Upload videos</h2>
        <a href="<?= url('/creator/videos') ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </a>
    </div>

    <form method="POST" action="<?= url('/creator/videos/store') ?>" enctype="multipart/form-data" id="videoForm">
        <?= csrf_field() ?>

        <div class="upload-card">
            <div class="upload-card-body" style="padding:40px;">
                <div class="upload-zone" id="dropZone">
                    <div class="upload-zone-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
                    </div>
                    <div class="upload-zone-text">Drag and drop video files to upload</div>
                    <button type="button" class="upload-zone-btn" onclick="document.getElementById('videoFileInput').click()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
                        SELECT FILES
                    </button>
                    <div class="upload-zone-filetypes">Your videos will be private until you publish them. Max 10GB</div>
                    <input type="file" id="videoFileInput" name="video_file" accept="video/mp4,video/webm,video/quicktime" style="display:none;">
                    <div id="uploadProgress" class="upload-progress" style="display:none;">
                        <div class="upload-progress-header">
                            <span class="upload-progress-name" id="uploadFileName"></span>
                            <span class="upload-progress-pct" id="uploadPercent">0%</span>
                        </div>
                        <div class="upload-progress-bar"><div class="upload-progress-fill" id="uploadBar" style="width:0%"></div></div>
                        <div class="upload-progress-status" id="uploadStatus"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="upload-card">
            <div class="upload-card-header">Details</div>
            <div class="upload-card-body">
                <div class="yt-field">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required maxlength="100" placeholder="Add a title that describes your video">
                    <div class="hint">Max 100 characters</div>
                </div>
                <div class="yt-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" maxlength="5000" rows="4" placeholder="Tell viewers about your video"></textarea>
                    <div class="hint">Max 5000 characters</div>
                </div>
                <div class="yt-field">
                    <label>Thumbnail</label>
                    <div class="thumb-upload">
                        <div class="thumb-drop" id="thumbDrop" onclick="document.getElementById('thumbnail').click()">
                            <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            <span>Upload thumbnail</span>
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp">
                        </div>
                        <div class="thumb-info">
                            Your thumbnail is the first thing viewers see when they come across your video across YouTube. A good thumbnail, along with a great title, gives viewers a preview of what your video is about.<br><br>
                            Recommended: 1280x720px. JPEG, PNG, GIF or WebP. Max 2MB.
                        </div>
                    </div>
                </div>
                <div class="yt-field-row">
                    <div class="yt-field">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="yt-field">
                        <label for="visibility">Visibility</label>
                        <select id="visibility" name="visibility">
                            <option value="public">Public</option>
                            <option value="unlisted">Unlisted</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                </div>
                <div class="yt-field">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" placeholder="Add tags to help people find your video">
                    <div class="hint">Separate tags with commas</div>
                </div>
            </div>
            <div class="upload-actions">
                <a href="<?= url('/creator/videos') ?>" class="yt-btn-ghost">Cancel</a>
                <button type="submit" class="yt-btn-primary" id="submitBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg>
                    Upload
                </button>
            </div>
        </div>
    </form>

    <div class="upload-card" style="margin-top:16px;">
        <div class="upload-card-header">Checklist</div>
        <div class="upload-card-body">
            <ul class="tips-list">
                <li><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>Check that the title and description are accurate</li>
                <li><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>Upload a high-resolution thumbnail</li>
                <li><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>Add your video to a playlist</li>
                <li><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>Add tags so viewers can find your video</li>
            </ul>
        </div>
    </div>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('videoFileInput');
const thumbInput = document.getElementById('thumbnail');
const thumbDrop = document.getElementById('thumbDrop');

dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragging'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragging'));
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragging');
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        showFileSelected(e.dataTransfer.files[0]);
    }
});
fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) showFileSelected(e.target.files[0]);
});

function showFileSelected(file) {
    dropZone.classList.add('has-file');
    dropZone.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="#2ba640"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'
        + '<div style="font-size:14px;font-weight:500;margin-top:8px;color:var(--yt-text-primary);">' + file.name + '</div>'
        + '<div style="font-size:12px;color:var(--yt-text-secondary);margin-top:4px;">' + formatSize(file.size) + '</div>'
        + '<input type="file" id="videoFileInput" accept="video/mp4,video/webm,video/quicktime" style="display:none;">';
    const newInput = document.getElementById('videoFileInput');
    newInput.addEventListener('change', (e) => { if (e.target.files.length > 0) showFileSelected(e.target.files[0]); });
    document.getElementById('uploadProgress').style.display = 'none';
}

thumbInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        const reader = new FileReader();
        reader.onload = (ev) => {
            thumbDrop.innerHTML = '<img src="' + ev.target.result + '" alt="Thumbnail"><input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">';
            document.getElementById('thumbnail').addEventListener('change', arguments.callee);
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024, sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
