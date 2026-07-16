<?php $__layout = 'layouts.app'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Customize Channel</h4>
    <a href="<?= url('/creator/channel') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Channel Information</h6></div>
    <div class="card-body">
        <form method="POST" action="<?= url('/creator/channel/update') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="name" class="form-label">Channel Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="<?= e($channel['name']) ?>" required maxlength="100">
                <div class="form-text">Your channel name will be displayed to viewers.</div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" maxlength="5000"><?= e($channel['description'] ?? '') ?></textarea>
                <div class="form-text">Tell viewers about your channel. Max 5000 characters.</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="country" class="form-label">Country</label>
                    <select class="form-select" id="country" name="country">
                        <option value="">Select country...</option>
                        <?php
                        $countries = ['US'=>'United States','GB'=>'United Kingdom','CA'=>'Canada','AU'=>'Australia','DE'=>'Germany','FR'=>'France','JP'=>'Japan','IN'=>'India','BR'=>'Brazil','MX'=>'Mexico','KR'=>'South Korea','ES'=>'Spain','IT'=>'Italy','NL'=>'Netherlands','SE'=>'Sweden','PL'=>'Poland','RU'=>'Russia','NG'=>'Nigeria','ZA'=>'South Africa','AR'=>'Argentina'];
                        foreach ($countries as $code => $name):
                        ?>
                            <option value="<?= $code ?>" <?= ($channel['country'] ?? '') === $code ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="website" class="form-label">Website URL</label>
                    <input type="url" class="form-control" id="website" name="website" value="<?= e($channel['website'] ?? '') ?>" placeholder="https://example.com">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </form>
    </div>
</div>
