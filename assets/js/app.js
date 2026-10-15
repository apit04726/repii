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
    function switchActiveTab(type, shouldScroll = false) {
        tabBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-type') === type);
        });
        dockBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-type') === type);
        });
        updatePlaceholder(type);

        if (shouldScroll) {
            if (navigator.vibrate) navigator.vibrate(10);
            searchForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => urlInput.focus(), 300);
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            switchActiveTab(btn.getAttribute('data-type'), false);
        });
    });

    dockBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            switchActiveTab(btn.getAttribute('data-type'), true);
        });
    });

    function updatePlaceholder(type) {
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

    function isValidInstagramTarget(val) {
        if (!val) return false;
        return /(?:instagram\.com|instagr\.am)\/(?:p|reel|reels|stories|tv|highlights)\/[A-Za-z0-9_.-]+/i.test(val) ||
               /(?:instagram\.com|instagr\.am)\/[A-Za-z0-9_.-]{3,30}\/?$/i.test(val) ||
               /^@[A-Za-z0-9._]{2,30}$/.test(val);
    }

    function triggerAutoSearch() {
        const val = urlInput.value.trim();
        if (!val || submitBtn.disabled) return;
        
        lastSearchedUrl = val;
        clearBtn.style.display = 'flex';
        detectUrlType(val);
        searchForm.dispatchEvent(new Event('submit'));
    }

    // 3. Input Auto-Detection, Debounced Auto-Search & Clear Button
    urlInput.addEventListener('input', () => {
        const val = urlInput.value.trim();
        clearBtn.style.display = val ? 'flex' : 'none';

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

    clearBtn.addEventListener('click', () => {
        urlInput.value = '';
        clearBtn.style.display = 'none';
        clearResultsAndErrors();
        urlInput.focus();
    });

    // Auto-search on paste (Ctrl+V, mobile long press, clipboard suggestion)
    urlInput.addEventListener('paste', () => {
        setTimeout(() => {
            const val = urlInput.value.trim();
            if (val && isValidInstagramTarget(val)) {
                triggerAutoSearch();
            }
        }, 50);
    });

    function detectUrlType(val) {
        if (!val) {
            detectionIndicator.classList.remove('active');
            return;
        }

        const t = (typeof i18n !== 'undefined' && i18n[currentAppLang]) ? i18n[currentAppLang] : null;
        let detected = '';
        let tabKey = 'all';

        if (/instagram\.com\/(?:reel|reels)\/([A-Za-z0-9_-]+)/i.test(val)) {
            detected = t ? t.detection.reel : 'Instagram Reel Detected';
            tabKey = 'reels';
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
            detectedTypeSpan.textContent = detected;
            detectionIndicator.classList.add('active');
            // Sync active tab in both desktop bar and mobile dock
            tabBtns.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-type') === tabKey);
            });
            dockBtns.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-type') === tabKey);
            });
        } else {
            detectionIndicator.classList.remove('active');
        }
    }

    // 4. Clipboard Paste Button (Auto triggers search)
    pasteBtn.addEventListener('click', async () => {
        try {
            if (navigator.clipboard && navigator.clipboard.readText) {
                const text = await navigator.clipboard.readText();
                if (text) {
                    urlInput.value = text.trim();
                    clearBtn.style.display = 'flex';
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

    // 5. Form Submission & AJAX
    searchForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = urlInput.value.trim();
        lastSearchedUrl = url;

        if (!url) {
            showError('Please paste a valid Instagram link.');
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

        const t = (typeof i18n !== 'undefined' && i18n[currentAppLang]) ? i18n[currentAppLang] : null;

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
                
                const labelText = t ? (m.type === 'video' ? t.results.downloadVideo : t.results.downloadImage) : `Download ${m.quality || (m.type === 'video' ? 'Video (MP4)' : 'Image (JPG)')}`;
                btn.innerHTML = `${iconSvg} <span>${labelText}</span>`;
                downloadOptions.appendChild(btn);
            });
        } else if (data.download_url) {
            const mainBtn = document.createElement('a');
            mainBtn.href = data.download_url;
            mainBtn.className = 'btn-download-main';
            mainBtn.setAttribute('download', '');
            mainBtn.setAttribute('rel', 'nofollow noreferrer');
            const labelText = t ? t.results.downloadFile : 'Download File (HD)';
            mainBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> <span>${labelText}</span>`;
            downloadOptions.appendChild(mainBtn);
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
                photos: 'Photos'
            },
            dock: {
                all: 'All',
                reels: 'Reels',
                dp: 'DP',
                stories: 'Stories',
                photos: 'Photos'
            },
            placeholders: {
                all: 'Paste Instagram Reel, DP, Story, or Post URL...',
                reels: 'Paste Instagram Reel link (e.g. https://www.instagram.com/reel/...)',
                dp: 'Enter Username or Profile link (e.g. @cristiano or instagram.com/...)',
                stories: 'Paste Instagram Story link or Username...',
                photos: 'Paste Instagram Post / Carousel photo link...'
            },
            pasteBtn: 'Paste',
            searchBtn: 'Search',
            searchingBtn: 'Processing...',
            detection: {
                reel: 'Instagram Reel Detected',
                story: 'Instagram Story Detected',
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
                heading: 'How to Download from Instagram',
                subheading: 'Save any Reel, Profile DP, Story or Photo in 3 easy steps',
                step1Title: 'Copy &amp; Paste Link',
                step1Desc: 'Copy any Instagram Reel link and tap <strong>"Paste"</strong> in InstaGo. Our smart engine auto-detects the link immediately.',
                step2Title: 'Auto-Fetch &amp; Preview',
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
                heading: 'Frequently Asked Questions',
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
                disclaimer: '<strong>Disclaimer:</strong> InstaGo is an independent tool and is not affiliated, endorsed, or associated with Instagram, Meta, or Facebook. We do not host any copyrighted videos or photos on our servers; all media files are served directly from official CDN servers.',
                linkReels: 'Reels Downloader',
                linkDP: 'DP Downloader',
                linkStories: 'Story Downloader',
                copyright: `&copy; ${new Date().getFullYear()} <strong>InstaGo</strong>. All rights reserved. Fast & Simple Instagram Downloader.`
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
                photos: 'ફોટોઝ'
            },
            dock: {
                all: 'બધા',
                reels: 'રીલ્સ',
                dp: 'DP',
                stories: 'સ્ટોરીઝ',
                photos: 'ફોટોઝ'
            },
            placeholders: {
                all: 'ઇન્સ્ટાગ્રામ રીલ, DP, સ્ટોરી કે પોસ્ટ લિંક પેસ્ટ કરો...',
                reels: 'ઇન્સ્ટાગ્રામ રીલ લિંક પેસ્ટ કરો (જેમ કે instagram.com/reel/...)',
                dp: 'યુઝરનેમ અથવા પ્રોફાઇલ લિંક નાખો (જેમ કે @cristiano)',
                stories: 'ઇન્સ્ટાગ્રામ સ્ટોરી લિંક અથવા યુઝરનેમ નાખો...',
                photos: 'ઇન્સ્ટાગ્રામ ફોટો / કેરોયુઝલ લિંક પેસ્ટ કરો...'
            },
            pasteBtn: 'પેસ્ટ',
            searchBtn: 'શોધો',
            searchingBtn: 'પ્રોસેસિંગ...',
            detection: {
                reel: 'ઇન્સ્ટાગ્રામ રીલ મળી',
                story: 'ઇન્સ્ટાગ્રામ સ્ટોરી મળી',
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
                heading: 'ઇન્સ્ટાગ્રામ પરથી કેવી રીતે ડાઉનલોડ કરવું?',
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
                heading: 'વારંવાર પૂછાતા પ્રશ્નો (FAQ)',
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
                disclaimer: '<strong>ડિસ્ક્લેમર:</strong> InstaGo એક સ્વતંત્ર ટૂલ છે અને Instagram, Meta કે Facebook સાથે સંલગ્ન નથી. અમે અમારા સર્વર પર કોઈપણ કોપીરાઇટ વિડીયો કે ફોટો હોસ્ટ કરતા નથી; તમામ મીડિયા સત્તાવાર CDN સર્વર પરથી સીધા પહોંચાડાય છે.',
                linkReels: 'રીલ્સ ડાઉનલોડર',
                linkDP: 'DP ડાઉનલોડર',
                linkStories: 'સ્ટોરી ડાઉનલોડર',
                copyright: `&copy; ${new Date().getFullYear()} <strong>InstaGo</strong>. સર્વાધિકાર સુરક્ષિત. ઝડપી અને સરળ ઇન્સ્ટાગ્રામ ડાઉનલોડર.`
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
                photos: 'फोटो'
            },
            dock: {
                all: 'सभी',
                reels: 'रील्स',
                dp: 'DP',
                stories: 'स्टोरीज',
                photos: 'फोटो'
            },
            placeholders: {
                all: 'इंस्टाग्राम रील, DP, स्टोरी या पोस्ट लिंक पेस्ट करें...',
                reels: 'इंस्टाग्राम रील लिंक पेस्ट करें (जैसे instagram.com/reel/...)',
                dp: 'यूजरनेम या प्रोफाइल लिंक दर्ज करें (जैसे @cristiano)',
                stories: 'इंस्टाग्राम स्टोरी लिंक या यूजरनेम दर्ज करें...',
                photos: 'इंस्टाग्राम फोटो / हिंडोला पोस्ट लिंक पेस्ट करें...'
            },
            pasteBtn: 'पेस्ट',
            searchBtn: 'सर्च करें',
            searchingBtn: 'प्रोसेस हो रहा है...',
            detection: {
                reel: 'इंस्टाग्राम रील मिली',
                story: 'इंस्टाग्राम स्टोरी मिली',
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
                heading: 'इंस्टाग्राम से कैसे डाउनलोड करें?',
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
                heading: 'अक्सर पूछे जाने वाले सवाल (FAQ)',
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
                disclaimer: '<strong>डिस्क्लेमर:</strong> InstaGo एक स्वतंत्र टूल है और Instagram, Meta या Facebook से संबद्ध नहीं है। हम अपने सर्वर पर कोई भी कॉपीराइट वीडियो या फोटो होस्ट नहीं करते हैं; सभी मीडिया आधिकारिक CDN सर्वर से सीधे आते हैं।',
                linkReels: 'रील्स डाउनलोडर',
                linkDP: 'DP डाउनलोडर',
                linkStories: 'स्टोरी डाउनलोडर',
                copyright: `&copy; ${new Date().getFullYear()} <strong>InstaGo</strong>. सर्वाधिकार सुरक्षित। तेज़ और आसान इंस्टाग्राम डाउनलोडर.`
            }
        }
    };

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
        const tabPhotos = document.getElementById('tabLabelPhotos');
        if (tabAll) tabAll.textContent = t.tabs.all;
        if (tabReels) tabReels.textContent = t.tabs.reels;
        if (tabDP) tabDP.textContent = t.tabs.dp;
        if (tabStories) tabStories.textContent = t.tabs.stories;
        if (tabPhotos) tabPhotos.textContent = t.tabs.photos;

        const dockAll = document.getElementById('dockLabelAll');
        const dockReels = document.getElementById('dockLabelReels');
        const dockDP = document.getElementById('dockLabelDP');
        const dockStories = document.getElementById('dockLabelStories');
        const dockPhotos = document.getElementById('dockLabelPhotos');
        if (dockAll) dockAll.textContent = t.dock.all;
        if (dockReels) dockReels.textContent = t.dock.reels;
        if (dockDP) dockDP.textContent = t.dock.dp;
        if (dockStories) dockStories.textContent = t.dock.stories;
        if (dockPhotos) dockPhotos.textContent = t.dock.photos;

        // 5. Input Actions & Placeholder
        const pasteText = document.getElementById('pasteBtnText');
        if (pasteText) pasteText.textContent = t.pasteBtn;
        if (submitBtnText && !submitBtn.disabled) submitBtnText.textContent = t.searchBtn;
        
        // Update placeholder for active tab
        const activeTabBtn = document.querySelector('.tab-btn.active');
        const activeType = activeTabBtn ? activeTabBtn.getAttribute('data-type') : 'all';
        updatePlaceholder(activeType);

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
        if (guideHeading) guideHeading.textContent = t.guide.heading;
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
        if (faqH) faqH.textContent = t.faq.heading;
        if (faqSub) faqSub.textContent = t.faq.subheading;
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
        const fDisc = document.getElementById('footerDisclaimer');
        const fReels = document.getElementById('footLinkReels');
        const fDP = document.getElementById('footLinkDP');
        const fStories = document.getElementById('footLinkStories');
        const fCopy = document.getElementById('footerCopyright');
        if (fDisc) fDisc.innerHTML = t.footer.disclaimer;
        if (fReels) fReels.textContent = t.footer.linkReels;
        if (fDP) fDP.textContent = t.footer.linkDP;
        if (fStories) fStories.textContent = t.footer.linkStories;
        if (fCopy) fCopy.innerHTML = t.footer.copyright;

        // 12. Persist in storage and set html lang
        document.documentElement.setAttribute('lang', lang);
        try {
            localStorage.setItem('instago_app_lang', lang);
            localStorage.setItem('instago_guide_lang', lang);
        } catch(e) {}
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

    // Apply saved or default language on startup
    applyLanguage(currentAppLang);
});
