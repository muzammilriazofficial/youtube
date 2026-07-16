<footer class="yt-footer">
    <div class="yt-footer-grid">
        <div>
            <h6><svg viewBox="0 0 24 24" width="20" height="20" fill="#FF0000" style="vertical-align:middle;margin-right:4px"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 3.993L9 16z"/></svg> YouTube Clone</h6>
            <p style="font-size:13px;color:var(--yt-text-secondary);margin-top:8px">Watch, upload, and share videos with the world.</p>
            <div class="yt-footer-social">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-twitter-x"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-youtube"></i></a>
            </div>
        </div>
        <div>
            <h6>Explore</h6>
            <ul>
                <li><a href="<?= url('/trending') ?>">Trending</a></li>
                <li><a href="<?= url('/music') ?>">Music</a></li>
                <li><a href="<?= url('/gaming') ?>">Gaming</a></li>
                <li><a href="<?= url('/news') ?>">News</a></li>
                <li><a href="<?= url('/sports') ?>">Sports</a></li>
                <li><a href="<?= url('/learning') ?>">Learning</a></li>
            </ul>
        </div>
        <div>
            <h6>Legal</h6>
            <ul>
                <li><a href="<?= url('/about') ?>">About</a></li>
                <li><a href="<?= url('/terms') ?>">Terms of Service</a></li>
                <li><a href="<?= url('/privacy') ?>">Privacy Policy</a></li>
                <li><a href="<?= url('/contact') ?>">Contact Us</a></li>
            </ul>
        </div>
        <div>
            <h6>Account</h6>
            <ul>
                <li><a href="<?= url('/login') ?>">Sign In</a></li>
                <li><a href="<?= url('/register') ?>">Register</a></li>
                <li><a href="<?= url('/viewer/dashboard') ?>">Dashboard</a></li>
                <li><a href="<?= url('/viewer/playlists') ?>">Playlists</a></li>
            </ul>
        </div>
    </div>
    <div class="yt-footer-bottom">
        <p>&copy; <?= date('Y') ?> YouTube Clone. All rights reserved. Built with PHP.</p>
    </div>
</footer>
