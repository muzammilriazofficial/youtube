<?php
$commentId = $comment['id'] ?? 0;
$userId = $comment['user_id'] ?? 0;
$username = $comment['username'] ?? 'User';
$userAvatar = $comment['user_avatar'] ?? '';
$body = $comment['body'] ?? '';
$likeCount = $comment['like_count'] ?? 0;
$createdAt = $comment['created_at'] ?? '';
$replies = $comment['replies'] ?? [];
$videoId = $video['id'] ?? 0;
$initial = strtoupper(substr($username, 0, 1));
?>
<div class="yt-comment" id="comment-<?= $commentId ?>">
    <div class="avatar">
        <?php if ($userAvatar): ?>
            <img src="<?= url(e($userAvatar)) ?>" alt="<?= e($username) ?>">
        <?php else: ?>
            <?= $initial ?>
        <?php endif; ?>
    </div>
    <div class="yt-comment-body">
        <div class="yt-comment-header">
            <span class="author">@<?= e($username) ?></span>
            <span class="time"><?= time_ago($createdAt) ?></span>
        </div>
        <p class="yt-comment-text"><?= e($body) ?></p>
        <div class="yt-comment-toolbar">
            <button class="yt-comment-like" data-comment-like="<?= $commentId ?>">
                <i class="bi bi-hand-thumbs-up"></i>
                <span class="like-count"><?= $likeCount ?></span>
            </button>
            <span class="like-divider"></span>
            <button class="yt-comment-dislike"><i class="bi bi-hand-thumbs-down"></i></button>
            <button class="yt-comment-reply-btn" data-reply-toggle="<?= $commentId ?>">Reply</button>
        </div>

        <div class="yt-reply-form" style="display:none;margin-top:12px">
            <form class="yt-comment-form" data-video-id="<?= $videoId ?>" data-parent-id="<?= $commentId ?>">
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                <input type="text" class="yt-comment-input" placeholder="Add a reply..." required>
                <div class="yt-comment-actions show">
                    <button type="button" class="yt-comment-cancel" onclick="this.closest('.yt-reply-form').style.display='none'">Cancel</button>
                    <button type="submit" class="yt-comment-submit">Reply</button>
                </div>
            </form>
        </div>

        <?php if (!empty($replies)): ?>
            <button class="yt-show-replies" data-show-replies="<?= $commentId ?>">
                <i class="bi bi-chevron-down"></i> <?= count($replies) ?> <?= count($replies) === 1 ? 'reply' : 'replies' ?>
            </button>
            <div class="yt-comment-replies" style="display:none">
                <?php foreach ($replies as $reply): ?>
                    <?php
                    $replyId = $reply['id'] ?? 0;
                    $replyUser = $reply['username'] ?? 'User';
                    $replyAvatar = $reply['user_avatar'] ?? '';
                    $replyBody = $reply['body'] ?? '';
                    $replyLikes = $reply['like_count'] ?? 0;
                    $replyTime = $reply['created_at'] ?? '';
                    ?>
                    <div class="yt-comment" id="comment-<?= $replyId ?>">
                        <div class="avatar">
                            <?php if ($replyAvatar): ?>
                                <img src="<?= url(e($replyAvatar)) ?>" alt="<?= e($replyUser) ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($replyUser, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="yt-comment-body">
                            <div class="yt-comment-header">
                                <span class="author">@<?= e($replyUser) ?></span>
                                <span class="time"><?= time_ago($replyTime) ?></span>
                            </div>
                            <p class="yt-comment-text"><?= e($replyBody) ?></p>
                            <div class="yt-comment-toolbar">
                                <button class="yt-comment-like" data-comment-like="<?= $replyId ?>">
                                    <i class="bi bi-hand-thumbs-up"></i>
                                    <span class="like-count"><?= $replyLikes ?></span>
                                </button>
                                <span class="like-divider"></span>
                                <button class="yt-comment-dislike"><i class="bi bi-hand-thumbs-down"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
