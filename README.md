# use BootPress\Blog\Component as Blog;

[![Packagist][badge-version]][link-packagist]
[![License MIT][badge-license]](LICENSE.md)
[![HHVM Tested][badge-hhvm]][link-travis]
[![PHP 7 Supported][badge-php]][link-travis]
[![Build Status][badge-travis]][link-travis]
[![Code Climate][badge-code-climate]][link-code-climate]
[![Test Coverage][badge-coverage]][link-coverage]

A file based blog that can be implemented in any project.  Includes featured, future, and similar posts, pages, listings, archives, authors, tags, categories, sitemaps, feeds, and full-text searching.  No admin necessary.

## Installation

Add the following to your ``composer.json`` file.

``` bash
{
    "require": {
        "bootpress/blog": "^1.0"
    }
}
```

Create an ``.htaccess`` file in your website's public root folder to redirect everything that doesn't exist to an ``index.php`` file.

```htaccess
# Prevent directory browsing
Options All -Indexes

# Turn on URL re-writing (remove 'example.com/' if not on localhost)
RewriteEngine On
RewriteBase /example.com/

# If the file exists, then that's all folks
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule .+ - [L]

# For everything else, there's BootPress
RewriteRule ^(.*)$ index.php [L]
```

Your ``index.php`` file should then look something like this:

```php
<?php

use BootPress\Page\Component as Page;
use BootPress\Blog\Component as Blog;
use BootPress\Asset\Component as Asset;
use BootPress\Sitemap\Component as Sitemap;

$autoloader = require '../vendor/autoload.php';

// Setup the page
$page = Page::html(array(
    'dir' => '../page', // a private (root) directory
    'base' => 'http://localhost/example.com',
    'suffix' => '.html',
));
$html = '';

// Deliver sitemap and assets first
if ($asset = Asset::cached('assets')) {
    $page->send($asset);
} elseif ($xml = Sitemap::page()) {
    $page->send($xml);
}

// Implement a blog
$blog = new Blog();
if ($template = $blog->page()) {
    if (empty($template['file'])) { // A 'txt', 'json', 'xml', 'rdf', 'rss', or 'atom' page
        $page->send(Asset::dispatch($template['type'], $template['vars']['content']));
    } else { // An 'index.html.twig' file
        $html = $blog->theme->renderTwig($template);
    }
}

// Create the layout
$html = $page->display($blog->theme->layout($html));

// Send to user
$page->send(Asset::dispatch('html', $html));
```

## Setup Blog

Create a ``../page/blog/config.yml`` file with the following information:

```yaml
blog:
    name: Example # The name of your website
    image: logo.png # The main image relative to this directory
    listings: blog # The url base for all your listing pages - authors, archives, tags, etc.
    breadcrumb: Blog # How to reference the listings in your breadcrumbs array
    theme: default # The main theme for your site
```

You can access any of these in your Twig templates eg. ``{{ blog.name }}``, including the ``{{ blog.page }}`` you are on.  Eventually this file will be full of authors, categories, and tags that you can easily manage as well.  You can create a [Bootstrap list group](http://getbootstrap.com/components/#list-group) of categories by:

```twig
<ul class="list-group">
    {% for category in blog.query('categories') %}
        <li class="list-group-item">
            <span class="badge">{{ category.count }}</span>
            <a href="{{ category.url }}">{{ category.name }}</a>
            {# if category.subs #}
        </li>
    {% endfor %}
</ul>
```

Other ``{{ blog.query(...) }}``'s include '**tags**', '**authors**', '**archives**', '**recent**', '**featured**', '**similar**', '**posts**', and **[...]** listings of every sort, otherwise known as "The Loop".

## Create Content

A BootPress Blog is a flat-file CMS, which means you don't need any fancy admin interface to manage all of the content that is scattered througout a database.  You simply create files.  All of your blog's posts and pages will reside in the ``../page/blog/content/`` directory, and if you look at a URL, you will be able to follow the folders straight to your ``index.html.twig`` file. For example:

| URL                                   | File                                                         |
| ------------------------------------- | ------------------------------------------------------------ |
| /                                     | blog/content/index.html.twig                                 |
| /feed.rss                             | blog/content/feed.rss.twig                                   |
| /about-me.html                        | blog/content/about-me/index.html.twig                        |
| /category/post.html                   | blog/content/category/post/index.html.twig                   |
| /category/subcategory/long-title.html | blog/content/category/subcategory/long-title/index.html.twig |

Why not have the '**/about-me.html**' URL file at '**content/about-me.html.twig**' instead of '**content/about-me/index.html.twig**' instead, right? This is so you can have all of the assets that you want to use, right there where you want to use them.  Linking to them is even easier.  Place an '**image.jpg**' in the '**content/about-me/**' folder, and link to ``{{ 'image.jpg'|asset }}`` in the '**index.html.twig**' file. Would you like to resize that?  Try an ``{{ 'image.jpg?w=300'|asset }}``. To see all the options, check out the [Quick Reference "Glide"](http://glide.thephpleague.com/1.0/api/quick-reference/).

Non-HTML files are accessed according to the '**/feed.rss**' URL example above.

## Twig Templates

Every ``index.html.twig`` file is a Twig template that receives the [BootPress Page Component](https://packagist.org/packages/bootpress/page), so that you can interact with your HTML Page.  The methods available to you are:

- ``{{ page.set() }}`` - Set HTML Page properties.  Things like the title, keywords (tags), author, etc.
- ``{{ page.url() }} `` - Either create a url, or manipulate it's query string and fragment.
- ``{{ page.get() }} `` - Access $_GET parameters.
- ``{{ page.post() }}`` - Access $_POST parameters.
- ``{{ page.tag() }} `` - Generate an HTML tag programatically.
- ``{{ page.meta() }} `` - Insert ``<meta>`` tag(s) into the ``<head>`` section of your page.
- ``{{ page.link() }} `` - Include js, css, ico, etc links in your page.
- ``{{ page.style() }} `` - Add CSS ``<style>`` formatting to the ``<head>`` of your page.
- ``{{ page.script() }} `` - Add Java``<script>`` code to the bottom of your page.
- ``{{ page.jquery() }} `` - Put some jQuery into your ``$(document).ready(function(){...})``.
- ``{{ page.id() }} `` - Get a unique id to reference in your CSS or JavaScript.

The main one you will use everytime is ``{{ page.set() }}`` like so:

```twig
{{ page.set({
    title: 'A Flowery Post',
    description: 'Aren\'t they beautiful?',
    keywords: 'flowers, nature',
    image: 'flowers.jpg',
    published: 'January 1, 2015'
}) }}
```

The Page properties you can set (and retrieve) are:

- '**title**' - The page ``<title>``.
- '**description**' - The meta description of this page.
- '**keywords**' - A comma-separated list of keywords for tagging your blog posts.
- '**robots**' - If set to false then we will not sitemap this page, and the robots will be told to go away.
- '**theme**' - To use a different theme then the one used by default.
- '**image**' - The main image for this page (if any).
- '**author**' - The post author's name.
- '**featured**' - If set to true then it will be displayed before all other posts.  Otherwise known as a "sticky post".
- '**published**' - A date (eg. ``'Jan 1, 2015'``) if this is a post, or ``true`` if it is a page.  If ``false`` (the default) then we consider it unpublished and won't tell anyone.  If a date is in the future then we will wait until then before publishing.
- ... and any other value that you want to set and retrieve later on.  The above just have special meanings to us.

To make things even easier, you can put all that information in YAML format within a Twig comment at the top of the page.  For example:

```twig
{#
title: A Flowery Post
description: Aren't they beautiful?
keywords: flowers, nature
image: flowers.jpg
published: January 1, 2015
#}

{% markdown %}

These are my flowers:

<img src="{{ 'flowers.jpg'|asset }}">

Aren't they ***beautiful***?

{% endmarkdown %}
```

When you check ``if ($template = $blog->page()) { ... }`` we will look for the corresponding URL Twig File, and if it is there, your ``$template`` will be an array with the following keys:

- '**file**' - The appropriate Twig template that is equipped to deal with these '**type**' of '**vars**'.  If this is a 'txt', 'json', 'xml', 'rdf', 'rss', or 'atom' page then it will be empty.
- '**type**' - The kind of Blog page you are working with.  Either 'page', 'txt', 'json', 'xml', 'rdf', 'rss', 'atom', 'post', 'category', 'index', 'archives', 'authors', or 'tags'.
- '**vars**' - Varies according to the '**type**', but if the '**file**' is empty, you can go ahead and ``$page->send(Asset::dispatch($template['type'], $template['vars']['content']));``.  We don't automatically send it for you, so that you can have the opportunity to log or cache the output before sending.

At this point, you have your blog info, and you can do anything you want with it.  You can implement a BootPress Blog into any project.  It is as flexible as flexible can be, but if you like the way we do things so far, then let's continue shall we?

## Themes

BootPress Themes live in your ``../page/blog/themes/`` folder.  Assuming you have selected the '**default**', when you ``$html = $blog->theme->renderTwig($template)``, it will pass the ``$template['vars']`` to the ``$template['file']`` in the '**../page/blog/themes/default/**' folder, and return your **$html**.  If the ``$template['file']`` does not exist, then a default one will be provided for you.  If at any time you are wondering what ``$template['vars']`` you have to work with, just ``{{ dump() }}`` them, and they will be all spelled out for you.

When you ``$blog->theme->layout($html)``, it will pass the **$html** ``{{ content }}`` to your '**../page/blog/themes/default/index.html.twig**' file which could look something like this:

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <title>{{ page.title }}</title>
    
    <link rel="stylesheet" href="{{ 'css/bootstrap.css'|asset }}">
    <!--[if lte IE 8]><script src="{{ 'js/html5shiv.js'|asset }}"></script><![endif]-->
</head>
<body>

    <!-- Content -->
    <div id="content">
        {{ content }}
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        {% include '@theme/sidebar.html.twig' %} 
    </div>

    <!-- Scripts -->
    <script src="{{ 'js/jquery.js'|asset }}"></script>
    <script src="{{ 'js/bootstrap.js'|asset }}"></script>
</body>
</html>
```

## Plugins

Plugins are [Twig macros](http://twig.sensiolabs.org/doc/templates.html#macros) that reside in your ``../page/blog/plugins/`` folder, and are easily accessed in any template via ``{% import '@plugin/name' as name %}``.  My recommendation is to follow the packagist naming schema of 'vendor/package' with the main file being 'macro.twig'.  For example, if you put the following at ``../page/blog/plugins/kylob/mailto/macro.twig``:

```twig
{% macro eval(string) %}

    {% set js = '' %}
    {% set string = 'document.write(' ~ json_encode(string) ~ ');' %}
    {% for i in range(0, string|length - 1) %}
        {% set js = js ~ '%' ~ bin2hex(string|slice(i, 1)) %}
    {% endfor %}
    <script type="text/javascript">eval(unescape('{{ js }}'))</script>
    
{% endmacro eval %}
```

You could then hide an email address from spam bots like so:

```twig
{% import '@plugin/kylob/mailto/macro.twig' as mailto %}

{{ mailto.eval('<a href="mailto:me@example.com">Contact Me</a>') }}
```

Which would result in:

```html
<script type="text/javascript">eval(unescape('%64%6f%63%75%6d%65%6e%74%2e%77%72%69%74%65%28%22%3c%61%20%68%72%65%66%3d%5c%22%6d%61%69%6c%74%6f%3a%6d%65%40%65%78%61%6d%70%6c%65%2e%63%6f%6d%5c%22%3e%43%6f%6e%74%61%63%74%20%4d%65%3c%5c%2f%61%3e%22%29%3b'))</script>
```

You can pass variables among macros in the same namespace (on the same page) by setting ``{{ this(_self, 'key', 'value') }}`` in one macro, and accessing ``{{ this(_self, 'key') }}`` in another.  This allows you to create "properties" so that your macro plugins can behave a little more like "classes".  You can also use nearly every native php function that would be considered safe to use.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[badge-version]: https://img.shields.io/packagist/v/bootpress/blog.svg?style=flat-square&label=Packagist
[badge-license]: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square
[badge-hhvm]: https://img.shields.io/badge/HHVM-Tested-8892bf.svg?style=flat-square
[badge-php]: https://img.shields.io/badge/PHP%207-Supported-8892bf.svg?style=flat-square
[badge-travis]: https://img.shields.io/travis/Kylob/Blog/master.svg?style=flat-square
[badge-code-climate]: https://img.shields.io/codeclimate/github/Kylob/Blog.svg?style=flat-square
[badge-coverage]: https://img.shields.io/codeclimate/coverage/github/Kylob/Blog.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bootpress/blog
[link-travis]: https://travis-ci.org/Kylob/Blog
[link-code-climate]: https://codeclimate.com/github/Kylob/Blog
[link-coverage]: https://codeclimate.com/github/Kylob/Blog/coverage
