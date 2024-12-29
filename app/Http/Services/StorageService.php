<?php

namespace App\Http\Services;

use App\Exceptions\NotFoundException;
use App\Models\Storage;

class StorageService
{
    public function getStorageById(int $storageId)
    {
        $storage = Storage::where('id', $storageId)->first();

        if (empty($storage)) {
            throw NotFoundException::getInstance("Storage with id {$storageId} not found");
        }

        return $storage;
    }
}
