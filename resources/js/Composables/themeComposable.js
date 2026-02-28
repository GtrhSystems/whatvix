import { ref } from 'vue'

export function useTheme() {
  const theme = ref(localStorage.getItem('theme') || 'system')
  const systemDarkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches)

  const updateThemeClass = () => {
    if (theme.value === 'dark' || (theme.value === 'system' && systemDarkMode.value)) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  const handleThemeChange = (themeMode) => {
    theme.value = themeMode
    localStorage.setItem('theme', themeMode)
    updateThemeClass()
  }

  return {
    theme,
    handleThemeChange
  }
}
