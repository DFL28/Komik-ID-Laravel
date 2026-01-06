const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: "new", args: ['--no-sandbox'] });
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    console.log('Loading chapter page...');
    await page.goto('https://kiryuu03.com/manga/bill-the-blacksmith/chapter-1.697166/', { waitUntil: 'domcontentloaded' });

    // Scroll untuk trigger lazy loading
    await page.evaluate(async () => {
        await new Promise((resolve) => {
            let scrolls = 0;
            const timer = setInterval(() => {
                window.scrollBy(0, 500);
                scrolls++;
                if (scrolls > 20) {
                    clearInterval(timer);
                    resolve();
                }
            }, 100);
        });
    });

    await new Promise(r => setTimeout(r, 3000));

    const debug = await page.evaluate(() => {
        const result = {
            hasReaderArea: !!document.querySelector('#readerarea'),
            hasReaderAreaImages: document.querySelectorAll('#readerarea img').length,
            hasTs_reader: !!window.ts_reader,
            allImages: document.querySelectorAll('img').length,
            sampleImageSrcs: []
        };

        const imgs = Array.from(document.querySelectorAll('img'));
        imgs.slice(0, 30).forEach(img => {
            result.sampleImageSrcs.push({
                src: img.src.substring(0, 100),
                id: img.id,
                class: img.className
            });
        });

        // Check if ts_reader exists
        if (window.ts_reader) {
            result.ts_reader_sources = window.ts_reader.sources ? window.ts_reader.sources.length : 0;
        }

        return result;
    });

    console.log('Debug Info:');
    console.log('Has #readerarea:', debug.hasReaderArea);
    console.log('#readerarea img count:', debug.hasReaderAreaImages);
    console.log('Has window.ts_reader:', debug.hasTs_reader);
    if (debug.ts_reader_sources !== undefined) {
        console.log('ts_reader.sources count:', debug.ts_reader_sources);
    }
    console.log('Total images on page:', debug.allImages);
    console.log('\nFirst 10 Images:');
    debug.sampleImageSrcs.forEach((img, idx) => {
        console.log(`[${idx}] ${img.src}`);
        if (img.id) console.log(`    ID: ${img.id}`);
        if (img.class) console.log(`    Class: ${img.class}`);
    });

    await browser.close();
})();
