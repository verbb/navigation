import { defineScreenshotScenario } from '@verbb/craft-screenshots/api';
import { createCpFullScreenPreset } from '@verbb/craft-screenshots/presets';

import { seedNavigationFixture } from '../../support/fixtures';

const preset = createCpFullScreenPreset({
    viewport: { width: 1157, height: 674, deviceScaleFactor: 2 },
});
let buildRoute = '/admin/navigation';

export default defineScreenshotScenario({
    id: 'navigation-feature-tour-overview',
    output: 'feature-tour/navigation-nodes.png',
    route: () => buildRoute,
    viewport: preset.viewport,
    expectedOutput: { width: 2314, height: 1348 },
    async setup(context) {
        const fixture = await seedNavigationFixture(context);
        buildRoute = fixture.buildRoute;
    },
    waitFor: [
        { type: 'loadState', state: 'networkidle' },
        { type: 'selector', selector: '#navigation-nodes-index .element', state: 'visible' },
        { type: 'selector', selector: '.navigation-nodes-sidebar', state: 'visible' },
    ],
    steps: [
        ...preset.steps,
        { type: 'evaluate', expression: `document.querySelector('.tab-list-item[data-id="custom"] a, .tab-list-item a')?.click()` },
        { type: 'wait', waitFor: { type: 'timeout', ms: 300 } },
    ],
    target: preset.target,
    caption: 'A populated Navigation menu arranged into a nested hierarchy in Craft 5.',
    intent: 'Recreates the production Navigation builder image with the current Craft 5 node index and authoring sidebar.',
});
