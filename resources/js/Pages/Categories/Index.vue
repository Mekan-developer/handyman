<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CategoryFormModal from '@/Pages/Categories/Partials/CategoryFormModal.vue'

const { t } = useI18n()

const props = defineProps({
    categories: Object,
    parentCategories: Array,
})

// ── Modal state ───────────────────────────────────────────────────────────────
const showModal = ref(false)
const editingCategory = ref(null)

const form = useForm({
    name: '',
    is_active: true,
    parent_id: null,
})

function openCreate() {
    editingCategory.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

function openEdit(category) {
    editingCategory.value = category
    form.name = category.name
    form.is_active = category.is_active
    form.parent_id = category.parent_id
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingCategory.value = null
    form.reset()
    form.clearErrors()
}

function submit() {
    if (editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: closeModal,
        })
    } else {
        form.post(route('categories.store'), {
            onSuccess: closeModal,
        })
    }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function destroy(category) {
    if (!confirm(t('categories.delete_confirm'))) { return }
    router.delete(route('categories.destroy', category.id))
}

// ── Pagination ────────────────────────────────────────────────────────────────
const currentPage = computed(() => props.categories?.current_page ?? 1)
const lastPage = computed(() => props.categories?.last_page ?? 1)
const categoryList = computed(() => props.categories?.data ?? [])

// ── Parent select options: exclude children (only roots shown in modal) ───────
// The parentCategories prop already contains only root categories from the server.
// When editing a child, we still show all roots so it can be re-assigned or cleared.
const availableParents = computed(() => {
    if (!editingCategory.value) { return props.parentCategories }
    // Exclude the category being edited itself (can't be its own parent)
    return props.parentCategories.filter(c => c.id !== editingCategory.value.id)
})
</script>

<template>
    <AdminLayout :title="t('categories.title')">
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ t('categories.title') }}
                </h1>
                <button
                    @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('categories.add') }}
                </button>
            </div>

            <!-- Table card -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                    #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                    {{ t('categories.name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                    {{ t('categories.parent') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                    {{ t('categories.status') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                    {{ t('categories.created_at') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                    {{ t('categories.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            <tr v-if="categoryList.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-slate-500">
                                    {{ t('categories.empty') }}
                                </td>
                            </tr>
                            <tr
                                v-for="(category, index) in categoryList"
                                :key="category.id"
                                class="group cursor-default transition-colors duration-150 hover:bg-blue-50/60 dark:hover:bg-slate-700"
                            >
                                <td class="px-6 py-4 text-sm text-gray-400 dark:text-slate-500">
                                    {{ index + 1 + (currentPage - 1) * 15 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <!-- Indent child categories -->
                                        <span
                                            v-if="category.parent_id"
                                            class="flex h-5 w-5 flex-shrink-0 items-center justify-center"
                                        >
                                            <svg class="h-4 w-4 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-slate-300">
                                            {{ category.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400 dark:text-slate-500">
                                    <span v-if="category.parent" class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        {{ category.parent.name }}
                                    </span>
                                    <span v-else class="text-gray-300 dark:text-slate-600">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="category.is_active
                                            ? 'bg-green-100 text-green-700 ring-1 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                            : 'bg-red-100 text-red-600 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    >
                                        <span
                                            :class="category.is_active ? 'bg-green-500 dark:bg-green-400' : 'bg-red-400'"
                                            class="h-1.5 w-1.5 rounded-full"
                                        />
                                        {{ category.is_active ? t('categories.active') : t('categories.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400 dark:text-slate-500">
                                    {{ category.created_at }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            @click="openEdit(category)"
                                            class="rounded-lg p-2 text-slate-400 hover:bg-blue-100 hover:text-blue-600 dark:hover:bg-blue-500/15 dark:hover:text-blue-400 transition-all duration-150"
                                            title="Edit"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="destroy(category)"
                                            class="rounded-lg p-2 text-slate-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-500/15 dark:hover:text-red-400 transition-all duration-150"
                                            title="Delete"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-gray-100 px-6 py-4 dark:border-slate-700">
                    <p class="text-sm text-gray-500 dark:text-slate-400">
                        {{ t('categories.title') }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="page in lastPage"
                            :key="page"
                            :href="route('categories.index', { page })"
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
        </div>

        <!-- Category form modal (create & edit) -->
        <CategoryFormModal
            :show="showModal"
            :form="form"
            :editing="editingCategory"
            :parent-categories="availableParents"
            @close="closeModal"
            @submit="submit"
        />
    </AdminLayout>
</template>
