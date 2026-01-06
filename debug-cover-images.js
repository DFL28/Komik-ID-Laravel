const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: "new", args: ['--no-sandbox'] });
    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    console.log('Loading page...');
    await page.goto('https://kiryuu03.com/manga/bill-the-blacksmith/', { waitUntil: 'domcontentloaded' });
    await new Promise(r => setTimeout(r, 2000));

    const imageInfo = await page.evaluate(() => {
        const result = {
            thumbImg: !!document.querySelector('.thumb img'),
            summaryImg: !!document.querySelector('.summary_image img'),
            itempropImg: !!document.querySelector('img[itemprop="image"]'),
            allImages: []
        };

        const imgs = Array.from(document.querySelectorAll('img'));
        imgs.forEach((img, idx) => {
            if (idx < 10) { // First 10 images only
                result.allImages.push({
                    src: img.src.substring(0, 100),
                    width: img.width,
                    height: img.height,
                    alt: img.alt,
                    class: img.className
                });
            }
        });

        return result;
    });

    console.log('Image Analysis:');
    console.log('Has .thumb img:', imageInfo.thumbImg);
    console.log('Has .summary_image img:', imageInfo.summaryImg);
    console.log('Has img[itemprop="image"]:', imageInfo.itempropImg);
    console.log('\nFirst 10 images:');
    imageInfo.allImages.forEach((img, idx) => {
        console.log(`\n[${idx}]`);
        console.log('  SRC:', img.src);
        console.log('  Size:', `${img.width}x${img.height}`);
        console.log('  Alt:', img.alt);
        console.log('  Class:', img.class);
    });

    await browser.close();
})();
