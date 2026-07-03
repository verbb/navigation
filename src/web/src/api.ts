import type { BuilderNode, BuilderState, MenuContentTab, StructureMove } from './types';
import { getCraft } from './utils/cp';

export async function fetchBuilderState(menuId: number, siteId: number): Promise<BuilderState> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/get-state', {
    data: { menuId, siteId },
  });

  return response.data as unknown as BuilderState;
}

export async function saveDraft(
  menuId: number,
  siteId: number,
  structureMoves: StructureMove[],
): Promise<Record<string, unknown>> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/save-draft', {
    data: { menuId, siteId, structureMoves },
  });

  return response.data;
}

export async function publishMenu(
  menuId: number,
  siteId: number,
  applyStructure: boolean,
  moves: StructureMove[],
): Promise<Record<string, unknown>> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/build-sessions/publish', {
    data: { menuId, siteId, applyStructure, moves },
  });

  return response.data;
}

export async function discardSession(menuId: number, siteId: number): Promise<Record<string, unknown>> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/build-sessions/discard', {
    data: { menuId, siteId },
  });

  return response.data;
}

export async function stageDelete(
  menuId: number,
  siteId: number,
  nodeId: number,
  withDescendants = false,
): Promise<{ nodes?: BuilderNode[]; session?: Record<string, unknown>; message?: string }> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/stage-delete', {
    data: { menuId, siteId, nodeId, withDescendants },
  });

  return response.data as { nodes?: BuilderNode[]; session?: Record<string, unknown>; message?: string };
}

export async function setNodeStatus(
  menuId: number,
  siteId: number,
  nodeIds: number[],
  status: 'enabled' | 'disabled',
): Promise<{ nodes?: BuilderNode[]; message?: string }> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/set-node-status', {
    data: { menuId, siteId, nodeIds, status },
  });

  return response.data as { nodes?: BuilderNode[]; message?: string };
}

export async function duplicateNodes(
  menuId: number,
  siteId: number,
  nodeIds: number[],
  deep = false,
): Promise<{ nodes?: BuilderNode[]; message?: string }> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/duplicate-nodes', {
    data: { menuId, siteId, nodeIds, deep },
  });

  return response.data as { nodes?: BuilderNode[]; message?: string };
}

export async function unstageDelete(
  menuId: number,
  siteId: number,
  nodeId: number,
): Promise<{ nodes?: BuilderNode[]; changeCount?: number }> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/build-sessions/unstage-delete', {
    data: { menuId, siteId, nodeId },
  });

  return response.data as { nodes?: BuilderNode[]; changeCount?: number };
}

export async function copyNodesToSite(
  menuId: number,
  sourceSiteId: number,
  nodeIds: number[],
  targetSiteId: number,
  deep = false,
): Promise<{ nodeId?: number; copiedNodeIds?: number[]; message?: string }> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/nodes/copy-to-site', {
    data: { menuId, sourceSiteId, nodeIds, siteId: targetSiteId, deep },
  });

  return response.data as { nodeId?: number; copiedNodeIds?: number[]; message?: string };
}

/** @deprecated Use {@see copyNodesToSite} instead. */
export async function copyNodeToSite(
  nodeId: number,
  targetSiteId: number,
): Promise<{ nodeId?: number; message?: string }> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/nodes/copy-to-site', {
    data: { nodeId, siteId: targetSiteId },
  });

  return response.data as { nodeId?: number; message?: string };
}

export async function addNodes(nodes: Record<string, unknown>[]): Promise<Record<string, unknown>> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/nodes/add-nodes', {
    data: { nodes },
  });

  return response.data;
}

export async function fetchMenuContentForm(menuId: number, siteId: number): Promise<MenuContentTab[]> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/menu-content-form', {
    data: { menuId, siteId },
  });

  return (response.data as { tabs: MenuContentTab[] }).tabs ?? [];
}

export async function saveMenuContentDraft(
  menuId: number,
  siteId: number,
  fields: Record<string, unknown>,
): Promise<Record<string, unknown>> {
  const response = await getCraft().sendActionRequest('POST', 'navigation/builder/save-menu-content', {
    data: { menuId, siteId, fields },
  });

  return response.data;
}

export function displayError(error: unknown): void {
  const response = (error as { response?: { data?: { message?: string } } })?.response;

  if (response?.data?.message) {
    getCraft().cp.displayError(response.data.message);
  } else {
    getCraft().cp.displayError();
  }
}

export function t(message: string, params?: Record<string, unknown>): string {
  return getCraft().t('navigation', message, params);
}
