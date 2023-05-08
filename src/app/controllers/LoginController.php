<?php
use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    public function IndexAction()
    {
        // nothing here
    }
    public function loginAction()
    {
        $user = new Users();
        $user->assign(
            $this->request->getPost(),
            [
                'email',
                'password',
            ]
        );
        
        // query to find the user by name and email
        $sql = 'SELECT * FROM Users WHERE password = :password: AND email = :email:';
        $query = $this->modelsManager->createQuery($sql);
        $usr = $query->execute([
            'email' => $user->email,
            'password' => $user->password
        ]);
        // if(isset($_POST['remember']) && isset($usr[0])) {
        //     $this->cookies->set('email', $user->email);
        //     $this->cookies->set('password', $user->password);
        // }
        // if some result is found, then return as logged in, else user doesn't exist
        if (isset($usr[0])) {
            $this->view->success = true;
            $this->view->message = "LoggedIn succesfully";
        } else {
            $this->view->message = "Invalid Credentials";
        }
    }
}
