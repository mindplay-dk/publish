mindplay/publish
================

This library provides a service/component implementing text functions such as
length, split and word-wrap, but using approximations for proportional fonts.

The approximation is based on "lorem ipsum", english letter frequency, and the
average character widths of proportional fonts - the resulting unit of length
is therefore something pretty close to monospace width, which is best explained
by the following example:

```PHP
$lipsum = '<500 words of lorem ipsum...>';

var_dump(strlen($lipsum)); // => int(3432)

$text = new TextService();

var_dump($text->length($lipsum)); // => double(3053.079176)
```

The `wordwrap()` and `split()` methods, for a line length of 60 units, will
split the 500-word lorem ipsum sample to 55 lines, whereas `wordwrap()` will
split the same sample into 60 lines.

Neither is perfect, but good enough for government work.

It's also possible to mark up text as truncated HTML with ellipsis, in an SEO
friendly fashion - this is useful for example to truncate headlines which may
be longer than desirable, but you don't want search engines to index headlines
that have been truncated. Example:

```PHP
$text = new TextService();

$content = 'Forsaking monastic tradition, twelve jovial friars gave up their vocation for a questionable existence on the flying trapeze.';

echo $text->ellipsis($content, 30, 2);
```

From which the output would be:

```HTML
<span class="truncated">
    <span class="truncated-visible">
        Forsaking monastic tradition, twelve jovial friars gave up their
        <span class="truncated-ellipsis"></span>
    </span>
    <span class="truncated-invisible"> vocation for a questionable existence on the flying trapeze.</span>
</span>
```

Using [CSS](http://jsfiddle.net/mindplay/1p67r5s9/) you can make this content appear
as truncated, without actually discarding any content.
