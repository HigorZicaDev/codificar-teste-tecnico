<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import TicketPriorityBadge from '@/components/TicketPriorityBadge.vue';
import TicketStatusBadge from '@/components/TicketStatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDatetime } from '@/lib/utils';
import * as tickets from '@/routes/tickets';
import * as owner from '@/routes/tickets/owner';
import type { Ticket } from '@/types';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    ticket: Ticket;
}>();

const autoAssignForm = useForm({});

function autoAssign() {
    autoAssignForm.put(owner.autoAssign(props.ticket).url);
}
</script>

<template>
    <Head :title="`Chamado #${ticket.id}`" />

    <div class="space-y-6">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <Link :href="tickets.index().url" class="hover:text-gray-700">Chamados</Link>
            <span>/</span>
            <span class="text-gray-900">#{{ String(ticket.id).padStart(4, '0') }}</span>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
                <div class="space-y-2">
                    <h1 class="text-xl font-semibold text-gray-900">{{ ticket.title }}</h1>
                    <div class="flex items-center gap-3">
                        <TicketStatusBadge :status="ticket.status" />
                        <TicketPriorityBadge :priority="ticket.priority" />
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        :disabled="autoAssignForm.processing"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-60"
                        @click="autoAssign"
                    >
                        {{ autoAssignForm.processing ? 'Atribuindo...' : 'Atribuição automática' }}
                    </button>
                    <Link
                        :href="tickets.edit(ticket).url"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Editar
                    </Link>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="mb-1 text-xs font-medium tracking-wider text-gray-400 uppercase">
                            Responsável
                        </dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ ticket.owner?.name ?? '—' }}
                        </dd>
                        <dd class="text-xs text-gray-400">{{ ticket.owner?.email ?? '' }}</dd>
                    </div>

                    <div>
                        <dt class="mb-1 text-xs font-medium tracking-wider text-gray-400 uppercase">
                            Aberto em
                        </dt>
                        <dd class="text-sm text-gray-700">
                            {{ formatDatetime(ticket.opened_at) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="mb-1 text-xs font-medium tracking-wider text-gray-400 uppercase">
                            Fechado em
                        </dt>
                        <dd class="text-sm text-gray-700">
                            {{ ticket.closed_at ? formatDatetime(ticket.closed_at) : '—' }}
                        </dd>
                    </div>

                </div>

                <div class="mt-6">
                    <dt class="mb-2 text-xs font-medium tracking-wider text-gray-400 uppercase">
                        Descrição
                    </dt>
                    <dd class="rounded-md bg-gray-50 p-4 text-sm leading-relaxed text-gray-700">
                        {{ ticket.description }}
                    </dd>
                </div>
            </div>
        </div>
    </div>
</template>
