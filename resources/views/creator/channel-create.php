<?php $__layout = 'layouts.app'; ?>

<style>
    .channel-create-wrap {
        max-width: 620px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .channel-create-card {
        background: var(--yt-spec-brand-background-solid, #fff);
        border: 1px solid var(--yt-spec-10-percent-layer, #e5e5e5);
        border-radius: 12px;
        padding: 32px 40px 40px;
        text-align: center;
    }
    .channel-create-card h2 {
        font-size: 24px;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--yt-spec-text-primary, #0f0f0f);
    }
    .channel-create-card .subtitle {
        font-size: 14px;
        color: var(--yt-spec-text-secondary, #606060);
        margin-bottom: 28px;
    }
    .channel-avatar-upload {
        position: relative;
        width: 96px;
        height: 96px;
        margin: 0 auto 24px;
        cursor: pointer;
    }
    .channel-avatar-upload .avatar-circle {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: var(--yt-spec-badge-chip-background, #f2f2f2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid var(--yt-spec-10-percent-layer, #e5e5e5);
        transition: border-color 0.2s;
    }
    .channel-avatar-upload:hover .avatar-circle {
        border-color: var(--yt-spec-text-primary, #0f0f0f);
    }
    .channel-avatar-upload .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .channel-avatar-upload .avatar-circle svg {
        width: 40px;
        height: 40px;
        fill: var(--yt-spec-text-secondary, #606060);
    }
    .channel-avatar-upload .camera-badge {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(0,0,0,0.65);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .channel-avatar-upload .camera-badge svg {
        width: 16px;
        height: 16px;
        fill: #fff;
    }
    .channel-avatar-upload input[type="file"] {
        display: none;
    }
    .channel-create-card .form-group {
        text-align: left;
        margin-bottom: 20px;
    }
    .channel-create-card .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: var(--yt-spec-text-primary, #0f0f0f);
        margin-bottom: 6px;
    }
    .channel-create-card .form-group input[type="text"],
    .channel-create-card .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--yt-spec-text-input-border, #ccc);
        border-radius: 4px;
        font-size: 14px;
        background: var(--yt-spec-general-background-a, #f9f9f9);
        color: var(--yt-spec-text-primary, #0f0f0f);
        outline: none;
        transition: border-color 0.2s;
    }
    .channel-create-card .form-group input[type="text"]:focus,
    .channel-create-card .form-group textarea:focus {
        border-color: #065fd4;
        background: var(--yt-spec-brand-background-solid, #fff);
    }
    .channel-create-card .form-group .handle-input-wrap {
        display: flex;
        align-items: center;
        border: 1px solid var(--yt-spec-text-input-border, #ccc);
        border-radius: 4px;
        background: var(--yt-spec-general-background-a, #f9f9f9);
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .channel-create-card .form-group .handle-input-wrap:focus-within {
        border-color: #065fd4;
        background: var(--yt-spec-brand-background-solid, #fff);
    }
    .channel-create-card .form-group .handle-input-wrap .prefix {
        padding: 8px 0 8px 12px;
        font-size: 14px;
        color: var(--yt-spec-text-secondary, #606060);
        white-space: nowrap;
        user-select: none;
    }
    .channel-create-card .form-group .handle-input-wrap input {
        border: none;
        background: transparent;
        padding: 8px 12px 8px 4px;
        flex: 1;
        min-width: 0;
    }
    .channel-create-card .form-group .handle-input-wrap input:focus {
        border: none;
        outline: none;
        box-shadow: none;
    }
    .channel-create-card .form-group .hint {
        font-size: 12px;
        color: var(--yt-spec-text-secondary, #606060);
        margin-top: 4px;
    }
    .channel-create-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid var(--yt-spec-10-percent-layer, #e5e5e5);
    }
    .channel-create-actions .btn-cancel {
        padding: 8px 16px;
        border-radius: 18px;
        font-size: 14px;
        font-weight: 500;
        background: transparent;
        color: var(--yt-spec-text-primary, #0f0f0f);
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .channel-create-actions .btn-cancel:hover {
        background: var(--yt-spec-badge-chip-background, #f2f2f2);
    }
    .channel-create-actions .btn-create {
        padding: 8px 16px;
        border-radius: 18px;
        font-size: 14px;
        font-weight: 500;
        background: #065fd4;
        color: #fff;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }
    .channel-create-actions .btn-create:hover {
        background: #0b57c4;
    }
    .channel-create-actions .btn-create:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    .yt-alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .yt-alert-error {
        background: #fce8e6;
        color: #c5221f;
        border: 1px solid #f5c6cb;
    }
</style>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="channel-create-wrap">
        <div class="yt-alert yt-alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <?= e($_SESSION['flash_error']) ?>
        </div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="channel-create-wrap">
    <div class="channel-create-card">
        <h2>How you'll appear</h2>
        <p class="subtitle">This information will appear on your public channel and can be changed later.</p>

        <form method="POST" action="<?= url('/creator/channel/store') ?>" enctype="multipart/form-data" id="channelCreateForm">
            <?= csrf_field() ?>

            <div class="channel-avatar-upload" onclick="document.getElementById('avatarInput').click()" title="Upload profile picture">
                <div class="avatar-circle" id="avatarPreview">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= e($user['avatar']) ?>" alt="Avatar">
                    <?php else: ?>
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    <?php endif; ?>
                </div>
                <div class="camera-badge">
                    <svg viewBox="0 0 24 24"><path d="M12 15.2c1.77 0 3.2-1.43 3.2-3.2S13.77 8.8 12 8.8 8.8 10.23 8.8 12s1.43 3.2 3.2 3.2zM9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9z"/></svg>
                </div>
                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp">
            </div>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= e(old('name', $user['username'] ?? '')) ?>" required maxlength="100" autofocus placeholder="Enter your channel name">
                <div class="hint">Choose a unique name for your channel. You can change this later.</div>
            </div>

            <div class="form-group">
                <label for="handle">Handle</label>
                <div class="handle-input-wrap">
                    <span class="prefix">@</span>
                    <input type="text" id="handle" name="handle" value="<?= e(old('handle', $user['username'] ?? '')) ?>" maxlength="30" placeholder="yourhandle">
                </div>
                <div class="hint">Your handle is unique and can be used to mention your channel in comments and elsewhere.</div>
            </div>

            <div class="channel-create-actions">
                <a href="<?= url('/') ?>" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-create" id="createBtn" disabled>Create Channel</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const nameInput = document.getElementById('name');
    const handleInput = document.getElementById('handle');
    const createBtn = document.getElementById('createBtn');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const form = document.getElementById('channelCreateForm');

    function checkReady() {
        createBtn.disabled = !nameInput.value.trim();
    }

    nameInput.addEventListener('input', function() {
        checkReady();
    });

    handleInput.addEventListener('input', function() {
        let val = handleInput.value.replace(/[^a-zA-Z0-9_]/g, '');
        handleInput.value = val;
    });

    avatarInput.addEventListener('change', function() {
        if (avatarInput.files && avatarInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
            };
            reader.readAsDataURL(avatarInput.files[0]);
        }
    });

    checkReady();
})();
</script>
