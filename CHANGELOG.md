# CHANGELOG

История изменений проекта по данным git-истории и метаданных файлов.

---

## [Unreleased] — uncommitted (по состоянию на 2026-05-16)

### Added
- `OrderTask.description` (миграция `2026_05_16_134937_add_description_to_order_tasks_table.php`)
- Actions для мобильного API:
  - `RequestMasterOtpAction`, `VerifyMasterOtpAction` (OTP-авторизация через Cache + Sanctum)
  - `StartMasterOrderAction`, `CompleteMasterOrderAction`
  - `CreateOrderTaskAction`, `UploadTaskPhotoAction`
  - `CreditMasterBalanceAction` (начисление по `PaymentModel`)
  - `ResetMasterBalanceAction`
- Mobile API v1 контроллеры: `MasterAuthController`, `MasterOrderController`, `MasterProfileController`, `MasterTaskController`
- Form Requests v1: `CompleteOrderRequest`, `CreateTaskRequest`, `RequestOtpRequest`, `VerifyOtpRequest`, `UploadTaskPhotoRequest`
- API Resources v1: `MasterOrderResource`, `MasterProfileResource`, `MasterTaskResource`
- Web-контроллеры-заглушки: `ClientController`, `PaymentController` + страницы [Clients/Index.vue](resources/js/Pages/Clients/Index.vue), [Payments/Index.vue](resources/js/Pages/Payments/Index.vue)
- Events: `OrderCreated` (broadcast на канал `orders`)
- Exception: `OtpException`
- Jobs: `app/Jobs/ConvertOrderPhotoJob`, `ConvertTaskPhotoJob`
- Support: `PhotoConverter` (JPEG/PNG/GIF/WebP → WebP, scaling до 1800px, quality 85)
- Локализация: `lang/{ru,tk}/clients.php`, `payments.php`
- Bruno-коллекция [bruno/](bruno/) для ручного тестирования API
- Звук [public/sounds/new-order.mp3](public/sounds/new-order.mp3) для уведомлений о новом заказе
- Тесты: `ConvertOrderPhotoJobTest`, `CreditMasterBalanceActionTest`, `MasterLocationApiTest`, `PhotoConverterTest`

### Changed
- `CreateOrderAction` — теперь принимает массив `UploadedFile[]` и кладёт каждое фото в `orders/{id}/problem`, диспатчит `ConvertOrderPhotoJob`, бросает `OrderCreated`
- `UpdateOrderStatusAction` — добавлен вызов `CreditMasterBalanceAction` при переходе в `Completed`
- `MasterController` — добавлен `resetBalance` и `trajectory`
- `Master` модель — `HasApiTokens`, `latestLocation()`, `hasActiveAccess()`
- `OrderTask` — добавлены константы статусов конвертации фото
- `MasterRepository`, `OrderRepository` — расширены методами под мобильный API и карту
- `AdminLayout.vue` — подписка на канал `orders` для real-time уведомлений
- `Pages/Masters/Index.vue`, `Map.vue` — доработки
- Локализация masters/orders (ru/tk) обновлена
- Roads: `routes/api/v1.php` (новый), `routes/web.php` (добавлены маршруты orders, masters/map, trajectory, resetBalance, ClientController, PaymentController)

---

## [0.3.0] — 2026-05-09 (`a563b40`)

**feat: модуль заказов (Orders) и API геолокации мастеров в реальном времени**

### Added
- Миграции: `orders`, `order_photos`, `order_tasks`, `add_order_id_to_master_locations_table`
- `OrderStatus` enum со state-machine методами (`isFinal`, `label`, `color`)
- `Order`, `OrderPhoto`, `OrderTask` модели + Factories
- `OrderRepository`, `OrderController` (Web), `AssignMasterAction`, `SetOrderFinalPriceAction`, `UpdateOrderStatusAction`, `CreateOrderAction`, `DeleteOrderAction`
- Form Requests: `StoreOrderRequest`, `AssignMasterToOrderRequest`, `SetOrderFinalPriceRequest`, `UpdateOrderStatusRequest`
- `OrderResource`, `OrderException`
- Vue: `Orders/Index.vue`, `Show.vue`, partials (`AssignMasterModal`, `SetPriceModal`, `ChangeStatusModal`, `OrderStatusBadge`)
- API геолокации: `MasterLocationController`, `UpdateMasterLocationAction`, `StoreMasterLocationRequest`, `MasterLocationResource`
- `MasterLocationUpdated` broadcast event (канал `masters-map.{cityId}`)
- `masters/map` страница c Leaflet + realtime через Reverb
- Console `master:simulate-movement` для эмуляции живых координат
- `OrderSeeder`, `MasterLocationSeeder`
- Локализация orders.php (ru/tk)
- Тесты: `OrderTest`, и др.

---

## [0.2.0] — 2026-05-07 (`27a6325`)

**feat: Cities, Categories, Masters CRUD modules with full stack**

### Added
- `cities` таблица, модель, Factory, Seeder, Repository, Controller, Resource, FormRequests
- `categories` таблица (с self-referencing `parent_id`), полный CRUD-стек
- `masters` таблица + `category_master` pivot + `master_locations` таблица
- `PaymentModel` enum
- `MasterRepository`, `MasterController`, `MasterResource`
- Actions: `CreateCity/Category/MasterAction`, `Update*`, `Delete*`
- Vue страницы: `Cities/Index.vue`, `Categories/Index.vue`, `Masters/Index.vue` + form-modals
- Локализация: cities.php, categories.php, masters.php (ru/tk)

---

## [0.1.0] — 2026-05-04 (`77ce311`)

**feat: admin panel foundation with layout, auth, and profile pages**

### Added
- `AdminLayout.vue` (sidebar, topbar, theme toggle, language switcher)
- Pinia stores: `useThemeStore`, `useLocaleStore`, `useNotificationStore`
- vue-i18n + загрузка переводов через Inertia shared prop (`HandleInertiaRequests::loadTranslations`)
- `WithNotification` trait + flash-уведомления
- Локализация: auth.php, layout.php, dashboard.php, profile.php, notifications.php, resources.php (ru/tk)
- Профиль (edit, update password, delete account)

---

## [0.0.1] — 2026-05-04 (`ea68d28`)

**first commit**

### Added
- Laravel 11 skeleton
- Laravel Breeze + Inertia + Vue 3 starter
- Tailwind v3, Vite, Pint, PHPUnit
- Базовые миграции (users, cache, jobs)
- Reverb-конфиг и Sanctum
