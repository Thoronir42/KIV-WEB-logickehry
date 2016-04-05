<?php

namespace model\database;

/**
 *
 * @author Stepan
 */
interface IRenderableWeekEntity {
	
	public function getID();
	
	public function getType();
	public function getLabel();
	
	public function getTitle();
	public function hasSubtitle();
	public function getSubtitle();
	
	public function getDate();
	public function getTimeFrom();
	public function getTimeTo();
	
	public function getTimeLength();
	
	public function hasGameAssigned();
	public function getGameTypeID();
	
	public function isEvent();
}
