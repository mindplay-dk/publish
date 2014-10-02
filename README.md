mindplay/publish
================

This library provides a service/component implementing text functions such as
length, split and word-wrap, but using approximations for proportional fonts.

The approximation is based on "lorem ipsum", english letter frequency, and the
average character widths of proportional fonts - the resulting unit of length
is therefore something pretty close to monospace width, which is best explained
by the following example:

    $lipsum = '<500 words of lorem ipsum...>';

    var_dump(strlen($lipsum)); // => int(3432)

    $text = new TextService();

    var_dump($text->length($lipsum)); // => double(3053.079176)

The `wordwrap()` and `split()` methods, for a line length of 60 units, will
split the 500-word lorem ipsum sample to 55 lines, whereas `wordwrap()` will
split the same sample into 60 lines.

Neither is perfect, but good enough for government work.
