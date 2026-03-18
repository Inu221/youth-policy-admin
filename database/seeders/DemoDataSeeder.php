<?php

namespace Database\Seeders;

use App\Models\ActualEvent;
use App\Models\ActualEventFile;
use App\Models\ActualEventLink;
use App\Models\ActualEventParticipant;
use App\Models\ActualEventVerification;
use App\Models\AnnualPlan;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Participant;
use App\Models\PlannedEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password';

    private User $director;
    private User $analyst;

    /**
     * @var array<string, Department>
     */
    private array $departments = [];

    /**
     * @var array<string, User>
     */
    private array $users = [];

    /**
     * @var array<string, AnnualPlan>
     */
    private array $annualPlans = [];

    /**
     * @var array<string, PlannedEvent>
     */
    private array $plannedEvents = [];

    /**
     * @var array<string, ActualEvent>
     */
    private array $actualEvents = [];

    /**
     * @var array<string, Participant>
     */
    private array $participants = [];

    public function run(): void
    {
        Model::unguard();

        try {
            $this->seedUsersAndDepartments();
            $this->seedAnnualPlans();
            $this->seedPlannedEvents();
            $this->seedActualEvents();
            $this->seedParticipants();
            $this->seedActualEventParticipants();
            $this->syncParticipantCounters();
            $this->seedActualEventLinks();
            $this->seedActualEventFiles();
            $this->seedManualAuditEntries();
            $this->printCredentials();
        } finally {
            Model::reguard();
        }
    }

    private function seedUsersAndDepartments(): void
    {
        $this->director = $this->upsertUser('director', [
            'full_name' => 'Руководитель системы',
            'email' => 'director@example.com',
            'role' => User::ROLE_DIRECTOR,
            'department_id' => null,
            'phone' => '+7 900 100-00-01',
            'is_active' => true,
            'last_login_at' => now()->subHours(2),
        ]);

        $this->analyst = $this->upsertUser('analyst', [
            'full_name' => 'Главный аналитик',
            'email' => 'analyst@example.com',
            'role' => User::ROLE_ANALYST,
            'department_id' => null,
            'phone' => '+7 900 100-00-02',
            'is_active' => true,
            'last_login_at' => now()->subHours(5),
        ]);

        $this->departments['north'] = $this->upsertDepartment('Северск', [
            'name' => 'Управление молодежных инициатив города Северска',
            'status' => Department::STATUS_ACTIVE,
        ]);

        $this->departments['center'] = $this->upsertDepartment('Центр развития', [
            'name' => 'Центр развития добровольчества и патриотических проектов',
            'status' => Department::STATUS_ACTIVE,
        ]);

        $this->departments['south'] = $this->upsertDepartment('Южный округ', [
            'name' => 'Управление молодежной политики Южного округа',
            'status' => Department::STATUS_ACTIVE,
        ]);

        $this->departments['archive'] = $this->upsertDepartment('Архив', [
            'name' => 'Архивное управление молодежных программ',
            'status' => Department::STATUS_ARCHIVED,
        ]);

        $this->users['north_head'] = $this->upsertUser('head_north', [
            'full_name' => 'Анна Лебедева',
            'email' => 'head_north@example.com',
            'role' => User::ROLE_DEPARTMENT_HEAD,
            'department_id' => $this->departments['north']->id,
            'phone' => '+7 900 200-10-01',
            'is_active' => true,
            'last_login_at' => now()->subDay(),
        ]);

        $this->users['center_head'] = $this->upsertUser('head_center', [
            'full_name' => 'Максим Ермаков',
            'email' => 'head_center@example.com',
            'role' => User::ROLE_DEPARTMENT_HEAD,
            'department_id' => $this->departments['center']->id,
            'phone' => '+7 900 200-10-02',
            'is_active' => true,
            'last_login_at' => now()->subHours(10),
        ]);

        $this->users['south_head'] = $this->upsertUser('head_south', [
            'full_name' => 'Ольга Воронова',
            'email' => 'head_south@example.com',
            'role' => User::ROLE_DEPARTMENT_HEAD,
            'department_id' => $this->departments['south']->id,
            'phone' => '+7 900 200-10-03',
            'is_active' => true,
            'last_login_at' => now()->subHours(18),
        ]);

        $this->users['archive_head'] = $this->upsertUser('head_archive', [
            'full_name' => 'Сергей Новиков',
            'email' => 'head_archive@example.com',
            'role' => User::ROLE_DEPARTMENT_HEAD,
            'department_id' => $this->departments['archive']->id,
            'phone' => '+7 900 200-10-04',
            'is_active' => true,
            'last_login_at' => now()->subDays(4),
        ]);

        $this->users['inactive_analyst'] = $this->upsertUser('analyst_inactive', [
            'full_name' => 'Резервный аналитик',
            'email' => 'analyst_inactive@example.com',
            'role' => User::ROLE_ANALYST,
            'department_id' => null,
            'phone' => '+7 900 300-00-05',
            'is_active' => false,
            'last_login_at' => now()->subMonths(2),
        ]);

        foreach ([
            'north' => 'north_head',
            'center' => 'center_head',
            'south' => 'south_head',
            'archive' => 'archive_head',
        ] as $departmentKey => $userKey) {
            $department = $this->departments[$departmentKey];
            $department->fill([
                'responsible_user_id' => $this->users[$userKey]->id,
                'deleted_at' => null,
            ])->save();
        }
    }

    private function seedAnnualPlans(): void
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $this->annualPlans['north_current'] = $this->upsertAnnualPlan(
            $this->departments['north']->id,
            $currentYear,
            [
                'title' => "План мероприятий Северска на {$currentYear} год",
                'status' => AnnualPlan::STATUS_APPROVED,
                'created_by' => $this->users['north_head']->id,
                'approved_by' => $this->director->id,
                'approved_at' => CarbonImmutable::create($currentYear, 1, 16, 10, 30, 0),
                'approval_comment' => 'Утвержден после корректировки календаря ключевых событий.',
            ]
        );

        $this->annualPlans['center_current'] = $this->upsertAnnualPlan(
            $this->departments['center']->id,
            $currentYear,
            [
                'title' => "План проектов центра развития на {$currentYear} год",
                'status' => AnnualPlan::STATUS_DRAFT,
                'created_by' => $this->users['center_head']->id,
                'approved_by' => null,
                'approved_at' => null,
                'approval_comment' => 'Черновик находится на доработке после замечаний аналитика.',
            ]
        );

        $this->annualPlans['south_current'] = $this->upsertAnnualPlan(
            $this->departments['south']->id,
            $currentYear,
            [
                'title' => "Годовой план Южного округа на {$currentYear} год",
                'status' => AnnualPlan::STATUS_CLOSED,
                'created_by' => $this->users['south_head']->id,
                'approved_by' => $this->director->id,
                'approved_at' => CarbonImmutable::create($currentYear, 1, 12, 9, 0, 0),
                'approval_comment' => 'План исполнен и закрыт после итоговой сверки.',
            ]
        );

        $this->annualPlans['archive_previous'] = $this->upsertAnnualPlan(
            $this->departments['archive']->id,
            $previousYear,
            [
                'title' => "Архивный план молодежных программ на {$previousYear} год",
                'status' => AnnualPlan::STATUS_CLOSED,
                'created_by' => $this->users['archive_head']->id,
                'approved_by' => $this->director->id,
                'approved_at' => CarbonImmutable::create($previousYear, 2, 1, 11, 0, 0),
                'approval_comment' => 'Сохранен для исторической выборки и демонстрации архива.',
            ]
        );
    }

    private function seedPlannedEvents(): void
    {
        $year = now()->year;

        $this->plannedEvents['north_forum'] = $this->upsertPlannedEvent('Форум молодежных лидеров', [
            'annual_plan_id' => $this->annualPlans['north_current']->id,
            'description' => 'Ежегодный городской форум для лидеров молодежных объединений.',
            'planned_start_at' => CarbonImmutable::create($year, 2, 20, 10, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 2, 20, 17, 30, 0),
            'location_name' => 'Дворец молодежи "Север"',
            'location_url' => 'https://maps.example.com/seversk-forum',
            'responsible_user_id' => $this->users['north_head']->id,
            'planned_participants_count' => 10,
            'status' => PlannedEvent::STATUS_ARCHIVED,
            'created_by' => $this->users['north_head']->id,
            'updated_by' => $this->director->id,
        ]);

        $this->plannedEvents['north_school'] = $this->upsertPlannedEvent('Школа волонтера', [
            'annual_plan_id' => $this->annualPlans['north_current']->id,
            'description' => 'Подготовка новых муниципальных волонтерских координаторов.',
            'planned_start_at' => CarbonImmutable::create($year, 5, 14, 11, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 5, 14, 16, 0, 0),
            'location_name' => 'Центр молодежных инициатив',
            'location_url' => 'https://maps.example.com/volunteer-school',
            'responsible_user_id' => $this->users['north_head']->id,
            'planned_participants_count' => 35,
            'status' => PlannedEvent::STATUS_PLANNED,
            'created_by' => $this->users['north_head']->id,
            'updated_by' => null,
        ]);

        $this->plannedEvents['north_quest'] = $this->upsertPlannedEvent('Патриотический квест "Маршрут памяти"', [
            'annual_plan_id' => $this->annualPlans['north_current']->id,
            'description' => 'Маршрутная игра по памятным местам города для школьных команд.',
            'planned_start_at' => CarbonImmutable::create($year, 3, 27, 12, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 3, 27, 15, 30, 0),
            'location_name' => 'Мемориальный парк',
            'location_url' => 'https://maps.example.com/marshrut-pamyati',
            'responsible_user_id' => $this->users['north_head']->id,
            'planned_participants_count' => 60,
            'status' => PlannedEvent::STATUS_IN_PROGRESS,
            'created_by' => $this->users['north_head']->id,
            'updated_by' => $this->users['north_head']->id,
        ]);

        $this->plannedEvents['center_grants'] = $this->upsertPlannedEvent('Конкурс грантовых инициатив', [
            'annual_plan_id' => $this->annualPlans['center_current']->id,
            'description' => 'Очная защита молодежных проектных заявок.',
            'planned_start_at' => CarbonImmutable::create($year, 4, 3, 10, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 4, 3, 14, 0, 0),
            'location_name' => 'Конференц-зал центра развития',
            'location_url' => 'https://maps.example.com/grants-defense',
            'responsible_user_id' => $this->users['center_head']->id,
            'planned_participants_count' => 45,
            'status' => PlannedEvent::STATUS_PLANNED,
            'created_by' => $this->users['center_head']->id,
            'updated_by' => null,
        ]);

        $this->plannedEvents['center_media'] = $this->upsertPlannedEvent('Медиа-лаборатория для пресс-служб', [
            'annual_plan_id' => $this->annualPlans['center_current']->id,
            'description' => 'Практикум по съемке и упаковке контента для молодежных событий.',
            'planned_start_at' => CarbonImmutable::create($year, 4, 12, 11, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 4, 12, 17, 0, 0),
            'location_name' => 'Коворкинг "Медиа Точка"',
            'location_url' => 'https://maps.example.com/media-lab',
            'responsible_user_id' => $this->users['center_head']->id,
            'planned_participants_count' => 28,
            'status' => PlannedEvent::STATUS_PLANNED,
            'created_by' => $this->users['center_head']->id,
            'updated_by' => null,
        ]);

        $this->plannedEvents['south_slet'] = $this->upsertPlannedEvent('Слет студенческих объединений', [
            'annual_plan_id' => $this->annualPlans['south_current']->id,
            'description' => 'Окружная встреча студенческих советов и клубов.',
            'planned_start_at' => CarbonImmutable::create($year, 2, 14, 9, 30, 0),
            'planned_end_at' => CarbonImmutable::create($year, 2, 14, 18, 0, 0),
            'location_name' => 'Дом общественных организаций',
            'location_url' => 'https://maps.example.com/student-slet',
            'responsible_user_id' => $this->users['south_head']->id,
            'planned_participants_count' => 9,
            'status' => PlannedEvent::STATUS_ARCHIVED,
            'created_by' => $this->users['south_head']->id,
            'updated_by' => $this->director->id,
        ]);

        $this->plannedEvents['south_training'] = $this->upsertPlannedEvent('Тренинг по социальному проектированию', [
            'annual_plan_id' => $this->annualPlans['south_current']->id,
            'description' => 'Подготовка команд к летней грантовой кампании.',
            'planned_start_at' => CarbonImmutable::create($year, 3, 25, 10, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 3, 25, 15, 0, 0),
            'location_name' => 'Молодежный центр "Юг"',
            'location_url' => 'https://maps.example.com/social-design',
            'responsible_user_id' => $this->users['south_head']->id,
            'planned_participants_count' => 30,
            'status' => PlannedEvent::STATUS_CANCELLED,
            'created_by' => $this->users['south_head']->id,
            'updated_by' => $this->users['south_head']->id,
        ]);

        $this->plannedEvents['south_dialog'] = $this->upsertPlannedEvent('Открытый диалог с молодежью', [
            'annual_plan_id' => $this->annualPlans['south_current']->id,
            'description' => 'Публичная встреча молодежи округа с руководством.',
            'planned_start_at' => CarbonImmutable::create($year, 3, 22, 16, 0, 0),
            'planned_end_at' => CarbonImmutable::create($year, 3, 22, 18, 0, 0),
            'location_name' => 'Актовый зал администрации округа',
            'location_url' => 'https://maps.example.com/open-dialog',
            'responsible_user_id' => $this->users['south_head']->id,
            'planned_participants_count' => 55,
            'status' => PlannedEvent::STATUS_PLANNED,
            'created_by' => $this->users['south_head']->id,
            'updated_by' => null,
        ]);
    }

    private function seedActualEvents(): void
    {
        $year = now()->year;

        $this->actualEvents['north_forum'] = $this->upsertActualEvent('Форум молодежных лидеров 2026', [
            'department_id' => $this->departments['north']->id,
            'planned_event_id' => $this->plannedEvents['north_forum']->id,
            'description' => 'Состоялся с панельной дискуссией, практикумом и защитой молодежных инициатив.',
            'actual_start_at' => CarbonImmutable::create($year, 2, 20, 10, 0, 0),
            'actual_end_at' => CarbonImmutable::create($year, 2, 20, 17, 10, 0),
            'location_name' => 'Дворец молодежи "Север"',
            'location_url' => 'https://maps.example.com/seversk-forum',
            'responsible_user_id' => $this->users['north_head']->id,
            'planned_participants_snapshot' => $this->plannedEvents['north_forum']->planned_participants_count,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_ARCHIVED,
            'created_by' => $this->users['north_head']->id,
            'updated_by' => $this->director->id,
            'completed_at' => CarbonImmutable::create($year, 2, 20, 17, 10, 0),
        ]);

        $this->actualEvents['north_intensive'] = $this->upsertActualEvent('Добровольческий интенсив для кураторов', [
            'department_id' => $this->departments['north']->id,
            'planned_event_id' => null,
            'description' => 'Внеплановая рабочая сессия для кураторов муниципальных волонтерских штабов.',
            'actual_start_at' => CarbonImmutable::create($year, 3, 19, 11, 0, 0),
            'actual_end_at' => CarbonImmutable::create($year, 3, 19, 16, 0, 0),
            'location_name' => 'Ресурсный центр добровольчества',
            'location_url' => 'https://maps.example.com/intensive',
            'responsible_user_id' => $this->users['north_head']->id,
            'planned_participants_snapshot' => 12,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_IN_PROGRESS,
            'created_by' => $this->users['north_head']->id,
            'updated_by' => null,
            'completed_at' => null,
        ]);

        $this->actualEvents['north_quest'] = $this->upsertActualEvent('Патриотический квест "Маршрут памяти"', [
            'department_id' => $this->departments['north']->id,
            'planned_event_id' => $this->plannedEvents['north_quest']->id,
            'description' => 'Подготовлены маршрутные листы и подтверждены школьные команды-участники.',
            'actual_start_at' => CarbonImmutable::create($year, 3, 27, 12, 0, 0),
            'actual_end_at' => CarbonImmutable::create($year, 3, 27, 15, 30, 0),
            'location_name' => 'Мемориальный парк',
            'location_url' => 'https://maps.example.com/marshrut-pamyati',
            'responsible_user_id' => $this->users['north_head']->id,
            'planned_participants_snapshot' => $this->plannedEvents['north_quest']->planned_participants_count,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_PLANNED,
            'created_by' => $this->users['north_head']->id,
            'updated_by' => null,
            'completed_at' => null,
        ]);

        $this->actualEvents['center_grants'] = $this->upsertActualEvent('Очная защита грантовых инициатив', [
            'department_id' => $this->departments['center']->id,
            'planned_event_id' => null,
            'description' => 'Защита заявок прошла с замечаниями по пакету отчетных документов.',
            'actual_start_at' => CarbonImmutable::create($year, 3, 5, 10, 0, 0),
            'actual_end_at' => CarbonImmutable::create($year, 3, 5, 15, 20, 0),
            'location_name' => 'Конференц-зал центра развития',
            'location_url' => 'https://maps.example.com/grants-defense',
            'responsible_user_id' => $this->users['center_head']->id,
            'planned_participants_snapshot' => 40,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_ARCHIVED,
            'created_by' => $this->users['center_head']->id,
            'updated_by' => $this->analyst->id,
            'completed_at' => CarbonImmutable::create($year, 3, 5, 15, 20, 0),
        ]);

        $this->actualEvents['center_media'] = $this->upsertActualEvent('Медиа-лаборатория для пресс-служб', [
            'department_id' => $this->departments['center']->id,
            'planned_event_id' => $this->plannedEvents['center_media']->id,
            'description' => 'Регистрация участников открыта, материалы и программа опубликованы.',
            'actual_start_at' => CarbonImmutable::create($year, 4, 12, 11, 0, 0),
            'actual_end_at' => CarbonImmutable::create($year, 4, 12, 17, 0, 0),
            'location_name' => 'Коворкинг "Медиа Точка"',
            'location_url' => 'https://maps.example.com/media-lab',
            'responsible_user_id' => $this->users['center_head']->id,
            'planned_participants_snapshot' => $this->plannedEvents['center_media']->planned_participants_count,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_PLANNED,
            'created_by' => $this->users['center_head']->id,
            'updated_by' => null,
            'completed_at' => null,
        ]);

        $this->actualEvents['south_slet'] = $this->upsertActualEvent('Слет студенческих объединений Южного округа', [
            'department_id' => $this->departments['south']->id,
            'planned_event_id' => $this->plannedEvents['south_slet']->id,
            'description' => 'Итоговый окружной слет с презентацией лучших студенческих практик.',
            'actual_start_at' => CarbonImmutable::create($year, 2, 14, 9, 30, 0),
            'actual_end_at' => CarbonImmutable::create($year, 2, 14, 18, 0, 0),
            'location_name' => 'Дом общественных организаций',
            'location_url' => 'https://maps.example.com/student-slet',
            'responsible_user_id' => $this->users['south_head']->id,
            'planned_participants_snapshot' => $this->plannedEvents['south_slet']->planned_participants_count,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_ARCHIVED,
            'created_by' => $this->users['south_head']->id,
            'updated_by' => $this->director->id,
            'completed_at' => CarbonImmutable::create($year, 2, 14, 18, 0, 0),
        ]);

        $this->actualEvents['south_dialog'] = $this->upsertActualEvent('Открытый диалог с молодежью: март', [
            'department_id' => $this->departments['south']->id,
            'planned_event_id' => $this->plannedEvents['south_dialog']->id,
            'description' => 'Мероприятие перенесено из-за изменения графика выездной коллегии.',
            'actual_start_at' => CarbonImmutable::create($year, 3, 22, 16, 0, 0),
            'actual_end_at' => CarbonImmutable::create($year, 3, 22, 18, 0, 0),
            'location_name' => 'Актовый зал администрации округа',
            'location_url' => 'https://maps.example.com/open-dialog',
            'responsible_user_id' => $this->users['south_head']->id,
            'planned_participants_snapshot' => $this->plannedEvents['south_dialog']->planned_participants_count,
            'actual_participants_count' => 0,
            'status' => ActualEvent::STATUS_CANCELLED,
            'created_by' => $this->users['south_head']->id,
            'updated_by' => $this->users['south_head']->id,
            'completed_at' => null,
        ]);

        $this->upsertVerification($this->actualEvents['north_forum'], [
            'status' => ActualEventVerification::STATUS_APPROVED,
            'reviewer_id' => $this->analyst->id,
            'comment' => 'Отчет полный, ссылка на публикацию и материалы приложены.',
            'reviewed_at' => CarbonImmutable::create($year, 2, 21, 12, 15, 0),
        ]);

        $this->upsertVerification($this->actualEvents['north_intensive'], [
            'status' => ActualEventVerification::STATUS_PENDING,
            'reviewer_id' => null,
            'comment' => null,
            'reviewed_at' => null,
        ]);

        $this->upsertVerification($this->actualEvents['center_grants'], [
            'status' => ActualEventVerification::STATUS_REJECTED,
            'reviewer_id' => $this->analyst->id,
            'comment' => 'Не хватает подтверждающего файла и уточнения по итоговому числу участников.',
            'reviewed_at' => CarbonImmutable::create($year, 3, 6, 9, 45, 0),
        ]);

        $this->upsertVerification($this->actualEvents['south_slet'], [
            'status' => ActualEventVerification::STATUS_APPROVED,
            'reviewer_id' => $this->director->id,
            'comment' => 'Мероприятие принято без замечаний.',
            'reviewed_at' => CarbonImmutable::create($year, 2, 15, 10, 0, 0),
        ]);
    }

    private function seedParticipants(): void
    {
        $participants = [
            ['key' => 'starceva', 'full_name' => 'Алина Старцева', 'birth_date' => '2009-05-14', 'phone' => '+7 921 100-10-01', 'email' => 'alina.starceva@example.com'],
            ['key' => 'rumyantsev', 'full_name' => 'Илья Румянцев', 'birth_date' => '2008-11-03', 'phone' => '+7 921 100-10-02', 'email' => 'ilya.rumyantsev@example.com'],
            ['key' => 'orlova', 'full_name' => 'София Орлова', 'birth_date' => '2004-02-11', 'phone' => '+7 921 100-10-03', 'email' => 'sofia.orlova@example.com'],
            ['key' => 'vlasov', 'full_name' => 'Кирилл Власов', 'birth_date' => '2003-08-23', 'phone' => '+7 921 100-10-04', 'email' => 'kirill.vlasov@example.com'],
            ['key' => 'chernova', 'full_name' => 'Мария Чернова', 'birth_date' => '2002-06-19', 'phone' => '+7 921 100-10-05', 'email' => 'maria.chernova@example.com'],
            ['key' => 'titov', 'full_name' => 'Даниил Титов', 'birth_date' => '2001-12-01', 'phone' => '+7 921 100-10-06', 'email' => 'daniil.titov@example.com'],
            ['key' => 'frolova', 'full_name' => 'Елизавета Фролова', 'birth_date' => '1999-07-09', 'phone' => '+7 921 100-10-07', 'email' => 'elizaveta.frolova@example.com'],
            ['key' => 'belyaev', 'full_name' => 'Никита Беляев', 'birth_date' => '1998-10-30', 'phone' => '+7 921 100-10-08', 'email' => 'nikita.belyaev@example.com'],
            ['key' => 'kovaleva', 'full_name' => 'Полина Ковалева', 'birth_date' => '1997-03-17', 'phone' => '+7 921 100-10-09', 'email' => 'polina.kovaleva@example.com'],
            ['key' => 'zakharov', 'full_name' => 'Артем Захаров', 'birth_date' => '1996-01-25', 'phone' => '+7 921 100-10-10', 'email' => 'artem.zakharov@example.com'],
            ['key' => 'sokolova', 'full_name' => 'Виктория Соколова', 'birth_date' => '1994-04-12', 'phone' => '+7 921 100-10-11', 'email' => 'victoria.sokolova@example.com'],
            ['key' => 'emelyanov', 'full_name' => 'Роман Емельянов', 'birth_date' => '1991-09-15', 'phone' => '+7 921 100-10-12', 'email' => 'roman.emelyanov@example.com'],
            ['key' => 'mironova', 'full_name' => 'Ольга Миронова', 'birth_date' => '1989-07-07', 'phone' => '+7 921 100-10-13', 'email' => 'olga.mironova@example.com'],
            ['key' => 'abramov', 'full_name' => 'Сергей Абрамов', 'birth_date' => '1987-02-20', 'phone' => null, 'email' => 'sergey.abramov@example.com'],
            ['key' => 'gromova', 'full_name' => 'Наталья Громова', 'birth_date' => '1984-11-28', 'phone' => '+7 921 100-10-15', 'email' => null],
            ['key' => 'denisov', 'full_name' => 'Павел Денисов', 'birth_date' => '1981-05-06', 'phone' => '+7 921 100-10-16', 'email' => 'pavel.denisov@example.com'],
            ['key' => 'melnikova', 'full_name' => 'Ирина Мельникова', 'birth_date' => '1979-01-18', 'phone' => '+7 921 100-10-17', 'email' => 'irina.melnikova@example.com'],
            ['key' => 'zhukov', 'full_name' => 'Андрей Жуков', 'birth_date' => '1975-08-27', 'phone' => '+7 921 100-10-18', 'email' => 'andrey.zhukov@example.com'],
            ['key' => 'samoylova', 'full_name' => 'Юлия Самойлова', 'birth_date' => '2000-05-12', 'phone' => null, 'email' => 'yulia.samoylova@example.com'],
            ['key' => 'korneev', 'full_name' => 'Максим Корнеев', 'birth_date' => '1995-09-04', 'phone' => '+7 921 100-10-20', 'email' => 'maxim.korneev@example.com'],
        ];

        foreach ($participants as $participantData) {
            $key = $participantData['key'];
            unset($participantData['key']);

            $participant = Participant::withTrashed()->firstOrNew([
                'full_name' => $participantData['full_name'],
            ]);

            $participant->fill([
                ...$participantData,
                'attendance_count' => 0,
                'deleted_at' => null,
            ])->save();

            $this->participants[$key] = $participant->fresh();
        }
    }

    private function seedActualEventParticipants(): void
    {
        $this->attachParticipants('north_forum', [
            'starceva',
            'orlova',
            'vlasov',
            'chernova',
            'titov',
            'frolova',
            'belyaev',
            'kovaleva',
            'zakharov',
            'sokolova',
        ]);

        $this->attachParticipants('north_intensive', [
            'orlova',
            'chernova',
            'frolova',
            'kovaleva',
            'emelyanov',
            'mironova',
        ]);

        $this->attachParticipants('center_grants', [
            'rumyantsev',
            'vlasov',
            'titov',
            'belyaev',
            'zakharov',
            'emelyanov',
            'abramov',
        ]);

        $this->attachParticipants('south_slet', [
            'starceva',
            'rumyantsev',
            'orlova',
            'sokolova',
            'emelyanov',
            'mironova',
            'gromova',
            'denisov',
            'melnikova',
        ]);
    }

    private function syncParticipantCounters(): void
    {
        foreach ($this->participants as $participant) {
            $participant->update([
                'attendance_count' => ActualEventParticipant::where('participant_id', $participant->id)->count(),
            ]);
        }

        foreach ($this->actualEvents as $key => $actualEvent) {
            $participantCount = ActualEventParticipant::where('actual_event_id', $actualEvent->id)->count();

            $actualEvent->fill([
                'actual_participants_count' => $participantCount,
                'completed_at' => $actualEvent->status === ActualEvent::STATUS_ARCHIVED
                    ? ($actualEvent->actual_end_at ?? $actualEvent->actual_start_at)
                    : null,
            ])->save();

            $this->actualEvents[$key] = $actualEvent->fresh();
        }
    }

    private function seedActualEventLinks(): void
    {
        $links = [
            ['event' => 'north_forum', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://vk.com/wall-100001_2001', 'is_primary' => true, 'created_by' => $this->users['north_head']->id],
            ['event' => 'north_forum', 'type' => ActualEventLink::TYPE_MEDIA, 'url' => 'https://disk.example.com/forum-liderov-photo', 'is_primary' => false, 'created_by' => $this->users['north_head']->id],
            ['event' => 'north_intensive', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://t.me/youth_policy/154', 'is_primary' => true, 'created_by' => $this->users['north_head']->id],
            ['event' => 'north_quest', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://vk.com/wall-100001_2044', 'is_primary' => true, 'created_by' => $this->users['north_head']->id],
            ['event' => 'center_grants', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://vk.com/wall-100002_876', 'is_primary' => true, 'created_by' => $this->users['center_head']->id],
            ['event' => 'center_grants', 'type' => ActualEventLink::TYPE_OTHER, 'url' => 'https://docs.example.com/grants-defense-minutes', 'is_primary' => false, 'created_by' => $this->analyst->id],
            ['event' => 'center_media', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://t.me/youth_media/77', 'is_primary' => true, 'created_by' => $this->users['center_head']->id],
            ['event' => 'south_slet', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://vk.com/wall-100003_610', 'is_primary' => true, 'created_by' => $this->users['south_head']->id],
            ['event' => 'south_slet', 'type' => ActualEventLink::TYPE_MEDIA, 'url' => 'https://disk.example.com/student-slet-video', 'is_primary' => false, 'created_by' => $this->users['south_head']->id],
            ['event' => 'south_dialog', 'type' => ActualEventLink::TYPE_SOCIAL_POST, 'url' => 'https://vk.com/wall-100003_645', 'is_primary' => true, 'created_by' => $this->users['south_head']->id],
        ];

        foreach ($links as $linkData) {
            $event = $this->actualEvents[$linkData['event']];

            ActualEventLink::query()->updateOrCreate(
                [
                    'actual_event_id' => $event->id,
                    'url' => $linkData['url'],
                ],
                [
                    'link_type' => $linkData['type'],
                    'is_primary' => $linkData['is_primary'],
                    'created_by' => $linkData['created_by'],
                    'created_at' => now()->subDays(10),
                ]
            );
        }
    }

    private function seedActualEventFiles(): void
    {
        Storage::disk('public')->makeDirectory('event_files');

        $files = [
            [
                'event' => 'north_forum',
                'original_name' => 'program-forum-liderov.pdf',
                'mime_type' => 'application/pdf',
                'content' => "Demo PDF content for forum schedule.\n",
                'uploaded_by' => $this->users['north_head']->id,
                'created_at' => CarbonImmutable::create(now()->year, 2, 20, 18, 0, 0),
            ],
            [
                'event' => 'north_forum',
                'original_name' => 'attendance-forum-liderov.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'content' => "Demo spreadsheet content for attendance.\n",
                'uploaded_by' => $this->users['north_head']->id,
                'created_at' => CarbonImmutable::create(now()->year, 2, 20, 18, 5, 0),
            ],
            [
                'event' => 'center_grants',
                'original_name' => 'remarks-grants-defense.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'content' => "Demo DOCX placeholder with analyst remarks.\n",
                'uploaded_by' => $this->analyst->id,
                'created_at' => CarbonImmutable::create(now()->year, 3, 6, 9, 55, 0),
            ],
            [
                'event' => 'south_slet',
                'original_name' => 'photo-report-student-slet.jpg',
                'mime_type' => 'image/jpeg',
                'content' => "Demo image placeholder for the student gathering.\n",
                'uploaded_by' => $this->users['south_head']->id,
                'created_at' => CarbonImmutable::create(now()->year, 2, 14, 18, 15, 0),
            ],
        ];

        foreach ($files as $fileData) {
            $event = $this->actualEvents[$fileData['event']];
            $storedName = Str::slug(pathinfo($fileData['original_name'], PATHINFO_FILENAME))
                . '-' . $event->id . '.' . pathinfo($fileData['original_name'], PATHINFO_EXTENSION);
            $filePath = 'event_files/' . $storedName;

            Storage::disk('public')->put($filePath, $fileData['content']);

            ActualEventFile::query()->updateOrCreate(
                [
                    'actual_event_id' => $event->id,
                    'original_name' => $fileData['original_name'],
                ],
                [
                    'stored_name' => $storedName,
                    'file_path' => $filePath,
                    'mime_type' => $fileData['mime_type'],
                    'file_size' => strlen($fileData['content']),
                    'uploaded_by' => $fileData['uploaded_by'],
                    'created_at' => $fileData['created_at'],
                ]
            );
        }
    }

    private function seedManualAuditEntries(): void
    {
        $entries = [
            [
                'user_id' => $this->director->id,
                'entity_type' => Department::class,
                'entity_id' => $this->departments['north']->id,
                'action' => 'updated',
                'old_values' => ['responsible_user_id' => null],
                'new_values' => ['responsible_user_id' => $this->users['north_head']->id],
                'description' => 'Назначено ответственное лицо подразделения Северск',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder/Local Demo',
                'created_at' => now()->subDays(20),
            ],
            [
                'user_id' => $this->director->id,
                'entity_type' => User::class,
                'entity_id' => $this->users['center_head']->id,
                'action' => 'created',
                'old_values' => null,
                'new_values' => ['role' => User::ROLE_DEPARTMENT_HEAD, 'department_id' => $this->departments['center']->id],
                'description' => 'Создан пользователь Максим Ермаков',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder/Local Demo',
                'created_at' => now()->subDays(19),
            ],
            [
                'user_id' => $this->director->id,
                'entity_type' => AnnualPlan::class,
                'entity_id' => $this->annualPlans['north_current']->id,
                'action' => 'updated',
                'old_values' => ['status' => AnnualPlan::STATUS_DRAFT],
                'new_values' => ['status' => AnnualPlan::STATUS_APPROVED],
                'description' => 'План Северска переведен в статус "Утвержден"',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder/Local Demo',
                'created_at' => now()->subDays(18),
            ],
            [
                'user_id' => $this->analyst->id,
                'entity_type' => ActualEvent::class,
                'entity_id' => $this->actualEvents['center_grants']->id,
                'action' => 'updated',
                'old_values' => ['verification' => 'pending'],
                'new_values' => ['verification' => 'rejected'],
                'description' => 'Аналитик отклонил отчет по очной защите грантовых инициатив',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder/Local Demo',
                'created_at' => now()->subDays(12),
            ],
        ];

        foreach ($entries as $entry) {
            AuditLog::query()->updateOrCreate(
                [
                    'entity_type' => $entry['entity_type'],
                    'entity_id' => $entry['entity_id'],
                    'action' => $entry['action'],
                    'description' => $entry['description'],
                ],
                $entry
            );
        }
    }

    private function printCredentials(): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->info('Демо-данные загружены.');
        $this->command->line('Логин для всех демо-аккаунтов: пароль `password`.');
        $this->command->line('director / analyst / head_north / head_center / head_south / head_archive');
    }

    private function attachParticipants(string $eventKey, array $participantKeys): void
    {
        $event = $this->actualEvents[$eventKey];
        $addedBy = $event->responsible_user_id;

        foreach ($participantKeys as $participantKey) {
            ActualEventParticipant::query()->firstOrCreate(
                [
                    'actual_event_id' => $event->id,
                    'participant_id' => $this->participants[$participantKey]->id,
                ],
                [
                    'added_by' => $addedBy,
                    'created_at' => $event->actual_start_at->subDays(1),
                ]
            );
        }
    }

    private function upsertUser(string $username, array $attributes): User
    {
        $user = User::withTrashed()->firstOrNew(['username' => $username]);
        $isNew = ! $user->exists;

        $user->fill([
            'username' => $username,
            'name' => $attributes['full_name'],
            'full_name' => $attributes['full_name'],
            'email' => $attributes['email'],
            'role' => $attributes['role'],
            'department_id' => $attributes['department_id'],
            'phone' => $attributes['phone'],
            'is_active' => $attributes['is_active'],
            'last_login_at' => $attributes['last_login_at'],
            'email_verified_at' => $user->email_verified_at ?? now(),
            'permissions' => ['platform.index' => true],
            'deleted_at' => null,
        ]);

        if ($isNew || empty($user->password)) {
            $user->password = Hash::make(self::DEFAULT_PASSWORD);
            $user->remember_token = Str::random(10);
        }

        $user->save();

        return $user->fresh();
    }

    private function upsertDepartment(string $shortName, array $attributes): Department
    {
        $department = Department::withTrashed()->firstOrNew(['short_name' => $shortName]);
        $department->fill([
            'name' => $attributes['name'],
            'short_name' => $shortName,
            'status' => $attributes['status'],
            'deleted_at' => null,
        ])->save();

        return $department->fresh();
    }

    private function upsertAnnualPlan(int $departmentId, int $year, array $attributes): AnnualPlan
    {
        $annualPlan = AnnualPlan::withTrashed()->firstOrNew([
            'department_id' => $departmentId,
            'year' => $year,
        ]);

        $annualPlan->fill([
            'title' => $attributes['title'],
            'status' => $attributes['status'],
            'created_by' => $attributes['created_by'],
            'approved_by' => $attributes['approved_by'],
            'approved_at' => $attributes['approved_at'],
            'approval_comment' => $attributes['approval_comment'],
            'deleted_at' => null,
        ])->save();

        return $annualPlan->fresh();
    }

    private function upsertPlannedEvent(string $title, array $attributes): PlannedEvent
    {
        $plannedEvent = PlannedEvent::withTrashed()->firstOrNew([
            'title' => $title,
            'planned_start_at' => $attributes['planned_start_at'],
        ]);
        $plannedEvent->fill([
            'annual_plan_id' => $attributes['annual_plan_id'],
            'title' => $title,
            'description' => $attributes['description'],
            'planned_start_at' => $attributes['planned_start_at'],
            'planned_end_at' => $attributes['planned_end_at'],
            'location_name' => $attributes['location_name'],
            'location_url' => $attributes['location_url'],
            'responsible_user_id' => $attributes['responsible_user_id'],
            'planned_participants_count' => $attributes['planned_participants_count'],
            'status' => $attributes['status'],
            'created_by' => $attributes['created_by'],
            'updated_by' => $attributes['updated_by'],
            'deleted_at' => null,
        ])->save();

        return $plannedEvent->fresh();
    }

    private function upsertActualEvent(string $title, array $attributes): ActualEvent
    {
        $actualEvent = ActualEvent::withTrashed()->firstOrNew([
            'title' => $title,
            'actual_start_at' => $attributes['actual_start_at'],
        ]);
        $actualEvent->fill([
            'department_id' => $attributes['department_id'],
            'planned_event_id' => $attributes['planned_event_id'],
            'title' => $title,
            'description' => $attributes['description'],
            'actual_start_at' => $attributes['actual_start_at'],
            'actual_end_at' => $attributes['actual_end_at'],
            'location_name' => $attributes['location_name'],
            'location_url' => $attributes['location_url'],
            'responsible_user_id' => $attributes['responsible_user_id'],
            'planned_participants_snapshot' => $attributes['planned_participants_snapshot'],
            'actual_participants_count' => $attributes['actual_participants_count'],
            'status' => $attributes['status'],
            'created_by' => $attributes['created_by'],
            'updated_by' => $attributes['updated_by'],
            'completed_at' => $attributes['completed_at'],
            'deleted_at' => null,
        ])->save();

        return $actualEvent->fresh();
    }

    private function upsertVerification(ActualEvent $actualEvent, array $attributes): void
    {
        ActualEventVerification::query()->updateOrCreate(
            ['actual_event_id' => $actualEvent->id],
            [
                'status' => $attributes['status'],
                'reviewer_id' => $attributes['reviewer_id'],
                'comment' => $attributes['comment'],
                'reviewed_at' => $attributes['reviewed_at'],
            ]
        );
    }
}
