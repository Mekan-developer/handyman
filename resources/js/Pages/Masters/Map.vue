<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import 'leaflet/dist/leaflet.css'

const { t } = useI18n()

const props = defineProps({
    masters: Array,
})

const mapContainer = ref(null)
let map = null
const trajectoryLayers = {}
const activeTrajectories = ref(new Set())
const loadingTrajectory = ref(null)

onMounted(async () => {
    const L = (await import('leaflet')).default

    // Fix default icon paths for Vite builds
    delete L.Icon.Default.prototype._getIconUrl
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    })

    map = L.map(mapContainer.value).setView([37.95, 58.38], 11)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map)

    props.masters.forEach((master) => {
        if (!master.latest_location) { return }

        const { latitude, longitude } = master.latest_location

        const marker = L.marker([latitude, longitude]).addTo(map)

        const popupHtml = `
            <div style="min-width:160px;">
                <div style="font-weight:600;font-size:13px;margin-bottom:4px;">${master.name}</div>
                <div style="color:#6b7280;font-size:12px;">${master.phone}</div>
                <div style="color:#6b7280;font-size:12px;margin-bottom:8px;">${master.city?.name ?? ''}</div>
                <button
                    onclick="window.__showTrajectory(${master.id})"
                    style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;width:100%"
                >
                    ${t('masters.trajectory')}
                </button>
            </div>
        `
        marker.bindPopup(popupHtml)
    })

    // Global callback for popup button
    window.__showTrajectory = (masterId) => {
        toggleTrajectory(L, masterId)
    }
})

onBeforeUnmount(() => {
    delete window.__showTrajectory
    if (map) {
        map.remove()
        map = null
    }
})

async function toggleTrajectory(L, masterId) {
    if (activeTrajectories.value.has(masterId)) {
        trajectoryLayers[masterId]?.remove()
        delete trajectoryLayers[masterId]
        activeTrajectories.value.delete(masterId)
        return
    }

    loadingTrajectory.value = masterId

    try {
        const response = await fetch(route('masters.trajectory', masterId))
        const data = await response.json()

        if (!data.points?.length) { return }

        const latlngs = data.points.map((p) => [p.latitude, p.longitude])

        const polyline = L.polyline(latlngs, {
            color: '#2563eb',
            weight: 3,
            opacity: 0.8,
        }).addTo(map)

        // Start point
        L.circleMarker(latlngs[0], { radius: 6, color: '#16a34a', fillColor: '#22c55e', fillOpacity: 1 })
            .addTo(map)
            .bindTooltip(data.points[0].recorded_at, { permanent: false })

        // End point
        const last = latlngs[latlngs.length - 1]
        L.circleMarker(last, { radius: 6, color: '#dc2626', fillColor: '#ef4444', fillOpacity: 1 })
            .addTo(map)
            .bindTooltip(data.points[data.points.length - 1].recorded_at, { permanent: false })

        trajectoryLayers[masterId] = polyline
        activeTrajectories.value.add(masterId)
        map.fitBounds(polyline.getBounds(), { padding: [40, 40] })
    } finally {
        loadingTrajectory.value = null
    }
}
</script>

<template>
    <AdminLayout :title="t('masters.map')">
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ t('masters.map') }}
                </h1>
                <Link
                    :href="route('masters.index')"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    {{ t('masters.title') }}
                </Link>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap gap-4 rounded-xl bg-white px-4 py-3 shadow-sm dark:bg-slate-800">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                    <span class="h-3 w-3 rounded-full bg-blue-600" />
                    {{ t('masters.trajectory') }}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                    <span class="h-3 w-3 rounded-full bg-green-500" />
                    {{ t('masters.last_seen') }}
                </div>
                <p class="ml-auto text-xs text-gray-400 dark:text-slate-500">
                    {{ masters.length }} {{ t('masters.active') }}
                </p>
            </div>

            <!-- Map container -->
            <div class="overflow-hidden rounded-xl shadow-sm" style="height: 600px;">
                <div ref="mapContainer" class="h-full w-full" />
            </div>
        </div>
    </AdminLayout>
</template>
