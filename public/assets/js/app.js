/* ============================================
   YouTube Clone - Main JavaScript
   Complete Production JS (Vanilla ES6+)
   ============================================ */

'use strict';

/* ── CSRF Token Helper ── */
const BASE_URL = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/$/, '') || '';

const CSRF = {
    getToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    },
    getField() {
        return `<input type="hidden" name="_token" value="${this.getToken()}">`;
    }
};

/* ── AJAX Helper ── */
const Ajax = {
    async request(url, options = {}) {
        const defaults = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': CSRF.getToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        };

        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.body = new URLSearchParams(options.body).toString();
        }

        const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };

        try {
            const response = await fetch(url, config);
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();
                if (!response.ok) {
                    throw { status: response.status, ...data };
                }
                return data;
            }

            if (!response.ok) {
                throw { status: response.status, message: response.statusText };
            }

            return await response.text();
        } catch (error) {
            if (error.status) throw error;
            throw { status: 0, message: 'Network error. Please check your connection.' };
        }
    },

    post(url, data = {}) {
        return this.request(url, { method: 'POST', body: data });
    },

    put(url, data = {}) {
        return this.request(url, { method: 'PUT', body: data });
    },

    delete(url, data = {}) {
        return this.request(url, { method: 'DELETE', body: data });
    },

    get(url) {
        return this.request(url, { method: 'GET' });
    }
};

/* ── Toast Notification System ── */
const Toast = {
    container: null,

    init() {
        if (this.container) return;
        this.container = document.createElement('div');
        this.container.className = 'yt-toast-container';
        document.body.appendChild(this.container);
    },

    show(message, type = 'info', duration = 4000) {
        this.init();

        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        const toast = document.createElement('div');
        toast.className = `yt-toast yt-toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon"><i class="bi ${icons[type] || icons.info}"></i></span>
            <span class="toast-text">${this.escapeHtml(message)}</span>
            <span class="toast-close"><i class="bi bi-x"></i></span>
        `;

        toast.querySelector('.toast-close').addEventListener('click', () => this.remove(toast));
        this.container.appendChild(toast);

        setTimeout(() => this.remove(toast), duration);
    },

    remove(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    },

    success(msg) { this.show(msg, 'success'); },
    error(msg) { this.show(msg, 'error'); },
    warning(msg) { this.show(msg, 'warning'); },
    info(msg) { this.show(msg, 'info'); },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

/* ── Theme Toggle ── */
const Theme = {
    init() {
        const saved = localStorage.getItem('theme') || 'dark';
        this.apply(saved);

        document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
            btn.addEventListener('click', () => this.toggle());
        });
    },

    apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    },

    toggle() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        this.apply(current === 'dark' ? 'light' : 'dark');
    },

    get current() {
        return document.documentElement.getAttribute('data-theme') || 'dark';
    }
};

/* ── Sidebar Controller ── */
const Sidebar = {
    sidebar: null,
    overlay: null,
    main: null,

    init() {
        this.sidebar = document.querySelector('.yt-sidebar');
        this.overlay = document.querySelector('.yt-sidebar-overlay');
        this.main = document.querySelector('.yt-main');

        if (!this.sidebar) return;

        document.querySelectorAll('[data-sidebar-toggle]').forEach(btn => {
            btn.addEventListener('click', () => this.toggle());
        });

        document.querySelectorAll('[data-sidebar-collapse]').forEach(btn => {
            btn.addEventListener('click', () => this.collapse());
        });

        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        const saved = localStorage.getItem('sidebar-collapsed');
        if (saved === 'false') {
            this.sidebar.classList.remove('collapsed');
        }
    },

    toggle() {
        this.collapse();
    },

    collapse() {
        this.sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar-collapsed', this.sidebar.classList.contains('collapsed'));
    },

    close() {
        this.sidebar?.classList.remove('mobile-show');
        this.overlay?.classList.remove('show');
        document.body.style.overflow = '';
    }
};

/* ── Search Controller ── */
const Search = {
    input: null,
    form: null,
    wrapper: null,
    autocomplete: null,
    debounceTimer: null,

    init() {
        this.form = document.querySelector('.yt-search-form');
        this.wrapper = document.querySelector('.yt-search-wrapper');

        if (!this.wrapper) return;

        this.input = this.wrapper.querySelector('.yt-search-input');
        this.autocomplete = this.wrapper.querySelector('.yt-search-autocomplete');

        if (!this.input) return;

        this.input.addEventListener('input', () => this.onInput());
        this.input.addEventListener('focus', () => this.onFocus());
        this.input.addEventListener('keydown', (e) => this.onKeydown(e));

        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) this.hideAutocomplete();
        });

        document.querySelectorAll('[data-search-expand]').forEach(btn => {
            btn.addEventListener('click', () => this.expand());
        });

        document.querySelectorAll('[data-search-close]').forEach(btn => {
            btn.addEventListener('click', () => this.collapse());
        });
    },

    onInput() {
        clearTimeout(this.debounceTimer);
        const query = this.input.value.trim();

        if (query.length < 2) {
            this.hideAutocomplete();
            return;
        }

        this.debounceTimer = setTimeout(() => this.fetchSuggestions(query), 300);
    },

    onFocus() {
        const query = this.input.value.trim();
        if (query.length >= 2 && this.autocomplete?.children.length > 0) {
            this.showAutocomplete();
        }
    },

    onKeydown(e) {
        if (e.key === 'Escape') {
            this.hideAutocomplete();
            if (this.input.value === '') this.collapse();
        }
    },

    async fetchSuggestions(query) {
        try {
            const data = await Ajax.get(`${BASE_URL}/search/suggest?q=${encodeURIComponent(query)}`);
            if (data.suggestions && this.autocomplete) {
                this.autocomplete.innerHTML = data.suggestions.map(s =>
                    `<a href="${BASE_URL}/search?q=${encodeURIComponent(s)}" class="yt-search-suggestion">
                        <i class="bi bi-search"></i><span>${this.escapeHtml(s)}</span>
                    </a>`
                ).join('');
                this.showAutocomplete();
            }
        } catch {
            this.hideAutocomplete();
        }
    },

    showAutocomplete() {
        this.autocomplete?.classList.add('show');
    },

    hideAutocomplete() {
        this.autocomplete?.classList.remove('show');
    },

    expand() {
        const searchContainers = document.querySelectorAll('.yt-search');
        searchContainers.forEach(s => s.classList.add('expanded'));
        const mobileInput = document.querySelector('.yt-search.expanded .yt-search-input');
        if (mobileInput) mobileInput.focus();
    },

    collapse() {
        document.querySelectorAll('.yt-search').forEach(s => s.classList.remove('expanded'));
    },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

/* ── Dropdown Controller ── */
const Dropdowns = {
    active: null,

    init() {
        document.addEventListener('click', (e) => {
            const toggleBtn = e.target.closest('[data-dropdown-toggle]');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();
                const targetId = toggleBtn.getAttribute('data-dropdown-toggle');
                const dropdown = document.getElementById(targetId);
                if (!dropdown) return;

                if (this.active && this.active !== dropdown) {
                    this.active.classList.remove('show');
                }

                dropdown.classList.toggle('show');
                this.active = dropdown.classList.contains('show') ? dropdown : null;
                return;
            }

            if (this.active && !e.target.closest('.yt-notif-dropdown, .yt-user-dropdown, .yt-video-options-dropdown')) {
                this.active.classList.remove('show');
                this.active = null;
            }
        });
    }
};

/* ── Notifications Polling ── */
const Notifications = {
    interval: null,
    badge: null,
    lastCount: 0,

    init() {
        this.badge = document.querySelector('.yt-notif-count');
        const bell = document.querySelector('[data-notif-poll]');
        if (!bell) return;

        this.poll();
        this.interval = setInterval(() => this.poll(), 30000);
    },

    async poll() {
        try {
            const data = await Ajax.get(`${BASE_URL}/viewer/notifications/check`);
            if (data.count !== undefined && data.count !== this.lastCount) {
                this.lastCount = data.count;
                this.updateBadge(data.count);
                if (data.count > 0 && this.lastCount === 0) {
                    Toast.info('You have new notifications');
                }
            }
        } catch { /* silent */ }
    },

    updateBadge(count) {
        if (!this.badge) return;
        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.style.display = 'flex';
        } else {
            this.badge.style.display = 'none';
        }
    }
};

/* ── Like / Dislike Toggle ── */
const VideoActions = {
    init() {
        document.querySelectorAll('[data-like-video]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleLike(btn);
            });
        });

        document.querySelectorAll('[data-subscribe]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSubscribe(btn);
            });
        });

        document.querySelectorAll('[data-watch-later]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleWatchLater(btn);
            });
        });
    },

    async toggleLike(btn) {
        const videoId = btn.getAttribute('data-like-video');
        try {
            const data = await Ajax.post(`${BASE_URL}/viewer/like-video`, { video_id: videoId });
            if (data.status === 'liked' || data.liked) {
                btn.classList.add('active');
                btn.querySelector('.like-count').textContent = data.like_count || '';
                Toast.success('Video liked');
            } else if (data.status === 'unliked' || data.unliked) {
                btn.classList.remove('active');
                btn.querySelector('.like-count').textContent = data.like_count || '';
            }
        } catch (err) {
            Toast.error(err.message || 'Could not like video');
        }
    },

    async toggleSubscribe(btn) {
        const channelId = btn.getAttribute('data-subscribe');
        try {
            const data = await Ajax.post(`${BASE_URL}/viewer/subscriptions/toggle`, { channel_id: channelId });
            if (data.status === 'subscribed') {
                btn.classList.add('subscribed');
                btn.classList.remove('not-subscribed');
                btn.textContent = 'Subscribed';
                Toast.success('Subscribed!');
            } else if (data.status === 'unsubscribed') {
                btn.classList.remove('subscribed');
                btn.classList.add('not-subscribed');
                btn.textContent = 'Subscribe';
            }
        } catch (err) {
            Toast.error(err.message || 'Could not update subscription');
        }
    },

    async toggleWatchLater(btn) {
        const videoId = btn.getAttribute('data-watch-later');
        try {
            const data = await Ajax.post(`${BASE_URL}/viewer/watch-later/add`, { video_id: videoId });
            if (data.status === 'added') {
                btn.innerHTML = '<i class="bi bi-clock-fill"></i> Saved';
                Toast.success('Added to Watch Later');
            } else if (data.status === 'removed') {
                btn.innerHTML = '<i class="bi bi-clock"></i> Watch Later';
                Toast.info('Removed from Watch Later');
            }
        } catch (err) {
            Toast.error(err.message || 'Could not update Watch Later');
        }
    }
};

/* ── Comments System ── */
const Comments = {
    init() {
        document.querySelectorAll('.yt-comment-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submit(form);
            });
        });

        document.querySelectorAll('[data-show-replies]').forEach(btn => {
            btn.addEventListener('click', () => this.showReplies(btn));
        });

        document.querySelectorAll('[data-reply-toggle]').forEach(btn => {
            btn.addEventListener('click', () => this.toggleReplyForm(btn));
        });

        document.querySelectorAll('[data-comment-like]').forEach(btn => {
            btn.addEventListener('click', () => this.likeComment(btn));
        });
    },

    async submit(form) {
        const input = form.querySelector('.yt-comment-input');
        const body = input.value.trim();
        if (!body) return;

        const videoId = form.getAttribute('data-video-id');
        const parentId = form.getAttribute('data-parent-id') || '';

        try {
            const payload = { video_id: videoId, body };
            if (parentId) payload.parent_id = parentId;

            const data = await Ajax.post(`${BASE_URL}/viewer/comment`, payload);
            if (data.comment) {
                input.value = '';
                Toast.success('Comment added');

                if (parentId) {
                    const repliesContainer = document.querySelector(`#comment-${parentId} .yt-comment-replies`);
                    if (repliesContainer) {
                        repliesContainer.insertAdjacentHTML('beforeend', this.renderComment(data.comment));
                    }
                } else {
                    const commentsSection = document.querySelector('.yt-comments-list');
                    if (commentsSection) {
                        commentsSection.insertAdjacentHTML('afterbegin', this.renderComment(data.comment));
                    }
                }
            }
        } catch (err) {
            Toast.error(err.message || 'Could not post comment');
        }
    },

    async likeComment(btn) {
        const commentId = btn.getAttribute('data-comment-like');
        try {
            const data = await Ajax.post(`${BASE_URL}/viewer/comment/like`, { comment_id: commentId });
            if (data.status === 'ok') {
                btn.classList.add('active');
                btn.querySelector('.like-count').textContent = data.like_count;
            }
        } catch { /* silent */ }
    },

    showReplies(btn) {
        const commentId = btn.getAttribute('data-show-replies');
        const repliesDiv = document.querySelector(`#comment-${commentId} .yt-comment-replies`);
        if (repliesDiv) {
            repliesDiv.style.display = repliesDiv.style.display === 'none' ? 'block' : 'none';
        }
    },

    toggleReplyForm(btn) {
        const commentId = btn.getAttribute('data-reply-toggle');
        const replyForm = document.querySelector(`#comment-${commentId} .yt-reply-form`);
        if (replyForm) {
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            if (replyForm.style.display === 'block') {
                replyForm.querySelector('input')?.focus();
            }
        }
    },

    renderComment(comment) {
        const initial = (comment.username || 'U').charAt(0).toUpperCase();
        return `
        <div class="yt-comment" id="comment-${comment.id}">
            <div class="avatar">${initial}</div>
            <div class="yt-comment-body">
                <div class="yt-comment-header">
                    <span class="author">${this.escapeHtml(comment.username || 'User')}</span>
                    <span class="time">${comment.time_ago || ''}</span>
                </div>
                <p class="yt-comment-text">${this.escapeHtml(comment.body)}</p>
                <div class="yt-comment-toolbar">
                    <button class="yt-comment-like" data-comment-like="${comment.id}">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span class="like-count">${comment.like_count || 0}</span>
                    </button>
                    <span class="like-divider"></span>
                    <button class="yt-comment-dislike"><i class="bi bi-hand-thumbs-down"></i></button>
                    <button class="yt-comment-reply-btn" data-reply-toggle="${comment.id}">Reply</button>
                </div>
            </div>
        </div>`;
    },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

/* ── Report Modal ── */
const Report = {
    modal: null,

    init() {
        document.querySelectorAll('[data-report]').forEach(btn => {
            btn.addEventListener('click', () => this.open(btn));
        });
    },

    open(btn) {
        const type = btn.getAttribute('data-report-type') || 'video';
        const id = btn.getAttribute('data-report');
        const modal = document.getElementById('reportModal');
        if (!modal) return;

        modal.querySelector('[name="reportable_type"]').value = type;
        modal.querySelector('[name="reportable_id"]').value = id;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    },

    close() {
        const modal = document.getElementById('reportModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    },

    async submit(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            await Ajax.post(`${BASE_URL}/viewer/report`, data);
            Toast.success('Report submitted. Thank you!');
            this.close();
            form.reset();
        } catch (err) {
            Toast.error(err.message || 'Could not submit report');
        }
    }
};

/* ── Playlist Management ── */
const Playlists = {
    async addVideo(playlistId, videoId) {
        try {
            await Ajax.post(`${BASE_URL}/viewer/playlists/add-video`, {
                playlist_id: playlistId,
                video_id: videoId
            });
            Toast.success('Video added to playlist');
        } catch (err) {
            Toast.error(err.message || 'Could not add video');
        }
    },

    async removeVideo(playlistId, videoId) {
        try {
            await Ajax.post(`${BASE_URL}/viewer/playlists/remove-video`, {
                playlist_id: playlistId,
                video_id: videoId
            });
            Toast.info('Video removed from playlist');
            const el = document.querySelector(`[data-playlist-video="${playlistId}-${videoId}"]`);
            if (el) el.remove();
        } catch (err) {
            Toast.error(err.message || 'Could not remove video');
        }
    }
};

/* ── Confirmation Modal ── */
const ConfirmModal = {
    init() {
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const message = btn.getAttribute('data-confirm') || 'Are you sure?';
                this.show(message).then(confirmed => {
                    if (confirmed) {
                        if (btn.tagName === 'A') {
                            window.location.href = btn.href;
                        } else if (btn.getAttribute('data-confirm-action')) {
                            const action = btn.getAttribute('data-confirm-action');
                            if (typeof window[action] === 'function') window[action]();
                        } else {
                            btn.click();
                        }
                    }
                });
            });
        });
    },

    show(message) {
        return new Promise(resolve => {
            const existing = document.getElementById('ytConfirmModal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'ytConfirmModal';
            modal.className = 'yt-modal-backdrop show';
            modal.innerHTML = `
                <div class="yt-modal">
                    <div class="yt-modal-header">
                        <h5>Confirm</h5>
                        <button class="yt-modal-close" data-close><i class="bi bi-x"></i></button>
                    </div>
                    <div class="yt-modal-body"><p>${Toast.escapeHtml(message)}</p></div>
                    <div class="yt-modal-footer">
                        <button class="yt-btn yt-btn-ghost" data-cancel>Cancel</button>
                        <button class="yt-btn yt-btn-danger" data-confirm>Confirm</button>
                    </div>
                </div>`;

            document.body.appendChild(modal);

            const cleanup = (result) => {
                modal.classList.remove('show');
                setTimeout(() => modal.remove(), 200);
                resolve(result);
            };

            modal.querySelector('[data-confirm]').addEventListener('click', () => cleanup(true));
            modal.querySelector('[data-cancel]').addEventListener('click', () => cleanup(false));
            modal.querySelector('[data-close]').addEventListener('click', () => cleanup(false));
            modal.addEventListener('click', (e) => { if (e.target === modal) cleanup(false); });
        });
    }
};

/* ── Infinite Scroll ── */
const InfiniteScroll = {
    loading: false,
    page: 1,
    url: null,
    container: null,
    loader: null,

    init(containerSelector, loadUrl) {
        this.container = document.querySelector(containerSelector);
        this.url = loadUrl;
        if (!this.container) return;

        this.loader = document.createElement('div');
        this.loader.className = 'yt-infinite-loader';
        this.loader.innerHTML = '<div class="loader-dot"></div><div class="loader-dot"></div><div class="loader-dot"></div>';
        this.container.after(this.loader);

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.loading) this.loadMore();
            });
        }, { rootMargin: '200px' });

        observer.observe(this.loader);
    },

    async loadMore() {
        this.loading = true;
        this.page++;

        try {
            const data = await Ajax.get(`${this.url}?page=${this.page}`);
            if (data.html) {
                this.container.insertAdjacentHTML('beforeend', data.html);
            }
            if (!data.has_more) {
                this.loader.remove();
            }
        } catch {
            this.loader.innerHTML = '<p class="text-muted small">Failed to load more</p>';
        }

        this.loading = false;
    }
};

/* ── Form Validation ── */
const FormValidator = {
    init(form) {
        if (typeof form === 'string') form = document.querySelector(form);
        if (!form) return;

        form.addEventListener('submit', (e) => {
            if (!this.validate(form)) {
                e.preventDefault();
            }
        });

        form.querySelectorAll('[data-validate]').forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) this.validateField(input);
            });
        });
    },

    validate(form) {
        let valid = true;
        form.querySelectorAll('[data-validate]').forEach(input => {
            if (!this.validateField(input)) valid = false;
        });
        return valid;
    },

    validateField(input) {
        const rules = (input.getAttribute('data-validate') || '').split('|');
        const value = input.value.trim();
        let error = '';

        for (const rule of rules) {
            const [name, param] = rule.split(':');

            switch (name) {
                case 'required':
                    if (!value) error = 'This field is required';
                    break;
                case 'email':
                    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) error = 'Invalid email address';
                    break;
                case 'min':
                    if (value && value.length < parseInt(param)) error = `Minimum ${param} characters`;
                    break;
                case 'max':
                    if (value && value.length > parseInt(param)) error = `Maximum ${param} characters`;
                    break;
                case 'matches':
                    const match = document.querySelector(param);
                    if (match && value !== match.value) error = 'Fields do not match';
                    break;
            }
            if (error) break;
        }

        const group = input.closest('.yt-form-group');
        const errorEl = group?.querySelector('.yt-form-error');

        if (error) {
            input.classList.add('is-invalid');
            input.style.borderColor = 'var(--yt-danger)';
            if (errorEl) errorEl.textContent = error;
            else if (group) {
                const err = document.createElement('div');
                err.className = 'yt-form-error';
                err.textContent = error;
                group.appendChild(err);
            }
            return false;
        } else {
            input.classList.remove('is-invalid');
            input.style.borderColor = '';
            if (errorEl) errorEl.remove();
            return true;
        }
    }
};

/* ── File Upload Preview ── */
const FileUpload = {
    init() {
        document.querySelectorAll('[data-upload-zone]').forEach(zone => {
            const input = zone.querySelector('input[type="file"]');
            if (!input) return;

            input.addEventListener('change', (e) => this.preview(e.target, zone));

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    this.preview(input, zone);
                }
            });

            zone.addEventListener('click', (e) => {
                if (!e.target.closest('.remove-preview') && !e.target.closest('input')) {
                    input.click();
                }
            });
        });
    },

    preview(input, zone) {
        const file = input.files[0];
        if (!file) return;

        const existing = zone.querySelector('.yt-upload-preview');
        if (existing) existing.remove();

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.createElement('div');
                preview.className = 'yt-upload-preview';
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-preview" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>`;
                zone.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }

        const nameEl = zone.querySelector('.upload-filename');
        if (nameEl) nameEl.textContent = file.name;

        const sizeEl = zone.querySelector('.upload-filesize');
        if (sizeEl) sizeEl.textContent = FileUpload.formatSize(file.size);
    },

    formatSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
        return bytes.toFixed(1) + ' ' + units[i];
    }
};

/* ── Copy to Clipboard ── */
const Clipboard = {
    init() {
        document.querySelectorAll('[data-copy]').forEach(btn => {
            btn.addEventListener('click', () => {
                const text = btn.getAttribute('data-copy') || btn.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    Toast.success('Copied to clipboard');
                }).catch(() => {
                    Toast.error('Failed to copy');
                });
            });
        });
    }
};

/* ── Number Formatting ── */
function formatNumber(n) {
    if (n >= 1e9) return (n / 1e9).toFixed(1) + 'B';
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
    return n.toString();
}

/* ── Time Ago ── */
function timeAgo(dateStr) {
    const now = Date.now();
    const then = new Date(dateStr).getTime();
    const diff = Math.floor((now - then) / 1000);

    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    if (diff < 2592000) return Math.floor(diff / 604800) + ' weeks ago';
    if (diff < 31536000) return Math.floor(diff / 2592000) + ' months ago';
    return Math.floor(diff / 31536000) + ' years ago';
}

/* ── Video Processing Status Polling ── */
const VideoProcessing = {
    intervals: {},

    poll(videoId, callback) {
        this.intervals[videoId] = setInterval(async () => {
            try {
                const data = await Ajax.get(`${BASE_URL}/viewer/video/${videoId}/status`);
                if (data.status === 'completed' || data.status === 'error') {
                    clearInterval(this.intervals[videoId]);
                    delete this.intervals[videoId];
                    callback(data);
                }
            } catch { /* silent */ }
        }, 5000);
    },

    stop(videoId) {
        if (this.intervals[videoId]) {
            clearInterval(this.intervals[videoId]);
            delete this.intervals[videoId];
        }
    }
};

/* ── Scroll to Top ── */
const ScrollToTop = {
    btn: null,

    init() {
        this.btn = document.querySelector('.yt-scroll-top');
        if (!this.btn) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                this.btn.classList.add('show');
            } else {
                this.btn.classList.remove('show');
            }
        });

        this.btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
};

/* ── Keyboard Shortcuts ── */
const Shortcuts = {
    init() {
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;

            switch (e.key) {
                case '/':
                    e.preventDefault();
                    const searchInput = document.querySelector('.yt-search-input');
                    if (searchInput) {
                        searchInput.focus();
                        if (window.innerWidth < 768) Search.expand();
                    }
                    break;
                case 'Escape':
                    Search.collapse();
                    Search.hideAutocomplete();
                    document.querySelectorAll('.yt-modal-backdrop.show').forEach(m => {
                        m.classList.remove('show');
                        document.body.style.overflow = '';
                    });
                    if (Dropdowns.active) {
                        Dropdowns.active.classList.remove('show');
                        Dropdowns.active = null;
                    }
                    break;
            }
        });
    }
};

/* ── URL Parameter Management ── */
const UrlParams = {
    get(key) {
        return new URLSearchParams(window.location.search).get(key);
    },

    set(key, value) {
        const params = new URLSearchParams(window.location.search);
        params.set(key, value);
        window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
    },

    remove(key) {
        const params = new URLSearchParams(window.location.search);
        params.delete(key);
        const qs = params.toString();
        window.history.replaceState({}, '', qs ? `${window.location.pathname}?${qs}` : window.location.pathname);
    }
};

/* ── Debounce / Throttle Utilities ── */
function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(null, args), delay);
    };
}

function throttle(fn, limit = 100) {
    let inThrottle;
    return (...args) => {
        if (!inThrottle) {
            fn.apply(null, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/* ── Lazy Loading (Enhanced) ── */
const LazyLoad = {
    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '200px' });

        document.querySelectorAll('img[data-src]').forEach(img => observer.observe(img));
    }
};

/* ── Admin Table Search ── */
const AdminTable = {
    init() {
        const searchInput = document.querySelector('[data-table-search]');
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                const query = e.target.value.toLowerCase();
                const table = document.querySelector(searchInput.getAttribute('data-table-search'));
                if (!table) return;

                table.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            }, 250));
        }
    }
};

/* ── Tab Navigation ── */
const Tabs = {
    init() {
        document.querySelectorAll('[data-tab-group]').forEach(group => {
            const tabs = group.querySelectorAll('[data-tab]');
            const targetId = group.getAttribute('data-tab-group');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    const panelId = tab.getAttribute('data-tab');
                    document.querySelectorAll(`[data-tab-panel="${targetId}"]`).forEach(p => {
                        p.style.display = p.id === panelId ? '' : 'none';
                    });
                });
            });
        });
    }
};

/* ── Progress Bar ── */
function setProgress(elementId, percent) {
    const bar = document.querySelector(`#${elementId} .yt-progress-bar`);
    if (bar) bar.style.width = `${Math.min(100, Math.max(0, percent))}%`;
}

/* ── Chart.js Helpers (loaded separately in charts.js but basic wrapper here) ── */
const ChartHelper = {
    getDefaults() {
        const isDark = Theme.current === 'dark';
        return {
            textColor: isDark ? '#aaa' : '#606060',
            gridColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
            backgroundColor: isDark ? '#272727' : '#ffffff'
        };
    }
};

/* ============================================
   INITIALIZATION
   ============================================ */
document.addEventListener('DOMContentLoaded', () => {
    Theme.init();
    Sidebar.init();
    Search.init();
    Dropdowns.init();
    Notifications.init();
    VideoActions.init();
    Comments.init();
    Report.init();
    ConfirmModal.init();
    ScrollToTop.init();
    Shortcuts.init();
    Clipboard.init();
    FileUpload.init();
    LazyLoad.init();
    Tabs.init();
    AdminTable.init();

    document.querySelectorAll('form[data-validate]').forEach(form => {
        FormValidator.init(form);
    });

    document.querySelectorAll('.yt-alert .alert-close').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.yt-alert').remove());
    });

    document.querySelectorAll('.video-card[role="link"]').forEach(card => {
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });

    document.querySelectorAll('.video-card').forEach(card => {
        const img = card.querySelector('.thumbnail img');
        if (!img) return;
        const apply = () => {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = 50;
                canvas.height = 50;
                ctx.drawImage(img, 0, 0, 50, 50);
                const d = ctx.getImageData(0, 0, 50, 50).data;
                let r = 0, g = 0, b = 0, count = 0;
                for (let i = 0; i < d.length; i += 16) {
                    r += d[i]; g += d[i+1]; b += d[i+2]; count++;
                }
                r = Math.round(r / count);
                g = Math.round(g / count);
                b = Math.round(b / count);
                card.style.setProperty('--thumb-rgb', `${r},${g},${b}`);
            } catch(e) {}
        };
        if (img.complete) apply();
        else img.addEventListener('load', apply);
    });
});

/* ── Skeleton Loaders ── */
(function() {
    var MIN_SKELETON_MS = 500;
    var startTime = Date.now();

    /* Loading bar */
    var bar = document.querySelector('.yt-loading-bar');
    if (bar) bar.classList.add('active');

    window.addEventListener('load', function() {
        var elapsed = Date.now() - startTime;
        var wait = Math.max(0, MIN_SKELETON_MS - elapsed);

        setTimeout(function() {
            /* Hide loading bar */
            if (bar) {
                bar.classList.remove('active');
                bar.classList.add('done');
            }

            /* Reveal all page content */
            document.querySelectorAll('.yt-page-content').forEach(function(el) {
                el.classList.add('revealed');
            });

            /* Hide all skeleton pages */
            document.querySelectorAll('.yt-skel-page').forEach(function(el) {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 300);
            });
        }, wait);
    });
})();
