import type {
    ScreenshotStep,
    ScreenshotTarget,
    ScreenshotViewport,
} from '@verbb/docs-screenshots/types';
import {
    createCpDetailViewPreset as createBaseCpDetailViewPreset,
    createCpFocusedRegionPreset as createBaseCpFocusedRegionPreset,
    createCpFullScreenPreset as createBaseCpFullScreenPreset,
    createCpModalPreset as createBaseCpModalPreset,
} from '@verbb/docs-screenshots/presets';

type CpFocusedRegionOptions = {
    selector?: string;
    viewport?: ScreenshotViewport;
    padding?: NonNullable<Extract<ScreenshotTarget, { type: 'selector' }>['padding']>;
    hidePlaceholder?: boolean;
};

type CpFullScreenOptions = {
    viewport?: ScreenshotViewport;
    hidePlaceholder?: boolean;
};

type CpModalOptions = {
    viewport?: ScreenshotViewport;
    selector?: string;
    padding?: NonNullable<Extract<ScreenshotTarget, { type: 'selector' }>['padding']>;
};

type CpDetailViewOptions = {
    viewport?: ScreenshotViewport;
    selector?: string;
    padding?: NonNullable<Extract<ScreenshotTarget, { type: 'selector' }>['padding']>;
    hidePlaceholder?: boolean;
};

type NavigationBuilderFrameOptions = {
    fitToViewport?: boolean;
    hideAddNodePanel?: boolean;
};

const navigationScrollResetSelectors = [
    'html',
    'body',
    '#content-container',
    '#main-content',
    '#content',
    '.content-pane',
    '.navigation-builder-app',
];

function buildNavigationCleanupCss({ hidePlaceholder = true }: { hidePlaceholder?: boolean }) {
    const rules = [
        'craft-global-sidebar, footer#global-footer { display: none !important; }',
        'craft-global-sidebar { width: 0 !important; min-width: 0 !important; flex: 0 0 0 !important; }',
        '#global-header * { display: none !important; }',
        '#details-container { position: static !important; }',
        'body.fixed-header #header { position: static !important; top: auto !important; }',
        'body.fixed-header #content-container { padding-top: 0 !important; }',
        '#content-container, #main-content, #content { max-width: none !important; }',
        '#content-container { padding: 24px !important; }',
        '#main-content { padding-top: 0 !important; }',
        '#page-container, #content-container, #main-content, #content, .content-pane { left: 0 !important; margin-left: 0 !important; }',
        'html, body, * { scrollbar-width: none !important; -ms-overflow-style: none !important; }',
        'html::-webkit-scrollbar, body::-webkit-scrollbar, *::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }',
    ];

    if (hidePlaceholder) {
        rules.push('.cp-placeholder, .placeholder { display: none !important; }');
    }

    return rules.join('\n');
}

function buildNavigationCleanupStep({ hidePlaceholder = true }: { hidePlaceholder?: boolean } = {}): ScreenshotStep {
    const css = buildNavigationCleanupCss({ hidePlaceholder });

    return {
        type: 'evaluate',
        expression: `
            (() => {
                const styleId = 'navigation-docs-screenshot-cleanup';
                let style = document.getElementById(styleId);

                if (!(style instanceof HTMLStyleElement)) {
                    style = document.createElement('style');
                    style.id = styleId;
                    document.head.appendChild(style);
                }

                style.textContent = ${JSON.stringify(css)};

                ${JSON.stringify(navigationScrollResetSelectors)}.forEach((selector) => {
                    document.querySelectorAll(selector).forEach((element) => {
                        if (element instanceof HTMLElement) {
                            element.scrollTop = 0;
                            element.scrollLeft = 0;
                        }
                    });
                });
            })();
        `,
    };
}

export function createExpandBuilderSidebarTabStep(tabLabel: string): ScreenshotStep {
    return {
        type: 'evaluate',
        expression: `
            (() => {
                const host = document.querySelector('#navigation-builder-sidebar-root');
                const root = host?.shadowRoot;

                if (!root) {
                    return;
                }

                for (const button of root.querySelectorAll('#accordion button')) {
                    if (!(button instanceof HTMLButtonElement)) {
                        continue;
                    }

                    if (button.textContent?.trim() === ${JSON.stringify(tabLabel)}) {
                        if (button.getAttribute('data-active') !== 'true') {
                            button.click();
                        }

                        return;
                    }
                }
            })();
        `,
    };
}

export function createNavigationBuilderPromoCleanupStep(): ScreenshotStep {
    const css = [
        buildNavigationCleanupCss({ hidePlaceholder: true }),
        '#header-container { display: none !important; }',
        '#details-toggle-wrapper { display: none !important; }',
        '#content-container { padding: 0 !important; background: hsl(212, 50%, 93%) !important; }',
        '#content-container, #main-content, #content, .content-pane { overflow: visible !important; }',
        '#main-content { gap: 0 !important; align-items: stretch !important; padding-top: 0 !important; }',
        '#content.content-pane { padding: 0 !important; }',
        '#details-container { padding: 0 !important; margin: 0 !important; }',
        '#details .navigation-nodes-sidebar { margin: 0 !important; }',
    ].join('\n');

    return {
        type: 'evaluate',
        expression: `
            (() => {
                const styleId = 'navigation-docs-screenshot-promo-cleanup';
                let style = document.getElementById(styleId);

                if (!(style instanceof HTMLStyleElement)) {
                    style = document.createElement('style');
                    style.id = styleId;
                    document.head.appendChild(style);
                }

                style.textContent = ${JSON.stringify(css)};

                ${JSON.stringify(navigationScrollResetSelectors)}.forEach((selector) => {
                    document.querySelectorAll(selector).forEach((element) => {
                        if (element instanceof HTMLElement) {
                            element.scrollTop = 0;
                            element.scrollLeft = 0;
                        }
                    });
                });
            })();
        `,
    };
}

export function createNavigationBuilderPromoCropStep(padding = 20): ScreenshotStep {
    return {
        type: 'evaluate',
        expression: `
            (() => {
                document.getElementById('navigation-docs-screenshot-frame')?.remove();

                const getShadowRoot = (selector) => {
                    const host = document.querySelector(selector);
                    return host instanceof HTMLElement ? host.shadowRoot : null;
                };

                const resetScroll = (root) => {
                    const elements = root.querySelectorAll('*');

                    for (const element of elements) {
                        if (element instanceof HTMLElement) {
                            element.scrollTop = 0;
                            element.scrollLeft = 0;
                        }
                    }

                    if (root instanceof HTMLElement) {
                        root.scrollTop = 0;
                        root.scrollLeft = 0;
                    }
                };

                const appRoot = getShadowRoot('#navigation-builder-app');
                const sidebarRoot = getShadowRoot('#navigation-builder-sidebar-root');
                const contentContainer = document.querySelector('#content-container');

                if (appRoot) {
                    const treeLayoutStyleId = 'navigation-docs-screenshot-tree-layout';
                    let treeLayoutStyle = appRoot.getElementById(treeLayoutStyleId);

                    if (!(treeLayoutStyle instanceof HTMLStyleElement)) {
                        treeLayoutStyle = document.createElement('style');
                        treeLayoutStyle.id = treeLayoutStyleId;
                        appRoot.appendChild(treeLayoutStyle);
                    }

                    treeLayoutStyle.textContent = [
                        '.overflow-hidden.rounded-md.border { margin: 0 !important; max-width: 100% !important; }',
                        '.overflow-x-auto { overflow-x: visible !important; }',
                        '[role="table"] { min-width: 0 !important; width: 100% !important; }',
                    ].join('\\n');

                    resetScroll(appRoot);
                }

                resetScroll(document);
                window.scrollTo(0, 0);

                if (contentContainer instanceof HTMLElement) {
                    contentContainer.style.paddingLeft = '0px';
                }

                const inset = ${padding};

                const measureFrame = () => {
                    const treeCard = appRoot?.querySelector('.overflow-hidden.rounded-md.border')
                        ?? appRoot?.querySelector('[role="table"]')?.closest('.overflow-hidden')
                        ?? appRoot?.querySelector('[role="table"]');

                    const sidebarPanel = sidebarRoot?.querySelector('#accordion')
                        ?? sidebarRoot?.querySelector('#navigation-builder-sidebar');

                    if (!(treeCard instanceof HTMLElement) || !(sidebarPanel instanceof HTMLElement)) {
                        return null;
                    }

                    treeCard.scrollIntoView({ block: 'nearest', inline: 'start' });

                    const treeRect = treeCard.getBoundingClientRect();
                    const sidebarRect = sidebarPanel.getBoundingClientRect();
                    const detailsContainer = document.querySelector('#details-container');
                    const detailsRect = detailsContainer instanceof HTMLElement
                        ? detailsContainer.getBoundingClientRect()
                        : sidebarRect;
                    const contentRight = Math.max(treeRect.right, sidebarRect.right, detailsRect.right);

                    return {
                        left: Math.min(treeRect.left, sidebarRect.left) - inset,
                        top: Math.min(treeRect.top, sidebarRect.top) - inset,
                        right: contentRight + inset,
                        bottom: Math.max(treeRect.bottom, sidebarRect.bottom) + inset,
                    };
                };

                let frameRect = measureFrame();

                if (!frameRect) {
                    return;
                }

                if (frameRect.left < inset && contentContainer instanceof HTMLElement) {
                    contentContainer.style.paddingLeft = \`\${inset - frameRect.left}px\`;
                    frameRect = measureFrame();
                }

                if (!frameRect) {
                    return;
                }

                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                let left = Math.max(0, frameRect.left);
                let top = Math.max(0, frameRect.top);
                let right = Math.max(left + 1, frameRect.right);
                let bottom = Math.max(top + 1, frameRect.bottom);

                if (right > viewportWidth) {
                    right = viewportWidth;
                }

                if (bottom > viewportHeight) {
                    bottom = viewportHeight;
                }

                const frame = document.createElement('div');
                frame.id = 'navigation-docs-screenshot-frame';
                frame.style.position = 'fixed';
                frame.style.pointerEvents = 'none';
                frame.style.zIndex = '2147483646';
                frame.style.background = 'transparent';
                frame.style.left = \`\${left}px\`;
                frame.style.top = \`\${top}px\`;
                frame.style.width = \`\${Math.max(1, right - left)}px\`;
                frame.style.height = \`\${Math.max(1, bottom - top)}px\`;
                document.body.appendChild(frame);
            })();
        `,
    };
}

export function createNavigationBuilderFrameStep(options: NavigationBuilderFrameOptions = {}): ScreenshotStep {
    return {
        type: 'evaluate',
        expression: `
            (() => {
                const builder = document.querySelector('#navigation-builder-app, .navigation-builder-app');

                if (!(builder instanceof HTMLElement)) {
                    return;
                }

                if (${options.fitToViewport ?? false}) {
                    builder.style.maxWidth = '100%';
                    builder.style.width = '100%';
                }

                if (${options.hideAddNodePanel ?? false}) {
                    const addPanel = builder.querySelector('[data-navigation-add-panel], .navigation-add-node-panel');

                    if (addPanel instanceof HTMLElement) {
                        addPanel.style.display = 'none';
                    }
                }
            })();
        `,
    };
}

export function createCpFocusedRegionPreset(options: CpFocusedRegionOptions = {}) {
    const preset = createBaseCpFocusedRegionPreset(options);

    return {
        ...preset,
        steps: [
            buildNavigationCleanupStep({ hidePlaceholder: options.hidePlaceholder }),
            ...preset.steps,
        ] satisfies ScreenshotStep[],
    };
}

export function createCpFullScreenPreset(options: CpFullScreenOptions = {}) {
    const preset = createBaseCpFullScreenPreset(options);

    return {
        ...preset,
        steps: [
            buildNavigationCleanupStep({ hidePlaceholder: options.hidePlaceholder }),
            ...preset.steps,
        ] satisfies ScreenshotStep[],
    };
}

export function createCpModalPreset(options: CpModalOptions = {}) {
    return createBaseCpModalPreset(options);
}

export function createCpDetailViewPreset(options: CpDetailViewOptions = {}) {
    const preset = createBaseCpDetailViewPreset(options);

    return {
        ...preset,
        steps: [
            buildNavigationCleanupStep({ hidePlaceholder: options.hidePlaceholder }),
            ...preset.steps,
        ] satisfies ScreenshotStep[],
    };
}
