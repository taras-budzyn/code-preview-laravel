<?php

namespace App\Http\Controllers\Operations;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use App\Services\InvoiceCalculationService;
use Symfony\Component\HttpFoundation\Response;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use App\Services\TranslationService;
use \Illuminate\Support\Carbon;


trait ManageInvoiceItemsOperation
{

    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupUpdateDiscountHistoryOperationRoutes($segment, $routeName, $controller)
    {
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupUpdateDiscountHistoryDefaults()
    {
        $this->crud->allowAccess('update');
        $this->crud->applyConfigurationFromSettings('create');

        $this->crud->setupDefaultSaveActions();

        $this->crud->enableGroupedErrors();
        $this->crud->enableInlineErrors();


        $this->crud->replaceSaveActions([
            'name' => 'save_and_back',
            'redirect' => function ($crud, $request, $itemId) {
                return $crud->route;
            },
            'button_text' => __('Save'),
            'visible' => function ($crud) {
                return true;
            },
        ]);


        $this->crud->operation(['list', 'show'], function () {
            //$this->crud->addButtonFromModelFunction('line', 'change_group', 'changeGroup', 'beginning');
        });
    }

    public function showInvoiceItem($invoiceId, $invoiceItemId)
    {

        $invoice = Invoice::findOrFail($invoiceId);
        $invoiceItem = InvoiceItem::findOrFail($invoiceItemId);

        $this->getColumns($invoice, $invoiceItem);

        $this->crud->setHeading(__("Invoice item") . ' ' . $invoiceItemId);
        $this->crud->setSubheading(__("Preview invoice item"));

        return view('admin.invoice-item.show', [
            'entry' => $invoiceItem,
            'invoice' => $invoice,
            'backUrl' => route('invoice-item.index'),
            'crud' => $this->crud
        ]);
    }

    public function getCreateInvoiceItemForm($id)
    {
        if (!backpack_user()->can('create invoice')) {
            return redirect()->back();
        }
        $invoice = $this->crud->getEntry($id);

        abort_unless(Invoice::STATUS_SENT != $invoice->status, Response::HTTP_FORBIDDEN);

        $this->getFields($invoice);

        $this->crud->setEntityNameStrings(__('New Invoice Line Item'), __('New Invoice Line Item'));
        $this->crud->replaceSaveActions([
            [
                'name' => 'save_and_create_line_item',
                'redirect' => function ($crud, $request, $id) {
                    return backpack_url('invoice/' . $id . '/new-line-item');
                },
                'button_text' => trans('invoiceItem.save_line_and_add_new'),
                'order' => 1
            ],
            [
                'name' => 'save_and_back',
                'redirect' => function ($crud, $request, $id) {
                    return backpack_url('invoice/' . $id . '/show');
                },
                'button_text' => __('Save'),
                'order' => 2
            ]
        ]);
        $this->data['entry'] = $invoice;
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();

        return view('admin.invoices.create_form_item', $this->data);
    }

    // create inline item (NOTE: the same url used for edit)
    public function updateInvoiceItem($id)
    {

        if (request('invoiceItem')) {
            return $this->postUpdateInvoiceItem($id, request('invoiceItem'));
        }

        $invoice = Invoice::findOrFail($id);
        abort_unless(Invoice::STATUS_SENT != $invoice->status, Response::HTTP_FORBIDDEN);

        $this->validatesUpdateInvoiceItemFormRequest();

        \Alert::success(trans('backpack::crud.update_success'))->flash();

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'type' => request('type'),
            'text' => request('text'),
            'amount' => request('amount'),
            'price_per_unit' => request('price_per_unit'),
            'status' => invoiceItem::STATUS_MANUAL_CREATED,
            'nvs_contract_id' => request('nvs_contract_id'),
            'compensations_id' => request('compensations_id'),
            'discount_id' => request('discount_id'),
            'pause_id' => request('pause_id')
        ]);

        //TODO: should pass data not the id of invoice; rewrite
        $invoice->total = InvoiceCalculationService::getTotal($id);
        $invoice->save();

        //return $this->crud->performSaveAction($invoice->id);
        if (request('save_action') == 'save_and_create_line_item') {
            return redirect()->to(backpack_url(sprintf('invoice/%d/new-line-item', $id)));
        } else {
            return redirect()->to(backpack_url(sprintf('invoice/%d/show', $id)));
        }
    }


    // Form for edit line item
    public function editInvoiceItem($invoiceId, $itemId)
    {
        if (!backpack_user()->can('update invoice')) {
            return redirect()->back();
        }

        $invoice = Invoice::findOrFail($invoiceId);
        abort_unless(Invoice::STATUS_SENT != $invoice->status && Invoice::STATUS_PAID != $invoice->status, Response::HTTP_FORBIDDEN);

        $invoiceItem = InvoiceItem::findOrFail($itemId);

        $this->crud->setEntityNameStrings(__('Edit Invoice Line Item'), __('Edit Invoice Line Item'));

        $this->getFields($invoice, $invoiceItem);

        $this->crud->field('invoiceItem')->type('hidden')->value($itemId);

        $this->data['crud'] = $this->crud;
        $this->data['entry'] = $invoice;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add') . ' ' . $this->crud->entity_name;

        return view('admin.invoices.create_form_item', $this->data);
    }

    // edit existing item
    public function postUpdateInvoiceItem($id, $itemId)
    {
        $this->validatesUpdateInvoiceItemFormRequest();

        $invoice = Invoice::findOrFail($id);
        abort_unless(Invoice::STATUS_SENT != $invoice->status, Response::HTTP_FORBIDDEN);
        $invoiceItem = InvoiceItem::findOrFail($itemId);

        $invoiceItem->type = request('type');
        $invoiceItem->text = request('text');
        $invoiceItem->amount = request('amount');
        $invoiceItem->price_per_unit = request('price_per_unit');
        if ($invoiceItem->status === InvoiceItem::STATUS_AUTO_CREATED) {
            $invoiceItem->status = invoiceItem::STATUS_EDITED;
            $invoice->status = Invoice::STATUS_EDITED;
        }
        $invoiceItem->nvs_contract_id = request('nvs_contract_id');
        $invoiceItem->compensations_id = request('compensations_id');
        $invoiceItem->discount_id = request('discount_id');
        $invoiceItem->pause_id = request('pause_id');

        if ($invoiceItem->save()) {
            //TODO: should pass data not the id of invoice; rewrite
            $invoice->total = InvoiceCalculationService::getTotal($id);
            $invoice->save();

            \Alert::success(trans('backpack::crud.update_success'))->flash();
            return redirect()->to(backpack_url(sprintf('invoice/%d/show', $id)));
        } else {
            \Alert::error(trans('backpack::crud.related_entry_created_error'))->flash();
            return redirect()->back();
        }
    }

    public function deleteInvoiceItem($invoiceId, $itemId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoiceItem = InvoiceItem::findOrFail($itemId);

        if ($invoiceItem->delete()) {
            if ($invoiceItem->status === InvoiceItem::STATUS_AUTO_CREATED) {
                $invoice->status = Invoice::STATUS_EDITED;
            }
            $invoice->total = InvoiceCalculationService::getTotal($invoiceId);
            $invoice->save();
            \Alert::success(__('Successfully removed the invoice item.'))->flash();
        } else {
            \Alert::error(__('Unable to remove invoice item.'))->flash();
        }

        if (request()->ajax()) {
            return 'redirect'; //app('request')->create(url()->previous())->getRequestUri();
        } else {
            return redirect()->back();
        }
    }

    public function validatesUpdateInvoiceItemFormRequest()
    {
        $rules = [
            'type' => 'required',
            'amount' => 'required|numeric|min:1',
            'price_per_unit' => 'required|numeric'
        ];

        $rules += $this->getValidationRules();
        $validator = Validator::make(
            request()->all(),
            $rules
        );

        $validator->validate();
    }


    private function getColumns($invoice, $invoiceItem)
    {
        $this->crud->set('show.setFromDb', false);

        $this->crud->removeButton('create');
        $this->crud->removeButton('show');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');

        $this->crud->column('type')->type('select_from_array')->label(__('Line Item type'))->options(
            TranslationService::translateInvoiceItemList(InvoiceItem::TYPES)
        );

        $this->crud->column('text')->type('text')->label(__('Line item custom text'));

        $this->crud->column('amount')->type('numeric')->label(__('Amount in units'));

        $this->crud->column('price_per_unit')->type('numeric')->label(__('Price per unit'));

        $this->crud->column('status')->type('select_from_array')->label(__('Line item status'))->options(
            TranslationService::translateInvoiceItemList(InvoiceItem::getStatuses())
        );

        $this->crud->column('invoice_id')->type('numeric')->label(__('Invoice'))->wrapper([
            'href' => static function (CrudPanel $crud, array $data): string {
                return backpack_url(sprintf('invoice/%d/show', $data['text']));
            },
        ]);

        $this->crud->column('invoice_status')->type('closure')->label(__('Invoice status'))->function(static function (InvoiceItem $invoiceItem): string {
            return __(sprintf('admin.%s', $invoiceItem->invoice->status));
        });

        $this->crud->column('invoice_issued_at')->type('closure')->label(__('Date issued'))->function(static function (InvoiceItem $invoiceItem): string {
            return Carbon::parse($invoiceItem->invoice->issued_at)->format('Y-m-d');
        });

        if ($invoiceItem->nvs_contract_id) {
            $this->crud->column('nvs_contract_id')->type('text')->label(__('NVS contract'))->wrapper([
                'href' => static function (CrudPanel $crud, array $data): string {
                    return backpack_url(sprintf('nvm/%d/show', $data['text']));
                },
            ]);
        }

        if ($invoiceItem->discount_id) {
            $this->crud->column('discount_id')->type('text')->label(__('Compensation'))->wrapper([
                'href' => static function (CrudPanel $crud, array $data): string {
                    return backpack_url(sprintf('discount/%d/show', $data['text']));
                },
            ]);
        }

        if ($invoiceItem->pause_id) {
            $this->crud->column('pause_id')->type('text')->label(__('Pause contract request'));
        }


        $this->crud->addButtonFromModelFunction(
            'line',
            'edit_invoice_item',
            'editInvoiceItem',
            'end'
        );

        $this->crud->addButtonFromView(
            'line',
            'delete_invoice_item',
            'invoices.delete_invoice_item',
            'end'
        );
    }

    private function getValidationRules()
    {
        $additionalRules = [
            InvoiceItem::TYPE_CREDIT_ITEM => ['text' => 'required|min:1'],
            InvoiceItem::TYPE_CUSTOM_ITEM => ['text' => 'required|min:1'],
            InvoiceItem::TYPE_NVS_DISCOUNT => ['nvs_contract_id' => 'required|numeric'],
            InvoiceItem::TYPE_COMPENSATION_DISCOUNT => ['compensations_id' => 'required|numeric'],
            InvoiceItem::TYPE_REGULAR_DISCOUNT => ['discount_id' => 'required|numeric'],
            InvoiceItem::TYPE_PAUSED_DISCOUNT => ['pause_id' => 'required|numeric'],
        ];
        return (request('type') && isset($additionalRules[request('type')]))  ? $additionalRules[request('type')] : [];
    }
}
