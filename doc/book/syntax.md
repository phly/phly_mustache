# Syntax

The following is a list of syntax elements and features supported by
`phly-mustache`. 

## Escaping

By default all variables assigned to a view are escaped. 

```php
$view = ['foo' => 't&h\\e"s<e>'];
$test = $mustache->render(
    '{{foo}}',
    $view
);
```
        
You will get characters escaped as below:

```html
    t&amp;h\\e&quot;s&lt;e&gt;
```

## Preventing Escaping

You can prevent escaping characters by using triple brackets `{{{` + `}}}`.

```php
$view = ['foo' => 't&h\\e"s<e>'];
$test = $mustache->render(
    '{{{foo}}}',
    $view
);
```

This will output the same value you have given:

```html
    t&h\\e"s<e>
```

## Comments

Everyone needs to comment at some point or another. You can comment
inside the delimeters `{{! }}`. Notice the `!` .

```html
{{!template-with-comments.mustache}}
First line {{! this is a comment}}
Second line
{{! this is
a 
multiline
comment}}
Third line
```

When called with the following:

```php
$test = $mustache->render('template-with-comments', []);
```

It will render as:

```html
First line 
Second line

Third line
```

## Conditionals

Mustache tends to eschew logic within templates themselves. That said, simple
conditionals are often necessary. These can be accomplished using the
`{{#}}`/`{{/}}` pair. Both tags will reference a variable in the view. If that
variable is present and a truthy value, the content between the pair will be
rendered; otherwise, it will be omitted. The content may contain arbitrary
mustache markup, including references to variables.

The following tmplate contains conditional blocks:

```html
{{!template-with-conditional.mustache}}
Hello {{name}}
You have just won ${{value}}!
{{#in_ca}}
Well, ${{taxed_value}}, after taxes.
{{/in_ca}}
```

As an example of a view:

```php
class Customer
{
    public $name  = 'Chris';
    public $value = 1000000;
    public $in_ca = true;

    public function taxed_value()
    {
        return $this->value - ($this->value * 0.4);
    }
}
```

Putting it together:

```php
$chris = new Customer;
$test = $mustache->render(
    'template-with-conditional',
    $chris
);
```

Generates the following output:

```html
Hello Chris
You have just won $1000000!
Well, $600000, after taxes.
```

With the following view object, we'll get a different result:

```php
class NonCalifornian extends Customer
{
    public $in_ca = false;
}

$matthew = new NonCalifornian;
$matthew->name = 'Matthew';
$test = $mustache->render(
    'template-with-conditional',
    $matthew
);
```

The above will result in:

```html
Hello Matthew
You have just won $1000000!
```

This occurs because the `$in_ca` value is a non-truthy value; any value that
would evalue to a boolean `false` (e.g., a `null` value, a zero integer or
float, and empty string) when used in a conditional will be treated as if the
value is not present, essentially skipping the conditional.

## Iteration

While mustache tends to eschew logic, just as with conditionals, we may
occasionally have repetitive data we need to render. Mustache provides
functionality for iteration as well, using the concept of "sections".

A section begins with a `{{#}}` token, and ends with a `{{/}}` token, and each
references the variable within the view. The view variable is assumed to be
iterable, with each item being another view (i.e., an associative array or an
object). The tokens surround mustache content. Unlike conditionals, the
assumption is that variables will dereference based on the current item in the
iteration. This may be better understood with an example. Given the following
template:

```html
{{!template-with-enumerable.mustache}}
{{name}}:
<ul>
{{#items}}
    <li>{{item}}</li>
{{/items}}
</ul>
```

We then have the following view:

```php
class ViewWithArrayEnumerable
{
    public $name = "Joe's shopping cart";
    public $items = [
        ['item' => 'bananas'],
        ['item' => 'apples'],
    ];
}

$view = ViewWithArrayEnumerable;
$test = $mustache->render(
    'template-with-enumerable',
    $view
);
```
    
This results in: 

```html
Joe's shopping cart:
<ul>
    <li>bananas</li>
    <li>apples</li>
</ul>
```

As noted, `$items` only needs to be iterable; it doesn't have to be an array, it
can be any `Traversable` object.

```php
class ViewWithTraversableObject
{
    public $name = "Joe's shopping cart";
    public $items;

    public function __construct()
    {
        $this->items = new ArrayObject([
            ['item' => 'bananas'],
            ['item' => 'apples'],
        ]);
    }
}

$view = new ViewWithTraversableObject;
$test = $mustache->render(
    'template-with-enumerable',
    $view
);
```

The above will result in the exact same output as with the array example.

To take it a step further, each "item" could be an object:

```php
class Item
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }
}

class ViewWithTraversableObject
{
    public $name = "Joe's shopping cart";
    public $items;

    public function __construct()
    {
        $this->items = new ArrayObject([
            new Item('bananas'),
            new Item('apples'),
        ]);
    }
}

$view = new ViewWithTraversableObject;
$test = $mustache->render(
    'template-with-enumerable',
    $view
);
```

## Higher Order Sections

Mustache has a concept of "higher order sections." 

In the previous section on [iteration](#iteration), we indicated that the
`{{#}}`/`{{/}}` syntax indicates a *section*.  While sections can be used for
iteration, this is not their only use.

A *higher order section* is a variable that refers to a *callable*. In such a
case, the mustache content for the section is passed, as well as a reference to
the mustache renderer, allowing the callable to return arbitrary content, and,
if desired, render additional mustache content.

This is best illustrated with the following example.

```php
class ViewWithHigherOrderSection
{
    public $name = 'Tater';

    public function bolder()
    {
        return function($text, $renderer) {
            return '<b>' . call_user_func($renderer, $text) . '</b>';
        };
    }
}

$view = new ViewWithHigherOrderSection();
$test = $mustache->render(
    '{{#bolder}}Hi {{name}}.{{/bolder}}',
    $view
);
```

The above generates the following output: 

```html
<b>Hi Tater.</b>
```

In other words, *higher order sections render inside out*.

## Nested Sections

In the previous sections on [iteration](iteration) and [higher order
sections](#higher-order-sections), we indicated that the ``{{#}}``/``{{/}}``
syntax indicates a *section*.

Another use for sections is for rendering hierarchical or nested data
structures. When used in this way, the default scope within a section
assumes that we are now within the scope of the dereferenced variable; as
we go deeper in the nesting, we get into gradually more specific scope.
Any given variable may contain another section, iterable content, higher
order sections, or simply scalar output.

Let's look at the following template:

```html
{{!template-with-dereferencing.mustache}}
{{#a}}
    <h1>{{title}}</h1>
    <p>{{description}}</p>
    <ul>
        {{#list}}
        <li>{{label}}</li>
        {{/list}}
    </ul>
{{/a}}
```

And here's a view that might be used with it:

```php
$view = [
    'a' => [
        'title'       => 'this is an object',
        'description' => 'one of its attributes is a list',
        'list'        => [
            ['label' => 'listitem1'],
            ['label' => 'listitem2'],
        ],
    ],
];
$test = $mustache->render(
    'template-with-dereferencing',
    $view
);
```

The generated output will resemble the following: 

```html
<h1>this is an object</h1>
<p>one of its attributes is a list</p>
<ul>
    <li>listitem1</li>
    <li>listitem2</li>
</ul>
```

## Inverted Sections

An inverted section is one that begins with `{{^}}` vs `{#}`. They render when
the referenced variable is empty.

Take the following, for example:

```html
{{!template-with-inverted-section.mustache}}
{{#repo}}<b>{{name}}</b>{{/repo}}
{{^repo}}No repos{{/repo}}
```

The following view omits the `repo` variable:

```php
$view = ['repo' => []];
$test = $mustache->render(
    'template-with-inverted-section',
    $view
);
```
        
And generates the following output: 

```html
No repos
```

## Partials

Partials are a basic form of inclusion within Mustache; anytime you find you
have re-usable bits of templates, move them into a partial, and refer to the
partial from the parent template.

Typically, you will only reference partials within your templates using the
partial name:

```html
{{>partial-name}}
```

However, you may optionally pass a list of partials from PHP when actually
rendering. When you do so, the list should be a set of alias/template pairs:

```php
$mustache->render($template, [], [
    'winnings' => 'user-winnings',
]);
```
    
In the above example, `winnings` refers to the template
`user-winnings.mustache`. Thus, within the `$template` being rendered, you may
refer to the following partial:

```html
{{>winnings}}
```
    
and it will resolve to the appropriate aliased template.

When using partials, you should keep in mind a few rules.

First, if the parent template uses alternate tag delimiters, your partial does
not. If you want the partial to use the same alternate tag delimiters, it
will need to make the same declaration.

Second, and similarly, if The parent template uses one or more
[pragmas](pragmas.md), your partial will not. If you want the partial to use the
same pragmas, you must also reference them in your partial.

Basically, partials render in their own scope. If you remember that one rule, you 
should have no problems.

As a concrete example, consider the following two templates:

```html
{{!template-with-partial.mustache}}
Welcome, {{name}}! {{>partial-template}}

{{!partial-template.mustache}}
You just won ${{value}} (which is ${{taxed_value}} after tax)
```

And now the associated view:

```php
class ViewWithObjectForPartial
{
    public $name = 'Joe';
    public $winnings;
    public $value = 1000;
    public function taxed_value() 
    {
        return $this->value - ($this->value * 0.4);
    }
}

$view = new ViewWithObjectForPartial();
$test = $mustache->render(
    'template-with-partial',
    $view
);
```

They generate the following output:

```html
Welcome, Joe! You just won $1000 (which is $600 after tax)
```

## Aliasing Partials

As noted in the previous section, you can alias partials. Let's consider a more
concrete example. Given the following two templates:

```html
{{!partial-template.mustache}}
You just won ${{value}} (which is ${{taxed_value}} after tax)

{{!template-with-aliased-partial.mustache}}
Welcome, {{name}}! {{>winnings}}
```

This view will allow the two to work together, by aliasing the `winnings`
template to `partial-template`:

```php
$view = ViewWithObjectForPartial();
$test = $mustache->render(
    'template-with-aliased-partial',
    $view,
    ['winnings' => 'partial-template']
);
```

Generating the following output:

```html
Welcome, Joe! You just won $1000 (which is $600 after tax)
```

## Alternate Delimiters

You can specify delimiters other than `{{` and `}}`. This is possible via 
defining the new delimiter inside `{{=` and `=}}` blocks tags.

As an example, in the following, the template redefines the delimiters to
`<%`/`%>`:

```html
{{!template-with-delim-set.mustache}}
{{=<% %>=}}
This is content, <%substitution%>, from new delimiters.
```
    
Given the following:

```php
$test = $mustache->render('template-with-delim-set', ['substitution' => 'working']);
```

The associated output will be:

```html
This is content, working, from new delimiters.
```

Alternate Delimiters in selected areas only
-------------------------------------------

Sometimes you may want alternative delimiter in selected areas only. This can be
done by placing the `{{=`/`=}}` declarations inside a section
(`{{#section}}`/`{{/section}}`):

```html
{{!template-with-delim-set-in-section.mustache}}
Some text with {{content}}
{{#section}}
{{=<% %>=}}
    <%name%>
{{/section}}
{{postcontent}}
```

Given the following view:

```php
$test = $mustache->render('template-with-delim-set-in-section', [
    'content' => 'style',
    'section' => [
        'name' => '-World',
    ],
    'postcontent' => 'P.S. Done',
]);
```

You can expect the following output:

```html
Some text with style
    -World
P.S. Done
```

## Alternate Delimiters Apply To Child Sections

You can apply alternate delimiters to child sections via substitution.

```html
{{!template-with-sections-and-delim-set.mustache}}
{{=<% %>=}}
Some text with <%content%>
<%#substitution%>
    <%name%>
<%/substitution%>
```

Since the `substitution` section is a child of the template, its children also
inherit the new delimiters. Thus, with the following view:

```php
$test = $mustache->render('template-with-sections-and-delim-set', 
    ['content' => 'style', 'substitution' => ['name' => '-World']]
);
```

You can expect the following output:

```html
Some text with style
    -World
```

Partials do not inherit alternative delimiters, nor do their own alternate
delimiter declarations affect the parent:

Thus, given the following templates:

```html
{{!partial-template.mustache}}
You just won ${{value}} (which is ${{taxed_value}} after tax)

{{!template-with-partials-and-delim-set.mustache}}
{{=<% %>=}}
This is content, <%substitution%>, from new delimiters.
<%>partial-template%>
```

And this view:

```php
$test = $mustache->render('template-with-partials-and-delim-set', [
    'substitution' => 'style',
    'value'        => 1000000,
    'taxed_value'  =>  400000,
]);
```

The output is:

```html
This is content, style, from new delimiters.
You just won $1000000 (which is $400000 after tax)
```

## Recursive Partials

Partials can be used recursively :

```html
{{!crazy_recursive.mustache}}
<html>
<body>
<ul>
    {{#top_nodes}}
        {{> node}}
    {{/top_nodes}}
</ul>
</body>
</html>

{{!node.mustache}}
<li>
    {{contents}}
    <ul>
        {{#children}}
            {{>node}}
        {{/children}}
    </ul>
</li>
```
    
```php
$view = [
    'top_nodes' => [
        'contents' => '1',
        'children' => [
            [
                'contents' => '2',
                'children' => [
                    [
                        'contents' => 3,
                        'children' => [],
                    ]
                ],
            ],
            [
                'contents' => '4',
                'children' => [
                    [
                        'contents' => '5',
                        'children' => [
                            [
                                'contents' => '6',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
$test = $mustache->render('crazy_recursive', $view);
```

Try yourself to see the rendering!

## PHP functions will not work inside templates

As a mustache implementation, `phly-mustache` will only perform substitutions as
dictated by the view object. As such, you cannot execute arbitrary PHP from your
templates.

For example, consider this template:

```html
{{!template-referencing-php-function.mustache}}
{{message}}

template-referencing-static-function-notempty.mustache
{{#message}}
{{message}}
{{/message}}
```

In none of the following examples will arbitray PHP be executed.

```php
// This attempts to assign the time() function as a value. phly-mustache
// will treat this as the string 'time', and not as a function.
$test = $mustache->render('template-referencing-php-function', [
    'message' => 'time',
]);

// The following is the same as the previous, only using an object property.
$model = (object) ['message' => 'time'];
$test  = $mustache->render('template-referencing-php-function', $model);

// This example assigns a static method as a value. phly-mustache will
// not call it, either.
$model = ['message' => 'DateTime::createFromFormat'];
$test = $mustache->render('template-referencing-php-function', $model);

// This one attempts to trick the renderer by using the implicit-iterator pragma
// to dereference the value. This will still treat the value as a string.
$model = ['message' => 'time'];
$mustache->getRenderer()->addPragma(new ImplicitIterator());
$test = $mustache->render('template-referencing-static-function-notempty', $model);

// This example assigns a callable in conjunction with the implicit-iterator
// pragma. In thiis cas, the value is treated as an array.
// not call it, either.
$model = ['section' => ['DateTime', 'createFromFormat']];
$mustache->getRenderer()->addPragma(new ImplicitIterator());
$test = $mustache->render('template-referencing-static-function', $model);
```

## Placeholders and Template Inheritance

- Available since: **1.1.0**.

Placeholders are basically unnamed sections, and are denoted by the combination
of `{{$name}}` and `{{/name}}`. When encountered by the renderer, any mustache
content within will be rendered as normal mustache content.

Placeholders are primarily of use with the concept of hierarchical views. These
are denoted by the combination of `{{<name}}` and `{{/name}}`. When encountered,
the template denoted by `name` will be tokenized, and any placeholders that are
defined in the content will be used to replace those found in the parent
template.

As an example, consider the following parent template:

```html
{{!super.mustache}}
<html>
<head><title>{{$title}}Default title{{/title}}</title></head>
<body>
{{>navigation}}
<div class="content">
{{$content}}Default content of the page{{/content}}
</div>
{{>footer}}
</body>
</html>
```

And the related partials:

```html
{{!navigation.mustache}}
<nav><a href="/">Home</a> | <a href="/blog">Blog</a></nav>

{{!footer.mustache}}
<footer>
End of page
</footer>
```

If rendered by itself, it will result in the following:

```html
<html>
<head><title>Default title</title></head>
<body>
<nav><a href="/">Home</a> | <a href="/blog">Blog</a></nav>
<div class="content">
Default content of the page
</div>
<footer>
End of page
</footer>
</body>
</html>
```

Now, consider the following child template:

```html
{{!sub.mustache}}
{{<super}}
{{$title}}Profile of {{username}} | Twitter{{/title}}
{{$content}}
Here is {{username}}'s profile page
{{/content}}
{{/super}}
```

The above template indicates that it _inherits_ from `super`, and defines
several _placeholder_ sections, one each for `title` and `content`.

Now let's consider a view that defines `username` as "Matthew", and render it
wit the template `sub`:

```php
$view = new stdClass;
$view->username = 'Matthew';
$test = $mustache->render('sub', $view);
```

This will result in the following:

```html
<html>
<head><title>Profile of Matthew</title></head>
<body>
<nav><a href="/">Home</a> | <a href="/blog">Blog</a></nav>
<div class="content">
Here is Matthew's profile page
</div>
<footer>
End of page
</footer>
</body>
</html>
```

Notice how the child retains the view context of the parent, and that all mustache 
tokens defined in it are rendered as if they were simply another mustache template.

Hierarchical templates may be nested arbitrarily deep, allowing for concepts
such as template themes.
