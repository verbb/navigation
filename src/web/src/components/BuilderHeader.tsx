import { NodeTreeToolbar } from './NodeTreeToolbar';

type Props = {
  showToolbar?: boolean;
};

export function BuilderHeader({ showToolbar = true }: Props) {
  if (!showToolbar) {
    return null;
  }

  return <NodeTreeToolbar />;
}
