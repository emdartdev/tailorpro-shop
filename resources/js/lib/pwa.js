import { ref, computed } from 'vue';

const deferredPrompt = ref(null);
const isStandalone = ref(false);
const isIos = ref(false);
const isAndroid = ref(false);
const isMobile = ref(false);
const showInstallModal = ref(false);
const activePlatform = ref('android');
const isInitialized = ref(false);

export function usePwa() {
  function checkStandalone() {
    if (typeof window === 'undefined') return false;
    return (
      window.matchMedia('(display-mode: standalone)').matches ||
      window.matchMedia('(display-mode: window-controls-overlay)').matches ||
      window.navigator.standalone === true ||
      document.referrer.includes('android-app://') ||
      window.location.search.includes('source=pwa')
    );
  }

  function detectPlatform() {
    if (typeof navigator === 'undefined') return;
    const ua = navigator.userAgent || '';
    isIos.value = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
    isAndroid.value = /Android/i.test(ua);
    isMobile.value = isIos.value || isAndroid.value || /Mobi|Tablet|iPad/i.test(ua);

    if (isIos.value) {
      activePlatform.value = 'ios';
    } else if (isAndroid.value) {
      activePlatform.value = 'android';
    } else {
      activePlatform.value = 'desktop';
    }
  }

  function initPwa() {
    if (isInitialized.value || typeof window === 'undefined') return;
    isInitialized.value = true;

    isStandalone.value = checkStandalone();
    detectPlatform();

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt.value = e;
    });

    window.addEventListener('appinstalled', () => {
      isStandalone.value = true;
      deferredPrompt.value = null;
      showInstallModal.value = false;
    });

    // Check media query changes in case app enters or exits standalone mode
    try {
      const matcher = window.matchMedia('(display-mode: standalone)');
      matcher.addEventListener('change', (e) => {
        isStandalone.value = e.matches;
      });
    } catch (e) {
      // Older browsers
    }
  }

  async function promptInstall() {
    if (deferredPrompt.value) {
      try {
        deferredPrompt.value.prompt();
        const choice = await deferredPrompt.value.userChoice;
        if (choice.outcome === 'accepted') {
          deferredPrompt.value = null;
          isStandalone.value = true;
          showInstallModal.value = false;
          return true;
        }
      } catch (err) {
        console.error('PWA install prompt error:', err);
      }
    }
    // If native prompt is not available or rejected/not supported (e.g. iOS Safari), open the guide modal
    showInstallModal.value = true;
    return false;
  }

  function openInstallModal(platform) {
    if (platform) {
      activePlatform.value = platform;
    } else {
      detectPlatform();
    }
    showInstallModal.value = true;
  }

  function closeInstallModal() {
    showInstallModal.value = false;
  }

  const canPromptNative = computed(() => !!deferredPrompt.value);

  return {
    deferredPrompt,
    isStandalone,
    isIos,
    isAndroid,
    isMobile,
    canPromptNative,
    showInstallModal,
    activePlatform,
    initPwa,
    promptInstall,
    openInstallModal,
    closeInstallModal,
  };
}
