Installation 
============

Composer
--------

Require ``phly/mustache`` via `Composer <http://getcomposer.org>`_:

.. code-block:: bash

    $ composer require phly/mustache

Instantiation
-------------

Usage is fairly straightforward:

.. code-block:: php

    require 'vendor/autoload.php';

    $mustache = new Phly\Mustache\Mustache();
    echo $mustache->render('Hello, {{name}}!', array('name' => 'Hari K T'));

Alternately, import the classes and/or namespaces you will use:

.. code-block:: php

    use Phly\Mustache\Mustache;
    require 'vendor/autoload.php';
    $mustache = Mustache();

Usage
=====

.. code-block:: php

    $mustache = new Phly\Mustache\Mustache();
    echo $mustache->render('name-of-template', 'view');

By default, ``phly-mustache`` will look under the current directory for
templates ending with ``.mustache``; you can create a stack of
directories to search by using the ``setTemplatePath()`` method:

.. code-block:: php

    $mustache->setTemplatePath($path1)
             ->setTemplatePath($path2);

In the above, it will search first ``$path2``, then ``$path1`` to resolve the template.

You may also change the suffix it will use to resolve templates:

.. code-block:: php

    $mustache = new Mustache();
    $mustache->setSuffix('mst'); // now looks for files ending in ".mst"

