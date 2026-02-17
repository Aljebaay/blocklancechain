/**
 * Parity Harness - Screenshot Capture
 * Captures screenshots from both legacy and Laravel servers.
 */
const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');
const config = require('./urls.json');

const SCREENSHOTS_DIR = path.join(__dirname, 'screenshots');

async function ensureDir(dir) {
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

async function captureScreenshot(browser, url, outputPath, options = {}) {
  const page = await browser.newPage();
  await page.setViewport(config.viewport);

  // Disable animations for deterministic screenshots
  await page.evaluateOnNewDocument(() => {
    const style = document.createElement('style');
    style.textContent = `
      *, *::before, *::after {
        animation-duration: 0s !important;
        animation-delay: 0s !important;
        transition-duration: 0s !important;
        transition-delay: 0s !important;
      }
    `;
    document.addEventListener('DOMContentLoaded', () => {
      document.head.appendChild(style);
    });
  });

  try {
    await page.goto(url, {
      waitUntil: 'networkidle2',
      timeout: 30000
    });

    // Wait a bit for any lazy-loaded content
    await new Promise(r => setTimeout(r, 1000));

    // Mask CSRF tokens and dynamic timestamps
    await page.evaluate(() => {
      // Hide CSRF token inputs
      document.querySelectorAll('input[name="_token"]').forEach(el => {
        el.value = 'MASKED';
      });
      // Hide dynamic timestamps - but only the actual value, not layout-affecting elements
      document.querySelectorAll('.time.d-none').forEach(el => {
        el.textContent = 'MASKED';
      });
    });

    await page.screenshot({
      path: outputPath,
      fullPage: true,
      type: 'png'
    });

    console.log(`  Captured: ${outputPath}`);
    return { success: true, path: outputPath };
  } catch (error) {
    console.error(`  Error capturing ${url}: ${error.message}`);
    return { success: false, error: error.message };
  } finally {
    await page.close();
  }
}

async function main() {
  await ensureDir(path.join(SCREENSHOTS_DIR, 'legacy'));
  await ensureDir(path.join(SCREENSHOTS_DIR, 'laravel'));

  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
  });

  const results = {};

  for (const page of config.pages) {
    console.log(`\nCapturing: ${page.title} (${page.path})`);

    const legacyUrl = config.legacyBase + page.path;
    const laravelUrl = config.laravelBase + page.path;

    const legacyResult = await captureScreenshot(
      browser,
      legacyUrl,
      path.join(SCREENSHOTS_DIR, 'legacy', `${page.id}.png`)
    );

    const laravelResult = await captureScreenshot(
      browser,
      laravelUrl,
      path.join(SCREENSHOTS_DIR, 'laravel', `${page.id}.png`)
    );

    results[page.id] = {
      title: page.title,
      path: page.path,
      legacy: legacyResult,
      laravel: laravelResult
    };
  }

  await browser.close();

  // Save results
  fs.writeFileSync(
    path.join(__dirname, 'capture-results.json'),
    JSON.stringify(results, null, 2)
  );

  console.log('\nCapture complete. Results saved to capture-results.json');
}

main().catch(console.error);
