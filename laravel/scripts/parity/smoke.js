/**
 * Parity Harness - HTTP Smoke Tests
 * Validates status codes and content-types for key pages and assets.
 */
const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');
const config = require('./urls.json');

function fetch(url) {
  return new Promise((resolve, reject) => {
    const client = url.startsWith('https') ? https : http;
    const req = client.get(url, { timeout: 10000 }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        resolve({
          status: res.statusCode,
          headers: res.headers,
          contentType: res.headers['content-type'] || '',
          body: body.substring(0, 500)
        });
      });
    });
    req.on('error', reject);
    req.on('timeout', () => {
      req.destroy();
      reject(new Error('Request timed out'));
    });
  });
}

async function smokeTestUrl(baseUrl, pathStr, expected = {}) {
  const url = baseUrl + pathStr;
  try {
    const res = await fetch(url);
    const statusOk = expected.expectedStatus ? res.status === expected.expectedStatus : res.status >= 200 && res.status < 400;
    const ctOk = expected.expectedContentType
      ? res.contentType.includes(expected.expectedContentType)
      : true;

    return {
      url,
      path: pathStr,
      status: res.status,
      contentType: res.contentType.split(';')[0].trim(),
      statusOk,
      contentTypeOk: ctOk,
      pass: statusOk && ctOk
    };
  } catch (error) {
    return {
      url,
      path: pathStr,
      status: 0,
      contentType: '',
      statusOk: false,
      contentTypeOk: false,
      pass: false,
      error: error.message
    };
  }
}

async function main() {
  console.log('Smoke Testing Laravel Server...\n');

  const results = { pages: [], assets: [] };

  // Test pages
  for (const page of config.pages) {
    const result = await smokeTestUrl(config.laravelBase, page.path, { expectedStatus: 200 });
    results.pages.push({ ...result, id: page.id, title: page.title });
    console.log(`  [${result.pass ? 'PASS' : 'FAIL'}] ${page.title} (${page.path}) - ${result.status} ${result.contentType}`);
  }

  // Test assets
  if (config.smokeChecks && config.smokeChecks.assets) {
    console.log('\nAssets:');
    for (const asset of config.smokeChecks.assets) {
      const result = await smokeTestUrl(config.laravelBase, asset.path, asset);
      results.assets.push(result);
      console.log(`  [${result.pass ? 'PASS' : 'FAIL'}] ${asset.path} - ${result.status} ${result.contentType}`);
    }
  }

  // Summary
  const allPass = [...results.pages, ...results.assets].every(r => r.pass);
  console.log(`\nOverall: ${allPass ? 'PASS' : 'FAIL'}`);

  fs.writeFileSync(
    path.join(__dirname, 'smoke-results.json'),
    JSON.stringify({ timestamp: new Date().toISOString(), overall: allPass ? 'PASS' : 'FAIL', results }, null, 2)
  );

  console.log('Results saved to smoke-results.json');
}

main().catch(console.error);
