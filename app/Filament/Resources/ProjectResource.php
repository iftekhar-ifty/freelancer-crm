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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
//                                ->visible(fn (Closure $get) => $get('payment_type') === 'milestone')
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
                        ])
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('budget')
                            ->label('Budget Type')
                            ->dehydrated(false)
                            ->options([
                                'total' => 'total',
                                'hourly' => 'hourly',
                            ])->reactive(),
                        Forms\Components\TextInput::make('total_price')
                            ->visible(fn(Forms\Get $get) => $get('budget') == 'total')
                            ->integer(),
                        Forms\Components\TextInput::make('hourly_rate')
                            ->visible(fn(Forms\Get $get) => $get('budget') == 'hourly')
                            ->integer(),
                        Forms\Components\DatePicker::make('deadline')->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'planned' => 'planned',
                                'active' => 'active',
                                'completed' => 'completed',
                                'archived' => 'archived',
                            ]),
                        Forms\Components\Select::make('payment_type')
                            ->options([
                                'fixed' => 'fixed',
                                'milestone' => 'milestone',
                                'hourly' => 'hourly',
                                'recurring' => 'recurring',
                            ]),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
