Configuration
=============

app/config/config.yml

.. code-block:: yaml

  snowcap_im:

    # the directory where your imagemagick binaries are (optional). Default: /usr/bin/
    binary_path: '/opt/local/bin/'

    # the public directory of your web application relative to the kernel root_path (optional). Default: ../web
    web_path: '../public'

    # the directory where the cached image are stored from your public directory (optional). Default: cache/im
    cache_path: 'images/cache'

    # the timeout in seconds for the imagemagick process (optional). Default: 60
    timeout: 300

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
