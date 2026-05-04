import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useLocaleStore = defineStore('locale', () => {
    const locale = ref(localStorage.getItem('locale') || 'ru')

    function setLocale(lang) {
        locale.value = lang
    }

    watch(locale, (value) => {
        localStorage.setItem('locale', value)
    })

    return { locale, setLocale }
})
