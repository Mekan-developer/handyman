# TASKS

Актуальное состояние задач по проекту (обновлено 2026-06-15).

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
- Единый API error handler в `bootstrap/app.php` → локализованный JSON по заголовку locale

### Admin Panel — CRUD-модули (все полностью реализованы)

| Модуль | Маршруты | Vue | Тест |
|--------|---------|-----|------|
| Oblasts | ✅ CRUD | ✅ Index + Modal | ✅ OblastTest |
| Regions | ✅ CRUD | ✅ Index + Modal | ✅ RegionTest |
| Cities | ✅ CRUD | ✅ Index + Modal | ✅ CityTest |
| Categories | ✅ CRUD | ✅ Index + Modal | ✅ CategoryTest |
| CategoryContent | ✅ upsert | ✅ CategoryContentModal | ✅ CategoryContentTest |
| Masters | ✅ CRUD + Map + Trajectory + Balance | ✅ Index + Map | ✅ MasterTest |
| Orders | ✅ index/show/store/update/destroy/assign/setPrice/updateStatus/masterTrajectory | ✅ Index + Show + EditOrderModal | ✅ OrderTest |
| Clients | ✅ CRUD + toggleBlock | ✅ Index + Modal | ✅ ClientTest |
| Banners | ✅ CRUD + toggle | ✅ Index + Modal | ✅ BannerTest |
| Notifications | ✅ read/readAll/delete/deleteAll | ✅ есть в AdminLayout | ❌ нет |
| Dashboard | ✅ реальные данные через DashboardRepository | ✅ 4 stat-карточки | ❌ нет |
| Payments | ❌ заглушка | ✅ страница-заглушка | ❌ нет |
| Users | ✅ CRUD + роли (Administrator/Manager/Operator) | ✅ Index + Modal | ✅ UserTest (26 тестов) |

### Mobile API (api/v1)

#### Master API
- OTP auth: request-otp, verify-otp, logout
- GET/PATCH /api/v1/master/me
- Orders: index, show, start, complete
- Tasks: store + photo upload (WebP фоновая конвертация)
- Location: POST /api/v1/master/{master}/location

#### Client API
- OTP auth: request-otp, verify-otp, logout, complete-registration
- GET/PATCH /api/v1/client/me (ClientProfileController)
- Catalog: GET oblasts, regions, cities, categories, category content, banners
- Orders: index, show, store, cancel

### Backend Infrastructure
- Модели: User (с ролями), Oblast, Region, City, Category, CategoryContent, CategoryContentImage, Master, MasterLocation, Order, OrderTask, OrderPhoto, Client, Banner
- Enums: `OrderStatus` (`app/OrderStatus.php`), `PaymentModel` (`app/PaymentModel.php`), `UserRole` (`app/Enums/UserRole.php`)
- Exceptions: OrderException, OtpException, MasterDisabledException, ApiException
- Events: OrderCreated, OrderStatusChanged, MasterAssigned, MasterLocationUpdated
- Jobs: ConvertOrderPhotoJob, ConvertTaskPhotoJob (WebP, tries=3, backoff=30)
- Repositories: Oblast, Region, City, Category, CategoryContent, Master, Order, Client, Banner, Dashboard, User
- Actions: полный набор для всех модулей (46+ классов, включая Create/Update/DeleteUserAction)
- Policies: `UserPolicy` (`app/Policies/`) — авторизация управления пользователями
- Middleware: `CheckRole` (`role:administrator,manager`) — роль-based контроль доступа
- Resources: Admin + API V1 (Master + Client) + UserResource
- Lang: `lang/{ru,tk}/` — api, auth, banners, categories, cities, clients, dashboard, layout, masters, notifications, oblasts, orders, payments, profile, regions, resources, users, validation (18 файлов каждый)

### UI компоненты
- PhoneInput.vue — поле телефона с +993, форматом `XX XX-XX-XX`
- `formatPhone` утилита в utils/formatPhone.js
- Применено во всех формах (MasterFormModal, ClientFormModal, CreateOrderModal, EditOrderModal) и таблицах (Masters/Index, Clients/Index, Orders/Index, Orders/Show)

### Тесты (Feature)
- ✅ OblastTest, RegionTest, CityTest, CategoryTest, CategoryContentTest
- ✅ MasterTest, OrderTest, BannerTest, ClientTest
- ✅ CreditMasterBalanceActionTest, ConvertOrderPhotoJobTest, PhotoConverterTest
- ✅ MasterLocationApiTest, MasterAuthTest
- ✅ ClientOrderCancelTest, ClientCatalogCategoryContentTest
- ✅ ApiExceptionHandlingTest

---

## 🚧 Текущая незакоммиченная работа

- **User Management** — полностью реализовано, не закоммичено
  - Migration, Enum, Model, Repository, Actions, Policy, Middleware
  - UserController, StoreUserRequest, UpdateUserRequest, UserResource
  - Routes реструктурированы (роль-based доступ)
  - Users/Index.vue, UserFormModal.vue, AdminLayout.vue обновлён
  - 26 тестов в UserTest.php

---

## ❌ Не сделано / TODO

| # | Задача | Приоритет |
|---|--------|-----------|
| 1 | **Payments модуль** — нет модели, миграции, Repository, логики | Средний |
| 2 | **SMS-шлюз OTP** — `RequestMasterOtpAction` и `RequestClientOtpAction` только Log::info | Низкий |
| 3 | **Приватные Broadcast каналы** — `orders` и `masters-map.*` публичные | Низкий |
| 4 | **Auth для location ping** — `/master/{master}/location` без auth | Низкий |
| 5 | **OrderStatus enum location** — лежит в `app/OrderStatus.php`, а не `app/Enums/` | Низкий |
| 6 | **Dashboard тесты** — нет тестов для DashboardController/Repository | Низкий |
| 7 | **Notification тесты** — нет тестов для NotificationController | Низкий |
| 8 | **Flutter-приложение** — API готов, мобильного клиента нет | — |

---

## Полезные команды

```bash
php artisan route:list --except-vendor
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run dev
php artisan master:simulate-movement
```
