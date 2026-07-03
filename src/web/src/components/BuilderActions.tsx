import { useEffect, useState } from 'react';
import { Button } from '@verbb/plugin-kit-react/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowUpRightFromSquare, faCheck } from '@fortawesome/pro-solid-svg-icons';
import { cn } from '@verbb/plugin-kit-react/utils';
import { useBuilderStore } from '../store';
import { t } from '../api';

type Props = {
  initialSettingsUrl: string;
  initialCanEditSettings: boolean;
};

export function BuilderActions({ initialSettingsUrl, initialCanEditSettings }: Props) {
  const state = useBuilderStore((s) => s.state);
  const loading = useBuilderStore((s) => s.loading);
  const error = useBuilderStore((s) => s.error);
  const publishing = useBuilderStore((s) => s.publishing);
  const discarding = useBuilderStore((s) => s.discarding);
  const saveFeedbackState = useBuilderStore((s) => s.saveFeedbackState);
  const structureDirty = useBuilderStore((s) => s.structureDirty);
  const publish = useBuilderStore((s) => s.publish);
  const discard = useBuilderStore((s) => s.discard);
  const setMenuContentOpen = useBuilderStore((s) => s.setMenuContentOpen);
  const [showSavedState, setShowSavedState] = useState(false);

  const settingsUrl = state?.settingsUrl ?? initialSettingsUrl;
  const canEditSettings = state?.permissions.canEditSettings ?? initialCanEditSettings;
  const stagingEnabled = state?.stagingEnabled ?? true;
  const hasMenuContent = state?.menuContent.hasFields ?? false;
  const changeCount = state?.session?.changeCount ?? 0;
  const hasUnsavedChanges = structureDirty || changeCount > 0;
  const isBusy = publishing || discarding;
  const saveLabel = changeCount > 0 ? t('Save ({count})', { count: changeCount }) : t('Save');

  useEffect(() => {
    if (publishing || saveFeedbackState !== 'success') {
      setShowSavedState(false);
      return undefined;
    }

    setShowSavedState(true);

    const timeout = setTimeout(() => {
      setShowSavedState(false);
    }, 2200);

    return () => {
      clearTimeout(timeout);
    };
  }, [publishing, saveFeedbackState]);

  if (loading || error || !stagingEnabled) {
    return canEditSettings ? (
      <Button
        href={settingsUrl}
        target="_blank"
        rel="noopener noreferrer"
        variant="default"
        className="gap-1.5"
      >
        {t('Settings')}
        <FontAwesomeIcon icon={faArrowUpRightFromSquare} className="size-3.5 opacity-70" />
      </Button>
    ) : null;
  }

  return (
    <div className="flex items-center gap-2">
      {hasMenuContent && (
        <Button
          type="button"
          variant="default"
          onClick={(event) => {
            event.currentTarget.blur();
            setMenuContentOpen(true);
          }}
        >
          {t('Menu content')}
        </Button>
      )}

      {canEditSettings && (
        <Button
          href={settingsUrl}
          target="_blank"
          rel="noopener noreferrer"
          variant="default"
          className="gap-1.5"
        >
          {t('Settings')}
          <FontAwesomeIcon icon={faArrowUpRightFromSquare} className="size-3.5 opacity-70" />
        </Button>
      )}

      {hasUnsavedChanges && (
        <Button
          type="button"
          variant="default"
          loading={discarding}
          disabled={isBusy}
          spinnerVariant="outline"
          onClick={() => void discard()}
        >
          {t('Discard')}
        </Button>
      )}

      <Button
        type="button"
        variant="primary"
        loading={publishing}
        disabled={isBusy}
        onClick={() => void publish()}
      >
        <span
          className={cn(
            'inline-flex items-center',
            showSavedState ? 'relative' : '',
          )}
        >
          {showSavedState && (
            <span className="pointer-events-none absolute inset-0 flex items-center justify-center">
              <FontAwesomeIcon icon={faCheck} className="size-3 transition-opacity duration-300" />
            </span>
          )}
          <span className={showSavedState ? 'text-transparent' : ''}>{saveLabel}</span>
        </span>
      </Button>
    </div>
  );
}
