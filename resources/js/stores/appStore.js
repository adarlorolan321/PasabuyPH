import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAppStore = defineStore('app', () => {
    const loading = ref(false);
    const flashMessage = ref(null);

    function setLoading(value) {
        loading.value = value;
    }

    function setFlash(message, type = 'info') {
        flashMessage.value = { message, type };
    }

    function clearFlash() {
        flashMessage.value = null;
    }

    return {
        loading,
        flashMessage,
        setLoading,
        setFlash,
        clearFlash,
    };
});
