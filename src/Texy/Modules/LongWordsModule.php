<?php

/**
 * This file is part of the Texy! (https://texy.info)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Texy\Modules;

use Texy;


/**
 * Long words wrap module.
 */
final class LongWordsModule extends Texy\Module
{
	public const
		DONT = 0, // don't hyphenate
		HERE = 1, // hyphenate here
		AFTER = 2; // hyphenate after

	public const SAFE_LIMIT = 1000;

	public $wordLimit = 20;

	private $consonants = [
		'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'z',
		'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z',
		"\u{10D}", "\u{10F}", "\u{148}", "\u{159}", "\u{161}", "\u{165}", "\u{17E}", // Czech UTF-8
		"\u{10C}", "\u{10E}", "\u{147}", "\u{158}", "\u{160}", "\u{164}", "\u{17D}",
	];

	private $vowels = [
		'a', 'e', 'i', 'o', 'u', 'y',
		'A', 'E', 'I', 'O', 'U', 'Y',
		"\u{E1}", "\u{E9}", "\u{11B}", "\u{ED}", "\u{F3}", "\u{FA}", "\u{16F}", "\u{FD}", // Czech UTF-8
		"\u{C1}", "\u{C9}", "\u{11A}", "\u{CD}", "\u{D3}", "\u{DA}", "\u{16E}", "\u{DD}",
	];

	private $before_r = [
		'b', 'B', 'c', 'C', 'd', 'D', 'f', 'F', 'g', 'G', 'k', 'K', 'p', 'P', 'r', 'R', 't', 'T', 'v', 'V',
		"\u{10D}", "\u{10C}", "\u{10F}", "\u{10E}", "\u{159}", "\u{158}", "\u{165}", "\u{164}", // Czech UTF-8
	];

	private $before_l = [
		'b', 'B', 'c', 'C', 'd', 'D', 'f', 'F', 'g', 'G', 'k', 'K', 'l', 'L', 'p', 'P', 't', 'T', 'v', 'V',
		"\u{10D}", "\u{10C}", "\u{10F}", "\u{10E}", "\u{165}", "\u{164}", // Czech UTF-8
	];

	private $before_h = ['c', 'C', 's', 'S'];

	private $doubleVowels = ['a', 'A', 'o', 'O'];


	public function __construct(Texy\Texy $texy)
	{
		$this->texy = $texy;

		$this->consonants = array_flip($this->consonants);
		$this->vowels = array_flip($this->vowels);
		$this->before_r = array_flip($this->before_r);
		$this->before_l = array_flip($this->before_l);
		$this->before_h = array_flip($this->before_h);
		$this->doubleVowels = array_flip($this->doubleVowels);

		$texy->registerPostLine([$this, 'postLine'], 'longwords');
	}


	public function postLine(string $text): string
	{
		return Texy\Regexp::replace(
			$text,
			'#[^\ \n\t\x14\x15\x16\x{2013}\x{2014}\x{ad}-]{' . $this->wordLimit . ',}#u',
			[$this, 'pattern']
		);
	}


	/**
	 * Callback for long words.
	 * @internal
	 */
	public function pattern(array $matches): string
	{
		[$mWord] = $matches;
		// [0] => lllloooonnnnggggwwwoorrdddd

		if (iconv_strlen($mWord, 'UTF-8') > self::SAFE_LIMIT) {
			return $mWord;
		}

		$chars = [];
		preg_match_all(
			'#[' . Texy\Patterns::MARK . ']+|.#u',
			$mWord,
			$chars
		);

		$chars = $chars[0];
		if (count($chars) < $this->wordLimit) {
			return $mWord;
		}

		$consonants = $this->consonants;
		$vowels = $this->vowels;
		$before_r = $this->before_r;
		$before_l = $this->before_l;
		$before_h = $this->before_h;
		$doubleVowels = $this->doubleVowels;

		$s = [];
		$trans = [];

		$s[] = '';
		$trans[] = -1;
		foreach ($chars as $key => $char) {
			if (ord($char[0]) < 32) {
				continue;
			}
			$s[] = $char;
			$trans[] = $key;
		}
		$s[] = '';
		$len = count($s) - 2;

		$positions = [];
		$a = 0;
		$last = 1;

		while (++$a < $len) {
			$hyphen = self::DONT; // Do not hyphenate
			do {
				if ($s[$a] === "\u{A0}") {
					$a++;
					continue 2;  // here and after never
				}

				if ($s[$a] === '.') {
					$hyphen = self::HERE;
					break;
				}

				if (isset($consonants[$s[$a]])) { // consonants

					if (isset($vowels[$s[$a + 1]])) {
						if (isset($vowels[$s[$a - 1]])) {
							$hyphen = self::HERE;
						}
						break;
					}

					if (($s[$a] === 's') && ($s[$a - 1] === 'n') && isset($consonants[$s[$a + 1]])) {
						$hyphen = self::AFTER;
						break;
					}

					if (isset($consonants[$s[$a + 1]]) && isset($vowels[$s[$a - 1]])) {
						if ($s[$a + 1] === 'r') {
							$hyphen = isset($before_r[$s[$a]]) ? self::HERE : self::AFTER;
							break;
						}

						if ($s[$a + 1] === 'l') {
							$hyphen = isset($before_l[$s[$a]]) ? self::HERE : self::AFTER;
							break;
						}

						if ($s[$a + 1] === 'h') { // CH
							$hyphen = isset($before_h[$s[$a]]) ? self::DONT : self::AFTER;
							break;
						}

						$hyphen = self::AFTER;
						break;
					}

					break;
				} // end of consonants

				if (($s[$a] === 'u') && isset($doubleVowels[$s[$a - 1]])) {
					$hyphen = self::AFTER;
					break;
				}
				if (isset($vowels[$s[$a]]) && isset($vowels[$s[$a - 1]])) {
					$hyphen = self::HERE;
					break;
				}
			} while (0);

			if ($hyphen === self::DONT && ($a - $last > $this->wordLimit * 0.6)) {
				$positions[] = $last = $a - 1; // Hyphenate here
			}
			if ($hyphen === self::HERE) {
				$positions[] = $last = $a - 1; // Hyphenate here
			}
			if ($hyphen === self::AFTER) {
				$positions[] = $last = $a;
				$a++; // Hyphenate after
			}
		} // while


		$a = end($positions);
		if (($a === $len - 1) && isset($consonants[$s[$len]])) {
			array_pop($positions);
		}

		$syllables = [];
		$last = 0;
		foreach ($positions as $pos) {
			if ($pos - $last > $this->wordLimit * 0.6) {
				$syllables[] = implode('', array_splice($chars, 0, $trans[$pos] - $trans[$last]));
				$last = $pos;
			}
		}
		$syllables[] = implode('', $chars);
		return implode("\u{AD}", $syllables);
	}
}
