<?php
namespace model\database\views;

use \model\database\tables\User;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class UserExtended extends User{
	
	var $role_label;
	
	var $ratings;
}
