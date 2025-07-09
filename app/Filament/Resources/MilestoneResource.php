<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilestoneResource\Pages;
use App\Filament\Resources\MilestoneResource\RelationManagers;
use App\Models\Milestone;
use App\Models\Payment;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Select::make('project_id')
                        ->label('Project')
                        ->options(Project::query()->where('payment_type', 'milestone')
                            ->where('user_id', auth()->id())
                            ->pluck('title', 'id'))
                        ->required()
                        ->searchable()
                        ->reactive(),

                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->prefix('$'),
                    DatePicker::make('due_date')
                        ->required(),

                    Toggle::make('is_paid')
                        ->label('Mark as Paid')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('payment_date', $state ? now()->format('Y-m-d') : null);
                        }),

                    DatePicker::make('payment_date')
//                        ->hidden(fn (Closure $get) => !$get('is_paid'))
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->boolean()
                    ->label('Paid'),

                TextColumn::make('payment_date')
                    ->date()
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(Project::query()->where('payment_type', 'milestone')
                        ->where('user_id', auth()->id())
                        ->pluck('title', 'id')),

                Filter::make('overdue')
                    ->label('Overdue Milestones')
                    ->query(fn (Builder $query) => $query->where('due_date', '<', now())
                        ->where('is_paid', false)),
            ])
            ->actions([
                 Action::make('mark_paid')
                ->label('Mark Paid')
                ->icon('heroicon-o-check-circle')
                ->action(function (Milestone $record) {
                    $record->update([
                        'is_paid' => true,
                        'payment_date' => now()
                    ]);

                    // Automatically create payment record
                    Payment::create([
                        'user_id' => auth()->id(),
                        'client_id' => $record->project->client_id,
                        'project_id' => $record->project_id,
                        'milestone_id' => $record->id,
                        'amount' => $record->amount,
                        'payment_date' => now(),
                        'method' => 'bank' // Default, can be changed
                    ]);
                })
                ->hidden(fn (Milestone $record) => $record->is_paid),
//                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMilestones::route('/'),
            'create' => Pages\CreateMilestone::route('/create'),
            'edit' => Pages\EditMilestone::route('/{record}/edit'),
        ];
    }
}
