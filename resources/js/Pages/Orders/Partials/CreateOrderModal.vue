<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import Modal from '@/Components/Modal.vue'
import InputError from '@/Components/InputError.vue'
import { useThemeStore } from '@/stores/useThemeStore'
import { useLocaleStore } from '@/stores/useLocaleStore'
import 'leaflet/dist/leaflet.css'

const { t } = useI18n()
const { isDark } = storeToRefs(useThemeStore())
const { locale } = storeToRefs(useLocaleStore())

const props = defineProps({
    show: { type: Boolean, required: true },
    cities: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    clients: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const TM_CENTER = [37.95, 58.38]

const form = useForm({
    client_id: null,
    client_name: '',
    client_phone: '',
    city_id: null,
    category_id: null,
    description: '',
    client_address: '',
    client_lat: '',
    client_lng: '',
    photos: [],
})

// ── Клиент: поиск / выбор / ручной ввод ───────────────────────────────────────
const clientSearch = ref('')
const showClientDropdown = ref(false)

const filteredClients = computed(() => {
    const q = clientSearch.value.trim().toLowerCase()
    const list = q
        ? props.clients.filter(c =>
            (c.name ?? '').toLowerCase().includes(q) || (c.phone ?? '').includes(q))
        : props.clients
    return list.slice(0, 8)
})

const selectedClient = computed(() =>
    props.clients.find(c => c.id === form.client_id) ?? null,
)

function selectClient(client) {
    form.client_id = client.id
    form.client_name = client.name ?? ''
    form.client_phone = client.phone ?? ''
    if (client.city_id) { form.city_id = client.city_id }
    clientSearch.value = ''
    showClientDropdown.value = false
}

function deselectClient() {
    form.client_id = null
    form.client_name = ''
    form.client_phone = ''
}

// ── Карта-пикер ───────────────────────────────────────────────────────────────
const mapContainer = ref(null)
let map = null
let L = null
let leafletLayerFn = null
let marker = null

async function initMap() {
    if (!mapContainer.value) { return }
    if (map) { map.invalidateSize(); return }

    L = (await import('leaflet')).default
    leafletLayerFn = (await import('protomaps-leaflet')).leafletLayer

    map = L.map(mapContainer.value, {
        minZoom: 7,
        maxZoom: 18,
        zoomControl: true,
    }).setView(TM_CENTER, 12)

    leafletLayerFn({
        url: '/tiles/turkmenistan.pmtiles',
        flavor: isDark.value ? 'dark' : 'light',
        lang: locale.value,
        maxDataZoom: 14,
        devicePixelRatio: Math.max(2, window.devicePixelRatio || 1),
    }).addTo(map)

    map.on('click', (e) => {
        form.client_lat = e.latlng.lat.toFixed(6)
        form.client_lng = e.latlng.lng.toFixed(6)
        placeMarker([e.latlng.lat, e.latlng.lng])
    })

    setTimeout(() => map?.invalidateSize(), 150)
}

function placeMarker(latLng) {
    if (!map) { return }
    if (marker) {
        marker.setLatLng(latLng)
        return
    }
    marker = L.marker(latLng, { draggable: true }).addTo(map)
    marker.on('dragend', () => {
        const p = marker.getLatLng()
        form.client_lat = p.lat.toFixed(6)
        form.client_lng = p.lng.toFixed(6)
    })
}

function syncMarkerFromFields() {
    const lat = parseFloat(form.client_lat)
    const lng = parseFloat(form.client_lng)
    if (!Number.isFinite(lat) || !Number.isFinite(lng) || !map) { return }
    placeMarker([lat, lng])
    map.setView([lat, lng], Math.max(map.getZoom(), 14))
}

function destroyMap() {
    if (map) {
        map.remove()
        map = null
    }
    marker = null
}

// ── Фото ──────────────────────────────────────────────────────────────────────
const photoInput = ref(null)
const photoPreviews = ref([])

function onPhotosChange(e) {
    const files = Array.from(e.target.files).slice(0, 4)
    form.photos = files
    photoPreviews.value = files.map(f => URL.createObjectURL(f))
}

function removePhoto(index) {
    form.photos = form.photos.filter((_, i) => i !== index)
    photoPreviews.value = photoPreviews.value.filter((_, i) => i !== index)
    if (photoInput.value) { photoInput.value.value = '' }
}

// ── Жизненный цикл модалки ────────────────────────────────────────────────────
watch(() => props.show, async (val) => {
    if (val) {
        form.reset()
        form.clearErrors()
        form.photos = []
        photoPreviews.value = []
        clientSearch.value = ''
        showClientDropdown.value = false
        await nextTick()
        initMap()
    } else {
        destroyMap()
    }
})

onBeforeUnmount(destroyMap)

function submit() {
    form.post(route('orders.store'), {
        forceFormData: true,
        onSuccess: () => emit('close'),
    })
}

const inputClass = 'w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white dark:focus:bg-slate-700'
const errorInputClass = 'border-red-400 dark:border-red-500'
const labelClass = 'block text-sm font-medium text-gray-700 dark:text-slate-300'
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-slate-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                {{ t('orders.modals.create_title') }}
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

        <form @submit.prevent="submit" class="max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">

                <!-- Client section -->
                <div class="space-y-2 sm:col-span-2">
                    <label :class="labelClass">{{ t('orders.create.client_section') }}</label>

                    <!-- Selected existing client -->
                    <div
                        v-if="selectedClient"
                        class="flex items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50/60 px-4 py-2.5 dark:border-blue-500/40 dark:bg-blue-500/10"
                    >
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-900 dark:text-slate-100">
                                {{ selectedClient.name || selectedClient.phone }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-slate-400">{{ selectedClient.phone }}</div>
                        </div>
                        <span class="hidden text-xs text-blue-600 dark:text-blue-300 sm:inline">
                            {{ t('orders.create.selected_client') }}
                        </span>
                        <button
                            type="button"
                            @click="deselectClient"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700 transition-colors"
                        >
                            {{ t('orders.create.deselect') }}
                        </button>
                    </div>

                    <!-- Search + manual entry -->
                    <div v-else class="space-y-2">
                        <div class="relative">
                            <input
                                v-model="clientSearch"
                                type="text"
                                :placeholder="t('orders.create.search_client')"
                                @focus="showClientDropdown = true"
                                @input="showClientDropdown = true"
                                :class="inputClass"
                            />
                            <div
                                v-if="showClientDropdown && clientSearch"
                                class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-slate-600 dark:bg-slate-800"
                            >
                                <button
                                    v-for="c in filteredClients"
                                    :key="c.id"
                                    type="button"
                                    @click="selectClient(c)"
                                    class="flex w-full items-center justify-between gap-2 px-4 py-2.5 text-left hover:bg-blue-50 dark:hover:bg-slate-700"
                                >
                                    <span class="text-sm text-gray-900 dark:text-slate-200">{{ c.name || '—' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-slate-400">{{ c.phone }}</span>
                                </button>
                                <p v-if="filteredClients.length === 0" class="px-4 py-3 text-sm text-gray-400 dark:text-slate-500">
                                    {{ t('orders.create.no_clients_found') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <input
                                    v-model="form.client_name"
                                    type="text"
                                    :placeholder="t('orders.fields.client_name')"
                                    :class="[inputClass, form.errors.client_name ? errorInputClass : '']"
                                />
                                <InputError :message="form.errors.client_name" />
                            </div>
                            <div class="space-y-1">
                                <input
                                    v-model="form.client_phone"
                                    type="text"
                                    :placeholder="t('orders.fields.client_phone')"
                                    :class="[inputClass, form.errors.client_phone ? errorInputClass : '']"
                                />
                                <InputError :message="form.errors.client_phone" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-slate-500">{{ t('orders.create.new_client_hint') }}</p>
                    </div>
                </div>

                <!-- City -->
                <div class="space-y-1">
                    <label :class="labelClass">{{ t('orders.fields.city') }}</label>
                    <select v-model="form.city_id" :class="[inputClass, form.errors.city_id ? errorInputClass : '']">
                        <option :value="null" disabled>{{ t('orders.modals.select_city') }}</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                    </select>
                    <InputError :message="form.errors.city_id" />
                </div>

                <!-- Category -->
                <div class="space-y-1">
                    <label :class="labelClass">{{ t('orders.fields.category') }}</label>
                    <select v-model="form.category_id" :class="[inputClass, form.errors.category_id ? errorInputClass : '']">
                        <option :value="null" disabled>{{ t('orders.modals.select_category') }}</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <InputError :message="form.errors.category_id" />
                </div>

                <!-- Address -->
                <div class="space-y-1 sm:col-span-2">
                    <label :class="labelClass">{{ t('orders.fields.client_address') }}</label>
                    <input
                        v-model="form.client_address"
                        type="text"
                        :class="[inputClass, form.errors.client_address ? errorInputClass : '']"
                    />
                    <InputError :message="form.errors.client_address" />
                </div>

                <!-- Description -->
                <div class="space-y-1 sm:col-span-2">
                    <label :class="labelClass">{{ t('orders.fields.description') }}</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        :class="[inputClass, form.errors.description ? errorInputClass : '']"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <!-- Map picker -->
                <div class="space-y-1 sm:col-span-2">
                    <label :class="labelClass">{{ t('orders.fields.location') }}</label>
                    <div
                        ref="mapContainer"
                        class="h-64 w-full overflow-hidden rounded-xl border border-gray-300 dark:border-slate-600"
                    />
                    <p class="text-xs text-gray-400 dark:text-slate-500">{{ t('orders.create.pick_location') }}</p>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="space-y-1">
                            <input
                                v-model="form.client_lat"
                                type="number"
                                step="any"
                                :placeholder="t('orders.fields.client_lat')"
                                @change="syncMarkerFromFields"
                                :class="[inputClass, form.errors.client_lat ? errorInputClass : '']"
                            />
                            <InputError :message="form.errors.client_lat" />
                        </div>
                        <div class="space-y-1">
                            <input
                                v-model="form.client_lng"
                                type="number"
                                step="any"
                                :placeholder="t('orders.fields.client_lng')"
                                @change="syncMarkerFromFields"
                                :class="[inputClass, form.errors.client_lng ? errorInputClass : '']"
                            />
                            <InputError :message="form.errors.client_lng" />
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="space-y-2 sm:col-span-2">
                    <label :class="labelClass">{{ t('orders.fields.photos') }}</label>
                    <input
                        ref="photoInput"
                        type="file"
                        accept="image/*"
                        multiple
                        class="hidden"
                        @change="onPhotosChange"
                    />
                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="(url, i) in photoPreviews"
                            :key="i"
                            class="relative h-20 w-20 overflow-hidden rounded-lg border border-gray-200 dark:border-slate-600"
                        >
                            <img :src="url" class="h-full w-full object-cover" alt="" />
                            <button
                                type="button"
                                @click="removePhoto(i)"
                                class="absolute right-0.5 top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <button
                            v-if="photoPreviews.length < 4"
                            type="button"
                            @click="photoInput?.click()"
                            class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-gray-300 text-gray-400 hover:border-blue-400 hover:text-blue-500 dark:border-slate-600 dark:hover:border-blue-500 transition-colors"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span class="text-[10px]">{{ t('orders.create.add_photos') }}</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-slate-500">{{ t('orders.create.photos_hint') }}</p>
                    <InputError :message="form.errors.photos" />
                </div>
            </div>

            <div class="sticky bottom-0 flex justify-end gap-2 border-t border-gray-100 bg-white px-6 py-4 dark:border-slate-700 dark:bg-slate-800">
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors"
                >
                    {{ t('orders.cancel') }}
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? '...' : t('orders.add') }}
                </button>
            </div>
        </form>
    </Modal>
</template>
