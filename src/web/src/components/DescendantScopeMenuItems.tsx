import { DropdownItem, DropdownSeparator } from '@verbb/plugin-kit-react/components/DropdownMenu';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { t } from '../api';

type Props = {
  label: string;
  /** Optional when the parent menu handles `pk-select` by item value. */
  onAction?: (deep: boolean) => void;
  disabled?: boolean;
  includeDeepOption?: boolean;
  deepAsSubmenu?: boolean;
  shallowLabel?: string;
  destructive?: boolean;
  /** pk-icon name (built-in or registered), e.g. `clone`, `xmark`. */
  icon?: string;
};

export function DescendantScopeMenuItems({
  label,
  onAction,
  disabled = false,
  includeDeepOption = false,
  deepAsSubmenu = false,
  shallowLabel,
  destructive = false,
  icon,
}: Props) {
  const iconNode = icon ? <Icon slot="prefix" icon={icon} /> : null;
  const resolvedShallowLabel = shallowLabel ?? t('Selected only');
  const handleAction = (deep: boolean) => {
    onAction?.(deep);
  };

  if (!includeDeepOption) {
    return (
      <DropdownItem
        value={label}
        disabled={disabled}
        destructive={destructive}
        onPkSelect={() => handleAction(false)}
      >
        {iconNode}
        {label}
      </DropdownItem>
    );
  }

  if (!deepAsSubmenu) {
    return (
      <>
        <DropdownItem
          value={`${label}-shallow`}
          disabled={disabled}
          destructive={destructive}
          onPkSelect={() => handleAction(false)}
        >
          {iconNode}
          {label}
        </DropdownItem>
        <DropdownItem
          value={`${label}-deep`}
          disabled={disabled}
          destructive={destructive}
          onPkSelect={() => handleAction(true)}
        >
          {iconNode}
          {t('{label} (with descendants)', { label })}
        </DropdownItem>
      </>
    );
  }

  return (
    <DropdownItem value={`${label}-submenu`} disabled={disabled} destructive={destructive}>
      {iconNode}
      {label}
      <div slot="submenu">
        <DropdownItem value={`${label}-shallow`} onPkSelect={() => handleAction(false)}>
          {resolvedShallowLabel}
        </DropdownItem>
        <DropdownItem value={`${label}-deep`} onPkSelect={() => handleAction(true)}>
          {t('With descendants')}
        </DropdownItem>
      </div>
    </DropdownItem>
  );
}

export { DropdownSeparator };
