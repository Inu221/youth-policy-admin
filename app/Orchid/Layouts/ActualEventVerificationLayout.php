<?php

namespace App\Orchid\Layouts;

use App\Models\ActualEventVerification;
use Illuminate\Support\HtmlString;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class ActualEventVerificationLayout extends Rows
{
    /**
     * @return array
     */
    public function fields(): array
    {
        $actualEvent = $this->query->get('actualEvent');
        $user = auth()->user();

        if (!$user->isAnalyst() && !$user->isDirector()) {
            return [];
        }

        $verification = $actualEvent->verification;
        $primaryLink = $actualEvent->links()
            ->where('link_type', 'social_post')
            ->where('is_primary', true)
            ->first();

        $fields = [
            Label::make('verification_section')
                ->title('Верификация мероприятия')
                ->value(new HtmlString('<hr class="my-3">')),
        ];

        if ($primaryLink) {
            $fields[] = Label::make('social_link_label')
                ->title('Ссылка на пост в соцсети')
                ->value(new HtmlString(
                    '<a href="' . e($primaryLink->url) . '" target="_blank" class="btn btn-sm btn-link">'
                    . e($primaryLink->url) . ' <i class="bi bi-box-arrow-up-right"></i></a>'
                ));
        }

        if ($verification) {
            $statusBadge = match ($verification->status) {
                ActualEventVerification::STATUS_APPROVED => '<span class="badge bg-success">Одобрено</span>',
                ActualEventVerification::STATUS_REJECTED => '<span class="badge bg-danger">Отклонено</span>',
                default => '<span class="badge bg-warning">Ожидает проверки</span>',
            };

            $fields[] = Label::make('verification_status')
                ->title('Статус верификации')
                ->value(new HtmlString($statusBadge));

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
                    ->value(new HtmlString(nl2br(e($verification->comment))));
            }

            if ($verification->status === ActualEventVerification::STATUS_PENDING) {
                $fields[] = TextArea::make('verification_new_comment')
                    ->title('Комментарий (необязательно)')
                    ->rows(3)
                    ->placeholder('Добавьте комментарий к решению...');
            }
        } else {
            $fields[] = Label::make('verification_status')
                ->title('Статус верификации')
                ->value(new HtmlString('<span class="badge bg-secondary">Не создана</span>'));

            $fields[] = TextArea::make('verification_new_comment')
                ->title('Комментарий (необязательно)')
                ->rows(3)
                ->placeholder('Добавьте комментарий к решению...');
        }

        return $fields;
    }
}
