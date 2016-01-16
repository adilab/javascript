<?php

/**
 *
 *
 * AdiPHP : Rapid Development Tools (http://adilab.net)
 * Copyright (c) Adrian Zurkiewicz
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @version     0.1
 * @copyright   Adrian Zurkiewicz
 * @link        http://adilab.net
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Adi\JavaScript;

/**
 * Facade of JQuery sequence
 * 
 * @since AdiPHP v 0.0.1
 * 
 */

class JQuery extends JavaScript implements JavaScriptExtensionInterface {

	/**
	 *
	 * @var string
	 */
	private $selector;

	/**
	 *
	 * @var array
	 */
	private $buffer = array();

	/**
	 * Constructor
	 *
	 * <code>
	 * $js = new JQuery('#id');
	 * echo $js->attr('value', 'Hello world')->css('color', '#ff0000')->focus()->select();
	 * </code>
	 *
	 * @param string $selector JQuery selector. For example: '#id', 'input'...
	 *
	 */
	function __construct($selector) {
		
		parent::__construct();

		$this->selector = $selector;
	}

	/**
	 * Use magic function to cast php call to JQuery call
	 *
	 * @param string $name Method name
	 * @param array $arguments Method arguments
	 * @return self
	 * 
	 */
	public function __call($name, $arguments) {

		$arg = NULL;


		if (is_array($arguments)) {

			foreach ($arguments as $value) {

				Data::check($value);

				if ($arg) {
					$arg .= ',';
				}

				$arg .= $value->getData();
			}
		}


		$this->buffer[] = "{$name}({$arg})";

		return $this;
	}

	/**
	 * Returns JQuery code
	 * 
	 * @return string
	 */
	public function getScript() {

		$result = NULL;

		foreach ($this->buffer as $call) {

			if ($result) {
				$result .= '.';
			}

			$result .= $call;
		}

		if (!$result) {
			return NULL;
		}
		
		Data::check($this->selector);

		return "$({$this->selector}).{$result};";
	}

}
