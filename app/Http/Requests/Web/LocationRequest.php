<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Poligon dikirim sebagai satu hidden input berisi string JSON (hasil klik di peta) —
     * decode dulu jadi array supaya bisa divalidasi per-titik seperti field biasa.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('polygon'))) {
            $decoded = json_decode((string) $this->input('polygon'), true);
            $this->merge(['polygon' => is_array($decoded) ? $decoded : null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:RADIUS,POLYGON'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'long' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required_if:type,RADIUS', 'nullable', 'integer', 'min:10'],
            'polygon' => ['required_if:type,POLYGON', 'nullable', 'array', 'min:3'],
            'polygon.*.lat' => ['required_with:polygon', 'numeric', 'between:-90,90'],
            'polygon.*.lng' => ['required_with:polygon', 'numeric', 'between:-180,180'],
        ];
    }
}
