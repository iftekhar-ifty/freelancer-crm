<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\TextInput::make('title')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state = null ) {
                                $set('slug', str()->slug($state));
                            })
                            ->required(),
                        Forms\Components\TextInput::make('slug')->required(),
                        Forms\Components\MarkdownEditor::make('description')->required(),
                    ]),
                    Section::make('Milestones')
                        ->schema([
                            Repeater::make('milestones')
                                ->relationship()
                                ->schema([
                                    TextInput::make('title')
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('amount')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required(),

                                    DatePicker::make('due_date')
                                        ->required(),

                                    Toggle::make('is_paid')
                                        ->reactive()
                                ])
                                ->columns(2)
                        ])->visible(fn (Forms\Get $get) => $get('payment_type') == 'milestone')
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'planned' => 'planned',
                                'active' => 'active',
                                'completed' => 'completed',
                                'archived' => 'archived',
                            ]),
                        Forms\Components\Select::make('payment_type')
                            ->reactive()
                            ->options([
                                'fixed' => 'fixed',
                                'milestone' => 'milestone',
                                'hourly' => 'hourly',
                                'recurring' => 'recurring',
                            ]),
                        Forms\Components\TextInput::make('total_price')
                            ->visible(fn(Forms\Get $get) => $get('payment_type') == 'fixed' || $get('payment_type') == 'milestone' || $get('payment_type') == 'recurring'),
                        Forms\Components\TextInput::make('hourly_rate')
                            ->visible(fn(Forms\Get $get) => $get('payment_type') == 'hourly'),
                        Forms\Components\DatePicker::make('deadline')->required(),

                    ])
                ])->columnSpan(1)


            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('client.name')->searchable(),
                Tables\Columns\TextColumn::make('total_price')->searchable(),
                Tables\Columns\TextColumn::make('hourly_rate')->searchable(),
                Tables\Columns\TextColumn::make('deadline')->badge()->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')->label('Client')->relationship('client', 'name'),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
