<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Controllers\Operations\ContractOperation;
use App\Http\Requests\ClientContractRequest;
use App\Models\Contract;
use App\Services\ContractClientService;
use App\Services\ContractFormService;
use App\Services\ContractPrice;
use App\Services\MemberSubscriptionService;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read CrudPanel $crud
 */
class ClientContractCrudController extends CrudController
{
    use ListOperation;
    use ShowOperation;
    use CreateOperation;
    use UpdateOperation {
        update as traitUpdate;
    }
    use ContractOperation;

    /** @var MemberSubscriptionService */
    private $memberSubscriptionService;

    /** @var ContractPrice */
    private $contractPrice;

    /** @var ContractFormService */
    private $contractSaveFormService;

    /** @var ContractClientService */
    private $clientContractService;

    public function __construct(MemberSubscriptionService $memberSubscriptionService, ContractPrice $contractPrice, ContractFormService $contractSaveFormService, ContractClientService $contractClientService)
    {
        $this->memberSubscriptionService = $memberSubscriptionService;
        $this->contractPrice = $contractPrice;
        $this->contractSaveFormService = $contractSaveFormService;
        $this->clientContractService = $contractClientService;

        parent::__construct();
    }

    public function setup(): void
    {
        abort_unless(backpack_user()->hasPermissionTo('show own data'), Response::HTTP_FORBIDDEN);
        $this->crud->setModel(Contract::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/clientcontract');
        $this->crud->setEntityNameStrings('mano sutartis', 'mano sutartys');
        $this->crud->addClause('where', 'user_id', backpack_user()->id);
        $this->crud->setSubheading(' ');
        $this->crud->removeAllFilters();
    }

    public function update(): RedirectResponse
    {
        $this->crud->validateRequest();
        $contractId = (int) Route::current()->parameter('id');

        $contract = Contract::whereId($contractId)
            ->whereUserId(backpack_user()->id)
            ->first();

        abort_unless($contract, Response::HTTP_FORBIDDEN);

        $data = $this->crud->getRequest()->request->all();

        $this->clientContractService->update($data, $contractId);

        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect(route('clientcontract.index'));
    }

    public function show(int $contractId): View
    {
        $contract = Contract::whereId($contractId)
            ->whereUserId(backpack_user()->id)
            ->first();

        abort_unless($contract, Response::HTTP_FORBIDDEN);

        return view('admin.contracts.show', [
            'contract' => $contract,
            'backUrl' => route('clientcontract.index'),
            'price' => $this->contractPrice->getByContract($contract),
            'crud' => $this->crud
        ]);
    }

    public function edit(int $contractId): View
    {
        $contract = Contract::whereId($contractId)->whereUserId(backpack_user()->id)->first();

        abort_unless($contract, Response::HTTP_FORBIDDEN);

        return view('admin.client_contract.edit', [
            'crud' => $this->crud,
            'entry' => $contract,
            'saveAction' => $this->crud->getSaveAction(),
            'availableSubscriptions' => $this->contractSaveFormService->getAvailableSubscription($contract),
            'members' => $this->contractSaveFormService->getUserMembers(backpack_user()),
        ]);
    }

    public function create(): View
    {
        return view('admin.client_contract.create', [
            'crud' => $this->crud,
            'saveAction' => $this->crud->getSaveAction(),
            'availableSubscriptions' => $this->contractSaveFormService->getAvailableSubscription(),
            'members' => $this->contractSaveFormService->getUserMembers(backpack_user()),
        ]);
    }

    public function store(): RedirectResponse
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $data = $this->crud->getRequest()->request->all();

        $this->clientContractService->store($data);

        Alert::success(trans('backpack::crud.create_success'))->flash();

        return redirect('admin/clientcontract');
    }

    protected function setupCreateOperation(): void
    {
        $this->crud->setValidation(ClientContractRequest::class);

        $this->fields($this->contractSaveFormService->getData());

        $this->crud->set('create.showCancelButton', false);
        $this->crud->set('update.showCancelButton', false);
    }

    protected function setupListOperation(): void
    {

        $this->crud->addClause('where', function (Builder $query): void {
            $query->where('contracts.status', '!=', Contract::STATUS_DRAFT)
                ->orWhere('contracts.specify_allow', '=', 1)
                ->orWhereNotNull('contracts.specify_date');
        });

        $this->columns();
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     */
    protected function setupUpdateOperation(): void
    {
        $this->crud->setValidation(ClientContractRequest::class);

        $this->crud->setHeading(__('Text') . " " . Route::current()->parameter('id'));

        $routeId = (int) Route::current()->parameter('id');
        $contract = $routeId ? Contract::find($routeId) : null;
        $this->fields($this->contractSaveFormService->getData($contract));
    }

    private function columns(): void
    {
        $this->crud->column('id')->type('id')->label(__('Text'));

        $this->crud->column('user_id')->type('closure')->label(__('Client'))->function(
            static function (Contract $contract): string {
                return $contract->user->full_name;
            }
        );
        $this->crud->column('member_id')->type('select')->label(__('Member'))
            ->entity('member')->attribute('full_name');

        $this->crud->column('contract_template_id')->type('select')->label(__('Text'))
            ->entity('contractTemplate')->attribute('public_name');

        $this->crud->column('member_subscription_id')->type('select')->label(__('Text'))
            ->entity('memberSubscription')->attribute('name');

        $this->crud->column('status')->type('closure')->label(__('Status'))->function(
            static function (Contract $contract): string {
                return __(sprintf('admin.%s', $contract->status));
            }
        );
        $this->crud->removeButton("update");
        $this->crud->addButtonFromModelFunction('line', 'update_function', 'updateClientFunction', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'contract_cancelation', 'contractCancelation');
        $this->crud->addButtonFromView('line', 'change_status_contract_signed', 'change_status_contract_signed');
    }
}
