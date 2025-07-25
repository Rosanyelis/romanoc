<?php

namespace App\Filament\Resources;

use App\Models\Law;
use Filament\Forms;
use Filament\Tables;
use App\Models\Article;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ArticleResolution;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ArticleResolutionResource\Pages;
use App\Filament\Resources\ArticleResolutionResource\RelationManagers;

class ArticleResolutionResource extends Resource
{
    protected static ?string $model = ArticleResolution::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Leyes y Reglamentos';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Resoluciones';
    protected static ?string $pluralNavigationLabel = 'Resoluciones';
    protected static ?string $pluralModelLabel = 'Resoluciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                # ley o reglamento
                Forms\Components\Select::make('law_id')
                    ->label('Ley o Reglamento')
                    ->options(Law::all()->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (callable $set) {
                        $set('article_id', null);
                    })
                    ->default(function ($record) {
                        return $record?->article?->law_id;
                    })
                    ->required()
                    ->placeholder('Seleccione una ley o reglamento'),
                # Articulos segun la ley seleccionada
                Forms\Components\Select::make('article_id')
                    ->label('Artículo de la Ley o Reglamento')
                    ->options(function (callable $get, $record) {
                        $lawId = $get('law_id');
                        if (!$lawId) {
                            // Si estamos editando y no hay law_id seleccionado, usar el del registro
                            if ($record && $record->article) {
                                $lawId = $record->article->law_id;
                            }
                        }
                        if (!$lawId) {
                            return [];
                        }
                        return Article::where('law_id', $lawId)->pluck('article_title', 'id');
                    })
                    ->searchable()
                    ->default(function ($record) {
                        return $record?->article_id;
                    })
                    ->required()
                    ->placeholder('Seleccione un artículo')
                    ->visible(fn (callable $get) => $get('law_id') !== null),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Archivo de la Resolución')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('url')
                    ->label('Cargar Archivo de la Resolución (pdf, docx, doc)')
                    ->disk('public')
                    ->directory('resolutions')
                    ->visibility('public')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('article.law.name')
                    ->label('Ley o Reglamento')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('article.article_title')
                    ->label('Artículo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Archivo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->icon('heroicon-o-document-text')
                    ->label('Archivo')
                    ->url(fn (ArticleResolution $record) => Storage::url($record->url))
                    ->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Creado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListArticleResolutions::route('/'),
            'create' => Pages\CreateArticleResolution::route('/create'),
            'edit' => Pages\EditArticleResolution::route('/{record}/edit'),
        ];
    }
}
