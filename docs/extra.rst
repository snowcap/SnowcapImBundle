Extra information
=================

Image transformations
---------------------

To know more about the possible options for the formats, you'll find the exhaustive list on the ImageMagick website

Below you'll find a list of the possible syntaxes available in the *resize* and *thumbnail* format options

=================   ==================================================================================================
Size                Description
=================   ==================================================================================================
scale%              Height and width both scaled by specified percentage.
scale-x%xscale-y%   Height and width individually scaled by specified percentages. (Only one % symbol needed.)
width               Width given, height automagically selected to preserve aspect ratio.
xheight             Height given, width automagically selected to preserve aspect ratio.
widthxheight        Maximum values of height and width given, aspect ratio preserved.
widthxheight^       Minimum values of width and height given, aspect ratio preserved.
widthxheight!       Width and height emphatically given, original aspect ratio ignored.
widthxheight>       Shrinks images with dimension(s) larger than the corresponding width and/or height dimension(s).
widthxheight<       Enlarges images with dimension(s) smaller than the corresponding width and/or height dimension(s).
area@               Resize image to have specified area in pixels. Aspect ratio is preserved.
=================   ==================================================================================================


Form type
---------

ImBundle comes with a form type extension for the SnowcapCoreBundle Image form type. It allows you to specify a format for the image preview displayed next to the field.

.. code-block:: php

  $builder->add(
      'picture',
      'snowcap_core_image',
      array(
          'file_path' => 'picturePath',
          'im_format' => '200x200',
      )
  );

The code above will display an image preview resized to 200x200px next to the image field, very interesting for an admin for example.