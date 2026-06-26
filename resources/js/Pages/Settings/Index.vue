<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import InputError from '@/Components/InputError.vue'

const { t } = useI18n()

const props = defineProps({
    masterAppRules: { type: String, default: '' },
    clientAppRules: { type: String, default: '' },
})

const form = useForm({
    master_app_rules: props.masterAppRules ?? '',
    client_app_rules: props.clientAppRules ?? '',
})

// Which section editor is open: null | 'master' | 'client'
const activeSection = ref(null)
// Local copy while editing (avoids touching the form until Save)
const editorContent = ref('')

const sections = [
    {
        key: 'master',
        field: 'master_app_rules',
        titleKey: 'settings.master_app.title',
        hintKey: 'settings.master_app.hint',
        placeholderKey: 'settings.master_app.placeholder',
        color: 'indigo',
        iconPath: 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z',
    },
    {
        key: 'client',
        field: 'client_app_rules',
        titleKey: 'settings.client_app.title',
        hintKey: 'settings.client_app.hint',
        placeholderKey: 'settings.client_app.placeholder',
        color: 'emerald',
        iconPath: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    },
]

function currentSection() {
    return sections.find(s => s.key === activeSection.value)
}

function openEditor(section) {
    editorContent.value = form[section.field] ?? ''
    activeSection.value = section.key
}

function closeEditor() {
    activeSection.value = null
    editorContent.value = ''
    form.clearErrors()
}

function submit() {
    const section = currentSection()
    if (!section) { return }

    form[section.field] = editorContent.value

    form.put(route('settings.update'), {
        preserveScroll: true,
        onSuccess: closeEditor,
    })
}

// Strip HTML tags for plain-text preview
function stripHtml(html) {
    return html ? html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : ''
}

function hasContent(field) {
    return stripHtml(form[field]).length > 0
}
</script>

<template>
    <AdminLayout :title="t('settings.title')">
        <div class="mx-auto max-w-3xl space-y-4">

            <!-- Section cards -->
            <div
                v-for="section in sections"
                :key="section.key"
                class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex items-center gap-4 p-5">

                    <!-- App icon -->
                    <div
                        :class="[
                            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                            section.color === 'indigo'
                                ? 'bg-indigo-100 dark:bg-indigo-900/40'
                                : 'bg-emerald-100 dark:bg-emerald-900/40',
                        ]"
                    >
                        <svg
                            :class="[
                                'h-5 w-5',
                                section.color === 'indigo'
                                    ? 'text-indigo-600 dark:text-indigo-400'
                                    : 'text-emerald-600 dark:text-emerald-400',
                            ]"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="section.iconPath" />
                        </svg>
                    </div>

                    <!-- Title + hint -->
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ t(section.titleKey) }}
                        </p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-slate-400">
                            {{ t(section.hintKey) }}
                        </p>

                        <!-- Preview of saved content -->
                        <p
                            v-if="hasContent(section.field)"
                            class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-slate-300"
                        >
                            {{ stripHtml(form[section.field]) }}
                        </p>
                        <p v-else class="mt-2 text-sm italic text-gray-400 dark:text-slate-500">
                            {{ t(section.placeholderKey) }}
                        </p>
                    </div>

                    <!-- Edit (eye) button -->
                    <button
                        type="button"
                        @click="openEditor(section)"
                        :class="[
                            'shrink-0 rounded-lg p-2.5 transition-colors',
                            section.color === 'indigo'
                                ? 'text-indigo-500 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30'
                                : 'text-emerald-500 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30',
                        ]"
                        :title="t('settings.edit')"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Editor drawer -->
        <Modal
            :show="activeSection !== null"
            max-width="2xl"
            @close="closeEditor"
        >
            <div v-if="currentSection()" class="flex h-full flex-col">

                <!-- Drawer header -->
                <div class="flex shrink-0 items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-slate-700">
                    <div
                        :class="[
                            'flex h-9 w-9 items-center justify-center rounded-lg',
                            currentSection().color === 'indigo'
                                ? 'bg-indigo-100 dark:bg-indigo-900/40'
                                : 'bg-emerald-100 dark:bg-emerald-900/40',
                        ]"
                    >
                        <svg
                            :class="[
                                'h-4 w-4',
                                currentSection().color === 'indigo'
                                    ? 'text-indigo-600 dark:text-indigo-400'
                                    : 'text-emerald-600 dark:text-emerald-400',
                            ]"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="currentSection().iconPath" />
                        </svg>
                    </div>
                    <h2 class="flex-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ t(currentSection().titleKey) }}
                    </h2>
                    <button
                        type="button"
                        @click="closeEditor"
                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Editor -->
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <RichTextEditor
                        v-model="editorContent"
                        :placeholder="t(currentSection().placeholderKey)"
                    />
                    <InputError
                        :message="form.errors[currentSection().field]"
                        class="mt-2"
                    />
                </div>

                <!-- Drawer footer -->
                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-700">
                    <button
                        type="button"
                        @click="closeEditor"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                    >
                        {{ t('layout.actions.cancel') }}
                    </button>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ t('settings.save') }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
