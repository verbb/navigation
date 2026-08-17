import type { ReactNode } from 'react';
import { AppErrorBoundary } from '@verbb/plugin-kit-react/utils';

declare const Craft: {
  t: (category: string, message: string, params?: Record<string, unknown>) => string;
};

type Props = {
  children: ReactNode;
};

/** Thin labels wrapper around kit AppErrorBoundary (Formie FormBuilderErrorBoundary shape). */
export function BuilderErrorBoundary({ children }: Props) {
  return (
    <AppErrorBoundary
      consoleLabel="Navigation builder crashed:"
      title={Craft.t('navigation', 'Something went wrong')}
      message={Craft.t(
        'navigation',
        'The navigation builder failed to load. Please refresh the page or try again.',
      )}
      detailsLabel={Craft.t('navigation', 'Show error details')}
      reloadLabel={Craft.t('navigation', 'Reload')}
      containerClassName="flex flex-1 items-center justify-center py-12"
    >
      {children}
    </AppErrorBoundary>
  );
}
