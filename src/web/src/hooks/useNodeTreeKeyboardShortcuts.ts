import { useEffect } from 'react';
import { useBuilderStore } from '../store';
import { getNodeMoveCapabilities } from '../utils/nodeMoveActions';

function isTypingTarget(): boolean {
  const focused = document.activeElement;

  if (!(focused instanceof Element)) {
    return false;
  }

  if (
    focused instanceof HTMLInputElement ||
    focused instanceof HTMLTextAreaElement ||
    focused instanceof HTMLSelectElement
  ) {
    return true;
  }

  if (focused instanceof HTMLElement && focused.isContentEditable) {
    return true;
  }

  return Boolean(
    focused.closest('[contenteditable="true"], [role="textbox"], [role="menu"], [role="dialog"]'),
  );
}

function getSelectedMoveNodeId(
  selectedNodeIds: number[],
  lastSelectedNodeId: number | null,
): number | null {
  if (selectedNodeIds.length === 0) {
    return null;
  }

  if (lastSelectedNodeId !== null && selectedNodeIds.includes(lastSelectedNodeId)) {
    return lastSelectedNodeId;
  }

  return selectedNodeIds[selectedNodeIds.length - 1] ?? null;
}

/** Alt/Option + arrow keys reorder the highlighted row when nothing else is focused. */
export function useNodeTreeKeyboardShortcuts(enabled: boolean): void {
  const moveNodeUp = useBuilderStore((s) => s.moveNodeUp);
  const moveNodeDown = useBuilderStore((s) => s.moveNodeDown);
  const indent = useBuilderStore((s) => s.indent);
  const outdent = useBuilderStore((s) => s.outdent);

  useEffect(() => {
    if (!enabled) {
      return;
    }

    const handleKeyDown = (event: KeyboardEvent) => {
      if (!event.altKey || event.metaKey || event.ctrlKey || event.shiftKey) {
        return;
      }

      if (isTypingTarget()) {
        return;
      }

      const { selectedNodeIds, lastSelectedNodeId, nodes, state } = useBuilderStore.getState();
      const nodeId = getSelectedMoveNodeId(selectedNodeIds, lastSelectedNodeId);

      if (nodeId === null) {
        return;
      }

      const node = nodes.find((candidate) => candidate.id === nodeId);

      if (!node || node.pendingDelete) {
        return;
      }

      const maxLevels = state?.menu.maxLevels ?? null;
      const capabilities = getNodeMoveCapabilities(nodes, nodeId, maxLevels);

      let handled = false;

      switch (event.key) {
        case 'ArrowUp':
          if (capabilities.canMoveUp) {
            moveNodeUp(nodeId);
            handled = true;
          }
          break;
        case 'ArrowDown':
          if (capabilities.canMoveDown) {
            moveNodeDown(nodeId);
            handled = true;
          }
          break;
        case 'ArrowLeft':
          if (capabilities.canMoveLeft) {
            outdent(nodeId);
            handled = true;
          }
          break;
        case 'ArrowRight':
          if (capabilities.canMoveRight) {
            indent(nodeId);
            handled = true;
          }
          break;
      }

      if (handled) {
        event.preventDefault();
        document
          .querySelector<HTMLElement>(`[data-node-id="${nodeId}"]`)
          ?.scrollIntoView({ block: 'nearest' });
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [enabled, indent, moveNodeDown, moveNodeUp, outdent]);
}
