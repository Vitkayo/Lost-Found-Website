import { chromium } from 'playwright';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const configPath = join(__dirname, 'screenshot-config.json');
const screenshotsDir = join(rootDir, 'screenshots');

if (!existsSync(configPath)) {
  console.error('Missing scripts/screenshot-config.json');
  console.error('Copy scripts/screenshot-config.example.json and adjust credentials first.');
  process.exit(1);
}

const config = JSON.parse(readFileSync(configPath, 'utf8'));
const baseUrl = (config.base_url ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const viewport = {
  width: config.viewport?.width ?? 1280,
  height: config.viewport?.height ?? 800,
};

const shots = [
  {
    name: 'homepage.png',
    path: '/',
    auth: null,
    caption: 'Landing page with hero search and recent campus reports',
  },
  {
    name: 'public-feature.png',
    path: '/board?sort=desc',
    auth: null,
    caption: 'Community board with search, filters, and item cards',
  },
  {
    name: 'dashboard.png',
    path: '/account',
    auth: 'user',
    caption: 'Account dashboard with owned reports and submitted claims',
  },
  {
    name: 'create-form.png',
    path: '/report',
    auth: 'user',
    caption: 'Report form for submitting a lost or found item',
  },
  {
    name: 'admin-dashboard.png',
    path: '/admin/dashboard',
    auth: 'admin',
    caption: 'Admin moderation dashboard for reports, claims, and users',
  },
];

async function loginUser(page, account, admin = false) {
  const loginPath = admin ? '/admin/login' : '/login';
  await page.goto(`${baseUrl}${loginPath}`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', account.email);
  await page.fill('input[name="password"]', account.password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
}

async function capture(page, shot) {
  await page.setViewportSize(viewport);
  await page.goto(`${baseUrl}${shot.path}`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(400);

  const outputPath = join(screenshotsDir, shot.name);
  await page.screenshot({
    path: outputPath,
    fullPage: false,
  });

  return outputPath;
}

const browser = await chromium.launch({ headless: true });

try {
  console.log(`Capturing portfolio screenshots at ${viewport.width}x${viewport.height}`);
  console.log(`Base URL: ${baseUrl}\n`);

  for (const shot of shots) {
    const context = await browser.newContext({
      viewport,
      deviceScaleFactor: 1,
    });
    const page = await context.newPage();

    if (shot.auth === 'user') {
      await loginUser(page, config.user);
    } else if (shot.auth === 'admin') {
      await loginUser(page, config.admin, true);
    }

    const outputPath = await capture(page, shot);
    const stats = await import('node:fs/promises').then((fs) => fs.stat(outputPath));
    const sizeKb = Math.round(stats.size / 1024);

    console.log(`✓ ${shot.name} (${sizeKb} KB) — ${shot.caption}`);
    await context.close();
  }
} finally {
  await browser.close();
}

console.log('\nDone. Screenshots saved to screenshots/');
