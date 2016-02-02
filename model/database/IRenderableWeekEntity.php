<?php

namespace model\database;

/**
 *
 * @author Stepan
 */
interface IRenderableWeekEntity {
	
	public function getType();
	
	public function getTitle();
	public function getSubtitle();
	
	public function getDate();
	public function getTimeFrom();
	public function getTimeTo();
	
	public function getTimeLength();
	
	public function hasGameAssigned();
	public function getGameTypeID();
	
	public function isEvent();
}
