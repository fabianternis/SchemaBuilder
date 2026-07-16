# Why i build resource template-views
In the framework, i use "views" are used to dynamically inject content in page-templates.
I brought this to the next level. By defining a `public $schema` variable on some of my Models(= Objects that represent Database-tables(and their entires)) that exists of an array with values like ```
            [
                'name' => 'name',
                'type' => 'text',
                'required' => true,
                'on_index' => true,
                'placeholder' => 'Your Project Name',
            ],```.
Based on that the page-templates("views") are also templated, so i just have to write One template that is used to generate the other page-templates. (i just have to perfectly adjust my css once and nothing will be missing in the future – or when i want to make a change, i just have to make it once and not {amount of Models to change}-times).
Also: the `on_index` defines if this column of the table should be shown on the overview (list of {Model name}s).