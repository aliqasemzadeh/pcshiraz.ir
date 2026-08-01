<button
    type="button"
    @click="toast.dispose()"
    aria-label="@lang('close')"
    class="ms-auto -mx-1.5 -my-1.5 bg-neutral-primary-soft text-body hover:text-heading rounded-base focus:ring-2 focus:ring-neutral-tertiary p-1.5 hover:bg-neutral-secondary-medium inline-flex items-center justify-center h-8 w-8"
>
    <span class="sr-only">@lang('close')</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
    </svg>
</button>
