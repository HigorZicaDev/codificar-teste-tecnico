<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import * as tickets from '@/routes/tickets';

const page = usePage();
const flash = computed(() => page.props.flash as { success: string | null; error: string | null });

const flashMessage = ref<{ type: 'success' | 'error'; text: string } | null>(null);

watch(
    flash,
    (value) => {
        if (value.success) {
            flashMessage.value = { type: 'success', text: value.success };
            setTimeout(() => (flashMessage.value = null), 4000);
        } else if (value.error) {
            flashMessage.value = { type: 'error', text: value.error };
            setTimeout(() => (flashMessage.value = null), 4000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <Link :href="tickets.index().url" class="text-lg font-semibold text-gray-900">
                    Codificar Sistemas
                </Link>
                <nav class="flex items-center gap-6">
                    <Link
                        :href="tickets.index().url"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900"
                    >
                        Listagem de Tickets
                    </Link>
                </nav>
            </div>
        </header>

        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="flashMessage"
                class="fixed top-4 right-4 z-50 max-w-sm rounded-lg px-4 py-3 text-sm font-medium shadow-lg"
                :class="{
                    'bg-green-50 text-green-800 border border-green-200': flashMessage.type === 'success',
                    'bg-red-50 text-red-800 border border-red-200': flashMessage.type === 'error',
                }"
            >
                {{ flashMessage.text }}
            </div>
        </transition>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <slot />
        </main>
    </div>
</template>
