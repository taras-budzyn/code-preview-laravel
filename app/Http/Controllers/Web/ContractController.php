<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebContractRequest;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Group;
use App\Models\GroupSetting;
use App\Models\PauseContract;
use App\Models\Place;
use App\Services\ContractFormSaverService;
use App\Services\ContractPrice;
use App\Services\MemberSubscriptionService;
use App\Services\RolesAndPermissionsService;
use App\Services\TranslationService;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContractController extends Controller
{
    /** @var MemberSubscriptionService */
    private $memberSubscriptionService;

    /** @var ContractPrice */
    private $contractPrice;

    /** @var ContractFormSaverService */
    private $clientContractService;

    public function __construct(
        MemberSubscriptionService $memberSubscriptionService,
        ContractPrice $contractPrice,
        ContractFormSaverService $clientContractService
    ) {
        $this->memberSubscriptionService = $memberSubscriptionService;
        $this->contractPrice = $contractPrice;
        $this->clientContractService = $clientContractService;
    }

    public function create(): View
    {
        $timesPerWeekOptions = [];
        $availableSubscriptions = [];
        $price = '0.0';

        if ((int) old('group_type_id') && old('age_range') && old('city')) {
            $availableSubscriptions = $this->memberSubscriptionService->findAvailableMap(
                (int) old('group_type_id'),
                old('age_range'),
                old('city'),
                old('group_level'),
                (int) old('contract_template_id')
            );

            if ((int) old('times_per_week')) {
                $membersSubscription = $availableSubscriptions->firstWhere('times_per_week', (int) old('times_per_week'));
                $price = $membersSubscription['price'] ?? $price;
            }

            $timesPerWeekOptions = $availableSubscriptions->pluck('times_per_week', 'times_per_week')->toArray();
        }

        return view('web.contract.show', [
            'classOptions' => array_combine(Contract::CLASSES, Contract::CLASSES),
            'groupTypeOptions' => GroupSetting::all()->pluck('name', 'id')->all(),
            'ageRangeOptions' => TranslationService::translateAdminList(Group::AGE_RANGE),
            'groupLevelOptions' => TranslationService::translateAdminList(Group::LEVEL),
            'cityOptions' => TranslationService::translateAdminList(Place::CITY),
            'contractTemplateOptions' => ContractTemplate::whereStatus(ContractTemplate::STATUS[0])->pluck('public_name', 'id'),
            'placeOptions' => Place::all()->sortBy('name')->pluck('name', 'id')->all(),
            'timesPerWeekOptions' => $timesPerWeekOptions,
            'availableSubscriptions' => $availableSubscriptions,
            'price' => $price,
            'isRepresentativeHimself' => null !== old('is_representative_himself') ? (bool) old('is_representative_himself') : false,
        ]);
    }

    public function store(WebContractRequest $request): RedirectResponse
    {
        $data = $request->all();
        $data['member_type'] = false;
        $data['member_status'] = 'on_line';
        $data['user_status'] = 'on_line';
        $data['is_representative_himself'] = isset($data['is_representative_himself'])
            ? (bool) $data['is_representative_himself']
            : false;
        $data['contract_source'] = PauseContract::SOURCE_ONLINE_FORM;

        $data['agreement_events'] = isset($data['agreement_events']) ? (bool) $data['agreement_events'] : false;
        $data['agreement_personal_data'] = (bool) $data['agreement_personal_data'];
        $data['agreement_photo'] = (bool) $data['agreement_photo'];
        $data['agreement_personal_data_third_company'] = (bool) $data['agreement_personal_data_third_company'];

        $user = $this->clientContractService->saveUser($data, new User());
        $user->assignRole(RolesAndPermissionsService::CLIENT_ROLE);
        $member = $this->clientContractService->saveMember($data);
        $this->clientContractService->saveContract($data, $member, $user);
        $this->clientContractService->updateUserMemberRelationship($user, $member, $data);

        return redirect(route('web.contract.create'))->with('status', __('Text'));
    }
}
