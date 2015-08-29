# Pragmas

Pragmas are a way to extend the mustache syntax, as well as alter it. They are
invoked using the syntax `{{%PRAGMA-NAME}}`.

## Behavior

### Pragmas Are Section Specific

Lets take the Implicit-Iterator defined in one section:

```html
{{!template-with-pragma-in-section.mustache}}
Some content, with {{type}}
{{#section1}}
{{%IMPLICIT-ITERATOR}}
    {{#subsection}}
        {{.}}
    {{/subsection}}
{{/section1}}
{{#section2}}
    {{#subsection}}
        {{.}}
    {{/subsection}}
{{/section2}}
```

Note that the pragma is only defined in `section1`.

Now consider the following view:

```php
$mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
$test = $mustache->render('template-with-pragma-in-section', [
    'type' => 'style',
    'section1' => [
        'subsection' => [1, 2, 3],
    ],
    'section2' => [
        'subsection' => [4, 5, 6],
    ],
]);
```

When the above is excecuted, only the contents of `section1.subsection` will be
iterated; `section2.subsection` will not. This results in the following output:

```html
Some content, with style

        1
            2
            3
```
                
## Pragmas Do Not Extend To Partials

Pragmas only apply to the specific template in which they are defined. This
means that any partials or parent templates (if using template inheritance) are
not affected.

As an example, consider the following templates:

```html
{{!partial-with-section.mustache}}
This is from the partial
{{#section}}
    {{#subsection}}
        {{.}}
    {{/subsection}}
{{/section}}

{{!template-with-pragma-and-partial.mustache}}
{{%IMPLICIT-ITERATOR}}
Some content, with {{type}}
{{>partial-with-section}}
```

And the following view:

```php
$mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
$test = $mustache->render('template-with-pragma-and-partial', [
    'type' => 'style',
    'section' => [
        'subsection' => [1, 2, 3],
    ],
]);
```

You can expect the following output:

```html
Some content, with style

This is from the partial
```

## Shipped Pragmas

### Implicit Iterator

Normally, sections expect key/value pairs, but often when iterating, you will
have simply a list of values. 

The implicit iterator pragma allows iteration of indexed arrays or `Traversable`
objects with scalar values, with the option of specifying the iterator "key" to
use within the template. By default, a variable key "." will be replaced by the
current value of the iterator.

To assign the name, add the verbiage ` iterator=varname` when invoking the
pragma.

As an example:

```html
{{!template-with-implicit-iterator.mustache}}
{{%IMPLICIT-ITERATOR iterator=bob}}
{{#foo}}
    {{bob}}
{{/foo}}
```

The above will assign each list item in `foo` to the variable `bob`, which we
can then render.

Here's the related view:
    
```php 
$mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
$view = ['foo' => [1, 2, 3, 4, 5, 'french']];
$test = $mustache->render(
    'template-with-implicit-iterator',
    $view
);
```

The two together render the following:

```html
1
2
3
4
5
french
```

We could have also used the default `.` placeholder instead, which would have
resulted in the following template:

```html
{{!template-with-implicit-iterator.mustache}}
{{%IMPLICIT-ITERATOR}}
{{#foo}}
    {{.}}
{{/foo}}
```

### Sub-Views

The Sub-Views pragma allows you to implement the two-step view pattern.  When
active, any variable whose value is an instance of
`Phly\Mustache\Pragma\SubView` will be substituted by rendering the template and
view that object encapsulates.

The `SubView` class takes a template name and a view via the constructor:

```php
use Phly\Mustache\Pragma\SubView;

$subView = new SubView('some-partial', ['name' => 'Matthew']);
```

That object is then assigned as a value to a view key:

```php
$view = new stdClass;
$view->content = $subView;
```

The template might look like this:

```html
{{!layout}}
{{%SUB-VIEWS}}
<html>
<body>
    {{content}}
</body>
</html>
```

and the partial like this:

```html
{{!some-partial}}
Hello, {{name}}!
```

Rendering the view:

```php
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\SubViews;

$mustache = new Mustache();
$subViews = new SubViews($mustache);
$rendered = $mustache->render('layout', $view);
```

will result in:

```html
<html>
<body>
    Hello, Matthew!
</body>
</html>
```

Sub views may be nested, and re-used.

Typically, you should use [template inheritance](syntax.md#placeholders-and-template-inheritance)
instead whenever possible, as it is built-in, and easier to re-use.
