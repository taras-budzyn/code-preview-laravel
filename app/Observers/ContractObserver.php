<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Models\Contract;
use App\Models\ContractGroupHistory;
use App\User;
use App\Models\Nvm;

class ContractObserver
{
    public function created(Contract $contract): void
    {
        $this->syncUserMemberRelationshipByContract($contract);
    }

    public function updated(Contract $contract): void
    {
        $changes = $contract->getChanges();

        if (isset($changes['member_id'])) {
            $this->syncUserMemberRelationshipByContract($contract);
        }

        if (isset($changes['user_id'])) {
            $this->syncUserMemberRelationshipByContract($contract);
            $this->syncUserMemberRelationship(User::find($contract->getOriginal('user_id')));
        }
    }

    public function deleted(Contract $contract): void
    {
        $this->syncUserMemberRelationshipByContract($contract);
        $this->deleteRelatedNvsContractsAndFiles($contract);
    }

    public function restored(Contract $contract): void
    {
        $this->syncUserMemberRelationshipByContract($contract);
    }

    public function forceDeleted(Contract $contract): void
    {
        $this->syncUserMemberRelationshipByContract($contract);
    }

    private function syncUserMemberRelationshipByContract(Contract $contract): void
    {
        $this->syncUserMemberRelationship($contract->user()->first());
    }

    private function syncUserMemberRelationship(User $user): void
    {
        $memberIds = Contract::whereUserId($user->id)->pluck('member_id')->filter();

        $user->members()->sync($memberIds);
    }

    private function deleteRelatedNvsContractsAndFiles(Contract $contract): void {
        $toDelete = Nvm::where('member_contract_id', $contract->id)->get();
        foreach($toDelete as $nvm) {
            $nvm->delete();
        }
    }
}
