import { isOrderedDragTarget, type TreeInstance } from '@headless-tree/core';
import type { BuilderNode } from '../types';
import {
  getNodeRowPaddingLeft,
  NODE_TREE_CHECKBOX_COLUMN_PX,
  NODE_TREE_TYPE_COLUMN_PX,
} from './nodeRowLayout';
import { NAVIGATION_TREE_ROOT_ID } from './headlessTreeData';

export type NavigationDragLinePosition = {
  top: number;
  left: number;
  right: number;
};

export function computeNavigationDragLinePosition(
  tree: TreeInstance<BuilderNode>,
  showTypeColumn: boolean,
): NavigationDragLinePosition | null {
  const target = tree.getDragTarget();
  const treeEl = tree.getElement();

  if (!target || !treeEl) {
    return null;
  }

  const treeRect = treeEl.getBoundingClientRect();
  const right = showTypeColumn ? NODE_TREE_TYPE_COLUMN_PX : 0;

  if (!isOrderedDragTarget(target)) {
    const folderItem = target.item;

    if (folderItem.getId() === NAVIGATION_TREE_ROOT_ID) {
      return null;
    }

    const folderRect = folderItem.getElement()?.getBoundingClientRect();

    if (!folderRect) {
      return null;
    }

    const childLevel = folderItem.getItemMeta().level + 2;

    return {
      top: folderRect.bottom - treeRect.top,
      left: NODE_TREE_CHECKBOX_COLUMN_PX + getNodeRowPaddingLeft(childLevel),
      right,
    };
  }

  const items = tree.getItems().filter((item) => item.getId() !== NAVIGATION_TREE_ROOT_ID);
  const lineIndex = target.dragLineIndex;
  const level = target.dragLineLevel + 1;
  const left = NODE_TREE_CHECKBOX_COLUMN_PX + getNodeRowPaddingLeft(level);

  const rectAt = (index: number) => items[index]?.getElement()?.getBoundingClientRect();

  let gapCenter: number | null = null;

  if (items.length === 0) {
    return null;
  }

  if (lineIndex >= items.length) {
    const aboveRect = rectAt(items.length - 1);
    gapCenter = aboveRect?.bottom ?? null;
  } else if (lineIndex <= 0) {
    const belowRect = rectAt(0);
    gapCenter = belowRect?.top ?? null;
  } else {
    const aboveRect = rectAt(lineIndex - 1);
    const belowRect = rectAt(lineIndex);

    if (aboveRect && belowRect) {
      gapCenter = (aboveRect.bottom + belowRect.top) / 2;
    } else {
      gapCenter = belowRect?.top ?? aboveRect?.bottom ?? null;
    }
  }

  if (gapCenter === null) {
    return null;
  }

  return {
    top: gapCenter - treeRect.top,
    left,
    right,
  };
}
