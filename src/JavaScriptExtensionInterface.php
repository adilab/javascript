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
 * Extension of JavaScript facade
 * 
 * @since AdiPHP v 0.0.1
 * 
 */

interface JavaScriptExtensionInterface {
	
	/**
	 * Returns JavaScript code
	 * 
	 * @return string
	 */	
	
	public function getScript(); 
	
	
}

