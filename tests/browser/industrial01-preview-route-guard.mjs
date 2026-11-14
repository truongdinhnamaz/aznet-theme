// The reviewed public URL manifest remains authoritative. A WPVibe preview
// token is injected only at runtime and must never enter repo evidence.
const tokenPattern = /^[A-Za-z0-9_-]{12,128}$/;
const normalizedPath = (url) => url.pathname.replace(/\/+$/, '') || '/';

export function previewTokenFromEnv(value = '') {
  if (typeof value !== 'string' || (value !== '' && !tokenPattern.test(value))) {
    throw Error('Invalid preview token format');
  }
  return value;
}

export function requestedAuditUrl(publicUrl, token = '') {
  previewTokenFromEnv(token);
  const url = new URL(publicUrl);
  if (url.username || url.password || url.search || url.hash) {
    throw Error('Expected a credential-free, query-free public route');
  }
  if (token) {
    if (url.protocol !== 'https:') throw Error('Preview requires HTTPS');
    url.searchParams.set('wpvibe_preview', token);
  }
  return url.href;
}

export function samePreviewRoute(requestedUrl, finalUrl, token = '') {
  if (!token || !tokenPattern.test(token)) return false;
  const requested = new URL(requestedUrl);
  const actual = new URL(finalUrl);
  const expectedQuery = (url) => url.searchParams.size === 1 &&
    url.searchParams.get('wpvibe_preview') === token;
  return requested.protocol === 'https:' &&
    requested.origin === actual.origin &&
    normalizedPath(requested) === normalizedPath(actual) &&
    !requested.hash && !actual.hash &&
    expectedQuery(requested) && expectedQuery(actual);
}

export function auditedBodyClass(token = '') {
  previewTokenFromEnv(token);
  return token ? 'wp-theme-aznet-theme-wpvibe-draft' : 'wp-theme-aznet-theme';
}

export function redactReportSecrets(record, token = '') {
  previewTokenFromEnv(token);
  const json = JSON.stringify(record, null, 2) + '\n';
  return token ? json.split(token).join('[REDACTED_PREVIEW_TOKEN]') : json;
}
