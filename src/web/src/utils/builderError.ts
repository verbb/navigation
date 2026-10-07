type ErrorRecord = Record<string, unknown>;

function asRecord(value: unknown): ErrorRecord | null {
  return typeof value === 'object' && value !== null ? value as ErrorRecord : null;
}

function scalar(value: unknown): string | null {
  if (typeof value === 'string' && value.trim()) {
    return value.trim();
  }

  if (typeof value === 'number') {
    return String(value);
  }

  return null;
}

function traceLine(value: unknown): string | null {
  if (typeof value === 'string') {
    return value;
  }

  const item = asRecord(value);

  if (!item) {
    return null;
  }

  const file = scalar(item.file);
  const line = scalar(item.line);
  const callable = scalar(item.function) ?? scalar(item.method);
  const location = file ? `${file}${line ? `:${line}` : ''}` : null;

  return [location, callable].filter(Boolean).join(' — ') || null;
}

/**
 * Builds a plain-text support report without serializing request headers or
 * other Axios configuration that can contain CSRF and authorization values.
 */
export function formatBuilderError(error: unknown): string {
  const source = asRecord(error);
  const response = asRecord(source?.response);
  const data = asRecord(response?.data);
  const lines: string[] = [];
  const status = scalar(response?.status);
  const statusText = scalar(response?.statusText);

  if (status || statusText) {
    lines.push(`HTTP ${[status, statusText].filter(Boolean).join(' ')}`);
  }

  const responseText = typeof response?.data === 'string' ? response.data.trim() : null;
  const heading = scalar(data?.name) ?? scalar(data?.type);
  const message = scalar(data?.message) ?? scalar(data?.error) ?? scalar(source?.message);

  if (heading && heading !== message) {
    lines.push(heading);
  }

  if (message) {
    lines.push(message);
  } else if (responseText) {
    lines.push(responseText);
  }

  const file = scalar(data?.file);
  const line = scalar(data?.line);

  if (file) {
    lines.push(`${file}${line ? `:${line}` : ''}`);
  }

  const trace = Array.isArray(data?.trace)
    ? data.trace.map(traceLine).filter((item): item is string => Boolean(item))
    : [];

  if (trace.length) {
    lines.push('', 'Trace:', ...trace);
  } else {
    const stack = scalar(source?.stack);

    if (stack && stack !== message) {
      lines.push('', stack);
    }
  }

  if (!lines.length) {
    lines.push(typeof error === 'string' ? error : String(error));
  }

  return lines.join('\n');
}
