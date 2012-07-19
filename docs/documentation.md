Phly\Mustache
=============
Phly\Mustache is a Mustache (http://mustache.github.com) implementation written
for PHP 5.3+. It conforms to the principles of mustache, and allows for
extension of the format via pragmas.

Autoloading
===========
phly_mustache follows the PSR-0 standard for class naming conventions, meaning any 
PSR-0-compliant class loader will work. ( Eg https://github.com/auraphp/Aura.Autoload, Zend etc). 
To simplify things out of the box, the component contains an "_autoload.php" file which will 
register an autoloader for the phly_mustache component with spl_autoload. You can simply 
include that file, and start using phly_mustache.

Instantiation
=============

Usage is fairly straightforward:

.. code-block:: php

    include '/path/to/library/Phly/Mustache/_autoload.php';
    // or use one of the PSR-0 autoloaders like Aura.Autoload or Zend
    use Phly\Mustache\Mustache;

    $mustache = new Mustache();
    echo $mustache->render('some-template', $view);


By default, phly_mustache will look under the current directory for templates 
ending with '.mustache'; you can create a stack of directories to search by using 
the setTemplatePath() method:

.. code-block:: php

    $mustache->setTemplatePath($path1)
             ->setTemplatePath($path2);

In the above, it will search first $path2, then $path1 to resolve the template.

You may also change the suffix it will use to resolve templates:

.. code-block:: php

    $mustache = new Mustache();
    $mustache->setTemplatePath(__DIR__ . '/templates');

Rendering String Templates
==========================

.. code-block:: php

    $test = $mustache->render(
        'Hello {{planet}}',
        array('planet' => 'World')
    );
    echo $test;

which outputs as 

.. code-block:: html

    Hello World

In the coming examples I will skip the ``echo`` statement to make the codes look small. 
We are also not using the opening php tags.

Rendering File Templates
========================

Let the template be `renders-file-templates.mustache` is your `templates` folder. 
From here onwards we assume you have your template in `templates` folder. 
Comments inside templates are marked between `{{!` and `}}`. Please not the character `!`.

.. code-block:: html

    {{!renders-file-templates.mustache}}
    Hello {{planet}}

Now you can render it 

.. code-block:: php

    $test = $mustache->render('renders-file-templates', array(
        'planet' => 'World',
    ));

Outputs : 

.. code-block:: html

    Hello World

Rendering Object Properties
===========================

You can also render object properties like 

.. code-block:: php

    $view = new \stdClass;
    $view->planet = 'World';
    $test = $mustache->render(
        'Hello {{planet}}',
        $view
    );

    {{!render-object-properties.mustache}}
    {{content}}

    $view = new stdClass;
    $view->content = 'This is the content';
    $test = $mustache->render('render-object-properties', $view);

Render methods which return value
=================================

Lets assume you have a class `ViewWithMethod`. You can render the method return value

.. code-block:: php

    class ViewWithMethod
    {
        public $name  = 'Chris';
        public $value = 1000000;
        public $in_ca = true;

        public function taxed_value()
        {
            return $this->value - ($this->value * 0.4);
        }
    }

.. code-block:: html

    {{!template-with-method-substitution.mustache}}
    Hello {{name}}
    You have just won ${{taxed_value}}!

.. code-block:: php

    $chris = new ViewWithMethod();
    $test = $mustache->render(
        'template-with-method-substitution',
        $chris
    );

Output : 

.. code-block:: html

    Hello Chris
    You have just won $600000!

Comments
========

Every one need to comment on something. You can comment inside `{{! }}`. Notice the `!` .

.. code-block:: html

    {{!template-with-comments.mustache}}
    First line {{! this is a comment}}
    Second line
    {{! this is
    a 
    multiline
    comment}}
    Third line

When called 

.. code-block:: php

    $test = $mustache->render('template-with-comments', array());

Will render as :

.. code-block:: html

    First line 
    Second line

    Third line

Rendering Conditions
====================

.. code-block:: html

    {{!template-with-conditional.mustache}}
    Hello {{name}}
    You have just won ${{value}}!
    {{#in_ca}}
    Well, ${{taxed_value}}, after taxes.
    {{/in_ca}}

.. code-block:: php

    class ViewWithMethod
    {
        public $name  = 'Chris';
        public $value = 1000000;
        public $in_ca = true;

        public function taxed_value()
        {
            return $this->value - ($this->value * 0.4);
        }
    }

    $chris = new ViewWithMethod;
    $test = $mustache->render(
        'template-with-conditional',
        $chris
    );

Output : 

.. code-block:: html

    Hello Chris
    You have just won $1000000!
    Well, $600000, after taxes.

Skipping the conditions with false/empty value
==============================================

.. code-block:: php

    $chris = new ViewWithMethod;
    $chris->in_ca = false;
    $test = $mustache->render(
        'template-with-conditional',
        $chris
    );

Ouput :

.. code-block:: html

    Hello Chris
    You have just won $1000000!

If ``$chris->in_ca = null`` then also you get the same output

Iterating through array
=======================

.. code-block:: html

    {{!template-with-enumerable.mustache}}
    {{name}}:
    <ul>
    {{#items}}
        <li>{{item}}</li>
    {{/items}}
    </ul>

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
    
Output : 

.. code-block:: html

    Joe's shopping card:
    <ul>
        <li>bananas</li>
        <li>apples</li>
    </ul>

Iterating via Traversable Object
================================

.. code-block:: php

    class ViewWithTraversableObject
    {
        public $name = "Joe's shopping card";
        public $items;

        public function __construct()
        {
            $this->items = new \ArrayObject(array(
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

Output :

.. code-block:: html

    Joe's shopping card:
    <ul>
        <li>bananas</li>
        <li>apples</li>
    </ul>

Higher Order Sections Render Inside Out
=======================================

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

Rendering Nested Array
======================

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

.. code-block:: php

    $view = array(
        'a' => array(
            'title' => 'this is an object',
            'description' => 'one of its attributes is a list',
            'list' => array(
                array('label' => 'listitem1'),
                array('label' => 'listitem2'),
            ),
        ),
    );
    $test = $mustache->render(
        'template-with-dereferencing',
        $view
    );

Output : 

.. code-block:: html

    <h1>this is an object</h1>
    <p>one of its attributes is a list</p>
    <ul>
        <li>listitem1</li>
                <li>listitem2</li>
            </ul>

There is whitespace issue as seen in the `ul` , `li` when rendering.

Inverted Sections Render On Empty Values
========================================

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
========

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
utilize one or more pragmas, but those declarations will not perist to the partial; 
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
=================

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

Escaping
========

By default all characters assigned to view are escaped. 

.. code-block:: php

    $view = array('foo' => 't&h\\e"s<e>');
    $test = $mustache->render(
        '{{foo}}',
        $view
    );
        
You will get characters escpaed as below

Output : 

.. code-block:: html

    t&amp;h\\e&quot;s&lt;e&gt;

Prevent Escaping
================

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

Pragma Implicit Iterator
========================

.. code-block:: html

    {{!template-with-implicit-iterator.mustache}}
    {{%IMPLICIT-ITERATOR iterator=bob}}
    {{#foo}}
        {{bob}}
    {{/foo}}
    
.. code-block:: php
    
    $mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
    $view = array('foo' => array(1, 2, 3, 4, 5, 'french'));
    $test = $mustache->render(
        'template-with-implicit-iterator',
        $view
    );

Output : 

.. code-block:: html

    1
    2
    3
    4
    5
    french
 
Template Suffix
===============

You would have noticed we have not added the suffix when we pass the template name. 
By default the suffix is mustache.
But you can change the suffix of your likes. For eg to `html`.

.. code-block:: php

    $mustache->setSuffix('html');
    $test = $mustache->render('alternate-suffix', array());

So we assume `alternate-suffix.html` is your template in templates folder.

Alternate Delimiters
====================

You can specify alternate delimiters other than `{{` and `}}` . This is possible via 
adding new deliminiter inside `{{=<% %>=}}`
Assuming the `<%` and `%>` is new delimiter.

.. code-block:: html

    {{!template-with-delim-set.mustache}}
    {{=<% %>=}}
    This is content, <%substitution%>, from new delimiters.
    
.. code-block:: php

    $test = $mustache->render('template-with-delim-set', array('substitution' => 'working'));

Outout : 

.. code-block:: html

    This is content, working, from new delimiters.

Alternate Delimiters in selected areas only
===========================================

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
============================================

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

Pragmas Are Section Specific
============================

Lets take the Implicit Iterator defined in one section.

.. code-block:: html

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

You can see its only in section1.

.. code-block:: php

    $mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
    $test = $mustache->render('template-with-pragma-in-section', array(
        'type' => 'style',
        'section1' => array(
            'subsection' => array(1, 2, 3),
        ),
        'section2' => array(
            'subsection' => array(4, 5, 6),
        ),
    ));
    
The contents of `section1.subsection` will be iterated. But not that of `section2.subsection`.

Output : 

.. code-block:: html

    Some content, with style

            1
                2
                3
                
Pragmas Do Not Extend To Partials
=================================

.. code-block:: html

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
    
.. code-block:: php

    $mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
    $test = $mustache->render('template-with-pragma-and-partial', array(
        'type' => 'style',
        'section' => array(
            'subsection' => array(1, 2, 3),
        ),
    ));

Output : 

.. code-block:: html

    Some content, with style

    This is from the partial

Recursive Partials
==================

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
============================================

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
===================================

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
