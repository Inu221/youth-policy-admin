<?php

namespace App\Orchid\Layouts;

use App\Models\ActualEvent;
use App\Models\ActualEventVerification;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\TextArea;

class ActualEventVerificationLayout extends Rows
{
    /**
     * Views.
     *
     * @return array
     */
    public function fields(): array
    {
        $actualEvent = $this->query->get('actualEvent');
        $user = auth()->user();

        // Only show for analyst and director
        if (!$user->isAnalyst() && !$user->isDirector()) {
            return [];
        }

        $verification = $actualEvent->verification;
        $primaryLink = $actualEvent->links()->where('link_type', 'social_post')->where('is_primary', true)->first();

        $fields = [
            Label::make('verification_section')
                ->title('Верификация мероприятия')
                ->value('<hr class="my-3">'),
        ];

        // Show social link
        if ($primaryLink) {
            $fields[] = Label::make('social_link_label')
                ->title('Ссылка на пост в соцсети')
                ->value('<a href="' . e($primaryLink->url) . '" target="_blank" class="btn btn-sm btn-link">'
                    . e($primaryLink->url) . ' <i class="bi bi-box-arrow-up-right"></i></a>');
        }

        // Show current status
        if ($verification) {
            $statusBadge = match ($verification->status) {
                ActualEventVerification::STATUS_APPROVED => '<span class="badge bg-success">Одобрено</span>',
                ActualEventVerification::STATUS_REJECTED => '<span class="badge bg-danger">Отклонено</span>',
                default => '<span class="badge bg-warning">Ожидает проверки</span>',
            };

            $fields[] = Label::make('verification_status')
                ->title('Статус верификации')
                ->value($statusBadge);

            if ($verification->reviewer) {
                $fields[] = Label::make('verification_reviewer')
                    ->title('Проверил')
                    ->value($verification->reviewer->full_name ?? $verification->reviewer->name);
            }

            if ($verification->reviewed_at) {
                $fields[] = Label::make('verification_date')
                    ->title('Дата проверки')
                    ->value($verification->reviewed_at->format('d.m.Y H:i'));
            }

            if ($verification->comment) {
                $fields[] = Label::make('verification_comment')
                    ->title('Комментарий')
                    ->value(nl2br(e($verification->comment)));
            }

            // Show action buttons if pending
            if ($verification->status === ActualEventVerification::STATUS_PENDING) {
                $fields[] = TextArea::make('verification_new_comment')
                    ->title('Комментарий (необязательно)')
                    ->rows(3)
                    ->placeholder('Добавьте комментарий к решению...');
            }
        } else {
            $fields[] = Label::make('verification_status')
                ->title('Статус верификации')
                ->value('<span class="badge bg-secondary">Не создана</span>');

            $fields[] = TextArea::make('verification_new_comment')
                ->title('Комментарий (необязательно)')
                ->rows(3)
                ->placeholder('Добавьте комментарий к решению...');
        }

        return $fields;
    }
}
