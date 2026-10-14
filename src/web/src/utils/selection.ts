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
  selectionAnchorNodeId: number | null;
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
  const { selectedNodeIds, lastSelectedNodeId, selectionAnchorNodeId } = current;
  const additive = modifiers.metaKey || modifiers.ctrlKey;
  const rangeAnchorNodeId = selectionAnchorNodeId ?? lastSelectedNodeId;
  const range =
    modifiers.shiftKey &&
    rangeAnchorNodeId !== null &&
    visibleSelectableNodeIds.includes(rangeAnchorNodeId);

  if (range) {
    const anchorIndex = visibleSelectableNodeIds.indexOf(rangeAnchorNodeId);
    const targetIndex = visibleSelectableNodeIds.indexOf(nodeId);

    if (anchorIndex !== -1 && targetIndex !== -1) {
      const [from, to] =
        anchorIndex < targetIndex ? [anchorIndex, targetIndex] : [targetIndex, anchorIndex];

      return {
        selectedNodeIds: visibleSelectableNodeIds.slice(from, to + 1),
        lastSelectedNodeId: nodeId,
        // Repeated Shift-clicks resize the range from its original anchor.
        selectionAnchorNodeId: rangeAnchorNodeId,
      };
    }
  }

  if (additive || modifiers.toggle) {
    const isSelected = selectedNodeIds.includes(nodeId);
    const nextSelectedNodeIds = isSelected
      ? selectedNodeIds.filter((id) => id !== nodeId)
      : [...selectedNodeIds, nodeId];

    return {
      selectedNodeIds: nextSelectedNodeIds,
      lastSelectedNodeId: isSelected && selectedNodeIds.length === 1 ? null : nodeId,
      selectionAnchorNodeId: nextSelectedNodeIds.length ? nodeId : null,
    };
  }

  if (
    selectedNodeIds.length === 1 &&
    selectedNodeIds[0] === nodeId
  ) {
    return {
      selectedNodeIds: [],
      lastSelectedNodeId: null,
      selectionAnchorNodeId: null,
    };
  }

  return {
    selectedNodeIds: [nodeId],
    lastSelectedNodeId: nodeId,
    selectionAnchorNodeId: nodeId,
  };
}
