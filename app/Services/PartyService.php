<?php

namespace App\Services;

use App\Models\Party;

class PartyService
{
    /**
     * Create a new party.
     */
    public function createParty(array $data): Party
    {
        return Party::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'address' => $data['address'],
        ]);
    }

    /**
     * Update an existing party.
     */
    public function updateParty(Party $party, array $data): Party
    {
        $party->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'address' => $data['address'],
        ]);

        return $party;
    }
}
