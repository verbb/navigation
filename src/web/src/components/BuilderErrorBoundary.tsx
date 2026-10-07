import type { ReactNode } from 'react';
import { AppErrorBoundary } from '@verbb/plugin-kit-react/utils';

declare const Craft: {
  t: (category: string, message: string, params?: Record<string, unknown>) => string;
};

type Props = {
  children: ReactNode;
};

/** Navigation labels and layout for the shared Plugin Kit app error boundary. */
export function BuilderErrorBoundary({ children }: Props) {
  return (
    <AppErrorBoundary
      consoleLabel="Navigation builder crashed:"
      heading={Craft.t('navigation', 'Something went wrong')}
      message={Craft.t(
        'navigation',
        'The navigation builder failed to load. Please refresh the page or try again.',
      )}
      detailsLabel={Craft.t('navigation', 'Show error details')}
      copyLabel={Craft.t('navigation', 'Copy error details')}
      copiedLabel={Craft.t('navigation', 'Error details copied.')}
      copyErrorLabel={Craft.t(
        'navigation',
        'Copy failed. Select the details and copy them manually.',
      )}
      reloadLabel={Craft.t('navigation', 'Reload')}
      className="[--pk-state-panel-min-height:20rem]"
    >
      {children}
    </AppErrorBoundary>
  );
}
