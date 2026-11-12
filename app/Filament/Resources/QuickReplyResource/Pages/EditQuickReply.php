<?php

namespace App\Filament\Resources\QuickReplyResource\Pages;

use App\Filament\Resources\QuickReplyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuickReply extends EditRecord
{
    protected static string $resource = QuickReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
