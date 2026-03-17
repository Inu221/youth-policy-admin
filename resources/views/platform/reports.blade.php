<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">ТОП-20 участников</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ФИО</th>
                                <th>Возраст</th>
                                <th class="text-center">Посещений</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topParticipants as $index => $participant)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $participant->full_name }}</td>
                                <td>
                                    @if($participant->birth_date)
                                        {{ now()->year - $participant->birth_date->year }} лет
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $participant->attendance_count }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Мероприятия по статусам ({{ $currentYear }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Статус</th>
                                <th class="text-center">Количество</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eventsByStatus as $status)
                            <tr>
                                <td>
                                    @switch($status->status)
                                        @case('planned')
                                            <span class="badge bg-info">Запланировано</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-warning">В процессе</span>
                                            @break
                                        @case('archived')
                                            <span class="badge bg-success">Завершено</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Отменено</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-center">{{ $status->count }}</td>
                            </tr>
                            @endforeach
                            @if($eventsByStatus->isEmpty())
                            <tr>
                                <td colspan="2" class="text-center text-muted">Нет данных</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Возрастная структура участников</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Возрастная группа</th>
                                <th class="text-center">Количество</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ageDistribution as $age)
                            <tr>
                                <td>{{ $age->age_group }}</td>
                                <td class="text-center">{{ $age->count }}</td>
                            </tr>
                            @endforeach
                            @if($ageDistribution->isEmpty())
                            <tr>
                                <td colspan="2" class="text-center text-muted">Нет данных</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(count($participantsByDepartment) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Участники по подразделениям ({{ $currentYear }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Подразделение</th>
                                <th class="text-center">Всего участников</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($participantsByDepartment as $dept)
                            <tr>
                                <td>{{ $dept->short_name ?: $dept->name }}</td>
                                <td class="text-center">{{ $dept->total_participants }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
