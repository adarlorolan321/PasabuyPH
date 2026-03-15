import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { registerSW } from 'virtual:pwa-register';
import App from './App.vue';
import router from './router';

registerSW({ immediate: true });

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
window.__ROLAN_ROUTER__ = router;
app.mount('#app');
