<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import 'leaflet/dist/leaflet.css'

const { t } = useI18n()

const props = defineProps({
    masters: Array,
    cityIds: { type: Array, default: () => [] },
})

const mapContainer = ref(null)
let map = null
let L = null
const markers = {}
const trajectoryLayers = {}
const activeTrajectories = ref(new Set())
const lastUpdateAt = ref({})
const subscribedChannels = []

onMounted(async () => {
    L = (await import('leaflet')).default

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
        addOrUpdateMarker(master.id, master, master.latest_location.latitude, master.latest_location.longitude)
        lastUpdateAt.value[master.id] = master.latest_location.recorded_at
    })

    window.__showTrajectory = (masterId) => toggleTrajectory(masterId)

    subscribeToCityChannels()
})

onBeforeUnmount(() => {
    delete window.__showTrajectory
    subscribedChannels.forEach((channel) => window.Echo?.leave(channel))
    if (map) {
        map.remove()
        map = null
    }
})

function subscribeToCityChannels() {
    if (!window.Echo) {
        console.warn('[Map] Echo is not initialized — realtime updates disabled.')
        return
    }

    console.log('[Map] subscribing to cityIds:', props.cityIds)

    if (!props.cityIds?.length) {
        console.warn('[Map] cityIds is empty — no subscriptions will be made.')
        return
    }

    props.cityIds.forEach((cityId) => {
        const channelName = `masters-map.${cityId}`
        subscribedChannels.push(channelName)

        const channel = window.Echo.channel(channelName)
        console.log('[Map] subscribed to', channelName)

        channel.listen('.master.location.updated', (payload) => {
            console.log('[Map] received event:', payload)
            handleLocationUpdate(payload)
        })

        channel.subscribed(() => console.log('[Map] confirmed subscribe:', channelName))
        channel.error((e) => console.error('[Map] channel error:', channelName, e))
    })
}

function handleLocationUpdate(payload) {
    const master = props.masters.find((m) => m.id === payload.master_id)
    if (!master) { return }

    addOrUpdateMarker(payload.master_id, master, payload.latitude, payload.longitude, true)
    lastUpdateAt.value[payload.master_id] = payload.recorded_at

    if (activeTrajectories.value.has(payload.master_id) && trajectoryLayers[payload.master_id]) {
        trajectoryLayers[payload.master_id].addLatLng([parseFloat(payload.latitude), parseFloat(payload.longitude)])
    }
}

function addOrUpdateMarker(masterId, master, lat, lng, animated = false) {
    const latLng = [parseFloat(lat), parseFloat(lng)]

    if (markers[masterId]) {
        if (animated) {
            animateMarkerTo(markers[masterId], latLng)
        } else {
            markers[masterId].setLatLng(latLng)
        }
        return
    }

    const icon = L.divIcon({
        className: 'master-marker',
        html: `<div style="background:#2563eb;width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.35);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:11px;">${escapeHtml(initialOf(master.name))}</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    })

    const marker = L.marker(latLng, { icon }).addTo(map)

    const popupHtml = `
        <div style="min-width:180px;">
            <div style="font-weight:600;font-size:13px;margin-bottom:4px;">${escapeHtml(master.name)}</div>
            <div style="color:#6b7280;font-size:12px;">${escapeHtml(master.phone)}</div>
            <div style="color:#6b7280;font-size:12px;margin-bottom:8px;">${escapeHtml(master.city?.name ?? '')}</div>
            <button
                onclick="window.__showTrajectory(${master.id})"
                style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;width:100%"
            >${t('masters.trajectory')}</button>
        </div>
    `
    marker.bindPopup(popupHtml)
    markers[masterId] = marker
}

function animateMarkerTo(marker, targetLatLng, duration = 1500) {
    const start = marker.getLatLng()
    const end = L.latLng(targetLatLng[0], targetLatLng[1])
    const startTime = performance.now()

    function step(now) {
        const t = Math.min(1, (now - startTime) / duration)
        const ease = 1 - Math.pow(1 - t, 3)
        const lat = start.lat + (end.lat - start.lat) * ease
        const lng = start.lng + (end.lng - start.lng) * ease
        marker.setLatLng([lat, lng])
        if (t < 1) { requestAnimationFrame(step) }
    }
    requestAnimationFrame(step)
}

async function toggleTrajectory(masterId) {
    if (activeTrajectories.value.has(masterId)) {
        trajectoryLayers[masterId]?.remove()
        delete trajectoryLayers[masterId]
        activeTrajectories.value.delete(masterId)
        return
    }

    const response = await fetch(route('masters.trajectory', masterId))
    const data = await response.json()
    if (!data.points?.length) { return }

    const latlngs = data.points.map((p) => [p.latitude, p.longitude])
    const polyline = L.polyline(latlngs, { color: '#2563eb', weight: 3, opacity: 0.8 }).addTo(map)

    L.circleMarker(latlngs[0], { radius: 6, color: '#16a34a', fillColor: '#22c55e', fillOpacity: 1 })
        .addTo(map).bindTooltip(data.points[0].recorded_at)

    const last = latlngs[latlngs.length - 1]
    L.circleMarker(last, { radius: 6, color: '#dc2626', fillColor: '#ef4444', fillOpacity: 1 })
        .addTo(map).bindTooltip(data.points[data.points.length - 1].recorded_at)

    trajectoryLayers[masterId] = polyline
    activeTrajectories.value.add(masterId)
    map.fitBounds(polyline.getBounds(), { padding: [40, 40] })
}

function initialOf(name) {
    return (name?.trim()?.charAt(0) ?? '?').toUpperCase()
}

function escapeHtml(value) {
    const div = document.createElement('div')
    div.textContent = String(value ?? '')
    return div.innerHTML
}
</script>

<template>
    <AdminLayout :title="t('masters.map')">
        <div class="space-y-4">
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

            <div class="flex flex-wrap items-center gap-4 rounded-xl bg-white px-4 py-3 shadow-sm dark:bg-slate-800">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                    <span class="h-3 w-3 rounded-full bg-blue-600" />
                    {{ t('masters.last_seen') }}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                    <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-green-500" />
                    Live
                </div>
                <p class="ml-auto text-xs text-gray-400 dark:text-slate-500">
                    {{ masters.length }} {{ t('masters.active') }}
                </p>
            </div>

            <div class="overflow-hidden rounded-xl shadow-sm" style="height: 600px;">
                <div ref="mapContainer" class="h-full w-full" />
            </div>
        </div>
    </AdminLayout>
</template>
