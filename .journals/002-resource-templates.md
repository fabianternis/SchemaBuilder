## Why I Built Schema-Driven Resource Views in Laravel
In standard Laravel(the PHP-framework, i use) development, building CRUD (Create, Read, Update, Delete) operations typically requires creating dedicated Blade views (`index.blade.php`, `create.blade.php`, `edit.blade.php`) for every single model(A model is an Object representing databse-tables(and their entires)). This approach sometimes violates the "Don't Repeat Yourself" principle, producing redundant code and multiplying maintenance-time and effort when styling, layout or other adjustments are required.

To solbve this in my Project, I designed and implemnted a metadata-driven resource-template architecture that generates resource-views dynamically.

---

### Code (Defining the schema)

Instead of hardcoding forms, tables and co. in Blade, which is teh standart approach, I simply created a public varible `$schema`in my Model. Following is an example of how it looks on the `Project`-model.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    // ... other model stuff

    public $schema = [
        'name' => 'projects',
        'base_route' => 'projects',
        'columns' => [
            [
                'name' => 'name',
                'type' => 'text',
                'required' => true,
                'on_index' => true,
                'placeholder' => 'My Cool Project\'s name'
            ],
            [
                'name' => 'slug',
                'type' => 'text',
                'required' => false,
                'on_index' => false,
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'required' => false,
                'on_index' => true,
            ],
            [
                'name' => 'production_url',
                'type' => 'url',
                'required' => false,
                'on_index' => false,
            ],
            [
                'name' => 'repo_url',
                'type' => 'url',
                'required' => false,
                'on_index' => false,
            ],
        ],
    ];
}

```


---

### How the Dynamic Views Work

By defining the `array $schema` on the Model, my Blade views function as dynamic template-generators instead of static layouts. They use the metadata to dynamically generate the UI-template based on the configuration.

#### index-View (Dynamic Overview Tables with fallfack to NULL-state)

The index-template loops over the schema's columns, i definesd, to construct table-headers and render the cells based on the `on_index`(has to be true) and the `type`(e.g. 'textarea' gets trimmed to specific anmount of characters) properties.

```blade
<thead>
    <tr>
        @foreach($schema['columns'] as $column)
            @if($column['on_index'])
                <th>{{ $column['name'] }}</th>
            @endif
        @endforeach
        <th class="col-actions-header">Actions</th>
    </tr>
</thead>
<tbody>
    @foreach($items as $item)
    <tr>
        @foreach($schema['columns'] as $column)
            @if($column['on_index'])
                @if($column['type'] == 'textarea')
                    <td>{{ Str::limit($item->{$column['name']}, 12) }}</td>
                @else
                    <td>{{ $item->{$column['name']} }}</td>
                @endif
            @endif
        @endforeach
        <td class="col-actions-cell">
            <a href="{{ route($schema['base_route'].'.show', $item) }}" class="btn-icon">
                <x-heroicon-o-eye class="btn-icon-svg" />
            </a>
            <a href="{{ route($schema['base_route'].'.edit', $item) }}" class="btn-icon">
                <x-heroicon-o-pencil-square class="btn-icon-svg" />
            </a>
        </td>
    </tr>
    @endforeach
</tbody>

```

#### create-Viee (Schema-Driven Form-genration) _edit-View is about the same_

The form-builder dynamically generates input-elements like `<textarea>` and `<input type="url">` and input-attributes (`required`, `placeholder`, ...) and rennders the HTML.

```blade
<form action="{{ route($schema['base_route'].'.store') }}" method="post" class="form-stack">
    @csrf
    @foreach($schema['columns'] as $column)
    <div class="form-group">
        <label for="{{ $column['name'] }}">
            @lang($column['name'])
            <span class="form-hint">{{ !$column['required'] ? ' (Optional)' : '' }}</span>
        </label>
        
        @if($column['type'] === 'textarea')
            <textarea id="{{ $column['name'] }}" name="{{ $column['name'] }}" rows="3" {{ $column['required'] ? 'required' : '' }}></textarea>
        @else
            <input id="{{ $column['name'] }}" type="{{ $column['type'] === 'url' ? 'url' : 'text' }}" name="{{ $column['name'] }}" {{ $column['required'] ? 'required' : '' }}>
        @endif
    </div>
    @endforeach
    <div class="form-actions">
        <a class="btn-ghost" href="{{ route($schema['base_route'].'.index') }}">Cancel</a>
        <button type="submit" class="btn-primary">Create</button>
    </div>
</form>
```

### Resulting Benefits

* **Zero-Code UI Expansion:** Adding a new resource to the application no longer requires writing or copying Blade files. I just configure a new `$schema`-array within the new Model-class, and the CRUD UI generates instantly when defined in the Controllers and in Routing.
* **Global Maintenance:** Layout-tweaks, visual bugfixes, accessibility adjustments  CSS utility updates are modified in a single file instead of across ({Model Count} * 4) different files.