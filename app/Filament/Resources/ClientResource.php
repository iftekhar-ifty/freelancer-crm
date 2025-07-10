<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->required()->email(),
                    Forms\Components\TextInput::make('phone')->required(),
                    Forms\Components\TextInput::make('company')->required(),
                    Forms\Components\TextInput::make('details'),
                    Forms\Components\Select::make('from')
                        ->required()
                        ->options([
                            'Fiver' => 'Fiver',
                            'Upwork' => 'Upwork',
                            'Social Media' => 'Social Media',
                            'Others' => 'Others',
                        ])
                ])->columns(2)
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('company')->searchable(),
                Tables\Columns\TextColumn::make('details')->searchable(),
                Tables\Columns\TextColumn::make('from')->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Social Media' => 'gray',
                        'Fiver' => 'warning',
                        'Upwork' => 'success',
                        'Others' => 'danger',
                    }),

            ])
            ->filters([
                SelectFilter::make('from')
                    ->label('From where')
                    ->options([
                        'Social Media' => 'Social Media',
                        'Fiver' => 'Fiver',
                        'Upwork' => 'Upwork',
                        'Others' => 'Others',
                    ])
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
            'index' => Pages\ListClients::route('/'),
//            'create' => Pages\CreateClient::route('/create'),
//            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
