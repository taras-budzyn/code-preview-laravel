<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ContractTemplate;
use App\Models\Group;
use App\Models\GroupSetting;
use App\Models\Place;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use function assert;

class ClientContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        $rules = [
            'user_name' => 'required|min:2|max:255',
            'user_surname' => 'required|min:2|max:255',
            'user_personal_code' => 'nullable|integer',
            'user_birthday' => 'required|date|before:today',
            'user_residence' => 'required|min:1|max:500',
            'user_phone_number' => 'required|max:50',

            'group_type_id' => sprintf('required|exists:%s,id', GroupSetting::class),
            'age_range' => ['required', Rule::in(Group::AGE_RANGE),],
            'city' => ['required', Rule::in(Place::CITY),],
            'times_per_week' => 'required|numeric|max:100',
            'contract_template_id' => sprintf('required|exists:%s,id', ContractTemplate::class),
            'agreement_photo' => 'required',
            'agreement_personal_data' => 'required',
            'agreement_personal_data_third_company' => 'required',
        ];

        if (false === (bool) Request::get('is_representative_himself')) {
            $rules += [
                'member_name' => 'required|min:2|max:255',
                'member_surname' => 'required|min:2|max:255',
                'member_personal_code' => 'required|integer',
                'member_birthday' => 'required|date|before:today',
                'member_residence' => 'required|min:1|max:500',
                'member_phone_number' => 'nullable|max:50',
                'member_email' => 'nullable|email|max:100',
                'member_id' => 'required_if:member_type,1',
            ];
        }

        if ("schoolers_age" === (string) Request::get('age_range')) {
            $rules += [
                'has_nvs' => 'required',
            ];
        }

        if (
            "schoolers_age" === (string) Request::get('age_range')
            && false === (bool) Request::get('is_representative_himself')
        ) {
            $rules += [
                'member_class' => 'required|min:1|integer',
            ];
        }

        if ("schoolers_age" === (string) Request::get('age_range') || "preschoolers_age" === (string) Request::get('age_range')) {
            $rules += [
                'agreement_events' => 'required',
            ];
        }

        if (1 === (int) Request::get('group_type_id')) {
            $rules['group_level'] = ['required', Rule::in(Group::LEVEL),];
        }

        if (true === (bool) Request::get('e_invoice')) {
            $rules += [
                'e_invoice_value' => 'required',
            ];
        }

        return $rules;
    }


    public function withValidator(Validator $validator): void
    {
        $validator->after(
            function ($validator): void {
                if (
                    (bool) $this->request->get('is_representative_himself')
                    || 1 !== (int) $this->request->get('member_type')
                    || (int) $this->request->get('member_id')
                ) {
                    return;
                }

                $user = backpack_user();
                assert($user instanceof User);

                if (!$user->members->count()) {
                    return;
                }

                if (!$user->members->contains((int) $this->request->get('member_id'))) {
                    $validator->errors()->add('member_id', __('Text'));
                }
            }
        );
    }
}
