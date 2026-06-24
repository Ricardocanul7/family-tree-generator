<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellido')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->options([
                                'male' => 'Masculino',
                                'female' => 'Femenino',
                            ]),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento'),
                        Forms\Components\DatePicker::make('death_date')
                            ->label('Fecha de fallecimiento'),
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->directory('people')
                            ->avatar(),
                        Forms\Components\Textarea::make('biography')
                            ->label('Biografía')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Relaciones Familiares')
                    ->schema([
                        Forms\Components\Select::make('parents')
                            ->label('Padres')
                            ->multiple()
                            ->relationship('parents', 'first_name')
                            ->searchable()
                            ->preload()
                            ->options(fn () => Person::query()->orderBy('first_name')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->first_name} {$p->last_name}"])),
                        Forms\Components\Select::make('children')
                            ->label('Hijos')
                            ->multiple()
                            ->relationship('children', 'first_name')
                            ->searchable()
                            ->preload()
                            ->options(fn () => Person::query()->orderBy('first_name')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->first_name} {$p->last_name}"])),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (Person $record) => $record->photo_url),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Género')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Masculino',
                        'female' => 'Femenino',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Nacimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('death_date')
                    ->label('Fallecimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Hijos')
                    ->counts('children'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Género')
                    ->options([
                        'male' => 'Masculino',
                        'female' => 'Femenino',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_tree')
                    ->label('Ver Árbol')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Person $record) => route('family-tree.person', $record)),
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
