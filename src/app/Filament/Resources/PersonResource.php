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
                Forms\Components\Section::make(__('Personal Information'))
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('First Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('Last Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->label(__('Gender'))
                            ->options([
                                'male' => __('Male'),
                                'female' => __('Female'),
                            ]),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label(__('Birth Date')),
                        Forms\Components\DatePicker::make('death_date')
                            ->label(__('Death Date')),
                        Forms\Components\FileUpload::make('photo')
                            ->label(__('Photo'))
                            ->image()
                            ->directory('people')
                            ->avatar(),
                        Forms\Components\Textarea::make('biography')
                            ->label(__('Biography'))
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make(__('Family Relations'))
                    ->schema([
                        Forms\Components\Select::make('parents')
                            ->label(__('Parents'))
                            ->multiple()
                            ->relationship('parents', 'first_name')
                            ->searchable()
                            ->preload()
                            ->options(fn () => Person::query()->orderBy('first_name')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->first_name} {$p->last_name}"])),
                        Forms\Components\Select::make('children')
                            ->label(__('Children'))
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
                    ->label(__('First Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('Last Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => __('Male'),
                        'female' => __('Female'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label(__('Birth'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('death_date')
                    ->label(__('Death'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('children_count')
                    ->label(__('Children'))
                    ->counts('children'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label(__('Gender'))
                    ->options([
                        'male' => __('Male'),
                        'female' => __('Female'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_tree')
                    ->label(__('View Tree'))
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
