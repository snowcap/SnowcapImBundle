Installation
============

Requirements
------------

* You need to have the ImageMagick binaries available (convert & mogrify)
* You need to have a cache folder in your web dir, writeable by the webserver

Add the bundle in your project
------------------------------

For Symfony < 2.7
~~~~~~~~~~~~~~~~~

.. code-block:: json

  {
      "require": {
          "snowcap/im-bundle": "~1.0"
      }
  }

For Symfony >= 2.7
~~~~~~~~~~~~~~~~~~

.. code-block:: json

  {
      "require": {
          "snowcap/im-bundle": "~2.0"
      }
  }

Activate the bundle
-------------------

app/AppKernel.php

.. code-block:: php

  new Snowcap\ImBundle\SnowcapImBundle(),

Add routing
-----------

app/config/routing.yml

.. code-block:: yaml

  snowcap_im:
    resource: "@SnowcapImBundle/Resources/config/routing.yml"
