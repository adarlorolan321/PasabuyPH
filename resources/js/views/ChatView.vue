<template>
    <div class="flex flex-col flex-1 min-h-0 bg-slate-50 dark:bg-slate-900">
        <!-- Chat layout: sidebar + main -->
        <div class="flex flex-1 min-h-0">
            <!-- Conversation list (sidebar) -->
            <aside
                class="w-full md:w-80 lg:w-96 flex-shrink-0 border-r border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex flex-col"
                :class="{ 'hidden md:flex': selectedConversation && isMobile }"
            >
                <div class="p-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Messages</h2>
                    <button
                        type="button"
                        class="text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:underline"
                        @click="showNewChat = true"
                    >
                        New chat
                    </button>
                </div>
                <!-- New chat: get or create conversation by user ID -->
                <div v-if="showNewChat" class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Start a conversation with another user by ID:</p>
                    <form class="flex gap-2" @submit.prevent="startNewChat">
                        <input
                            v-model="newChatUserId"
                            type="number"
                            min="1"
                            placeholder="User ID"
                            class="flex-1 min-w-0 px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900"
                        />
                        <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600">Start</button>
                        <button type="button" class="px-3 py-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 text-sm" @click="showNewChat = false; newChatUserId = ''">Cancel</button>
                    </form>
                    <p v-if="newChatError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ newChatError }}</p>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <template v-if="loadingConversations">
                        <div class="p-4 text-center text-slate-500 dark:text-slate-400 text-sm">Loading…</div>
                    </template>
                    <template v-else-if="!conversations.length">
                        <div class="p-4 text-center text-slate-500 dark:text-slate-400 text-sm">No conversations yet. Start one from a trip or profile.</div>
                    </template>
                    <button
                        v-for="conv in conversations"
                        :key="conv.id"
                        type="button"
                        class="w-full flex items-center gap-3 px-4 py-3 text-left border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition min-h-[44px]"
                        :class="{ 'bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-l-emerald-500': selectedConversation?.id === conv.id }"
                        @click="selectConversation(conv)"
                    >
                        <div class="flex h-10 w-10 shrink-0 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 items-center justify-center text-sm font-semibold">
                            {{ (conv.other_user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-800 dark:text-slate-100 truncate">{{ conv.other_user?.name || conv.other_user?.email }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ conv.last_message?.body || 'No messages yet' }}</p>
                        </div>
                    </button>
                </div>
            </aside>

            <!-- Message panel -->
            <main
                class="flex-1 flex flex-col min-w-0 bg-white dark:bg-slate-800"
                :class="{ 'hidden md:flex': !selectedConversation && isMobile }"
            >
                <template v-if="selectedConversation">
                    <!-- Header -->
                    <header class="flex items-center gap-3 px-4 py-3 border-b border-slate-200 dark:border-slate-700 shrink-0">
                        <button
                            type="button"
                            class="md:hidden p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 min-h-[44px] min-w-[44px] flex items-center justify-center"
                            aria-label="Back to conversations"
                            @click="selectedConversation = null"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <div class="flex h-9 w-9 shrink-0 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 items-center justify-center text-sm font-semibold">
                            {{ (selectedConversation.other_user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ selectedConversation.other_user?.name || selectedConversation.other_user?.email }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Chat</p>
                        </div>
                    </header>

                    <!-- Messages (oldest at top, newest at bottom) -->
                    <div
                        ref="messagesEndRef"
                        class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-3 flex flex-col"
                    >
                        <div v-if="loadingMessages" class="flex justify-center py-4">
                            <span class="text-sm text-slate-500 dark:text-slate-400">Loading messages…</span>
                        </div>
                        <template v-else>
                            <div
                                v-for="msg in sortedMessages"
                                :key="msg.id"
                                class="flex"
                                :class="isOwnMessage(msg) ? 'justify-end' : 'justify-start'"
                            >
                                <div
                                    class="max-w-[85%] sm:max-w-[75%] rounded-2xl px-4 py-2.5 text-sm shadow-sm"
                                    :class="isOwnMessage(msg)
                                        ? 'bg-emerald-500 text-white rounded-br-md'
                                        : 'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-slate-100 rounded-bl-md'"
                                >
                                    <p class="whitespace-pre-wrap break-words">{{ msg.body }}</p>
                                    <p
                                        class="text-xs mt-1"
                                        :class="isOwnMessage(msg) ? 'text-emerald-100' : 'text-slate-500 dark:text-slate-400'"
                                    >
                                        {{ formatTime(msg.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Send form -->
                    <form
                        class="p-3 border-t border-slate-200 dark:border-slate-700 shrink-0 safe-area-bottom"
                        @submit.prevent="sendMessage"
                    >
                        <div class="flex gap-2">
                            <input
                                v-model="messageDraft"
                                type="text"
                                placeholder="Type a message…"
                                class="flex-1 min-w-0 px-4 py-3 text-base rounded-2xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                :disabled="sending"
                                maxlength="10000"
                                autocomplete="off"
                            />
                            <button
                                type="submit"
                                class="shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-500 text-white hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                :disabled="sending || !messageDraft.trim()"
                                aria-label="Send message"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 2 9 18z" /></svg>
                            </button>
                        </div>
                    </form>
                </template>

                <!-- Empty state when no conversation selected -->
                <div
                    v-else
                    class="flex flex-1 items-center justify-center p-4 text-slate-500 dark:text-slate-400 text-center"
                >
                    <p class="hidden md:block">Select a conversation or start a new chat.</p>
                    <p class="md:hidden">Select a conversation from the list.</p>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useChatApi } from '@/composables/useChatApi';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const {
    getConversations,
    getOrCreateConversation,
    getMessages,
    sendMessage: sendMessageApi,
} = useChatApi();

const conversations = ref([]);
const loadingConversations = ref(false);
const selectedConversation = ref(null);
const messages = ref([]);
const loadingMessages = ref(false);
const messageDraft = ref('');
const sending = ref(false);
const messagesEndRef = ref(null);
const pollTimer = ref(null);
const isMobile = ref(typeof window !== 'undefined' && window.innerWidth < 768);
const showNewChat = ref(false);
const newChatUserId = ref('');
const newChatError = ref('');

const sortedMessages = computed(() => [...messages.value].sort((a, b) => new Date(a.created_at) - new Date(b.created_at)));

function isOwnMessage(msg) {
    return msg.sender?.id === authStore.user?.id;
}

function formatTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const now = new Date();
    const sameDay = d.toDateString() === now.toDateString();
    if (sameDay) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    return d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

async function loadConversations() {
    loadingConversations.value = true;
    try {
        conversations.value = await getConversations();
    } catch (e) {
        console.error('Failed to load conversations', e);
        conversations.value = [];
    } finally {
        loadingConversations.value = false;
    }
}

async function startNewChat() {
    const id = parseInt(newChatUserId.value, 10);
    if (!id || id === authStore.user?.id) {
        newChatError.value = 'Enter a valid user ID (not yourself).';
        return;
    }
    newChatError.value = '';
    try {
        const conv = await getOrCreateConversation(id);
        const other = conv.user_one.id === authStore.user?.id ? conv.user_two : conv.user_one;
        const item = {
            id: conv.id,
            other_user: { id: other.id, name: other.name, email: other.email },
            last_message: null,
            updated_at: conv.created_at,
        };
        const idx = conversations.value.findIndex((c) => c.id === conv.id);
        if (idx >= 0) conversations.value[idx] = item;
        else conversations.value = [item, ...conversations.value];
        showNewChat.value = false;
        newChatUserId.value = '';
        selectConversation(item);
    } catch (e) {
        newChatError.value = e.response?.data?.message || 'Could not start conversation.';
    }
}

function selectConversation(conv) {
    selectedConversation.value = conv;
    messages.value = [];
    messageDraft.value = '';
    if (route.params.conversationId !== String(conv.id)) {
        router.replace({ name: 'chat', params: { conversationId: String(conv.id) } });
    }
    loadMessages(conv.id);
}

async function loadMessages(conversationId, merge = false) {
    if (!conversationId) return;
    if (!merge) loadingMessages.value = true;
    try {
        const { messages: list, meta } = await getMessages(conversationId, 1, 50);
        // API returns newest first; we store and display oldest first
        const ordered = [...list].reverse();
        if (merge) {
            const ids = new Set(messages.value.map((m) => m.id));
            const newOnes = ordered.filter((m) => !ids.has(m.id));
            if (newOnes.length) {
                const combined = [...messages.value, ...newOnes];
                messages.value = combined.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            }
        } else {
            messages.value = ordered;
        }
        scrollToEnd();
    } catch (e) {
        console.error('Failed to load messages', e);
        if (!merge) messages.value = [];
    } finally {
        loadingMessages.value = false;
    }
}

function scrollToEnd() {
    nextTick(() => {
        if (messagesEndRef.value) messagesEndRef.value.scrollTop = messagesEndRef.value.scrollHeight;
    });
}

async function sendMessage() {
    const body = messageDraft.value.trim();
    if (!body || !selectedConversation.value) return;
    sending.value = true;
    try {
        const sent = await sendMessageApi(selectedConversation.value.id, body);
        messageDraft.value = '';
        messages.value = [...messages.value, sent];
        scrollToEnd();
    } catch (e) {
        console.error('Failed to send message', e);
    } finally {
        sending.value = false;
    }
}

function startPolling() {
    if (pollTimer.value) return;
    pollTimer.value = setInterval(() => {
        if (selectedConversation.value?.id) {
            loadMessages(selectedConversation.value.id, true);
        }
    }, 5000);
}

function stopPolling() {
    if (pollTimer.value) {
        clearInterval(pollTimer.value);
        pollTimer.value = null;
    }
}

function onResize() {
    isMobile.value = window.innerWidth < 768;
}

onMounted(async () => {
    await loadConversations();
    const convId = route.params.conversationId;
    if (convId) {
        const conv = conversations.value.find((c) => String(c.id) === convId);
        if (conv) selectConversation(conv);
    }
    startPolling();
    window.addEventListener('resize', onResize);
});

onUnmounted(() => {
    stopPolling();
    window.removeEventListener('resize', onResize);
});

watch(
    () => route.params.conversationId,
    (id) => {
        if (!id) return;
        const conv = conversations.value.find((c) => String(c.id) === id);
        if (conv && conv.id !== selectedConversation.value?.id) selectConversation(conv);
    }
);
</script>
