export type NodeSelectionModifiers = {
  shiftKey: boolean;
  metaKey: boolean;
  ctrlKey: boolean;
  /** Checkbox click without modifiers toggles instead of replacing selection. */
  toggle?: boolean;
};

export type NodeSelectionState = {
  selectedNodeIds: number[];
  lastSelectedNodeId: number | null;
};

export function isRowInteractiveTarget(target: EventTarget | null): boolean {
  if (!(target instanceof Element)) {
    return false;
  }

  return Boolean(
    target.closest(
      'button, a, input, label, textarea, select, [role="checkbox"], [data-no-row-select]',
    ),
  );
}

export function computeNodeSelection(
  nodeId: number,
  visibleSelectableNodeIds: number[],
  current: NodeSelectionState,
  modifiers: NodeSelectionModifiers,
): NodeSelectionState {
  const { selectedNodeIds, lastSelectedNodeId } = current;
  const additive = modifiers.metaKey || modifiers.ctrlKey;
  const range =
    modifiers.shiftKey &&
    lastSelectedNodeId !== null &&
    visibleSelectableNodeIds.includes(lastSelectedNodeId);

  if (range) {
    const anchorIndex = visibleSelectableNodeIds.indexOf(lastSelectedNodeId);
    const targetIndex = visibleSelectableNodeIds.indexOf(nodeId);

    if (anchorIndex !== -1 && targetIndex !== -1) {
      const [from, to] =
        anchorIndex < targetIndex ? [anchorIndex, targetIndex] : [targetIndex, anchorIndex];

      return {
        selectedNodeIds: visibleSelectableNodeIds.slice(from, to + 1),
        lastSelectedNodeId: nodeId,
      };
    }
  }

  if (additive || modifiers.toggle) {
    const isSelected = selectedNodeIds.includes(nodeId);

    return {
      selectedNodeIds: isSelected
        ? selectedNodeIds.filter((id) => id !== nodeId)
        : [...selectedNodeIds, nodeId],
      lastSelectedNodeId: isSelected && selectedNodeIds.length === 1 ? null : nodeId,
    };
  }

  if (
    selectedNodeIds.length === 1 &&
    selectedNodeIds[0] === nodeId
  ) {
    return {
      selectedNodeIds: [],
      lastSelectedNodeId: null,
    };
  }

  return {
    selectedNodeIds: [nodeId],
    lastSelectedNodeId: nodeId,
  };
}
