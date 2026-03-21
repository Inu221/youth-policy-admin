<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Всего мероприятий (план)</h6>
                <h2 class="card-title">{{ $totalPlannedEvents }}</h2>
                <p class="text-muted small mb-0">за {{ $currentYear }} год</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Завершено (факт)</h6>
                <h2 class="card-title text-success">{{ $completedEvents }}</h2>
                <p class="text-muted small mb-0">
                    @if($totalPlannedEvents > 0)
                        {{ round(($completedEvents / $totalPlannedEvents) * 100) }}% от плана
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Годовых планов</h6>
                <h2 class="card-title">{{ $totalPlans }}</h2>
                <p class="text-muted small mb-0">на {{ $currentYear }} год</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Всего участников</h6>
                <h2 class="card-title text-primary">{{ number_format($totalParticipants, 0, ',', ' ') }}</h2>
                <p class="text-muted small mb-0">посещений мероприятий</p>
            </div>
        </div>
    </div>
</div>

@if(count($departmentProgress) > 0)
<div class="row mt-4">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Мероприятия за 30 дней</h5>
            </div>
            <div class="card-body">
                <canvas id="eventsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Участники за 30 дней</h5>
            </div>
            <div class="card-body">
                <canvas id="participantsChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Прогресс по подразделениям</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Подразделение</th>
                                <th class="text-center">Всего мероприятий (план)</th>
                                <th class="text-center">Завершено (факт)</th>
                                <th class="text-center">Прогресс</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departmentProgress as $dept)
                            <tr>
                                <td>{{ $dept['name'] }}</td>
                                <td class="text-center">{{ $dept['total'] }}</td>
                                <td class="text-center">{{ $dept['completed'] }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                            <div class="progress-bar 
                                                @if($dept['percentage'] >= 75) bg-success
                                                @elseif($dept['percentage'] >= 50) bg-info
                                                @elseif($dept['percentage'] >= 25) bg-warning
                                                @else bg-danger
                                                @endif
                                            "
                                            role="progressbar"
                                            style="width: {{ $dept['percentage'] }}%;"
                                            aria-valuenow="{{ $dept['percentage'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                                {{ $dept['percentage'] }}%
                                            </div>
                                        </div>
                                    </div>
                                </td>
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

<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6 class="alert-heading">Быстрые ссылки</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('platform.annual-plans') }}" class="btn btn-sm btn-outline-primary">Годовые планы</a>
                <a href="{{ route('platform.planned-events') }}" class="btn btn-sm btn-outline-primary">Плановые мероприятия</a>
                <a href="{{ route('platform.actual-events') }}" class="btn btn-sm btn-outline-primary">Фактические мероприятия</a>
                <a href="{{ route('platform.participants') }}" class="btn btn-sm btn-outline-primary">Участники</a>
                @can('viewAny', \App\Models\Department::class)
                    <a href="{{ route('platform.departments') }}" class="btn btn-sm btn-outline-primary">Подразделения</a>
                @endcan
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData ?? ['dates' => [], 'events' => [], 'participants' => []]);

    const eventsCtx = document.getElementById('eventsChart');
    if (eventsCtx) {
        new Chart(eventsCtx, {
            type: 'line',
            data: {
                labels: chartData.dates,
                datasets: [{
                    label: 'Мероприятия',
                    data: chartData.events,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    const participantsCtx = document.getElementById('participantsChart');
    if (participantsCtx) {
        new Chart(participantsCtx, {
            type: 'bar',
            data: {
                labels: chartData.dates,
                datasets: [{
                    label: 'Участники',
                    data: chartData.participants,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
