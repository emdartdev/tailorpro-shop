import { ref, watchEffect } from 'vue';

const currentTheme = ref(localStorage.getItem('app-theme') || 'light');

watchEffect(() => {
  localStorage.setItem('app-theme', currentTheme.value);
  if (typeof document !== 'undefined') {
    if (currentTheme.value === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }
});

export function useTheme() {
  const toggleTheme = () => {
    currentTheme.value = currentTheme.value === 'dark' ? 'light' : 'dark';
  };

  const setTheme = (theme) => {
    currentTheme.value = theme;
  };

  return {
    theme: currentTheme,
    toggleTheme,
    setTheme,
  };
}

export function initTheme() {
  const saved = localStorage.getItem('app-theme') || 'light';
  currentTheme.value = saved;
}
