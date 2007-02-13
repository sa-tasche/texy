<?php

/**
 * Texy! universal text -> html converter
 * --------------------------------------
 *
 * This source file is subject to the GNU GPL license.
 *
 * @author     David Grudl aka -dgx- <dave@dgx.cz>
 * @link       http://texy.info/
 * @copyright  Copyright (c) 2004-2007 David Grudl
 * @license    GNU GENERAL PUBLIC LICENSE v2
 * @package    Texy
 * @category   Text
 * @version    $Revision$ $Date$
 */

// security - include texy.php, not this file
if (!defined('TEXY')) die();






/**
 * PHRASES MODULE CLASS
 *
 *   **strong**
 *   *emphasis*
 *   ***strong+emphasis***
 *   ^^superscript^^
 *   __subscript__
 *   ++inserted++
 *   --deleted--
 *   ~~cite~~
 *   "span"
 *   ~span~
 *   `....`
 *   ``....``
 */
class TexyPhraseModule extends TexyModule
{
    /** @var callback    Callback that will be called with newly created element */
    public $codeHandler;  // function myUserFunc($element)
    public $handler;

    public $allowed = array(
        '***'  => 'strong em',
        '**'  => 'strong',
        '*'   => 'em',
        '++'  => 'ins',
        '--'  => 'del',
        '^^'  => 'sup',
        '__'  => 'sub',
        '"'   => 'span',
        '~'   => 'span',
        '~~'  => 'cite',
        '""()'=> 'acronym',
        '()'  => 'acronym',
        '`'   => 'code',
        '``'  => '',
    );




    /**
     * Module initialization.
     */
    public function init()
    {
        // strong & em speciality *** ... *** !!! its not good!
        if (@$this->allowed['***'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\*)\*\*\*(?!\ |\*)(.+)<MODIFIER>?(?<!\ |\*)\*\*\*(?!\*)()<LINK>??()#U',
                $this->allowed['***']
            );

        /*
                '#((?>(?:[*+-^_"~`])+))(\S.+)<MODIFIER>?(?<!\ )(?1)<LINK>?#'
                '#(?<!$ch)\*\*\*(?!\ |\*)(.+)<MODIFIER>?    (?<!\ |\*)\*\*\*(?!\*)()<LINK>??()#U',
                '#(?<!\*)\*\*\*(?!\ |\*)(.+)<MODIFIER>?    (?<!\ |\*)\*\*\*(?!\*)()<LINK>??()#U',
                '#(?<!\*)\*\*  (?!\ |\*)(.+)<MODIFIER>?    (?<!\ |\*)\*\*  (?!\*)<LINK>??()#U',
                '#(?<!\*)\*    (?!\ |\*)(.+)<MODIFIER>?    (?<!\ |\*)\*    (?!\*)<LINK>??()#U',
                '#(?<!\+)\+\+  (?!\ |\+)(.+)<MODIFIER>?    (?<!\ |\+)\+\+  (?!\+)()#U',
                '#(?<!\-)\-\-  (?!\ |\-)(.+)<MODIFIER>?    (?<!\ |\-)\-\-  (?!\-)()#U',
                '#(?<!\^)\^\^  (?!\ |\^)(.+)<MODIFIER>?    (?<!\ |\^)\^\^  (?!\^)()#U',
                '#(?<!\_)\_\_  (?!\ |\_)(.+)<MODIFIER>?    (?<!\ |\_)\_\_  (?!\_)()#U',
                '#(?<!\")\"    (?!\ )   ([^\"]+)<MODIFIER>?(?<!\ )\"       (?!\")<LINK>??()#U',
                '#(?<!\~)\~    (?!\ )   ([^\~]+)<MODIFIER>?(?<!\ )\~       (?!\~)<LINK>??()#U',
                '#(?<!\~)\~\~  (?!\ |\~)(.+)<MODIFIER>?    (?<!\ |\~)\~\~  (?!\~)<LINK>??()#U',
                '#       \`\`           (\S[^:HASH:]*)     (?<!\ )\`\`                   ()#U',
                '#       \`             (\S[^:HASH:]*)<MODIFIER>?(?<!\ )\`               ()#U'
        */

        // **strong**
        if (@$this->allowed['**'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\*)\*\*(?!\ |\*)(.+)<MODIFIER>?(?<!\ |\*)\*\*(?!\*)<LINK>??()#U',
                $this->allowed['**']
            );

        // *emphasis*
        if (@$this->allowed['*'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\*)\*(?!\ |\*)(.+)<MODIFIER>?(?<!\ |\*)\*(?!\*)<LINK>??()#U',
                $this->allowed['*']
            );

        // ++inserted++
        if (@$this->allowed['++'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\+)\+\+(?!\ |\+)(.+)<MODIFIER>?(?<!\ |\+)\+\+(?!\+)()#U',
                $this->allowed['++']
            );

        // --deleted--
        if (@$this->allowed['--'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\-)\-\-(?!\ |\-)(.+)<MODIFIER>?(?<!\ |\-)\-\-(?!\-)()#U',
                $this->allowed['--']
            );

        // ^^superscript^^
        if (@$this->allowed['^^'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\^)\^\^(?!\ |\^)(.+)<MODIFIER>?(?<!\ |\^)\^\^(?!\^)()#U',
                $this->allowed['^^']
            );

        // __subscript__
        if (@$this->allowed['__'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\_)\_\_(?!\ |\_)(.+)<MODIFIER>?(?<!\ |\_)\_\_(?!\_)()#U',
                $this->allowed['__']
            );

        // "span"
        if (@$this->allowed['"'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\")\"(?!\ )([^\"]+)<MODIFIER>?(?<!\ )\"(?!\")<LINK>??()#U',
                $this->allowed['"']
            );
//      $this->texy->registerLinePattern($this, 'processPhrase',  '#()(?<!\")\"(?!\ )(?:.|(?R))+<MODIFIER>?(?<!\ )\"(?!\")<LINK>?()#',         $this->allowed['"']);

        // ~alternative span~
        if (@$this->allowed['~'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\~)\~(?!\ )([^\~]+)<MODIFIER>?(?<!\ )\~(?!\~)<LINK>??()#U',
                $this->allowed['~']
            );

        // ~~cite~~
        if (@$this->allowed['~~'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\~)\~\~(?!\ |\~)(.+)<MODIFIER>?(?<!\ |\~)\~\~(?!\~)<LINK>??()#U',
                $this->allowed['~~']
            );

        if (@$this->allowed['""()'] !== FALSE)
            // acronym/abbr "et al."((and others))
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<!\")\"(?!\ )([^\"]+)<MODIFIER>?(?<!\ )\"(?!\")\(\((.+)\)\)()#U',
                $this->allowed['""()']
            );

        if (@$this->allowed['()'] !== FALSE)
            // acronym/abbr NATO((North Atlantic Treaty Organisation))
            $this->texy->registerLinePattern(
                $this,
                'processPhrase',
                '#(?<![:CHAR:])([:CHAR:]{2,})()()()\(\((.+)\)\)#U<UTF>',
                $this->allowed['()']
            );


        // ``protected`` (experimental, dont use)
        if (@$this->allowed['``'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processProtect',
                '#\`\`(\S[^:HASH:]*)(?<!\ )\`\`()#U',
                FALSE
            );

        // `code`
        if (@$this->allowed['`'] !== FALSE)
            $this->texy->registerLinePattern(
                $this,
                'processCode',
                '#\`(\S[^:HASH:]*)<MODIFIER>?(?<!\ )\`()#U'
            );

        // `=samp
        $this->texy->registerBlockPattern(
            $this,
            'processBlock',
            '#^`=(none|code|kbd|samp|var|span)$#mUi'
        );
    }




    /**
     * Callback function: **.... .(title)[class]{style}**
     * @return string
     */
    public function processPhrase($parser, $matches, $tags)
    {
        list($match, $mContent, $mMod1, $mMod2, $mMod3, $mLink) = $matches;
        if ($mContent == NULL) {
            preg_match('#^(.)+(.+)'.TEXY_PATTERN_MODIFIER.'?\\1+()$#U', $match, $matches);
            list($match, $mDelim, $mContent, $mMod1, $mMod2, $mMod3, $mLink) = $matches;
        }
        //    [1] => ...
        //    [2] => (title)
        //    [3] => [class]
        //    [4] => {style}

        if (($tags == 'span') && $mLink) $tags = ''; // eliminate wasted spans, use <a ..> instead
        if (($tags == 'span') && !$mMod1 && !$mMod2 && !$mMod3) return $match; // don't use wasted spans...
        $tags = array_reverse(explode(' ', $tags));
        $el = NULL;

        foreach ($tags as $tag) {
            $el = new TexyInlineTagElement($this->texy);
            $el->tag = $tag;
            if ($tag == 'acronym' || $tag == 'abbr') { $el->modifier->title = $mLink; $mLink=''; }

            if ($this->handler)
                if (call_user_func_array($this->handler, array($el, $tags)) === FALSE) return '';

            $mContent = $parser->element->appendChild($el, $mContent);
        }

        if ($mLink) {
            $el = new TexyLinkElement($this->texy);
            $el->setLinkRaw($mLink, $mContent);
            $mContent = $parser->element->appendChild($el, $mContent);
        }

        if ($el)
            $el->modifier->setProperties($mMod1, $mMod2, $mMod3);


        return $mContent;
    }





    /**
     * Callback function `=code
     */
    public function processBlock($parser, $matches)
    {
        list(, $mTag) = $matches;
        //    [1] => ...

        $this->allowed['`'] = strtolower($mTag);
        if ($this->allowed['`'] == 'none') $this->allowed['`'] = '';
    }






    /**
     * Callback function: `.... .(title)[class]{style}`
     * @return string
     */
    public function processCode($parser, $matches)
    {
        list(, $mContent, $mMod1, $mMod2, $mMod3) = $matches;
        //    [1] => ...
        //    [2] => (title)
        //    [3] => [class]
        //    [4] => {style}

        $texy = $this->texy;
        $el = new TexyTextualElement($texy);
        $el->modifier->setProperties($mMod1, $mMod2, $mMod3);
        $el->contentType = TexyDomElement::CONTENT_TEXTUAL;
        $el->setContent($mContent, FALSE);  // content isn't html safe
        $el->tag = $this->allowed['`'];

        if ($this->codeHandler)
            if (call_user_func_array($this->codeHandler, array($el, 'code')) === FALSE) return '';

        $el->safeContent(); // ensure that content is HTML safe

        return $parser->element->appendChild($el);
    }








    /**
     * User callback - PROTECT PHRASE
     * @return string
     */
    public function processProtect($parser, $matches, $isHtmlSafe = FALSE)
    {
        list(, $mContent) = $matches;

        $el = new TexyTextualElement($this->texy);
        $el->contentType = TexyDomElement::CONTENT_TEXTUAL;
//    $el->contentType = TexyDomElement::CONTENT_BLOCK;
        $el->setContent( Texy::freezeSpaces($mContent), $isHtmlSafe );

        return $parser->element->appendChild($el);
    }




} // TexyPhraseModule