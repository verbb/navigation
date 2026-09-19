import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import type { ScreenshotSetupContext } from '@verbb/craft-screenshots/types';

type NavigationFixture = { buildRoute: string };
const supportDir = dirname(fileURLToPath(import.meta.url));
const seedScript = readFileSync(join(supportDir, 'seed', 'seed-navigation.php'), 'utf8');

export async function seedNavigationFixture(context: ScreenshotSetupContext): Promise<NavigationFixture> {
    const output = await context.runCraftScript(seedScript, { label: 'seed-navigation' });
    const fixture = JSON.parse(output.trim()) as NavigationFixture;
    if (!fixture.buildRoute) throw new Error(`Invalid Navigation fixture payload: ${output}`);
    return fixture;
}
