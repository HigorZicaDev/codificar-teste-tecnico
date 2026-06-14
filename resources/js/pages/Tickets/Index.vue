<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TicketFilters from '@/components/TicketFilters.vue';
import TicketPriorityBadge from '@/components/TicketPriorityBadge.vue';
import TicketStatusBadge from '@/components/TicketStatusBadge.vue';
import { formatDatetime } from '@/lib/utils';
import * as ticketRoutes from '@/routes/tickets';
import type { Owner, PaginatedTickets, TicketFilters as Filters } from '@/types';

defineOptions({ layout: AppLayout });

defineProps<{
    tickets: PaginatedTickets;
    filters: Filters;
    owners: Owner[];
}>();
</script>

<template>
    <Head title="Chamados" />

    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Tickets</h1>
                <p class="mt-1 text-sm text-gray-500">Gerência de chamados internos da equipe</p>
            </div>
            <Link
                :href="ticketRoutes.create().url"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                + Novo Ticket
            </Link>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-6 py-4">
                <TicketFilters :filters="filters" :owners="owners" />
            </div>

            <div v-if="tickets.data.length === 0" class="py-20 text-center">
                <p class="text-sm text-gray-400">Nenhum chamado encontrado.</p>
                <Link
                    :href="ticketRoutes.create().url"
                    class="mt-3 inline-block text-sm font-medium text-blue-600 hover:text-blue-700"
                >
                    Criar o primeiro chamado
                </Link>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                ID
                            </th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Título
                            </th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Prioridade
                            </th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Status
                            </th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Responsável
                            </th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Data Início
                            </th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">
                                #{{ String(ticket.id).padStart(4, '0') }}
                            </td>
                            <td class="max-w-xs px-6 py-4">
                                <p class="truncate font-medium text-gray-900">{{ ticket.title }}</p>
                                <p class="mt-0.5 truncate text-xs text-gray-400">
                                    {{ ticket.description }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <TicketPriorityBadge :priority="ticket.priority" />
                            </td>
                            <td class="px-6 py-4">
                                <TicketStatusBadge :status="ticket.status" />
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ ticket.owner?.name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ formatDatetime(ticket.date_start) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="ticketRoutes.show(ticket).url"
                                        class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                        title="Visualizar"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="ticketRoutes.edit(ticket).url"
                                        class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                        title="Editar"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="tickets.data.length > 0"
                class="flex items-center justify-between border-t border-gray-200 px-6 py-4"
            >
                <p class="text-sm text-gray-500">
                    Mostrando {{ tickets.from }} a {{ tickets.to }} de {{ tickets.total }} resultados
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in tickets.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded px-3 py-1 text-sm"
                            :class="
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'text-gray-500 hover:bg-gray-100'
                            "
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded px-3 py-1 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
