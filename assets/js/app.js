/**
 * InstaGo - Frontend Application Script
 * Smooth AJAX, Clipboard Integration, Auto-Detection & Dynamic Media Preview
 */

document.addEventListener('DOMContentLoaded', () => {
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

    const tabBtns = document.querySelectorAll('.tab-btn');
    const faqItems = document.querySelectorAll('.faq-item');

    // 1. Theme Toggle Logic
    const savedTheme = localStorage.getItem('instago_theme') || 
        (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
    setTheme(savedTheme);

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    });

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('instago_theme', theme);
        if (theme === 'light') {
            themeIconSun.style.display = 'none';
            themeIconMoon.style.display = 'block';
        } else {
            themeIconSun.style.display = 'block';
            themeIconMoon.style.display = 'none';
        }
    }

    // 2. Tab Navigation
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const type = btn.getAttribute('data-type');
            updatePlaceholder(type);
        });
    });

    function updatePlaceholder(type) {
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
            case 'photos':
                urlInput.placeholder = 'Paste Instagram Post / Carousel photo link...';
                break;
            default:
                urlInput.placeholder = 'Paste Instagram Reel, DP, Story, or Post URL...';
        }
    }

    // 3. Input Auto-Detection & Clear Button
    urlInput.addEventListener('input', () => {
        const val = urlInput.value.trim();
        clearBtn.style.display = val ? 'flex' : 'none';
        detectUrlType(val);
    });

    clearBtn.addEventListener('click', () => {
        urlInput.value = '';
        clearBtn.style.display = 'none';
        detectionIndicator.classList.remove('active');
        urlInput.focus();
    });

    function detectUrlType(val) {
        if (!val) {
            detectionIndicator.classList.remove('active');
            return;
        }

        let detected = '';
        let tabKey = 'all';

        if (/instagram\.com\/(?:reel|reels)\/([A-Za-z0-9_-]+)/i.test(val)) {
            detected = 'Instagram Reel Detected';
            tabKey = 'reels';
        } else if (/instagram\.com\/stories\/([A-Za-z0-9._-]+)/i.test(val)) {
            detected = 'Instagram Story Detected';
            tabKey = 'stories';
        } else if (/instagram\.com\/(?:p|tv)\/([A-Za-z0-9_-]+)/i.test(val)) {
            detected = 'Instagram Post / Photo Detected';
            tabKey = 'photos';
        } else if (/^@?[A-Za-z0-9._]{2,30}$/.test(val) || (/instagram\.com\/([A-Za-z0-9._]{2,30})\/?$/i.test(val) && !val.includes('/p/') && !val.includes('/reel/'))) {
            detected = 'Instagram DP / Profile Detected';
            tabKey = 'dp';
        }

        if (detected) {
            detectedTypeSpan.textContent = detected;
            detectionIndicator.classList.add('active');
            // Sync active tab
            tabBtns.forEach(btn => {
                if (btn.getAttribute('data-type') === tabKey) {
                    tabBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
            });
        } else {
            detectionIndicator.classList.remove('active');
        }
    }

    // 4. Clipboard Paste Button
    pasteBtn.addEventListener('click', async () => {
        try {
            if (navigator.clipboard && navigator.clipboard.readText) {
                const text = await navigator.clipboard.readText();
                if (text) {
                    urlInput.value = text.trim();
                    clearBtn.style.display = 'flex';
                    detectUrlType(text.trim());
                    // Auto-trigger search for fast UX
                    searchForm.dispatchEvent(new Event('submit'));
                }
            } else {
                urlInput.focus();
                document.execCommand('paste');
            }
        } catch (err) {
            urlInput.focus();
            showToast('Press Ctrl+V to paste the link', 'info');
        }
    });

    // 5. Form Submission & AJAX
    searchForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = urlInput.value.trim();

        if (!url) {
            showError('કૃપા કરીને માન્ય Instagram લિંક પેસ્ટ કરો (Please paste an Instagram link).');
            urlInput.focus();
            return;
        }

        // Reset UI States
        hideError();
        resultCard.style.display = 'none';
        loaderCard.style.display = 'flex';
        submitBtn.disabled = true;
        submitBtnText.textContent = 'Fetching...';

        // Animated loader status messages
        const statuses = [
            'Instagram લિંક ચેક થઈ રહી છે...',
            'HD મીડિયા સ્ટીમ મેળવી રહ્યા છીએ...',
            'ડાઉનલોડ લિંક્સ તૈયાર કરી રહ્યા છીએ...'
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
                showError(res.message || 'માહિતી મેળવવામાં સમસ્યા આવી. કૃપા કરીને લિંક ફરીથી તપાસો.');
            }
        } catch (error) {
            clearInterval(statusTimer);
            loaderCard.style.display = 'none';
            submitBtn.disabled = false;
            submitBtnText.textContent = 'Search';
            showError('સર્વર સાથે કનેક્ટ થવામાં સમસ્યા આવી. કૃપા કરીને થોડીવાર પછી પ્રયાસ કરો.');
        }
    });

    // 6. Render Result Card
    function renderResult(data) {
        const typeBadge = document.getElementById('resultTypeBadge');
        const authorAvatar = document.getElementById('resultAuthorAvatar');
        const authorName = document.getElementById('resultAuthorName');
        const mediaContainer = document.getElementById('mediaPreviewContainer');
        const captionText = document.getElementById('resultCaption');
        const downloadOptions = document.getElementById('downloadOptions');

        // Set Header
        typeBadge.textContent = (data.type || 'Media').toUpperCase();
        authorName.textContent = data.author || (data.username ? '@' + data.username : 'Instagram Media');
        
        if (data.type === 'dp' && data.thumbnail) {
            authorAvatar.src = data.thumbnail;
        } else {
            authorAvatar.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>';
        }

        // Caption / Bio
        captionText.textContent = data.caption || data.biography || (data.full_name ? data.full_name + ' Profile' : 'No description provided');

        // Media Preview
        mediaContainer.innerHTML = '';
        if (data.type === 'reel' || (data.media && data.media[0] && data.media[0].type === 'video')) {
            const videoUrl = data.download_url || (data.media && data.media[0].url);
            const videoElem = document.createElement('video');
            videoElem.className = 'media-preview-video';
            videoElem.controls = true;
            videoElem.playsInline = true;
            if (data.thumbnail) videoElem.poster = data.thumbnail;
            videoElem.src = videoUrl;
            mediaContainer.appendChild(videoElem);
        } else {
            const imgElem = document.createElement('img');
            imgElem.className = 'media-preview-img';
            imgElem.alt = 'Instagram Media Preview';
            imgElem.src = data.thumbnail || data.download_url;
            mediaContainer.appendChild(imgElem);
        }

        // Download Action Buttons
        downloadOptions.innerHTML = '';

        if (data.media && data.media.length > 0) {
            data.media.forEach((m, index) => {
                const btn = document.createElement('a');
                btn.href = m.proxy_url || m.url;
                btn.className = (index === 0) ? 'btn-download-main' : 'btn-download-sub';
                btn.setAttribute('download', '');
                btn.setAttribute('rel', 'nofollow noreferrer');
                
                const iconSvg = m.type === 'video' 
                    ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`
                    : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`;
                
                btn.innerHTML = `${iconSvg} <span>Download ${m.quality || (m.type === 'video' ? 'Video (MP4)' : 'Image (JPG)')}</span>`;
                downloadOptions.appendChild(btn);
            });
        } else if (data.download_url) {
            const mainBtn = document.createElement('a');
            mainBtn.href = data.download_url;
            mainBtn.className = 'btn-download-main';
            mainBtn.setAttribute('download', '');
            mainBtn.setAttribute('rel', 'nofollow noreferrer');
            mainBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> <span>Download File (HD)</span>`;
            downloadOptions.appendChild(mainBtn);
        }

        // Reset / Download Another Button
        const resetBtn = document.createElement('button');
        resetBtn.type = 'button';
        resetBtn.className = 'btn-reset';
        resetBtn.textContent = '↺ Download Another Link';
        resetBtn.addEventListener('click', () => {
            resultCard.style.display = 'none';
            urlInput.value = '';
            clearBtn.style.display = 'none';
            detectionIndicator.classList.remove('active');
            urlInput.focus();
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
});
