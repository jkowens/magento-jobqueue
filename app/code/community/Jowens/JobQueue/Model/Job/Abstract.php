<?php

abstract class Jowens_JobQueue_Model_Job_Abstract extends Mage_Core_Model_Abstract
{
  private $name;
  private $storeId;

  public function __construct($name=null) {
    $this->name = $name ? $name : $this->getType();
    $this->setStoreId(Mage::app()->getStore()->getStoreId());
  }

  public abstract function perform();

  public function performImmediate($retryQueue="default") {
    try {
      $this->perform();
    } catch(Exception $e) {
      $this->enqueue($retryQueue);
      Mage::log($e);
    }
  }

  public function enqueue($queue="default", $run_at=null) {
    $job = Mage::getModel('jobqueue/job');
    $job->setStoreId($this->getStoreId());
    $job->setName($this->getName());
    $job->setHandler(serialize($this));
    $job->setQueue($queue);
    $job->setRunAt($run_at);
    $job->setCreatedAt(now());
    $job->save();
  }

  public function setName($name) 
  {
    $this->name = $name;
    return $this;
  }

  public function getName() 
  {
    return $this->name;
  }

  public function setStoreId($storeId) 
  {
    $this->storeId = $storeId;
    return $this;
  }

  public function getStoreId() 
  {
    return $this->storeId;
  }	

  public function getType() 
  {
    return get_class($this);
  }
}
