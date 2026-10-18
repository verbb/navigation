import type { BuilderNode, BuilderState, MenuContentTab, StructureMove } from './types';
import { getCraft } from './utils/cp';

// Serialize requests for a menu, including reads after mutations. Ignoring an
// older response alone cannot prevent that older request from winning on the server.
const requests = new Map<string, Promise<unknown>>();
const serverStructureRevisions = new Map<string, string>();
const pendingNodeAdds = new Map<string, { fingerprint: string; requestId: string }>();
// Only call when the store adopts a server tree, never while retaining dirty structure.
export function acceptStructureRevision(menuId: number, revision?: string): void {
  if (revision) serverStructureRevisions.set(String(menuId), revision);
  else serverStructureRevisions.delete(String(menuId));
}
function sendBuilderRequest(...args: Parameters<ReturnType<typeof getCraft>['sendActionRequest']>) {
  const data = args[2]?.data;
  const firstNode = (data?.nodes as Array<Record<string, unknown>> | undefined)?.[0];
  const key = String(data?.menuId ?? firstNode?.menuId ?? 'legacy');
  const previous = requests.get(key);
  const send = () => {
    if (['navigation/builder/apply-structure', 'navigation/build-sessions/publish', 'navigation/builder/save-draft', 'navigation/builder/duplicate-nodes', 'navigation/nodes/copy-to-site'].includes(args[1]) && data && serverStructureRevisions.has(key)) {
      data.structureRevision = serverStructureRevisions.get(key);
    }
    return getCraft().sendActionRequest(...args).then((response) => {
      const revision = response.data?.structureRevision;
      const before = response.data?.previousStructureRevision;
      const baseline = serverStructureRevisions.get(key);
      const guardedMove = args[1] === 'navigation/builder/apply-structure'
        && data?.structureRevision === baseline;
      // A mutation may have followed someone else's save. Its new token is ours
      // only when the before token matches the tree we were editing.
      if (typeof revision === 'string' && baseline
        && (before === baseline || guardedMove)) serverStructureRevisions.set(key, revision);
      return response;
    });
  };
  const pending = previous ? previous.then(send, send) : send();
  requests.set(key, pending);
  const cleanup = () => { if (requests.get(key) === pending) requests.delete(key); };
  void pending.then(cleanup, cleanup);
  return pending;
}

export async function fetchBuilderState(menuId: number, siteId: number): Promise<BuilderState> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/get-state', {
    data: { menuId, siteId },
  });

  return response.data as unknown as BuilderState;
}

export async function saveDraft(
  menuId: number,
  siteId: number,
  structureMoves: StructureMove[],
  structureRevision?: string,
): Promise<Record<string, unknown>> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/save-draft', {
    data: { menuId, siteId, structureMoves, structureRevision },
  });

  return response.data;
}

export async function publishMenu(
  menuId: number,
  siteId: number,
  applyStructure: boolean,
  moves: StructureMove[],
  structureRevision?: string,
): Promise<Record<string, unknown>> {
  const response = await sendBuilderRequest('POST', 'navigation/build-sessions/publish', {
    data: { menuId, siteId, applyStructure, moves, structureRevision },
  });

  pendingNodeAdds.delete(String(menuId));
  return response.data;
}

/** Live Structure Saves: persist moves immediately (no build session). */
export async function applyStructure(
  menuId: number,
  siteId: number,
  moves: StructureMove[],
  structureRevision?: string,
): Promise<Record<string, unknown>> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/apply-structure', {
    data: { menuId, siteId, moves, structureRevision },
  });

  return response.data;
}

export async function discardSession(menuId: number, siteId: number): Promise<Record<string, unknown>> {
  const response = await sendBuilderRequest('POST', 'navigation/build-sessions/discard', {
    data: { menuId, siteId },
  });

  pendingNodeAdds.delete(String(menuId));
  return response.data;
}

export async function stageDelete(
  menuId: number,
  siteId: number,
  nodeId: number,
  withDescendants = false,
): Promise<{ structureRevision?: string; nodes?: BuilderNode[]; session?: Record<string, unknown>; message?: string }> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/stage-delete', {
    data: { menuId, siteId, nodeId, withDescendants },
  });

  return response.data as { structureRevision?: string; nodes?: BuilderNode[]; session?: Record<string, unknown>; message?: string };
}

export async function setNodeStatus(
  menuId: number,
  siteId: number,
  nodeIds: number[],
  status: 'enabled' | 'disabled',
): Promise<{ structureRevision?: string; nodes?: BuilderNode[]; session?: BuilderState['session']; message?: string }> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/set-node-status', {
    data: { menuId, siteId, nodeIds, status },
  });

  return response.data as { structureRevision?: string; nodes?: BuilderNode[]; session?: BuilderState['session']; message?: string };
}

export async function duplicateNodes(
  menuId: number,
  siteId: number,
  nodeIds: number[],
  deep = false,
): Promise<{
  nodes?: BuilderNode[];
  session?: BuilderState['session'];
  duplications?: Array<{ sourceId: number; duplicateId: number }>;
  duplicatedNodeIds?: number[];
  message?: string;
}> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/duplicate-nodes', {
    data: { menuId, siteId, nodeIds, deep },
  });

  return response.data as {
    nodes?: BuilderNode[];
    session?: BuilderState['session'];
    duplications?: Array<{ sourceId: number; duplicateId: number }>;
    duplicatedNodeIds?: number[];
    message?: string;
  };
}

export async function unstageDelete(
  menuId: number,
  siteId: number,
  nodeId: number,
): Promise<{ structureRevision?: string; nodes?: BuilderNode[]; session?: BuilderState['session']; changeCount?: number; message?: string }> {
  const response = await sendBuilderRequest('POST', 'navigation/build-sessions/unstage-delete', {
    data: { menuId, siteId, nodeId },
  });

  return response.data as { structureRevision?: string; nodes?: BuilderNode[]; session?: BuilderState['session']; changeCount?: number; message?: string };
}

export async function copyNodesToSite(
  menuId: number,
  sourceSiteId: number,
  nodeIds: number[],
  targetSiteId: number,
  deep = false,
  remapLinkedElements = false,
): Promise<{ nodeId?: number; copiedNodeIds?: number[]; message?: string }> {
  const response = await sendBuilderRequest('POST', 'navigation/nodes/copy-to-site', {
    data: { menuId, sourceSiteId, nodeIds, siteId: targetSiteId, deep, remapLinkedElements },
  });

  return response.data as { nodeId?: number; copiedNodeIds?: number[]; message?: string };
}

/** @deprecated Use {@see copyNodesToSite} instead. */
export async function copyNodeToSite(
  nodeId: number,
  targetSiteId: number,
): Promise<{ nodeId?: number; message?: string }> {
  const response = await sendBuilderRequest('POST', 'navigation/nodes/copy-to-site', {
    data: { nodeId, siteId: targetSiteId },
  });

  return response.data as { nodeId?: number; message?: string };
}

function newAddRequestId(): string {
  // getRandomValues also works in HTTP development control panels.
  const bytes = crypto.getRandomValues(new Uint8Array(16));
  bytes[6] = (bytes[6] & 0x0f) | 0x40;
  bytes[8] = (bytes[8] & 0x3f) | 0x80;
  const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
  return [hex.slice(0, 8), hex.slice(8, 12), hex.slice(12, 16), hex.slice(16, 20), hex.slice(20)].join('-');
}

export async function addNodes(nodes: Record<string, unknown>[]): Promise<Record<string, unknown>> {
  const key = String(nodes[0]?.menuId ?? 'legacy');
  const fingerprint = JSON.stringify(nodes);
  let pending = pendingNodeAdds.get(key);
  if (!pending || pending.fingerprint !== fingerprint) {
    pending = { fingerprint, requestId: newAddRequestId() };
    pendingNodeAdds.set(key, pending);
  }
  // Keep the identity after an uncertain response, so a manual retry cannot add twice.
  const response = await sendBuilderRequest('POST', 'navigation/nodes/add-nodes', {
    data: { nodes, requestId: pending.requestId },
  });
  if (pendingNodeAdds.get(key) === pending) pendingNodeAdds.delete(key);

  return response.data;
}

export async function fetchMenuContentForm(menuId: number, siteId: number): Promise<MenuContentTab[]> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/menu-content-form', {
    data: { menuId, siteId },
  });

  return (response.data as { tabs: MenuContentTab[] }).tabs ?? [];
}

export async function saveMenuContentDraft(
  menuId: number,
  siteId: number,
  fields: Record<string, unknown>,
): Promise<Record<string, unknown>> {
  const response = await sendBuilderRequest('POST', 'navigation/builder/save-menu-content', {
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

export function displayNotice(message: string): void {
  getCraft().cp.displayNotice(message);
}

export function t(message: string, params?: Record<string, unknown>): string {
  return getCraft().t('navigation', message, params);
}
