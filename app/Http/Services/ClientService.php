<?php

namespace App\Http\Services;

use App\Exceptions\NotFoundException;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class ClientService
{
    /**
     * @param int $clientId
     * @return ClientResource
     * @throws NotFoundException
     */
    public function getClientById(int $clientId): ClientResource
    {
        Log::debug(
            'Start getting client by id',
            [
                'client_id' => $clientId
            ]
        );

        $client = Client::where('id', $clientId)->first();

        if (!$client) {
            throw NotFoundException::getInstance("Client with id $clientId not found");
        }

        return new ClientResource($client);
    }
}
