# TASKS

Реальное состояние задач по проекту, собранное из кода и git-истории.

---

## ✅ Выполнено

### Инфраструктура и фундамент
- Laravel 11 + Inertia v2 + Vue 3 + Tailwind v3 (структура bootstrap/app.php)
- Laravel Breeze (auth scaffolding: login, register, password reset, email verify, password confirm)
- Pinia stores: `useThemeStore`, `useLocaleStore`, `useNotificationStore`
- vue-i18n: переводы тянутся из `lang/{ru,tk}/*.php` через Inertia shared prop `translations` ([HandleInertiaRequests.php](app/Http/Middleware/HandleInertiaRequests.php))
- Dark/Light тема через Pinia + LocalStorage (`dark` класс на `<html>`)
- `WithNotification` trait + автоподхват flash через AdminLayout
- Ziggy v2 (named routes на фронтенде)
- Laravel Sanctum (выдача токенов для мобильного API)
- Laravel Reverb (WebSocket broadcaster) + Echo + pusher-js на фронте
- Health endpoint `/up`

### Admin Panel — UI
- `AdminLayout` с боковым меню, переключателем темы и языка, отображением flash-уведомлений ([AdminLayout.vue](resources/js/Layouts/AdminLayout.vue))
- Dashboard страница ([Dashboard.vue](resources/js/Pages/Dashboard.vue))
- Profile (edit / update password / delete) ([Pages/Profile/](resources/js/Pages/Profile/))

### Cities (Города) — CRUD
- Migration, Model, Factory, Seeder
- `CityRepository`, Actions (`CreateCityAction`, `UpdateCityAction`, `DeleteCityAction`)
- `CityController`, Form Requests, `CityResource`
- Vue: [Pages/Cities/Index.vue](resources/js/Pages/Cities/Index.vue) + `CityFormModal.vue`

### Categories (Категории) — CRUD с parent_id (древовидные)
- Migration, Model, Factory, Seeder
- `CategoryRepository`, Actions, FormRequests, `CategoryResource`
- Vue: [Pages/Categories/Index.vue](resources/js/Pages/Categories/Index.vue) + `CategoryFormModal.vue`

### Masters (Мастера) — CRUD + балансы + карта
- Migration `masters`, pivot `category_master`, `master_locations`
- `PaymentModel` enum (Percentage, FixedPerJob, Salary, SalaryPercentage)
- `MasterRepository` (с методами `forMap`, `trajectory`, `eligibleForOrder`)
- Actions: Create/Update/Delete, `ResetMasterBalanceAction`, `CreditMasterBalanceAction`, `UpdateMasterLocationAction`
- Vue: [Pages/Masters/Index.vue](resources/js/Pages/Masters/Index.vue) (CRUD) + [Map.vue](resources/js/Pages/Masters/Map.vue) (Leaflet карта)
- `MasterFormModal.vue` (форма создания/редактирования)
- Carga живых координат через Reverb (канал `masters-map.{cityId}`)
- Trajectory endpoint (история перемещений мастера за N часов)

### Orders (Заказы) — модуль
- Migration `orders` (с client_lat/lng, статусы, denormalised client_*, final_price, таймштампы фазы)
- Migration `order_photos`, `order_tasks` (с `description` через отдельную миграцию)
- `OrderStatus` enum (Pending → Assigned → InProgress → Completed | Cancelled) + state machine в `UpdateOrderStatusAction`
- `OrderRepository` (paginate с фильтрами, forMaster, findForMasterOrFail)
- Actions: `CreateOrderAction` (с загрузкой фото), `AssignMasterAction`, `SetOrderFinalPriceAction`, `UpdateOrderStatusAction`, `DeleteOrderAction`
- Web: `OrderController` ([index/show/store/destroy/assign/setPrice/updateStatus/masterTrajectoryForOrder](app/Http/Controllers/OrderController.php))
- Vue: [Pages/Orders/Index.vue](resources/js/Pages/Orders/Index.vue), [Show.vue](resources/js/Pages/Orders/Show.vue) с Leaflet картой клиент↔мастер + трекинг
- Modals: `AssignMasterModal`, `SetPriceModal`, `ChangeStatusModal`, `OrderStatusBadge`
- Real-time уведомление о новом заказе (`order.created` event, beep через AudioContext, [public/sounds/new-order.mp3](public/sounds/new-order.mp3))
- `OrderCreated` ShouldBroadcastNow event на канал `orders`
- `OrderException` с typed factory methods

### Master Mobile API (v1)
- Routes: [routes/api/v1.php](routes/api/v1.php)
- `MasterAuthController` — OTP (request/verify/logout) через Cache (5 мин) + Sanctum token `mobile`
- `MasterLocationController` — приём GPS ping'ов (пока публичный)
- `MasterProfileController` — `GET /master/me`
- `MasterOrderController` — list (active/history), show, start, complete
- `MasterTaskController` — создание тасок + загрузка before/after фото
- `RequestMasterOtpAction`, `VerifyMasterOtpAction`, `OtpException`
- Resources: `MasterProfileResource`, `MasterOrderResource`, `MasterTaskResource`, `MasterLocationResource`
- Bruno коллекция для ручного тестирования: [bruno/](bruno/)

### Фоновая обработка фото
- `PhotoConverter` — конвертация JPEG/PNG/GIF/WebP → WebP, скейл до 1800px по высоте, quality 85
- `ConvertOrderPhotoJob` (tries=3, backoff=30) — обработка фото заказа
- `ConvertTaskPhotoJob` — обработка before/after фото задачи
- Статусы: pending → converting → done | failed

### Events / Broadcasting
- `OrderCreated` → канал `orders` (для дашборда админов)
- `MasterLocationUpdated` → канал `masters-map.{cityId}` (для карты)
- Channel auth в [routes/channels.php](routes/channels.php) (public для масштабирования карты, TODO — приватный)

### Заглушки (страницы-индексы без логики)
- `ClientController::index` → [Pages/Clients/Index.vue](resources/js/Pages/Clients/Index.vue)
- `PaymentController::index` → [Pages/Payments/Index.vue](resources/js/Pages/Payments/Index.vue)

### Локализация
- `lang/ru/` и `lang/tk/`: auth, categories, cities, clients, dashboard, layout, masters, notifications, orders, payments, profile, resources

### Тесты
- Feature: `CategoryTest`, `CityTest`, `MasterTest`, `OrderTest`, `ProfileTest`
- Job: `ConvertOrderPhotoJobTest`
- Action: `CreditMasterBalanceActionTest`
- Support: `PhotoConverterTest`
- Auth (Breeze): Authentication, EmailVerification, PasswordConfirmation, PasswordReset, PasswordUpdate
- API: `MasterLocationApiTest`
- Console команда `master:simulate-movement` для эмуляции живых координат без Flutter

---

## 🛠 Текущая работа (uncommitted)

Изменения в git status, ещё не закоммичены:

- Расширение OrderTask описанием (миграция `2026_05_16_134937_add_description_to_order_tasks_table.php`) — добавлено поле `description`
- Доработка `CreateOrderAction`, `UpdateOrderStatusAction`, `MasterController`, `Master`, `OrderTask`, `MasterRepository`, `OrderRepository`
- Локализация (ru/tk): masters, orders + новые файлы clients.php, payments.php
- Новые actions: `CompleteMasterOrderAction`, `CreateOrderTaskAction`, `CreditMasterBalanceAction`, `RequestMasterOtpAction`, `ResetMasterBalanceAction`, `StartMasterOrderAction`, `UploadTaskPhotoAction`, `VerifyMasterOtpAction`
- Новые контроллеры: `ClientController`, `PaymentController`, `Api/V1/Master*Controller`
- Events: `OrderCreated`
- Exceptions: `OtpException`
- Jobs (`app/Jobs/`) и Support (`app/Support/PhotoConverter`)
- Bruno коллекция для API
- Тесты: `ConvertOrderPhotoJobTest`, `CreditMasterBalanceActionTest`, и др.

---

## 📌 Планируемое / TODO в коде

- **SMS-шлюз для OTP**: `RequestMasterOtpAction::handle` сейчас просто `Log::info` — `// TODO: send via SMS gateway`
- **Приватный канал карты**: `Broadcast::channel('masters-map.{cityId}', fn() => true)` — комментарий "In production, switch to private channel + admin auth gate"
- **Открытый location ping**: `POST /master/{master}/location` без auth ("temporary open auth until OTP flow stabilises")
- **Клиенты (Clients)**: страница — пустая заглушка, бизнес-логика и БД не реализованы
- **Платежи (Payments)**: страница — пустая заглушка
- **Mobile App (Flutter)**: API готов, клиента нет
- **Edit Order**: в Routes есть только `index/show/store/destroy` — нет `update` для редактирования заказа
- **Реальная авторизация админа**: пока стандартный Breeze; ролей и Policies нет
