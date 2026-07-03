import {
  DropdownMenuItem,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
} from '@verbb/plugin-kit-react/components';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { t } from '../api';

type Props = {
  label: string;
  onAction: (deep: boolean) => void;
  disabled?: boolean;
  includeDeepOption?: boolean;
  deepAsSubmenu?: boolean;
  shallowLabel?: string;
  variant?: 'default' | 'destructive';
  icon?: IconDefinition;
};

export function DescendantScopeMenuItems({
  label,
  onAction,
  disabled = false,
  includeDeepOption = false,
  deepAsSubmenu = false,
  shallowLabel,
  variant,
  icon,
}: Props) {
  const iconNode = icon ? <FontAwesomeIcon icon={icon} /> : null;
  const resolvedShallowLabel = shallowLabel ?? t('Selected only');

  if (!includeDeepOption) {
    return (
      <DropdownMenuItem variant={variant} disabled={disabled} onClick={() => onAction(false)}>
        {iconNode}
        {label}
      </DropdownMenuItem>
    );
  }

  if (!deepAsSubmenu) {
    return (
      <>
        <DropdownMenuItem variant={variant} disabled={disabled} onClick={() => onAction(false)}>
          {iconNode}
          {label}
        </DropdownMenuItem>
        <DropdownMenuItem variant={variant} disabled={disabled} onClick={() => onAction(true)}>
          {iconNode}
          {t('{label} (with descendants)', { label })}
        </DropdownMenuItem>
      </>
    );
  }

  return (
    <DropdownMenuSub>
      <DropdownMenuSubTrigger disabled={disabled} className={variant === 'destructive' ? 'text-error' : undefined}>
        {iconNode}
        {label}
      </DropdownMenuSubTrigger>
      <DropdownMenuSubContent className="min-w-44">
        <DropdownMenuItem onClick={() => onAction(false)}>{resolvedShallowLabel}</DropdownMenuItem>
        <DropdownMenuItem onClick={() => onAction(true)}>{t('With descendants')}</DropdownMenuItem>
      </DropdownMenuSubContent>
    </DropdownMenuSub>
  );
}
