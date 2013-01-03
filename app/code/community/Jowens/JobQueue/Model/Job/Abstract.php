<?php

abstract class Jowens_JobQueue_Model_Job_Abstract extends Mage_Core_Model_Abstract
{
	private $name;
	private $storeId;

	public function __construct() {
		$this->setStoreId(Mage::app()->getStore()->getStoreId());
	}

	public abstract function perform();

	public function setName($name) 
	{
		$this->name = $name;
	}

	public function getName() 
	{
		return $this->getType() . ": " . $this->name;
	}

	public function setStoreId($storeId) 
	{
		$this->storeId = $storeId;
	}

	public function getStoreId() 
	{
		return $this->storeId;
	}	

	public function getType() 
	{
		$tokens = explode("_", get_class($this));
		return array_pop($tokens);
	}
}