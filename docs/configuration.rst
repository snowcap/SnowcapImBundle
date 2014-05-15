Configuration
=============

app/config.yml

.. code-block:: yaml

  snowcap_im:

    # optional, the directory where your imagemagick binaries are. Default: /usr/bin/
    binary_path: '/opt/local/bin/'

    # optional too, a list of pre-defined conversions
    formats:
      # resizes to 50 width, at 80% quality
      small:
        resize: 50x
        quality: 80

      # resizes to fit in 100x100, only if bigger, and remove all crap (meta-data, ...)
      medium:
        thumbnail: 100x100>

      # crop to get exactly 100x100, keeping the maximum area possible, centered
      square:
        resize: 100x100^
        gravity: center
        crop: 100x100+0+0
