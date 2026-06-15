<script setup>
import { useI18n } from 'vue-i18n'
import Modal from '@/Components/Modal.vue'

const { t } = useI18n()

defineProps({
    show: { type: Boolean, required: true },
    form: { type: Object, required: true },
    editing: { type: Object, default: null },
})

const emit = defineEmits(['close', 'submit'])
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="flex h-full flex-col">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-slate-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ editing ? t('users.edit') : t('users.add') }}
                </h2>
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-700 dark:hover:text-slate-300 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="emit('submit')" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <!-- Name -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('users.fields.name') }} <span class="text-red-400">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            autofocus
                            :class="form.errors.name
                                ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20 dark:border-red-500'
                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-600 dark:focus:border-blue-500'"
                            class="w-full rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:bg-white focus:outline-none focus:ring-4 dark:bg-slate-700/50 dark:text-white dark:placeholder-slate-500 dark:focus:bg-slate-700 transition-all"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('users.fields.email') }} <span class="text-red-400">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            :class="form.errors.email
                                ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20 dark:border-red-500'
                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-600 dark:focus:border-blue-500'"
                            class="w-full rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:bg-white focus:outline-none focus:ring-4 dark:bg-slate-700/50 dark:text-white dark:placeholder-slate-500 dark:focus:bg-slate-700 transition-all"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('users.fields.role') }} <span class="text-red-400">*</span>
                        </label>
                        <select
                            v-model="form.role"
                            :class="form.errors.role
                                ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20 dark:border-red-500'
                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-600 dark:focus:border-blue-500'"
                            class="w-full rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-900 shadow-sm focus:bg-white focus:outline-none focus:ring-4 dark:bg-slate-700/50 dark:text-white dark:focus:bg-slate-700 transition-all"
                        >
                            <option value="administrator">{{ t('users.roles.administrator') }}</option>
                            <option value="manager">{{ t('users.roles.manager') }}</option>
                            <option value="operator">{{ t('users.roles.operator') }}</option>
                        </select>
                        <p v-if="form.errors.role" class="mt-1.5 text-xs text-red-500">{{ form.errors.role }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('users.fields.password') }}
                            <span v-if="!editing" class="text-red-400">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            :class="form.errors.password
                                ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20 dark:border-red-500'
                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-600 dark:focus:border-blue-500'"
                            class="w-full rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:bg-white focus:outline-none focus:ring-4 dark:bg-slate-700/50 dark:text-white dark:placeholder-slate-500 dark:focus:bg-slate-700 transition-all"
                        />
                        <p v-if="editing" class="mt-1 text-xs text-gray-400 dark:text-slate-500">
                            {{ t('users.password_hint') }}
                        </p>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('users.fields.password_confirmation') }}
                            <span v-if="!editing" class="text-red-400">*</span>
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            :class="form.errors.password_confirmation
                                ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20 dark:border-red-500'
                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-600 dark:focus:border-blue-500'"
                            class="w-full rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:bg-white focus:outline-none focus:ring-4 dark:bg-slate-700/50 dark:text-white dark:placeholder-slate-500 dark:focus:bg-slate-700 transition-all"
                        />
                        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                    </div>
                </div>

                <div class="flex shrink-0 justify-end gap-2 border-t border-gray-100 px-6 py-4 dark:border-slate-700">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors"
                    >
                        {{ t('layout.actions.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? '...' : (editing ? t('layout.actions.update') : t('layout.actions.save')) }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
