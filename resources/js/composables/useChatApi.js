import api from '@/api/axios';

/**
 * Chat API composable for conversations and messages.
 */
export function useChatApi() {
    async function getConversations() {
        const { data } = await api.get('/conversations');
        return data.data;
    }

    async function getOrCreateConversation(otherUserId) {
        const { data } = await api.post('/conversations', { user_id: otherUserId });
        return data.data;
    }

    async function getConversation(conversationId) {
        const { data } = await api.get(`/conversations/${conversationId}`);
        return data.data;
    }

    async function getMessages(conversationId, page = 1, perPage = 50) {
        const { data } = await api.get(`/conversations/${conversationId}/messages`, {
            params: { page, per_page: perPage },
        });
        return { messages: data.data, meta: data.meta };
    }

    async function sendMessage(conversationId, body) {
        const { data } = await api.post(`/conversations/${conversationId}/messages`, { body });
        return data.data;
    }

    return {
        getConversations,
        getOrCreateConversation,
        getConversation,
        getMessages,
        sendMessage,
    };
}
