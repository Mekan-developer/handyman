<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import OrderStatusBadge from '@/Pages/Orders/Partials/OrderStatusBadge.vue'
import CreateOrderModal from '@/Pages/Orders/Partials/CreateOrderModal.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { formatPhone } from '@/utils/formatPhone'

const { t } = useI18n()

const props = defineProps({
    orders: Object,
    cities: Array,
    categories: Array,
    clients: Array,
    statuses: Array,
    filters: Object,
})

const showCreate = ref(false)

const statusFilter = ref(props.filters?.status ?? '')
const cityFilter = ref(props.filters?.city_id ?? '')

watch([statusFilter, cityFilter], () => {
    router.get(route('orders.index'), {
        status: statusFilter.value || undefined,
        city_id: cityFilter.value || undefined,
    }, { preserveState: true, replace: true })
})

const currentPage = computed(() => props.orders?.meta?.current_page ?? props.orders?.current_page ?? 1)
const lastPage = computed(() => props.orders?.meta?.last_page ?? props.orders?.last_page ?? 1)
const orderList = computed(() => props.orders?.data ?? [])

const deleteTarget = ref(null)
const deleting = ref(false)

function destroy(order) {
    deleteTarget.value = order
}

function confirmDelete() {
    deleting.value = true
    router.delete(route('orders.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
        onFinish: () => { deleting.value = false },
    })
}
</script>

<template>
    <AdminLayout :title="t('orders.title')">
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ t('orders.title') }}
                </h1>
                <button
                    type="button"
                    @click="showCreate = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ t('orders.add') }}
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800">
                <select
                    v-model="statusFilter"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                >
                    <option value="">{{ t('orders.filters.all_statuses') }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
                <select
                    v-model="cityFilter"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                >
                    <option value="">{{ t('orders.filters.all_cities') }}</option>
                    <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">№</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.client_name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.city') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.category') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.master') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.final_price') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ t('orders.fields.created_at') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            <tr v-if="orderList.length === 0">
                                <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-slate-500">
                                    {{ t('orders.empty') }}
                                </td>
                            </tr>
                            <tr
                                v-for="(order, index) in orderList"
                                :key="order.id"
                                class="cursor-pointer transition-colors hover:bg-blue-50/60 dark:hover:bg-slate-700"
                                @click="router.visit(route('orders.show', order.id))"
                            >
                                <td class="px-6 py-4 text-sm font-mono text-gray-500 dark:text-slate-400">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-slate-200">{{ order.client_name }}</div>
                                    <div class="text-xs text-gray-400">{{ formatPhone(order.client_phone) }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ order.city?.name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ order.category?.name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span v-if="order.master" class="text-gray-700 dark:text-slate-300">{{ order.master.name }}</span>
                                    <span v-else class="text-gray-300 dark:text-slate-600">{{ t('orders.no_master') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <OrderStatusBadge :status="order.status" :label="order.status_label" :color="order.status_color" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">
                                    <span v-if="order.final_price">{{ order.final_price }}</span>
                                    <span v-else class="text-gray-300 dark:text-slate-600">—</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-400 dark:text-slate-500">{{ order.created_at }}</td>
                                <td class="px-6 py-4 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('orders.show', order.id)"
                                            class="rounded-lg p-2 text-slate-400 hover:bg-blue-100 hover:text-blue-600 dark:hover:bg-blue-500/15 dark:hover:text-blue-400 transition-all"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="destroy(order)"
                                            class="rounded-lg p-2 text-slate-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-500/15 dark:hover:text-red-400 transition-all"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M5.79 5.79c-.34-.06-.68-.114-1.022-.166m.232.166L6 19.673c.058 1.176 1.026 2.077 2.244 2.077h7.512c1.218 0 2.186-.901 2.244-2.077l.875-13.882M5.79 5.79H18.21" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="lastPage > 1" class="flex items-center justify-end gap-1 border-t border-gray-100 px-6 py-4 dark:border-slate-700">
                    <Link
                        v-for="page in lastPage"
                        :key="page"
                        :href="route('orders.index', { page, status: filters?.status, city_id: filters?.city_id })"
                        :class="page === currentPage
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-700'"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium transition-colors"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>

        <CreateOrderModal
            :show="showCreate"
            :cities="cities"
            :categories="categories"
            :clients="clients"
            @close="showCreate = false"
        />

        <ConfirmModal
            :show="deleteTarget !== null"
            :message="t('orders.delete_confirm')"
            :processing="deleting"
            @confirm="confirmDelete"
            @close="deleteTarget = null"
        />
    </AdminLayout>
</template>
