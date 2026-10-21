import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { initAuth } from './lib/auth';
import { initTheme } from './lib/theme';
import { initLanguage } from './lib/language';
import { usePwa } from './lib/pwa';

// Initialize Theme, Auth, Language, PWA
initTheme();
initLanguage();
const { initPwa } = usePwa();
initPwa();

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

initAuth().finally(() => {
  app.mount('#app');

  if ('serviceWorker' in navigator && !window.location.host.includes('localhost:5173')) {
    const registerSw = () => {
      navigator.serviceWorker.register('/sw.js').catch((err) => {
        console.warn('Service Worker registration skipped or failed:', err);
      });
    };
    if (document.readyState === 'complete') {
      registerSw();
    } else {
      window.addEventListener('load', registerSw);
    }
  }
});
