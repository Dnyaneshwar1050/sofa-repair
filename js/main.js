async function postForm(form, endpoint) {
  const btn = form.querySelector('button[type="submit"]');
  const originalText = btn ? btn.textContent : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Submitting...'; }

  try {
    const formData = new FormData(form);
    const res = await fetch(endpoint, { method: 'POST', body: formData });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Request failed');
    return data;
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = originalText; }
  }
}

document.addEventListener('submit', async (e) => {
  const form = e.target;
  if (!(form instanceof HTMLFormElement)) return;

  const endpoint = form.getAttribute('data-endpoint');
  if (!endpoint) return;

  e.preventDefault();
  const notice = form.querySelector('[data-notice]');
  if (notice) { notice.className = 'notice'; notice.textContent = ''; }

  try {
    const data = await postForm(form, endpoint);
    if (notice) { notice.className = 'notice ok'; notice.textContent = data.message || 'Saved.'; }
    form.reset();
  } catch (err) {
    if (notice) { notice.className = 'notice err'; notice.textContent = err.message || 'Error.'; }
  }
});

