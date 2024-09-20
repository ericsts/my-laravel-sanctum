<?php

namespace App\Filament\Resources\TokenResource\Pages;

use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\TokenResource;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ManageRecords;

class ManageTokens extends ManageRecords
{
    protected static string $resource = TokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('md')
                ->form([
                    Select::make('user_id')
                        ->options(User::all()->pluck('name', 'id')),
                    TextInput::make('token_name')
                        ->required(),
                    DatePicker::make('expires_at'),
                ])
                ->action(function (array $data): void {
                    $user = User::find($data['user_id']);
                    $plainTextToken = $user->createToken(
                        $data['token_name'],
                        ['*'],
                        $data['expires_at'] ? Carbon::createFromFormat('Y-m-d', $data['expires_at']) : null
                    )->plainTextToken;

                    $this->replaceMountedAction('showToken', [
                        'token' => $plainTextToken,
                    ]);

                    Notification::make()
                        ->title('Token created successfully')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function showTokenAction(): Action
    {
        return Action::make('token')
            ->fillForm( fn(array $arguments) => [
                'token' => $arguments['token'],
            ])
            ->form([
                TextInput::make('token')
                    ->helperText('Copy this token and use it to authenticate your requests'),
            ])
            ->modalHeading('Copy the token to your clipboard')
            ->modalIcon('heroicon-m-clipboard')
            ->modalAlignment(Alignment::Center)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false);
    }
}
