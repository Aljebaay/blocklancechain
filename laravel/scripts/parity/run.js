/**
 * Parity Harness - Main Runner
 * Runs capture, diff, and smoke tests in sequence.
 */
const { execSync } = require('child_process');
const path = require('path');

const scriptDir = __dirname;

function run(script, label) {
  console.log(`\n${'='.repeat(60)}`);
  console.log(`  ${label}`);
  console.log(`${'='.repeat(60)}\n`);

  try {
    execSync(`node ${path.join(scriptDir, script)}`, {
      stdio: 'inherit',
      cwd: scriptDir
    });
    return true;
  } catch (error) {
    console.error(`\n${label} failed: ${error.message}`);
    return false;
  }
}

async function main() {
  const args = process.argv.slice(2);

  if (args.includes('--smoke-only')) {
    run('smoke.js', 'Smoke Tests');
    return;
  }

  if (args.includes('--diff-only')) {
    run('diff.js', 'Pixel Diff Computation');
    return;
  }

  if (args.includes('--capture-only')) {
    run('capture.js', 'Screenshot Capture');
    return;
  }

  // Full run
  const captureOk = run('capture.js', 'Screenshot Capture');
  if (captureOk) {
    run('diff.js', 'Pixel Diff Computation');
  }
  run('smoke.js', 'Smoke Tests');

  console.log('\n\nParity harness complete. Check report.json and report.md for results.');
}

main();
