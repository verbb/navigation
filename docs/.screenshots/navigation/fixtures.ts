import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import type { ScreenshotSetupContext } from '@verbb/docs-screenshots/types';

export type MainMenuFixture = {
    menuId: number;
    buildRoute: string;
    editRoute: string;
};

const MAIN_MENU_HANDLE = 'docsScreenshotMainMenu';
const fixtureDir = dirname(fileURLToPath(import.meta.url));
const promoMenuSeedScript = readFileSync(join(fixtureDir, 'seed-promo-menu.php'), 'utf8');

export async function seedMainMenuFixture(context: ScreenshotSetupContext): Promise<MainMenuFixture> {
    const output = await context.runCraftScript(promoMenuSeedScript, { label: 'seed-promo-menu' });

    const menuId = Number.parseInt(output.trim(), 10);

    if (!Number.isFinite(menuId) || menuId <= 0) {
        throw new Error(`Unable to determine menu ID from output: ${output}`);
    }

    const adminPath = context.profile.adminPath ?? 'admin';

    return {
        menuId,
        buildRoute: `/${adminPath}/navigation/menus/build/${menuId}`,
        editRoute: `/${adminPath}/navigation/menus/edit/${menuId}`,
    };
}

export { MAIN_MENU_HANDLE };
