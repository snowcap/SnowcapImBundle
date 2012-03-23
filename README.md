# Snowcap IM Bundle

# Introduction

Imagemagick wrapper for Symfony2.   
It's a general wrapper to access imagemagick command line functions, instead of using bindings like iMagick, which doesn't cover all the imagemagick functionalities.

It allows you to use all the convert/mogrify power, from your controller or your views

## Usage

### Twig Tag

<pre>
{% imresize %}
  &lt;p>some content&lt;/p>
  &lt;img src="/some/path" width="100" />
  &lt;img src="{{ asset('/some/path') }}" width="100" />
{% endimresize %}  
</pre>

This will parse all content inside the tag and render image caches regarding their HTML width and/or height attributes

### Twig function

<pre>
  &lt;img src="{{ imresize('/some/path','small') }}"/>
  &lt;img src="{{ imresize('/some/path','120x') }}"/>
  &lt;img src="{{ imresize('/some/path','x120') }}"/>
  &lt;img src="{{ imresize('/some/path','120x120') }}"/>
</pre>
The format - the second argument - can be a predefined format in your configuration, or a [width]x[height] syntax

### Twig filter

<pre>
  &lt;img src="{{ '/some/path' | imresize('small') }}"/>
  &lt;img src="{{ asset('/some/path') | imresize('small') }}"/>
</pre>

### From a controller

<pre>
$im = $this->get("snowcap_im.manager");

// to create a cached file
$im->convert($format, $path);

// to resize the source file
$im->mogrify($format, $path);
</pre>

## Installation

### Requirements

* You need to have the ImageMagick binaries available (convert & mogrify)
* You need to have a cache folder in your web dir, writeable by the webserver

### Clone the repo

put the following in your <code>deps</code> file
<pre>
[SnowcapImBundle]
    git=https://github.com/snowcap/SnowcapImBundle.git
    target=/bundles/Snowcap/ImBundle
</pre>

### Activate the bundle

<code>app/autoload.php</code>
<pre>
    'Snowcap'          => __DIR__.'/../vendor/bundles',
</pre>

<code>app/AppKernel.php</code>
<pre>
new Snowcap\ImBundle\SnowcapImBundle(),
</pre>

<code>app/config/routing.yml</code>
<pre>
snowcap_im:
  resource: "@SnowcapImBundle/Resources/config/routing.yml"
</pre>

## Configuration

<code>app/config.yml</code>
<pre>
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
</pre>

## Clearing the cache

You can clear the cache with the following command-line task

<pre>
./app/console snowcap:im:clear [age]
</pre>
Where the age argument - optionnal - will only clear cache older than the argument

