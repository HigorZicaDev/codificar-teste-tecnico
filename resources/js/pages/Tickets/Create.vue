<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TicketForm from '@/components/TicketForm.vue';
import * as tickets from '@/routes/tickets';
import type { Owner } from '@/types';

defineOptions({ layout: AppLayout });

defineProps<{
    owners: Owner[];
}>();
</script>

<template>
    <Head title="Novo Chamado" />

    <div class="space-y-6">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <Link :href="tickets.index().url" class="hover:text-gray-700">Chamados</Link>
            <span>/</span>
            <span class="text-gray-900">Novo chamado</span>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h1 class="mb-6 text-lg font-semibold text-gray-900">Novo chamado</h1>
            <TicketForm :owners="owners" />
        </div>
    </div>
</template>
