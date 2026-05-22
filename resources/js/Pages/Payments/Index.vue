<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()

const props = defineProps({
    payments: { type: Object, default: null },
})

const hasData = computed(() => props.payments?.data?.length > 0)

const stats = [
    {
        label: t('payments.stats.total_paid'),
        icon: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        label: t('payments.stats.pending_payouts'),
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        bg: 'bg-amber-50 dark:bg-amber-500/10',
        color: 'text-amber-600 dark:text-amber-400',
    },
    {
        label: t('payments.stats.masters_with_balance'),
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
        color: 'text-indigo-600 dark:text-indigo-400',
    },
]

const features = [
    {
        title: t('payments.planned.history'),
        description: t('payments.planned.history_desc'),
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
        color: 'text-indigo-600 dark:text-indigo-400',
    },
    {
        title: t('payments.planned.models'),
        description: t('payments.planned.models_desc'),
        icon: 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        title: t('payments.planned.payouts'),
        description: t('payments.planned.payouts_desc'),
        icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        bg: 'bg-violet-50 dark:bg-violet-500/10',
        color: 'text-violet-600 dark:text-violet-400',
    },
    {
        title: t('payments.planned.auto_credit'),
        description: t('payments.planned.auto_credit_desc'),
        icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        bg: 'bg-sky-50 dark:bg-sky-500/10',
        color: 'text-sky-600 dark:text-sky-400',
    },
]
</script>

<template>
    <Head :title="t('payments.title')" />

    <AdminLayout :title="t('payments.title')">
        <div class="space-y-6">

            <!-- Page header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ t('payments.title') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                        {{ t('payments.subtitle') }}
                    </p>
                </div>
                <span
                    v-if="!hasData"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    {{ t('payments.in_development') }}
                </span>
            </div>

            <!-- ─── Has data: table ─── -->
            <template v-if="hasData">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700/60">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('payments.fields.master') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('payments.fields.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('payments.fields.model') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('payments.fields.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            <tr
                                v-for="(payment, i) in payments.data"
                                :key="payment.id"
                                class="hover:bg-gray-50 dark:hover:bg-slate-700/40"
                            >
                                <td class="px-6 py-4 text-sm text-gray-400 dark:text-slate-500">{{ i + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-slate-200">{{ payment.master?.name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ payment.amount }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ payment.model }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ payment.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- ─── No data: description ─── -->
            <template v-else>

                <!-- Stats placeholder -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700/60"
                    >
                        <div :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', stat.bg]">
                            <svg :class="['h-5 w-5', stat.color]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-300 dark:text-slate-600">—</p>
                            <p class="text-sm text-gray-500 dark:text-slate-400">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Roadmap -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700/60">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-slate-700">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300">
                            {{ t('payments.roadmap_title') }}
                        </h2>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-slate-700">
                        <li
                            v-for="f in features"
                            :key="f.title"
                            class="flex items-start gap-4 px-6 py-4"
                        >
                            <div :class="['mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg', f.bg]">
                                <svg :class="['h-5 w-5', f.color]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="f.icon" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ f.title }}</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-slate-400">{{ f.description }}</p>
                            </div>
                            <span class="mt-0.5 shrink-0 inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-slate-700 dark:text-slate-400">
                                {{ t('payments.planned_label') }}
                            </span>
                        </li>
                    </ul>
                </div>

            </template>
        </div>
    </AdminLayout>
</template>
