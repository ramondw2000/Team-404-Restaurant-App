<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Manage Roles');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'color' => ['required', 'string', 'in:purple,blue,teal,green,amber,orange,rose,red,indigo,cyan,pink,slate'],
        ];
    }
}
