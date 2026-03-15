# Vue 3 Frontend Structure

This folder contains the Vue 3 SPA built with Vite.

## Stack

- **Vue 3** (Composition API, `<script setup>`)
- **Vue Router 4** – client-side routing
- **Pinia** – state management
- **Axios** – HTTP client for the Laravel API
- **Vite** – build tool (config in project root)
- **Tailwind CSS** – styling (via `resources/css/app.css`)

## Directory structure

```
resources/js/
├── api/
│   └── axios.js       # Axios instance: baseURL /api, Bearer token, 401 handling
├── layouts/
│   └── AppLayout.vue  # Main layout (header, nav, footer)
├── router/
│   └── index.js       # Vue Router config and routes
├── stores/
│   ├── index.js       # Re-exports stores
│   ├── authStore.js   # Auth state (user, token, login/logout)
│   └── appStore.js    # App-level state (loading, flash messages)
├── views/
│   ├── HomeView.vue
│   └── AboutView.vue
├── App.vue            # Root component
├── app.js             # Entry: createApp, Pinia, Router, mount
└── bootstrap.js       # Global axios defaults
```

## Usage

- **Run dev server:** `npm run dev` (with `php artisan serve` for Laravel).
- **Build for production:** `npm run build`.
- **API calls:** Use the configured client: `import api from '@/api/axios';` then `api.get('/auth/user')`, etc. The token from `authStore` is sent via the request interceptor.

## Adding routes

Edit `router/index.js`: add entries to the `routes` array and create the corresponding component in `views/`.

## Adding Pinia stores

Create a new file in `stores/` using `defineStore`, then re-export from `stores/index.js` if desired.
