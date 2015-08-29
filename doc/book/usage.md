# Usage and Installation 

## Installation

To install `phly-mustache`, require ``phly/mustache`` via
[Composer](http://getcomposer.org):

```bash
$ composer require phly/mustache
```

## Usage

Usage is fairly straightforward:

```php
require 'vendor/autoload.php';

$mustache = new Phly\Mustache\Mustache();
echo $mustache->render('name-of-template', 'view');
```

Alternately, import the classes and/or namespaces you will use:

```php
use Phly\Mustache\Mustache;
require 'vendor/autoload.php';
$mustache = Mustache();
```

By default, `phly-mustache` will look under the current directory for templates
ending with the suffix `.mustache`; you can create a stack of directories to
search by using the `setTemplatePath()` method:

```php
$mustache
    ->setTemplatePath($path1)
    ->setTemplatePath($path2);
```

In the above, it will search first `$path2`, then `$path1` to resolve the template.

You may also change the suffix it will use to resolve templates:

```php
$mustache = new Mustache();
$mustache->setSuffix('mst'); // now looks for files ending in ".mst"
```
