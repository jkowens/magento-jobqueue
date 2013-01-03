<?php

class Jowens_JobQueue_Model_Job_Order extends Jowens_JobQueue_Model_Job_Abstract {
	public function perform() {
		Mage::log("Hello Jordan" . rand());
		throw new Exception ("Crapped a big one!");
	}
}