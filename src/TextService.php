<?php

namespace mindplay\publish;

/**
 * A helper service implementing text functions such as length, split and
 * word-wrap, but using approximations for proportional fonts.
 *
 * The approximation is based on "lorem ipsum", english letter frequency,
 * and average character widths of proportional fonts - the resulting unit
 * of length is therefore something pretty close to monospace width, which
 * is best explained by the following example:
 *
 *     $lipsum = '<500 words of lorem ipsum...>';
 *
 *     var_dump(strlen($lipsum)); // => int(3432)
 *
 *     $text = new TextService();
 *
 *     var_dump($text->length($lipsum)); // => double(3053.079176)
 *
 * Similarly, the wordwrap and split functions will split the lorem ipsum
 * sample to 55 lines, whereas {@link wordwrap()} splits to 60 lines.
 *
 * Neither is perfect, but good enough for government work.
 */
class TextService
{
    /**
     * @type float average letter width relative to monospace (based on english letter frequency)
     */
    const COMPENSATION = 2.27;

    /**
     * @var float[] map of average proportional letter widths
     */
    public static $WIDTHS = array(
        0 => 0.1847,
        1 => 0.2753,
        2 => 0.3659,
        3 => 0.4565,
        4 => 0.5471,
        5 => 0.6377,
        6 => 0.7282,
        7 => 0.9094,
        8 => 1,
    );

    /**
     * @var int[] map of characters to letter width indices
     *
     * @see $WIDTHS
     */
    public static $CHARS = array(
        33 => 0, # !
        34 => 2, # "
        35 => 4, # #
        36 => 4, # $
        37 => 7, # %
        38 => 5, # &
        39 => 0, # '
        40 => 2, # (
        41 => 2, # )
        42 => 2, # *
        43 => 4, # +
        44 => 1, # ,
        45 => 2, # -
        46 => 1, # .
        47 => 1, # /
        48 => 4, # 0
        49 => 4, # 1
        50 => 4, # 2
        51 => 4, # 3
        52 => 4, # 4
        53 => 4, # 5
        54 => 4, # 6
        55 => 4, # 7
        56 => 4, # 8
        57 => 4, # 9
        58 => 1, # :
        59 => 1, # ;
        60 => 4, # <
        61 => 4, # =
        62 => 4, # >
        63 => 4, # ?
        64 => 8, # @
        65 => 6, # A
        66 => 5, # B
        67 => 5, # C
        68 => 5, # D
        69 => 4, # E
        70 => 4, # F
        71 => 6, # G
        72 => 5, # H
        73 => 0, # I
        74 => 3, # J
        75 => 5, # K
        76 => 4, # L
        77 => 6, # M
        78 => 5, # N
        79 => 6, # O
        80 => 4, # P
        81 => 6, # Q
        82 => 5, # R
        83 => 5, # S
        84 => 4, # T
        85 => 5, # U
        86 => 6, # V
        87 => 7, # W
        88 => 5, # X
        89 => 6, # Y
        90 => 5, # Z
        91 => 1, # [
        92 => 1, # \
        93 => 1, # ]
        94 => 3, # ^
        95 => 4, # _
        96 => 2, # `
        97 => 4, # a
        98 => 4, # b
        99 => 4, # c
        100 => 4, # d
        101 => 4, # e
        102 => 2, # f
        103 => 4, # g
        104 => 4, # h
        105 => 0, # i
        106 => 0, # j
        107 => 3, # k
        108 => 0, # l
        109 => 6, # m
        110 => 4, # n
        111 => 4, # o
        112 => 4, # p
        113 => 4, # q
        114 => 2, # r
        115 => 4, # s
        116 => 1, # t
        117 => 4, # u
        118 => 4, # v
        119 => 7, # w
        120 => 4, # x
        121 => 4, # y
        122 => 4, # z
        123 => 2, # {
        124 => 0, # |
        125 => 2, # }
        126 => 4, # ~
    );

    /**
     * @param string $text text to measure
     *
     * @return float length (in average monospace units)
     */
    public function length($text)
    {
        $length = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = substr($text, $i, 1);

            $length += @self::$WIDTHS[self::$CHARS[ord($char)]];
        }

        return $length * self::COMPENSATION;
    }

    /**
     * Proportional word-wrap (approximation)
     *
     * Attempts to work as a drop-in replacement for {@link wordwrap()}
     *
     * @link http://www.php.net/manual/en/function.wordwrap.php#82580
     *
     * @param string    $text  text to word-wrap
     * @param int|float $width max. length (in average monospace units)
     * @param string    $break string to insert between lines
     *
     * @return string
     */
    public function wordwrap($text, $width = 75, $break = "\n")
    {
        return implode($break, $this->split($text, $width));
    }

    /**
     * Proportional string split (approximation)
     *
     * Attempts to work as a drop-in replacement for {@link str_split()}
     *
     * @link http://www.php.net/manual/en/function.wordwrap.php#82580
     *
     * @param string    $text  text to word-wrap
     * @param int|float $width max. length (in average monospace units)
     *
     * @return string[] lines of text
     */
    public function split($text, $width = 75)
    {
        $width /= self::COMPENSATION;

        $lines = array();
        $line = '';
        $word = '';
        $word_w = 0;
        $line_w = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = substr($text, $i, 1);

            if ($char === ' ') {
                $word = trim($word);

                if (strlen($word)) {
                    if ($line_w + $word_w >= $width) {
                        $lines[] = $line;
                        $line = $word;
                        $line_w = $word_w;
                    } else {
                        $line .= (strlen($line) ? ' ' : '') . $word;
                        $line_w += $word_w;
                    }
                }

                $word = '';
                $word_w = 0;
            } else {
                if ($char == "\n") {
                    $lines[] = $line . ' ' . $word;
                    $line = '';
                    $line_w = 0;
                    $word = '';
                    $word_w = 0;
                } else {
                    $word .= $char;
                    $word_w += @self::$WIDTHS[self::$CHARS[ord($char)]];
                }
            }
        }

        if (strlen($word)) {
            $line .= ' ' . $word;
        }

        if (strlen($line)) {
            $lines[] = $line;
        }

        return $lines;
    }
}
