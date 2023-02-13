<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MemberSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientContractController extends Controller
{
    /** @var MemberSubscriptionService */
    private $memberSubscriptionService;

    public function __construct(MemberSubscriptionService $memberSubscriptionService)
    {
        $this->memberSubscriptionService = $memberSubscriptionService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this
            ->memberSubscriptionService
            ->findAvailableMap(
                $request->request->getInt('group_type_id'),
                $request->get('age_range'),
                $request->get('city'),
                $request->get('group_level'),
                $request->request->getInt('contract_template_id')
            ));
    }
}
