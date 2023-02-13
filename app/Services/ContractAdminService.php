<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Contract;
use App\Models\PauseContract;
use App\User;

class ContractAdminService
{
    /** @var ContractFormSaverService */
    private $clientContractService;

    public function __construct(ContractFormSaverService $clientContractService)
    {
        $this->clientContractService = $clientContractService;
    }

    public function store(array $data): void
    {
        $data['contract_source'] = PauseContract::SOURCE_BUDORA;
        $this->save($data);
    }

    public function update(array $data, int $contractId): void
    {
        $contract = Contract::whereId($contractId)->first();
        $this->save($data, $contract);
    }

    private function save(array $data, ?Contract $contract = null): void
    {
        if (isset($data['user_id']) && $data['user_id']) {
            $user = User::findOrFail($data['user_id']);
            $data['user_id'] = $user->id;
        } else {
            $user = new User();
            $data['user_status'] = 'waiting_approval';
        }

        foreach (['agreement_events', 'agreement_personal_data', 'agreement_photo', 'agreement_personal_data_third_company'] as $key) {
            $data[$key] = !isset($data[$key]) || null === $data[$key] ? null : (bool) $data[$key];
        }

        $user = $this->clientContractService->saveUser($data, $user);

        if (!isset($data['user_id']) || !$data['user_id']) {
            $user->assignRole(RolesAndPermissionsService::CLIENT_ROLE);
        }

        $member = $this->clientContractService->saveMember($data);
        $this->clientContractService->saveContract($data, $member, $user, $contract);
        $this->clientContractService->updateUserMemberRelationship($user, $member, $data);
    }
}
