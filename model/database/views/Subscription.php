<?php
namespace model\database\views;


/**
 * Description of Subscription
 *
 * @author Stepan
 */
class Subscription{
	
#	Mirror of model\database\tables\User
	var $user_id;
	
	var $orion_login;
	
	var $name;
	
	var $surname;
	
	var $role_id;
	
	
# Mirror of model\database\tables\User
	var $game_type_id = false;
	
	var $game_name;
	
	var $subtitle = false;
	
	var $avg_playtime;
	
	var $min_players;
	
	var $max_players;
	
}