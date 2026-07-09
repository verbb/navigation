import type { SchemaIndex } from '@verbb/plugin-kit-react/forms/engine/SchemaIndex';

export type { SchemaIndex };

export type NodeStatusFilter = 'all' | 'enabled' | 'disabled' | 'trashed';

export type TreeColumnId = 'type';

export type BuilderNode = {
  id: number;
  title: string;
  type: string;
  typeLabel: string;
  typeClass: string;
  typeColorRgb: string;
  typeTextColorRgb: string;
  url: string | null;
  level: number;
  parentId: number | null;
  status: string;
  newWindow: boolean;
  classes: string | null;
  pendingAdd: boolean;
  pendingDelete: boolean;
  pendingEdit: boolean;
  enabled: boolean;
  enabledForSite: boolean;
  hasDescendants: boolean;
  isElementLinked: boolean;
};

export type StructureMove = {
  elementId: number;
  parentId: number | null;
  prevId: number | null;
};

export type BuilderTab = {
  id: string;
  label: string;
  button: string;
  category: 'element' | 'nodeType';
  type: string;
  elementType?: string;
  sources?: string[] | null;
  pickerConfig?: {
    criteria?: Record<string, unknown>;
    condition?: Record<string, unknown> | null;
  } | null;
  hasTitle?: boolean;
  hasUrl?: boolean;
  hasNewWindow?: boolean;
  schemaIndex: SchemaIndex;
  defaultValues: Record<string, unknown>;
};

export type MenuContentTab = {
  id: string;
  label: string;
  html: string;
};

export type BuilderSession = {
  uid: string;
  structureMoves: StructureMove[];
  addedNodeIds: number[];
  stagedDeletes: Array<{ nodeId: number; enabled: boolean; enabledForSite: boolean }>;
  menuContentDraft: Record<string, unknown>;
  changeCount: number;
  hasStructureMoves: boolean;
  hasMenuContentDraft: boolean;
};

export type BuilderSite = {
  id: number;
  handle: string;
  name: string;
};

export type BuilderState = {
  menu: {
    id: number;
    uid: string;
    name: string;
    handle: string;
    instructions: string | null;
    instructionsHtml: string | null;
    maxLevels: number | null;
    structureId: number;
    showSiteMenu: boolean;
    propagationMethod: string;
  };
  site: { id: number; handle: string; name: string };
  canCopyToSite: boolean;
  copyToSiteTargets: BuilderSite[];
  stagingEnabled: boolean;
  session: BuilderSession | null;
  nodes: BuilderNode[];
  parentOptions: Array<{ label: string; value: string }>;
  builderTabs: BuilderTab[];
  menuContent: { hasFields: boolean };
  permissions: { canManage: boolean; canEditSettings: boolean };
  settingsUrl: string;
  elementType: string;
};

declare global {
  interface Window {
    NavigationBuilderConfig: {
      menuId: number;
      siteId: number;
      csrfTokenName: string;
      csrfTokenValue: string;
    };
    Garnish: GarnishGlobal;
    $: JQueryStatic;
  }
}

type GarnishGlobal = {
  getPostData: ($form: JQuery) => string;
};

type JQueryStatic = (selector: string | Element | unknown) => JQuery;
type JQuery = {
  data: (key: string, value?: unknown) => unknown;
  find: (selector: string) => JQuery;
  on: (events: string, handler: (e: Event) => void) => JQuery;
  off: (events: string) => JQuery;
  length: number;
  [index: number]: Element;
};

export {};
