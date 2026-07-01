# Project Guidelines

1-Laravel 13
2-Livewire 4
3-FluxUI.dev https://fluxui.dev/
4-TailwindCSS
5-AlpineJS
6-PHP 8.4 || 8.5
7-All Text Must Translate in /lang/en/main.php and /lang/fa/main.php and If you want to add new text add it in main.php.
8-Optimize Queries check N + 1 problem.
9-Try to use AlpineJS for UI and Livewire for Backend.
10-We have to use modal for create and edit form.
11-We have to use pagination for list.
12-Use Blade for view and Livewire for component.
13-Use TailwindCSS for styling.
14-for better validation use livewire form https://livewire.laravel.com/docs/4.x/forms.
15-For icons use lucide icons https://lucide.dev/icons. and you can add icon by `php artisan flux:icon icon-name`.
16-For models eloquent use https://laravel.com/docs/13.x/eloquent and add relations.
17-for permissions use https://spatie.be/docs/laravel-permission/v6/introduction and we add all permissions in /lang/fa/permissions.php and /lang/en/permissions.php.
18-In livewire 4 there is no Volt and Livewire\Component is good.
19-Use https://livewire.laravel.com/docs/4.x/attribute-computed for computed properties and load data by that.
20-Use For all modal <flux:modal flyout position="right"> not normal modal.
21-Use https://fluxui.dev/components/select#backend-search for select when database options.
21-for event use full name of event assign-data name is not good use panels.administrator.learning-management.school.edit.assign-data
22-When you want to load livewire component user <livewire:component-name :key="$componentId" />
24-After all livewire action we need Flux::toast('message');
25-for actions use buttons with icon and tooltip.
<flux:tooltip content="{{ __('main.import') }}">
<flux:button size="xs" variant="primary" color="teal" icon="upload" icon:variant="outline" wire:click="$dispatch('learning-management.student.import.assign-data', { classId: {{ $class->id }} })" />
</flux:tooltip>

                            <flux:tooltip content="{{ __('main.delete') }}">
                                <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="delete({{ $class->id }})" wire:confirm="{{ __('main.are_you_sure') }}" />
                            </flux:tooltip>
28-For Table use https://fluxui.dev/components/table and add search in searchable fields top of <flux:table.columns>
29-For Search and Fillter create use card and use <flux:card> and data of table use <flux:table.columns>
30-https://fluxui.dev/components/pillbox#searchable use it for search.
32-searchable for all select and search <flux:select searchable>
33-Buttons use <flux:button> they have many colors base on action user can use. For example <flux:button color="orange">Save</flux:button> for save action.
<flux:button variant="primary" color="zinc">Zinc</flux:button>
<flux:button variant="primary" color="red">Red</flux:button>
<flux:button variant="primary" color="orange">Orange</flux:button>
<flux:button variant="primary" color="amber">Amber</flux:button>
<flux:button variant="primary" color="yellow">Yellow</flux:button>
<flux:button variant="primary" color="lime">Lime</flux:button>
<flux:button variant="primary" color="green">Green</flux:button>
<flux:button variant="primary" color="emerald">Emerald</flux:button>
<flux:button variant="primary" color="teal">Teal</flux:button>
<flux:button variant="primary" color="cyan">Cyan</flux:button>
<flux:button variant="primary" color="sky">Sky</flux:button>
<flux:button variant="primary" color="blue">Blue</flux:button>
<flux:button variant="primary" color="indigo">Indigo</flux:button>
<flux:button variant="primary" color="violet">Violet</flux:button>
<flux:button variant="primary" color="purple">Purple</flux:button>
<flux:button variant="primary" color="fuchsia">Fuchsia</flux:button>
<flux:button variant="primary" color="pink">Pink</flux:button>
<flux:button variant="primary" color="rose">Rose</flux:button>
33-In forms and modals only user w-full buttons and only save
34-try to use colors
35- for edit and delete use these buttons
<flux:tooltip content="{{ __('actions.edit') }}">
<flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user.edit.assign-data', { user: {{ $user->id }} })" />
</flux:tooltip>

                                    <flux:tooltip content="{{ __('actions.delete') }}">
                                        <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="delete({{ $user->id }})" wire:confirm="{{ __('main.are_you_sure') }}" />
                                    </flux:tooltip>
36-Livewire events use Livewire\Attributes\On; and use $this->dispatch('event-name');
<?php // resources/views/components/⚡dashboard.blade.php
 
use Livewire\Attributes\On;
use Livewire\Component;
 
new class extends Component {
    #[On('post-created')] 
    public function updatePostList($title)
    {
        Flux::toast("New post created: {$title}");
    }
};
37-never use protected $listeners more about events in livewire https://livewire.laravel.com/docs/4.x/events
38-Control modals in livewire
     // Control "confirm" modals anywhere on the page...
        Flux::modal('confirm')->show();
        Flux::modal('confirm')->close();
        // Closes all modals on the page...
        Flux::modals()->close();

39-Here is example of page with flux:table and create button
<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('main.users') }}</flux:heading>

            <flux:modal.trigger name="user-create-modal">
                <flux:button variant="primary" color="teal" icon="plus">
                    {{ __('main.create_user') }}
                </flux:button>
            </flux:modal.trigger>

        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('actions.search') }}..." />
            </div>

            <flux:table :paginate="$this->users">
                <flux:table.columns>
                    <flux:table.column>{{ __('main.first_name') }}</flux:table.column>
                    <flux:table.column>{{ __('main.last_name') }}</flux:table.column>
                    <flux:table.column>{{ __('main.email') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('main.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>{{ $user->first_name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('actions.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user.edit.assign-data', { user: {{ $user->id }} })" />
                                    </flux:tooltip>

                                    <flux:tooltip content="{{ __('actions.delete') }}">
                                        <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="delete({{ $user->id }})" wire:confirm="{{ __('main.are_you_sure') }}" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:user.create />
    <livewire:user.edit />
</div>
40-For per_page use  ->paginate(config('main.per_page'));
41-use wire:confirm  for any danger like remove and delete <flux:button
    type="button"
    wire:click="delete"
    wire:confirm="Are you sure you want to delete this post?"
>
    Delete post 
</flux:button>
42-Try to use <flux:callout icon="cube" variant="secondary" inline>
    <flux:callout.heading>Your package is delayed</flux:callout.heading>
    <x-slot name="actions">
        <flux:button>Track order -></flux:button>
        <flux:button variant="ghost">Reschedule</flux:button>
    </x-slot>
</flux:callout> when you want to display a record like permissions and roles and users in modals.
43-no need to add cancel button in modals.
44-only use <flux:button></flux:button> has color="zinc".
45-For each model we need php artisan livewire:form ModelForm which guide can be find in 
https://livewire.laravel.com/docs/4.x/forms#extracting-a-form-object
use setModel like post
    public function setPost(Post $post)
    {
        $this->post = $post;
 
        $this->title = $post->title;
 
        $this->content = $post->content;
    }
 
46-When we have one modal component dont add <div></div> just use <flux:modal></flux:modal>
47-Never use model.live in forms. Just use when I told you.
48-there is no flex:number use <flux:input type="number" />
49-<flux:modal.trigger name="create-user-modal">
    <flux:button>Create user</flux:button>
</flux:modal.trigger>
when there is data on open modal use <flux:modal.trigger name="create-user-modal"></flux:modal.trigger>
50-For submit button use <flux:button type="submit" variant="primary" color="teal">Button</flux:button> - variant="primary" color="teal"
51-#[Fillable([])] 
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden; 
try Fillable instead of $fillable array.
52-use <flux:date-picker  selectable-header /> for date picker and <flux:time-picker  selectable-header /> for time picker.
53-for active for is_active  <flux:field variant="inline">
    <flux:label>Enable notifications</flux:label>
    <flux:switch wire:model.live="notifications" />
    <flux:error name="notifications" />
</flux:field>
54-refresh data: if you add livewire component in a page after create or edit use refresh data on that page. this need to use livewire events #[On('refresh-data')] on page and dispatch livewire event refresh-data on create or edit page.
55-Add breadcrumbs to livewire page
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="{{ route('home') }}" icon="home" />
    <flux:breadcrumbs.item href="#"></flux:breadcrumbs.item>
    <flux:breadcrumbs.item>Post</flux:breadcrumbs.item>
</flux:breadcrumbs>
56-All configurable in config/main.php
57-For actions use text like "Edit" and "Delete" use lang/fa/actions.php and lang/en/actions.php dont add new word in language files.

