<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Models\Post;
use Doctrine\DBAL\Schema\Schema;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Markdown;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Post Content')->Schema([
                    TextInput::make('title')
                        ->live()
                        ->required()
                        ->minLength(1)
                        ->maxLength(150)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'edit') {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        }),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true),
                    Select::make('category')
                        ->multiple()
                        ->relationship('categories', 'name')
                        ->searchable(),
                    MarkdownEditor::make(
                        'content'
                    )->required()
                        ->columnSpanFull(),
                ])->columns(2)->columnSpan(2),
                Section::make('Meta')->schema([
                    FileUpload::make('image')
                        ->required()
                        ->directory('posts/thumbnails'),
                    Checkbox::make('feature')->nullable(),
                    DateTimePicker::make('published_at')->nullable(),
                    Select::make('user_id')
                        ->relationship('author', 'name')
                        ->searchable()
                        ->required(),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->square(),
                TextColumn::make('title')
                    ->limit(30)
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('slug')->sortable()->searchable()->limit(10),
                TextColumn::make('author.name')->sortable()->searchable(),
                TextColumn::make('published_at')
                    ->label('Posted at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('categories.name')
                    ->listWithLineBreaks(),
                TextColumn::make('comments_count')
                    ->label('Comments')
                    ->counts('comments')
                    ->sortable()
                    ->alignment(Alignment::Center),
                IconColumn::make('featured')
                    ->boolean()
                    ->trueColor('info')
                    ->falseColor('warning')
                    ->alignment(Alignment::Center),
            ])
            ->filters([
                SelectFilter::make('Category')
                    ->relationship('categories', 'name')
                    ->indicator('Category'),
                SelectFilter::make('Author')
                    ->relationship('author', 'name')
                    ->indicator('Auther'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ComponentsSection::make('Post')->schema([
                    TextEntry::make('content'),
                    TextEntry::make('categories.name'),
                ]),
                ComponentsSection::make('Comments')->schema([
                    TextEntry::make('Comments.comment'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
