<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilestoneResource\Pages;
use App\Filament\Resources\MilestoneResource\RelationManagers;
use App\Models\Milestone;
use App\Models\Project;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                        ->hidden(fn (Closure $get) => !$get('is_paid'))
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
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
            'index' => Pages\ListMilestones::route('/'),
            'create' => Pages\CreateMilestone::route('/create'),
            'edit' => Pages\EditMilestone::route('/{record}/edit'),
        ];
    }
}
