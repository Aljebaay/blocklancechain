/**
 * Parity Harness - Pixel Diff Computation
 * Compares screenshots from legacy and Laravel and computes diff percentage.
 */
const fs = require('fs');
const path = require('path');
const { PNG } = require('pngjs');
const pixelmatch = require('pixelmatch');

const SCREENSHOTS_DIR = path.join(__dirname, 'screenshots');
const DIFF_DIR = path.join(SCREENSHOTS_DIR, 'diff');
const PASS_THRESHOLD = 0.005; // 0.50%

function ensureDir(dir) {
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

function readPNG(filePath) {
  if (!fs.existsSync(filePath)) return null;
  const data = fs.readFileSync(filePath);
  return PNG.sync.read(data);
}

function comparePair(pageId) {
  const legacyPath = path.join(SCREENSHOTS_DIR, 'legacy', `${pageId}.png`);
  const laravelPath = path.join(SCREENSHOTS_DIR, 'laravel', `${pageId}.png`);

  const legacy = readPNG(legacyPath);
  const laravel = readPNG(laravelPath);

  if (!legacy || !laravel) {
    return {
      pageId,
      status: 'SKIP',
      reason: !legacy ? 'Legacy screenshot missing' : 'Laravel screenshot missing',
      diffPercent: null
    };
  }

  // Use the larger dimensions for comparison
  const width = Math.max(legacy.width, laravel.width);
  const height = Math.max(legacy.height, laravel.height);

  // Create canvases with the larger size, fill with white
  const img1 = new PNG({ width, height });
  const img2 = new PNG({ width, height });
  const diffImg = new PNG({ width, height });

  // Fill with white background
  for (let i = 0; i < width * height * 4; i += 4) {
    img1.data[i] = img1.data[i + 1] = img1.data[i + 2] = 255;
    img1.data[i + 3] = 255;
    img2.data[i] = img2.data[i + 1] = img2.data[i + 2] = 255;
    img2.data[i + 3] = 255;
  }

  // Copy legacy image data
  for (let y = 0; y < legacy.height; y++) {
    for (let x = 0; x < legacy.width; x++) {
      const srcIdx = (y * legacy.width + x) * 4;
      const dstIdx = (y * width + x) * 4;
      img1.data[dstIdx] = legacy.data[srcIdx];
      img1.data[dstIdx + 1] = legacy.data[srcIdx + 1];
      img1.data[dstIdx + 2] = legacy.data[srcIdx + 2];
      img1.data[dstIdx + 3] = legacy.data[srcIdx + 3];
    }
  }

  // Copy laravel image data
  for (let y = 0; y < laravel.height; y++) {
    for (let x = 0; x < laravel.width; x++) {
      const srcIdx = (y * laravel.width + x) * 4;
      const dstIdx = (y * width + x) * 4;
      img2.data[dstIdx] = laravel.data[srcIdx];
      img2.data[dstIdx + 1] = laravel.data[srcIdx + 1];
      img2.data[dstIdx + 2] = laravel.data[srcIdx + 2];
      img2.data[dstIdx + 3] = laravel.data[srcIdx + 3];
    }
  }

  const numDiffPixels = pixelmatch(img1.data, img2.data, diffImg.data, width, height, {
    threshold: 0.1
  });

  const totalPixels = width * height;
  const diffPercent = (numDiffPixels / totalPixels) * 100;
  const pass = diffPercent <= (PASS_THRESHOLD * 100);

  // Save diff image
  ensureDir(DIFF_DIR);
  const diffPath = path.join(DIFF_DIR, `${pageId}.png`);
  fs.writeFileSync(diffPath, PNG.sync.write(diffImg));

  return {
    pageId,
    status: pass ? 'PASS' : 'FAIL',
    diffPercent: parseFloat(diffPercent.toFixed(4)),
    numDiffPixels,
    totalPixels,
    dimensions: { legacy: { w: legacy.width, h: legacy.height }, laravel: { w: laravel.width, h: laravel.height } },
    diffImagePath: diffPath
  };
}

function main() {
  ensureDir(DIFF_DIR);

  // Get all page IDs from legacy screenshots
  const legacyDir = path.join(SCREENSHOTS_DIR, 'legacy');
  if (!fs.existsSync(legacyDir)) {
    console.log('No legacy screenshots found. Run capture first.');
    return;
  }

  const pageIds = fs.readdirSync(legacyDir)
    .filter(f => f.endsWith('.png'))
    .map(f => f.replace('.png', ''));

  const results = {};
  let allPass = true;

  for (const pageId of pageIds) {
    const result = comparePair(pageId);
    results[pageId] = result;

    if (result.status !== 'PASS') {
      allPass = false;
    }

    const statusIcon = result.status === 'PASS' ? 'PASS' : result.status === 'SKIP' ? 'SKIP' : 'FAIL';
    const diffStr = result.diffPercent !== null ? `${result.diffPercent}%` : 'N/A';
    console.log(`  [${statusIcon}] ${pageId}: ${diffStr}`);
  }

  // Write report.json
  const report = {
    timestamp: new Date().toISOString(),
    overallStatus: allPass ? 'PASS' : 'FAIL',
    threshold: `${PASS_THRESHOLD * 100}%`,
    pages: results
  };

  fs.writeFileSync(
    path.join(__dirname, 'report.json'),
    JSON.stringify(report, null, 2)
  );

  // Write report.md
  let md = `# Parity Report\n\n`;
  md += `Generated: ${report.timestamp}\n`;
  md += `Overall: **${report.overallStatus}**\n`;
  md += `Threshold: ${report.threshold}\n\n`;
  md += `| Page | Status | Diff % | Legacy Size | Laravel Size |\n`;
  md += `|------|--------|--------|-------------|-------------|\n`;

  for (const [id, r] of Object.entries(results)) {
    const dims = r.dimensions
      ? `${r.dimensions.legacy.w}x${r.dimensions.legacy.h} | ${r.dimensions.laravel.w}x${r.dimensions.laravel.h}`
      : 'N/A | N/A';
    md += `| ${id} | ${r.status} | ${r.diffPercent !== null ? r.diffPercent + '%' : 'N/A'} | ${dims} |\n`;
  }

  fs.writeFileSync(path.join(__dirname, 'report.md'), md);

  console.log(`\nOverall: ${report.overallStatus}`);
  console.log('Reports saved to report.json and report.md');
}

main();
