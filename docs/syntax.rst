.. _syntax:

Syntax
======

The following syntax is supported by ``phly_mustache``. 

.. _syntax-escaping:

Escaping
--------

By default all variables assigned to a view are escaped. 

.. code-block:: php

    $view = array('foo' => 't&h\\e"s<e>');
    $test = $mustache->render(
        '{{foo}}',
        $view
    );
        
You will get characters escaped as below:

.. code-block:: html

    t&amp;h\\e&quot;s&lt;e&gt;

.. _syntax-prevent-escaping:

Prevent Escaping
----------------

You can prevent escaping characters by using triple brackets `{{{`

.. code-block:: php

    $view = array('foo' => 't&h\\e"s<e>');
    $test = $mustache->render(
        '{{{foo}}}',
        $view
    );

This will output the same value you have given 

Output : 

.. code-block:: html

    t&h\\e"s<e>

.. _syntax-comments:

Comments
--------

Everyone needs to comment at some point or another. You can comment
inside the delimeters `{{! }}`. Notice the `!` .

.. code-block:: html

    {{!template-with-comments.mustache}}
    First line {{! this is a comment}}
    Second line
    {{! this is
    a 
    multiline
    comment}}
    Third line

When called:

.. code-block:: php

    $test = $mustache->render('template-with-comments', array());

It will render as:

.. code-block:: html

    First line 
    Second line

    Third line

.. _syntax-conditionals:

Conditionals
------------

Mustache tends to eschew logic within templates themselves. That said,
simple conditionals are often necessary. These can be accomplished using
the ``{{#}}``/``{{/}}`` pair. Both tags will reference a variable in the
view. If that variable is present and a truthy value, the content
between the pair will be rendered; otherwise, it will be omitted. The
content may contain arbitrary mustache markup, including references to
variables.

.. code-block:: html

    {{!template-with-conditional.mustache}}
    Hello {{name}}
    You have just won ${{value}}!
    {{#in_ca}}
    Well, ${{taxed_value}}, after taxes.
    {{/in_ca}}

.. code-block:: php

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

    $chris = new Customer;
    $test = $mustache->render(
        'template-with-conditional',
        $chris
    );

Output: 

.. code-block:: html

    Hello Chris
    You have just won $1000000!
    Well, $600000, after taxes.

With the following view object, we'll get a different result:

.. code-block:: php

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

The above will result in:

.. code-block:: html

    Hello Matthew
    You have just won $1000000!

This occurs because the ``$in_ca`` value is a non-truthy value; any
value that would evalue to a boolean ``false`` (e.g., a ``null`` value,
a zero integer or float, and empty string) when used in a conditional
will be treated as if the value is not present, essentially skipping the
conditional.

.. _syntax-iteration:

Iteration
---------

While mustache tends to eschew logic, just as with conditionals, we may
occasionally have repetitive data we need to render. Mustache provides
functionality for iteration as well, using the concept of "sections".

A section begins with a ``{{#}}`` token, and ends with a ``{{/}}`` token,
and each references the variable within the view. The view variable is
assumed to be iterable, with each item being another view (i.e., an
associative array or an object).  The tokens surround mustache content.
Unlike conditionals, the assumption is that variables will dereference
based on the current item in the iteration. This may be better
understood with an example. Given the following template:

.. code-block:: html

    {{!template-with-enumerable.mustache}}
    {{name}}:
    <ul>
    {{#items}}
        <li>{{item}}</li>
    {{/items}}
    </ul>

We then have the following view:

.. code-block:: php

    class ViewWithArrayEnumerable
    {
        public $name = "Joe's shopping card";
        public $items = array(
            array('item' => 'bananas'),
            array('item' => 'apples'),
        );
    }
    
    $view = ViewWithArrayEnumerable;
    $test = $mustache->render(
        'template-with-enumerable',
        $view
    );
    
This results in: 

.. code-block:: html

    Joe's shopping card:
    <ul>
        <li>bananas</li>
        <li>apples</li>
    </ul>

As noted, the ``$items`` only needs to be iterable; it doesn't have to
be an array, it can be any ``Traversable`` object.

.. code-block:: php

    class ViewWithTraversableObject
    {
        public $name = "Joe's shopping card";
        public $items;

        public function __construct()
        {
            $this->items = new ArrayObject(array(
                array('item' => 'bananas'),
                array('item' => 'apples'),
            ));
        }
    }

    $view = new ViewWithTraversableObject;
    $test = $mustache->render(
        'template-with-enumerable',
        $view
    );

The above will result in the exact same output as with the array
example.

To take it a step further, each "item" could be an object:

.. code-block:: php

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
        public $name = "Joe's shopping card";
        public $items;

        public function __construct()
        {
            $this->items = new ArrayObject(array(
                new Item('bananas'),
                new Item('apples'),
            ));
        }
    }

    $view = new ViewWithTraversableObject;
    $test = $mustache->render(
        'template-with-enumerable',
        $view
    );


.. _syntax-higher-order-sections:

Higher Order Sections Render Inside Out
---------------------------------------

Mustache has a concept of "higher order sections." 

In the previous section on :ref:`iteration <syntax-iteration>`, we
indicated that the ``{{#}}``/``{{/}}`` syntax indicates a *section*.
While sections can be used for iteration, this is not their only use.

A higher order section is a variable that refers to a callable. In such
a case, the mustache content for the section is passed, as well as a
reference to the mustache renderer, allowing the callable to return
arbitrary content, and, if desired, render additional mustache content.

This is best illustrated with the following example.

.. code-block:: php

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

Output : 

.. code-block:: html

    <b>Hi Tater.</b>

.. _syntax-nested-sections:

Rendering Nested Sections
-------------------------

In the previous sections on :ref:`iteration <syntax-iteration>` and
:ref:`higher order sections <syntax-higher-order-sections>`, we
indicated that the ``{{#}}``/``{{/}}`` syntax indicates a *section*.

Another use for sections is for rendering hierarchical or nested data
structures. When used in this way, the default scope within a section
assumes that we are now within the scope of the dereference variable; as
we go deeper in the nesting, we get into gradually more specific scope.
Any given variable may contain another section, iterable content, higher
order sections, or simply scalar output.

Let's look at the following template:

.. code-block:: html

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

And here's a view that might be used with it:

.. code-block:: php

    $view = array(
        'a' => array(
            'title'       => 'this is an object',
            'description' => 'one of its attributes is a list',
            'list'        => array(
                array('label' => 'listitem1'),
                array('label' => 'listitem2'),
            ),
        ),
    );
    $test = $mustache->render(
        'template-with-dereferencing',
        $view
    );

The generated output will resemble the following: 

.. code-block:: html

    <h1>this is an object</h1>
    <p>one of its attributes is a list</p>
    <ul>
        <li>listitem1</li>
        <li>listitem2</li>
    </ul>

Inverted Sections Render On Empty Values
----------------------------------------

.. code-block:: html

    {{!template-with-inverted-section.mustache}}
    {{#repo}}<b>{{name}}</b>{{/repo}}
    {{^repo}}No repos{{/repo}}

.. code-block:: php

    $view = array('repo' => array());
    $test = $mustache->render(
        'template-with-inverted-section',
        $view
    );
        
Output : 

    No repos

Partials
--------

Partials are a basic form of inclusion within Mustache; anytime you find you have 
re-usable bits of templates, move them into a partial, and refer to the partial 
from the parent template.

Typically, you will only reference partials within your templates, using standard syntax:

.. code-block:: html

    {{>partial-name}}

However, you may optionally pass a list of partials when rendering. When you do so, 
the list should be a set of alias/template pairs:

.. code-block:: php

    $mustache->render($template, array(), array(
        'winnings' => 'user-winnings',
    ));
    
In the above example, 'winnings' refers to the template "user-winnings.mustache". Thus, 
within the $template being rendered, you may refer to the following partial:

.. code-block:: html

    {{>winnings}}

    
and it will resolve to the appropriate aliased template.

A few things to remember when using partials:

The parent template may change tag delimiters, but if you want to use the same delimiters 
in your partial, you will need to make the same declaration. The parent template may 
utilize one or more :ref:`pragmas <pragmas>`, but those declarations will not perist to the partial; 
if you want those pragmas, you must reference them in your partial.
Basically, partials render in their own scope. If you remember that one rule, you 
should have no problems.
  
.. code-block:: html

    {{!template-with-partial.mustache}}
    Welcome, {{name}}! {{>partial-template}}
    
    {{!partial-template.mustache}}
    You just won ${{value}} (which is ${{taxed_value}} after tax)

.. code-block:: php

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

Output : 

.. code-block:: html

    Welcome, Joe! You just won $1000 (which is $600 after tax)

Aliasing Partials
-----------------

.. code-block:: html

    {{!partial-template.mustache}}
    You just won ${{value}} (which is ${{taxed_value}} after tax)
    
    {{!template-with-aliased-partial.mustache}}
    Welcome, {{name}}! {{>winnings}}

.. code-block:: php

    $view = ViewWithObjectForPartial();
    $test = $mustache->render(
        'template-with-aliased-partial',
        $view,
        array('winnings' => 'partial-template')
    );

Output: 

.. code-block:: html

    Welcome, Joe! You just won $1000 (which is $600 after tax)

Alternate Delimiters
--------------------

You can specify alternate delimiters other than `{{` and `}}` . This is possible via 
adding new deliminiter inside `{{=<% %>=}}`
Assuming the `<%` and `%>` is new delimiter.

.. code-block:: html

    {{!template-with-delim-set.mustache}}
    {{=<% %>=}}
    This is content, <%substitution%>, from new delimiters.
    
.. code-block:: php

    $test = $mustache->render('template-with-delim-set', array('substitution' => 'working'));

Output : 

.. code-block:: html

    This is content, working, from new delimiters.

Alternate Delimiters in selected areas only
-------------------------------------------

Sometimes you may want alternative delimiter in selected areas. Its also possible 
adding it inside `{{#section}}` and `{{/section}}`

.. code-block:: html

    {{!template-with-delim-set-in-section.mustache}}
    Some text with {{content}}
    {{#section}}
    {{=<% %>=}}
        <%name%>
    {{/section}}
    {{postcontent}}

.. code-block:: php

    $test = $mustache->render('template-with-delim-set-in-section', array(
        'content' => 'style',
        'section' => array(
            'name' => '-World',
        ),
        'postcontent' => 'P.S. Done',
    ));

Output : 

.. code-block:: html

    Some text with style
        -World
    P.S. Done

Alternate Delimiters Apply To Child Sections
--------------------------------------------

You can apply alternate delimiters to child via substitution

.. code-block:: html

    {{!template-with-sections-and-delim-set.mustache}}
    {{=<% %>=}}
    Some text with <%content%>
    <%#substitution%>
        <%name%>
    <%/substitution%>

.. code-block:: php

    $test = $mustache->render('template-with-sections-and-delim-set', 
        array('content' => 'style', 'substitution' => array('name' => '-World'))
    );

Output : 

.. code-block:: html

    Some text with style
        -World

Partials don't have any effect on alternative delimiters. 

.. code-block:: html

    {{!partial-template.mustache}}
    You just won ${{value}} (which is ${{taxed_value}} after tax)

    {{!template-with-partials-and-delim-set.mustache}}
    {{=<% %>=}}
    This is content, <%substitution%>, from new delimiters.
    <%>partial-template%>

.. code-block:: php

    $test = $mustache->render('template-with-partials-and-delim-set', array(
        'substitution' => 'style',
        'value'        => 1000000,
        'taxed_value'  =>  400000,
    ));

Output :

.. code-block:: html

    This is content, style, from new delimiters.
    You just won $1000000 (which is $400000 after tax)

Recursive Partials
------------------

Partials can be used recursively 

.. code-block:: html

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
    
.. code-block:: php

    $view = array(
        'top_nodes' => array(
            'contents' => '1',
            'children' => array(
                array(
                    'contents' => '2',
                    'children' => array(
                        array(
                            'contents' => 3,
                            'children' => array(),
                        )
                    ),
                ),
                array(
                    'contents' => '4',
                    'children' => array(
                        array(
                            'contents' => '5',
                            'children' => array(
                                array(
                                    'contents' => '6',
                                    'children' => array(),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
    $test = $mustache->render('crazy_recursive', $view);

Try yourself to see the rendering :)

PHP functions will not work inside templates
--------------------------------------------

.. code-block:: html

    {{!template-referencing-php-function.mustache}}
    {{message}}
    
    template-referencing-static-function-notempty.mustache
    {{#message}}
    {{message}}
    {{/message}}

.. code-block:: php

    $test = $mustache->render('template-referencing-php-function', array(
        'message' => 'time',
    ));
    
    $model = (object) array('message' => 'time');
    $test  = $mustache->render('template-referencing-php-function', $model);
    
    $model = array('message' => 'DateTime::createFromFormat');
    $test = $mustache->render('template-referencing-php-function', $model);

    $model = array('message' => 'time');
    $mustache->getRenderer()->addPragma(new ImplicitIterator());
    $test = $mustache->render('template-referencing-static-function-notempty', $model);

    $model = array('section' => array('DateTime', 'createFromFormat'));
    $mustache->getRenderer()->addPragma(new ImplicitIterator());
    $test = $mustache->render('template-referencing-static-function', $model);

Hierarchieal / Template Inheritance
-----------------------------------

Hierarchical Views and Placeholders (Available in versions 1.1.0 and up).

Placeholders are basically unnamed sections, and are denoted by the combination of 
{{$name}} and {{/name}}. When encountered by the renderer, any mustache content 
within will be rendered as normal mustache content.

Placeholders are primarily of use with the concept of hierarchical views. These 
are denoted by the combination of {{<name}} and {{/name}}. When encountered, the 
template denoted by name will be tokenized, and any placeholders that are defined 
in the content will be used to replace those found in the parent template.

As an example, consider the following parent template, "super.mustache":

.. code-block:: html

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
    
    {{!navigation.mustache}}
    <nav><a href="/">Home</a> | <a href="/blog">Blog</a></nav>
    
    {{!footer.mustache}}
    <footer>
    End of page
    </footer>

If rendered by itself, it will result in the following:


.. code-block:: html

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

Now, consider the following child template, "sub.mustache":

.. code-block:: html

    {{!sub.mustache}}
    {{<super}}
    {{$title}}Profile of {{username}} | Twitter{{/title}}
    {{$content}}
    Here is {{username}}'s profile page
    {{/content}}
    {{/super}}

If we have a view that defines "username" as "Matthew" and render "sub.mustache", 

.. code-block:: php

    $view = new stdClass;
    $view->username = 'Matthew';
    $test = $mustache->render('sub', $view);

we'll get the following:

.. code-block:: html

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

Notice how the child retains the view context of the parent, and that all mustache 
tokens defined in it are rendered as if they were simply another mustache template.

Hierarchical templates may be nested arbitrarily deep.

