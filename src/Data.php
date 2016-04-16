<?php

/**
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
 * JavaScript data container
 * 
 * @since AdiPHP v 0.0.1
 * 
 */

class Data {

	const TYPE_NULL = 1;
	const TYPE_STRING = 2;
	const TYPE_NUMERIC = 3;
	const TYPE_BOOLEAN = 4;
	const TYPE_ASSOCIATIVE_ARRAY = 100;
	const TYPE_INDEX_ARRAY = 101;
	const TYPE_OBJECT = 200;
	const TYPE_JAVASCRIPT_CODE = 1000;

	/**
	 *
	 * @var integer
	 */
	private $type;

	/**
	 *
	 * @var string
	 */
	private $data;

	/**
	 * Constructor
	 *
	 * @param mixed $data Simple type variable
	 * @param string $type self::TYPE_INDEX_ARRAY, self::TYPE_JAVASCRIPT_CODE, or NULL in order to auto detect
	 * 
	 */
	protected function __construct($data, $type = NULL) {

		$this->data = $this->init($data, $type);
		$this->type = $type;
	}

	/**
	 * Creates container for variable and auto detect type.
	 *
	 * <code>
	 * $d = Data::mixed('Hello world');
	 * echo $d->getType();
	 * echo $d->getData();
	 * </code>
	 *
	 * <code>
	 * $d = Data::mixed(array('aaa', 'bbb', 'ccc'));
	 * echo $d->getType();
	 * echo $d->getData();	
	 * </code> 
	 *
	 * @param mixed $value Simple type variable
	 * @return self
	 *
	 */
	static public function mixed($value) {

		return new self($value);
	}

	/**
	 * Creates container for a string
	 *
	 * <code>
	 * $d = Data::string('Hello world');
	 * echo $d->getType();
	 * echo $d->getData();	
	 * </code>
	 *
	 * @param string $value String
	 * @return self
	 *
	 */
	static public function string($value) {

		return new self($value, self::TYPE_STRING);
	}

	/**
	 * Creates container for index array
	 *
	 * <code>
	 * $d = Data::indexArray(array('a', 'b', 'c'));
	 * echo $d->getType();
	 * echo $d->getData();	
	 * </code>
	 *
	 * @param mixed $value mixed or array
	 * @return self
	 *
	 */
	static public function indexArray($value) {

		if (!is_array($value)) {
			$value = array($value);
		}

		return new self($value, self::TYPE_INDEX_ARRAY);
	}

	/**
	 * Creates container for sequence of JavaScript code
	 *
	 * <code>
	 * $d = Data::code("alert('Hello world')");
	 * echo $d->getType();
	 * echo $d->getData();	
	 * </code>
	 *
	 * @param string $value JavaScript code as string
	 * @return self
	 *
	 */
	static public function code($value) {

		return new self($value, self::TYPE_JAVASCRIPT_CODE);
	}

	/**
	 * Returns information of data type
	 *
	 * @return integer
	 *
	 */
	public function getType() {

		return $this->type;
	}

	/**
	 * Returns information of data type
	 *
	 * @return string
	 *
	 */
	public function getTypeDescription() {

		if ($this->type == self::TYPE_NULL) {
			return 'null';
		}
		if ($this->type == self::TYPE_STRING) {
			return 'string';
		}
		if ($this->type == self::TYPE_NUMERIC) {
			return 'numeric';
		}
		if ($this->type == self::TYPE_BOOLEAN) {
			return 'boolean';
		}
		if ($this->type == self::TYPE_ASSOCIATIVE_ARRAY) {
			return 'array';
		}
		if ($this->type == self::TYPE_INDEX_ARRAY) {
			return 'array';
		}
		if ($this->type == self::TYPE_OBJECT) {
			return 'object';
		}
		if ($this->type == self::TYPE_JAVASCRIPT_CODE) {
			return 'code';
		}

		return 'unknown';
	}

	/**
	 * Returns data ready to be inserted as JavaScript code
	 *
	 * @return string
	 *
	 */
	public function getData() {

		return $this->data;
	}

	/**
	 * Ensures that $var is a reference to instance of Data, and if not then its cast.
	 *
	 * <code>
	 * Data::check($value);
	 * </code> 
	 *
	 * @param mixed &$var Simple type variable, or reference to instance of Data
	 * 
	 */
	static public function check(&$var) {

		if (!$var instanceof self) {

			$var = self::mixed($var);
		}
	}

	/**
	 * Output getData()
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getData();
	}

	/**
	 * 
	 * Initialization of container values
	 * 
	 * @param type $value Simple type variable
	 * @param type $info Information of value type
	 * @return string
	 */
	private function init($value, &$info = NULL) {

		if ($info == self::TYPE_STRING) {

			return $this->dataString($value, $info);
		}

		if ($info == self::TYPE_JAVASCRIPT_CODE) {

			return $value;
		}


		return $this->autoDetect($value, $info);
	}

	/**
	 * Try to detect data type, and prepare data and data information
	 * 
	 * @param self $value
	 * @param type $info Information of data type
	 * @return string|\self
	 */
	private function autoDetect($value, & $info = NULL) {

		$type = gettype($value);


		if (func_num_args() == 1) {
			$info = NULL;
		}





		if ($info == self::TYPE_JAVASCRIPT_CODE) {

			return $value;
		} else if ($info == self::TYPE_INDEX_ARRAY) {
			$elements = NULL;

			foreach ($value as $v) {

				if ($elements) {
					$elements .= ', ';
				}

				$elements .= $this->autoDetect($v);
			}


			return "Array({$elements})";
		} else if (!isset($value)) {

			$info = self::TYPE_NULL;
			return json_encode(NULL);
		} else if ($type == 'NULL') {

			$info = self::TYPE_NULL;
			return json_encode(NULL);
		} else if ($type == 'array') {
			$elements = NULL;


			foreach ($value as $k => $v) {

				if ($elements) {
					$elements .= ', ';
				}

				$v = $this->autoDetect($v);
				$k = $this->autoDetect($k);


				$elements .= "{$k}: {$v}";
			}


			$info = self::TYPE_ASSOCIATIVE_ARRAY;
			return "{{$elements}}";
		} else if ($type == 'boolean') {  //  boolean
			if ($value) {

				$info = self::TYPE_BOOLEAN;
				return json_encode(true);
			} else {

				$info = self::TYPE_BOOLEAN;
				return json_encode(false);
			}
		} else if (($type == 'integer') or ( $type == 'double')) { // integer or double
			$info = self::TYPE_NUMERIC;
			return $value;
		} else if ($type == 'object') {  //  object
			if ($value instanceof self) {

				return $value;
				
			} else if ($value instanceof JavaScript) {
				
				$info = self::TYPE_JAVASCRIPT_CODE;
				
				return $value->render(false);

			} else {

				$info = self::TYPE_OBJECT;

				return $this->dataString(get_class($value));
			}
		}




		return $this->dataString((string) $value, $info);
	}

	/**
	 * Prepares string values
	 * 
	 * @param string $value
	 * @param string $info Data information
	 * @return string
	 */
	private function dataString($value, & $info = NULL) {

		if (func_num_args() > 1) {
			$info = self::TYPE_STRING;
		}

		return json_encode($value);
	}

}

?>
