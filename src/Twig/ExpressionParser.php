<?php

namespace BootPress\Blog\Twig;

use Twig_Token;
use Twig_Node_Expression_Array;

class ExpressionParser extends \Twig_ExpressionParser
{
    public function parseArrayExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(Twig_Token::PUNCTUATION_TYPE, '[', 'An array element was expected');

        $node = new Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $first = true;
        while (!$stream->test(Twig_Token::PUNCTUATION_TYPE, ']')) {
            if (!$first) {
                $stream->expect(Twig_Token::PUNCTUATION_TYPE, ',', 'An array element must be followed by a comma');

                // trailing ,?
                if ($stream->test(Twig_Token::PUNCTUATION_TYPE, ']')) {
                    break;
                }
            }
            $first = false;

            // $node->addElement($this->parseExpression());
            $value = $this->parseExpression();
            if ($stream->test(Twig_Token::PUNCTUATION_TYPE, ':')) {
                $stream->expect(Twig_Token::PUNCTUATION_TYPE, ':');
                $node->addElement($this->parseExpression(), $value);
            } else {
                $node->addElement($value);
            }
        }
        $stream->expect(Twig_Token::PUNCTUATION_TYPE, ']', 'An opened array is not properly closed');

        return $node;
    }
}
