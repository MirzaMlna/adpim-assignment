const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? null;

if (csrfToken) {
    window.csrfToken = csrfToken;
}
