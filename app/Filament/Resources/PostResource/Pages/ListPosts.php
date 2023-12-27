<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make(),
            'Featured' => Tab::make()
                ->query(fn (Builder $query): Builder => $query->where('featured', true)),
            'unFeatured' => Tab::make()
                ->query(fn (Builder $query): Builder => $query->where('featured', false)),
            'This week' => Tab::make()
                ->query(fn (Builder $query): Builder => $query->whereDate('created_at', '<=', now()->subWeek())),

        ];
    }
}
