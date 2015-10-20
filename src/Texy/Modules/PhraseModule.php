<?php

/**
 * This file is part of the Texy! (http://texy.info)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Texy\Modules;

use Texy;
use Texy\Patterns;


/**
 * Phrases module.
 */
final class PhraseModule extends Texy\Module
{

	public $tags = [
		'phrase/strong' => 'strong', // or 'b'
		'phrase/em' => 'em', // or 'i'
		'phrase/em-alt' => 'em',
		'phrase/em-alt2' => 'em',
		'phrase/ins' => 'ins',
		'phrase/del' => 'del',
		'phrase/sup' => 'sup',
		'phrase/sup-alt' => 'sup',
		'phrase/sub' => 'sub',
		'phrase/sub-alt' => 'sub',
		'phrase/span' => 'a',
		'phrase/span-alt' => 'a',
		'phrase/cite' => 'cite',
		'phrase/acronym' => 'acronym',
		'phrase/acronym-alt' => 'acronym',
		'phrase/code' => 'code',
		'phrase/quote' => 'q',
		'phrase/quicklink' => 'a',
	];


	/** @var bool  are links allowed? */
	public $linksAllowed = TRUE;


	public function __construct($texy)
	{
		$this->texy = $texy;

		$texy->addHandler('phrase', [$this, 'solve']);

/*
		// UNIVERSAL
		$texy->registerLinePattern(
			array($this, 'patternPhrase'),
			'#((?>([*+/^_"~`-])+?))(?!\s)(.*(?!\\2).)'.Texy\Patterns::MODIFIER.'?(?<!\s)\\1(?!\\2)(?::('.Texy\Patterns::LINK_URL.'))??()#Uus',
			'phrase/strong'
		);
*/

		// ***strong+emphasis***
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\*)\*\*\*(?![\s*])((?:[^ *]++|[ *])+)'.Patterns::MODIFIER.'?(?<![\s*])\*\*\*(?!\*)(?::('.Patterns::LINK_URL.'))??()#Uus',
			'phrase/strong+em'
		);

		// **strong**
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\*)\*\*(?![\s*])((?:[^ *]++|[ *])+)'.Patterns::MODIFIER.'?(?<![\s*])\*\*(?!\*)(?::('.Patterns::LINK_URL.'))??()#Uus',
			'phrase/strong'
		);

		// //emphasis//
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<![/:])\/\/(?![\s/])((?:[^ /]++|[ /])+)'.Patterns::MODIFIER.'?(?<![\s/:])\/\/(?!\/)(?::('.Patterns::LINK_URL.'))??()#Uus',
			'phrase/em'
		);

		// *emphasisAlt*
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\*)\*(?![\s*])((?:[^\s*]++|[*])+)'.Patterns::MODIFIER.'?(?<![\s*])\*(?!\*)(?::('.Patterns::LINK_URL.'))??()#Uus',
			'phrase/em-alt'
		);

		// *emphasisAlt2*
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<![^\s.,;:<>()"\''.Patterns::MARK.'-])\*(?![\s*])((?:[^ *]++|[ *])+)'.Patterns::MODIFIER.'?(?<![\s*])\*(?![^\s.,;:<>()"?!\'-])(?::('.Patterns::LINK_URL.'))??()#Uus',
			'phrase/em-alt2'
		);

		// ++inserted++
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\+)\+\+(?![\s+])((?:[^\r\n +]++|[ +])+)'.Patterns::MODIFIER.'?(?<![\s+])\+\+(?!\+)()#Uu',
			'phrase/ins'
		);

		// --deleted--
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<![<-])\-\-(?![\s>-])((?:[^\r\n -]++|[ -])+)'.Patterns::MODIFIER.'?(?<![\s<-])\-\-(?![>-])()#Uu',
			'phrase/del'
		);

		// ^^superscript^^
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\^)\^\^(?![\s^])((?:[^\r\n ^]++|[ ^])+)'.Patterns::MODIFIER.'?(?<![\s^])\^\^(?!\^)()#Uu',
			'phrase/sup'
		);

		// m^2 alternative superscript
		$texy->registerLinePattern(
			[$this, 'patternSupSub'],
			'#(?<=[a-z0-9])\^([n0-9+-]{1,4}?)(?![a-z0-9])#Uui',
			'phrase/sup-alt'
		);

		// __subscript__
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\_)\_\_(?![\s_])((?:[^\r\n _]++|[ _])+)'.Patterns::MODIFIER.'?(?<![\s_])\_\_(?!\_)()#Uu',
			'phrase/sub'
		);

		// m_2 alternative subscript
		$texy->registerLinePattern(
			[$this, 'patternSupSub'],
			'#(?<=[a-z])\_([n0-9]{1,3})(?![a-z0-9])#Uui',
			'phrase/sub-alt'
		);

		// "span"
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\")\"(?!\s)((?:[^\r "]++|[ ])+)'.Patterns::MODIFIER.'?(?<!\s)\"(?!\")(?::('.Patterns::LINK_URL.'))??()#Uu',
			'phrase/span'
		);

		// ~alternative span~
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\~)\~(?!\s)((?:[^\r ~]++|[ ])+)'.Patterns::MODIFIER.'?(?<!\s)\~(?!\~)(?::('.Patterns::LINK_URL.'))??()#Uu',
			'phrase/span-alt'
		);

		// ~~cite~~
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\~)\~\~(?![\s~])((?:[^\r\n ~]++|[ ~])+)'.Patterns::MODIFIER.'?(?<![\s~])\~\~(?!\~)(?::('.Patterns::LINK_URL.'))??()#Uu',
			'phrase/cite'
		);

		// >>quote<<
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\>)\>\>(?![\s>])((?:[^\r\n <]++|[ <])+)'.Patterns::MODIFIER.'?(?<![\s<])\<\<(?!\<)(?::('.Patterns::LINK_URL.'))??()#Uu',
			'phrase/quote'
		);

		// acronym/abbr "et al."((and others))
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\")\"(?!\s)((?:[^\r\n "]++|[ ])+)'.Patterns::MODIFIER.'?(?<!\s)\"(?!\")\(\((.+)\)\)()#Uu',
			'phrase/acronym'
		);

		// acronym/abbr NATO((North Atlantic Treaty Organisation))
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!['.Patterns::CHAR.'])(['.Patterns::CHAR.']{2,})()\(\(((?:[^\n )]++|[ )])+)\)\)#Uu',
			'phrase/acronym-alt'
		);

		// ''notexy''
		$texy->registerLinePattern(
			[$this, 'patternNoTexy'],
			'#(?<!\')\'\'(?![\s\'])((?:[^'.Patterns::MARK.'\r\n\']++|[\'])+)(?<![\s\'])\'\'(?!\')()#Uu',
			'phrase/notexy'
		);

		// `code`
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#\`(\S(?:[^'.Patterns::MARK.'\r\n `]++|[ `])*)'.Patterns::MODIFIER.'?(?<!\s)\`(?::('.Patterns::LINK_URL.'))??()#Uu',
			'phrase/code'
		);

		// ....:LINK
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(['.Patterns::CHAR.'0-9@\#$%&.,_-]++)()(?=:\[)(?::('.Patterns::LINK_URL.'))()#Uu',
			'phrase/quicklink'
		);

		// [text |link]
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<!\[)\[(?![\s*])([^|\r\n\]]++)\|((?:[^'.Patterns::MARK.'|\r\n \]]++|[ ])+)'.Patterns::MODIFIER.'?(?<!\s)\](?!\])()#Uu',
			'phrase/wikilink'
		);

		// [text](link)
		$texy->registerLinePattern(
			[$this, 'patternPhrase'],
			'#(?<![[.])\[(?![\s*])((?:[^|\r\n \]]++|[ ])+)'.Patterns::MODIFIER.'?(?<!\s)\]\(((?:[^'.Patterns::MARK.'\r )]++|[ ])+)\)()#Uu',
			'phrase/markdown'
		);


		$texy->allowed['phrase/ins'] = FALSE;
		$texy->allowed['phrase/del'] = FALSE;
		$texy->allowed['phrase/sup'] = FALSE;
		$texy->allowed['phrase/sub'] = FALSE;
		$texy->allowed['phrase/cite'] = FALSE;
	}


	/**
	 * Callback for: **.... .(title)[class]{style}**:LINK.
	 *
	 * @param  Texy\LineParser
	 * @param  array      regexp matches
	 * @param  string     pattern name
	 * @return Texy\HtmlElement|string|FALSE
	 */
	public function patternPhrase($parser, $matches, $phrase)
	{
		list(, $mContent, $mMod, $mLink) = $matches;
		// [1] => **
		// [2] => ...
		// [3] => .(title)[class]{style}
		// [4] => LINK

		if ($phrase === 'phrase/wikilink') {
			list($mLink, $mMod) = [$mMod, $mLink];
			$mContent = trim($mContent);
		}

		$tx = $this->texy;
		$mod = new Texy\Modifier($mMod);
		$link = NULL;

		$parser->again = $phrase !== 'phrase/code' && $phrase !== 'phrase/quicklink';

		if ($phrase === 'phrase/span' || $phrase === 'phrase/span-alt') {
			if ($mLink == NULL) {
				if (!$mMod) {
					return FALSE; // means "..."
				}
			} else {
				$link = $tx->linkModule->factoryLink($mLink, $mMod, $mContent);
			}

		} elseif ($phrase === 'phrase/acronym' || $phrase === 'phrase/acronym-alt') {
			$mod->title = trim(html_entity_decode($mLink, ENT_QUOTES, 'UTF-8'));

		} elseif ($phrase === 'phrase/quote') {
			$mod->cite = $tx->blockQuoteModule->citeLink($mLink);

		} elseif ($mLink != NULL) {
			$link = $tx->linkModule->factoryLink($mLink, NULL, $mContent);
		}

		return $tx->invokeAroundHandlers('phrase', $parser, [$phrase, $mContent, $mod, $link]);
	}


	/**
	 * Callback for: any^2 any_2.
	 *
	 * @param  Texy\LineParser
	 * @param  array      regexp matches
	 * @param  string     pattern name
	 * @return Texy\HtmlElement|string|FALSE
	 */
	public function patternSupSub($parser, $matches, $phrase)
	{
		list(, $mContent) = $matches;
		$mod = new Texy\Modifier();
		$link = NULL;
		$mContent = str_replace('-', "\xE2\x88\x92", $mContent); // &minus;
		return $this->texy->invokeAroundHandlers('phrase', $parser, [$phrase, $mContent, $mod, $link]);
	}


	/**
	 * @param  Texy\LineParser
	 * @param  array      regexp matches
	 * @param  string     pattern name
	 * @return string
	 */
	public function patternNoTexy($parser, $matches)
	{
		list(, $mContent) = $matches;
		return $this->texy->protect(htmlspecialchars($mContent, ENT_NOQUOTES, 'UTF-8'), Texy\Texy::CONTENT_TEXTUAL);
	}


	/**
	 * Finish invocation.
	 *
	 * @param  Texy\HandlerInvocation  handler invocation
	 * @param  string
	 * @param  string
	 * @param  Texy\Modifier
	 * @param  Link
	 * @return Texy\HtmlElement
	 */
	public function solve($invocation, $phrase, $content, $mod, $link)
	{
		$tx = $this->texy;

		$tag = isset($this->tags[$phrase]) ? $this->tags[$phrase] : NULL;

		if ($tag === 'a') {
			$tag = $link && $this->linksAllowed ? NULL : 'span';
		}

		if ($phrase === 'phrase/code') {
			$content = $tx->protect(htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8'), Texy\Texy::CONTENT_TEXTUAL);
		}

		if ($phrase === 'phrase/strong+em') {
			$el = Texy\HtmlElement::el($this->tags['phrase/strong']);
			$el->create($this->tags['phrase/em'], $content);
			$mod->decorate($tx, $el);

		} elseif ($tag) {
			$el = Texy\HtmlElement::el($tag)->setText($content);
			$mod->decorate($tx, $el);

			if ($tag === 'q') {
				$el->attrs['cite'] = $mod->cite;
			}
		} else {
			$el = $content; // trick
		}

		if ($link && $this->linksAllowed) {
			return $tx->linkModule->solve(NULL, $link, $el);
		}

		return $el;
	}

}