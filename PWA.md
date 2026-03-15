# PWA configuration (vite-plugin-pwa)

This project uses **vite-plugin-pwa** with Workbox for:

- **Offline caching** – JS, CSS, HTML, and static assets are precached. API responses use a NetworkFirst cache (`/api/*`).
- **Service worker** – Registered automatically on load via `virtual:pwa-register` in `resources/js/app.js`. Uses `registerType: 'autoUpdate'` so new versions apply on next load.
- **Installable manifest** – `public/build/manifest.webmanifest` is linked from the app blade; `theme-color` and meta description are set for “Add to home screen”.

## Icons (required for installability)

Add these files under `public/icons/`:

- `icon-192x192.png` (192×192)
- `icon-512x512.png` (512×512)

See `public/icons/README.md`. Without them, the app may still work offline but some browsers won’t offer “Install app”.

## Build

- **Production:** `npm run build` generates:
  - `public/build/manifest.webmanifest`
  - `public/build/sw.js` and Workbox runtime
  - Precached assets (JS, CSS, etc.)

## Optional: prompt for updates

To ask the user before reloading when a new version is available, in `vite.config.js` set:

```js
registerType: 'prompt',
```

Then in `resources/js/app.js` use the callback:

```js
registerSW({
  immediate: true,
  onNeedRefresh() {
    if (confirm('New content available. Reload?')) {
      updateSW();
    }
  },
});
```

You’ll need to capture the return value of `registerSW()` to get `updateSW`.
