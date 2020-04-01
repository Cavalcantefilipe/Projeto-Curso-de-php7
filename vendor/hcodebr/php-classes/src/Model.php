<?php 

namespace Hcode;

class Model {

	private $values = [];

	public function setData($data)
	{

		foreach ($data as $key => $value)
		{
			$this->{"set".$key}($value);
		}

	}

	public function __call($name, $args)
	{

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));

		if (in_array($fieldName, $this->fields))
		{
			if($method == "get"){
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
			}else{
					$this->values[$fieldName] = $args[0];
			}
		}

	}

	public function getValues()
	{
		return $this->values;
	}

}

 ?>
