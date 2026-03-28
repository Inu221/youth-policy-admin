<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <strong>Период отчета:</strong> {{ $currentYear }} год
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Рейтинг муниципалитетов по выполнению планов</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="80">#</th>
                                <th>Подразделение</th>
                                <th class="text-center" width="150">План</th>
                                <th class="text-center" width="150">Факт</th>
                                <th class="text-center" width="200">Выполнение</th>
                                <th class="text-center" width="120">Процент</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentRankings as $index => $dept)
                            <tr>
                                <td class="text-center">
                                    @if($index === 0)
                                        <span class="badge bg-warning text-dark">🥇 1</span>
                                    @elseif($index === 1)
                                        <span class="badge bg-secondary">🥈 2</span>
                                    @elseif($index === 2)
                                        <span class="badge" style="background-color: #cd7f32;">🥉 3</span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $dept['name'] }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $dept['total'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $dept['completed'] }}</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 25px;">
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
                                </td>
                                <td class="text-center">
                                    <strong class="
                                        @if($dept['percentage'] >= 75) text-success
                                        @elseif($dept['percentage'] >= 50) text-info
                                        @elseif($dept['percentage'] >= 25) text-warning
                                        @else text-danger
                                        @endif
                                    ">{{ $dept['percentage'] }}%</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Нет данных для отображения
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-secondary">
            <h6 class="alert-heading">Легенда:</h6>
            <ul class="mb-0">
                <li><span class="badge bg-success">≥ 75%</span> - Отличное выполнение</li>
                <li><span class="badge bg-info">50-74%</span> - Хорошее выполнение</li>
                <li><span class="badge bg-warning">25-49%</span> - Требует внимания</li>
                <li><span class="badge bg-danger">< 25%</span> - Критическое состояние</li>
            </ul>
        </div>
    </div>
</div>
