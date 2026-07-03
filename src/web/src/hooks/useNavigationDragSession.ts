import { useEffect, useRef, useState } from 'react';
import { isOrderedDragTarget, type TreeInstance } from '@headless-tree/core';
import type { BuilderNode } from '../types';
import {
  computeNavigationDragLinePosition,
  type NavigationDragLinePosition,
} from '../utils/navigationDragLine';

export type NavigationDragSessionSnapshot = {
  linePosition: NavigationDragLinePosition | null;
  nestTargetId: string | null;
};

function snapshotsEqual(
  a: NavigationDragSessionSnapshot,
  b: NavigationDragSessionSnapshot,
): boolean {
  if (a.nestTargetId !== b.nestTargetId) {
    return false;
  }

  if (a.linePosition === b.linePosition) {
    return true;
  }

  if (!a.linePosition || !b.linePosition) {
    return a.linePosition === b.linePosition;
  }

  return (
    a.linePosition.top === b.linePosition.top &&
    a.linePosition.left === b.linePosition.left &&
    a.linePosition.right === b.linePosition.right
  );
}

export function readNavigationDragSessionSnapshot(
  tree: TreeInstance<BuilderNode>,
  showTypeColumn: boolean,
): NavigationDragSessionSnapshot {
  const target = tree.getDragTarget();
  const nestTargetId =
    target && !isOrderedDragTarget(target) ? target.item.getId() : null;

  return {
    linePosition: nestTargetId
      ? null
      : computeNavigationDragLinePosition(tree, showTypeColumn),
    nestTargetId,
  };
}

/** Poll drag target during HTML5 drag so rows and the insert line stay in sync. */
export function useNavigationDragSession(
  tree: TreeInstance<BuilderNode>,
  showTypeColumn: boolean,
  isDragSession: boolean,
): NavigationDragSessionSnapshot {
  const [snapshot, setSnapshot] = useState<NavigationDragSessionSnapshot>({
    linePosition: null,
    nestTargetId: null,
  });
  const lastStableSnapshotRef = useRef<NavigationDragSessionSnapshot>({
    linePosition: null,
    nestTargetId: null,
  });

  useEffect(() => {
    if (!isDragSession) {
      lastStableSnapshotRef.current = { linePosition: null, nestTargetId: null };
      setSnapshot({ linePosition: null, nestTargetId: null });
      return;
    }

    let frameId = 0;

    const measure = () => {
      const hasDragTarget = Boolean(tree.getDragTarget());
      let next = readNavigationDragSessionSnapshot(tree, showTypeColumn);

      // HT briefly clears dragTarget on dragLeave between rows; hold the last target
      // so the line and nest highlight do not flash during fast pointer movement.
      if (hasDragTarget) {
        lastStableSnapshotRef.current = next;
      } else if (
        lastStableSnapshotRef.current.linePosition ||
        lastStableSnapshotRef.current.nestTargetId
      ) {
        next = lastStableSnapshotRef.current;
      }

      setSnapshot((prev) => (snapshotsEqual(prev, next) ? prev : next));
      frameId = window.requestAnimationFrame(measure);
    };

    frameId = window.requestAnimationFrame(measure);

    return () => {
      window.cancelAnimationFrame(frameId);
    };
  }, [isDragSession, showTypeColumn, tree]);

  return snapshot;
}
