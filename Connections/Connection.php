<?php
/**
 * Represents a connection between two users.  Connections are one-way, even though whenever a connection is added the inverse connection is as well (to enforce symmetry).
 * This decision was made because the overhead of 2N records was seen as unimportant for the current state of the app. 
 * @author Andy
 *
 */
class Connection extends AppModel {
	var $useTable = 'users_connections';
			
	public $belongsTo = array(
			'ToUser' => array(
					'className' => 'User',
					'foreignKey' => 'connected_user_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'FromUser' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			)			
	);
	
	/**
	 * Returns true if the two user id's passed are for connected users.
	 * Every user is trivially connected to herself.
	 * @param unknown_type $id
	 */
	function connected($user1Id, $user2Id) {
		return $this->hasAny(array('user_id' => $user1Id, 'connected_user_id' => $user2Id)) || $user1Id == $user2Id;		
	}
	
	/**
	 * Adds two records indicating symmetrical connection between the two users.  Has no effect if the users are already connected.
	 * Returns true if successful.
	 * @param unknown_type $user1Id
	 * @param unknown_type $user2Id
	 *
	 */
	function connect($user1Id, $user2Id) {
		
		$this->getDataSource()->begin();
		
		$retVal = false;				
		if (!$this->hasAny(array('user_id' => $user1Id, 'connected_user_id' => $user2Id))) {
			$retVal = $this->save(array( 
					'user_id' => $user1Id,
					'connected_user_id' => $user2Id));
			if (!$retVal) {
				$this->getDataSource()->rollback();
				return false;
			}
			$this->create();			
		} 
						
		if (!$this->hasAny(array('user_id' => $user2Id, 'connected_user_id' => $user1Id))) {
			$retVal = $this->save(array(
					'connected_user_id' => $user1Id,
					'user_id' => $user2Id));
			if (!$retVal) {
				$this->getDataSource()->rollback();
				return false;
			}
			$this->create();
		}
				
		
		$this->getDataSource()->commit();
		
		return true;
	}
	
	
	/**
	 * Retrieves the nodes, edges, and profile pictures necessary to display a user's network.
	 * @param unknown_type $userId user with respect to which network is defined. 
	 * @param unknown_type $degree degree from user to which her network should be displayed (not yet implmented).
	 */
	function findNetwork($userId, $degree=1) {
		$conns = $this->find('list', array('fields' => array('user_id', 'connected_user_id')));
		
		$edges = array();
		foreach($conns as $source => $target) {
			$edges[] = compact('source', 'target');
		}
			
		
		$users = $this->ToUser->find('all', array(
				'fields' => array('ToUser.nickname', 'ToUser.id'), 				
				'contain' => array('ProfilePicture' => array(
						'conditions' => array('type' => 'thumb'),
						'fields' => array('user_id', 'src')
				)),
				'order' => 'ToUser.id ASC',
				'conditions' => array('ToUser.id' => Set::extract('/source', $edges))
		));
		
	
		$pics = Set::extract($users, '/ProfilePicture/.');
		
		$nodes = array();
		foreach($users as $u) {
			$nodes[] = array(
					'name' => $u['ToUser']['nickname'],
					'id' => $u['ToUser']['id']
			);
		}
		
		return array($nodes, $edges, $pics);	
	}
}