<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class SignPresenter extends Nette\Application\UI\Presenter
{
    // form fo signing in
    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->addText('username', 'Uživateľské meno:')
            ->setRequired('Prosím vyplňte uživateľské meno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte heslo.');

        $form->addSubmit('send', 'Prihlásiť');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $values)
    {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávne prihlasovacie meno alebo heslo.');
        }
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect('Homepage:');
    }
}