<script setup lang="ts">
import { Search, X } from "lucide-vue-next";
import { computed, ref } from "vue";
import type { Contact } from "../types";
import { avatarUrl } from "../utils";

const props = defineProps<{
	show: boolean;
	contacts: Contact[];
	isLoading: boolean;
}>();

const emit = defineEmits<{
	(e: "start-chat", contact: Contact): void;
	(e: "close"): void;
}>();

const newChatSearchQuery = ref("");

const filteredContacts = computed(() => {
	if (!newChatSearchQuery.value) return props.contacts;
	const q = newChatSearchQuery.value.toLowerCase();
	return props.contacts.filter((c) => c.name.toLowerCase().includes(q));
});

function handleStartChat(contact: Contact) {
	newChatSearchQuery.value = "";
	emit("start-chat", contact);
}

function handleClose() {
	newChatSearchQuery.value = "";
	emit("close");
}
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/60" @click.self="handleClose">
            <div class="bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col max-h-[80vh] select-none">
                <!-- Header -->
                <div class="px-5 py-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 dark:text-zinc-100">Mulai Obrolan Baru</h3>
                    <button @click="handleClose" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 transition-colors">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Search -->
                <div class="p-4 border-b border-slate-100 dark:border-zinc-800">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 dark:text-zinc-500" />
                        <input
                            v-model="newChatSearchQuery"
                            type="text"
                            placeholder="Cari nama teman..."
                            class="w-full bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl pl-9 pr-3 py-2 text-xs font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 dark:placeholder-zinc-600 outline-none focus:border-indigo-400 dark:focus:border-indigo-500 transition-colors"
                        />
                    </div>
                </div>

                <!-- Contacts list -->
                <div class="flex-1 overflow-y-auto p-2 space-y-0.5" style="scrollbar-width:thin;">
                    <div v-if="isLoading" class="flex justify-center py-10">
                        <div class="w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    <div v-else-if="filteredContacts.length === 0" class="text-center text-xs text-slate-400 dark:text-zinc-500 py-10 font-medium">
                        Tidak ditemukan kontak
                    </div>
                    <button
                        v-else
                        v-for="contact in filteredContacts"
                        :key="contact.id"
                        @click="handleStartChat(contact)"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-zinc-900/50 text-left transition-colors cursor-pointer"
                    >
                        <div class="h-9 w-9 rounded-full border border-slate-200 dark:border-zinc-700 overflow-hidden bg-slate-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <img v-if="contact.foto_path" :src="avatarUrl(contact.foto_path)!" :alt="contact.name" class="w-full h-full object-cover" />
                            <span v-else class="text-xs font-black text-slate-500 dark:text-zinc-400">{{ contact.name.charAt(0) }}</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800 dark:text-zinc-200 truncate">{{ contact.name }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
