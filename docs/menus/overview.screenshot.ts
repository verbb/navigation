import { defineScreenshotScenario } from '@verbb/docs-screenshots/api';
import { seedMainMenuFixture } from '../.screenshots/navigation/fixtures';
import {
    createExpandBuilderSidebarTabStep,
    createNavigationBuilderPromoCleanupStep,
    createNavigationBuilderPromoCropStep,
} from '../.screenshots/navigation/presets';

let menuBuildRoute = '/admin/navigation/menus';

// Content is ~1280px wide; add headroom for 20px crop padding on each side.
// Playwright clips selector captures to the viewport, so 1320 avoids right-edge cut-off.
const viewport = {
    width: 1320,
    height: 820,
    deviceScaleFactor: 2,
};

export default defineScreenshotScenario({
    id: 'menus-overview',
    output: '_screenshots/menus/overview-builder.png',
    route: () => menuBuildRoute,
    viewport,
    async setup(context) {
        const fixture = await seedMainMenuFixture(context);
        menuBuildRoute = fixture.buildRoute;
    },
    waitFor: [
        { type: 'selector', selector: 'body.navigation-builder-ready' },
        { type: 'selector', selector: '#navigation-builder-app, .navigation-builder-app', state: 'visible' },
    ],
    preSteps: [
        createNavigationBuilderPromoCleanupStep(),
        { type: 'wait', waitFor: { type: 'timeout', ms: 300 } },
        createExpandBuilderSidebarTabStep('Entries'),
        { type: 'wait', waitFor: { type: 'timeout', ms: 200 } },
        createNavigationBuilderPromoCropStep(20),
        { type: 'wait', waitFor: { type: 'selector', selector: '#navigation-docs-screenshot-frame', state: 'visible' } },
        { type: 'wait', waitFor: { type: 'timeout', ms: 200 } },
    ],
    steps: [],
    target: {
        type: 'selector',
        selector: '#navigation-docs-screenshot-frame',
        padding: 0,
    },
    caption: 'Navigation menu builder with nested entry, category, custom URL, and asset nodes.',
    intent: 'Capture the node tree and add-node sidebar with a small CP grey margin, similar to the plugin promo.',
});
