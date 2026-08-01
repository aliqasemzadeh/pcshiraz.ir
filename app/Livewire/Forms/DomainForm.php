<?php

namespace App\Livewire\Forms;

use App\Models\Domain;
use Illuminate\Validation\Rule;
use Livewire\Form;

class DomainForm extends Form
{
    public ?Domain $domainModel = null;

    public ?int $user_id = null;

    public string $title = '';

    public string $domain = '';

    public ?string $description = null;

    public function setDomain(Domain $domain): void
    {
        $this->domainModel = $domain;
        $this->user_id = $domain->user_id;
        $this->title = $domain->title;
        $this->domain = $domain->domain;
        $this->description = $domain->description;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domains', 'domain')
                    ->where(fn ($query) => $query->where('user_id', $this->user_id)->whereNull('deleted_at'))
                    ->ignore($this->domainModel?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'user_id' => __('general.user'),
            'title' => __('general.title'),
            'domain' => __('general.domain_name'),
            'description' => __('general.description'),
        ];
    }

    public function store(): Domain
    {
        $this->validate();

        return Domain::query()->create([
            'user_id' => $this->user_id,
            'title' => $this->title,
            'domain' => $this->domain,
            'description' => $this->description,
        ]);
    }

    public function update(Domain $domain): Domain
    {
        $this->domainModel = $domain;
        $this->validate();

        $domain->update([
            'user_id' => $this->user_id,
            'title' => $this->title,
            'domain' => $this->domain,
            'description' => $this->description,
        ]);

        return $domain->refresh();
    }
}
