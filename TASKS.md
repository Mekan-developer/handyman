# TASKS

Актуальное состояние задач по проекту (обновлено 2026-06-10).

---

## ✅ Выполнено

### Инфраструктура и фундамент
- Laravel 11 + Inertia v2 + Vue 3 + Tailwind v3
- Laravel Breeze (auth scaffolding)
- Pinia stores: `useThemeStore`, `useLocaleStore`, `useNotificationStore`
- vue-i18n: переводы из `lang/{ru,tk}/*.php` через Inertia shared prop
- Dark/Light тема через Pinia + LocalStorage
- `WithNotification` trait + автоподхват flash в AdminLayout
- Ziggy v2, Laravel Sanctum, Laravel Reverb + Echo

### Admin Panel — CRUD-модули (все полностью реализованы)

| Модуль | Маршруты | Vue | Тест |
|--------|---------|-----|------|
| Oblasts | ✅ CRUD | ✅ Index + Modal | ❌ нет |
| Regions | ✅ CRUD | ✅ Index + Modal | ❌ нет |
| Cities | ✅ CRUD | ✅ Index + Modal | ✅ CityTest |
| Categories | ✅ CRUD | ✅ Index + Modal | ✅ CategoryTest |
| Masters | ✅ CRUD + Map + Trajectory + Balance | ✅ Index + Map | ✅ MasterTest |
| Orders | ✅ index/show/store/destroy/assign/setPrice/updateStatus | ✅ Index + Show | ✅ OrderTest |
| Clients | ✅ CRUD + toggleBlock | ✅ Index + Modal | ✅ ClientTest |
| Banners | ✅ CRUD + toggle | ✅ Index + Modal | ✅ BannerTest |
| Notifications | ✅ read/delete | ✅ есть в AdminLayout | ❌ нет |
| Dashboard | ✅ реальные данные через DashboardRepository | ✅ 4 stat-карточки | ❌ нет |
| Payments | ❌ заглушка | ✅ страница-заглушка | ❌ нет |

### Mobile API (api/v1)

#### Master API
- OTP auth: request-otp, verify-otp, logout
- GET/PATCH /api/v1/master/me
- Orders: index, show, start, complete
- Tasks: store + photo upload (WebP фоновая конвертация)
- Location: POST /api/v1/master/{master}/location

#### Client API
- OTP auth: request-otp, verify-otp, logout, complete-registration
- GET/PATCH /api/v1/client/me
- Catalog: GET oblasts, regions, cities, categories
- Orders: index, show, store

### Backend Infrastructure
- Модели: User, Oblast, Region, City, Category, Master, MasterLocation, Order, OrderTask, OrderPhoto, Client, Banner
- Enums: `OrderStatus` (в `app/OrderStatus.php`), `PaymentModel`
- Events: OrderCreated, OrderStatusChanged, MasterAssigned, MasterLocationUpdated
- Jobs: ConvertOrderPhotoJob, ConvertTaskPhotoJob (WebP, tries=3, backoff=30)
- Repositories: Oblast, Region, City, Category, Master, Order, Client, Banner, Dashboard
- Actions: полный набор для всех модулей (40+ классов)
- Resources: Admin + API V1 (Master + Client)

### Тесты
- ✅ CityTest, CategoryTest, MasterTest, OrderTest, BannerTest, ClientTest
- ✅ CreditMasterBalanceActionTest, ConvertOrderPhotoJobTest, PhotoConverterTest
- ✅ MasterLocationApiTest (в ClientTest — mobile API catalog)

---

## 🚧 Текущая незакоммиченная работа

**PMTiles / Offline векторные карты:**
- `TilesController.php` — раздаёт `.pmtiles` файлы с Range-запросами (HTTP 206)
- `Map.vue` — переходит с OSM на `protomaps-leaflet` (offline vector tiles)
- `routes/web.php` — добавлен `/tiles/{file}`, `/` редиректит на login
- `package.json` — добавлен `protomaps-leaflet ^5.1.0`, vite поднят до v8

---

## ❌ Не сделано / TODO

| # | Задача | Приоритет |
|---|--------|-----------|
| 1 | **OblastTest.php** — тесты CRUD для областей | Высокий |
| 2 | **RegionTest.php** — тесты CRUD для районов | Высокий |
| 3 | **Edit Order** — нет маршрута/экшена/формы update заказа | Высокий |
| 4 | **Payments модуль** — нет модели, миграции, Repository, логики | Средний |
| 5 | **SMS-шлюз OTP** — `RequestMasterOtpAction` только Log::info | Низкий |
| 6 | **Приватные Broadcast каналы** — `orders` и `masters-map.*` публичные | Низкий |
| 7 | **Auth для location ping** — `/master/{master}/location` без auth | Низкий |
| 8 | **OrderStatus enum location** — лежит в `app/OrderStatus.php`, а не `app/Enums/` | Низкий |
| 9 | **Flutter-приложение** — API готов, мобильного клиента нет | — |

---

## Полезные команды

```bash
php artisan route:list --except-vendor
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run dev
php artisan master:simulate-movement
```
