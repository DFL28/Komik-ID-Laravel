const puppeteer = require('puppeteer');

const url = process.argv[2];
const mode = process.argv[3] || 'detail'; // 'list', 'detail', 'chapter'

if (!url) {
    console.error('Usage: node scraper.js <url> [mode]');
    process.exit(1);
}

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            headless: "new",
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();

        // Set User Agent to avoid detection
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        console.error(`[Puppeteer] Navigating to ${url}...`);

        // Go to URL and wait
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 }); // Networkidle2 sometimes hangs on ads

        // Auto Scroll to trigger lazy loading / HTMX
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 200;
                let scrolls = 0;
                const timer = setInterval(() => {
                    const scrollHeight = document.body.scrollHeight;
                    window.scrollBy(0, distance);
                    totalHeight += distance;
                    scrolls++;

                    // Limit scroll to avoid infinite loops or taking too long (max 50 scrolls)
                    if (totalHeight >= scrollHeight || scrolls > 50) {
                        clearInterval(timer);
                        resolve();
                    }
                }, 100);
            });
        });

        // Wait for potential dynamic content
        await new Promise(r => setTimeout(r, 2000));

        const data = await page.evaluate(async (mode) => {
            const result = {
                title: document.title,
                description: '',
                cover: '',
                chapters: [],
                images: [], // For chapter mode
                genres: []
            };

            // Helper
            const clean = (str) => str ? str.trim().replace(/\s+/g, ' ') : '';
            const hostname = window.location.hostname;

            // --- WESTMANGA LOGIC ---
            if (hostname.includes('westmanga')) {
                if (mode === 'detail') {
                    const titleEl = document.querySelector('h1');
                    if (titleEl) result.title = clean(titleEl.innerText);

                    const img = document.querySelector('.thumb img, .summary_image img');
                    if (img) result.cover = img.src;

                    const descEl = document.querySelector('.entry-content, .summary__content');
                    if (descEl) result.description = clean(descEl.innerText);

                    // Genres
                    const genreLinks = document.querySelectorAll('.mgen a, .genres a, .genre-info a');
                    if (genreLinks.length > 0) result.genres = Array.from(genreLinks).map(a => clean(a.innerText));

                    const chapterLinks = document.querySelectorAll('li .chapter-link a, .chapter-list a, .listing-chapters_wrap a');
                    chapterLinks.forEach(a => {
                        const numText = a.innerText.match(/Chapter\s*([\d\.]+)/i);
                        const num = numText ? parseFloat(numText[1]) : 0;
                        result.chapters.push({
                            title: clean(a.innerText),
                            number: num,
                            url: a.href,
                        });
                    });
                } else if (mode === 'chapter') {
                    const imgs = document.querySelectorAll('#readerarea img, .reader-area img');
                    if (imgs.length > 0) {
                        result.images = Array.from(imgs).map(i => i.src);
                    }
                }
            }

            // --- KIRYUU LOGIC ---
            else if (hostname.includes('kiryuu')) {
                if (mode === 'detail') {
                    const titleEl = document.querySelector('h1');
                    if (titleEl) result.title = clean(titleEl.innerText);

                    // Cover: Smart detection for Kiryuu
                    // Strategy 1: WordPress post image (most reliable)
                    let coverImg = document.querySelector('img.wp-post-image, img[class*="post-image"]');

                    // Strategy 2: Image with alt matching title or common cover selectors
                    if (!coverImg) {
                        const titleText = document.querySelector('h1')?.innerText.toLowerCase();
                        coverImg = document.querySelector('.thumb img, .summary_image img, img[itemprop="image"]');

                        // Try to find image with alt matching title
                        if (!coverImg && titleText) {
                            const imgs = Array.from(document.querySelectorAll('img'));
                            coverImg = imgs.find(img => {
                                const alt = (img.alt || '').toLowerCase();
                                return alt.length > 3 && titleText.includes(alt.substring(0, 10));
                            });
                        }
                    }

                    // Strategy 3: Find largest non-ad image as fallback
                    if (!coverImg) {
                        const imgs = Array.from(document.querySelectorAll('img'));

                        // Ad keywords to exclude
                        const adKeywords = ['royal', 'casino', 'betting', 'slot', 'judi', 'banner', 'ads', 'iklan', 'dewa', 'ibo', 'tokyo', 'zeus', 'macau'];

                        const validImages = imgs.filter(i => {
                            const src = i.src.toLowerCase();
                            const alt = (i.alt || '').toLowerCase();

                            // Must be from uploads
                            if (!src.includes('uploads')) return false;

                            // Exclude logos and small images (check natural dimensions if available)
                            if (src.includes('logo') || src.includes('avatar')) return false;

                            // Exclude ad images (check both src and alt)
                            if (adKeywords.some(keyword => src.includes(keyword) || alt.includes(keyword))) {
                                return false;
                            }

                            // Exclude animated gifs (usually ads)  
                            if (src.endsWith('.gif')) return false;

                            // Prefer JPG/PNG/WEBP
                            if (!src.match(/\.(jpg|jpeg|png|webp)/i)) return false;

                            return true;
                        });

                        // Get the image with best score (largest size + has meaningful alt)
                        if (validImages.length > 0) {
                            coverImg = validImages.reduce((best, img) => {
                                // Use naturalWidth/Height if available (actual image size)
                                const width = img.naturalWidth || img.width || 0;
                                const height = img.naturalHeight || img.height || 0;
                                const bestWidth = best.naturalWidth || best.width || 0;
                                const bestHeight = best.naturalHeight || best.height || 0;

                                const currentSize = width * height;
                                const bestSize = bestWidth * bestHeight;

                                // Bonus score for having alt text
                                const currentScore = currentSize * (img.alt ? 1.2 : 1);
                                const bestScore = bestSize * (best.alt ? 1.2 : 1);

                                return currentScore > bestScore ? img : best;
                            });
                        }
                    }

                    if (coverImg) result.cover = coverImg.src;

                    const descEl = document.querySelector('.entry-content, [itemprop="description"]');
                    if (descEl) result.description = clean(descEl.innerText);

                    const genreLinks = document.querySelectorAll('.mgen a, .genres a');
                    if (genreLinks.length > 0) {
                        result.genres = Array.from(genreLinks).map(a => clean(a.innerText));
                    }

                    // Chapters
                    // Strategy: Fetch AJAX directly using postId from body class to bypass scrolling issues
                    let chaptersFound = false;
                    const postClass = Array.from(document.body.classList).find(c => c.startsWith('postid-'));

                    if (postClass) {
                        const postId = postClass.replace('postid-', '');
                        // Construct AJAX URL
                        const ajaxUrl = `https://${hostname}/wp-admin/admin-ajax.php?manga_id=${postId}&page=1&action=chapter_list`;

                        try {
                            // Fetch in browser context (shares Cloudflare cookies)
                            const resp = await fetch(ajaxUrl, {
                                method: 'GET',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });

                            if (resp.ok) {
                                const html = await resp.text();
                                if (html.length > 50) {
                                    // Parse the snippet
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(html, 'text/html');

                                    // Kiryuu NEW structure: <div data-chapter-number>
                                    let chapterDivs = doc.querySelectorAll('div[data-chapter-number]');

                                    if (chapterDivs.length > 0) {
                                        chapterDivs.forEach(div => {
                                            const a = div.querySelector('a');
                                            if (!a) return;

                                            const chapterNum = parseFloat(div.getAttribute('data-chapter-number'));
                                            const chapterTitle = clean(a.innerText || a.textContent);

                                            result.chapters.push({
                                                title: chapterTitle || `Chapter ${chapterNum}`,
                                                number: chapterNum || 0,
                                                url: a.href
                                            });
                                        });
                                    } else {
                                        // Fallback: Try old structure <li data-num>
                                        const listItems = doc.querySelectorAll('li[data-num], li');
                                        listItems.forEach(li => {
                                            const a = li.querySelector('a');
                                            if (!a || !a.href.includes('chapter')) return;

                                            const numAttr = li.getAttribute('data-num') || li.getAttribute('data-chapter-number');
                                            let num = numAttr ? parseFloat(numAttr) : 0;

                                            if (!num) {
                                                const txt = (a.innerText || a.textContent);
                                                const match = txt.match(/Chapter\s*([\d\.]+)/i);
                                                if (match) num = parseFloat(match[1]);
                                            }

                                            result.chapters.push({
                                                title: clean(a.innerText || a.textContent),
                                                number: num,
                                                url: a.href
                                            });
                                        });
                                    }

                                    if (result.chapters.length > 0) chaptersFound = true;
                                }
                            }
                        } catch (e) {
                            console.error('AJAX Fetch Failed', e);
                        }
                    }

                    // Fallback to DOM if AJAX failed or yielded nothing
                    if (!chaptersFound) {
                        const listItems = document.querySelectorAll('li[data-num] a');
                        // ... same old logic ...
                        if (listItems.length > 0) {
                            listItems.forEach(a => {
                                let t = clean(a.innerText);
                                let num = parseFloat(a.closest('li').getAttribute('data-num'));
                                result.chapters.push({
                                    title: t,
                                    number: num,
                                    url: a.href
                                });
                            });
                        } else {
                            const links = Array.from(document.querySelectorAll('a'));
                            const chapLinks = links.filter(a => (a.href.includes('chapter') || a.innerText.toLowerCase().includes('chapter')) && !a.href.includes('/manga/'));

                            const seenUrls = new Set();
                            chapLinks.forEach(a => {
                                if (seenUrls.has(a.href)) return;
                                seenUrls.add(a.href);

                                let nMatch = a.innerText.match(/Chapter\s*([\d\.]+)/i);
                                if (!nMatch) nMatch = a.href.match(/chapter-([\d\.]+)/i);

                                result.chapters.push({
                                    title: clean(a.innerText),
                                    number: nMatch ? parseFloat(nMatch[1]) : 0,
                                    url: a.href
                                });
                            });
                        }
                    }
                } else if (mode === 'chapter') {
                    // Kiryuu Chapter Images
                    // Strategy 1: Look for #readerarea (old structure)
                    let imgs = document.querySelectorAll('#readerarea img, .reader-area img');

                    // Strategy 2: Check window.ts_reader
                    if (imgs.length === 0 && window.ts_reader && window.ts_reader.sources) {
                        try {
                            const imageUrls = window.ts_reader.sources[0].images;
                            if (imageUrls && imageUrls.length > 0) {
                                result.images = imageUrls;
                            }
                        } catch (e) { }
                    }

                    // Strategy 3: Get all images from external CDN (NOT from kiryuu domain)
                    if (result.images.length === 0) {
                        const allImgs = Array.from(document.querySelectorAll('img'));
                        const chapterImgs = allImgs.filter(img => {
                            const src = img.src.toLowerCase();

                            // Must be from external CDN (not kiryuu's own domain)
                            if (src.includes('kiryuu')) return false;

                            // Must be a valid image URL
                            if (!src.match(/\.(jpg|jpeg|png|webp)/i)) return false;

                            // Exclude tiny images (must be reasonable manga page size)
                            if (img.width > 0 && img.width < 200) return false;

                            return true;
                        });

                        result.images = chapterImgs.map(i => i.src);
                    }
                }
            }

            return result;
        }, mode);

        console.log(JSON.stringify(data));
        await browser.close();

    } catch (e) {
        console.error(e);
        if (browser) await browser.close();
        process.exit(1);
    }
})();
