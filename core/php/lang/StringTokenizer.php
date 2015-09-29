<?php
namespace php\lang;
/**
 * The string tokenizer class allows an application to break a string into tokens.
 *
 * @example The following is one example of the use of the tokenizer. The code:
 * <code>
 * <?php
 *    $str = 'this is:@\t\n a test!';
 *    $delim = ' !@:'\t\n; // remove these chars
 *    $st = new StringTokenizer($str, $delim);
 *    while ($st->hasMoreTokens()) {
 *        echo $st->nextToken() . "\n";
 *    }
 *    prints the following output:
 *      this
 *      is
 *      a
 *      test
 * ?>
 * </code>
 **/
 
class StringTokenizer {

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $delim;
    /**
     * Constructs a string tokenizer for the specified string
     * @param string $str String to tokenize
     * @param string $delim The set of delimiters (the characters that separate tokens)
     * specified at creation time, default to ' '
     */
    public function __construct(/*string*/ $str, /*string*/ $delim = ' ') {
        $this->token = strtok($str, $delim);
        $this->delim = $delim;
    }

    public function __destruct() {
        unset($this);
    }

    /**
     * Tests if there are more tokens available from this tokenizer's string. It
     * does not move the internal pointer in any way. To move the internal pointer
     * to the next element call nextToken()
     * @return boolean - true if has more tokens, false otherwise
     */
    public function hasMoreTokens() {
        return ($this->token !== false);
    }

    /**
     * Returns the next token from this string tokenizer and advances the internal
     * pointer by one.
     * @return string - next element in the tokenized string
     */
    public function nextToken() {
        $current = $this->token;
        $this->token = strtok($this->delim);
        return $current;
    }
}
?>