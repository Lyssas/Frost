<?php
/**
* A user controller to manage login and view edit the user profile.
*
* @package FrostCore
*/
class CCUser extends CObject implements IController {


	/**
	* Constructor
	*/
	public function __construct() {
		parent::__construct();
	}

	/**
	* Show profile information of the user.
	*/
	public function Index() {
		$this->views->SetTitle('User Controller');
		$this->views->AddInclude(__DIR__ . '/index.tpl.php', array('is_authenticated'=>$this->user['isAuthenticated'],
		 'user'=>$this->user,
			));
	}

	/**
	* View and edit user profile.
	*/
	public function Profile() {
		$form = new CFormUserProfile($this, $this->user);
		$form->Check();

		$this->views->SetTitle('User Profile');
		$this->views->AddInclude(__DIR__ . '/profile.tpl.php', array('is_authenticated'=>$this->user['isAuthenticated'],
		 'user'=>$this->user,
		 'profile_form'=>$form->GetHTML(),
			));
	}


 /**
* Authenticate and login a user.
*/
  public function Login() {
    $form = new CFormUserLogin($this);
    if($form->Check() === false) {
    	$this->session->AddMessage('notice', 'Some fields did not validate and the form could not be processed.');
    	$this->RedirectToController('login');
    }
    $this->views->SetTitle('Login');
	$this->views->AddInclude(__DIR__ . '/login.tpl.php', array('login_form'=>$form,

								'allow_create_user'=> CLydia::Instance()->config['create_new_users'],
								'create_user_url'=> CLydia::Instance()->request->CreateUrl(null, 'create')
								));
  }

  /**
  * Create a new user
  */
  public function Create() {
  	$form = new CFormUserCreate($this);
  	if($form->Check() === false) {
  		$this->session->AddMessage('notice', 'You must fill in all values.');
  		$this->RedirectToController('Create');
  	}
  	$this->views->SetTitle('Create User');
    $this->views->AddInclude(__DIR__ . '/create.tpl.php', array('form' => $form->GetHTML()));     
                  }

	/**
	* Authenticate and login a user.
	*/
	public function DoLogin($form) {
		if($this->user->Login($form['acronym']['value'], $form['password']['value'])) {
			$this->RedirectToController('profile');
			$this->session->AddMessage('success', "Welcome {$this->user['name']}.");

		} else {
			$this->session->AddMessage('notice', "Failed to login, user does not exist or password does not match.");
			$this->RedirectToController('login');
		}
	}

	/**
	* Perform a creation of a user as callback on a submitted form
	*
	* @param $form CForm the form that was submitted
	*/
	public function DoCreate($form) {
		if($form['password']['value'] != $form['password1']['value'] || empty($form['password']['value'])
			|| empty($form['password1']['value'])) {
			$this->session->AddMessage('error', 'Password does not match or is empty.');
			$this->RedirectToController('create');
		} else if($this->user->Create($form['acronym']['value'],
									$form['password']['value'],
									$form['name']['value'],
									$form['email']['value']
									)) {
			$this->session->AddMessage('success', "Welcome {$this->user['name']}. You have successfully
				created a new account.");
			$this->user->Login($form['acronym']['value'], $form['password']['value']);
			$this->RedirectToController('profile');
		} else {
			$this->session->AddMessage('notice', "Failed to create an account.");
			$this->RedirectToController('create');
		}
	}

	/**
	* Logout a user.
	*/
	public function Logout() {
		$this->user->Logout();
		$this->RedirectToController('Index');
	}

	/**
	* Init the user database.
	*/
	public function Init() {
		$action = 'install';
		$this->user->Manage($action);
		$this->RedirectToController();
	}

	/**
	* Change the password.
	*/
	public function DoChangePassword($form) {
	  if($form['password']['value'] != $form['password1']['value'] || empty($form['password']['value']) || empty($form['password1']['value'])) {
      $this->session->AddMessage('error', 'Password does not match or is empty.');
    } else {
      $ret = $this->user->ChangePassword($form['password']['value']);
      $this->session->AddMessage($ret, 'Saved new password.', 'Failed updating password.');
    }
    $this->RedirectToController('profile');
  }

    /**
   * Save updates to profile information.
   */
  public function DoProfileSave($form) {
    $this->user['name'] = $form['name']['value'];
    $this->user['email'] = $form['email']['value'];
    $ret = $this->user->Save();
    $this->session->AddMessage($ret, 'Saved profile.', 'Failed saving profile.');
    $this->RedirectToController('profile');
  }

}