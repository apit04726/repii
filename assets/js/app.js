/**
 * InstaGo - Frontend Application Script
 * Smooth AJAX, Clipboard Integration, Auto-Detection & Dynamic Media Preview
 */

document.addEventListener('DOMContentLoaded', () => {
    // ============================================================
    // Smart Code & UI Protection: Active Strictly on Live Production
    // (Localhost / Development environment retains full Right-Click & DevTools for design inspection)
    // ============================================================
    const isLocalhost = Boolean(
        window.location.hostname === 'localhost' ||
        window.location.hostname === '127.0.0.1' ||
        window.location.hostname === '[::1]' ||
        window.location.hostname.endsWith('.local') ||
        window.location.protocol === 'file:'
    );

    if (!isLocalhost) {
        document.body.classList.add('live-protected');

        document.addEventListener('contextmenu', (e) => {
            const tag = (e.target && e.target.tagName ? e.target.tagName : '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || (e.target && e.target.isContentEditable)) {
                return true;
            }
            e.preventDefault();
            return false;
        }, false);

        document.addEventListener('keydown', (e) => {
            const key = e.key ? e.key.toUpperCase() : '';
            const code = e.keyCode || 0;

            // Block F12 (DevTools)
            if (key === 'F12' || code === 123) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Block Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C (Inspect/Console/Element)
            if (e.ctrlKey && e.shiftKey && (key === 'I' || key === 'J' || key === 'C' || code === 73 || code === 74 || code === 67)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Block Ctrl+U (View Page Source)
            if (e.ctrlKey && (key === 'U' || code === 85)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Block Ctrl+S (Save Webpage)
            if (e.ctrlKey && (key === 'S' || code === 83)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, false);

        document.addEventListener('dragstart', (e) => {
            const tag = (e.target && e.target.tagName ? e.target.tagName : '').toLowerCase();
            if (tag === 'img' || tag === 'a' || tag === 'video') {
                e.preventDefault();
                return false;
            }
        }, false);

        // Prevent Mobile Long-Press / Callout Menu on Links & Buttons
        document.addEventListener('touchstart', (e) => {
            const el = e.target ? e.target.closest('a, img, button, video') : null;
            if (el) {
                el.style.webkitTouchCallout = 'none';
            }
        }, { passive: true });
    }

    // DOM Elements
    const searchForm = document.getElementById('searchForm');
    const urlInput = document.getElementById('urlInput');
    const pasteBtn = document.getElementById('pasteBtn');
    const clearBtn = document.getElementById('clearBtn');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const detectionIndicator = document.getElementById('detectionIndicator');
    const detectedTypeSpan = document.getElementById('detectedType');

    const loaderCard = document.getElementById('loaderCard');
    const loaderStatus = document.getElementById('loaderStatus');
    const resultCard = document.getElementById('resultCard');
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');

    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIconSun = document.getElementById('themeIconSun');
    const themeIconMoon = document.getElementById('themeIconMoon');
    const metaThemeColor = document.getElementById('metaThemeColor');

    const tabBtns = document.querySelectorAll('.tab-btn');
    const dockBtns = document.querySelectorAll('.dock-btn');
    const faqItems = document.querySelectorAll('.faq-item');

    // Global Multi-Language State
    let currentAppLang = localStorage.getItem('instago_app_lang') || localStorage.getItem('instago_guide_lang') || 'en';
    const headerLangDropdown = document.getElementById('headerLangDropdown');
    const headerLangToggle = document.getElementById('headerLangToggle');
    const headerLangOptions = document.querySelectorAll('#headerLangMenu .lang-option-btn');
    const currentLangLabel = document.getElementById('currentLangLabel');

    // 1. Theme Toggle Logic (Preserves instant dark theme without 1-2 sec flicker)
    const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    updateThemeIcons(activeTheme);

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    });

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try {
            localStorage.setItem('instago_theme_user_choice', theme);
        } catch(e) {}
        updateThemeIcons(theme);
    }

    function updateThemeIcons(theme) {
        if (theme === 'light') {
            themeIconSun.style.display = 'none';
            themeIconMoon.style.display = 'block';
            if (metaThemeColor) metaThemeColor.setAttribute('content', '#f1f4f9');
        } else {
            themeIconSun.style.display = 'block';
            themeIconMoon.style.display = 'none';
            if (metaThemeColor) metaThemeColor.setAttribute('content', '#070a12');
        }
    }

    // 2. Tab & Mobile Dock Navigation
    let lastTabSwitchTime = 0;

    function scrollTabIntoCenter(btn) {
        if (!btn) return;
        const container = btn.closest('.tabs-container');
        if (!container) return;
        if (container.scrollWidth > container.clientWidth) {
            const btnLeft = btn.offsetLeft;
            const btnWidth = btn.offsetWidth;
            const containerWidth = container.clientWidth;
            const targetLeft = btnLeft - (containerWidth / 2) + (btnWidth / 2);
            container.scrollTo({
                left: Math.max(0, targetLeft),
                behavior: 'smooth'
            });
        }
    }

    function switchActiveTab(type, shouldScroll = false) {
        if (!type) type = 'all';

        tabBtns.forEach(btn => {
            const isActive = btn.getAttribute('data-type') === type;
            btn.classList.toggle('active', isActive);
            if (isActive) {
                scrollTabIntoCenter(btn);
            }
        });

        dockBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-type') === type);
        });

        if (urlInput) {
            updatePlaceholder(type);
        }

        if (shouldScroll && searchForm) {
            const isResultShowing = resultCard && resultCard.style.display === 'block';
            if (!isResultShowing) {
                if (navigator.vibrate) {
                    try { navigator.vibrate(10); } catch(e) {}
                }
                searchForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const isMobileOrTablet = window.innerWidth <= 1024 || ('ontouchstart' in window);
                if (!isMobileOrTablet && urlInput) {
                    setTimeout(() => urlInput.focus(), 300);
                }
            }
        }
    }

    // Auto-activate tab from query param if arriving from a subpage
    try {
        const initialType = new URLSearchParams(window.location.search).get('type');
        if (initialType && ['all', 'reels', 'dp', 'stories', 'highlights', 'photos'].includes(initialType)) {
            switchActiveTab(initialType, false);
        }
    } catch(e) {}

    function bindTabActivation(elements, isDock = false) {
        elements.forEach(btn => {
            let touchStartX = 0;
            let touchStartY = 0;
            let touchMoved = false;

            btn.addEventListener('touchstart', (e) => {
                if (e.touches && e.touches.length === 1) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;
                    touchMoved = false;
                }
            }, { passive: true });

            btn.addEventListener('touchmove', (e) => {
                if (e.touches && e.touches.length === 1) {
                    const diffX = Math.abs(e.touches[0].clientX - touchStartX);
                    const diffY = Math.abs(e.touches[0].clientY - touchStartY);
                    if (diffX > 10 || diffY > 10) {
                        touchMoved = true;
                    }
                }
            }, { passive: true });

            btn.addEventListener('touchend', (e) => {
                if (!touchMoved) {
                    const now = Date.now();
                    lastTabSwitchTime = now;
                    const type = btn.getAttribute('data-type') || 'all';
                    if (!searchForm) {
                        window.location.href = './?type=' + encodeURIComponent(type);
                        return;
                    }
                    switchActiveTab(type, isDock);
                }
            });

            btn.addEventListener('click', (e) => {
                const now = Date.now();
                if (now - lastTabSwitchTime < 350) return; // Prevent double trigger on touch devices
                lastTabSwitchTime = now;
                const type = btn.getAttribute('data-type') || 'all';
                if (!searchForm) {
                    window.location.href = './?type=' + encodeURIComponent(type);
                    return;
                }
                switchActiveTab(type, isDock);
            });
        });
    }

    bindTabActivation(tabBtns, false);
    bindTabActivation(dockBtns, false);

    function updatePlaceholder(type) {
        if (!urlInput) return;
        const langObj = (typeof i18n !== 'undefined' && i18n[currentAppLang]) ? i18n[currentAppLang] : null;
        if (langObj && langObj.placeholders) {
            urlInput.placeholder = langObj.placeholders[type] || langObj.placeholders.all;
            return;
        }

        switch (type) {
            case 'reels':
                urlInput.placeholder = 'Paste Instagram Reel link (e.g. https://www.instagram.com/reel/...)';
                break;
            case 'dp':
                urlInput.placeholder = 'Enter Username or Profile link (e.g. @cristiano or instagram.com/...)';
                break;
            case 'stories':
                urlInput.placeholder = 'Paste Instagram Story link or Username...';
                break;
            case 'highlights':
                urlInput.placeholder = 'Paste Instagram Highlight link (e.g. https://www.instagram.com/stories/highlights/...)';
                break;
            case 'photos':
                urlInput.placeholder = 'Paste Instagram Post / Carousel photo link...';
                break;
            default:
                urlInput.placeholder = 'Paste Instagram Reel, DP, Story, or Post URL...';
        }
    }

    let lastSearchedUrl = '';
    let autoSearchTimer = null;

    // Reset and clear results & errors
    function clearResultsAndErrors() {
        lastSearchedUrl = '';
        if (autoSearchTimer) clearTimeout(autoSearchTimer);
        
        resultCard.style.display = 'none';
        hideError();
        loaderCard.style.display = 'none';
        detectionIndicator.classList.remove('active');
        submitBtn.disabled = false;
        
        const langObj = (typeof i18n !== 'undefined' && i18n[currentAppLang]) ? i18n[currentAppLang] : null;
        submitBtnText.textContent = langObj ? langObj.searchBtn : 'Search';

        const mediaContainer = document.getElementById('mediaPreviewContainer');
        if (mediaContainer) {
            const video = mediaContainer.querySelector('video');
            if (video) {
                video.pause();
                video.removeAttribute('src');
                video.load();
            }
            mediaContainer.innerHTML = '';
        }
    }

    // Smart Searchbar Auto-Reset Handler (When returning to homepage after download)
    function checkAndApplyAutoReset() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const hasResetParam = urlParams.get('reset') === '1' || urlParams.get('cleared') === '1';
            const hasSessionReset = sessionStorage.getItem('instago_reset_search') === '1';
            const lastResetTs = parseInt(localStorage.getItem('instago_reset_search_ts') || '0', 10);
            const isFreshDownloadReturn = lastResetTs && (Date.now() - lastResetTs < 90000);

            if (hasResetParam || hasSessionReset || isFreshDownloadReturn) {
                sessionStorage.removeItem('instago_reset_search');
                localStorage.removeItem('instago_reset_search_ts');

                if (urlInput) {
                    urlInput.value = '';
                }
                if (clearBtn) {
                    clearBtn.style.display = 'none';
                }
                clearResultsAndErrors();

                if (hasResetParam) {
                    const cleanUrl = window.location.pathname + (window.location.hash || '');
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            }
        } catch(e) {}
    }

    checkAndApplyAutoReset();
    window.addEventListener('pageshow', checkAndApplyAutoReset);
    window.addEventListener('focus', checkAndApplyAutoReset);

    function isValidInstagramTarget(val) {
        if (!val) return false;
        return /(?:instagram\.com|instagr\.am)\/(?:p|reel|reels|stories|tv|highlights|s)\/[A-Za-z0-9_.-]+/i.test(val) ||
               /(?:instagram\.com|instagr\.am)\/[A-Za-z0-9_.-]{3,30}\/?$/i.test(val) ||
               /^@[A-Za-z0-9._]{2,30}$/.test(val);
    }

    function triggerAutoSearch() {
        const val = urlInput.value.trim();
        if (!val || submitBtn.disabled) return;

        if (urlInput) urlInput.blur();
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
        
        lastSearchedUrl = val;
        clearBtn.style.display = 'flex';
        detectUrlType(val);
        searchForm.dispatchEvent(new Event('submit'));
    }

    // 3. Input Auto-Detection, Debounced Auto-Search & Clear Button
    if (urlInput && searchForm) {
        urlInput.addEventListener('input', () => {
            const val = urlInput.value.trim();
            if (clearBtn) clearBtn.style.display = val ? 'flex' : 'none';

            if (!val) {
                clearResultsAndErrors();
                return;
            }

            detectUrlType(val);

            // Auto-search if a full link is entered
            if (autoSearchTimer) clearTimeout(autoSearchTimer);
            if (isValidInstagramTarget(val) && val !== lastSearchedUrl) {
                autoSearchTimer = setTimeout(() => {
                    triggerAutoSearch();
                }, 600);
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                urlInput.value = '';
                clearBtn.style.display = 'none';
                clearResultsAndErrors();
                const isMobileOrTablet = window.innerWidth <= 1024 || ('ontouchstart' in window);
                if (!isMobileOrTablet) {
                    urlInput.focus();
                }
            });
        }

        // Auto-search on paste (Ctrl+V, mobile long press, clipboard suggestion)
        urlInput.addEventListener('paste', () => {
            setTimeout(() => {
                const val = urlInput.value.trim();
                if (val && isValidInstagramTarget(val)) {
                    triggerAutoSearch();
                }
            }, 50);
        });
    }

    function detectUrlType(val) {
        if (!val) {
            if (detectionIndicator) detectionIndicator.classList.remove('active');
            return;
        }

        const t = (typeof i18n !== 'undefined' && i18n[currentAppLang]) ? i18n[currentAppLang] : null;
        let detected = '';
        let tabKey = 'all';

        if (/instagram\.com\/(?:reel|reels)\/([A-Za-z0-9_-]+)/i.test(val)) {
            detected = t ? t.detection.reel : 'Instagram Reel Detected';
            tabKey = 'reels';
        } else if (/instagram\.com\/(?:stories\/highlights\/[0-9]+|s\/[A-Za-z0-9_-]+)/i.test(val)) {
            detected = t ? (t.detection.highlight || 'Instagram Highlight Detected') : 'Instagram Highlight Detected';
            tabKey = 'highlights';
        } else if (/instagram\.com\/stories\/([A-Za-z0-9._-]+)/i.test(val)) {
            detected = t ? t.detection.story : 'Instagram Story Detected';
            tabKey = 'stories';
        } else if (/instagram\.com\/(?:p|tv)\/([A-Za-z0-9_-]+)/i.test(val)) {
            detected = t ? t.detection.post : 'Instagram Post / Photo Detected';
            tabKey = 'photos';
        } else if (/^@?[A-Za-z0-9._]{2,30}$/.test(val) || (/instagram\.com\/([A-Za-z0-9._]{2,30})\/?$/i.test(val) && !val.includes('/p/') && !val.includes('/reel/'))) {
            detected = t ? t.detection.dp : 'Instagram DP / Profile Detected';
            tabKey = 'dp';
        }

        if (detected) {
            if (detectedTypeSpan) detectedTypeSpan.textContent = detected;
            if (detectionIndicator) detectionIndicator.classList.add('active');
            // Sync active tab in both desktop bar and mobile dock with auto-scroll
            switchActiveTab(tabKey, false);
        } else {
            if (detectionIndicator) detectionIndicator.classList.remove('active');
        }
    }

    // 4. Clipboard Paste Button (Auto triggers search)
    if (pasteBtn && urlInput && searchForm) {
        pasteBtn.addEventListener('click', async () => {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        urlInput.value = text.trim();
                        if (clearBtn) clearBtn.style.display = 'flex';
                        detectUrlType(text.trim());
                        triggerAutoSearch();
                    }
                } else {
                    urlInput.focus();
                    document.execCommand('paste');
                    setTimeout(() => triggerAutoSearch(), 60);
                }
            } catch (err) {
                urlInput.focus();
                showToast('Press Ctrl+V to paste the link', 'info');
            }
        });
    }

    // 5. Form Submission & AJAX
    if (searchForm && urlInput) {
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const url = urlInput.value.trim();
            lastSearchedUrl = url;

            if (urlInput) urlInput.blur();
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }

            if (!url) {
                showError('Please paste a valid Instagram link.');
                const isMobileOrTablet = window.innerWidth <= 1024 || ('ontouchstart' in window);
                if (!isMobileOrTablet) {
                    urlInput.focus();
                }
                return;
            }

            // Reset UI States
            hideError();
            if (resultCard) resultCard.style.display = 'none';
            if (loaderCard) loaderCard.style.display = 'flex';
            if (submitBtn) submitBtn.disabled = true;
            if (submitBtnText) submitBtnText.textContent = 'Fetching...';

        // Animated loader status messages
        const statuses = [
            'Checking Instagram link...',
            'Fetching HD media stream...',
            'Preparing download links...'
        ];
        let statusIdx = 0;
        loaderStatus.textContent = statuses[0];
        const statusTimer = setInterval(() => {
            statusIdx = (statusIdx + 1) % statuses.length;
            loaderStatus.textContent = statuses[statusIdx];
        }, 1200);

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ url: url })
            });

            clearInterval(statusTimer);
            const res = await response.json();

            loaderCard.style.display = 'none';
            submitBtn.disabled = false;
            submitBtnText.textContent = 'Search';

            if (res.status === 'success' && res.data) {
                renderResult(res.data);
            } else {
                showError(res.message || 'Unable to fetch media. Please check the link and try again.');
            }
        } catch (error) {
            clearInterval(statusTimer);
            loaderCard.style.display = 'none';
            submitBtn.disabled = false;
            submitBtnText.textContent = 'Search';
            showError('Unable to connect to server. Please try again in a moment.');
        }
    });
    }

    // 6. Render Result Card
    function renderResult(data) {
        if (urlInput) urlInput.blur();
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }

        const typeBadge = document.getElementById('resultTypeBadge');
        const authorAvatar = document.getElementById('resultAuthorAvatar');
        const authorName = document.getElementById('resultAuthorName');
        const mediaContainer = document.getElementById('mediaPreviewContainer');
        const captionText = document.getElementById('resultCaption');
        const downloadOptions = document.getElementById('downloadOptions');

        // Set Header
        if ((data.type === 'story' || !data.type) && ((lastSearchedUrl && (/instagram\.com\/s\//i.test(lastSearchedUrl) || /stories\/highlights\//i.test(lastSearchedUrl))) || (typeof activeServiceTab !== 'undefined' && activeServiceTab === 'highlights'))) {
            data.type = 'highlight';
        }
        typeBadge.textContent = (data.type || 'Media').toUpperCase();
        authorName.textContent = data.author || (data.username ? '@' + data.username : 'Instagram Media');
        if (authorName.textContent === 'Instagram User' || authorName.textContent === '@instagram_user') {
            const storyMatch = (lastSearchedUrl || '').match(/instagram\.com\/stories\/(?!highlights\/)([A-Za-z0-9._]+)/i);
            const directUserMatch = (lastSearchedUrl || '').match(/^@?([A-Za-z0-9._]{1,30})$/);
            const profileMatch = (lastSearchedUrl || '').match(/instagram\.com\/([A-Za-z0-9._]{1,30})/i);
            if (storyMatch && storyMatch[1]) {
                authorName.textContent = '@' + storyMatch[1];
            } else if (directUserMatch && directUserMatch[1] && !/^(?:https?|reel|p|tv)$/i.test(directUserMatch[1])) {
                authorName.textContent = '@' + directUserMatch[1];
            } else if (profileMatch && profileMatch[1] && !/^(?:reel|reels|p|tv|stories|explore)$/i.test(profileMatch[1])) {
                authorName.textContent = '@' + profileMatch[1];
            }
        }
        
        authorAvatar.referrerPolicy = 'no-referrer';
        const firstMedia = (data.media && data.media[0]) ? data.media[0] : {};
        
        // Multi-tier avatar candidates chain (prioritizes signed preview, thumbnail, direct media, proxy)
        const rawCandidates = (data.type === 'dp') ? [
            data.preview_url,
            data.thumbnail,
            firstMedia.preview_url,
            firstMedia.thumbnail,
            data.proxy_url,
            firstMedia.proxy_url,
            firstMedia.url,
            data.download_url,
            firstMedia.download_url
        ] : [
            data.preview_url,
            data.thumbnail,
            firstMedia.preview_url,
            firstMedia.thumbnail
        ];

        // Deduplicate while preserving priority order
        const avatarCandidates = [];
        const seenCandidateUrls = new Set();
        rawCandidates.forEach(function(u) {
            if (u && typeof u === 'string' && u.length > 5 && !seenCandidateUrls.has(u)) {
                seenCandidateUrls.add(u);
                avatarCandidates.push(u);
            }
        });

        // Safe SVG fallback avatar with white silhouette, fully URI-encoded for mobile WebKit & desktop
        const svgFallback = 'data:image/svg+xml;utf8,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 100 100">' +
            '<defs><linearGradient id="avGrad" x1="0%" y1="0%" x2="100%" y2="100%">' +
            '<stop offset="0%" stop-color="#833ab4"/><stop offset="50%" stop-color="#fd1d1d"/><stop offset="100%" stop-color="#fcb045"/>' +
            '</linearGradient></defs>' +
            '<rect width="100" height="100" rx="50" fill="url(#avGrad)"/>' +
            '<circle cx="50" cy="40" r="18" fill="#ffffff" opacity="0.95"/>' +
            '<path d="M20 84 C22 62, 78 62, 80 84" fill="#ffffff" opacity="0.95"/>' +
            '</svg>'
        );

        if (avatarCandidates.length > 0) {
            let curAvatarIndex = 0;
            authorAvatar.src = avatarCandidates[0];
            authorAvatar.onerror = function() {
                curAvatarIndex++;
                if (curAvatarIndex < avatarCandidates.length) {
                    this.src = avatarCandidates[curAvatarIndex];
                } else {
                    this.src = svgFallback;
                    this.onerror = null;
                }
            };
        } else {
            authorAvatar.src = svgFallback;
        }

        // Caption / Bio
        captionText.textContent = data.caption || data.biography || (data.full_name ? data.full_name + ' Profile' : 'No description provided');

        // Toggle DP mode on media container for 1:1 aspect ratio
        if (data.type === 'dp') {
            mediaContainer.classList.add('is-dp-mode');
        } else {
            mediaContainer.classList.remove('is-dp-mode');
        }

        // Helper to render active media preview item
        function renderPreviewItem(item) {
            mediaContainer.innerHTML = '';
            const isVideo = item.type === 'video';
            const mediaSrc = item.url || item.download_url;
            const thumbSrc = item.thumbnail || item.preview_url || mediaSrc;

            if (isVideo) {
                const videoElem = document.createElement('video');
                videoElem.className = 'media-preview-video';
                videoElem.controls = true;
                videoElem.playsInline = true;
                videoElem.setAttribute('playsinline', 'true');
                videoElem.setAttribute('webkit-playsinline', 'true');
                videoElem.preload = 'metadata';
                videoElem.muted = false;
                videoElem.volume = 1.0;
                if (thumbSrc && thumbSrc !== mediaSrc) videoElem.poster = thumbSrc;
                videoElem.src = mediaSrc;

                // Detect portrait 9:16 aspect ratio
                videoElem.addEventListener('loadedmetadata', function() {
                    if (this.videoHeight > this.videoWidth) {
                        mediaContainer.classList.add('is-portrait-reel');
                    } else {
                        mediaContainer.classList.remove('is-portrait-reel');
                    }
                });

                // Dismiss search input focus if active when video starts playing
                videoElem.addEventListener('play', () => {
                    if (urlInput && document.activeElement === urlInput) {
                        urlInput.blur();
                    }
                });

                // Check if device is mobile or tablet — Desktop stays 100% native as requested
                const isMobileOrTablet = window.innerWidth <= 1024 || ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
                if (!isMobileOrTablet) {
                    // Desktop: Pure native player controls, untouched
                    mediaContainer.appendChild(videoElem);
                    return;
                }

                // Mobile & Tablet: Bottom-right touch interceptor directly over the 3-dots icon
                const touchInterceptor = document.createElement('div');
                touchInterceptor.className = 'native-dots-touch-target';
                touchInterceptor.title = 'Options';
                touchInterceptor.setAttribute('role', 'button');
                touchInterceptor.setAttribute('aria-label', 'Video Options');

                // Video Options Popup Menu for Mobile & Tablet
                const dlUrl = (typeof buildGatewayDownloadUrl === 'function')
                    ? buildGatewayDownloadUrl(item, data.shortcode || data.username || 'Media', 'video', thumbSrc)
                    : (item.download_url || item.url || data.download_url || mediaSrc);

                const optionsMenu = document.createElement('div');
                optionsMenu.className = 'video-options-menu';
                optionsMenu.innerHTML = `
                    <div class="video-options-header">
                        <span>⚡ Video Options</span>
                        <button type="button" class="video-options-close" aria-label="Close">&times;</button>
                    </div>
                    <div class="video-options-body">
                        <div class="video-option-row">
                            <span class="video-option-label">Playback Speed:</span>
                            <div class="video-speed-chips">
                                <button type="button" class="speed-chip" data-speed="0.5">0.5x</button>
                                <button type="button" class="speed-chip" data-speed="0.75">0.75x</button>
                                <button type="button" class="speed-chip active" data-speed="1">1x</button>
                                <button type="button" class="speed-chip" data-speed="1.25">1.25x</button>
                                <button type="button" class="speed-chip" data-speed="1.5">1.5x</button>
                                <button type="button" class="speed-chip" data-speed="2">2x</button>
                            </div>
                        </div>
                        <div class="video-option-actions">
                            <button type="button" class="video-action-btn pip-action-btn">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><rect x="12" y="10" width="8" height="6" rx="1"/></svg>
                                <span>Mini Player (PiP)</span>
                            </button>
                            <button type="button" class="video-action-btn loop-action-btn">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                <span class="loop-text">Loop: Off</span>
                            </button>
                            <a href="${dlUrl}" target="_blank" rel="noopener noreferrer" class="video-action-btn download-action-btn">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                <span>Download MP4</span>
                            </a>
                        </div>
                    </div>
                `;

                let isMenuOpen = false;
                let ignoreOutsideEventsUntil = 0;

                const openMenu = () => {
                    isMenuOpen = true;
                    optionsMenu.classList.add('active');
                    ignoreOutsideEventsUntil = Date.now() + 400;
                };

                const closeMenu = () => {
                    isMenuOpen = false;
                    optionsMenu.classList.remove('active');
                };

                const toggleMenu = (e) => {
                    if (e) {
                        e.stopPropagation();
                    }
                    if (isMenuOpen) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                };

                // Reliable mobile touch handling on 3-dots
                touchInterceptor.addEventListener('touchend', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    toggleMenu(e);
                });

                touchInterceptor.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    toggleMenu(e);
                });

                optionsMenu.querySelector('.video-options-close').addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeMenu();
                });

                // Speed chips
                optionsMenu.querySelectorAll('.speed-chip').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        optionsMenu.querySelectorAll('.speed-chip').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        const speed = parseFloat(btn.getAttribute('data-speed')) || 1;
                        videoElem.playbackRate = speed;
                    });
                });

                // PiP button
                const pipBtn = optionsMenu.querySelector('.pip-action-btn');
                if (document.pictureInPictureEnabled || videoElem.webkitSupportsPresentationMode) {
                    pipBtn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        try {
                            if (document.pictureInPictureElement) {
                                await document.exitPictureInPicture();
                            } else if (videoElem.requestPictureInPicture) {
                                await videoElem.requestPictureInPicture();
                            } else if (videoElem.webkitSetPresentationMode) {
                                videoElem.webkitSetPresentationMode('picture-in-picture');
                            }
                            closeMenu();
                        } catch (err) {}
                    });
                } else {
                    pipBtn.style.display = 'none';
                }

                // Loop toggle
                const loopBtn = optionsMenu.querySelector('.loop-action-btn');
                const loopText = optionsMenu.querySelector('.loop-text');
                loopBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    videoElem.loop = !videoElem.loop;
                    loopBtn.classList.toggle('active', videoElem.loop);
                    loopText.textContent = videoElem.loop ? 'Loop: On' : 'Loop: Off';
                });

                // Download action inside options
                const dlBtn = optionsMenu.querySelector('.download-action-btn');
                if (dlBtn) {
                    dlBtn.addEventListener('click', () => {
                        setTimeout(closeMenu, 400);
                    });
                }

                // Dismiss on outside tap with debounce guard
                const handleOutsideTap = (e) => {
                    if (!isMenuOpen) return;
                    if (Date.now() < ignoreOutsideEventsUntil) return;
                    if (optionsMenu.contains(e.target) || touchInterceptor.contains(e.target)) {
                        return;
                    }
                    closeMenu();
                };
                document.addEventListener('touchend', handleOutsideTap, { passive: true });
                document.addEventListener('click', handleOutsideTap);

                mediaContainer.appendChild(videoElem);
                mediaContainer.appendChild(touchInterceptor);
                mediaContainer.appendChild(optionsMenu);
            } else {
                const imgElem = document.createElement('img');
                imgElem.className = 'media-preview-img';
                imgElem.alt = item.title || data.full_name || (data.username ? '@' + data.username : 'Instagram Media Preview');
                imgElem.referrerPolicy = 'no-referrer';

                // Prioritize secure proxied preview URL to guarantee bypass of Instagram CDN 403 blocks
                const candidates = [
                    item.preview_url,
                    item.thumbnail,
                    data.preview_url,
                    data.thumbnail,
                    item.url,
                    item.download_url,
                    data.download_url
                ].filter(Boolean);

                let currentAttempt = 0;
                imgElem.src = candidates[0] || '';

                imgElem.onerror = function() {
                    currentAttempt++;
                    if (currentAttempt < candidates.length && candidates[currentAttempt] !== this.src) {
                        this.src = candidates[currentAttempt];
                    } else {
                        // Safe fallback: Stylized SVG avatar so it NEVER shows a broken image icon
                        const initial = (data.username || data.full_name || 'U').charAt(0).toUpperCase();
                        this.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 100 100"><defs><linearGradient id="ig" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#833ab4"/><stop offset="50%" stop-color="#fd1d1d"/><stop offset="100%" stop-color="#fcb045"/></linearGradient></defs><rect width="100" height="100" rx="20" fill="url(#ig)"/><circle cx="50" cy="42" r="18" fill="#ffffff" opacity="0.95"/><path d="M22 84 C24 64, 76 64, 78 84" fill="#ffffff" opacity="0.95"/><text x="50" y="47" font-family="sans-serif" font-size="14" font-weight="bold" fill="#e1306c" text-anchor="middle">${initial}</text></svg>`);
                        this.onerror = null;
                    }
                };
                mediaContainer.appendChild(imgElem);
            }
        }

        // Download Action Buttons & Multi-Media Smart Gallery
        downloadOptions.innerHTML = '';
        const t = (typeof i18n !== 'undefined' && i18n[currentAppLang]) ? i18n[currentAppLang] : null;

        // If it's a Reel, keep only video downloads (remove unwanted JPG cover items)
        if (data.type === 'reel' && data.media && data.media.length > 1) {
            const videoOnly = data.media.filter(m => m.type === 'video');
            if (videoOnly.length > 0) {
                data.media = videoOnly;
            }
        }

        // Helper to construct secure 5-second countdown gateway URL
        function buildGatewayDownloadUrl(item, fallbackName, defaultType, thumbFallback) {
            let targetUrl = item.proxy_url || item.url || item.download_url || '';
            const mediaType = item.type || defaultType || 'video';
            const cleanName = (item.title || fallbackName || 'InstaGo_Media').replace(/[^a-zA-Z0-9_-]/g, '_');
            const thumb = item.thumbnail || item.preview_url || thumbFallback || (mediaType === 'image' ? targetUrl : '');

            const curLang = localStorage.getItem('instago_app_lang') || 'gu';

            if (targetUrl && targetUrl.includes('download.php')) {
                let sep = targetUrl.includes('?') ? '&' : '?';
                if (!targetUrl.includes('name=') && cleanName) {
                    targetUrl += sep + 'name=' + encodeURIComponent(cleanName);
                    sep = '&';
                }
                if (!targetUrl.includes('thumb=') && thumb) {
                    try {
                        targetUrl += sep + 'thumb=' + encodeURIComponent(btoa(unescape(encodeURIComponent(thumb))));
                        sep = '&';
                    } catch(e) {}
                }
                if (!targetUrl.includes('lang=') && curLang) {
                    targetUrl += sep + 'lang=' + encodeURIComponent(curLang);
                }
                return targetUrl;
            }

            if (targetUrl) {
                try {
                    const b64 = btoa(unescape(encodeURIComponent(targetUrl)));
                    let url = `download.php?type=${encodeURIComponent(mediaType)}&name=${encodeURIComponent(cleanName)}&lang=${encodeURIComponent(curLang)}&url=${encodeURIComponent(b64)}`;
                    if (thumb) {
                        url += `&thumb=${encodeURIComponent(btoa(unescape(encodeURIComponent(thumb))))}`;
                    }
                    return url;
                } catch(e) {
                    return targetUrl;
                }
            }
            return targetUrl;
        }

        const resultCard = document.getElementById('resultCard');
        const reelQuickSpecs = document.getElementById('reelQuickSpecs');
        const specRes = document.getElementById('specRes');

        if (data.media && (data.media.length > 1 || data.type === 'highlight' || data.type === 'story')) {
            // 1. Multi-Media Mode: Interactive Smart Gallery (Highlights / Stories / Carousel)
            if (resultCard) {
                resultCard.classList.remove('single-media-mode');
                resultCard.classList.add('gallery-mode');
            }
            if (reelQuickSpecs) reelQuickSpecs.style.display = 'none';

            renderPreviewItem(data.media[0]);

            // Gallery Header & Item Counter
            const galleryHeader = document.createElement('div');
            galleryHeader.className = 'media-gallery-header';

            const countChip = document.createElement('span');
            countChip.className = 'gallery-count-chip';
            const itemTypeLabel = data.type === 'highlight' ? 'Highlights' : (data.type === 'story' ? 'Stories' : 'Items');
            countChip.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> ${data.media.length} ${itemTypeLabel}`;
            galleryHeader.appendChild(countChip);

            const hintText = document.createElement('span');
            hintText.className = 'gallery-hint-text';
            hintText.textContent = t ? (t.langCode === 'GU' ? 'પ્રિવ્યુ માટે કાર્ડ દબાવો • ડાઉનલોડ બટનથી સેવ કરો' : (t.langCode === 'HI' ? 'प्रीव्यू के लिए कार्ड दबाएं • डाउनलोड करें' : 'Tap item to preview • Click to download')) : 'Tap item to preview • Click to download';
            galleryHeader.appendChild(hintText);

            downloadOptions.appendChild(galleryHeader);

            // Smart Scrollable Container
            const galleryScroll = document.createElement('div');
            galleryScroll.className = 'media-gallery-scroll';

            data.media.forEach((m, index) => {
                const card = document.createElement('div');
                card.className = `media-item-card ${index === 0 ? 'active-item' : ''}`;
                card.setAttribute('data-index', index);

                // Thumbnail with Play / Photo Overlay
                const thumbBox = document.createElement('div');
                thumbBox.className = 'media-item-thumb-box';

                const thumbImg = document.createElement('img');
                thumbImg.className = 'media-item-thumb-img';
                thumbImg.src = m.thumbnail || m.preview_url || m.url;
                thumbImg.alt = m.title || `Item ${index + 1}`;
                thumbImg.referrerPolicy = 'no-referrer';
                thumbImg.onerror = function() {
                    if (m.preview_url && this.src !== m.preview_url) {
                        this.src = m.preview_url;
                    } else if (m.url && this.src !== m.url) {
                        this.src = m.url;
                    }
                };
                thumbBox.appendChild(thumbImg);

                const indexTag = document.createElement('span');
                indexTag.className = 'media-item-index-tag';
                indexTag.textContent = `#${index + 1}`;
                thumbBox.appendChild(indexTag);

                if (m.type === 'video') {
                    const playOverlay = document.createElement('div');
                    playOverlay.className = 'media-item-play-icon';
                    playOverlay.innerHTML = `<svg viewBox="0 0 24 24"><polygon points="8 5 19 12 8 19 8 5"/></svg>`;
                    thumbBox.appendChild(playOverlay);
                }

                // Metadata Details
                const info = document.createElement('div');
                info.className = 'media-item-info';

                const titleRow = document.createElement('div');
                titleRow.className = 'media-item-title-row';
                const title = document.createElement('span');
                title.className = 'media-item-title';
                const defaultTitle = data.type === 'highlight' ? `Highlight #${index + 1}` : (data.type === 'story' ? `Story #${index + 1}` : `Slide #${index + 1}`);
                title.textContent = m.title || defaultTitle;
                titleRow.appendChild(title);

                const quality = document.createElement('span');
                quality.className = 'media-item-quality-badge';
                const qualityText = m.type === 'video' ? '1080p • MP4' : 'HD • JPG';
                quality.innerHTML = m.type === 'video' 
                    ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg> <span>${qualityText}</span>`
                    : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/></svg> <span>${qualityText}</span>`;

                const nowPlaying = document.createElement('span');
                nowPlaying.className = 'media-item-now-playing';
                nowPlaying.innerHTML = `<svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg> ${t ? (t.langCode === 'GU' ? 'હાલમાં ચાલુ છે' : (t.langCode === 'HI' ? 'चल रहा है' : 'Playing')) : 'Playing'}`;

                info.appendChild(titleRow);
                info.appendChild(quality);
                info.appendChild(nowPlaying);

                // Direct Download Action Button (Same Tab Smooth Gateway Navigation)
                const dlBtn = document.createElement('a');
                dlBtn.href = buildGatewayDownloadUrl(m, 'Item_' + (index + 1), m.type, m.thumbnail);
                dlBtn.className = 'media-item-dl-btn';
                dlBtn.setAttribute('target', '_blank');
                dlBtn.setAttribute('rel', 'opener');
                const dlBtnFull = m.type === 'video' ? 'Download MP4' : 'Download JPG';
                const dlBtnMid = 'Download';
                const dlBtnShort = m.type === 'video' ? 'MP4' : 'JPG';
                dlBtn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg><span class="dl-lbl-full">${dlBtnFull}</span><span class="dl-lbl-mid">${dlBtnMid}</span><span class="dl-lbl-short">${dlBtnShort}</span>`;
                
                dlBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                });

                // Tap card to switch active preview
                card.addEventListener('click', () => {
                    renderPreviewItem(m);
                    galleryScroll.querySelectorAll('.media-item-card').forEach(c => c.classList.remove('active-item'));
                    card.classList.add('active-item');
                    if (window.innerWidth <= 820) {
                        mediaContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });

                card.appendChild(thumbBox);
                card.appendChild(info);
                card.appendChild(dlBtn);
                galleryScroll.appendChild(card);
            });

            downloadOptions.appendChild(galleryScroll);

        } else {
            // 2. Single Media Item Mode (Reel / Video / Single Photo)
            if (resultCard) {
                resultCard.classList.remove('gallery-mode');
                resultCard.classList.add('single-media-mode');
            }
            if (reelQuickSpecs) {
                reelQuickSpecs.style.display = 'flex';
                if (specRes) {
                    specRes.textContent = (data.type === 'video' || data.type === 'reel') ? '1080p MP4 HD' : (data.type === 'dp' ? 'Original Full Size' : 'Ultra HD JPG');
                }
                if (typeof window.refreshMobileAutoTickers === 'function') {
                    window.refreshMobileAutoTickers();
                }
            }

            const singleItem = (data.media && data.media.length === 1) ? data.media[0] : {
                type: (data.type === 'reel' ? 'video' : 'image'),
                url: data.download_url,
                thumbnail: data.thumbnail,
                preview_url: data.preview_url,
                proxy_url: data.download_url,
                title: data.caption
            };

            renderPreviewItem(singleItem);

            const btn = document.createElement('a');
            btn.href = buildGatewayDownloadUrl(singleItem, data.shortcode || data.username || 'Media', singleItem.type, singleItem.thumbnail || data.thumbnail);
            btn.className = 'btn-download-main';
            btn.setAttribute('target', '_blank');
            btn.setAttribute('rel', 'opener');
            const iconSvg = singleItem.type === 'video' 
                ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`
                : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`;
            const labelText = t ? (singleItem.type === 'video' ? t.results.downloadVideo : t.results.downloadImage) : (data.type === 'dp' ? 'Download Profile Picture (HD)' : `Download ${singleItem.quality || (singleItem.type === 'video' ? 'Video (MP4 HD)' : 'Image (JPG HD)')}`);
            btn.innerHTML = `${iconSvg} <span>${labelText}</span>`;
            downloadOptions.appendChild(btn);
        }

        // Reset / Download Another Button
        const resetBtn = document.createElement('button');
        resetBtn.type = 'button';
        resetBtn.className = 'btn-reset';
        resetBtn.textContent = t ? t.results.resetBtn : '↺ Download Another Link';
        resetBtn.addEventListener('click', () => {
            clearResultsAndErrors();
            urlInput.value = '';
            clearBtn.style.display = 'none';
            const isMobileOrTablet = window.innerWidth <= 1024 || ('ontouchstart' in window);
            if (!isMobileOrTablet) {
                urlInput.focus();
            }
            window.scrollTo({ top: searchForm.offsetTop - 100, behavior: 'smooth' });
        });
        downloadOptions.appendChild(resetBtn);

        // Display and scroll
        resultCard.style.display = 'block';
        resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // 7. Error Handling
    function showError(msg) {
        errorMessage.textContent = msg;
        errorAlert.classList.add('active');
    }

    function hideError() {
        errorAlert.classList.remove('active');
    }

    // 8. FAQ Accordion Toggle
    faqItems.forEach(item => {
        const questionBtn = item.querySelector('.faq-question');
        questionBtn.addEventListener('click', () => {
            const isOpen = item.classList.contains('open');
            // Close all
            faqItems.forEach(i => i.classList.remove('open'));
            // Toggle clicked
            if (!isOpen) {
                item.classList.add('open');
            }
        });
    });

    // 9. Complete Site-Wide Multi-Language System (English, Gujarati, Hindi)
    const guideHeading = document.getElementById('guideHeading');
    const guideSubheading = document.getElementById('guideSubheading');
    const stepTitle1 = document.getElementById('stepTitle1');
    const stepDesc1 = document.getElementById('stepDesc1');
    const stepTitle2 = document.getElementById('stepTitle2');
    const stepDesc2 = document.getElementById('stepDesc2');
    const stepTitle3 = document.getElementById('stepTitle3');
    const stepDesc3 = document.getElementById('stepDesc3');
    const tipTitleReels = document.getElementById('tipTitleReels');
    const tipDescReels = document.getElementById('tipDescReels');
    const tipTitleDP = document.getElementById('tipTitleDP');
    const tipDescDP = document.getElementById('tipDescDP');
    const tipTitleStories = document.getElementById('tipTitleStories');
    const tipDescStories = document.getElementById('tipDescStories');

    const i18n = {
        en: {
            langCode: 'EN',
            statusBadge: 'Meta CDN Active',
            heroBadge: 'Fast & Free 1080p Instagram Downloader',
            heroTitle: 'Download Instagram <br><span class="gradient-text">Reels, DP & Stories</span>',
            heroDesc: 'Save HD Reels, Full Resolution Profile Pictures (DP), Stories, and Carousel Photos directly to your Gallery or PC.',
            tabs: {
                all: 'All-in-One',
                reels: 'Reels',
                dp: 'Profile DP',
                stories: 'Stories',
                highlights: 'Highlights',
                photos: 'Photos'
            },
            dock: {
                all: 'All',
                reels: 'Reels',
                dp: 'DP',
                stories: 'Stories',
                highlights: 'Highlights',
                photos: 'Photos'
            },
            placeholders: {
                all: 'Paste Instagram Reel, DP, Story, Highlight, or Post URL...',
                reels: 'Paste Instagram Reel link (e.g. https://www.instagram.com/reel/...)',
                dp: 'Enter Username or Profile link (e.g. @cristiano or instagram.com/...)',
                stories: 'Paste Instagram Story link or Username...',
                highlights: 'Paste Instagram Highlight link (e.g. https://www.instagram.com/stories/highlights/...)',
                photos: 'Paste Instagram Post / Carousel photo link...'
            },
            pasteBtn: 'Paste',
            searchBtn: 'Search',
            searchingBtn: 'Processing...',
            detection: {
                reel: 'Instagram Reel Detected',
                story: 'Instagram Story Detected',
                highlight: 'Instagram Highlight Detected',
                post: 'Instagram Photo / Post Detected',
                dp: 'Instagram DP / Profile Detected'
            },
            perks: {
                p1: '1080p Ultra HD',
                p2: 'Zero Watermark',
                p3: '100% Anonymous',
                p4: 'Unlimited Free'
            },
            loader: {
                title: 'Processing Media',
                status: 'Connecting to Instagram servers...'
            },
            results: {
                downloadVideo: 'Download Video (MP4 HD)',
                downloadImage: 'Download Image (JPG HD)',
                downloadFile: 'Download File (HD)',
                resetBtn: '↺ Download Another Link'
            },
            guide: {
                heading: 'How to Download from <span class="gradient-text">Instagram</span>',
                subheading: 'Save any Reel, Profile DP, Story or Photo in 3 easy steps',
                step1Title: 'Copy & Paste Link',
                step1Desc: 'Copy any Instagram Reel link and tap <strong>"Paste"</strong> in InstaGo. Our smart engine auto-detects the link immediately.',
                step2Title: 'Auto-Fetch & Preview',
                step2Desc: 'InstaGo instantly connects to Instagram CDN to generate high-resolution video preview with complete creator details.',
                step3Title: 'Save in 1080p Full HD',
                step3Desc: 'Tap <strong>"Download Video (MP4 HD)"</strong> to save the crystal-clear original video without any watermark directly to your device.',
                tipReelsTitle: 'Reels & Videos',
                tipReelsDesc: 'Works for public Reels, audio tracks, and video clips in 1080p original clarity.',
                tipDpTitle: 'Full HD Profile DP',
                tipDpDesc: 'Simply enter any username (e.g. @cristiano) to view & zoom original full-size avatar.',
                tipStoriesTitle: 'Stories & Highlights',
                tipStoriesDesc: 'Paste profile link or story URL to download active 24h stories and highlight reels.'
            },
            ways: {
                heading: 'Ways to Download <span class="gradient-text">Instagram Reels Video</span>',
                subheading: 'Step-by-step instructions tailored for your smartphone or desktop',
                tabIos: 'iPhone & iPad (iOS)',
                tabAndroid: 'Android',
                tabPC: 'Windows PC & Mac',
                iosBadge: 'iOS Safari Flow',
                iosTitle: 'How to Download Instagram Reels on iPhone (iOS)',
                iosStep1: 'Open the <strong>Instagram app</strong> on your iPhone or iPad and navigate to your desired Reel video.',
                iosStep2: 'Tap the paper airplane <strong>Share icon</strong> at the bottom right, then tap <strong>"Copy Link"</strong>.',
                iosStep3: 'Open <strong>Safari</strong> browser, visit <strong>InstaGo</strong>, and tap the pink <strong>"Paste"</strong> button.',
                iosStep4: 'Click <strong>"Download MP4"</strong>. Safari will display a native download prompt; click <strong>"Download"</strong>.',
                iosStep5: 'Tap the blue Safari download circle, open the video, and tap <strong>Share &gt; Save Video</strong> to move it into your <strong>Photos App</strong>.',
                androidBadge: 'Android Chrome Flow',
                androidTitle: 'How to Download Instagram Reels on Android',
                androidStep1: 'Launch Instagram on your Android device (Samsung, OnePlus, Xiaomi, Pixel, etc.).',
                androidStep2: 'Go to the Reel video, tap the <strong>3 dots or Share icon</strong>, and choose <strong>"Copy link"</strong>.',
                androidStep3: 'Open Google Chrome, navigate to <strong>InstaGo</strong>, and tap <strong>"Paste"</strong>.',
                androidStep4: 'Tap <strong>"Download MP4 (1080p HD)"</strong>. Chrome will automatically download the file into your <strong>Downloads</strong> folder.',
                androidStep5: 'The video immediately appears in your <strong>Google Photos</strong> or phone\'s <strong>Gallery</strong> app.',
                pcBadge: 'Desktop PC & Mac Flow',
                pcTitle: 'How to Download Instagram Reels on PC & Mac',
                pcStep1: 'Open <strong>instagram.com</strong> in your Chrome, Edge, Safari, or Firefox browser on your desktop or laptop.',
                pcStep2: 'Click on the Reel you want to download and copy the URL from the browser address bar or click the three dots icon to copy link.',
                pcStep3: 'Paste the link into the search box on <strong>InstaGo</strong> and click <strong>Search</strong>.',
                pcStep4: 'Click <strong>"Download MP4"</strong> to save the high-speed uncompressed video straight to your PC\'s <strong>Downloads</strong> directory.'
            },
            articles: {
                art1Title: 'Download Instagram Reels Video without Watermark',
                art1P1: 'InstaGo Reels Downloader is engineered to deliver <strong>100% original, watermark-free video streams</strong>. Unlike screen-recorders or basic tools that compress videos or stamp third-party branding over your favourite clips, InstaGo connects directly to high-speed Instagram CDN nodes to fetch the raw, native MP4 video file.',
                art1P2: 'Whether you are an aspiring content creator archiving your portfolio, a video editor gathering inspiration, or simply saving funny Reels to share with family on WhatsApp, InstaGo ensures your video preserves its original 1080p Full HD resolution, rich dynamic colors, and pristine 320kbps stereo sound.',
                art2Title: 'What is Instagram Reels & Why Save Them?',
                art2P1: 'Instagram Reels is Meta\'s dynamic short-form video platform featuring 15 to 90-second creative multi-clip videos with trending audio tracks, AR augmented-reality filters, speed adjustments, and rich visual effects. Millions of viral tutorials, comedy skits, fitness routines, and travel guides are published daily.',
                art2P2: 'However, Instagram does not provide an uncompressed native offline download option for public Reels. Using InstaGo, you can easily save unlimited public Reels directly into your phone\'s camera roll or desktop hard drive without requiring any Instagram login, password, or third-party app installations.'
            },
            features: {
                heading: 'Why Choose <span class="gradient-text">InstaGo</span>?',
                subheading: 'Engineered for speed, privacy, and maximum video quality',
                f1Title: 'Ultra Fast Downloads',
                f1Desc: 'InstaGo fetches original media directly from high-speed Instagram CDN nodes without compression or delays.',
                f2Title: 'Original 1080p Quality',
                f2Desc: 'Get crystal-clear 1080p Full HD video streams and full-resolution profile images with zero watermark or logos.',
                f3Title: '100% Anonymous & Safe',
                f3Desc: 'No login, no password, and no account registration required. Your searches and downloads remain completely private.',
                f4Title: 'All-in-One Downloader',
                f4Desc: 'Reels, Profile DP, Active Stories, Carousels, and Photos all accessible from a single smart input field.',
                f5Title: 'Zero Watermarks or Stamps',
                f5Desc: 'Save completely clean videos without any creator watermark, TikTok stamps, or site logos added to the frame.',
                f6Title: 'Unlimited Free Access',
                f6Desc: 'Enjoy 24/7 unlimited downloads forever without daily download caps, subscriptions, or hidden paywalls.',
                dev1: 'iPhone & iPad (iOS Safari)',
                dev2: 'Android (Chrome & Samsung)',
                dev3: 'Windows PC & Mac'
            },
            faq: {
                heading: 'Frequently Asked <span class="gradient-text">Questions</span>',
                subheading: "Got questions? We've got answers",
                q1: 'Is InstaGo free to use?',
                a1: 'Yes, InstaGo is 100% free with unlimited downloads. You do not need to purchase any subscription or install any third-party app.',
                q2: 'How can I download Instagram Reels on my iPhone?',
                a2: 'Open Safari on your iPhone, paste the copied Reel URL into InstaGo, and click "Download MP4". Safari will prompt you with a download dialog, and the video will be saved directly into your Files or Photos app.',
                q3: 'Can I download full-size HD Profile Pictures (DP)?',
                a3: 'Yes! Simply type the username (e.g. <code>@cristiano</code>) or paste the profile link into InstaGo. We fetch the uncompressed original HD profile picture for you to view or download.',
                q4: 'Do the downloaded videos contain any watermark?',
                a4: 'No, all videos and photos are downloaded in their raw, native quality without adding any logos, stamps, or watermarks.',
                q5: 'Where are downloaded videos saved on my phone?',
                a5: 'On Android, files are saved in your device\'s <strong>Downloads</strong> folder or Gallery app. On iOS, they appear in the Safari Downloads list and can be saved to the Photos app with one tap.',
                q6: 'Can I download Reels in 1080p Full HD?',
                a6: 'Yes! If the creator uploaded the Reel in 1080p high definition, InstaGo retrieves the authentic direct 1080p source stream without downscaling or lossy compression.',
                q7: 'Can I extract audio or music from Instagram Reels?',
                a7: 'Yes, you can download the full MP4 video with original synchronized high-bitrate AAC audio, or convert it to pure MP3 format on your media player.',
                q8: 'Is it legal to download Instagram Reels?',
                a8: 'Downloading public Instagram videos for personal offline watching, archival, or educational fair use is permitted. Please respect creators’ copyrights and avoid commercial redistribution without permission.'
            },
            footer: {
                tagline: 'The fastest, 100% anonymous & watermark-free Instagram media downloader. Save HD Reels, Stories, DP, and Photos in 1080p.',
                disclaimer: '<strong>Disclaimer:</strong> InstaGo is an independent tool and is not affiliated, endorsed, or associated with Instagram, Meta, or Facebook. We do not host any copyrighted videos or photos on our servers; all media files are served directly from official CDN servers.',
                chip1: '100% Free Forever',
                chip2: 'No Login Required',
                chip3: 'Zero Watermark',
                colTools: 'Instagram Tools',
                linkReels: 'Reels Downloader',
                linkDP: 'DP Downloader',
                linkStories: 'Story Downloader',
                linkHighlights: 'Highlights Downloader',
                linkPhotos: 'Photo Downloader',
                colGuides: 'Device Guides',
                devIos: 'iPhone & iPad',
                devAndroid: 'Android',
                devPC: 'Windows PC',
                devMac: 'Mac OS',
                colLegal: 'Legal & Policy',
                legPrivacy: 'Privacy Policy',
                legTerms: 'Terms of Service',
                legAbout: 'About Us',
                legContact: 'Contact & DMCA',
                shareLabel: 'Share InstaGo with Friends:',
                copyright: `&copy; ${new Date().getFullYear()} <strong>InstaGo</strong>. All rights reserved. Fast & Simple Instagram Downloader.`
            },
            legal: {
                tabPrivacy: 'Privacy Policy',
                tabTerms: 'Terms of Service',
                tabAbout: 'About Us',
                tabContact: 'Contact Us',
                panePrivacy: `<h3>Privacy Policy &amp; Cookie Disclosure</h3>
<p class="modal-date">Last Updated: ${new Date().toLocaleDateString('en-US', {month: 'long', year: 'numeric'})}</p>
<p>At InstaGo, one of our main priorities is the privacy of our visitors. This Privacy Policy document outlines the types of information collected and how we use it in strict compliance with Google AdSense Policies, CCPA, and GDPR.</p>
<h4>1. Zero Log &amp; No Account Policy</h4>
<p>InstaGo does not require you to register, log in, or provide any personal credentials (such as Instagram username, password, or cookies). We do not record search queries or build personal profiles of our users.</p>
<h4>2. Google DoubleClick DART Cookies &amp; AdSense</h4>
<p>Google is one of our third-party vendors on our site. It uses cookies, known as DART cookies, to serve ads to our site visitors based upon their visit to this website and other sites on the internet. You may choose to opt out of the use of DART cookies by visiting the Google Ad and Content Network Privacy Policy at <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">policies.google.com/technologies/ads</a>.</p>
<h4>3. CDN &amp; Server Logs</h4>
<p>Like standard web hosts, our web servers record standard HTTP request logs (IP address, browser type, timestamp, referring URL). This data is used solely for system administration, fraud prevention, and performance optimization.</p>`,
                paneTerms: `<h3>Terms of Service &amp; Fair Use</h3>
<p class="modal-date">Last Updated: ${new Date().toLocaleDateString('en-US', {month: 'long', year: 'numeric'})}</p>
<p>By accessing or using InstaGo, you agree to be bound by these Terms of Service. If you disagree with any part of these terms, please do not use our service.</p>
<h4>1. Educational &amp; Personal Use</h4>
<p>InstaGo is provided strictly as an educational utility to assist users in making personal backup copies of public Instagram content they have legitimate rights to access under fair use doctrines.</p>
<h4>2. Intellectual Property Rights</h4>
<p>InstaGo does not own, host, or store any video, audio, or image files on its servers. All media files are retrieved on-the-fly and streamed directly from Instagram's official CDN. All copyrights, trademarks, and intellectual property remain the sole property of their respective creators and Meta Platforms Inc.</p>
<h4>3. User Responsibility &amp; Commercial Prohibition</h4>
<p>Users are strictly prohibited from utilizing InstaGo to pirate copyrighted work, distribute uncredited intellectual property, or use downloaded materials for unauthorized commercial purposes.</p>`,
                paneAbout: `<h3>About InstaGo</h3>
<p>InstaGo was founded with a clear mission: to provide the fastest, cleanest, and most reliable Instagram media downloader without intrusive popups, forced software downloads, or privacy compromises.</p>
<h4>Why Millions Choose InstaGo:</h4>
<ul>
    <li><strong>Pure 1080p Fidelity:</strong> We never downscale or watermark original Instagram Reels or Photos.</li>
    <li><strong>Safe &amp; Anonymous:</strong> Zero trackers, zero login walls, and zero stored personal data.</li>
    <li><strong>Lightning Fast CDN:</strong> Distributed nodes for instantaneous stream fetching.</li>
    <li><strong>Every Device Supported:</strong> Fully responsive on iOS Safari, Android, tablets, and desktop computers.</li>
</ul>`,
                paneContact: `<h3>Contact &amp; DMCA Inquiry</h3>
<p>We value user feedback, technical inquiries, and DMCA safe harbor notifications. If you have questions, feedback, or DMCA inquiries, please reach out to our dedicated support team:</p>
<div class="contact-card-box">
    <p><strong>Official Inquiries &amp; Support:</strong> support@instago.app</p>
    <p><strong>DMCA &amp; Copyright Compliance:</strong> dmca@instago.app</p>
    <p><strong>Response Time:</strong> Typically within 24 to 48 business hours.</p>
</div>`
            }
        },
        gu: {
            langCode: 'GU',
            statusBadge: 'મેટા CDN સક્રિય',
            heroBadge: 'ઝડપી અને મફત 1080p ઇન્સ્ટાગ્રામ ડાઉનલોડર',
            heroTitle: 'ઇન્સ્ટાગ્રામ ડાઉનલોડ કરો <br><span class="gradient-text">રીલ્સ, DP અને સ્ટોરીઝ</span>',
            heroDesc: 'HD રીલ્સ, ફૂલ રેઝોલ્યુશન પ્રોફાઇલ ફોટો (DP), સ્ટોરીઝ અને ફોટા સીધા તમારા મોબાઇલ ગેલેરી કે PC માં સેવ કરો.',
            tabs: {
                all: 'બધા મીડિયા',
                reels: 'રીલ્સ',
                dp: 'પ્રોફાઇલ DP',
                stories: 'સ્ટોરીઝ',
                highlights: 'હાઇલાઇટ્સ',
                photos: 'ફોટોઝ'
            },
            dock: {
                all: 'બધા',
                reels: 'રીલ્સ',
                dp: 'DP',
                stories: 'સ્ટોરીઝ',
                highlights: 'હાઇલાઇટ્સ',
                photos: 'ફોટોઝ'
            },
            placeholders: {
                all: 'ઇન્સ્ટાગ્રામ રીલ, DP, સ્ટોરી, હાઇલાઇટ કે પોસ્ટ લિંક પેસ્ટ કરો...',
                reels: 'ઇન્સ્ટાગ્રામ રીલ લિંક પેસ્ટ કરો (જેમ કે instagram.com/reel/...)',
                dp: 'યુઝરનેમ અથવા પ્રોફાઇલ લિંક નાખો (જેમ કે @cristiano)',
                stories: 'ઇન્સ્ટાગ્રામ સ્ટોરી લિંક અથવા યુઝરનેમ નાખો...',
                highlights: 'ઇન્સ્ટાગ્રામ હાઇલાઇટ લિંક પેસ્ટ કરો (જેમ કે instagram.com/stories/highlights/...)',
                photos: 'ઇન્સ્ટાગ્રામ ફોટો / કેરોયુઝલ લિંક પેસ્ટ કરો...'
            },
            pasteBtn: 'પેસ્ટ',
            searchBtn: 'શોધો',
            searchingBtn: 'પ્રોસેસિંગ...',
            detection: {
                reel: 'ઇન્સ્ટાગ્રામ રીલ મળી',
                story: 'ઇન્સ્ટાગ્રામ સ્ટોરી મળી',
                highlight: 'ઇન્સ્ટાગ્રામ હાઇલાઇટ મળી',
                post: 'ઇન્સ્ટાગ્રામ ફોટો/પોસ્ટ મળી',
                dp: 'ઇન્સ્ટાગ્રામ પ્રોફાઇલ DP મળી'
            },
            perks: {
                p1: '1080p અલ્ટ્રા HD',
                p2: 'કોઈ વોટરમાર્ક નહીં',
                p3: '100% સુરક્ષિત અને ગુપ્ત',
                p4: 'અમર્યાદિત મફત'
            },
            loader: {
                title: 'મીડિયા પ્રોસેસિંગ...',
                status: 'ઇન્સ્ટાગ્રામ સર્વર સાથે જોડાઈ રહ્યું છે...'
            },
            error: {
                generic: 'કંઈક ખોટું થયું છે. કૃપા કરીને લિંક ફરી તપાસો.'
            },
            results: {
                downloadVideo: 'વિડીયો ડાઉનલોડ કરો (MP4 HD)',
                downloadImage: 'ફોટો ડાઉનલોડ કરો (JPG HD)',
                downloadFile: 'ફાઇલ ડાઉનલોડ કરો (HD)',
                resetBtn: '↺ અન્ય લિંક ડાઉનલોડ કરો'
            },
            guide: {
                heading: 'ઇન્સ્ટાગ્રામ પરથી <span class="gradient-text">કેવી રીતે ડાઉનલોડ કરવું?</span>',
                subheading: 'રીલ્સ, પ્રોફાઇલ DP, સ્ટોરી કે ફોટા ફક્ત 3 સરળ સ્ટેપ્સમાં સેવ કરો',
                step1Title: 'લિંક કૉપી અને પેસ્ટ કરો',
                step1Desc: 'ઇન્સ્ટાગ્રામ રીલ લિંક કૉપી કરો અને InstaGo માં <strong>"Paste"</strong> બટન દબાવો. અમારું સ્માર્ટ સિસ્ટમ તરત જ મીડિયા ઓળખી લેશે.',
                step2Title: 'ઓટો-ફેચ અને પ્રિવ્યૂ',
                step2Desc: 'InstaGo ઇન્સ્ટાગ્રામ CDN સાથે જોડાઈને ક્રિએટર વિગતો સાથે તરત જ હાઇ-રિઝોલ્યુશન વિડીયો પ્રિવ્યૂ લાવશે.',
                step3Title: '1080p HD માં સેવ કરો',
                step3Desc: '<strong>"Download Video (MP4 HD)"</strong> બટન દબાવો અને કોઈપણ વોટરમાર્ક વગર ઓરિજિનલ ક્લિયર વિડીયો સીધો ગેલેરીમાં સેવ કરો.',
                tipReelsTitle: 'રીલ્સ અને વિડીયો',
                tipReelsDesc: 'બધી પબ્લિક રીલ્સ, ઓડિયો અને વિડીયો 1080p ફૂલ HD ક્વોલિટીમાં ડાઉનલોડ થાય છે.',
                tipDpTitle: 'ફૂલ HD પ્રોફાઇલ DP',
                tipDpDesc: 'માત્ર યુઝરનેમ (જેમ કે @cristiano) લખો અને કોઈપણ યુઝરનો ઓરિજિનલ HD ફોટો જુઓ અથવા ડાઉનલોડ કરો.',
                tipStoriesTitle: 'સ્ટોરી અને હાઇલાઇટ્સ',
                tipStoriesDesc: 'પ્રોફાઇલ અથવા સ્ટોરી લિંક નાખીને 24 કલાકની એક્ટિવ સ્ટોરી અને હાઇલાઇટ્સ સેવ કરો.'
            },
            ways: {
                heading: 'ઇન્સ્ટાગ્રામ રીલ્સ વિડિયો <span class="gradient-text">ડાઉનલોડ કરવાની રીતો</span>',
                subheading: 'તમારા સ્માર્ટફોન અથવા ડેસ્કટોપ માટે સ્ટેપ-બાય-સ્ટેપ સરળ માર્ગદર્શિકા',
                tabIos: 'iPhone અને iPad (iOS)',
                tabAndroid: 'Android',
                tabPC: 'Windows PC અને Mac',
                iosBadge: 'iOS સફારી ગાઇડ',
                iosTitle: 'iPhone (iOS) પર ઇન્સ્ટાગ્રામ રીલ્સ કેવી રીતે ડાઉનલોડ કરવી?',
                iosStep1: 'તમારા iPhone અથવા iPad પર <strong>ઇન્સ્ટાગ્રામ એપ</strong> ખોલો અને તમારી પસંદગીની રીલ વિડિયો પર જાઓ.',
                iosStep2: 'નીચે જમણી બાજુએ આપેલા <strong>શેર આઇકોન</strong> પર ટેપ કરો, પછી <strong>"Copy Link"</strong> પર ક્લિક કરો.',
                iosStep3: '<strong>Safari</strong> બ્રાઉઝર ખોલો, <strong>InstaGo</strong> પર જાઓ અને <strong>"Paste"</strong> બટન પર ટેપ કરો.',
                iosStep4: '<strong>"Download MP4"</strong> પર ક્લિક કરો. Safari માં ડાઉનલોડ પોપઅપ આવશે, તેમાં <strong>"Download"</strong> પસંદ કરો.',
                iosStep5: 'વાદળી Safari ડાઉનલોડ આઇકન પર ટેપ કરો, વિડિયો ઓપન કરો અને <strong>Share &gt; Save Video</strong> પર ક્લિક કરીને તમારી <strong>Photos એપ</strong> માં સેવ કરો.',
                androidBadge: 'Android ક્રોમ ગાઇડ',
                androidTitle: 'Android ફોન પર ઇન્સ્ટાગ્રામ રીલ્સ કેવી રીતે ડાઉનલોડ કરવી?',
                androidStep1: 'તમારા એન્ડ્રોઇડ ફોન (Samsung, OnePlus, Xiaomi, Pixel વગેરે) પર ઇન્સ્ટાગ્રામ એપ ઓપન કરો.',
                androidStep2: 'રીલ વિડિયો પર જાઓ, <strong>3 ડોટ્સ અથવા શેર આઇકન</strong> પર ટેપ કરો અને <strong>"Copy link"</strong> પસંદ કરો.',
                androidStep3: 'Google Chrome ખોલો, <strong>InstaGo</strong> વેબસાઇટ પર જાઓ અને <strong>"Paste"</strong> બટન દબાવો.',
                androidStep4: '<strong>"Download MP4 (1080p HD)"</strong> પર ક્લિક કરો. ક્રોમ આપમેળે તમારા <strong>Downloads</strong> ફોલ્ડરમાં ફાઇલ સેવ કરશે.',
                androidStep5: 'આ વિડિયો તરત જ તમારી ફોનની <strong>Gallery</strong> અથવા <strong>Google Photos</strong> એપમાં દેખાવા લાગશે.',
                pcBadge: 'Desktop PC અને Mac ગાઇડ',
                pcTitle: 'PC અને Mac કમ્પ્યુટર પર ઇન્સ્ટાગ્રામ રીલ્સ કેવી રીતે ડાઉનલોડ કરવી?',
                pcStep1: 'તમારા કમ્પ્યુટર અથવા લેપટોપમાં Chrome, Edge કે Safari બ્રાઉઝરમાં <strong>instagram.com</strong> ખોલો.',
                pcStep2: 'તમારે જે રીલ ડાઉનલોડ કરવી હોય તેની લિંક એડ્રેસ બારમાંથી અથવા 3 ડોટ્સ મેનૂમાંથી કોપી કરો.',
                pcStep3: 'તે લિંકને <strong>InstaGo</strong> ના સર્ચ બોક્સમાં પેસ્ટ કરો અને <strong>Search</strong> પર ક્લિક કરો.',
                pcStep4: '<strong>"Download MP4"</strong> પર ક્લિક કરો અને ઓરિજિનલ હાઇ-ક્વોલિટી વિડિયો સીધો તમારા PC ના <strong>Downloads</strong> ફોલ્ડરમાં સેવ કરો.'
            },
            articles: {
                art1Title: 'કોઈપણ વોટરમાર્ક વગર ઇન્સ્ટાગ્રામ રીલ્સ વિડિયો ડાઉનલોડ કરો',
                art1P1: 'InstaGo રીલ્સ ડાઉનલોડર <strong>100% ઓરિજિનલ, વોટરમાર્ક-મુક્ત વિડિયો</strong> પ્રદાન કરવા માટે બનાવવામાં આવ્યું છે. સાદા સ્ક્રીન-રેકોર્ડર્સ કે અન્ય ટૂલ્સ જે વિડિયો ક્વોલિટી ઘટાડે છે અથવા લોગો છાપે છે તેનાથી વિપરીત, InstaGo સીધા હાઇ-સ્પીડ ઇન્સ્ટાગ્રામ CDN સર્વર્સ સાથે કનેક્ટ થઈને ઓરિજિનલ MP4 ફાઇલ લાવે છે.',
                art1P2: 'તમે કન્ટેન્ટ ક્રિએટર હોવ, વિડિયો એડિટર હોવ કે વોટ્સએપ પર પરિવાર સાથે ફની રીલ્સ શેર કરવા માગતા હોવ, InstaGo તમારા વિડિયોનું 1080p ફુલ HD રિઝોલ્યુશન, ઉત્તમ કલર્સ અને ઓરિજિનલ ક્લિયર સાઉન્ડ અકબંધ રાખે છે.',
                art2Title: 'ઇન્સ્ટાગ્રામ રીલ્સ શું છે અને તેને શા માટે સેવ કરવી જોઈએ?',
                art2P1: 'ઇન્સ્ટાગ્રામ રીલ્સ એ મેટાનું શોર્ટ-વિડિયો પ્લેટફોર્મ છે જેમાં ટ્રેન્ડિંગ મ્યુઝિક, સ્પેશિયલ ઇફેક્ટ્સ અને ફિલ્ટર્સ સાથે 15 થી 90 સેકન્ડના ક્રિએટિવ વિડિઓઝ હોય છે. દરરોજ લાખો વાયરલ ટ્યુટોરિયલ્સ, કોમેડી અને ટ્રાવેલ ગાઇડ્સ પબ્લિશ થાય છે.',
                art2P2: 'જોકે, ઇન્સ્ટાગ્રામ એપ્લિકેશનમાં ઓફલાઇન સેવ કરવાનો ડાયરેક્ટ ઓપ્શન હોતો નથી. InstaGo ની મદદથી તમે કોઈપણ લોગિન કે પાસવર્ડ આપ્યા વગર અનલિમિટેડ પબ્લિક રીલ્સ સીધા તમારી ગેલેરીમાં કે કમ્પ્યુટરમાં સેવ કરી શકો છો.'
            },
            features: {
                heading: 'શા માટે <span class="gradient-text">InstaGo</span> પસંદ કરવું?',
                subheading: 'ઝડપ, પ્રાઇવસી અને શ્રેષ્ઠ વિડીયો ક્વોલિટી માટે ખાસ બનાવેલ',
                f1Title: 'અલ્ટ્રા ફાસ્ટ ડાઉનલોડ',
                f1Desc: 'InstaGo કોઈપણ કમ્પ્રેશન કે વિલંબ વગર હાઇ-સ્પીડ ઇન્સ્ટાગ્રામ CDN સર્વરથી સીધું મીડિયા લાવે છે.',
                f2Title: 'ઓરિજિનલ 1080p ક્વોલિટી',
                f2Desc: 'કોઈપણ વોટરમાર્ક કે લોગો વિના ક્રિસ્ટલ ક્લિયર 1080p ફુલ HD વિડીયો અને ફોટો ડાઉનલોડ કરો.',
                f3Title: '100% સુરક્ષિત અને ગુપ્ત',
                f3Desc: 'કોઈ લોગિન, પાસવર્ડ કે એકાઉન્ટની જરૂર નથી. તમારી શોધ અને ડાઉનલોડ્સ સંપૂર્ણપણે ખાનગી રહે છે.',
                f4Title: 'ઓલ-ઇન-વન ડાઉનલોડર',
                f4Desc: 'રીલ્સ, પ્રોફાઇલ DP, એક્ટિવ સ્ટોરી, કેરોયુઝલ અને ફોટા બધું એક જ ઇનપુટથી ડાઉનલોડ કરો.',
                f5Title: 'કોઈપણ વોટરમાર્ક કે લોગો નહીં',
                f5Desc: 'કોઈપણ ક્રિએટર વોટરમાર્ક, ટિકટોક સ્ટેમ્પ કે સાઇટ લોગો વિના એકદમ ક્લિન વિડીયો સેવ કરો.',
                f6Title: 'અનલિમિટેડ ફ્રી એક્સેસ',
                f6Desc: 'કોઈ દૈનિક મર્યાદા, સબસ્ક્રિપ્શન કે છુપાયેલા ચાર્જ વિના 24/7 અમર્યાદિત ફ્રી ડાઉનલોડ કરો.',
                dev1: 'iPhone અને iPad (iOS Safari)',
                dev2: 'Android (Chrome અને Samsung)',
                dev3: 'Windows PC અને Mac'
            },
            faq: {
                heading: 'વારંવાર પૂછાતા <span class="gradient-text">પ્રશ્નો (FAQ)</span>',
                subheading: 'કોઈ સવાલ છે? અહીં જવાબ છે',
                q1: 'શું InstaGo નો ઉપયોગ મફત છે?',
                a1: 'હા, InstaGo અમર્યાદિત ડાઉનલોડ્સ સાથે 100% મફત છે. કોઈ સબસ્ક્રિપ્શન ખરીદવાની કે થર્ડ-પાર્ટી એપ ઇન્સ્ટોલ કરવાની જરૂર નથી.',
                q2: 'હું મારા iPhone પર ઇન્સ્ટાગ્રામ રીલ્સ કેવી રીતે ડાઉનલોડ કરી શકું?',
                a2: 'તમારા iPhone પર Safari ખોલો, કૉપી કરેલી રીલ લિંક InstaGo માં પેસ્ટ કરો અને "Download MP4" પર ક્લિક કરો. વિડીયો સીધો તમારી Files અથવા Photos એપમાં સેવ થઈ જશે.',
                q3: 'શું હું ફૂલ સાઈઝ HD પ્રોફાઇલ ફોટો (DP) ડાઉનલોડ કરી શકું?',
                a3: 'હા! ફક્ત યુઝરનેમ (જેમ કે <code>@cristiano</code>) લખો અથવા પ્રોફાઇલ લિંક પેસ્ટ કરો. અમે ઓરિજિનલ ફૂલ-સાઇઝ HD પ્રોફાઇલ ફોટો મેળવીને ડાઉનલોડ માટે આપીએ છીએ.',
                q4: 'શું ડાઉનલોડ કરેલા વિડીયોમાં કોઈ વોટરમાર્ક હોય છે?',
                a4: 'ના, બધા વિડીયો અને ફોટા કોઈપણ લોગો, સ્ટેમ્પ કે વોટરમાર્ક વિના ઓરિજિનલ ક્વોલિટીમાં ડાઉનલોડ થાય છે.',
                q5: 'ડાઉનલોડ કરેલ વિડીયો મારા ફોનમાં ક્યાં સેવ થાય છે?',
                a5: 'Android માં ફાઇલો તમારા ડિવાઇસના <strong>Downloads</strong> ફોલ્ડર અથવા Gallery એપમાં સેવ થાય છે. iOS માં તે Safari Downloads માં દેખાય છે અને ગેલેરીમાં સેવ કરી શકાય છે.',
                q6: 'શું હું 1080p ફૂલ HD માં રીલ્સ ડાઉનલોડ કરી શકું?',
                a6: 'હા! જો ક્રિએટરે 1080p હાઇ ડેફિનેશનમાં રીલ અપલોડ કરી હોય, તો InstaGo કોઈપણ કમ્પ્રેશન વગર ઓરિજિનલ 1080p ફાઇલ આપે છે.',
                q7: 'શું હું ઇન્સ્ટાગ્રામ રીલ્સમાંથી ઓડિયો કે મ્યુઝિક સેવ કરી શકું?',
                a7: 'હા, તમે ઓરિજિનલ હાઇ-ક્વોલિટી AAC સાઉન્ડટ્રેક સાથે આખો MP4 વિડીયો ડાઉનલોડ કરી શકો છો.',
                q8: 'શું ઇન્સ્ટાગ્રામ રીલ્સ ડાઉનલોડ કરવી કાયદેસર છે?',
                a8: 'વ્યક્તિગત ઓફલાઇન જોવા કે એજ્યુકેશનલ ફેર-યુઝ માટે પબ્લિક વિડીયો સેવ કરવાની મંજૂરી છે. કૃપા કરીને ક્રિએટરના કોપીરાઇટનું સન્માન કરો.'
            },
            footer: {
                tagline: 'સૌથી ઝડપી, 100% ગુપ્ત અને વોટરમાર્ક-મુક્ત ઇન્સ્ટાગ્રામ ડાઉનલોડર. HD રીલ્સ, સ્ટોરીઝ, DP અને ફોટો 1080p માં સેવ કરો.',
                disclaimer: '<strong>ડિસ્ક્લેમર:</strong> InstaGo એક સ્વતંત્ર ટૂલ છે અને Instagram, Meta કે Facebook સાથે સંલગ્ન નથી. અમે અમારા સર્વર પર કોઈપણ કોપીરાઇટ વિડીયો કે ફોટો હોસ્ટ કરતા નથી; તમામ મીડિયા સત્તાવાર CDN સર્વર પરથી સીધા પહોંચાડાય છે.',
                chip1: '100% કાયમ માટે મફત',
                chip2: 'કોઈ લોગિન જરૂરી નથી',
                chip3: 'ઝીરો વોટરમાર્ક',
                colTools: 'ઇન્સ્ટાગ્રામ ટૂલ્સ',
                linkReels: 'રીલ્સ ડાઉનલોડર',
                linkDP: 'DP ડાઉનલોડર',
                linkStories: 'સ્ટોરી ડાઉનલોડર',
                linkHighlights: 'હાઇલાઇટ્સ ડાઉનલોડર',
                linkPhotos: 'ફોટો ડાઉનલોડર',
                colGuides: 'ડિવાઇસ ગાઇડ્સ',
                devIos: 'iPhone અને iPad',
                devAndroid: 'એન્ડ્રોઇડ (Android)',
                devPC: 'વિન્ડોઝ PC',
                devMac: 'મેક OS (Mac)',
                colLegal: 'કાનૂની અને નીતિ',
                legPrivacy: 'પ્રાઇવસી પોલિસી',
                legTerms: 'સેવાની શરતો',
                legAbout: 'અમારા વિશે',
                legContact: 'સંપર્ક અને DMCA',
                shareLabel: 'મિત્રો સાથે InstaGo શેર કરો:',
                copyright: `&copy; ${new Date().getFullYear()} <strong>InstaGo</strong>. સર્વાધિકાર સુરક્ષિત. ઝડપી અને સરળ ઇન્સ્ટાગ્રામ ડાઉનલોડર.`
            },
            legal: {
                tabPrivacy: 'પ્રાઇવસી પોલિસી',
                tabTerms: 'સેવાની શરતો',
                tabAbout: 'અમારા વિશે',
                tabContact: 'સંપર્ક કરો',
                panePrivacy: `<h3>પ્રાઇવસી પોલિસી અને કૂકી ડિસ્ક્લોઝર</h3>
<p class="modal-date">છેલ્લું અપડેટ: ${new Date().toLocaleDateString('gu-IN', {month: 'long', year: 'numeric'})}</p>
<p>InstaGo પર અમારા મુલાકાતીઓની ગોપનીયતા (Privacy) અમારી સર્વોચ્ચ પ્રાથમિકતા છે. આ પ્રાઇવસી પોલિસી દસ્તાવેજ સમજાવે છે કે કઈ માહિતી એકત્રિત થાય છે અને અમે Google AdSense નીતિઓ, CCPA અને GDPR નું કઈ રીતે ચુસ્ત પાલન કરીએ છીએ.</p>
<h4>૧. ઝીરો લોગ અને નો-એકાઉન્ટ પોલિસી</h4>
<p>InstaGo નો ઉપયોગ કરવા માટે તમારે કોઈપણ રજીસ્ટ્રેશન, લોગિન અથવા અંગત ઓળખ (જેમ કે ઇન્સ્ટાગ્રામ યુઝરનેમ, પાસવર્ડ કે કૂકીઝ) આપવાની જરૂર નથી. અમે કોઈપણ સર્ચ ક્વેરી રેકોર્ડ કરતા નથી કે યુઝર પ્રોફાઇલ બનાવતા નથી.</p>
<h4>૨. ગુગલ ડબલક્લિક DART કૂકીઝ અને એડસેન્સ</h4>
<p>ગૂગલ અમારી વેબસાઇટ પર થર્ડ-પાર્ટી વેન્ડર છે. તે મુલાકાતીઓને તેમની મુલાકાતના આધારે યોગ્ય જાહેરાતો બતાવવા માટે DART કૂકીઝનો ઉપયોગ કરે છે. તમે <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">policies.google.com/technologies/ads</a> પર જઈને આ કૂકીઝને બંધ કરવાનો વિકલ્પ પસંદ કરી શકો છો.</p>
<h4>૩. CDN અને સર્વર લોગ્સ</h4>
<p>અન્ય વેબસાઇટ્સની જેમ અમારા સર્વર્સ સામાન્ય ટેકનિકલ વિગતો (જેમ કે બ્રાઉઝરનો પ્રકાર, સમય) રેકોર્ડ કરે છે. આ ડેટાનો ઉપયોગ ફક્ત સિસ્ટમ મેનેજમેન્ટ, છેતરપિંડી અટકાવવા અને સ્પીડ વધારવા માટે જ થાય છે.</p>`,
                paneTerms: `<h3>સેવાની શરતો અને ફેર-યુઝ પોલિસી</h3>
<p class="modal-date">છેલ્લું અપડેટ: ${new Date().toLocaleDateString('gu-IN', {month: 'long', year: 'numeric'})}</p>
<p>InstaGo સેવાનો ઉપયોગ કરીને, તમે આ સેવાની શરતોનું પાલન કરવા સંમત થાઓ છો. જો તમે કોઈપણ શરત સાથે સંમત ન હોવ, તો કૃપા કરીને સેવાનો ઉપયોગ કરશો નહીં.</p>
<h4>૧. શૈક્ષણિક અને વ્યક્તિગત ઉપયોગ</h4>
<p>InstaGo નો હેતુ ફક્ત શૈક્ષણિક અને વ્યક્તિગત બેકઅપ માટે પબ્લિક ઇન્સ્ટાગ્રામ વિડિયો સેવ કરવામાં મદદ કરવાનો છે, જે ફેર-યુઝ નિયમો હેઠળ આવે છે.</p>
<h4>૨. બૌદ્ધિક સંપદા અને કોપીરાઇટ</h4>
<p>InstaGo પોતાના સર્વર પર કોઈપણ વિડિયો કે ફોટો હોસ્ટ કે સ્ટોર કરતું નથી. તમામ ફાઇલો સત્તાવાર ઇન્સ્ટાગ્રામ CDN પરથી સીધી સ્ટ્રીમ થાય છે. તમામ કોપીરાઇટ અને ટ્રેડમાર્ક્સ તેમના સંબંધિત ક્રિએટર્સ અને Meta Platforms Inc. ના રહે છે.</p>
<h4>૩. વપરાશકર્તાની જવાબદારી અને વ્યાપારી પ્રતિબંધ</h4>
<p>કોપીરાઇટ કરેલી સામગ્રીની અનધિકૃત વ્યાપારી વહેંચણી કે પુનઃઉત્પાદન માટે InstaGo નો ઉપયોગ કરવાની સખત મનાઈ છે.</p>`,
                paneAbout: `<h3>InstaGo વિશે</h3>
<p>InstaGo ની શરૂઆત એક સ્પષ્ટ ધ્યેય સાથે કરવામાં આવી છે: કોઈપણ હેરાન કરતી પોપઅપ જાહેરાતો કે વધારાના સોફ્ટવેર ઇન્સ્ટોલ કર્યા વગર સૌથી ઝડપી, સ્વચ્છ અને વિશ્વસનીય ઇન્સ્ટાગ્રામ ડાઉનલોડિંગ સેવા પૂરી પાડવી.</p>
<h4>શા માટે લાખો લોકો InstaGo પસંદ કરે છે:</h4>
<ul>
    <li><strong>૧૦૦% ૧૦૮૦p ઓરિજિનલ ક્વોલિટી:</strong> અમે ક્યારેય વિડિયો ક્વોલિટી ઘટાડતા નથી કે તેના પર વોટરમાર્ક લગાવતા નથી.</li>
    <li><strong>સુરક્ષિત અને ગુપ્ત:</strong> કોઈ ટ્રેકર્સ નહીં, કોઈ લોગિન વોલ નહીં અને કોઈ પર્સનલ ડેટા સેવ થતો નથી.</li>
    <li><strong>સુપરફાસ્ટ સ્પીડ:</strong> ઇન્સ્ટાગ્રામ સર્વર્સ સાથે સીધું કનેક્શન હોવાથી પળવારમાં ડાઉનલોડ થાય છે.</li>
    <li><strong>તમામ ડિવાઇસ સપોર્ટ:</strong> iPhone Safari, Android, ટેબ્લેટ અને PC પર પરફેક્ટ કામ કરે છે.</li>
</ul>`,
                paneContact: `<h3>સંપર્ક અને DMCA પૂછપરછ</h3>
<p>અમે અમારા વપરાશકર્તાઓના ફીડબેક, પ્રશ્નો અને DMCA સૂચનાઓનું સ્વાગત કરીએ છીએ. કોઈપણ સહાય કે પૂછપરછ માટે અમારી ટીમને ઇમેઇલ કરો:</p>
<div class="contact-card-box">
    <p><strong>સત્તાવાર સપોર્ટ ઇમેઇલ:</strong> support@instago.app</p>
    <p><strong>DMCA અને કોપીરાઇટ સંપર્ક:</strong> dmca@instago.app</p>
    <p><strong>જવાબ આપવાનો સમય:</strong> સામાન્ય રીતે ૨૪ થી ૪૮ કલાકની અંદર.</p>
</div>`
            }
        },
        hi: {
            langCode: 'HI',
            statusBadge: 'मेटा CDN सक्रिय',
            heroBadge: 'फास्ट और फ्री 1080p इंस्टाग्राम डाउनलोडर',
            heroTitle: 'इंस्टाग्राम डाउनलोड करें <br><span class="gradient-text">रील्स, DP और स्टोरीज</span>',
            heroDesc: 'HD रील्स, फुल रेजोल्यूशन प्रोफाइल फोटो (DP), स्टोरीज और फोटो सीधे अपनी फोन गैलरी या PC में सेव करें।',
            tabs: {
                all: 'सभी मीडिया',
                reels: 'रील्स',
                dp: 'प्रोफाइल DP',
                stories: 'स्टोरीज',
                highlights: 'हाइलाइट्स',
                photos: 'फोटो'
            },
            dock: {
                all: 'सभी',
                reels: 'रील्स',
                dp: 'DP',
                stories: 'स्टोरीज',
                highlights: 'हाइलाइट्स',
                photos: 'फोटो'
            },
            placeholders: {
                all: 'इंस्टाग्राम रील, DP, स्टोरी, हाइलाइट या पोस्ट लिंक पेस्ट करें...',
                reels: 'इंस्टाग्राम रील लिंक पेस्ट करें (जैसे instagram.com/reel/...)',
                dp: 'यूजरनेम या प्रोफाइल लिंक दर्ज करें (जैसे @cristiano)',
                stories: 'इंस्टाग्राम स्टोरी लिंक या यूजरनेम दर्ज करें...',
                highlights: 'इंस्टाग्राम हाइलाइट लिंक पेस्ट करें (जैसे instagram.com/stories/highlights/...)',
                photos: 'इंस्टाग्राम फोटो / हिंडोला पोस्ट लिंक पेस्ट करें...'
            },
            pasteBtn: 'पेस्ट',
            searchBtn: 'सर्च करें',
            searchingBtn: 'प्रोसेस हो रहा है...',
            detection: {
                reel: 'इंस्टाग्राम रील मिली',
                story: 'इंस्टाग्राम स्टोरी मिली',
                highlight: 'इंस्टाग्राम हाइलाइट मिली',
                post: 'इंस्टाग्राम फोटो/पोस्ट मिली',
                dp: 'इंस्टाग्राम प्रोफाइल DP मिली'
            },
            perks: {
                p1: '1080p अल्ट्रा HD',
                p2: 'बिना वॉटरमार्क',
                p3: '100% सुरक्षित और गुप्त',
                p4: 'अनलिमिटेड फ्री'
            },
            loader: {
                title: 'मीडिया प्रोसेस हो रहा है...',
                status: 'इंस्टाग्राम सर्वर से कनेक्ट हो रहा है...'
            },
            error: {
                generic: 'कुछ गलत हो गया। कृपया अपना लिंक दोबारा जांचें।'
            },
            results: {
                downloadVideo: 'वीडियो डाउनलोड करें (MP4 HD)',
                downloadImage: 'फोटो डाउनलोड करें (JPG HD)',
                downloadFile: 'फाइल डाउनलोड करें (HD)',
                resetBtn: '↺ अन्य लिंक डाउनलोड करें'
            },
            guide: {
                heading: 'इंस्टाग्राम से <span class="gradient-text">कैसे डाउनलोड करें?</span>',
                subheading: 'रील्स, प्रोफाइल DP, स्टोरी या फोटो सिर्फ 3 आसान स्टेप्स में सेव करें',
                step1Title: 'लिंक कॉपी और पेस्ट करें',
                step1Desc: 'इंस्टाग्राम रील लिंक कॉपी करें और InstaGo में <strong>"Paste"</strong> बटन दबाएं। हमारा स्मार्ट सिस्टम तुरंत वीडियो पहचान लेगा।',
                step2Title: 'ऑटो-फेच और प्रीव्यू',
                step2Desc: 'InstaGo इंस्टाग्राम CDN से कनेक्ट होकर क्रिएटर डिटेल्स के साथ तुरंत हाई-रिजोल्यूशन वीडियो प्रीव्यू दिखाता है।',
                step3Title: '1080p HD में सेव करें',
                step3Desc: '<strong>"Download Video (MP4 HD)"</strong> पर क्लिक करें और बिना किसी वॉटरमार्क के ओरिजिनल 1080p वीडियो सीधे गैलरी में सेव करें।',
                tipReelsTitle: 'रील्स और वीडियो',
                tipReelsDesc: 'सभी पब्लिक रील्स, ऑडियो और वीडियो 1080p ओरिजिनल क्लैरिटी में डाउनलोड करें।',
                tipDpTitle: 'फुल HD प्रोफाइल DP',
                tipDpDesc: 'सिर्फ यूजरनेम (जैसे @cristiano) डालें और किसी भी प्रोफाइल की ओरिजिनल साइज DP डाउनलोड करें।',
                tipStoriesTitle: 'स्टोरी और हाइलाइट्स',
                tipStoriesDesc: 'प्रोफाइल लिंक या स्टोरी URL डालकर 24 घंटे की एक्टिव स्टोरी और हाइलाइट्स सेव करें।'
            },
            ways: {
                heading: 'इंस्टाग्राम रील्स वीडियो <span class="gradient-text">डाउनलोड करने के तरीके</span>',
                subheading: 'आपके स्मार्टफोन या डेस्कटॉप के लिए स्टेप-बाय-स्टेप आसान निर्देश',
                tabIos: 'iPhone और iPad (iOS)',
                tabAndroid: 'Android',
                tabPC: 'Windows PC और Mac',
                iosBadge: 'iOS सफारी गाइड',
                iosTitle: 'iPhone (iOS) पर इंस्टाग्राम रील्स कैसे डाउनलोड करें?',
                iosStep1: 'अपने iPhone या iPad पर <strong>इंस्टाग्राम ऐप</strong> खोलें और अपनी पसंदीदा रील वीडियो पर जाएं।',
                iosStep2: 'नीचे दाईं ओर दिए गए <strong>शेयर आइकन</strong> पर टैप करें, फिर <strong>"Copy Link"</strong> चुनें।',
                iosStep3: '<strong>Safari</strong> ब्राउज़र खोलें, <strong>InstaGo</strong> पर जाएं और <strong>"Paste"</strong> बटन दबाएं।',
                iosStep4: '<strong>"Download MP4"</strong> पर क्लिक करें। Safari में डाउनलोड प्रॉम्प्ट दिखेगा, उसमें <strong>"Download"</strong> चुनें।',
                iosStep5: 'सफारी डाउनलोड आइकन पर टैप करें, वीडियो खोलें और <strong>Share &gt; Save Video</strong> चुनकर अपनी <strong>Photos ऐप</strong> में सुरक्षित करें।',
                androidBadge: 'Android क्रोम गाइड',
                androidTitle: 'Android फोन पर इंस्टाग्राम रील्स कैसे डाउनलोड करें?',
                androidStep1: 'अपने एंड्रॉइड फोन (Samsung, OnePlus, Xiaomi, Pixel आदि) पर इंस्टाग्राम खोलें।',
                androidStep2: 'रील वीडियो पर जाएं, <strong>3 डॉट्स या शेयर आइकन</strong> पर टैप करें और <strong>"Copy link"</strong> चुनें।',
                androidStep3: 'Google Chrome खोलें, <strong>InstaGo</strong> पर जाएं और <strong>"Paste"</strong> बटन दबाएं।',
                androidStep4: '<strong>"Download MP4 (1080p HD)"</strong> पर टैप करें। क्रोम आपके <strong>Downloads</strong> फोल्डर में वीडियो डाउनलोड कर देगा।',
                androidStep5: 'यह वीडियो तुरंत आपके फोन की <strong>Gallery</strong> या <strong>Google Photos</strong> ऐप में दिखाई देगा।',
                pcBadge: 'Desktop PC और Mac गाइड',
                pcTitle: 'PC और Mac कंप्यूटर पर इंस्टाग्राम रील्स कैसे डाउनलोड करें?',
                pcStep1: 'अपने कंप्यूटर या लैपटॉप में Chrome, Edge या Safari ब्राउज़र में <strong>instagram.com</strong> खोलें।',
                pcStep2: 'जिस रील को डाउनलोड करना है उसका लिंक ब्राउज़र एड्रेस बार या 3 डॉट्स मेनू से कॉपी करें।',
                pcStep3: 'उस लिंक को <strong>InstaGo</strong> के सर्च बॉक्स में पेस्ट करें और <strong>Search</strong> पर क्लिक करें।',
                pcStep4: '<strong>"Download MP4"</strong> पर क्लिक करके ओरिजिनल हाई-क्वालिटी वीडियो सीधे अपने PC के <strong>Downloads</strong> फोल्डर में सेव करें।'
            },
            articles: {
                art1Title: 'बिना किसी वॉटरमार्क के इंस्टाग्राम रील्स वीडियो डाउनलोड करें',
                art1P1: 'InstaGo रील्स डाउनलोडर <strong>100% ओरिजिनल, वॉटरमार्क-फ्री वीडियो</strong> देने के लिए तैयार किया गया है। स्क्रीन-रिकॉर्डर या अन्य टूल्स जो वीडियो कंप्रेस करते हैं या लोगो लगाते हैं, उनसे अलग InstaGo सीधे हाई-स्पीड इंस्टाग्राम CDN सर्वर से ओरिजिनल MP4 वीडियो लाता है।',
                art1P2: 'चाहे आप कंटेंट क्रिएटर हों, वीडियो एडिटर हों या व्हाट्सएप पर दोस्तों के साथ रील शेयर करना चाहते हों, InstaGo आपके वीडियो का 1080p फुल HD रेजोल्यूशन और शानदार ऑडियो सुरक्षित रखता है।',
                art2Title: 'इंस्टाग्राम रील्स क्या हैं और इन्हें क्यों सेव करें?',
                art2P1: 'इंस्टाग्राम रील्स मेटा का शॉर्ट-वीडियो प्लेटफॉर्म है जिसमें ट्रेंडिंग म्यूजिक और स्पेशल इफेक्ट्स के साथ 15 से 90 सेकंड के वीडियो होते हैं। रोज़ाना लाखों वायरल ट्यूटोरियल्स, कॉमेडी और ट्रैवल वीडियो पब्लिश होते हैं।',
                art2P2: 'लेकिन इंस्टाग्राम सीधे फोन में ऑफलाइन सेव करने की अनुमति नहीं देता। InstaGo से आप बिना किसी लॉगिन या पासवर्ड के सीधे अपनी गैलरी या कंप्यूटर में मनचाही रील्स सेव कर सकते हैं।'
            },
            features: {
                heading: 'क्यों चुनें <span class="gradient-text">InstaGo</span>?',
                subheading: 'स्पीड, प्राइवेसी और बेहतरीन वीडियो क्वालिटी के लिए खास बनाया गया',
                f1Title: 'अल्ट्रा फास्ट डाउनलोड',
                f1Desc: 'InstaGo बिना किसी कम्प्रेशन या देरी के हाई-स्पीड इंस्टाग्राम CDN सर्वर से सीधे मीडिया लाता है।',
                f2Title: 'ओरिजिनल 1080p क्वालिटी',
                f2Desc: 'बिना किसी वॉटरमार्क या लोगो के क्रिस्टल क्लियर 1080p फुल HD वीडियो और फोटो डाउनलोड करें।',
                f3Title: '100% सुरक्षित और प्राइवेट',
                f3Desc: 'कोई लॉगिन, पासवर्ड या अकाउंट की जरूरत नहीं। आपकी सर्च और डाउनलोड पूरी तरह से प्राइवेट रहते हैं।',
                f4Title: 'ऑल-इन-वन डाउनलोडर',
                f4Desc: 'रील्स, प्रोफाइल DP, एक्टिव स्टोरी, हिंडोला और फोटो सब कुछ एक ही इनपुट से डाउनलोड करें।',
                f5Title: 'बिना वॉटरमार्क या लोगो',
                f5Desc: 'बिना किसी क्रिएटर वॉटरमार्क, टिकटॉक स्टैम्प या साइट लोगो के बिल्कुल साफ और क्लीन वीडियो सेव करें।',
                f6Title: 'अनलिमिटेड फ्री एक्सेस',
                f6Desc: 'बिना किसी डेली लिमिट, सब्सक्रिप्शन या हिडन चार्ज के 24/7 अनलिमिटेड फ्री डाउनलोड का आनंद लें।',
                dev1: 'iPhone और iPad (iOS Safari)',
                dev2: 'Android (Chrome और Samsung)',
                dev3: 'Windows PC और Mac'
            },
            faq: {
                heading: 'अक्सर पूछे जाने वाले <span class="gradient-text">सवाल (FAQ)</span>',
                subheading: 'कोई सवाल है? यहाँ जवाब हैं',
                q1: 'क्या InstaGo का इस्तेमाल पूरी तरह फ्री है?',
                a1: 'हाँ, InstaGo अनलिमिटेड डाउनलोड के साथ 100% फ्री है। आपको कोई सब्सक्रिप्शन लेने या थर्ड-पार्टी ऐप इंस्टॉल करने की जरूरत नहीं है।',
                q2: 'मैं अपने iPhone पर इंस्टाग्राम रील्स कैसे डाउनलोड करूँ?',
                a2: 'अपने iPhone पर Safari खोलें, कॉपी किया गया रील लिंक InstaGo में पेस्ट करें और "Download MP4" पर क्लिक करें। वीडियो सीधे आपकी Files या Photos ऐप में सेव हो जाएगा।',
                q3: 'क्या मैं फुल साइज HD प्रोफाइल फोटो (DP) डाउनलोड कर सकता हूँ?',
                a3: 'हाँ! बस यूजरनेम (जैसे <code>@cristiano</code>) टाइप करें या प्रोफाइल लिंक पेस्ट करें। हम ओरिजिनल फुल-साइज HD प्रोफाइल पिक्चर लाकर देते हैं।',
                q4: 'क्या डाउनलोड किए गए वीडियो में कोई वॉटरमार्क होता है?',
                a4: 'नहीं, सभी वीडियो और फोटो बिना किसी लोगो, स्टाम्प या वॉटरमार्क के ओरिजिनल क्वालिटी में डाउनलोड होते हैं।',
                q5: 'डाउनलोड किए गए वीडियो मेरे फोन में कहाँ सेव होते हैं?',
                a5: 'Android पर फाइलें आपके डिवाइस के <strong>Downloads</strong> फोल्डर या Gallery ऐप में सेव होती हैं। iOS पर वे Safari Downloads में दिखती हैं।',
                q6: 'क्या मैं 1080p फुल HD में रील्स डाउनलोड कर सकता हूँ?',
                a6: 'हाँ! यदि क्रिएटर ने 1080p हाई डेफिनिशन में रील अपलोड की है, तो InstaGo बिना किसी कम्प्रेशन के ओरिजिनल 1080p सोर्स स्ट्रीम डाउनलोड करता है।',
                q7: 'क्या मैं इंस्टाग्राम रील्स से ऑडियो निकाल सकता हूँ?',
                a7: 'हाँ, आप ओरिजिनल हाई-बिटरेट AAC ऑडियो के साथ पूरा MP4 वीडियो डाउनलोड कर सकते हैं।',
                q8: 'क्या इंस्टाग्राम रील्स डाउनलोड करना कानूनी है?',
                a8: 'व्यक्तिगत ऑफलाइन देखने या फेयर-यूज़ के लिए पब्लिक वीडियो डाउनलोड करना पूरी तरह सामान्य और अनुमत है। कृपया क्रिएटर्स के कॉपीराइट का सम्मान करें।'
            },
            footer: {
                tagline: 'सबसे तेज़, 100% सुरक्षित और वॉटरमार्क-फ्री इंस्टाग्राम डाउनलोडर। HD रील्स, स्टोरीज, DP और फोटो 1080p में सेव करें।',
                disclaimer: '<strong>डिस्क्लेमर:</strong> InstaGo एक स्वतंत्र टूल है और Instagram, Meta या Facebook से संबद्ध नहीं है। हम अपने सर्वर पर कोई भी कॉपीराइट वीडियो या फोटो होस्ट नहीं करते हैं; सभी मीडिया आधिकारिक CDN सर्वर से सीधे आते हैं।',
                chip1: '100% हमेशा के लिए फ्री',
                chip2: 'लॉगिन की आवश्यकता नहीं',
                chip3: 'ज़ीरो वॉटरमार्क',
                colTools: 'इंस्टाग्राम टूल्स',
                linkReels: 'रील्स डाउनलोडर',
                linkDP: 'DP डाउनलोडर',
                linkStories: 'स्टोरी डाउनलोडर',
                linkHighlights: 'हाइलाइट्स डाउनलोडर',
                linkPhotos: 'फोटो डाउनलोडर',
                colGuides: 'डिवाइस गाइड',
                devIos: 'iPhone और iPad',
                devAndroid: 'एंड्रॉइड (Android)',
                devPC: 'विंडोज PC',
                devMac: 'मैक OS (Mac)',
                colLegal: 'कानूनी और नीतियां',
                legPrivacy: 'प्राइवेसी पॉलिसी',
                legTerms: 'सेवा की शर्तें',
                legAbout: 'हमारे बारे में',
                legContact: 'संपर्क और DMCA',
                shareLabel: 'दोस्तों के साथ InstaGo शेयर करें:',
                copyright: `&copy; ${new Date().getFullYear()} <strong>InstaGo</strong>. सर्वाधिकार सुरक्षित। तेज़ और आसान इंस्टाग्राम डाउनलोडर.`
            },
            legal: {
                tabPrivacy: 'प्राइवेसी पॉलिसी',
                tabTerms: 'सेवा की शर्तें',
                tabAbout: 'हमारे बारे में',
                tabContact: 'संपर्क करें',
                panePrivacy: `<h3>प्राइवेसी पॉलिसी और कुकी प्रकटीकरण</h3>
<p class="modal-date">अंतिम अपडेट: ${new Date().toLocaleDateString('hi-IN', {month: 'long', year: 'numeric'})}</p>
<p>InstaGo पर हमारे विज़िटर्स की प्राइवेसी हमारी पहली प्राथमिकता है। यह प्राइवेसी पॉलिसी दस्तावेज़ बताता है कि कौन सी जानकारी एकत्र की जाती है और हम Google AdSense नीतियों, CCPA और GDPR का सख्ती से पालन कैसे करते हैं।</p>
<h4>१. ज़ीरो लॉग और नो-अकाउंट पॉलिसी</h4>
<p>InstaGo का उपयोग करने के लिए किसी रजिस्ट्रेशन, लॉगिन या व्यक्तिगत क्रेडेंशियल (जैसे इंस्टाग्राम यूजरनेम, पासवर्ड या कुकीज़) की आवश्यकता नहीं है। हम कोई सर्च हिस्ट्री रिकॉर्ड नहीं करते हैं।</p>
<h4>२. गूगल डबलक्लिक DART कुकीज़ और एडसेंस</h4>
<p>गूगल हमारी साइट पर थर्ड-पार्टी वेंडर है। यह उपयोगकर्ताओं को उनकी रुचि के अनुसार विज्ञापन दिखाने के लिए DART कुकीज़ का उपयोग करता है। आप <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">policies.google.com/technologies/ads</a> पर जाकर DART कुकीज़ बंद कर सकते हैं।</p>
<h4>३. CDN और सर्वर लॉग्स</h4>
<p>अन्य वेब होस्ट की तरह हमारे सर्वर तकनीकी HTTP लॉग (ब्राउज़र प्रकार, समय) रिकॉर्ड करते हैं, जिसका उपयोग केवल सिस्टम सुरक्षा और परफॉर्मेंस सुधार के लिए किया जाता है।</p>`,
                paneTerms: `<h3>सेवा की शर्तें और फेयर-यूज़ नीति</h3>
<p class="modal-date">अंतिम अपडेट: ${new Date().toLocaleDateString('hi-IN', {month: 'long', year: 'numeric'})}</p>
<p>InstaGo का उपयोग करके, आप इन नियमों और शर्तों से बंधे होने के लिए सहमत होते हैं। यदि आप किसी भी शर्त से असहमत हैं, तो कृपया सेवा का उपयोग न करें।</p>
<h4>१. शैक्षिक और व्यक्तिगत उपयोग</h4>
<p>InstaGo केवल एक शैक्षिक टूल है जो उपयोगकर्ताओं को व्यक्तिगत बैकअप के लिए सार्वजनिक इंस्टाग्राम वीडियो सहेजने में मदद करता है।</p>
<h4>२. बौद्धिक संपदा अधिकार</h4>
<p>InstaGo अपने सर्वर पर कोई वीडियो या फोटो स्टोर नहीं करता है। सभी फाइलें आधिकारिक इंस्टाग्राम CDN से सीधे स्ट्रीम की जाती हैं। सभी कॉपीराइट मूल क्रिएटर्स और मेटा प्लेटफॉर्म्स के हैं।</p>
<h4>३. यूजर की ज़िम्मेदारी और व्यावसायिक प्रतिबंध</h4>
<p>कॉपीराइट सामग्री का अनधिकृत व्यावसायिक उपयोग करने या दूसरों के अधिकारों का उल्लंघन करने के लिए InstaGo का उपयोग करना सख्त वर्जित है।</p>`,
                paneAbout: `<h3>InstaGo के बारे में</h3>
<p>InstaGo की स्थापना एक स्पष्ट उद्देश्य के साथ की गई थी: बिना किसी कष्टप्रद विज्ञापनों या सॉफ़्टवेयर डाउनलोड के सबसे तेज़, स्वच्छ और विश्वसनीय इंस्टाग्राम डाउनलोडिंग सेवा प्रदान करना।</p>
<h4>लाखों लोग InstaGo क्यों चुनते हैं:</h4>
<ul>
    <li><strong>१००% १०८૦p ओरिजिनल क्वालिटी:</strong> हम कभी भी वीडियो क्वालिटी कम नहीं करते हैं और न ही वॉटरमार्क लगाते हैं।</li>
    <li><strong>सुरक्षित और गोपनीय:</strong> कोई लॉगिन की आवश्यकता नहीं, कोई व्यक्तिगत डेटा स्टोर नहीं होता।</li>
    <li><strong>सुपरफास्ट CDN स्पीड:</strong> इंस्टाग्राम सर्वर से सीधा कनेक्शन होने के कारण तुरंत डाउनलोड।</li>
    <li><strong>सभी डिवाइस पर समर्थित:</strong> iPhone Safari, Android, टैबलेट और PC पर बेहतरीन काम करता है।</li>
</ul>`,
                paneContact: `<h3>संपर्क और DMCA पूछताछ</h3>
<p>हम यूजर फीडबैक और DMCA सुरक्षित हार्बर सूचनाओं को महत्व देते हैं। किसी भी प्रश्न या पूछताछ के लिए हमारी सहायता टीम से संपर्क करें:</p>
<div class="contact-card-box">
    <p><strong>आधिकारिक सपोर्ट ईमेल:</strong> support@instago.app</p>
    <p><strong>DMCA और कॉपीराइट संपर्क:</strong> dmca@instago.app</p>
    <p><strong>प्रतिक्रिया समय:</strong> आमतौर पर २४ से ४८ घंटों के भीतर।</p>
</div>`
            }
        }
    };

    const subpagesI18n = {
        en: {
            contact: {
                breadHome: 'Home',
                breadCurrent: 'Contact & DMCA',
                badgeText: 'Support & Copyright Desk',
                titleText: 'Contact Us & DMCA Takedown Notice',
                subtitleText: 'We are here to assist with technical feedback, partner inquiries, and DMCA copyright compliance.',
                ch1TitleText: 'Technical Support & Feedback',
                ch1BadgeText: '24/7 Desk',
                ch1DescText: 'For issues with video downloading, browser compatibility, or suggestions:',
                ch2TitleText: 'DMCA & Copyright Compliance',
                ch2BadgeText: 'Safe Harbor',
                ch2DescText: 'For verified copyright holders, intellectual property, and link removal inquiries:',
                ch3TitleText: 'Business & Partnerships',
                ch3BadgeText: 'Enterprise',
                ch3DescText: 'For press, advertising inquiries, and technical infrastructure collaborations:',
                dmcaReqText: '<strong>DMCA Notice Requirements (17 U.S.C. § 512):</strong> When submitting a takedown notice, please provide: (1) Description of copyrighted work, (2) The exact Instagram URL to block, (3) Proof of copyright ownership, and (4) Your full contact credentials.',
                formTitleText: 'Send Us a Direct Message',
                formDescText: 'Fill out the form below and our dedicated support team will reply within 24 to 48 business hours.',
                labelNameText: 'Your Name *',
                labelEmailText: 'Email Address *',
                labelCategoryText: 'Inquiry Type',
                optGeneralText: 'General Support & Technical Feedback',
                optDmcaText: 'DMCA Copyright Takedown Notice',
                optBugText: 'Bug Report / Video Fetching Issue',
                optPartnerText: 'Business Partnership & Press',
                labelSubjectText: 'Subject',
                labelMessageText: 'Message *',
                btnSendMsgText: 'Send Message',
                trustLine: '🔒 We protect your data under our <a href="privacy.php" style="color:var(--accent-blue);">Privacy Policy</a>',
                respTime: 'Response: &le; 24h',
                btnHomeText: 'Back to Downloader',
                footerTermsLine: 'Read our <a href="terms.php" style="color:var(--accent-blue);">Terms of Service</a> &amp; <a href="privacy.php" style="color:var(--accent-blue);">Privacy Policy</a>'
            },
            about: {
                breadHome: 'Home',
                breadCurrent: 'About Us',
                badgeText: 'Mission & Engineering',
                titleText: 'About InstaGo',
                subtitleText: "Building the world's cleanest, fastest, and most privacy-focused Instagram media downloader.",
                btnHomeText: 'Start Downloading Media',
                assistText: 'Need assistance? <a href="contact.php" style="color:var(--accent-blue);">Contact Support</a>',
                articleBody: `<h2>Our Mission</h2>
<p><strong>InstaGo</strong> was founded with a straightforward goal: to provide an intuitive, high-speed, and secure web platform that empowers creators, educators, and social media enthusiasts to save publicly accessible Instagram media without intrusive advertisements, confusing redirects, or privacy compromises.</p>
<div class="legal-box-highlight success">
    <p><strong>Our Philosophy:</strong> Clean design, zero login hurdles, zero watermarks, and maximum native video fidelity (1080p Full HD MP4).</p>
</div>
<h2>What Makes InstaGo Unique?</h2>
<div class="legal-contact-grid">
    <div class="legal-contact-card">
        <h4>🎬 1080p Pure Fidelity</h4>
        <p>We never compress, resize, or alter original Instagram video bitrate or audio fidelity. Videos are delivered exactly as uploaded by original creators.</p>
    </div>
    <div class="legal-contact-card">
        <h4>🔒 Privacy by Design</h4>
        <p>No account required, no passwords stored, and zero tracking cookies. Your searches are processed entirely on-the-fly and never recorded.</p>
    </div>
    <div class="legal-contact-card">
        <h4>⚡ High-Speed CDN Proxy</h4>
        <p>Our distributed chunked proxy streams files with native download headers, preventing download timeouts and ensuring maximum download speed.</p>
    </div>
    <div class="legal-contact-card">
        <h4>📱 All Devices Supported</h4>
        <p>Engineered with a native app-like experience across iOS Safari (iPhone &amp; iPad), Android Chrome, Windows PC, Mac, and Linux.</p>
    </div>
</div>
<h2>Fair &amp; Ethical Service</h2>
<p>We believe in supporting content creators and respecting intellectual property. InstaGo operates strictly within fair-use boundaries by encouraging users to credit original creators when using media for educational or commentary purposes, and we offer responsive DMCA copyright removal channels for copyright holders.</p>
<h2>Technical Reliability &amp; Uptime</h2>
<p>We actively maintain multi-node failover parsing pipelines to guarantee 99.9% uptime, even when third-party social protocols update their delivery endpoints.</p>`
            },
            privacy: {
                breadHome: 'Home',
                breadCurrent: 'Privacy Policy',
                badgeText: 'Compliance & Transparency',
                titleText: 'Privacy Policy & Cookie Disclosure',
                subtitleText: `Last Updated: ${new Date().toLocaleDateString('en-US', {month: 'long', year: 'numeric'})} | Strict Compliance with Google AdSense, GDPR & CCPA`,
                tocTitle: 'On This Page',
                tocOverview: 'Overview & Scope',
                tocAdsense: '1. Google AdSense & Cookies',
                tocZeroLog: '2. Zero-Log Architecture',
                tocLogFiles: '3. Server Logs & CDN',
                tocThirdParty: '4. Third-Party Partners',
                tocCcpa: '5. CCPA Rights (California)',
                tocGdpr: '6. GDPR Compliance (EU)',
                tocChildren: "7. Children's Information",
                tocConsent: '8. Consent & Contact',
                btnHomeText: 'Back to Downloader',
                contactSupportText: 'Have inquiries? <a href="contact.php" style="color:var(--accent-blue);">Contact Support</a>',
                articleBody: `<div id="overview">
    <p>At <strong>InstaGo</strong>, the privacy of our visitors is of paramount importance to us. This Privacy Policy document details the types of information that is collected and recorded by InstaGo and how we safeguard and process it.</p>
    <div class="legal-box-highlight success">
        <p><strong>Zero-Credential Guarantee:</strong> We do NOT require user accounts, passwords, Instagram session credentials, or phone numbers. We do NOT store personal browsing history or media archives on our servers.</p>
    </div>
</div>
<h2 id="adsense">1. Google AdSense &amp; DoubleClick DART Cookies</h2>
<p>Google is a third-party vendor on our website. It utilizes cookies, specifically known as <strong>DART cookies</strong>, to serve personalized and non-personalized advertisements to our visitors based upon their visit to our site and other websites across the Internet.</p>
<p>Visitors may choose to opt out of the use of DART cookies by visiting the Google Ad and Content Network Privacy Policy at: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">https://policies.google.com/technologies/ads</a>.</p>
<p>For more detailed information regarding Google advertising practices and how Google handles user data when you visit partner sites, please review: <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener">Google Privacy &amp; Terms for Partner Sites</a>.</p>
<h2 id="zero-log">2. Information We Do NOT Collect (Zero-Log Guarantee)</h2>
<p>Unlike other platforms, InstaGo is engineered strictly as an on-the-fly gateway:</p>
<ul>
    <li><strong>No User Registration:</strong> You are never asked for your email, full name, address, or credit card details.</li>
    <li><strong>No Instagram Credentials:</strong> We never request or store your Instagram login username, password, two-factor authentication codes, or session tokens.</li>
    <li><strong>No Download History:</strong> Search queries and media requests are processed transiently in server memory and discarded immediately upon delivery.</li>
</ul>
<h2 id="log-files">3. Log Files &amp; CDN Infrastructure</h2>
<p>InstaGo follows a standard procedure of utilizing server log files. These files log visitors when they access websites. The information collected by log files includes internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of this information is for analyzing trends, administering the site, tracking users' movement around the website, and gathering broad demographic information to optimize delivery speed.</p>
<h2 id="third-party">4. Third-Party Advertising Partners</h2>
<p>Our advertising partners may utilize cookies, JavaScript, or Web Beacons in their respective advertisements and links that appear on InstaGo. These technologies measure the effectiveness of advertising campaigns and personalize the advertising content that you see.</p>
<p>Note that InstaGo has no access to or control over these cookies that are used by third-party advertisers.</p>
<h2 id="ccpa">5. CCPA Privacy Rights (Do Not Sell My Personal Information)</h2>
<p>Under the California Consumer Privacy Act (CCPA), California consumers have the right to request that a business that collects a consumer's personal data disclose the categories and specific pieces of personal data that a business has collected about consumers, or request deletion of any consumer data collected. Because InstaGo does not sell, trade, or retain personal data, we already exceed all CCPA compliance guidelines.</p>
<h2 id="gdpr">6. GDPR Data Protection Rights</h2>
<p>We want to make sure you are fully aware of all of your data protection rights under the General Data Protection Regulation (GDPR). Every user is entitled to the right to access, rectification, erasure, and restriction of processing under applicable statutory provisions.</p>
<h2 id="children">7. Children's Information &amp; COPPA</h2>
<p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity. InstaGo does not knowingly collect any Personal Identifiable Information from children under the age of 13.</p>
<h2 id="consent">8. Consent &amp; Contacting Our DPO</h2>
<p>By using our website, you hereby consent to our Privacy Policy and agree to its Terms and Conditions. If you have any additional questions or require more information, please reach out via our dedicated contact page.</p>`
            },
            terms: {
                breadHome: 'Home',
                breadCurrent: 'Terms of Service',
                badgeText: 'Usage Guidelines',
                titleText: 'Terms of Service & Fair Use',
                subtitleText: `Effective Date: ${new Date().toLocaleDateString('en-US', {month: 'long', year: 'numeric'})} | Please read these conditions carefully before using our platform.`,
                tocTitle: 'On This Page',
                tocIntro: 'Agreement to Terms',
                tocFairUse: '1. Fair Use & Purpose',
                tocIp: '2. IP & Hosting Disclaimer',
                tocConduct: '3. User Conduct',
                tocWarranties: '4. Disclaimer of Warranties',
                tocLiability: '5. Limitation of Liability',
                tocModifications: '6. Modifications',
                tocLaw: '7. Governing Law',
                btnHomeText: 'Back to Downloader',
                reviewPrivacyText: 'Review our <a href="privacy.php" style="color:var(--accent-blue);">Privacy Policy</a>',
                articleBody: `<div id="intro">
    <p>Welcome to <strong>InstaGo</strong>. By accessing or using our website, you agree that you have read, understood, and accept to be bound by these Terms of Service. If you do not agree with these terms, you must discontinue using our services immediately.</p>
</div>
<h2 id="fair-use">1. Fair Use &amp; Educational Purpose</h2>
<p>InstaGo is designed as an educational web utility to assist users in downloading and creating backup copies of publicly available Instagram content (such as Reels, Stories, Highlights, and Profile Pictures) for personal, non-commercial, offline viewing purposes.</p>
<div class="legal-box-highlight warning">
    <p><strong>Notice:</strong> InstaGo is strictly independent and is NOT affiliated, authorized, endorsed, sponsored, or certified in any manner by Instagram, Meta Platforms, Inc., or any of their subsidiaries.</p>
</div>
<h2 id="ip-disclaimer">2. Intellectual Property Rights &amp; Hosting Disclaimer</h2>
<p><strong>InstaGo does not host, store, archive, or replicate any copyrighted audio, video, or image media on its servers.</strong></p>
<p>All multimedia streams processed by this service are retrieved on-demand and delivered directly from public Content Delivery Network (CDN) servers operated by Instagram. All copyrights, trademarks, service marks, and trade names remain the exclusive property of their respective creators and copyright owners.</p>
<h2 id="user-conduct">3. User Responsibilities &amp; Prohibited Conduct</h2>
<p>As a condition of your use of InstaGo, you agree that you will not utilize downloaded materials for unauthorized public broadcast, commercial sale, resale, or distribution without explicit written consent from the copyright holder, nor attempt to disrupt or flood service infrastructure.</p>
<h2 id="warranties">4. Disclaimer of Warranties</h2>
<p>The service is provided on an "AS IS" and "AS AVAILABLE" basis without warranties of any kind, whether express, statutory, or implied.</p>
<h2 id="liability">5. Limitation of Liability</h2>
<p>To the maximum extent permitted by applicable law, in no event shall InstaGo, its developers, or affiliates be liable for any direct, indirect, incidental, or consequential damages arising from your access to or inability to use this web utility.</p>
<h2 id="modifications">6. Modifications to Terms</h2>
<p>We reserve the right to revise, modify, or replace these Terms of Service at any time without prior notice. Any modifications become effective immediately upon being posted on this page.</p>
<h2 id="governing-law">7. Governing Law &amp; Severability</h2>
<p>These Terms shall be interpreted and governed in accordance with international digital copyright practices and statutory safe harbor conventions.</p>`
            }
        },
        gu: {
            contact: {
                breadHome: 'હોમ',
                breadCurrent: 'સંપર્ક અને DMCA',
                badgeText: 'સપોર્ટ અને કોપીરાઇટ ડેસ્ક',
                titleText: 'અમારો સંપર્ક કરો અને DMCA નોટિસ',
                subtitleText: 'ટેકનિકલ ફીડબેક, બિઝનેસ પૂછપરછ અને DMCA કોપીરાઇટ મદદ માટે અમે અહીં હાજર છીએ.',
                ch1TitleText: 'ટેકનિકલ સપોર્ટ અને ફીડબેક',
                ch1BadgeText: '૨૪/૭ ડેસ્ક',
                ch1DescText: 'વિડીયો ડાઉનલોડિંગ, બ્રાઉઝર સમસ્યા અથવા સૂચનો માટે સંપર્ક કરો:',
                ch2TitleText: 'DMCA અને કોપીરાઇટ અનુપાલન',
                ch2BadgeText: 'સુરક્ષિત હાર્બર',
                ch2DescText: 'વેરિફાઇડ કોપીરાઇટ ધારકો, બૌદ્ધિક સંપદા અને લિંક દૂર કરવાની વિનંતીઓ માટે:',
                ch3TitleText: 'બિઝનેસ અને ભાગીદારી',
                ch3BadgeText: 'એન્ટરપ્રાઇઝ',
                ch3DescText: 'પ્રેસ, જાહેરાત પૂછપરછ અને ટેકનિકલ ઇન્ફ્રાસ્ટ્રક્ચર સહયોગ માટે:',
                dmcaReqText: '<strong>DMCA નોટિસ જરૂરિયાતો (17 U.S.C. § 512):</strong> વિનંતી મોકલતી વખતે કૃપા કરીને આપો: (૧) કોપીરાઇટ સામગ્રીની વિગત, (૨) બ્લોક કરવા માટેની ચોક્કસ ઇન્સ્ટાગ્રામ URL, (૩) માલિકીનો પુરાવો અને (૪) તમારી સંપર્ક વિગતો.',
                formTitleText: 'અમને ડાયરેક્ટ મેસેજ મોકલો',
                formDescText: 'નીચેનું ફોર્મ ભરો અને અમારી સપોર્ટ ટીમ ૨૪ થી ૪૮ કામકાજના કલાકોમાં જવાબ આપશે.',
                labelNameText: 'તમારું નામ *',
                labelEmailText: 'ઇમેઇલ એડ્રેસ *',
                labelCategoryText: 'પૂછપરછનો પ્રકાર',
                optGeneralText: 'સામાન્ય સપોર્ટ અને ટેકનિકલ ફીડબેક',
                optDmcaText: 'DMCA કોપીરાઇટ ટેકડાઉન નોટિસ',
                optBugText: 'બગ રિપોર્ટ / વિડીયો ફેચિંગ સમસ્યા',
                optPartnerText: 'બિઝનેસ પાર્ટનરશિપ અને પ્રેસ',
                labelSubjectText: 'વિષય (Subject)',
                labelMessageText: 'તમારો સંદેશ *',
                btnSendMsgText: 'મેસેજ મોકલો',
                trustLine: '🔒 તમારો ડેટા અમારી <a href="privacy.php" style="color:var(--accent-blue);">પ્રાઇવેસી પોલિસી</a> હેઠળ સુરક્ષિત છે',
                respTime: 'જવાબ: &le; ૨૪ કલાક',
                btnHomeText: 'ડાઉનલોડર પર પાછા જાઓ',
                footerTermsLine: 'અમારી <a href="terms.php" style="color:var(--accent-blue);">સેવાની શરતો</a> અને <a href="privacy.php" style="color:var(--accent-blue);">પ્રાઇવેસી પોલિસી</a> વાંચો'
            },
            about: {
                breadHome: 'હોમ',
                breadCurrent: 'અમારા વિશે',
                badgeText: 'મિશન અને એન્જિનિયરિંગ',
                titleText: 'InstaGo વિશે',
                subtitleText: 'વિશ્વનું સૌથી સ્વચ્છ, સૌથી ઝડપી અને સંપૂર્ણ ગુપ્ત ઇન્સ્ટાગ્રામ મીડિયા ડાઉનલોડર.',
                btnHomeText: 'મીડિયા ડાઉનલોડ કરવાનું શરૂ કરો',
                assistText: 'કોઈ મદદ જોઈએ છે? <a href="contact.php" style="color:var(--accent-blue);">સપોર્ટ ટીમનો સંપર્ક કરો</a>',
                articleBody: `<h2>અમારું મિશન</h2>
<p><strong>InstaGo</strong> ની સ્થાપના એક સ્પષ્ટ લક્ષ્ય સાથે કરવામાં આવી હતી: ક્રિએટર્સ, શિક્ષકો અને સોશિયલ મીડિયા ઉત્સાહીઓને કોઈપણ હેરાન કરતી પોપઅપ જાહેરાતો, અસ્પષ્ટ રીડાયરેક્ટ્સ અથવા પ્રાઇવેસી જોખમ વિના પબ્લિક ઇન્સ્ટાગ્રામ મીડિયા સેવ કરવા માટે ઝડપી, સલામત અને સરળ પ્લેટફોર્મ પૂરું પાડવું.</p>
<div class="legal-box-highlight success">
    <p><strong>અમારી ફિલસૂફી:</strong> સ્વચ્છ ડિઝાઇન, કોઈ લોગિન ઝંઝટ નહીં, ઝીરો વોટરમાર્ક અને ૧૦૦% ઓરિજિનલ વિડીયો ક્વોલિટી (1080p Full HD MP4).</p>
</div>
<h2>શા માટે InstaGo અનન્ય છે?</h2>
<div class="legal-contact-grid">
    <div class="legal-contact-card">
        <h4>🎬 1080p શુદ્ધ ક્વોલિટી</h4>
        <p>અમે ક્યારેય વિડિયો ક્વોલિટી કે ઓડિયો બિટરેટ ઘટાડતા નથી. મૂળ ક્રિએટરે જેવી ક્વોલિટીમાં અપલોડ કર્યું હોય તેવું જ શુદ્ધ વિડીયો મળે છે.</p>
    </div>
    <div class="legal-contact-card">
        <h4>🔒 પ્રાઇવેસી બાય ડિઝાઇન</h4>
        <p>કોઈ એકાઉન્ટની જરૂર નથી, પાસવર્ડ સેવ થતો નથી અને કોઈ ટ્રેકિંગ કૂકીઝ નથી. તમારી બધી જ સર્ચ પ્રક્રિયા સંપૂર્ણપણે ગુપ્ત રહે છે.</p>
    </div>
    <div class="legal-contact-card">
        <h4>⚡ હાઇ-સ્પીડ CDN પ્રોક્સી</h4>
        <p>અમારું વિતરિત નેટવર્ક ફાઇલોને નેટિવ હેડર્સ સાથે ઝડપથી સ્ટ્રીમ કરે છે, જેથી ટાઇમઆઉટ થયા વગર મહત્તમ ડાઉનલોડ સ્પીડ મળે છે.</p>
    </div>
    <div class="legal-contact-card">
        <h4>📱 તમામ ડિવાઇસ સપોર્ટ</h4>
        <p>iOS Safari (iPhone &amp; iPad), Android Chrome, Windows PC, Mac અને Linux પર એકદમ સ્મૂધ અને એપ જેવો અનુભવ આપે છે.</p>
    </div>
</div>
<h2>નૈતિક અને વાજબી સેવા</h2>
<p>અમે કન્ટેન્ટ ક્રિએટર્સ અને બૌદ્ધિક સંપદાનું સંપૂર્ણ સન્માન કરીએ છીએ. InstaGo શૈક્ષણિક અને વ્યક્તિગત સંદર્ભ માટે ફેર-યુઝના નિયમો હેઠળ કામ કરે છે અને કોપીરાઇટ માલિકો માટે ઝડપી DMCA રીમૂવલ સુવિધા પૂરી પાડે છે.</p>
<h2>ટેકનિકલ સ્થિરતા અને અપટાઇમ</h2>
<p>જ્યારે પણ તૃતીય-પક્ષ સોશિયલ પ્રોટોકોલ્સ બદલાય ત્યારે પણ અમારી સર્વિસ ૯૯.૯% ચાલુ રહે તે માટે અમે મલ્ટી-નોડ ફેલઓવર આર્કિટેક્ચર સક્રિય રાખીએ છીએ.</p>`
            },
            privacy: {
                breadHome: 'હોમ',
                breadCurrent: 'પ્રાઇવેસી પોલિસી',
                badgeText: 'પારદર્શિતા અને નિયમોનું પાલન',
                titleText: 'પ્રાઇવેસી પોલિસી અને કૂકી ડિસ્ક્લોઝર',
                subtitleText: `છેલ્લું અપડેટ: ${new Date().toLocaleDateString('gu-IN', {month: 'long', year: 'numeric'})} | Google AdSense, GDPR અને CCPA ના કડક નિયમો સાથે સુસંગત`,
                tocTitle: 'આ પેજ પર',
                tocOverview: 'વિહંગાવલોકન અને ક્ષેત્ર',
                tocAdsense: '૧. Google AdSense અને કૂકીઝ',
                tocZeroLog: '૨. ઝીરો-લોગ આર્કિટેક્ચર',
                tocLogFiles: '૩. સર્વર લોગ્સ અને CDN',
                tocThirdParty: '૪. તૃતીય-પક્ષ ભાગીદારો',
                tocCcpa: '૫. CCPA અધિકારો (California)',
                tocGdpr: '૬. GDPR ડેટા સુરક્ષા (EU)',
                tocChildren: '૭. બાળકોની સુરક્ષા',
                tocConsent: '૮. સંમતિ અને સંપર્ક',
                btnHomeText: 'ડાઉનલોડર પર પાછા જાઓ',
                contactSupportText: 'પ્રશ્નો છે? <a href="contact.php" style="color:var(--accent-blue);">સપોર્ટ ટીમનો સંપર્ક કરો</a>',
                articleBody: `<div id="overview">
    <p><strong>InstaGo</strong> પર અમારા મુલાકાતીઓની પ્રાઇવેસી અમારા માટે સર્વોપરી છે. આ પ્રાઇવેસી પોલિસી દસ્તાવેજ દર્શાવે છે કે InstaGo દ્વારા કઈ માહિતી એકત્રિત થાય છે અને અમે તેનું કેવી રીતે રક્ષણ કરીએ છીએ.</p>
    <div class="legal-box-highlight success">
        <p><strong>ઝીરો-ક્રેડેન્શિયલ ગેરંટી:</strong> અમને કોઈપણ યુઝર એકાઉન્ટ, પાસવર્ડ, ઇન્સ્ટાગ્રામ લોગિન કે ફોન નંબરની જરૂર નથી. અમે અમારા સર્વર પર કોઈપણ પર્સનલ બ્રાઉઝિંગ ઇતિહાસ કે મીડિયા સ્ટોર કરતા નથી.</p>
    </div>
</div>
<h2 id="adsense">૧. Google AdSense અને DoubleClick DART કૂકીઝ</h2>
<p>ગૂગલ અમારી વેબસાઇટ પર તૃતીય-પક્ષ વેન્ડર છે. તે મુલાકાતીઓને તેમની મુલાકાતના આધારે યોગ્ય અને સંબંધિત જાહેરાતો બતાવવા માટે <strong>DART કૂકીઝ</strong> નો ઉપયોગ કરે છે.</p>
<p>મુલાકાતીઓ આ લિંક પર જઈને DART કૂકીઝના ઉપયોગને નાપસંદ કરી શકે છે: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">https://policies.google.com/technologies/ads</a>.</p>
<p>ગૂગલ ડેટાનો ઉપયોગ કેવી રીતે કરે છે તેની વિગતવાર માહિતી માટે જુઓ: <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener">Google Privacy &amp; Terms for Partner Sites</a>.</p>
<h2 id="zero-log">૨. અમે કઈ માહિતી એકત્રિત કરતા નથી (ઝીરો-લોગ ગેરંટી)</h2>
<p>અન્ય પ્લેટફોર્મ્સથી વિપરીત, InstaGo ફક્ત એક ઓન-ધ-ફ્લાય ગેટવે તરીકે ડિઝાઇન કરવામાં આવ્યું છે:</p>
<ul>
    <li><strong>કોઈ યુઝર રજીસ્ટ્રેશન નહીં:</strong> તમારી પાસેથી ક્યારેય ઇમેઇલ, નામ કે ક્રેડિટ કાર્ડ વિગતો માંગવામાં આવતી નથી.</li>
    <li><strong>કોઈ ઇન્સ્ટાગ્રામ લોગિન નહીં:</strong> અમે ક્યારેય તમારું ઇન્સ્ટાગ્રામ યુઝરનેમ કે પાસવર્ડ સેવ કરતા નથી.</li>
    <li><strong>કોઈ ડાઉનલોડ હિસ્ટ્રી નહીં:</strong> સર્ચ કરેલી મીડિયા રિક્વેસ્ટ તાત્કાલિક પ્રોસેસ થાય છે અને તરત જ ડિલીટ થઈ જાય છે.</li>
</ul>
<h2 id="log-files">૩. સર્વર લોગ ફાઇલ્સ અને CDN ઇન્ફ્રાસ્ટ્રક્ચર</h2>
<p>InstaGo સામાન્ય સર્વર લોગિંગ પ્રક્રિયાને અનુસરે છે જેમાં IP એડ્રેસ, બ્રાઉઝરનો પ્રકાર અને સમય નોંધાય છે. આ માહિતી કોઈપણ વ્યક્તિગત ઓળખ સાથે જોડાયેલી નથી અને તેનો ઉપયોગ માત્ર સાઇટની ઝડપ અને સ્થિરતા વધારવા માટે થાય છે.</p>
<h2 id="third-party">૪. તૃતીય-પક્ષ જાહેરાત ભાગીદારો</h2>
<p>અમારા જાહેરાત ભાગીદારો જાહેરાતોની અસરકારકતા માપવા માટે કૂકીઝનો ઉપયોગ કરી શકે છે. InstaGo નું આ કૂકીઝ પર કોઈ નિયંત્રણ હોતું નથી.</p>
<h2 id="ccpa">૫. CCPA પ્રાઇવેસી અધિકારો (કેલિફોર્નિયા)</h2>
<p>કેલિફોર્નિયા કન્ઝ્યુમર પ્રાઇવેસી એક્ટ (CCPA) હેઠળ વપરાશકર્તાઓને ડેટા સુરક્ષાના અધિકારો મળે છે. InstaGo કોઈપણ પર્સનલ ડેટા વેચતું કે સાચવતું નથી, તેથી અમે તમામ CCPA નિયમોનું સંપૂર્ણ પાલન કરીએ છીએ.</p>
<h2 id="gdpr">૬. GDPR ડેટા સુરક્ષા અધિકારો (EU)</h2>
<p>જનરલ ડેટા પ્રોટેક્શન રેગ્યુલેશન (GDPR) હેઠળ તમામ મુલાકાતીઓને માહિતી મેળવવા, સુધારવા અને ડિલીટ કરવાના સંપૂર્ણ અધિકારો ઉપલબ્ધ છે.</p>
<h2 id="children">૭. બાળકોની સુરક્ષા અને COPPA</h2>
<p>ઇન્ટરનેટનો ઉપયોગ કરતી વખતે બાળકોનું રક્ષણ કરવું એ અમારી પ્રાથમિકતા છે. InstaGo ૧૩ વર્ષથી ઓછી ઉંમરના બાળકો પાસેથી કોઈપણ ઓળખી શકાય તેવી વ્યક્તિગત માહિતી એકત્રિત કરતું નથી.</p>
<h2 id="consent">૮. સંમતિ અને DPO સંપર્ક</h2>
<p>અમારી વેબસાઇટનો ઉપયોગ કરીને, તમે આ પ્રાઇવેસી પોલિસી અને શરતો સાથે સંમત થાઓ છો. કોઈપણ પ્રશ્ન માટે કૃપા કરીને અમારા સંપર્ક પેજ દ્વારા અમારો સંપર્ક કરો.</p>`
            },
            terms: {
                breadHome: 'હોમ',
                breadCurrent: 'સેવાની શરતો',
                badgeText: 'ઉપયોગની માર્ગદર્શિકા',
                titleText: 'સેવાની શરતો અને ફેર-યુઝ',
                subtitleText: `લાગુ તારીખ: ${new Date().toLocaleDateString('gu-IN', {month: 'long', year: 'numeric'})} | અમારા પ્લેટફોર્મનો ઉપયોગ કરતા પહેલાં કૃપા કરીને આ શરતો ધ્યાનથી વાંચો.`,
                tocTitle: 'આ પેજ પર',
                tocIntro: 'શરતોની સંમતિ',
                tocFairUse: '૧. ફેર-યુઝ અને શૈક્ષણિક હેતુ',
                tocIp: '૨. બૌદ્ધિક સંપદા અને નો-હોસ્ટિંગ',
                tocConduct: '૩. વપરાશકર્તાની જવાબદારી',
                tocWarranties: '૪. વૉરંટી અસ્વીકરણ',
                tocLiability: '૫. જવાબદારીની મર્યાદા',
                tocModifications: '૬. શરતોમાં સુધારા',
                tocLaw: '૭. લાગુ કાયદો',
                btnHomeText: 'ડાઉનલોડર પર પાછા જાઓ',
                reviewPrivacyText: 'અમારી <a href="privacy.php" style="color:var(--accent-blue);">પ્રાઇવેસી પોલિસી</a> જુઓ',
                articleBody: `<div id="intro">
    <p><strong>InstaGo</strong> માં આપનું સ્વાગત છે. અમારી વેબસાઇટનો ઉપયોગ કરીને તમે આ સેવાની શરતોનું પાલન કરવા સંમત થાઓ છો. જો તમે કોઈપણ શરત સાથે સંમત ન હોવ, તો કૃપા કરીને સેવાનો ઉપયોગ કરશો નહીં.</p>
</div>
<h2 id="fair-use">૧. ફેર-યુઝ અને શૈક્ષણિક હેતુ</h2>
<p>InstaGo એક શૈક્ષણિક વેબ ટૂલ તરીકે ડિઝાઇન કરવામાં આવ્યું છે જે વપરાશકર્તાઓને વ્યક્તિગત અને બિન-વ્યાવસાયિક ઑફલાઇન જોવા માટે સાર્વજનિક ઇન્સ્ટાગ્રામ સામગ્રી (જેમ કે રીલ્સ, સ્ટોરીઝ, હાઇલાઇટ્સ અને પ્રોફાઇલ ફોટા) ડાઉનલોડ કરવામાં મદદ કરે છે.</p>
<div class="legal-box-highlight warning">
    <p><strong>સૂચના:</strong> InstaGo સંપૂર્ણપણે સ્વતંત્ર છે અને ઇન્સ્ટાગ્રામ, Meta Platforms Inc. કે તેમની કોઈપણ પેટાકંપની સાથે જોડાયેલું નથી.</p>
</div>
<h2 id="ip-disclaimer">૨. બૌદ્ધિક સંપદા અધિકારો અને નો-હોસ્ટિંગ ડિસ્ક્લેમર</h2>
<p><strong>InstaGo પોતાના સર્વર પર કોઈપણ કોપીરાઇટ કરેલ ઓડિયો, વિડિયો કે ફોટો મીડિયા હોસ્ટ, સ્ટોર કે આર્કાઇવ કરતું નથી.</strong></p>
<p>બધા મીડિયા સ્ટ્રીમ્સ સીધા ઇન્સ્ટાગ્રામના સત્તાવાર Content Delivery Network (CDN) સર્વર પરથી જ લાવવામાં આવે છે. તમામ કોપીરાઇટ અને ટ્રેડમાર્ક્સ મૂળ ક્રિએટર્સના રહે છે.</p>
<h2 id="user-conduct">૩. વપરાશકર્તાની જવાબદારી અને પ્રતિબંધિત વર્તન</h2>
<p>ડાઉનલોડ કરેલ સામગ્રીનો ઉપયોગ અનધિકૃત વ્યાપારી હેતુઓ કે પુનઃવેચાણ માટે કરવાની સખત મનાઈ છે. વપરાશકર્તાઓએ હંમેશા ક્રિએટર્સના કોપીરાઇટનું સન્માન કરવું જરૂરી છે.</p>
<h2 id="warranties">૪. વૉરંટી અસ્વીકરણ</h2>
<p>આ સેવા કોઈપણ પ્રકારની સ્પષ્ટ કે ગર્ભિત વોરંટી વિના "જેવી છે તેવી" (AS IS) ઉપલબ્ધ કરાવવામાં આવે છે.</p>
<h2 id="liability">૫. જવાબદારીની મર્યાદા</h2>
<p>લાગુ કાયદા હેઠળ અનુમતિ મુજબ, InstaGo કે તેના ડેવલપર્સ આ ટૂલના ઉપયોગથી થતા કોઈપણ પ્રત્યક્ષ કે પરોક્ષ નુકસાન માટે જવાબદાર રહેશે નહીં.</p>
<h2 id="modifications">૬. શરતોમાં સુધારા</h2>
<p>અમે કોઈપણ સમયે આ સેવાની શરતોમાં ફેરફાર કરવાનો અધિકાર અનામત રાખીએ છીએ. સુધારેલી શરતો આ પેજ પર મુકાયા પછી તરત જ લાગુ થશે.</p>
<h2 id="governing-law">૭. લાગુ કાયદો</h2>
<p>આ શરતો આંતરરાષ્ટ્રીય ડિજિટલ કોપીરાઇટ નિયમો અને સેફ હાર્બર સિદ્ધાંતો અનુસાર સંચાલિત થાય છે.</p>`
            }
        },
        hi: {
            contact: {
                breadHome: 'होम',
                breadCurrent: 'संपर्क और DMCA',
                badgeText: 'सपोर्ट और कॉपीराइट डेस्क',
                titleText: 'हमसे संपर्क करें और DMCA नोटिस',
                subtitleText: 'तकनीकी प्रतिक्रिया, व्यावसायिक पूछताछ और DMCA कॉपीराइट सहायता के लिए हम उपलब्ध हैं।',
                ch1TitleText: 'तकनीकी सहायता और फीडबैक',
                ch1BadgeText: '२४/७ डेस्क',
                ch1DescText: 'वीडियो डाउनलोड करने में समस्या, ब्राउज़र अनुकूलता या सुझावों के लिए:',
                ch2TitleText: 'DMCA और कॉपीराइट अनुपालन',
                ch2BadgeText: 'सुरक्षित हार्बर',
                ch2DescText: 'सत्यापित कॉपीराइट धारकों, बौद्धिक संपदा और लिंक हटाने की पूछताछ के लिए:',
                ch3TitleText: 'व्यापार और साझेदारी',
                ch3BadgeText: 'एंटरप्राइज',
                ch3DescText: 'प्रेस, विज्ञापन पूछताछ और तकनीकी सहयोग के लिए:',
                dmcaReqText: '<strong>DMCA नोटिस आवश्यकताएं (17 U.S.C. § 512):</strong> नोटिस भेजते समय कृपया प्रदान करें: (१) कॉपीराइट कार्य का विवरण, (२) ब्लॉक करने के लिए सटीक इंस्टाग्राम URL, (३) स्वामित्व का प्रमाण और (૪) अपनी संपर्क जानकारी।',
                formTitleText: 'हमें सीधा संदेश भेजें',
                formDescText: 'नीचे दिया गया फॉर्म भरें और हमारी टीम २४ से ४८ व्यावसायिक घंटों में उत्तर देगी।',
                labelNameText: 'आपका नाम *',
                labelEmailText: 'ईमेल पता *',
                labelCategoryText: 'पूछताछ का प्रकार',
                optGeneralText: 'सामान्य सहायता और तकनीकी प्रतिक्रिया',
                optDmcaText: 'DMCA कॉपीराइट टेकडाउन नोटिस',
                optBugText: 'बग रिपोर्ट / वीडियो लाने में समस्या',
                optPartnerText: 'व्यावसायिक साझेदारी और प्रेस',
                labelSubjectText: 'विषय (Subject)',
                labelMessageText: 'संदेश *',
                btnSendMsgText: 'संदेश भेजें',
                trustLine: '🔒 आपका डेटा हमारी <a href="privacy.php" style="color:var(--accent-blue);">प्राइवेसी पॉलिसी</a> के तहत सुरक्षित है',
                respTime: 'प्रतिक्रिया: &le; २४ घंटे',
                btnHomeText: 'डाउनलोडर पर वापस जाएं',
                footerTermsLine: 'हमारी <a href="terms.php" style="color:var(--accent-blue);">सेवा की शर्तें</a> और <a href="privacy.php" style="color:var(--accent-blue);">प्राइवेसी पॉलिसी</a> पढ़ें'
            },
            about: {
                breadHome: 'होम',
                breadCurrent: 'हमारे बारे में',
                badgeText: 'मिशन और इंजीनियरिंग',
                titleText: 'InstaGo के बारे में',
                subtitleText: 'दुनिया का सबसे स्वच्छ, सबसे तेज़ और पूर्ण गोपनीय इंस्टाग्राम मीडिया डाउनलोडर।',
                btnHomeText: 'मीडिया डाउनलोड करना शुरू करें',
                assistText: 'सहायता चाहिए? <a href="contact.php" style="color:var(--accent-blue);">सपोर्ट टीम से संपर्क करें</a>',
                articleBody: `<h2>हमारा मिशन</h2>
<p><strong>InstaGo</strong> की स्थापना एक स्पष्ट लक्ष्य के साथ की गई थी: बिना किसी कष्टप्रद विज्ञापनों, भ्रामक रीडायरेक्ट्स या प्राइवेसी समझौते के क्रिएटर्स, शिक्षकों और सोशल मीडिया प्रेमियों को सार्वजनिक इंस्टाग्राम मीडिया सेव करने के लिए एक तेज़, सुरक्षित और सहज वेब प्लेटफ़ॉर्म प्रदान करना।</p>
<div class="legal-box-highlight success">
    <p><strong>हमारा दर्शन:</strong> स्वच्छ डिज़ाइन, बिना लॉगिन झंझट, शून्य वॉटरमार्क और अधिकतम मूल वीडियो स्पष्टता (1080p Full HD MP4)।</p>
</div>
<h2>क्या InstaGo को अद्वितीय बनाता है?</h2>
<div class="legal-contact-grid">
    <div class="legal-contact-card">
        <h4>🎬 1080p शुद्ध स्पष्टता</h4>
        <p>हम कभी भी वीडियो रिज़ॉल्यूशन या ऑडियो बिटरेट को संपीड़ित (compress) नहीं करते हैं। वीडियो बिल्कुल वैसे ही मिलता है जैसे मूल क्रिएटर ने अपलोड किया था।</p>
    </div>
    <div class="legal-contact-card">
        <h4>🔒 प्राइवेसी बाय डिज़ाइन</h4>
        <p>किसी अकाउंट की आवश्यकता नहीं, कोई पासवर्ड स्टोर नहीं होता और शून्य ट्रैकिंग कुकीज़। आपकी सभी खोजें पूरी तरह से गोपनीय रहती हैं।</p>
    </div>
    <div class="legal-contact-card">
        <h4>⚡ हाई-स्पीड CDN प्रॉक्सी</h4>
        <p>हमारा वितरित नेटवर्क फाइलों को सीधे स्ट्रीम करता है, जिससे डाउनलोड टाइमआउट नहीं होता और अधिकतम डाउनलोड गति मिलती है।</p>
    </div>
    <div class="legal-contact-card">
        <h4>📱 सभी डिवाइस समर्थित</h4>
        <p>iOS Safari (iPhone &amp; iPad), Android Chrome, Windows PC, Mac और Linux पर नेटिव ऐप जैसा सहज अनुभव प्रदान करता है।</p>
    </div>
</div>
<h2>उचित और नैतिक सेवा</h2>
<p>हम कंटेंट क्रिएटर्स और बौद्धिक संपदा का पूरा सम्मान करते हैं। InstaGo निष्पक्ष उपयोग (Fair Use) के तहत व्यक्तिगत संदर्भ के लिए संचालित होता है और कॉपीराइट धारकों के लिए त्वरित DMCA हटाने की सुविधा प्रदान करता है।</p>
<h2>तकनीकी विश्वसनीयता और अपटाइम</h2>
<p>थर्ड-पार्टी सोशल प्रोटोकॉल अपडेट होने पर भी ९૯.૯% निरंतर सेवा सुनिश्चित करने के लिए हम मल्टी-नोड बैकअप सिस्टम बनाए रखते हैं।</p>`
            },
            privacy: {
                breadHome: 'होम',
                breadCurrent: 'प्राइवेसी पॉलिसी',
                badgeText: 'पारदर्शिता और अनुपालन',
                titleText: 'प्राइवेसी पॉलिसी और कुकी प्रकटीकरण',
                subtitleText: `अंतिम अपडेट: ${new Date().toLocaleDateString('hi-IN', {month: 'long', year: 'numeric'})} | Google AdSense, GDPR और CCPA के सख्त नियमों के अनुरूप`,
                tocTitle: 'इस पेज पर',
                tocOverview: 'अवलोकन और दायरा',
                tocAdsense: '१. Google AdSense और कुकीज़',
                tocZeroLog: '२. ज़ीरो-लॉग आर्किटेक्चर',
                tocLogFiles: '३. सर्वर लॉग्स और CDN',
                tocThirdParty: '४. थर्ड-पार्टी पार्टनर्स',
                tocCcpa: '५. CCPA अधिकार (कैलिफ़ोर्निया)',
                tocGdpr: '६. GDPR डेटा सुरक्षा (EU)',
                tocChildren: '७. बच्चों की सुरक्षा',
                tocConsent: '૮. सहमति और संपर्क',
                btnHomeText: 'डाउनलोडर पर वापस जाएं',
                contactSupportText: 'कोई प्रश्न है? <a href="contact.php" style="color:var(--accent-blue);">सपोर्ट टीम से संपर्क करें</a>',
                articleBody: `<div id="overview">
    <p><strong>InstaGo</strong> पर हमारे आगंतुकों की गोपनीयता हमारे लिए अत्यंत महत्वपूर्ण है। यह प्राइवेसी पॉलिसी दस्तावेज़ बताता है कि InstaGo द्वारा किस प्रकार की जानकारी एकत्र की जाती है और हम इसे कैसे सुरक्षित रखते हैं।</p>
    <div class="legal-box-highlight success">
        <p><strong>शून्य-क्रेडेंशियल गारंटी:</strong> हमें किसी यूज़र अकाउंट, पासवर्ड, इंस्टाग्राम लॉगिन या फ़ोन नंबर की आवश्यकता नहीं है। हम अपने सर्वर पर कोई व्यक्तिगत ब्राउज़िंग इतिहास या मीडिया फ़ाइलें संग्रहीत नहीं करते हैं।</p>
    </div>
</div>
<h2 id="adsense">१. Google AdSense और DoubleClick DART कुकीज़</h2>
<p>Google हमारी वेबसाइट पर एक थर्ड-पार्टी वेंडर है। यह हमारी साइट और इंटरनेट पर अन्य साइटों पर आने वाले उपयोगकर्ताओं को प्रासंगिक विज्ञापन दिखाने के लिए <strong>DART कुकीज़</strong> का उपयोग करता है।</p>
<p>उपयोगकर्ता इस लिंक पर जाकर DART कुकीज़ के उपयोग से बाहर निकल सकते हैं: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">https://policies.google.com/technologies/ads</a>.</p>
<p>Google द्वारा डेटा उपयोग के बारे में अधिक जानकारी के लिए देखें: <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener">Google Privacy &amp; Terms for Partner Sites</a>.</p>
<h2 id="zero-log">२. जानकारी जो हम एकत्र नहीं करते (ज़ीरो-लॉग गारंटी)</h2>
<p>अन्य प्लेटफॉर्मों के विपरीत, InstaGo को केवल ऑन-द-फ़्लाई गेटवे के रूप में बनाया गया है:</p>
<ul>
    <li><strong>कोई यूज़र पंजीकरण नहीं:</strong> आपसे कभी भी ईमेल, नाम या क्रेडिट कार्ड विवरण नहीं मांगा जाता है।</li>
    <li><strong>कोई इंस्टाग्राम लॉगिन नहीं:</strong> हम कभी भी आपका इंस्टाग्राम पासवर्ड या टोकन संग्रहीत नहीं करते हैं।</li>
    <li><strong>कोई डाउनलोड इतिहास नहीं:</strong> मीडिया अनुरोध तुरंत प्रोसेस होते हैं और डिलीवरी के बाद नष्ट हो जाते हैं।</li>
</ul>
<h2 id="log-files">३. सर्वर लॉग फाइल्स और CDN इन्फ्रास्ट्रक्चर</h2>
<p>InstaGo मानक सर्वर लॉगिंग प्रक्रियाओं का पालन करता है। यह डेटा केवल सिस्टम प्रशासन, सुरक्षा और गति अनुकूलन के लिए उपयोग किया जाता है।</p>
<h2 id="third-party">४. थर्ड-पार्टी विज्ञापन भागीदार</h2>
<p>हमारे विज्ञापन भागीदार विज्ञापनों की प्रभावशीलता मापने के लिए कुकीज़ का उपयोग कर सकते हैं। InstaGo का इन कुकीज़ पर कोई नियंत्रण नहीं है।</p>
<h2 id="ccpa">५. CCPA प्राइवेसी अधिकार (कैलिफ़ोर्निया)</h2>
<p>कैलिफ़ोर्निया उपभोक्ता गोपनीयता अधिनियम (CCPA) के तहत उपयोगकर्ताओं को अपने डेटा की सुरक्षा के अधिकार हैं। InstaGo किसी भी व्यक्तिगत डेटा को नहीं बेचता है।</p>
<h2 id="gdpr">६. GDPR डेटा सुरक्षा अधिकार (EU)</h2>
<p>सामान्य डेटा संरक्षण विनियमन (GDPR) के तहत प्रत्येक उपयोगकर्ता को अपने डेटा तक पहुँचने, सुधारने और हटाने का अधिकार है।</p>
<h2 id="children">७. बच्चों की जानकारी और COPPA</h2>
<p>इंटरनेट का उपयोग करते समय बच्चों की सुरक्षा हमारी प्राथमिकता है। InstaGo जानबूझकर १३ वर्ष से कम उम्र के बच्चों से कोई व्यक्तिगत जानकारी एकत्र नहीं करता है।</p>
<h2 id="consent">૮. सहमति और DPO संपर्क</h2>
<p>हमारी वेबसाइट का उपयोग करके, आप इस प्राइवेसी पॉलिसी और इसकी शर्तों से सहमत होते हैं। किसी भी प्रश्न के लिए हमारे संपर्क पेज से संपर्क करें।</p>`
            },
            terms: {
                breadHome: 'होम',
                breadCurrent: 'सेवा की शर्तें',
                badgeText: 'उपयोग दिशानिर्देश',
                titleText: 'सेवा की शर्तें और फेयर-यूज़',
                subtitleText: `प्रभावी तिथि: ${new Date().toLocaleDateString('hi-IN', {month: 'long', year: 'numeric'})} | कृपया हमारे प्लेटफ़ॉर्म का उपयोग करने से पहले इन शर्तों को ध्यान से पढ़ें।`,
                tocTitle: 'इस पेज पर',
                tocIntro: 'शर्तों की सहमति',
                tocFairUse: '१. फेयर-यूज़ और शैक्षिक उद्देश्य',
                tocIp: '२. बौद्धिक संपदा और नो-होस्टिंग',
                tocConduct: '३. उपयोगकर्ता की ज़िम्मेदारी',
                tocWarranties: '४. वारंटी का अस्वीकरण',
                tocLiability: '५. देयता की सीमा',
                tocModifications: '६. शर्तों में संशोधन',
                tocLaw: '७. शासी कानून',
                btnHomeText: 'डाउनलोडर पर वापस जाएं',
                reviewPrivacyText: 'हमारी <a href="privacy.php" style="color:var(--accent-blue);">प्राइवेसी पॉलिसी</a> देखें',
                articleBody: `<div id="intro">
    <p><strong>InstaGo</strong> में आपका स्वागत है। हमारी वेबसाइट का उपयोग करके आप इन सेवा शर्तों से बंधे होने के लिए सहमत होते हैं। यदि आप किसी भी शर्त से असहमत हैं, तो कृपया सेवा का उपयोग न करें।</p>
</div>
<h2 id="fair-use">१. फेयर-यूज़ और शैक्षिक उद्देश्य</h2>
<p>InstaGo एक शैक्षिक वेब टूल है जो उपयोगकर्ताओं को व्यक्तिगत और गैर-व्यावसायिक उपयोग के लिए सार्वजनिक इंस्टाग्राम सामग्री (रील्स, स्टोरीज़, हाइलाइट्स और प्रोफ़ाइल चित्र) डाउनलोड करने में मदद करता है।</p>
<div class="legal-box-highlight warning">
    <p><strong>सूचना:</strong> InstaGo पूरी तरह से स्वतंत्र है और इंस्टाग्राम या Meta Platforms Inc. से संबद्ध नहीं है।</p>
</div>
<h2 id="ip-disclaimer">२. बौद्धिक संपदा और नो-होस्टिंग अस्वीकरण</h2>
<p><strong>InstaGo अपने सर्वर पर किसी भी कॉपीराइट ऑडियो, वीडियो या छवि को होस्ट, स्टोर या आर्काइव नहीं करता है।</strong></p>
<p>सभी मीडिया आधिकारिक इंस्टाग्राम CDN से सीधे स्ट्रीम किए जाते हैं। सभी कॉपीराइट मूल क्रिएटर्स के पास रहते हैं।</p>
<h2 id="user-conduct">३. उपयोगकर्ता की ज़िम्मेदारी और प्रतिबंध</h2>
<p>कॉपीराइट सामग्री का अनधिकृत व्यावसायिक उपयोग करने या दूसरों के अधिकारों का उल्लंघन करने के लिए InstaGo का उपयोग करना सख्त वर्जित है।</p>
<h2 id="warranties">४. वारंटी का अस्वीकरण</h2>
<p>सेवा "जैसी है" और "जैसी उपलब्ध है" (AS IS) आधार पर बिना किसी वारंटी के प्रदान की जाती है।</p>
<h2 id="liability">५. देयता की सीमा</h2>
<p>कानून द्वारा अनुमत सीमा तक, InstaGo या इसके डेवलपर्स इस टूल के उपयोग से होने वाले किसी भी नुकसान के लिए उत्तरदायी नहीं होंगे।</p>
<h2 id="modifications">६. शर्तों में संशोधन</h2>
<p>हम किसी भी समय इन नियमों और शर्तों को संशोधित करने का अधिकार सुरक्षित रखते हैं।</p>
<h2 id="governing-law">७. शासी कानून</h2>
<p>ये शर्तें अंतर्राष्ट्रीय डिजिटल कॉपीराइट नियमों और सेफ हार्बर सिद्धांतों द्वारा शासित होती हैं।</p>`
            }
        }
    };

    function applySubpageTranslations(lang) {
        const s = (subpagesI18n && subpagesI18n[lang]) ? subpagesI18n[lang] : subpagesI18n['en'];
        if (!s) return;

        const el = (id, val, isHtml) => {
            const dom = document.getElementById(id);
            if (dom && val !== undefined) {
                if (isHtml) dom.innerHTML = val;
                else dom.textContent = val;
            }
        };

        // 1. Contact Page
        if (s.contact && document.getElementById('contactBadgeText')) {
            el('contactBreadHome', s.contact.breadHome);
            el('contactBreadCurrent', s.contact.breadCurrent);
            el('contactBadgeText', s.contact.badgeText);
            el('contactTitleText', s.contact.titleText);
            el('contactSubtitleText', s.contact.subtitleText);
            el('ch1TitleText', s.contact.ch1TitleText);
            el('ch1BadgeText', s.contact.ch1BadgeText);
            el('ch1DescText', s.contact.ch1DescText);
            el('ch2TitleText', s.contact.ch2TitleText);
            el('ch2BadgeText', s.contact.ch2BadgeText);
            el('ch2DescText', s.contact.ch2DescText);
            el('ch3TitleText', s.contact.ch3TitleText);
            el('ch3BadgeText', s.contact.ch3BadgeText);
            el('ch3DescText', s.contact.ch3DescText);
            el('dmcaReqText', s.contact.dmcaReqText, true);
            el('contactFormTitleText', s.contact.formTitleText);
            el('contactFormDescText', s.contact.formDescText);
            el('labelNameText', s.contact.labelNameText);
            el('labelEmailText', s.contact.labelEmailText);
            el('labelCategoryText', s.contact.labelCategoryText);
            el('optGeneralText', s.contact.optGeneralText);
            el('optDmcaText', s.contact.optDmcaText);
            el('optBugText', s.contact.optBugText);
            el('optPartnerText', s.contact.optPartnerText);
            el('labelSubjectText', s.contact.labelSubjectText);
            el('labelMessageText', s.contact.labelMessageText);
            el('btnSendMsgText', s.contact.btnSendMsgText);
            el('contactTrustLine', s.contact.trustLine, true);
            el('contactRespTime', s.contact.respTime, true);
            el('contactBtnHomeText', s.contact.btnHomeText);
            el('contactFooterTermsLine', s.contact.footerTermsLine, true);
        }

        // 2. About Page
        if (s.about && document.getElementById('aboutBadgeText')) {
            el('aboutBreadHome', s.about.breadHome);
            el('aboutBreadCurrent', s.about.breadCurrent);
            el('aboutBadgeText', s.about.badgeText);
            el('aboutTitleText', s.about.titleText);
            el('aboutSubtitleText', s.about.subtitleText);
            el('aboutArticleBody', s.about.articleBody, true);
            el('aboutBtnHomeText', s.about.btnHomeText);
            el('aboutAssistText', s.about.assistText, true);
        }

        // 3. Privacy Policy Page
        if (s.privacy && document.getElementById('privBadgeText')) {
            el('privBreadHome', s.privacy.breadHome);
            el('privBreadCurrent', s.privacy.breadCurrent);
            el('privBadgeText', s.privacy.badgeText);
            el('privTitleText', s.privacy.titleText);
            el('privSubtitleText', s.privacy.subtitleText);
            el('privTocTitle', s.privacy.tocTitle);
            el('privTocOverview', s.privacy.tocOverview);
            el('privTocAdsense', s.privacy.tocAdsense);
            el('privTocZeroLog', s.privacy.tocZeroLog);
            el('privTocLogFiles', s.privacy.tocLogFiles);
            el('privTocThirdParty', s.privacy.tocThirdParty);
            el('privTocCcpa', s.privacy.tocCcpa);
            el('privTocGdpr', s.privacy.tocGdpr);
            el('privTocChildren', s.privacy.tocChildren);
            el('privTocConsent', s.privacy.tocConsent);
            el('privArticleBody', s.privacy.articleBody, true);
            el('privBtnHomeText', s.privacy.btnHomeText);
            el('privContactSupportText', s.privacy.contactSupportText, true);
        }

        // 4. Terms of Service Page
        if (s.terms && document.getElementById('termsBadgeText')) {
            el('termsBreadHome', s.terms.breadHome);
            el('termsBreadCurrent', s.terms.breadCurrent);
            el('termsBadgeText', s.terms.badgeText);
            el('termsTitleText', s.terms.titleText);
            el('termsSubtitleText', s.terms.subtitleText);
            el('termsTocTitle', s.terms.tocTitle);
            el('termsTocIntro', s.terms.tocIntro);
            el('termsTocFairUse', s.terms.tocFairUse);
            el('termsTocIp', s.terms.tocIp);
            el('termsTocConduct', s.terms.tocConduct);
            el('termsTocWarranties', s.terms.tocWarranties);
            el('termsTocLiability', s.terms.tocLiability);
            el('termsTocModifications', s.terms.tocModifications);
            el('termsTocLaw', s.terms.tocLaw);
            el('termsArticleBody', s.terms.articleBody, true);
            el('termsBtnHomeText', s.terms.btnHomeText);
            el('termsReviewPrivacyText', s.terms.reviewPrivacyText, true);
        }
    }

    function applyLanguage(lang) {
        if (!i18n[lang]) lang = 'en';
        currentAppLang = lang;
        const t = i18n[lang];

        // 1. Update Header dropdown
        if (currentLangLabel) currentLangLabel.textContent = t.langCode;
        if (headerLangOptions) {
            headerLangOptions.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
            });
        }

        // 2. Header Status Badge
        const statusBadge = document.getElementById('statusBadgeText');
        if (statusBadge) statusBadge.textContent = t.statusBadge;

        // 3. Hero Section
        const heroBadge = document.getElementById('heroBadgeText');
        const heroTitle = document.getElementById('heroTitle');
        const heroDesc = document.getElementById('heroDesc');
        if (heroBadge) heroBadge.textContent = t.heroBadge;
        if (heroTitle) heroTitle.innerHTML = t.heroTitle;
        if (heroDesc) heroDesc.textContent = t.heroDesc;

        // 4. Tabs & Mobile Dock
        const tabAll = document.getElementById('tabLabelAll');
        const tabReels = document.getElementById('tabLabelReels');
        const tabDP = document.getElementById('tabLabelDP');
        const tabStories = document.getElementById('tabLabelStories');
        const tabHighlights = document.getElementById('tabLabelHighlights');
        const tabPhotos = document.getElementById('tabLabelPhotos');
        if (tabAll) tabAll.textContent = t.tabs.all;
        if (tabReels) tabReels.textContent = t.tabs.reels;
        if (tabDP) tabDP.textContent = t.tabs.dp;
        if (tabStories) tabStories.textContent = t.tabs.stories;
        if (tabHighlights && t.tabs.highlights) tabHighlights.textContent = t.tabs.highlights;
        if (tabPhotos) tabPhotos.textContent = t.tabs.photos;

        const dockAll = document.getElementById('dockLabelAll');
        const dockReels = document.getElementById('dockLabelReels');
        const dockDP = document.getElementById('dockLabelDP');
        const dockStories = document.getElementById('dockLabelStories');
        const dockHighlights = document.getElementById('dockLabelHighlights');
        const dockPhotos = document.getElementById('dockLabelPhotos');
        if (dockAll) dockAll.textContent = t.dock.all;
        if (dockReels) dockReels.textContent = t.dock.reels;
        if (dockDP) dockDP.textContent = t.dock.dp;
        if (dockStories) dockStories.textContent = t.dock.stories;
        if (dockHighlights && t.dock.highlights) dockHighlights.textContent = t.dock.highlights;
        if (dockPhotos) dockPhotos.textContent = t.dock.photos;

        // 5. Input Actions & Placeholder
        const pasteText = document.getElementById('pasteBtnText');
        if (pasteText) pasteText.textContent = t.pasteBtn;
        if (submitBtnText && (!submitBtn || !submitBtn.disabled)) submitBtnText.textContent = t.searchBtn;
        
        // Update placeholder for active tab
        const activeTabBtn = document.querySelector('.tab-btn.active');
        const activeType = activeTabBtn ? activeTabBtn.getAttribute('data-type') : 'all';
        if (urlInput) {
            updatePlaceholder(activeType);
        }

        // 6. Micro Perks
        const p1 = document.getElementById('perk1');
        const p2 = document.getElementById('perk2');
        const p3 = document.getElementById('perk3');
        const p4 = document.getElementById('perk4');
        if (p1) p1.textContent = t.perks.p1;
        if (p2) p2.textContent = t.perks.p2;
        if (p3) p3.textContent = t.perks.p3;
        if (p4) p4.textContent = t.perks.p4;

        // 7. Loader Card
        const lTitle = document.getElementById('loaderTitle');
        if (lTitle) lTitle.textContent = t.loader.title;

        // 8. Guide Section
        if (guideHeading) guideHeading.innerHTML = t.guide.heading;
        if (guideSubheading) guideSubheading.textContent = t.guide.subheading;
        if (stepTitle1) stepTitle1.textContent = t.guide.step1Title;
        if (stepDesc1) stepDesc1.innerHTML = t.guide.step1Desc;
        if (stepTitle2) stepTitle2.textContent = t.guide.step2Title;
        if (stepDesc2) stepDesc2.innerHTML = t.guide.step2Desc;
        if (stepTitle3) stepTitle3.textContent = t.guide.step3Title;
        if (stepDesc3) stepDesc3.innerHTML = t.guide.step3Desc;
        if (tipTitleReels) tipTitleReels.textContent = t.guide.tipReelsTitle;
        if (tipDescReels) tipDescReels.textContent = t.guide.tipReelsDesc;
        if (tipTitleDP) tipTitleDP.textContent = t.guide.tipDpTitle;
        if (tipDescDP) tipDescDP.textContent = t.guide.tipDpDesc;
        if (tipTitleStories) tipTitleStories.textContent = t.guide.tipStoriesTitle;
        if (tipDescStories) tipDescStories.textContent = t.guide.tipStoriesDesc;

        // 8b. Device Guide Section (Ways to Download)
        const waysH = document.getElementById('waysHeading');
        const waysSub = document.getElementById('waysSubheading');
        const tIos = document.getElementById('tabIos');
        const tAnd = document.getElementById('tabAndroid');
        const tPC = document.getElementById('tabPC');
        if (waysH && t.ways) waysH.innerHTML = t.ways.heading;
        if (waysSub && t.ways) waysSub.textContent = t.ways.subheading;
        if (tIos && t.ways) tIos.textContent = t.ways.tabIos;
        if (tAnd && t.ways) tAnd.textContent = t.ways.tabAndroid;
        if (tPC && t.ways) tPC.textContent = t.ways.tabPC;

        const iosB = document.getElementById('iosBadge');
        const iosT = document.getElementById('iosGuideTitle');
        const iosS1 = document.getElementById('iosStep1');
        const iosS2 = document.getElementById('iosStep2');
        const iosS3 = document.getElementById('iosStep3');
        const iosS4 = document.getElementById('iosStep4');
        const iosS5 = document.getElementById('iosStep5');
        if (iosB && t.ways) iosB.textContent = t.ways.iosBadge;
        if (iosT && t.ways) iosT.textContent = t.ways.iosTitle;
        if (iosS1 && t.ways) iosS1.innerHTML = t.ways.iosStep1;
        if (iosS2 && t.ways) iosS2.innerHTML = t.ways.iosStep2;
        if (iosS3 && t.ways) iosS3.innerHTML = t.ways.iosStep3;
        if (iosS4 && t.ways) iosS4.innerHTML = t.ways.iosStep4;
        if (iosS5 && t.ways) iosS5.innerHTML = t.ways.iosStep5;

        const andB = document.getElementById('androidBadge');
        const andT = document.getElementById('androidGuideTitle');
        const andS1 = document.getElementById('androidStep1');
        const andS2 = document.getElementById('androidStep2');
        const andS3 = document.getElementById('androidStep3');
        const andS4 = document.getElementById('androidStep4');
        const andS5 = document.getElementById('androidStep5');
        if (andB && t.ways) andB.textContent = t.ways.androidBadge;
        if (andT && t.ways) andT.textContent = t.ways.androidTitle;
        if (andS1 && t.ways) andS1.innerHTML = t.ways.androidStep1;
        if (andS2 && t.ways) andS2.innerHTML = t.ways.androidStep2;
        if (andS3 && t.ways) andS3.innerHTML = t.ways.androidStep3;
        if (andS4 && t.ways) andS4.innerHTML = t.ways.androidStep4;
        if (andS5 && t.ways) andS5.innerHTML = t.ways.androidStep5;

        const pcB = document.getElementById('pcBadge');
        const pcT = document.getElementById('pcGuideTitle');
        const pcS1 = document.getElementById('pcStep1');
        const pcS2 = document.getElementById('pcStep2');
        const pcS3 = document.getElementById('pcStep3');
        const pcS4 = document.getElementById('pcStep4');
        if (pcB && t.ways) pcB.textContent = t.ways.pcBadge;
        if (pcT && t.ways) pcT.textContent = t.ways.pcTitle;
        if (pcS1 && t.ways) pcS1.innerHTML = t.ways.pcStep1;
        if (pcS2 && t.ways) pcS2.innerHTML = t.ways.pcStep2;
        if (pcS3 && t.ways) pcS3.innerHTML = t.ways.pcStep3;
        if (pcS4 && t.ways) pcS4.innerHTML = t.ways.pcStep4;

        // 8c. SEO Articles Section
        const a1T = document.getElementById('art1Title');
        const a1P1 = document.getElementById('art1P1');
        const a1P2 = document.getElementById('art1P2');
        const a2T = document.getElementById('art2Title');
        const a2P1 = document.getElementById('art2P1');
        const a2P2 = document.getElementById('art2P2');
        if (a1T && t.articles) a1T.textContent = t.articles.art1Title;
        if (a1P1 && t.articles) a1P1.innerHTML = t.articles.art1P1;
        if (a1P2 && t.articles) a1P2.innerHTML = t.articles.art1P2;
        if (a2T && t.articles) a2T.textContent = t.articles.art2Title;
        if (a2P1 && t.articles) a2P1.innerHTML = t.articles.art2P1;
        if (a2P2 && t.articles) a2P2.innerHTML = t.articles.art2P2;

        // 9. Features Section
        const fHeading = document.getElementById('featHeading');
        const fSubheading = document.getElementById('featSubheading');
        const f1T = document.getElementById('feat1Title');
        const f1D = document.getElementById('feat1Desc');
        const f2T = document.getElementById('feat2Title');
        const f2D = document.getElementById('feat2Desc');
        const f3T = document.getElementById('feat3Title');
        const f3D = document.getElementById('feat3Desc');
        const f4T = document.getElementById('feat4Title');
        const f4D = document.getElementById('feat4Desc');
        const f5T = document.getElementById('feat5Title');
        const f5D = document.getElementById('feat5Desc');
        const f6T = document.getElementById('feat6Title');
        const f6D = document.getElementById('feat6Desc');
        const dChip1 = document.getElementById('devChip1');
        const dChip2 = document.getElementById('devChip2');
        const dChip3 = document.getElementById('devChip3');
        if (fHeading) fHeading.innerHTML = t.features.heading;
        if (fSubheading) fSubheading.textContent = t.features.subheading;
        if (f1T) f1T.textContent = t.features.f1Title;
        if (f1D) f1D.textContent = t.features.f1Desc;
        if (f2T) f2T.textContent = t.features.f2Title;
        if (f2D) f2D.textContent = t.features.f2Desc;
        if (f3T) f3T.textContent = t.features.f3Title;
        if (f3D) f3D.textContent = t.features.f3Desc;
        if (f4T) f4T.textContent = t.features.f4Title;
        if (f4D) f4D.textContent = t.features.f4Desc;
        if (f5T && t.features.f5Title) f5T.textContent = t.features.f5Title;
        if (f5D && t.features.f5Desc) f5D.textContent = t.features.f5Desc;
        if (f6T && t.features.f6Title) f6T.textContent = t.features.f6Title;
        if (f6D && t.features.f6Desc) f6D.textContent = t.features.f6Desc;
        if (dChip1) dChip1.textContent = t.features.dev1;
        if (dChip2) dChip2.textContent = t.features.dev2;
        if (dChip3) dChip3.textContent = t.features.dev3;

        // 10. FAQ Section
        const faqH = document.getElementById('faqHeading');
        const faqSub = document.getElementById('faqSubheading');
        if (faqH) faqH.innerHTML = t.faq.heading;
        if (faqSub) faqSub.textContent = t.faq.subheading;
        const faqQ1 = document.getElementById('faqQ1');
        const faqA1 = document.getElementById('faqA1');
        const faqQ2 = document.getElementById('faqQ2');
        const faqA2 = document.getElementById('faqA2');
        const faqQ3 = document.getElementById('faqQ3');
        const faqA3 = document.getElementById('faqA3');
        const faqQ4 = document.getElementById('faqQ4');
        const faqA4 = document.getElementById('faqA4');
        const faqQ5 = document.getElementById('faqQ5');
        const faqA5 = document.getElementById('faqA5');
        const faqQ6 = document.getElementById('faqQ6');
        const faqA6 = document.getElementById('faqA6');
        const faqQ7 = document.getElementById('faqQ7');
        const faqA7 = document.getElementById('faqA7');
        const faqQ8 = document.getElementById('faqQ8');
        const faqA8 = document.getElementById('faqA8');
        if (faqQ1) faqQ1.textContent = t.faq.q1;
        if (faqA1) faqA1.innerHTML = t.faq.a1;
        if (faqQ2) faqQ2.textContent = t.faq.q2;
        if (faqA2) faqA2.innerHTML = t.faq.a2;
        if (faqQ3) faqQ3.textContent = t.faq.q3;
        if (faqA3) faqA3.innerHTML = t.faq.a3;
        if (faqQ4) faqQ4.textContent = t.faq.q4;
        if (faqA4) faqA4.innerHTML = t.faq.a4;
        if (faqQ5) faqQ5.textContent = t.faq.q5;
        if (faqA5) faqA5.innerHTML = t.faq.a5;
        if (faqQ6 && t.faq.q6) faqQ6.textContent = t.faq.q6;
        if (faqA6 && t.faq.a6) faqA6.innerHTML = t.faq.a6;
        if (faqQ7 && t.faq.q7) faqQ7.textContent = t.faq.q7;
        if (faqA7 && t.faq.a7) faqA7.innerHTML = t.faq.a7;
        if (faqQ8 && t.faq.q8) faqQ8.textContent = t.faq.q8;
        if (faqA8 && t.faq.a8) faqA8.innerHTML = t.faq.a8;

        // 11. Footer Section
        const fTagline = document.getElementById('footerTagline');
        const fDisc = document.getElementById('footerDisclaimer');
        const fChip1 = document.getElementById('trustChip1');
        const fChip2 = document.getElementById('trustChip2');
        const fChip3 = document.getElementById('trustChip3');
        const fColTools = document.getElementById('footColTools');
        const fReels = document.getElementById('footLinkReels');
        const fDP = document.getElementById('footLinkDP');
        const fStories = document.getElementById('footLinkStories');
        const fHighlights = document.getElementById('footLinkHighlights');
        const fPhotos = document.getElementById('footLinkPhotos');
        const fColGuides = document.getElementById('footColGuides');
        const fDevIos = document.getElementById('footDevIos');
        const fDevAnd = document.getElementById('footDevAndroid');
        const fDevPC = document.getElementById('footDevPC');
        const fDevMac = document.getElementById('footDevMac');
        const fColLegal = document.getElementById('footColLegal');
        const fLegPriv = document.getElementById('footLegPrivacy');
        const fLegTerms = document.getElementById('footLegTerms');
        const fLegAbout = document.getElementById('footLegAbout');
        const fLegCont = document.getElementById('footLegContact');
        const fShareLbl = document.getElementById('shareBarLabel');
        const fCopy = document.getElementById('footerCopyright');

        if (fTagline && t.footer.tagline) fTagline.textContent = t.footer.tagline;
        if (fDisc) fDisc.innerHTML = t.footer.disclaimer;
        if (fChip1 && t.footer.chip1) fChip1.textContent = t.footer.chip1;
        if (fChip2 && t.footer.chip2) fChip2.textContent = t.footer.chip2;
        if (fChip3 && t.footer.chip3) fChip3.textContent = t.footer.chip3;
        if (fColTools && t.footer.colTools) fColTools.textContent = t.footer.colTools;
        if (fReels) fReels.textContent = t.footer.linkReels;
        if (fDP) fDP.textContent = t.footer.linkDP;
        if (fStories) fStories.textContent = t.footer.linkStories;
        if (fHighlights && t.footer.linkHighlights) fHighlights.textContent = t.footer.linkHighlights;
        if (fPhotos && t.footer.linkPhotos) fPhotos.textContent = t.footer.linkPhotos;
        if (fColGuides && t.footer.colGuides) fColGuides.textContent = t.footer.colGuides;
        if (fDevIos && t.footer.devIos) fDevIos.textContent = t.footer.devIos;
        if (fDevAnd && t.footer.devAndroid) fDevAnd.textContent = t.footer.devAndroid;
        if (fDevPC && t.footer.devPC) fDevPC.textContent = t.footer.devPC;
        if (fDevMac && t.footer.devMac) fDevMac.textContent = t.footer.devMac;
        if (fColLegal && t.footer.colLegal) fColLegal.textContent = t.footer.colLegal;
        if (fLegPriv && t.footer.legPrivacy) fLegPriv.textContent = t.footer.legPrivacy;
        if (fLegTerms && t.footer.legTerms) fLegTerms.textContent = t.footer.legTerms;
        if (fLegAbout && t.footer.legAbout) fLegAbout.textContent = t.footer.legAbout;
        if (fLegCont && t.footer.legContact) fLegCont.textContent = t.footer.legContact;
        if (fShareLbl && t.footer.shareLabel) fShareLbl.textContent = t.footer.shareLabel;
        if (fCopy) fCopy.innerHTML = t.footer.copyright;

        // 11b. Legal Policy Modal Dialog
        const mTabPriv = document.getElementById('modalTabPrivacy');
        const mTabTerms = document.getElementById('modalTabTerms');
        const mTabAbout = document.getElementById('modalTabAbout');
        const mTabContact = document.getElementById('modalTabContact');
        const panePriv = document.getElementById('pane-privacy');
        const paneTerms = document.getElementById('pane-terms');
        const paneAbout = document.getElementById('pane-about');
        const paneContact = document.getElementById('pane-contact');

        if (mTabPriv && t.legal) mTabPriv.textContent = t.legal.tabPrivacy;
        if (mTabTerms && t.legal) mTabTerms.textContent = t.legal.tabTerms;
        if (mTabAbout && t.legal) mTabAbout.textContent = t.legal.tabAbout;
        if (mTabContact && t.legal) mTabContact.textContent = t.legal.tabContact;

        if (panePriv && t.legal) panePriv.innerHTML = t.legal.panePrivacy;
        if (paneTerms && t.legal) paneTerms.innerHTML = t.legal.paneTerms;
        if (paneAbout && t.legal) paneAbout.innerHTML = t.legal.paneAbout;
        if (paneContact && t.legal) paneContact.innerHTML = t.legal.paneContact;

        // 11c. Standalone Subpages Translation (contact.php, about.php, privacy.php, terms.php)
        if (typeof applySubpageTranslations === 'function') {
            applySubpageTranslations(lang);
        }

        // 12. Persist in storage and set html lang
        document.documentElement.setAttribute('lang', lang);
        try {
            localStorage.setItem('instago_app_lang', lang);
            localStorage.setItem('instago_guide_lang', lang);
        } catch(e) {}

        if (typeof window.refreshMobileAutoTickers === 'function') {
            window.refreshMobileAutoTickers();
        }
    }

    // Header Dropdown Event Listeners
    if (headerLangToggle && headerLangDropdown) {
        headerLangToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = headerLangDropdown.classList.toggle('open');
            headerLangToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (e) => {
            if (!headerLangDropdown.contains(e.target)) {
                headerLangDropdown.classList.remove('open');
                headerLangToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                headerLangDropdown.classList.remove('open');
                headerLangToggle.setAttribute('aria-expanded', 'false');
            }
        });

        if (headerLangOptions) {
            headerLangOptions.forEach(btn => {
                btn.addEventListener('click', () => {
                    const lang = btn.getAttribute('data-lang');
                    applyLanguage(lang);
                    headerLangDropdown.classList.remove('open');
                    headerLangToggle.setAttribute('aria-expanded', 'false');
                });
            });
        }
    }

    // Device OS Guide Tabs Switcher (iOS / Android / PC)
    const osTabBtns = document.querySelectorAll('.os-tab-btn');
    const devicePanels = document.querySelectorAll('.device-panel');

    if (osTabBtns.length > 0 && devicePanels.length > 0) {
        osTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetOS = btn.getAttribute('data-os') || btn.getAttribute('data-target');
                osTabBtns.forEach(b => b.classList.toggle('active', b === btn));
                devicePanels.forEach(p => {
                    p.classList.toggle('active', p.id === `panel-${targetOS}` || p.id === `pane-dev-${targetOS}`);
                });
            });
        });
    }

    // Google AdSense Legal Policy Modal (Privacy, Terms, About, Contact/DMCA)
    const legalModal = document.getElementById('legalModal');
    const legalCloseBtn = document.getElementById('legalModalClose');
    const legalOpenBtns = document.querySelectorAll('.legal-open-btn');
    const modalTabBtns = document.querySelectorAll('.modal-tab-btn');
    const modalPanes = document.querySelectorAll('.modal-tab-pane');

    function openLegalModal(policy) {
        if (!legalModal) return;
        legalModal.classList.add('active');
        legalModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (policy) {
            modalTabBtns.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-tab') === policy);
            });
            modalPanes.forEach(pane => {
                pane.classList.toggle('active', pane.id === `pane-${policy}`);
            });
        }
    }

    function closeLegalModal() {
        if (!legalModal) return;
        legalModal.classList.remove('active');
        legalModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (legalOpenBtns) {
        legalOpenBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const policy = btn.getAttribute('data-policy') || 'privacy';
                openLegalModal(policy);
            });
        });
    }

    if (legalCloseBtn) {
        legalCloseBtn.addEventListener('click', closeLegalModal);
    }

    if (legalModal) {
        legalModal.addEventListener('click', (e) => {
            if (e.target === legalModal) {
                closeLegalModal();
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && legalModal && legalModal.classList.contains('active')) {
            closeLegalModal();
        }
    });

    if (modalTabBtns) {
        modalTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                modalTabBtns.forEach(b => b.classList.toggle('active', b === btn));
                modalPanes.forEach(pane => {
                    pane.classList.toggle('active', pane.id === `pane-${tab}`);
                });
            });
        });
    }

    // Lightbox for Guide Step Screenshots
    const guideLightbox = document.getElementById('guideLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxCloseBtn = document.getElementById('lightboxCloseBtn');
    const stepImgFrames = document.querySelectorAll('.step-img-frame');

    if (stepImgFrames.length > 0 && guideLightbox && lightboxImg) {
        stepImgFrames.forEach(frame => {
            frame.addEventListener('click', () => {
                const src = frame.getAttribute('data-step-img');
                const title = frame.getAttribute('data-step-title') || 'Step Screenshot';
                if (src) {
                    lightboxImg.src = src;
                    if (lightboxTitle) lightboxTitle.textContent = title;
                    guideLightbox.classList.add('active');
                    guideLightbox.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        function closeGuideLightbox() {
            guideLightbox.classList.remove('active');
            guideLightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (lightboxCloseBtn) {
            lightboxCloseBtn.addEventListener('click', closeGuideLightbox);
        }

        guideLightbox.addEventListener('click', (e) => {
            if (e.target === guideLightbox) {
                closeGuideLightbox();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && guideLightbox.classList.contains('active')) {
                closeGuideLightbox();
            }
        });
    }

    // Smooth Scroll Reveal Animation for all sections
    const revealElements = document.querySelectorAll('.scroll-reveal');
    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: 0.08
        });

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        // Fallback for legacy browsers without IntersectionObserver
        revealElements.forEach(el => el.classList.add('revealed'));
    }

    // Footer Tool Links Quick Tab Switch
    document.querySelectorAll('.foot-tool-link').forEach(link => {
        link.addEventListener('click', (e) => {
            const type = link.getAttribute('data-type');
            if (type) {
                switchActiveTab(type, true);
            }
        });
    });

    // Apply saved or default language on startup
    applyLanguage(currentAppLang);

    // Initial Active Tab synchronization across mobile/tablet and desktop
    const initialActive = document.querySelector('.tab-btn.active') || document.querySelector('.dock-btn.active');
    const initialType = initialActive ? initialActive.getAttribute('data-type') : 'all';
    switchActiveTab(initialType, false);

    // ==========================================================================
    // News Headline Auto-Scroller for Mobile Devices (<= 699px only)
    // Applied to: 1. #reelQuickSpecs, 2. .footer-trust-chips
    // ==========================================================================
    function initMobileAutoScrollers() {
        const isMobile = () => window.innerWidth <= 699;

        function setupTicker(container) {
            if (!container) return null;

            let loopDistance = 0;
            let isInteracting = false;
            let resumeTimer = null;

            function rebuildClones() {
                container.querySelectorAll('.auto-ticker-clone').forEach(el => el.remove());

                if (!isMobile()) {
                    container.scrollLeft = 0;
                    loopDistance = 0;
                    return;
                }

                const originals = Array.from(container.children).filter(el => !el.classList.contains('auto-ticker-clone'));
                if (originals.length === 0) return;

                // Add 3 full clone sets for an infinite smooth continuous ticker runway
                for (let set = 0; set < 3; set++) {
                    originals.forEach(item => {
                        const clone = item.cloneNode(true);
                        clone.classList.add('auto-ticker-clone');
                        clone.removeAttribute('id');
                        clone.querySelectorAll('[id]').forEach(sub => sub.removeAttribute('id'));
                        clone.setAttribute('aria-hidden', 'true');
                        container.appendChild(clone);
                    });
                }

                const firstOrig = originals[0];
                const firstClone = container.querySelector('.auto-ticker-clone');
                if (firstOrig && firstClone) {
                    loopDistance = firstClone.offsetLeft - firstOrig.offsetLeft;
                }
            }

            rebuildClones();

            const speed = 0.65; // News headline ticker smooth glide speed (pixels per frame)

            function step() {
                if (isMobile()) {
                    if (container.offsetParent !== null && container.clientWidth > 0) {
                        if (loopDistance <= 0 || !container.querySelector('.auto-ticker-clone')) {
                            rebuildClones();
                        }

                        if (loopDistance > 0) {
                            if (container.scrollLeft >= loopDistance) {
                                container.scrollLeft -= loopDistance;
                            } else if (container.scrollLeft < 0) {
                                container.scrollLeft += loopDistance;
                            }

                            if (!isInteracting) {
                                container.scrollLeft += speed;
                            }
                        }
                    }
                }
                requestAnimationFrame(step);
            }

            const onPause = () => {
                isInteracting = true;
                if (resumeTimer) clearTimeout(resumeTimer);
            };

            const onResume = () => {
                if (resumeTimer) clearTimeout(resumeTimer);
                resumeTimer = setTimeout(() => {
                    isInteracting = false;
                }, 1600);
            };

            container.addEventListener('touchstart', onPause, { passive: true });
            container.addEventListener('touchend', onResume, { passive: true });
            container.addEventListener('touchcancel', onResume, { passive: true });
            container.addEventListener('mousedown', onPause);
            container.addEventListener('mouseup', onResume);
            container.addEventListener('mouseleave', onResume);

            requestAnimationFrame(step);

            return rebuildClones;
        }

        const refreshSpecs = setupTicker(document.getElementById('reelQuickSpecs'));
        const refreshFooter = setupTicker(document.querySelector('.footer-trust-chips'));

        window.refreshMobileAutoTickers = function() {
            if (refreshSpecs) refreshSpecs();
            if (refreshFooter) refreshFooter();
        };

        window.addEventListener('resize', () => {
            if (window.refreshMobileAutoTickers) {
                window.refreshMobileAutoTickers();
            }
        });
    }

    initMobileAutoScrollers();

    // Auto-hide bottom mobile dock when virtual keyboard opens or input is focused
    function initMobileKeyboardDockHandler() {
        const isMobileOrTablet = () => window.innerWidth <= 1024 || ('ontouchstart' in window);

        const onFocusIn = (e) => {
            if (!isMobileOrTablet()) return;
            const target = e.target;
            if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA')) {
                document.body.classList.add('keyboard-open');
            }
        };

        const onFocusOut = () => {
            setTimeout(() => {
                const active = document.activeElement;
                if (!active || (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA')) {
                    document.body.classList.remove('keyboard-open');
                }
            }, 100);
        };

        document.addEventListener('focusin', onFocusIn);
        document.addEventListener('focusout', onFocusOut);

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', () => {
                if (!isMobileOrTablet()) return;
                const currentHeight = window.visualViewport.height;
                if (window.innerHeight - currentHeight > 120) {
                    document.body.classList.add('keyboard-open');
                } else {
                    const active = document.activeElement;
                    if (!active || (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA')) {
                        document.body.classList.remove('keyboard-open');
                    }
                }
            });
        }
    }

    // Auto-clean searchbar when returning to homepage
    if (window.location.search.includes('reset=1') || sessionStorage.getItem('instago_reset_search') === '1') {
        sessionStorage.removeItem('instago_reset_search');
        if (typeof clearResultsAndErrors === 'function') {
            clearResultsAndErrors();
        }
        if (urlInput) {
            urlInput.value = '';
        }
        if (clearBtn) {
            clearBtn.style.display = 'none';
        }
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    // Smooth scroll for Legal Pages Table of Contents
    const legalTocLinks = document.querySelectorAll('.legal-toc-link[href^="#"]');
    if (legalTocLinks.length > 0) {
        legalTocLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const targetId = link.getAttribute('href');
                if (targetId && targetId.length > 1) {
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        const headerOffset = 85;
                        const elementPosition = targetEl.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                        legalTocLinks.forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                    }
                }
            });
        });
    }

    initMobileKeyboardDockHandler();
});
