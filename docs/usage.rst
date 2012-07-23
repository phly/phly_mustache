Usage
=====

Autoloading
-----------
``phly_mustache`` follows the `PSR-0 <https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md>`_
standard for class naming conventions, meaning any PSR-0-compliant class
loader will work.  To simplify things out of the box, the component
contains an ``_autoload.php`` file which will register an autoloader for
the ``phly_mustache`` component with ``spl_autoload``. You can simply
include that file, and start using ``phly_mustache``.

Instantiation
-------------

Usage is fairly straightforward:

.. code-block:: php

    include '/path/to/library/Phly/Mustache/_autoload.php';
    // or use any PSR-0-compliant autoloader
    use Phly\Mustache\Mustache;

    $mustache = new Mustache();
    echo $mustache->render('some-template', $view);


By default, ``phly_mustache`` will look under the current directory for
templates ending with ``.mustache``; you can create a stack of
directories to search by using the setTemplatePath() method:

.. code-block:: php

    $mustache->setTemplatePath($path1)
             ->setTemplatePath($path2);

In the above, it will search first $path2, then $path1 to resolve the template.

You may also change the suffix it will use to resolve templates:

.. code-block:: php

    $mustache = new Mustache();
    $mustache->setSuffix('mst'); // now looks for files ending in ".mst"

