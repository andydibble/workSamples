<?php
/**
 * Controller for user connections.  Two users are connected if they have at least one confirmed lunch in which they are both participating.
 * @author Andy
 *
 */
class ConnectionsController extends AppController {
	
	var $uses = array('User', 'Connection');
	var $helpers = array('Connections', 'ProfilePicture');
	
	function view($userId=null) {
		
		$authId = $this->Auth->user('id');
		if ($userId==null) {
			$userId = $authId;
		} else if (!$this->Connection->connected($authId, $userId)) {	//don't allow viewing if auth user is not connected to userId.
			$this->redirect($this->referer());
		}
		
		if ($userId != $authId) {
			$userName = $this->User->findNameById($userId);
			$this->set('userName', $userName);
		}
		
		$network = $this->User->find('first', array(
			'conditions' => array('User.id' => $userId),
			'contain' => array('ConnUser.name' => array('ProfilePicture'), 'ProfilePicture'),
			'fields' =>	array('name')
		));
		
		
		
		$this->User->hasOne['ProfilePicture']['conditions'] = array('type' => 'thumb');
		$me = $this->User->find('first', array(
			'conditions' => array('User.id' => $userId),
			'contain' => array('ProfilePicture.src'),
			'fields' =>	array('name', 'User.id')
		)); 
		
				
		$network = $this->Connection->find('all', array(
				'conditions' => array('user_id' => $userId),
				'contain' => array(
						'ToUser.name', 
						'ToUser.id' => array(
								'ProfilePicture' => array('conditions' => array('type' => 'thumb'))))				
		));
		
		$this->set(compact('me', 'network'));
	}
	
	/**
	 * View a network using the D3 library (force-directed graph)
	 * @param unknown_type $userId id of the user to focus on.
	 */
	function viewD3($userId) {			
		list($graph['users'], $graph['edges'], $pics) = $this->Connection->findNetwork($userId);
		
		$this->set('pics', $pics);		
		$this->set('focusId', $userId);
		$this->set('graph', json_encode($graph));			
	}	
}