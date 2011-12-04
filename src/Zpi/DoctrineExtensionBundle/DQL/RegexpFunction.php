<?php    
namespace Zpi\DoctrineExtensionBundle\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
    
/**
 * RegexpFunction ::= "REGEXP" "(" StateFieldPathExpression "," StringPrimary ")" 
 * 
 * Funkcja rozszerzacjąca DQL o funkcję REGEXP MySQL'a
 * @author lyzkov
 */
class RegexpFunction extends FunctionNode
{
    public $firstParameter;
    public $regexString;
    
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstParameter = $parser->StateFieldPathExpression();
        $parser->match(Lexer::T_COMMA);
        $this->regexString = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
        
    }
    
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return '(' . $sqlWalker->walkStateFieldPathExpression($this->firstParameter) . ' REGEXP \'' .
            $this->regexString . '\')';
    }
}