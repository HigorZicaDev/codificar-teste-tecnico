<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { Owner, Ticket } from '@/types';
import { toDatetimeLocal } from '@/lib/utils';
import * as tickets from '@/routes/tickets';

const props = defineProps<{
    ticket?: Ticket;
    owners: Owner[];
}>();

const isEdit = !!props.ticket;
const autoAssign = ref(!isEdit && !props.ticket?.owner_id);

const form = useForm({
    title: props.ticket?.title ?? '',
    description: props.ticket?.description ?? '',
    priority: props.ticket?.priority ?? 'medium',
    status: props.ticket?.status ?? 'open',
    date_start: toDatetimeLocal(props.ticket?.date_start) || toDatetimeLocal(new Date().toISOString()),
    owner_id: props.ticket?.owner_id ?? (null as number | null),
});

function submit() {
    const data = { ...form.data() };

    if (autoAssign.value) {
        data.owner_id = null;
    }

    if (isEdit && props.ticket) {
        form.transform(() => data).put(tickets.update(props.ticket!).url);
    } else {
        form.transform(() => data).post(tickets.store().url);
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Título</label>
            <input
                v-model="form.title"
                type="text"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                :class="{ 'border-red-400': form.errors.title }"
                placeholder="Título do chamado"
            />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">
                {{ form.errors.title }}
            </p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Descrição</label>
            <textarea
                v-model="form.description"
                rows="4"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                :class="{ 'border-red-400': form.errors.description }"
                placeholder="Descreva o problema ou solicitação"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">
                {{ form.errors.description }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Prioridade</label>
                <select
                    v-model="form.priority"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    :class="{ 'border-red-400': form.errors.priority }"
                >
                    <option value="low">Baixa</option>
                    <option value="medium">Média</option>
                    <option value="high">Alta</option>
                </select>
                <p v-if="form.errors.priority" class="mt-1 text-xs text-red-600">
                    {{ form.errors.priority }}
                </p>
            </div>

            <div v-if="isEdit">
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select
                    v-model="form.status"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    :class="{ 'border-red-400': form.errors.status }"
                >
                    <option value="open">Aberto</option>
                    <option value="in_progress">Em Progresso</option>
                    <option value="resolved">Resolvido</option>
                    <option value="closed">Fechado</option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">
                    {{ form.errors.status }}
                </p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Data de início</label>
                <input
                    v-model="form.date_start"
                    type="datetime-local"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    :class="{ 'border-red-400': form.errors.date_start }"
                />
                <p v-if="form.errors.date_start" class="mt-1 text-xs text-red-600">
                    {{ form.errors.date_start }}
                </p>
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Responsável</label>
            <label class="mb-3 flex cursor-pointer items-center gap-2">
                <input
                    v-model="autoAssign"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-600">
                    Atribuição automática (menor carga de chamados abertos)
                </span>
            </label>
            <select
                v-if="!autoAssign"
                v-model="form.owner_id"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                :class="{ 'border-red-400': form.errors.owner_id }"
            >
                <option :value="null">Selecionar responsável</option>
                <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                    {{ owner.name }}
                </option>
            </select>
            <p v-if="form.errors.owner_id" class="mt-1 text-xs text-red-600">
                {{ form.errors.owner_id }}
            </p>
        </div>

        <div class="flex items-center gap-3 border-t border-gray-100 pt-4">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
            >
                {{ form.processing ? 'Salvando...' : isEdit ? 'Salvar alterações' : 'Criar chamado' }}
            </button>
            <a
                :href="isEdit && ticket ? tickets.show(ticket).url : tickets.index().url"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                Cancelar
            </a>
        </div>
    </form>
</template>
