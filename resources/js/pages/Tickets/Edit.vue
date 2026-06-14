<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import TicketForm from '@/components/TicketForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDatetime } from '@/lib/utils';
import * as tickets from '@/routes/tickets';
import * as owner from '@/routes/tickets/owner';
import type { Owner, Ticket } from '@/types';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    ticket: Ticket;
    owners: Owner[];
}>();

const autoAssignForm = useForm({});

function autoAssign() {
    autoAssignForm.put(owner.autoAssign(props.ticket).url);
}
</script>

<template>
    <Head :title="`Editar #${ticket.id}`" />

    <div class="space-y-6">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <Link :href="tickets.index().url" class="hover:text-gray-700">Chamados</Link>
            <span>/</span>
            <Link :href="tickets.show(ticket).url" class="hover:text-gray-700">
                #{{ String(ticket.id).padStart(4, '0') }}
            </Link>
            <span>/</span>
            <span class="text-gray-900">Editar</span>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900">
                    Editar chamado #{{ String(ticket.id).padStart(4, '0') }}
                </h1>
                <span v-if="ticket.opened_at" class="text-xs text-gray-400">
                    Aberto em {{ formatDatetime(ticket.opened_at) }}
                </span>
                <button
                    type="button"
                    :disabled="autoAssignForm.processing"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-60"
                    @click="autoAssign"
                >
                    {{ autoAssignForm.processing ? 'Atribuindo...' : 'Atribuição automática' }}
                </button>
            </div>
            <TicketForm :ticket="ticket" :owners="owners" />
        </div>
    </div>
</template>
