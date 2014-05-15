Usage
=====

Twig Tag
--------

.. code-block:: jinja

  {% imresize %}
     <p>some content</p>
     <img src="/some/path" width="100" />
     <img src="{{ asset('/some/path') }}" width="100" />
  {% endimresize %}

This will parse all content inside the tag and render image caches regarding their HTML width and/or height attributes

Twig function
-------------

.. code-block:: jinja

  <img src="{{ imresize('/some/path','small') }}"/>
  <img src="{{ imresize('/some/path','120x') }}"/>
  <img src="{{ imresize('/some/path','x120') }}"/>
  <img src="{{ imresize('/some/path','120x120') }}"/>
  
The format - the second argument - can be a predefined format in your configuration, or a [width]x[height] syntax

Twig filter
-----------

.. code-block:: jinja

  <img src="{{ '/some/path' | imresize('small') }}"/>
  <img src="{{ asset('/some/path') | imresize('small') }}"/>

From a controller
-----------------

.. code-block:: php

  $im = $this->get("snowcap_im.manager");

  // to create a cached file
  $im->convert($format, $path);

  // to resize the source file
  $im->mogrify($format, $path);

In entities
-----------

If you need to alter an uploaded image, you can add annotations on the file public property from your entity

.. code-block:: php

  // ...
  use Snowcap\ImBundle\Doctrine\Mapping as SnowcapIm;
  // ...

    /**
     *
     * @Assert\File(maxSize="6000000")
     * @SnowcapIm\Mogrify(params={"thumbnail"="100x100>"})
     */
    public $file;

When the form is submitted, the file will then be "thumbnailed" to 100x100 if bigger. You can then use the $file->move() method like usual.

The *params* attribute can contain

* an array of ImageMagick key/values (like the example above)
* a format predefined in config

Clearing the cache
------------------

You can clear the cache with the following command-line task

.. code-block:: console

  ./app/console snowcap:im:clear [age]

Where the age argument - optional - will only clear cache older than the [age] days

