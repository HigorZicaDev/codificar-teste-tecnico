<script setup lang="ts">
import type { TicketStatus } from '@/types';

defineProps<{ status: TicketStatus }>();

const labels: Record<TicketStatus, string> = {
    open: 'Aberto',
    in_progress: 'Em Progresso',
    resolved: 'Resolvido',
    closed: 'Fechado',
};

const colors: Record<TicketStatus, string> = {
    open: 'bg-green-100 text-green-700',
    in_progress: 'bg-blue-100 text-blue-700',
    resolved: 'bg-purple-100 text-purple-700',
    closed: 'bg-gray-100 text-gray-600',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="colors[status]"
    >
        {{ labels[status] }}
    </span>
</template>
