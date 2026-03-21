<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Layouts\Rows;

class ProfilePasswordLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Password::make('old_password')
                ->placeholder(__('Enter the current password'))
                ->title(__('Current password'))
                ->help('Это ваш текущий пароль.'),

            Password::make('password')
                ->placeholder(__('Enter the password to be set'))
                ->title(__('New password')),

            Password::make('password_confirmation')
                ->placeholder(__('Enter the password to be set'))
                ->title(__('Confirm new password'))
                ->help('Надежный пароль должен быть достаточно длинным и содержать буквы и цифры.'),
        ];
    }
}
