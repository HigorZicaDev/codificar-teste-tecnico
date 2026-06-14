<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import * as tickets from '@/routes/tickets';
import type { Owner, TicketFilters } from '@/types';

const props = defineProps<{
    filters: TicketFilters;
    owners: Owner[];
}>();

const local = ref<TicketFilters>({ ...props.filters });
let searchTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    const query: Record<string, string> = {};

    if (local.value.search) {
query.search = local.value.search;
}

    if (local.value.status) {
query.status = local.value.status;
}

    if (local.value.priority) {
query.priority = local.value.priority;
}

    if (local.value.owner_id) {
query.owner_id = String(local.value.owner_id);
}

    router.get(tickets.index().url, query, { preserveState: true, replace: true });
}

function clearFilters() {
    local.value = {};
    router.get(tickets.index().url, {}, { preserveState: false });
}

watch(
    () => local.value.search,
    () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 400);
    },
);

watch(
    () => [local.value.status, local.value.priority, local.value.owner_id],
    applyFilters,
);

const hasFilters = () =>
    !!(
        local.value.search ||
        local.value.status ||
        local.value.priority ||
        local.value.owner_id
    );
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <input
            v-model="local.search"
            type="text"
            placeholder="Buscar por título ou descrição..."
            class="h-9 w-64 rounded-md border border-gray-300 px-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        />

        <select
            v-model="local.status"
            class="h-9 rounded-md border border-gray-300 px-3 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        >
            <option value="">Todos os status</option>
            <option value="open">Aberto</option>
            <option value="in_progress">Em Andamento</option>
            <option value="resolved">Resolvido</option>
            <option value="closed">Fechado</option>
        </select>

        <select
            v-model="local.priority"
            class="h-9 rounded-md border border-gray-300 px-3 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        >
            <option value="">Todas as prioridades</option>
            <option value="high">Alta</option>
            <option value="medium">Média</option>
            <option value="low">Baixa</option>
        </select>

        <select
            v-model="local.owner_id"
            class="h-9 rounded-md border border-gray-300 px-3 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        >
            <option value="">Todos os responsáveis</option>
            <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                {{ owner.name }}
            </option>
        </select>

        <button
            v-if="hasFilters()"
            type="button"
            class="h-9 px-3 text-sm text-gray-500 hover:text-gray-700"
            @click="clearFilters"
        >
            Limpar filtros
        </button>
    </div>
</template>
