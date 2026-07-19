import { cn } from '../utils/cn';
import type { BuilderNode } from '../types';

type Props = {
  node: BuilderNode;
};

export function NodeTypeBadge({ node }: Props) {
  return (
    <div className="cursor-default select-none text-right text-[10px] font-semibold uppercase tracking-wide">
      <span
        className={cn('inline-block whitespace-nowrap rounded border border-transparent px-1 py-0.5', node.typeClass)}
        title={node.url ?? undefined}
        style={{
          color: `rgb(${node.typeTextColorRgb})`,
          backgroundColor: `rgba(${node.typeColorRgb}, 0.1)`,
          borderColor: `rgba(${node.typeTextColorRgb}, 0.35)`,
        }}
      >
        {node.typeLabel}
      </span>
    </div>
  );
}
