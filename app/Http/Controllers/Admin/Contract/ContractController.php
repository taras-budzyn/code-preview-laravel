<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\PauseContract;
use App\Notifications\ContractSendNotification;
use App\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ContractController extends Controller
{
    public function status(Contract $contract, Request $request): RedirectResponse
    {
        $message = null;

        if (is_null($contract->number)) {
            $contract
                ->update(['number' => Str::random(64)]);
        }

        switch ($request->get('status')) {
            case Contract::STATUS_SUBMITTED:
                Notification::route('mail', $contract->user->email)
                    ->notify(new ContractSendNotification($contract));

                if ($contract->user) {
                    User::where('id', $contract->user->id)->update(['status' => 'active']);
                }

                $message = __('Text');

                break;

            case Contract::STATUS_SIGNED:
                return redirect(route(
                    'pausecontract.create',
                    ['type' => PauseContract::TYPE_SIGN, 'contract' => $contract->id]
                ));
        }

        $contract
            ->update(['status' => $request->get('status')]);
        Alert::success($message)->flash();

        return redirect(route('contract.index'));
    }
}
