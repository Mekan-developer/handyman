<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()

const props = defineProps({
    clients: { type: Object, default: null },
})

const hasData = computed(() => props.clients?.data?.length > 0)

const stats = [
    {
        label: t('clients.stats.total'),
        icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
        color: 'text-indigo-600 dark:text-indigo-400',
    },
    {
        label: t('clients.stats.active_orders'),
        icon: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z',
        bg: 'bg-violet-50 dark:bg-violet-500/10',
        color: 'text-violet-600 dark:text-violet-400',
    },
    {
        label: t('clients.stats.cities_covered'),
        icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
]

const features = [
    {
        title: t('clients.planned.registration'),
        description: t('clients.planned.registration_desc'),
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
        color: 'text-indigo-600 dark:text-indigo-400',
    },
    {
        title: t('clients.planned.orders'),
        description: t('clients.planned.orders_desc'),
        icon: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z',
        bg: 'bg-violet-50 dark:bg-violet-500/10',
        color: 'text-violet-600 dark:text-violet-400',
    },
    {
        title: t('clients.planned.geo'),
        description: t('clients.planned.geo_desc'),
        icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        title: t('clients.planned.photos'),
        description: t('clients.planned.photos_desc'),
        icon: 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
        bg: 'bg-amber-50 dark:bg-amber-500/10',
        color: 'text-amber-600 dark:text-amber-400',
    },
]
</script>

<template>
    <Head :title="t('clients.title')" />

    <AdminLayout :title="t('clients.title')">
        <div class="space-y-6">

            <!-- Page header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ t('clients.title') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                        {{ t('clients.subtitle') }}
                    </p>
                </div>
                <span
                    v-if="!hasData"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    {{ t('clients.in_development') }}
                </span>
            </div>

            <!-- ─── Has data: table ─── -->
            <template v-if="hasData">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700/60">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('clients.fields.name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('clients.fields.phone') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('clients.fields.city') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('clients.fields.orders_count') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            <tr
                                v-for="(client, i) in clients.data"
                                :key="client.id"
                                class="hover:bg-gray-50 dark:hover:bg-slate-700/40"
                            >
                                <td class="px-6 py-4 text-sm text-gray-400 dark:text-slate-500">{{ i + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-slate-200">{{ client.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ client.phone }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ client.city?.name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ client.orders_count ?? 0 }}</td>
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
                            {{ t('clients.roadmap_title') }}
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
                                {{ t('clients.planned_label') }}
                            </span>
                        </li>
                    </ul>
                </div>

            </template>
        </div>
    </AdminLayout>
</template>
