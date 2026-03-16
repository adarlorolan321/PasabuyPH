import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/api/axios';

export const useFeedStore = defineStore('feed', () => {
    const items = ref([]);
    const loading = ref(false);
    const error = ref('');
    const nextPageUrl = ref('/feed'); // relative to /api (axios baseURL)
    const hasMore = ref(true);

    async function loadNextPage() {
        if (loading.value || !hasMore.value || !nextPageUrl.value) return;
        loading.value = true;
        error.value = '';

        try {
            const { data } = await api.get(nextPageUrl.value);
            const newItems = data.data || [];
            items.value.push(...newItems);

            // Laravel resource pagination: links.next contains absolute URL
            const links = data.links || {};
            const next = links.next ?? null;
            if (next) {
                const url = new URL(next);
                // strip /api prefix because axios baseURL is /api
                const path = url.pathname.replace(/^\/api/, '');
                nextPageUrl.value = path + url.search;
                hasMore.value = true;
            } else {
                nextPageUrl.value = null;
                hasMore.value = false;
            }
        } catch (e) {
            error.value = e.response?.data?.message || 'Could not load feed.';
        } finally {
            loading.value = false;
        }
    }

    function reset() {
        items.value = [];
        error.value = '';
        nextPageUrl.value = '/feed';
        hasMore.value = true;
    }

    return {
        items,
        loading,
        error,
        hasMore,
        loadNextPage,
        reset,
    };
});

