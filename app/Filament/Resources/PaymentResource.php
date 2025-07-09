<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Client;
use App\Models\Milestone;
use App\Models\Payment;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Client')
                    ->options(Client::where('user_id', auth()->id())->pluck('name', 'id'))
                    ->required()
                    ->reactive(),

                Select::make('project_id')
                    ->label('Project')
                    ->options(function (callable $get) {
                        return Project::where('client_id', $get('client_id'))
                            ->pluck('title', 'id');
                    })
                    ->searchable()
                    ->reactive(),
                Select::make('milestone_id')
                    ->label('Milestone (Optional)')
                    ->options(function (callable $get) {
                        return Milestone::where('project_id', $get('project_id'))
                            ->where('is_paid', false)
                            ->pluck('title', 'id');
                    })
                    ->searchable()
                    ->hidden(fn (callable $get) => !$get('project_id')),

                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('$'),

                Select::make('method')
                    ->options([
                        'bank' => 'Bank Transfer',
                        'paypal' => 'PayPal',
                        'card' => 'Credit Card',
                        'cash' => 'Cash',
                        'crypto' => 'Cryptocurrency'
                    ])
                    ->required(),

                DatePicker::make('payment_date')
                    ->default(now())
                    ->required(),

                TextInput::make('reference')
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('project.title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('milestone.title')
                    ->label('Milestone'),

                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('method')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank' => 'Bank Transfer',
                        'paypal' => 'PayPal',
                        'card' => 'Credit Card',
                        'cash' => 'Cash',
                        'crypto' => 'Cryptocurrency'
                    })->badge(),

                TextColumn::make('payment_date')
                    ->date()
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title'),

                Filter::make('this_month')
                    ->label('This Month Payments')
                    ->query(fn (Builder $query) => $query->whereBetween('payment_date', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ]))
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
