const puppeteer = require('puppeteer');

const testUrl = 'https://kiryuu03.com/manga/solo-leveling/';

console.log('[DEBUG] Testing Kiryuu Chapter Extraction');
console.log('URL:', testUrl);
console.log('='.repeat(60));

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            headless: "new",
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();

        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        console.log('\n[1] Navigating to page...');
        await page.goto(testUrl, { waitUntil: 'domcontentloaded', timeout: 60000 });

        console.log('[2] Page loaded, scrolling...');
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 200;
                let scrolls = 0;
                const timer = setInterval(() => {
                    window.scrollBy(0, distance);
                    totalHeight += distance;
                    scrolls++;
                    if (totalHeight >= document.body.scrollHeight || scrolls > 50) {
                        clearInterval(timer);
                        resolve();
                    }
                }, 100);
            });
        });

        console.log('[3] Scroll complete, waiting 2s...');
        await new Promise(r => setTimeout(r, 2000));

        console.log('[4] Extracting manga info...');

        const debug = await page.evaluate(() => {
            const info = {
                title: document.title,
                bodyClasses: Array.from(document.body.classList),
                postId: null,
                foundSelectors: {
                    h1: !!document.querySelector('h1'),
                    description: !!document.querySelector('.entry-content, [itemprop="description"]'),
                    genres: document.querySelectorAll('.mgen a, .genres a').length,
                    chapterListDirect: document.querySelectorAll('li[data-num] a').length,
                    allLinks: document.querySelectorAll('a').length,
                    chapterLinks: 0
                },
                sampleHTML: '',
                ajaxTest: null
            };

            // Get post ID
            const postClass = Array.from(document.body.classList).find(c => c.startsWith('postid-'));
            if (postClass) {
                info.postId = postClass.replace('postid-', '');
            }

            // Sample HTML from potential chapter area
            const chapterArea = document.querySelector('#chapterlist, .eplister, .clstyle');
            if (chapterArea) {
                info.sampleHTML = chapterArea.innerHTML.substring(0, 500);
            } else {
                // Try to find any area with chapter mentions
                const allText = document.body.innerHTML;
                const chapterIndex = allText.toLowerCase().indexOf('chapter');
                if (chapterIndex > -1) {
                    info.sampleHTML = allText.substring(Math.max(0, chapterIndex - 200), chapterIndex + 300);
                }
            }

            // Count links that contain 'chapter'
            const allLinks = Array.from(document.querySelectorAll('a'));
            info.foundSelectors.chapterLinks = allLinks.filter(a =>
                a.href.includes('chapter') && !a.href.includes('/manga/')
            ).length;

            return info;
        });

        console.log('\n[DEBUG INFO]');
        console.log('Title:', debug.title);
        console.log('Body Classes:', debug.bodyClasses.join(', '));
        console.log('Post ID:', debug.postId || 'NOT FOUND');
        console.log('\nSelectors Found:');
        console.log('  - H1:', debug.foundSelectors.h1 ? '✓' : '✗');
        console.log('  - Description:', debug.foundSelectors.description ? '✓' : '✗');
        console.log('  - Genres:', debug.foundSelectors.genres);
        console.log('  - Chapter List (data-num):', debug.foundSelectors.chapterListDirect);
        console.log('  - Chapter Links (href):', debug.foundSelectors.chapterLinks);
        console.log('  - Total Links:', debug.foundSelectors.allLinks);

        console.log('\nSample HTML:');
        console.log(debug.sampleHTML);

        // Test AJAX if post ID found
        if (debug.postId) {
            console.log('\n[5] Testing AJAX Chapter List...');
            const ajaxUrl = `https://kiryuu03.com/wp-admin/admin-ajax.php?manga_id=${debug.postId}&page=1&action=chapter_list`;
            console.log('AJAX URL:', ajaxUrl);

            const ajaxResult = await page.evaluate(async (url) => {
                try {
                    const resp = await fetch(url, {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (resp.ok) {
                        const html = await resp.text();
                        return {
                            success: true,
                            length: html.length,
                            sample: html.substring(0, 500),
                            hasLi: html.includes('<li'),
                            hasChapter: html.toLowerCase().includes('chapter')
                        };
                    } else {
                        return { success: false, status: resp.status };
                    }
                } catch (e) {
                    return { success: false, error: e.message };
                }
            }, ajaxUrl);

            console.log('AJAX Result:', ajaxResult);

            if (ajaxResult.success && ajaxResult.hasLi) {
                console.log('\n✅ AJAX WORKING! Chapters can be extracted from AJAX response.');
            } else {
                console.log('\n⚠️  AJAX failed or returned empty.');
            }
        }

        await browser.close();
        console.log('\n[DONE] Test complete.');

    } catch (e) {
        console.error('\n[ERROR]', e.message);
        if (browser) await browser.close();
        process.exit(1);
    }
})();
