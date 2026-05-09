<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    maxWidth: { type: String, default: 'md' },
    closeable: { type: Boolean, default: true },
})

const emit = defineEmits(['close'])

const maxWidthClass = computed(() => ({
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
}[props.maxWidth] ?? 'max-w-md'))

function close() {
    if (props.closeable) {
        emit('close')
    }
}

function onKeydown(e) {
    if (e.key === 'Escape' && props.show) {
        close()
    }
}

watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[9999] flex items-center justify-center px-4 py-8"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/25 backdrop-blur-md"
                    @click="close"
                />

                <!-- Dialog panel -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="show"
                        class="relative w-full rounded-2xl bg-white shadow-2xl dark:bg-slate-800 dark:ring-1 dark:ring-slate-700"
                        :class="maxWidthClass"
                    >
                        <slot />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
