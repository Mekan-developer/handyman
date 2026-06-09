<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import OrderStatusBadge from '@/Pages/Orders/Partials/OrderStatusBadge.vue'
import AssignMasterModal from '@/Pages/Orders/Partials/AssignMasterModal.vue'
import SetPriceModal from '@/Pages/Orders/Partials/SetPriceModal.vue'
import ChangeStatusModal from '@/Pages/Orders/Partials/ChangeStatusModal.vue'
import 'leaflet/dist/leaflet.css'

const { t } = useI18n()

const props = defineProps({
    order: { type: Object, required: true },
    eligibleMasters: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
})

const showAssignModal = ref(false)
const showPriceModal = ref(false)
const showStatusModal = ref(false)

const mapContainer = ref(null)
let map = null
let masterMarkerL = null
let trajectoryLine = null

const liveDistance = ref(null)
const liveEta = ref(null)

const isTracking = computed(() =>
    props.order.master && ['assigned', 'in_progress'].includes(props.order.status)
)

onMounted(async () => {
    const L = (await import('leaflet')).default

    const clientLat = parseFloat(props.order.client_lat)
    const clientLng = parseFloat(props.order.client_lng)

    map = L.map(mapContainer.value, {
        maxBounds: L.latLngBounds([[35.1, 52.5], [42.8, 66.7]]),
        maxBoundsViscosity: 1.0,
        minZoom: 5,
    }).setView([clientLat, clientLng], 13)

    L.tileLayer('https://hyzmattm.com.tm/tiles/{z}/{x}/{y}.png', {
        attribution: '',
        maxZoom: 19,
        minZoom: 5,
        keepBuffer: 4,
        updateWhenIdle: true,
        updateWhenZooming: false,
    }).addTo(map)

    const allLatLngs = [[clientLat, clientLng]]

    // Client marker (green)
    const clientIcon = L.divIcon({
        className: 'custom-marker-client',
        html: '<div style="background:#22c55e;width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:12px;">К</div>',
        iconSize: [28, 28],
        iconAnchor: [14, 14],
    })
    L.marker([clientLat, clientLng], { icon: clientIcon })
        .addTo(map)
        .bindPopup(`<b>${escapeHtml(props.order.client_name)}</b><br>${escapeHtml(props.order.client_phone)}`)

    // Assigned master (blue) — when present
    if (props.order.master?.latest_location) {
        const masterLat = parseFloat(props.order.master.latest_location.latitude)
        const masterLng = parseFloat(props.order.master.latest_location.longitude)

        const masterIcon = L.divIcon({
            className: 'custom-marker-master',
            html: '<div style="background:#2563eb;width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:12px;">М</div>',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        })

        masterMarkerL = L.marker([masterLat, masterLng], { icon: masterIcon })
            .addTo(map)
            .bindPopup(`<b>${escapeHtml(props.order.master.name)}</b><br>${escapeHtml(props.order.master.phone)}`)

        allLatLngs.push([masterLat, masterLng])
        updateDistanceEta(masterLat, masterLng, clientLat, clientLng)

        // Load trajectory polyline
        if (isTracking.value) {
            await fetchAndDrawTrajectory(L)
        }
    } else {
        // No master assigned — show eligible candidates as gray dots
        props.eligibleMasters.forEach((m) => {
            if (!m.latest_location) { return }

            const lat = parseFloat(m.latest_location.latitude)
            const lng = parseFloat(m.latest_location.longitude)
            const distanceKm = haversineKm(clientLat, clientLng, lat, lng)

            const candidateIcon = L.divIcon({
                className: 'custom-marker-candidate',
                html: `<div style="background:#94a3b8;width:24px;height:24px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:10px;">${escapeHtml(initialOf(m.name))}</div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12],
            })

            const popupHtml = `
                <div style="min-width:180px;">
                    <div style="font-weight:600;font-size:13px;margin-bottom:2px;">${escapeHtml(m.name)}</div>
                    <div style="color:#6b7280;font-size:12px;">${escapeHtml(m.phone)}</div>
                    <div style="display:flex;align-items:center;gap:4px;color:#2563eb;font-size:12px;font-weight:500;margin:4px 0 8px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-7.5-7-12.5A7 7 0 0112 1a7 7 0 017 7.5C19 13.5 12 21 12 21z"/><circle cx="12" cy="8.5" r="2.5"/></svg>
                        ${formatDistance(distanceKm)}
                    </div>
                    <button
                        onclick="window.__assignFromMap(${m.id})"
                        style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;width:100%"
                    >${escapeHtml(t('orders.actions.assign_master'))}</button>
                </div>
            `

            L.marker([lat, lng], { icon: candidateIcon })
                .addTo(map)
                .bindPopup(popupHtml)

            allLatLngs.push([lat, lng])
        })
    }

    if (allLatLngs.length > 1) {
        map.fitBounds(L.latLngBounds(allLatLngs), { padding: [60, 60] })
    }

    // Wired to candidate "Assign" button in popups
    window.__assignFromMap = (masterId) => {
        router.post(route('orders.assign', props.order.id), { master_id: masterId })
    }

    // Real-time master tracking via Reverb
    if (isTracking.value && window.Echo && props.order.city?.id) {
        const clientLat = parseFloat(props.order.client_lat)
        const clientLng = parseFloat(props.order.client_lng)

        window.Echo.channel(`masters-map.${props.order.city.id}`)
            .listen('.master.location.updated', (payload) => {
                if (payload.master_id !== props.order.master.id) { return }

                const lat = parseFloat(payload.latitude)
                const lng = parseFloat(payload.longitude)

                // Move marker
                masterMarkerL?.setLatLng([lat, lng])

                // Extend polyline
                if (trajectoryLine) {
                    trajectoryLine.addLatLng([lat, lng])
                } else {
                    trajectoryLine = L.polyline([[lat, lng]], {
                        color: '#2563eb',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '8 5',
                    }).addTo(map)
                }

                updateDistanceEta(lat, lng, clientLat, clientLng)
            })
    }
})

onBeforeUnmount(() => {
    delete window.__assignFromMap
    if (props.order.city?.id) {
        window.Echo?.leave(`masters-map.${props.order.city.id}`)
    }
    if (map) {
        map.remove()
        map = null
    }
})

function initialOf(name) {
    return (name?.trim()?.charAt(0) ?? '?').toUpperCase()
}

function escapeHtml(value) {
    const div = document.createElement('div')
    div.textContent = String(value ?? '')
    return div.innerHTML
}

function haversineKm(lat1, lng1, lat2, lng2) {
    const toRad = (deg) => deg * Math.PI / 180
    const R = 6371
    const dLat = toRad(lat2 - lat1)
    const dLng = toRad(lng2 - lng1)
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2
    return 2 * R * Math.asin(Math.sqrt(a))
}

function formatDistance(km) {
    if (km < 1) { return `${Math.round(km * 1000)} м` }
    if (km < 10) { return `${km.toFixed(1)} км` }
    return `${Math.round(km)} км`
}

const ETA_SPEED_KMH = 60

function updateDistanceEta(masterLat, masterLng, clientLat, clientLng) {
    const km = haversineKm(masterLat, masterLng, clientLat, clientLng)
    liveDistance.value = km
    liveEta.value = Math.ceil((km / ETA_SPEED_KMH) * 60)
}

function formatEta(minutes) {
    if (minutes < 60) { return `~${minutes} мин` }
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return m === 0 ? `~${h} ч` : `~${h} ч ${m} мин`
}

async function fetchAndDrawTrajectory(L) {
    try {
        const res = await fetch(route('orders.master-trajectory', props.order.id))
        const json = await res.json()
        const points = (json.points ?? []).map(p => [parseFloat(p.latitude), parseFloat(p.longitude)])

        if (points.length < 2) { return }

        trajectoryLine = L.polyline(points, {
            color: '#2563eb',
            weight: 3,
            opacity: 0.7,
            dashArray: '8 5',
        }).addTo(map)
    } catch {
        // silent — trajectory is optional
    }
}

const sortedEligibleMasters = computed(() => {
    if (!props.eligibleMasters?.length) { return [] }
    const clientLat = parseFloat(props.order.client_lat)
    const clientLng = parseFloat(props.order.client_lng)

    return [...props.eligibleMasters]
        .map((m) => ({
            ...m,
            distance_km: m.latest_location
                ? haversineKm(clientLat, clientLng, parseFloat(m.latest_location.latitude), parseFloat(m.latest_location.longitude))
                : null,
        }))
        .sort((a, b) => {
            if (a.distance_km === null) { return 1 }
            if (b.distance_km === null) { return -1 }
            return a.distance_km - b.distance_km
        })
})

const cardClass = 'rounded-xl bg-white shadow-sm dark:bg-slate-800'
</script>

<template>
    <AdminLayout :title="`${t('orders.show')} #${order.id}`">
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('orders.index')"
                        class="rounded-lg p-2 text-slate-500 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ t('orders.show') }} <span class="font-mono text-gray-400">#{{ order.id }}</span>
                        </h1>
                        <div class="mt-1">
                            <OrderStatusBadge :status="order.status" :label="order.status_label" :color="order.status_color" />
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="!['completed', 'cancelled'].includes(order.status)"
                        @click="showAssignModal = true"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        {{ order.master ? t('orders.actions.change_master') : t('orders.actions.assign_master') }}
                    </button>

                    <button
                        v-if="!['completed', 'cancelled'].includes(order.status)"
                        @click="showPriceModal = true"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors"
                    >
                        {{ t('orders.actions.set_price') }}
                    </button>

                    <button
                        v-if="!['completed', 'cancelled'].includes(order.status)"
                        @click="showStatusModal = true"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors"
                    >
                        {{ t('orders.actions.change_status') }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                <!-- Left: details -->
                <div class="space-y-4 lg:col-span-1">
                    <!-- Client info -->
                    <div :class="cardClass">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ t('orders.fields.client_name') }}</h3>
                        </div>
                        <div class="space-y-2 px-5 py-4 text-sm">
                            <div>
                                <p class="text-base font-medium text-gray-900 dark:text-slate-200">{{ order.client_name }}</p>
                                <a :href="`tel:${order.client_phone}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ order.client_phone }}
                                </a>
                            </div>
                            <div v-if="order.client_address" class="text-gray-500 dark:text-slate-400">
                                {{ order.client_address }}
                            </div>
                        </div>
                    </div>

                    <!-- Order meta -->
                    <div :class="cardClass">
                        <div class="space-y-3 px-5 py-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">{{ t('orders.fields.city') }}</span>
                                <span class="font-medium text-gray-700 dark:text-slate-300">{{ order.city?.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">{{ t('orders.fields.category') }}</span>
                                <span class="font-medium text-gray-700 dark:text-slate-300">{{ order.category?.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">{{ t('orders.fields.created_at') }}</span>
                                <span class="font-medium text-gray-700 dark:text-slate-300">{{ order.created_at }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">{{ t('orders.fields.final_price') }}</span>
                                <span v-if="order.final_price" class="font-mono font-semibold text-green-600 dark:text-green-400">{{ order.final_price }}</span>
                                <span v-else class="text-gray-300 dark:text-slate-600">{{ t('orders.no_price') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Master info -->
                    <div :class="cardClass">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ t('orders.fields.master') }}</h3>
                        </div>
                        <div class="px-5 py-4 text-sm">
                            <div v-if="order.master">
                                <p class="text-base font-medium text-gray-900 dark:text-slate-200">{{ order.master.name }}</p>
                                <a :href="`tel:${order.master.phone}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ order.master.phone }}
                                </a>
                                <p v-if="order.assigned_at" class="mt-1 text-xs text-gray-400">
                                    {{ t('orders.fields.assigned_at') }}: {{ order.assigned_at }}
                                </p>

                                <!-- Live tracking info -->
                                <div v-if="isTracking && liveDistance !== null" class="mt-3 space-y-1.5 rounded-lg bg-blue-50 px-3 py-2.5 dark:bg-blue-900/20">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <!-- Distance -->
                                            <div class="flex items-center gap-1.5 text-blue-700 dark:text-blue-300">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                </svg>
                                                <span class="text-sm font-semibold">{{ formatDistance(liveDistance) }}</span>
                                            </div>
                                            <div class="h-3 w-px bg-blue-200 dark:bg-blue-700" />
                                            <!-- ETA -->
                                            <div class="flex items-center gap-1.5 text-blue-700 dark:text-blue-300">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-sm font-semibold">{{ formatEta(liveEta) }}</span>
                                            </div>
                                        </div>
                                        <!-- Ping dot -->
                                        <span class="relative flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75" />
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500" />
                                        </span>
                                    </div>
                                    <!-- Speed label -->
                                    <p class="text-xs text-blue-500 dark:text-blue-400">при скорости {{ ETA_SPEED_KMH }} км/ч</p>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 dark:text-slate-500">{{ t('orders.no_master') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right: description + map + photos -->
                <div class="space-y-4 lg:col-span-2">
                    <!-- Description -->
                    <div :class="cardClass">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ t('orders.fields.description') }}</h3>
                        </div>
                        <div class="px-5 py-4 text-sm text-gray-700 dark:text-slate-300 whitespace-pre-wrap">
                            {{ order.description }}
                        </div>
                    </div>

                    <!-- Map -->
                    <div :class="cardClass">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ t('orders.fields.location') }}</h3>
                        </div>
                        <div ref="mapContainer" class="h-96 w-full rounded-b-xl" />
                    </div>

                    <!-- Problem photos -->
                    <div v-if="order.photos?.length" :class="cardClass">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ t('orders.fields.photos') }}</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-4">
                            <a
                                v-for="photo in order.photos"
                                :key="photo.id"
                                :href="photo.url"
                                target="_blank"
                                class="group relative aspect-square overflow-hidden rounded-lg ring-1 ring-gray-200 dark:ring-slate-700"
                            >
                                <img :src="photo.url" :alt="`photo-${photo.id}`" class="h-full w-full object-cover transition-transform group-hover:scale-105" />
                            </a>
                        </div>
                    </div>

                    <!-- Tasks (Before/After) -->
                    <div v-if="order.tasks?.length" :class="cardClass">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ t('orders.fields.tasks') }}</h3>
                        </div>
                        <div class="space-y-4 p-5">
                            <div v-for="task in order.tasks" :key="task.id" class="rounded-lg border border-gray-200 p-4 dark:border-slate-700">
                                <p class="mb-3 text-sm font-medium text-gray-900 dark:text-slate-200">{{ task.title }}</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-slate-400">Before</p>
                                        <a v-if="task.before_photo_url" :href="task.before_photo_url" target="_blank">
                                            <img :src="task.before_photo_url" class="aspect-square w-full rounded-lg object-cover" />
                                        </a>
                                        <div v-else class="aspect-square rounded-lg bg-gray-100 dark:bg-slate-700" />
                                    </div>
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500 dark:text-slate-400">After</p>
                                        <a v-if="task.after_photo_url" :href="task.after_photo_url" target="_blank">
                                            <img :src="task.after_photo_url" class="aspect-square w-full rounded-lg object-cover" />
                                        </a>
                                        <div v-else class="aspect-square rounded-lg bg-gray-100 dark:bg-slate-700" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <AssignMasterModal
            :show="showAssignModal"
            :order-id="order.id"
            :masters="sortedEligibleMasters"
            @close="showAssignModal = false"
        />
        <SetPriceModal
            :show="showPriceModal"
            :order-id="order.id"
            :current-price="order.final_price"
            @close="showPriceModal = false"
        />
        <ChangeStatusModal
            :show="showStatusModal"
            :order-id="order.id"
            :current-status="order.status"
            :statuses="statuses"
            @close="showStatusModal = false"
        />
    </AdminLayout>
</template>
