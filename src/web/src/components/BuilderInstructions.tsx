import { useBuilderStore } from '../store';

export function BuilderInstructions() {
  const instructionsHtml = useBuilderStore((s) => s.state?.menu.instructionsHtml ?? null);

  if (!instructionsHtml) {
    return null;
  }

  return (
    <div
      className="navigation-nodes-instructions text-sm text-gray-600 [&_:is(p,ul,ol):last-child]:mb-0"
      dangerouslySetInnerHTML={{ __html: instructionsHtml }}
    />
  );
}
