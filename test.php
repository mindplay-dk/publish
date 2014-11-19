<?php

require __DIR__ . '/src/TextService.php';

use mindplay\publish\TextService;

/**
 * 500 words of lorem ipsum
 *
 * @link http://www.lipsum.com/
 */

$lipsum = 'Morbi tincidunt ante nec velit bibendum consectetur. Sed faucibus, felis nec dictum efficitur, purus turpis laoreet magna, in fermentum elit arcu sit amet tellus. Donec scelerisque euismod enim, sed mollis lacus placerat ac. Donec finibus mi sit amet diam elementum scelerisque. Vestibulum a tincidunt elit. Nam luctus, odio sed tincidunt iaculis, nunc sem lacinia urna, in maximus enim velit vitae risus. In hac habitasse platea dictumst. Nullam auctor condimentum aliquet. Etiam tristique semper augue in posuere. Vestibulum mattis congue finibus. Sed malesuada eget elit vitae sollicitudin. Donec eu erat lectus. Maecenas mauris nulla, egestas nec dignissim sed, dictum in mi. Ut consequat diam ligula, eu finibus ex luctus eu. Ut sollicitudin sem eget arcu molestie, dapibus efficitur nisl semper. Quisque volutpat ut metus nec iaculis. Integer posuere laoreet orci, at eleifend ex placerat sed. Curabitur malesuada sapien et urna iaculis ullamcorper. Nulla sodales odio ac finibus vehicula. Suspendisse iaculis mi non ultrices commodo. Vivamus tincidunt porta elit et consectetur. Curabitur in accumsan ante, id viverra eros. Integer vulputate sodales suscipit. Quisque ut massa nec ex tempor condimentum eu id erat. Nam maximus rutrum orci sit amet lacinia. Cras blandit dignissim mauris, in interdum neque blandit non. Ut a porttitor ante, ut efficitur mi. Nam malesuada, mi eu laoreet feugiat, ipsum turpis faucibus risus, eget porttitor odio ligula sed neque. Nam augue lectus, convallis sit amet elementum at, hendrerit sit amet enim. Proin consectetur lectus vitae sollicitudin facilisis. Quisque elementum nibh varius mollis tincidunt. Praesent maximus porttitor laoreet. Vivamus egestas blandit magna. Integer a consectetur nisi. Ut sit amet justo suscipit, tincidunt libero a, mollis augue. Sed eget accumsan eros, vel fringilla odio. Aliquam vel tellus arcu. Proin lobortis tortor porttitor, aliquam libero at, faucibus urna. Nullam posuere gravida felis nec ultricies. Curabitur imperdiet ornare quam sit amet molestie. Nunc efficitur congue metus id bibendum. Sed pretium semper nibh sit amet maximus. Quisque porttitor pulvinar orci, ullamcorper condimentum ex rhoncus eget. Fusce vel ante in est egestas efficitur. Pellentesque lacinia condimentum ornare. Praesent tincidunt interdum tempor. Quisque magna justo, volutpat eu rhoncus ac, ultricies blandit velit. Donec eget lectus dui. Donec condimentum vulputate ullamcorper. Suspendisse sed arcu sollicitudin, feugiat justo ac, ornare nisi. Mauris sit amet elementum purus. Nulla ut consequat leo. Maecenas posuere ornare vestibulum. Aenean ac dignissim dui. Pellentesque est leo, sagittis ut pulvinar eu, mattis eu urna. Integer ut elit ut nunc semper dapibus at et purus. Curabitur imperdiet tincidunt laoreet. Ut lobortis commodo commodo. Ut tempor, justo vitae finibus semper, ante augue rutrum ex, malesuada cursus massa lectus sit amet lorem. Vivamus eu magna non arcu ullamcorper gravida ut a dolor. Duis lectus nulla, tempor quis est vel, commodo condimentum ante. Suspendisse dignissim consectetur magna eu blandit. Vestibulum finibus, ex eu vehicula semper, neque nunc commodo elit, in blandit enim dui in tellus. Donec varius vitae tellus eu pellentesque. Aliquam magna ante, posuere sed tortor quis, posuere porttitor eros. Sed ut nisi justo. Fusce gravida cursus velit, eu hendrerit velit. Donec dignissim, libero consectetur congue bibendum, velit.';

/**
 * Based on english letter frequency
 *
 * @link http://en.wikipedia.org/wiki/Letter_frequency
 */

$freqs = array(1160,470,351,267,201,378,195,723,629,60,59,271,437,237,626,255,17,165,776,1667,149,65,675,2,162,3);

$letters = '';

foreach ($freqs as $index => $freq) {
    $letters .= str_repeat(chr(ord('a')+$index), $freq);
}

test(
    'Computes length in units sorta-similar to monospace',
    function () use ($lipsum, $letters) {
        $text = new TextService();

        $target = 0.88; // 88% accuracy is pretty good!

        expectNear($text->length($lipsum), strlen($lipsum), $target);
        expectNear($text->length($letters), strlen($letters), $target);
    }
);

test(
    'Breaks up paragraphs into lines of a maximum length',
    function () use ($lipsum) {
        $text = new TextService();

        $limit = 60;

        $lines = $text->split($lipsum, $limit);

        ok(count($lines) > 1, 'breaks up text into several lines', count($lines));

        $wordwrap = explode("\n", wordwrap($lipsum, $limit, "\n"));

        expectNear(count($lines), count($wordwrap), 0.9);

        foreach ($lines as $num => $line) {
            $length = $text->length($line);

            ok($length <= $limit, "line #{$num} length is below the limit", $length);
        }

        ok($text->wordwrap($lipsum, $limit, ' ') === $lipsum, 'wordwrap puts everything back together');
    }
);

test(
    'Marks up text as HTML with ellipsis',
    function () {
        $text = new TextService();

        $SAMPLE = 'Forsaking monastic tradition, twelve jovial friars gave up their vocation for a questionable existence on the flying trapeze.';

        eq($text->ellipsis($SAMPLE, 40, 1), '<span class="truncated"><span class="truncated-visible">Forsaking monastic tradition, twelve jovial<span class="truncated-ellipsis"></span></span><span class="truncated-invisible"> friars gave up their vocation for a questionable existence on the flying trapeze.</span></span>', 'truncates text to one line');
        eq($text->ellipsis($SAMPLE, 30, 2), '<span class="truncated"><span class="truncated-visible">Forsaking monastic tradition, twelve jovial friars gave up their<span class="truncated-ellipsis"></span></span><span class="truncated-invisible"> vocation for a questionable existence on the flying trapeze.</span></span>', 'truncates text to two lines');
        eq($text->ellipsis($SAMPLE, strlen($SAMPLE) + 50, 2), '<span class="truncated"><span class="truncated-visible">Forsaking monastic tradition, twelve jovial friars gave up their vocation for a questionable existence on the flying trapeze.</span></span>', 'does not truncate or add ellipsis if the given text fits in full');
    }
);

exit(status());

/**
 * @param float $value
 * @param float $expected
 * @param float $accuracy
 */
function expectNear($value, $expected, $accuracy) {
    $difference = 1 - abs(1 - ($value / $expected));

    $pct = 100 * $accuracy;

    ok($difference >= $accuracy, "{$value} should be near {$expected} by {$pct}%", round(100*$difference, 2).'%');
}

// https://gist.github.com/mindplay-dk/4260582

/**
 * @param string   $name     test description
 * @param callable $function test implementation
 */
function test($name, $function)
{
    echo "\n=== $name ===\n\n";

    try {
        call_user_func($function);
    } catch (Exception $e) {
        ok(false, "UNEXPECTED EXCEPTION", $e);
    }
}

/**
 * @param bool   $result result of assertion
 * @param string $why    description of assertion
 * @param mixed  $value  optional value (displays on failure)
 */
function ok($result, $why = null, $value = null)
{
    if ($result === true) {
        echo "- PASS: " . ($why === null ? 'OK' : $why) . ($value === null ? '' : ' (' . format($value) . ')') . "\n";
    } else {
        echo "# FAIL: " . ($why === null ? 'ERROR' : $why) . ($value === null ? '' : ' - ' . format($value, true)) . "\n";
        status(false);
    }
}

/**
 * @param mixed  $value    value
 * @param mixed  $expected expected value
 * @param string $why      description of assertion
 */
function eq($value, $expected, $why = null)
{
    $result = $value === $expected;

    $info = $result
        ? format($value)
        : "expected: " . format($expected, true) . ", got: " . format($value, true);

    ok($result, ($why === null ? $info : "$why ($info)"));
}

/**
 * @param mixed $value
 * @param bool  $verbose
 *
 * @return string
 */
function format($value, $verbose = false)
{
    if ($value instanceof Exception) {
        return get_class($value)
        . ($verbose ? ": \"" . $value->getMessage() . "\"" : '');
    }

    if (! $verbose && is_array($value)) {
        return 'array[' . count($value) . ']';
    }

    if (is_bool($value)) {
        return $value ? 'TRUE' : 'FALSE';
    }

    if (is_object($value) && !$verbose) {
        return get_class($value);
    }

    return print_r($value, true);
}

/**
 * @param bool|null $status test status
 *
 * @return int number of failures
 */
function status($status = null)
{
    static $failures = 0;

    if ($status === false) {
        $failures += 1;
    }

    return $failures;
}
