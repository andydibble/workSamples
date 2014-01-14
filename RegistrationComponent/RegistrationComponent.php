<?php
App::uses('Controller/Component', 'AppComponent');
/**
 * Component used to coordinate which actions are used for registration, their order, and redirection after registration is complete.
 * Allows user to leave/logoff and return to the registration action she was on (registration_stage field).  
 * Also manages marking registration as complete for a user (is_initialized field) when she has proceeded through the whole pathway.
 * @author Andy
 *
 */
class RegistrationComponent extends AppComponent {
	
	/**
	 * Id of the registering user.
	 * @var unknown_type
	 */
	private $userId;	
	
	/**
	 * The registration pathway.  Array steps are actions that all redirect to the next stage.  If the next stage is an array, the first action in the array is redirected to.
	 * @var unknown_type
	 */
	private $pathway = array('users/register', array('users/companies', 'companies/edit'), 'users/bio', 'users/interests', 'profilePictures/edit', 'users/newCity');
	
	/**
	 * set post registration fields
	 * @see Component::initialize()
	 */
	public function initialize(Controller $controller) {
		$this->postRegistrationAction = 'full_calendar/calendars';
		$this->postRegistrationMessage = '<strong>How do I get started?</strong>  Click on the calendar below to enter some times when you are available to meet.  This will create a Lunch Date, which will appear in search results for your current city.  When you are done, visit your <a href="'.Router::url('/users/view').'">profile page</a>.';
	}
		
	/**
	 * Manages redirection to the next stage in the pathway.  Also preserves which stage the user is on in her registration_stage field and redirects to the post registration, including display of post registration message.  
	 * @param unknown_type $queryString query string to include in redirect url.
	 */
	public function redirect($queryString=null) {
		$curStage = Inflector::variable($this->controller->request->params['controller']).'/'.Inflector::variable($this->controller->request->params['action']);
				
		$this->userId = $this->controller->User->id;
		if (!$this->userId) {
			$this->userId = $this->controller->Auth->user('id');
		}
		
		$userId = $this->userId;
				
		foreach($this->pathway  as $i => $stage) {				
			if (is_array($stage) && in_array($curStage, $stage) || $curStage==$stage) {		
				if ($i+1 < count($this->pathway)) {					
					$nextStage = $this->pathway[$i+1];
					if (is_array($nextStage)) {
						$nextStage = $nextStage[0];
					}
					break;					
				} else {	//run post registration action.									
					$this->controller->Session->setFlash($this->postRegistrationMessage);
					
					//mark user as initialized because registration is complete.
					$this->controller->User->save(array('id' => $userId, 'registration_stage' => null, 'is_initialized' => 1));	//clear because registration is complete.
							
					$this->controller->redirect('/'.$this->postRegistrationAction);					
				}
			}
		}
		
		if (isset($nextStage)) {			
			$this->controller->User->saveField('registration_stage', $nextStage);
				
			$url = '/'.$nextStage.'/'.$userId;
	
			if ($queryString) {
				$url .= '?'.$queryString;
			}
			$this->controller->redirect($url);
		}	
	}
}