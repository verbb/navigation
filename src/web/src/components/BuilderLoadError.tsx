import { Button } from '@verbb/plugin-kit-react/components/Button';
import { StatePanel } from '@verbb/plugin-kit-react/components/StatePanel';
import { t } from '../api';
import { formatBuilderError } from '../utils/builderError';

type Props = {
  error: unknown;
  onRetry: () => void;
};

export function BuilderLoadError({ error, onRetry }: Props) {
  const details = formatBuilderError(error);

  return (
    <StatePanel
      variant="error"
      heading={t('Couldn’t load menu builder.')}
      detailsLabel={t('Show error details')}
      copyLabel={t('Copy error details')}
      copiedLabel={t('Error details copied.')}
      copyErrorLabel={t('Copy failed. Select the details and copy them manually.')}
      copyable
      announce="assertive"
      className="[--pk-state-panel-min-height:20rem]"
    >
      <span>{t('Try again, or include the error details when contacting support.')}</span>
      <pre slot="details">{details}</pre>
      <Button slot="actions" type="button" variant="primary" onClick={onRetry}>
        {t('Try again')}
      </Button>
    </StatePanel>
  );
}
