.. _pragmas:

Pragmas
=======

Pragmas are a way to extend the mustache syntax, as well as alter it.

(More here... including list of those shipped, and examples of using the
various pragmas shipped.)

.. _pragmas-section-specific:

Pragmas Are Section Specific
----------------------------

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

    <?php
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
                
.. _pragmas-do-not-extend-to-partials:

Pragmas Do Not Extend To Partials
---------------------------------

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

    <?php
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

.. _pragmas-implicit-iterator:

Implicit Iterator
-----------------

.. code-block:: html

    {{!template-with-implicit-iterator.mustache}}
    {{%IMPLICIT-ITERATOR iterator=bob}}
    {{#foo}}
        {{bob}}
    {{/foo}}
    
.. code-block:: php
    
    <?php
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
 

