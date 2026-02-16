/**
 * Parity Harness - Side-by-side comparison of Legacy PHP and Laravel apps.
 *
 * Captures screenshots, HTML content, HTTP status, redirect chains,
 * and generates a pixel-diff report.
 *
 * Usage:
 *   node scripts/parity/run_parity.mjs [--path <id>]
 *
 * Requires both servers to be running:
 *   Legacy:  http://127.0.0.1:8081
 *   Laravel: http://127.0.0.1:8000
 */

import { chromium } from 'playwright';
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const configPath = join(__dirname, 'paths.json');
const config = JSON.parse(readFileSync(configPath, 'utf-8'));

const REPORT_DIR = join(__dirname, '..', '..', 'report', 'parity');
const MATRIX_PATH = join(__dirname, '..', '..', 'PARITY_MATRIX.md');

const filterPathId = process.argv.includes('--path')
  ? process.argv[process.argv.indexOf('--path') + 1]
  : null;

function normalizeHtml(html) {
  // Remove CSRF tokens
  html = html.replace(/name="_token"\s+value="[^"]*"/g, 'name="_token" value="CSRF"');
  html = html.replace(/<meta\s+name="csrf-token"\s+content="[^"]*"/g, '<meta name="csrf-token" content="CSRF"');
  // Remove dynamic timestamps (common patterns)
  html = html.replace(/\b\d{10,13}\b/g, 'TIMESTAMP');
  // Remove whitespace variance
  html = html.replace(/\s+/g, ' ');
  return html.trim();
}

async function capturePageData(page, url, routeDir, prefix) {
  const responses = [];
  const redirectChain = [];
  let finalStatus = 200;

  page.on('response', (resp) => {
    if (resp.url().startsWith(url.split('?')[0].substring(0, url.indexOf('/', 8)))) {
      responses.push({
        url: resp.url(),
        status: resp.status(),
        headers: resp.headers(),
      });
    }
  });

  try {
    const response = await page.goto(url, {
      waitUntil: 'networkidle',
      timeout: 30000,
    });

    if (response) {
      finalStatus = response.status();
      const chain = response.request().redirectedFrom();
      let req = chain;
      while (req) {
        const resp = await req.response();
        if (resp) {
          redirectChain.push({
            url: req.url(),
            status: resp.status(),
          });
        }
        req = req.redirectedFrom();
      }
      redirectChain.reverse();
    }
  } catch (err) {
    console.error(`  ERROR navigating to ${url}: ${err.message}`);
    finalStatus = -1;
  }

  // Wait a little more for any dynamic rendering
  await page.waitForTimeout(1000);

  // Take screenshot
  const screenshotPath = join(routeDir, `${prefix}_screenshot.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });

  // Get HTML content
  const html = await page.content();
  const htmlPath = join(routeDir, `${prefix}_page.html`);
  writeFileSync(htmlPath, html, 'utf-8');

  // Get page title
  const title = await page.title();

  // Get console errors
  const consoleErrors = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text());
    }
  });

  return {
    status: finalStatus,
    redirectChain,
    title,
    html,
    normalizedHtml: normalizeHtml(html),
    screenshotPath,
    htmlPath,
    consoleErrors,
  };
}

function computePixelDiff(img1Path, img2Path, diffPath) {
  try {
    const img1 = PNG.sync.read(readFileSync(img1Path));
    const img2 = PNG.sync.read(readFileSync(img2Path));

    // Use the larger dimensions
    const width = Math.max(img1.width, img2.width);
    const height = Math.max(img1.height, img2.height);

    // Resize images to the same dimensions by creating padded versions
    const padImage = (img, w, h) => {
      const padded = new PNG({ width: w, height: h });
      // Fill with white
      for (let i = 0; i < padded.data.length; i += 4) {
        padded.data[i] = 255;
        padded.data[i + 1] = 255;
        padded.data[i + 2] = 255;
        padded.data[i + 3] = 255;
      }
      // Copy original image data
      for (let y = 0; y < img.height; y++) {
        for (let x = 0; x < img.width; x++) {
          const srcIdx = (y * img.width + x) * 4;
          const dstIdx = (y * w + x) * 4;
          padded.data[dstIdx] = img.data[srcIdx];
          padded.data[dstIdx + 1] = img.data[srcIdx + 1];
          padded.data[dstIdx + 2] = img.data[srcIdx + 2];
          padded.data[dstIdx + 3] = img.data[srcIdx + 3];
        }
      }
      return padded;
    };

    const padded1 = padImage(img1, width, height);
    const padded2 = padImage(img2, width, height);

    const diff = new PNG({ width, height });
    const numDiffPixels = pixelmatch(
      padded1.data,
      padded2.data,
      diff.data,
      width,
      height,
      { threshold: 0.1 }
    );

    writeFileSync(diffPath, PNG.sync.write(diff));

    const totalPixels = width * height;
    const diffPercent = ((numDiffPixels / totalPixels) * 100).toFixed(2);

    return {
      diffPixels: numDiffPixels,
      totalPixels,
      diffPercent: parseFloat(diffPercent),
      width,
      height,
    };
  } catch (err) {
    console.error(`  Pixel diff error: ${err.message}`);
    return { diffPixels: -1, totalPixels: 0, diffPercent: 100, width: 0, height: 0 };
  }
}

function generateReport(results) {
  const rows = results.map((r) => {
    const statusMatch = r.legacy.status === r.laravel.status ? 'MATCH' : 'MISMATCH';
    const titleMatch = r.legacy.title === r.laravel.title ? 'MATCH' : 'MISMATCH';
    const visualStatus = r.pixelDiff
      ? r.pixelDiff.diffPercent < 1
        ? 'PASS'
        : r.pixelDiff.diffPercent < 5
        ? 'CLOSE'
        : 'FAIL'
      : 'ERROR';

    const overall =
      statusMatch === 'MATCH' && visualStatus === 'PASS' ? 'PASS' : 'FAIL';

    return {
      ...r,
      statusMatch,
      titleMatch,
      visualStatus,
      overall,
    };
  });

  // Generate HTML report
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Parity Report</title>
<style>
  body { font-family: 'Segoe UI', Tahoma, sans-serif; margin: 20px; background: #f5f5f5; }
  h1 { color: #333; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 30px; background: white; }
  th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
  th { background: #333; color: white; }
  .pass { background: #d4edda; color: #155724; font-weight: bold; }
  .fail { background: #f8d7da; color: #721c24; font-weight: bold; }
  .close { background: #fff3cd; color: #856404; font-weight: bold; }
  .screenshots { display: flex; gap: 10px; flex-wrap: wrap; margin: 10px 0; }
  .screenshots img { max-width: 400px; border: 1px solid #ccc; }
  .route-section { margin-bottom: 40px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
  .summary { font-size: 1.2em; margin-bottom: 20px; }
  .label { font-weight: bold; }
</style>
</head>
<body>
<h1>Parity Report</h1>
<p class="summary">Generated: ${new Date().toISOString()}</p>
<table>
<tr>
  <th>Route</th>
  <th>Path</th>
  <th>Status Match</th>
  <th>Title Match</th>
  <th>Visual</th>
  <th>Pixel Diff %</th>
  <th>Overall</th>
</tr>
${rows
  .map(
    (r) => `<tr>
  <td>${r.routeId}</td>
  <td>${r.path}</td>
  <td class="${r.statusMatch === 'MATCH' ? 'pass' : 'fail'}">${r.statusMatch} (${r.legacy.status} vs ${r.laravel.status})</td>
  <td class="${r.titleMatch === 'MATCH' ? 'pass' : 'fail'}">${r.titleMatch}</td>
  <td class="${r.visualStatus.toLowerCase()}">${r.visualStatus}</td>
  <td>${r.pixelDiff ? r.pixelDiff.diffPercent + '%' : 'N/A'}</td>
  <td class="${r.overall.toLowerCase()}">${r.overall}</td>
</tr>`
  )
  .join('\n')}
</table>

${rows
  .map(
    (r) => `<div class="route-section">
  <h2>${r.routeId}: ${r.path}</h2>
  <p><span class="label">Legacy status:</span> ${r.legacy.status} | <span class="label">Laravel status:</span> ${r.laravel.status}</p>
  <p><span class="label">Legacy title:</span> ${r.legacy.title}</p>
  <p><span class="label">Laravel title:</span> ${r.laravel.title}</p>
  <p><span class="label">Pixel diff:</span> ${r.pixelDiff ? r.pixelDiff.diffPercent + '% (' + r.pixelDiff.diffPixels + ' pixels)' : 'N/A'}</p>
  <div class="screenshots">
    <div><h4>Legacy</h4><img src="${r.routeId}/legacy_screenshot.png" alt="legacy"></div>
    <div><h4>Laravel</h4><img src="${r.routeId}/laravel_screenshot.png" alt="laravel"></div>
    <div><h4>Diff</h4><img src="${r.routeId}/diff.png" alt="diff"></div>
  </div>
</div>`
  )
  .join('\n')}

</body>
</html>`;

  writeFileSync(join(REPORT_DIR, 'index.html'), html, 'utf-8');

  // Update PARITY_MATRIX.md
  const matrix = `# PARITY MATRIX

> Auto-generated: ${new Date().toISOString()}

| ID | Path | Method | Auth | Legacy Status | Laravel Status | Title Match | Visual Diff % | Parity Status | Notes |
|----|------|--------|------|---------------|----------------|-------------|---------------|---------------|-------|
${rows
  .map(
    (r) =>
      `| ${r.routeId} | \`${r.path}\` | ${r.method} | ${r.auth} | ${r.legacy.status} | ${r.laravel.status} | ${r.titleMatch} | ${r.pixelDiff ? r.pixelDiff.diffPercent + '%' : 'N/A'} | ${r.overall} | ${r.notes || ''} |`
  )
  .join('\n')}

## Legend
- **PASS**: Status matches, visual diff < 1%
- **CLOSE**: Status matches, visual diff 1-5%
- **FAIL**: Status mismatch OR visual diff > 5%
- **NOT_IMPLEMENTED**: Route not yet implemented in Laravel

## Screenshots
See \`report/parity/<route-id>/\` for full screenshots and diff images.
`;
  writeFileSync(MATRIX_PATH, matrix, 'utf-8');

  return rows;
}

async function main() {
  console.log('=== Parity Harness ===');
  console.log(`Legacy:  ${config.legacyBase}`);
  console.log(`Laravel: ${config.laravelBase}`);
  console.log('');

  // Ensure report dir
  mkdirSync(REPORT_DIR, { recursive: true });

  const pathsToTest = filterPathId
    ? config.paths.filter((p) => p.id === filterPathId)
    : config.paths;

  if (pathsToTest.length === 0) {
    console.error('No paths to test. Check --path argument.');
    process.exit(1);
  }

  const browser = await chromium.launch({ headless: true });
  const results = [];

  for (const route of pathsToTest) {
    console.log(`\nTesting: ${route.id} (${route.path})`);

    const routeDir = join(REPORT_DIR, route.id);
    mkdirSync(routeDir, { recursive: true });

    // Legacy capture
    console.log(`  Capturing legacy: ${config.legacyBase}${route.path}`);
    const legacyContext = await browser.newContext({
      viewport: config.viewport,
    });
    const legacyPage = await legacyContext.newPage();
    const legacyData = await capturePageData(
      legacyPage,
      `${config.legacyBase}${route.path}`,
      routeDir,
      'legacy'
    );
    await legacyContext.close();

    // Laravel capture
    console.log(`  Capturing laravel: ${config.laravelBase}${route.path}`);
    const laravelContext = await browser.newContext({
      viewport: config.viewport,
    });
    const laravelPage = await laravelContext.newPage();
    const laravelData = await capturePageData(
      laravelPage,
      `${config.laravelBase}${route.path}`,
      routeDir,
      'laravel'
    );
    await laravelContext.close();

    // Pixel diff
    console.log('  Computing pixel diff...');
    const diffPath = join(routeDir, 'diff.png');
    const pixelDiff = computePixelDiff(
      legacyData.screenshotPath,
      laravelData.screenshotPath,
      diffPath
    );
    console.log(`  Diff: ${pixelDiff.diffPercent}% (${pixelDiff.diffPixels} pixels)`);

    results.push({
      routeId: route.id,
      path: route.path,
      method: route.method,
      auth: route.auth,
      legacy: legacyData,
      laravel: laravelData,
      pixelDiff,
      notes: '',
    });
  }

  await browser.close();

  // Generate report
  const reportRows = generateReport(results);

  console.log('\n=== Summary ===');
  const passCount = reportRows.filter((r) => r.overall === 'PASS').length;
  const failCount = reportRows.filter((r) => r.overall === 'FAIL').length;
  console.log(`PASS: ${passCount}  FAIL: ${failCount}  TOTAL: ${reportRows.length}`);
  console.log(`\nReport: ${join(REPORT_DIR, 'index.html')}`);
  console.log(`Matrix: ${MATRIX_PATH}`);
}

main().catch((err) => {
  console.error('Fatal error:', err);
  process.exit(1);
});
