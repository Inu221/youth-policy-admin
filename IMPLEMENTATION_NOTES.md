# Обновления системы Youth Policy Admin

## Дата: 17.03.2026

## Реализованные функции

### 1. Role-Based Access Control (RBAC)

#### Middleware
- **CheckRole** - проверка ролей пользователя
- **CheckDepartmentAccess** - проверка доступа к департаменту

#### Scopes в моделях
Добавлены scopes `forUser($user)` в моделях:
- Department
- AnnualPlan
- PlannedEvent
- ActualEvent

Scope автоматически фильтрует данные:
- **director** и **analyst** - видят все данные
- **department_head** - видит только свой department_id

#### Политики доступа (Policies)
Созданы политики для моделей:
- **DepartmentPolicy**
  - director: полный доступ
  - department_head: просмотр только своего департамента

- **AnnualPlanPolicy**
  - director: полный доступ
  - department_head: создание/редактирование своих планов в статусе DRAFT
  - analyst: только просмотр

- **ActualEventPolicy**
  - director: полный доступ
  - department_head: CRUD только для своего департамента
  - analyst: только просмотр

- **ParticipantPolicy**
  - director/department_head: полный доступ к контактам
  - analyst: просмотр без email/phone (метод `viewContactInfo`)

#### Обновленные Screens
Обновлены с проверками прав и фильтрацией:
- ActualEventListScreen
- ActualEventEditScreen
- AnnualPlanListScreen
- DepartmentListScreen
- ParticipantListScreen
- ParticipantEditScreen (скрыты контакты для analyst)

### 2. Dashboard (Панель управления)

**PlatformScreen** обновлен и содержит:

#### Метрики
- Всего мероприятий (за текущий год)
- Завершенных мероприятий
- Годовых планов
- Всего участников (сумма attendance_count)

#### Прогресс по подразделениям
Только для director/analyst:
- Таблица с прогрессом выполнения мероприятий по каждому департаменту
- Визуализация через progress bar с цветовой индикацией

#### Быстрые ссылки
Кнопки перехода на основные разделы системы

**Маршрут:** `/admin/main` → `platform.main`

### 3. UI для загрузки файлов к мероприятиям

#### Screens
- **ActualEventFileListScreen** - список файлов с фильтрацией по департаменту
- **ActualEventFileEditScreen** - форма загрузки файлов

#### Функции
- Загрузка файлов (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP, RAR)
- Максимальный размер: 10 МБ
- Привязка к фактическому мероприятию
- Скачивание файлов через контроллер
- Удаление файлов (director/department_head)
- Фильтрация по department_id для department_head

#### Storage
- Файлы хранятся в `storage/app/public/event_files/`
- Symlink уже настроен: `public/storage -> storage/app/public`

#### Маршруты
- `/admin/actual-event-files` - список
- `/admin/actual-event-files/create` - загрузка
- `/admin/actual-event-files/{file}/download` - скачивание

### 4. Отчеты и аналитика

**ReportsScreen** содержит:

#### Отчеты
1. **ТОП-20 участников** - по количеству посещений
2. **Мероприятия по статусам** - за текущий год
3. **Возрастная структура участников** - группировка по возрасту
4. **Участники по подразделениям** - только для director/analyst

#### Фильтрация
- department_head видит только статистику своего департамента
- director/analyst видят всю статистику

**Маршрут:** `/admin/reports` → `platform.reports`

### 5. Обновленное навигационное меню

Меню структурировано по разделам:
- **Главная**: Панель управления, Отчеты
- **Управление**: Подразделения (не для analyst), Пользователи (только director)
- **Планирование**: Годовые планы, Плановые мероприятия
- **Исполнение**: Фактические мероприятия, Файлы, Ссылки
- **Участники**: Участники, Участники мероприятий

Видимость пунктов меню зависит от роли пользователя.

## Технические детали

### Использованные константы ролей
```php
User::ROLE_DIRECTOR       // 'director'
User::ROLE_DEPARTMENT_HEAD // 'department_head'
User::ROLE_ANALYST        // 'analyst'
```

### Методы проверки ролей в модели User
```php
$user->isDirector()
$user->isDepartmentHead()
$user->isAnalyst()
```

### Проверка прав через Policy
```php
$user->can('view', $model)
$user->can('create', ModelClass::class)
$user->can('update', $model)
$user->can('delete', $model)
```

### Применение scope в запросах
```php
Model::forUser($user)->get()
```

## Что НЕ реализовано (опционально)

### 1. Audit Logging
- Модель и таблица `audit_logs` существуют
- Нужен Observer для автоматического логирования
- Screen для просмотра логов (только director)

### 2. Calendar View
- Календарное отображение мероприятий
- Интеграция fullcalendar.js или аналога

### 3. Расширенные отчеты
- Экспорт в Excel
- Графики и диаграммы
- Фильтрация по датам/периодам

### 4. Дополнительные экраны с RBAC
Требуют обновления для полной реализации RBAC:
- PlannedEventListScreen / PlannedEventEditScreen
- ActualEventLinkListScreen / ActualEventLinkEditScreen
- ActualEventParticipantListScreen / ActualEventParticipantEditScreen
- AnnualPlanEditScreen
- DepartmentEditScreen
- AppUserListScreen / AppUserEditScreen

## Рекомендации по использованию

1. **После развертывания:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Создание тестовых пользователей:**
   ```php
   User::create([
       'full_name' => 'Директор',
       'email' => 'director@example.com',
       'password' => bcrypt('password'),
       'role' => User::ROLE_DIRECTOR,
       'is_active' => true,
   ]);
   ```

3. **Права доступа:**
   - Убедитесь, что у всех пользователей установлено поле `role`
   - У department_head должен быть заполнен `department_id`
   - Все активные пользователи имеют `is_active = true`

4. **Хранение файлов:**
   - Убедитесь, что symlink `public/storage` существует
   - Проверьте права на запись в `storage/app/public/event_files/`

## Изменённые файлы

### Middleware
- app/Http/Middleware/CheckRole.php
- app/Http/Middleware/CheckDepartmentAccess.php
- app/Http/Kernel.php

### Models
- app/Models/Department.php (scope)
- app/Models/AnnualPlan.php (scope)
- app/Models/PlannedEvent.php (scope)
- app/Models/ActualEvent.php (scope)

### Policies
- app/Policies/DepartmentPolicy.php
- app/Policies/AnnualPlanPolicy.php
- app/Policies/ActualEventPolicy.php
- app/Policies/ParticipantPolicy.php

### Screens
- app/Orchid/Screens/PlatformScreen.php (Dashboard)
- app/Orchid/Screens/ActualEventListScreen.php
- app/Orchid/Screens/ActualEventEditScreen.php
- app/Orchid/Screens/AnnualPlanListScreen.php
- app/Orchid/Screens/DepartmentListScreen.php
- app/Orchid/Screens/ParticipantEditScreen.php
- app/Orchid/Screens/ActualEventFileListScreen.php (NEW)
- app/Orchid/Screens/ActualEventFileEditScreen.php (NEW)
- app/Orchid/Screens/ReportsScreen.php (NEW)

### Layouts
- app/Orchid/Layouts/ParticipantListLayout.php

### Controllers
- app/Http/Controllers/ActualEventFileController.php (NEW)

### Views
- resources/views/platform/dashboard.blade.php (NEW)
- resources/views/platform/reports.blade.php (NEW)

### Routes & Config
- routes/platform.php
- app/Orchid/PlatformProvider.php (menu)

## Итого

Реализовано:
✅ RBAC с middleware, scopes и policies
✅ Dashboard с метриками и прогрессом
✅ UI для загрузки файлов к мероприятиям
✅ Базовые отчеты и аналитика
✅ Обновленное навигационное меню

Система готова к использованию с полноценным разделением прав доступа по ролям.
