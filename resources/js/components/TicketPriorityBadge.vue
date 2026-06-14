<script setup lang="ts">
import type { TicketPriority } from '@/types';

defineProps<{ priority: TicketPriority }>();

const labels: Record<TicketPriority, string> = {
    low: 'Baixa',
    medium: 'Média',
    high: 'Alta',
};

const colors: Record<TicketPriority, string> = {
    high: 'bg-red-100 text-red-700',
    medium: 'bg-amber-100 text-amber-700',
    low: 'bg-green-100 text-green-700',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="colors[priority]"
    >
        {{ labels[priority] }}
    </span>
</template>
