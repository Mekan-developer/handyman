<script setup>
import { useI18n } from 'vue-i18n'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useThemeStore } from '@/stores/useThemeStore'
import PasswordInput from '@/Components/PasswordInput.vue'

const { t } = useI18n()
useThemeStore()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}

function inputCls(hasError) {
    return [
        'mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition-colors',
        'focus:outline-none focus:ring-1',
        'dark:bg-slate-700 dark:text-white dark:placeholder:text-slate-500',
        hasError
            ? 'border-red-400 focus:border-red-400 focus:ring-red-400 dark:border-red-500'
            : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:focus:border-indigo-400 dark:focus:ring-indigo-400',
    ].join(' ')
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-8 dark:from-slate-900 dark:to-slate-800">
        <Head :title="t('auth.register.title')" />

        <div class="w-full max-w-md">
            <!-- Logo & title -->
            <div class="mb-8 flex flex-col items-center text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Handyman</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ t('auth.register.subtitle') }}</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl bg-white p-8 shadow-sm dark:bg-slate-800">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('auth.register.name') }}
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            :class="inputCls(!!form.errors.name)"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('auth.register.email') }}
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            :class="inputCls(!!form.errors.email)"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('auth.register.password') }}
                        </label>
                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            :class="inputCls(!!form.errors.password)"
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ t('auth.register.confirm_password') }}
                        </label>
                        <PasswordInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            :class="inputCls(!!form.errors.password_confirmation)"
                        />
                        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Submit + Login link -->
                    <div class="flex flex-col gap-3 pt-1">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-slate-900"
                        >
                            {{ form.processing ? t('auth.register.processing') : t('auth.register.submit') }}
                        </button>
                        <p class="text-center text-sm text-gray-500 dark:text-slate-400">
                            <Link
                                :href="route('login')"
                                class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                {{ t('auth.register.already_registered') }}
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
