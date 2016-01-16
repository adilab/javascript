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

use Adi\JavaScript\Exception\InvalidFunctionNameException;


/**
 * Facade of JavaScript sequence
 * 
 * @since AdiPHP v 0.0.1
 *  
 * @method JQuery JQuery(string $selector JQuery selector such as '#id' or 'input') Returns instance of JQuery
 * @staticmethod JQuery JQuery(string $selector JQuery selector such as '#id' or 'input') Returns instance of JQuery
 * @staticmethod JQuery Sender() Returns instance of JQuery for event sender element (only for functions)
 */

class JavaScript {

	/**
	 *
	 * @var integer
	 */
	static private $id;

	/**
	 * Call a JavaScripy function
	 *
	 * @return self
	 */
	static public function __callStatic($method, $args) {

		if ($method == 'JQuery') {
			
			return new JQuery(@$args[0]);
		
			
		} else if ($method == 'Sender') {
			
			return new JQuery(Data::code('sender'));
			
		}
		
		$js = new self();
		call_user_func_array(array($js, $method), $args);

		return $js;
	}

	/**
	 *
	 * @var array() 
	 */
	private $code = array();

	/**
	 *
	 * @var string
	 */
	private $functionName = NULL;

	/**
	 *
	 * @var array() 
	 */
	private $functionArguments = array();

	/**
	 *
	 * @var string 
	 */
	private $confirmText = NULL;

	/**
	 * 
	 * @var boolean 
	 */
	private $onDocumentReady = false;

	/**
	 *
	 * @var array() 
	 */
	private $triggers = array();
	
	
	/**
	 *
	 * @var integer
	 */
	private $setTimeout = 0;

	/**
	 * Constructor
	 * 
	 * <code>
	 * $js = new JavaScript();
	 * $js->alert('Hello world');
	 * $js->JQuery('#id')->html('ok')->css('color', '#00ff00');
	 * echo $js;	
	 * </code>
	 *
	 */
	function __construct() {

		if ($this instanceof JavaScriptExtensionInterface) {

			$this->addCode($this);
		}


		if (!self::$id) {
			self::$id = rand(10000, 99999);
		}

		self::$id++;
	}
	
	
	
	/**
	 * Return url to use as href
	 * 
	 * Warning! This method can be used only with simple JavaScript constructions. Complex constructions may need to create a function and use getUrl method.
	 * 
	 * <code>
	 * $url = JavaScript::edit($module, $key)->asUrl();
	 * </code>
	 * 
	 * @return string
	 */
	public function asUrl() {
		
		$result = $this->render(FALSE);
		
		return "javascript:{$result}";
		
	}
	
	

	/**
	 * 
	 * Returns url to call the script
	 * 
	 * You need to have name of function, so first you must call makeFunction() method
	 * 
	 * @param boolean $readyToUse Make the script ready to use calling methods makeFunction() and dump()
	 * @return string
	 * @throws InvalidFunctionNameException
	 */
	public function getUrl($readyToUse = false) {
		
		if ($readyToUse) {

			if (!$this->functionName) {

				$this->makeFunction();
			}

			$this->dump();
		}


		if (!$this->functionName) {
			throw new InvalidFunctionNameException('The script has not a function name. Call makeFunction() method first.');
		}

		return "javascript:{$this->functionName}(this);";
	}

	/**
	 * 
	 * Returns href attribute to call the script
	 * 
	 * You need to have name of function, so first you must call makeFunction() method
	 * 
	 * @return string
	 * @throws InvalidFunctionNameException
	 */
	public function getHref() {

		if (!$this->functionName) {
			throw new InvalidFunctionNameException('The script has not a function name. Call makeFunction() method first.');
		}

		return "href='javascript:{$this->functionName}(this);'";
	}

	/**
	 * 
	 * Returns html event attribute to call the script
	 * 
	 * You need to have name of function, so first you must call makeFunction() method
	 * 
	 * <code>
	 * echo "<a {$js->getEvent('ondblclick')}>double-clicked</a>";
	 * </code>
	 * 
	 * @param string $event Html event name
	 * @return string
	 * @throws InvalidFunctionNameException
	 */
	public function getEvent($event) {

		if (!$this->functionName) {
			throw new InvalidFunctionNameException('The script has not a function name. Call makeFunction() method first.');
		}

		return "{$event}='{$this->functionName}(this);'";
	}

	
	/**
	 * 
	 * Returns code of call in script
	 * 
	 * You need to have name of function, so first you must call makeFunction() method
	 * 
	 * @return string
	 * @throws InvalidFunctionNameException
	 */
	public function getCaller() {

		if (!$this->functionName) {
			throw new InvalidFunctionNameException('The script has not a function name. Call makeFunction() method first.');
		}

		return "{$this->functionName}(this)";
	}	
	
	
	/**
	 * Execute this script when the document is ready
	 * 
	 * @param boolean $value
	 * @return self
	 */
	public function makeOnDocumentReady($value = true) {

		$this->onDocumentReady = $value;

		return $this;
	}

	/**
	 * Create a confirmation for this script
	 * 
	 * @param string $text
	 * @return self
	 */
	public function makeConfirm($text) {

		$this->confirmText = trim($text);

		return $this;
	}
	
	
	/**
	 * Sets time out for JavaScript code
	 * 
	 * @param integer $milliseconds
	 * @return self
	 */
	
	public function makeTimeout($milliseconds) {
		
		$this->setTimeout = $milliseconds;
		
		return $this;
		
	}
		

	/**
	 * Makes this script a function
	 * 
	 * @param string $functionName Name of function or NULL in order to auto create function name
	 * @param array $functionArguments List of names of function arguments
	 * @return self
	 */
	public function makeFunction($functionName = NULL, $functionArguments = NULL) {


		if (!$functionName) {

			self::$id++;

			$functionName = "ajs" . self::$id . rand();
		}



		if ($functionArguments) {

			if (!is_array($functionArguments)) {
				$functionArguments = array($functionArguments);
			}
		}

		$this->functionName = $functionName;
		$this->functionArguments = $functionArguments;

		return $this;
	}

	/**
	 * Call a JavaScripy function
	 * 
	 * @param string $method
	 * @param array $args
	 * @return self
	 */
	public function __call($method, $args) {


		if ($method == 'JQuery') {
			
			$jq = new JQuery(@$args[0]);
			
			$this->addCode($jq);
			
			return $jq;
		}


		$code = NULL;

		foreach ($args as $arg) {

			if ($code) {
				$code .= ",";
			}

			Data::check($arg);

			$code .= $arg->getData();
		}

		$code = "{$method}({$code});";

		$this->code[] = $code;
		
		return $this;
	}

	/**
	 * Renders JavaScript code
	 * 
	 * @return string
	 */
	public function __toString() {

		return $this->render();
	}



	/**
	 * 
	 * Adds a JQuery trigger.
	 *
	 * It is possible to add many triggers.
	 *
	 * <code>
	 * $js->addTrigger('#id', 'click');
	 * $js->addTrigger('#id2', 'keypress', 65); // Gdy "A" jest wciśnięty
	 * </code>
	 * 
	 * @param string $selector
	 * @param string $event
	 * @param string $which
	 * @return self
	 */
	public function addTrigger($selector, $event, $which = NULL) {

		$this->triggers[] = array(
			'selector' => $selector,
			'event' => $event,
			'which' => $which,
		);
		
		return $this;
	}

	/**
	 * Adds code of script
	 *
	 * @param string $code
	 * @return self
	 */
	public function addCode($code) {

		$this->code[] = $code;
		
		return $this;
	}

	
	/**
	 * Dump the script by PHP echo
	 * 
	 * @return self
	 */
	public function dump() {

		echo $this->render();

		return $this;
	}	
	
	
	
	/**
	 * Renders JavaScript code
	 * 
	 * @param boolean $isTag Determines whether to return the code in tag. Default: true
	 * @return string
	 */
	public function render($isTag = true) {

		$result = NULL;


		if (count($this->triggers)) {

			if (!$this->functionName) {

				$this->makeFunction();
			}
		}

		/*
		 * Prepares line of code
		 */

		foreach ($this->code as $line) {

			if ($line instanceof self) {

				if ($line === $this) {

					if ($line instanceof JavaScriptExtensionInterface) {

						$line = $line->getScript();
					} else {

						$line = get_class($line);

						$line = "/* Class '{$line}' not implements 'Adi\JavaScript\JavaScriptExtensionInterface' interface and cannot be rendered.*/\n";
					}
				} else {

					$line = $line->render(false);
				}
			}


			$result .= $line . "\n";
		}

		if ($this->confirmText) { // Creates confirm cover

			$this->coverConfirm($result);
		}
	
		if ($this->setTimeout) { // Creates setTimeout cover
			
			$this->coverTimeout($result);
		}		

		if ($this->onDocumentReady) { // Creates documen ready cover

			$this->coverDocumentReady($result);
		}
		
		if ($this->functionName) { // Creates function cover

			$this->coverFunction($result);
		}

		if (count($this->triggers)) { // Creates triggers

			$result .= $this->createTriggers();
		}

		$this->normalize($result);

		if ($isTag) {
			$result = "\n<script>\n{$result}\n</script>\n";
		}


		

		return $result;
	}

	
	
	/**
	 * Normalization of the code
	 * 
	 * @param type $code
	 */
	private function normalize(& $code) {

		$code = str_replace(array("\n\n", "\n\n\n"), "\n", $code);


		for ($i = strlen($code); $i >= 0; $i--) {

			if (substr($code, $i, 1) == ';') {

				$code = substr_replace($code, '', $i, 1);

				break;
			}
		}
		
		
		for ($i = strlen($code); $i >= 0; $i--) {

			if (substr($code, $i, 1) == "\n") {

				$code = substr_replace($code, '', $i, 1);

				break;
			}
		}		
		
	}




	/**
	 * Creates scripts triggers
	 * 
	 * @return string
	 */
	protected function createTriggers() {

		$triggers = NULL;

		

		foreach ($this->triggers as $trigger) {

			$script = "{$this->functionName}();";
			$selector = Data::string($trigger['selector'])->getData();
			$event = Data::string($trigger['event'])->getData();
			$which = @$trigger['which'];

			if ($which !== NULL) {

				$which = Data::mixed($which)->getData();

				$script = "if (e.which == {$which}) { {$script} }";
			}


			$triggers .= "\n$({$selector}).bind({$event}, function(e) { {$script} } );\n";
		}


		if ($triggers) {

			return "$(document).ready(function() {\n{$triggers}});";
		}
	}

	/**
	 * Creates documen ready cover
	 * 
	 * @param string $code
	 */
	protected function coverDocumentReady(& $code) {

		$code = "$(document).ready(function() {\n{$code}});";
	}
	
	/**
	 * Creates setTimeout cover
	 * 
	 * @param string $code
	 */
	protected function coverTimeout(& $code) {

		$code = "setTimeout(function(){{$code}\n}, {$this->setTimeout});\n";
	}	

	/**
	 * Create confirm cover
	 * 
	 * @param string $code
	 */
	protected function coverConfirm(& $code) {

		$text = $this->confirmText;

		Data::check($text);

		$text = $text->getData();

		$code = "if (confirm({$text})) {\n{$code}}" . "\n";
	}

	/**
	 * Create function cover
	 * 
	 * @param string $code
	 */
	protected function coverFunction(& $code) {

		$args = is_array(@$this->functionArguments) ? implode(',', $this->functionArguments) : 'sender';
		
		$code = "\nfunction {$this->functionName}({$args}) {\n{$code}}\n";
	}

}
