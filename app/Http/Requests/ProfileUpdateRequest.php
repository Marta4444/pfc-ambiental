<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'agent_num' => [
                'required',
                'string',
                'max:50',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        // Solo los administradores pueden actualizar el rol
        if ($this->user()->role === 'admin') {
            $rules['role'] = ['required', Rule::in(['admin', 'user'])];
        }

        return $rules;
    }

     /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'agent_num.required' => 'El número de agente es obligatorio.',
            'agent_num.unique' => 'Este número de agente ya está registrado.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }
}
