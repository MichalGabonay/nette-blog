<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class PostPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function renderShow($postId)
    {
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Stránka nebola nájdená!');
        }
 
        $this->template->post = $post;
        $this->template->comments = $post->related('comment')->order('created_at');
    }

    public function actionCreate()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    // create form for adding comments to post
    protected function createComponentCommentForm()
    {
        $form = new Form;

        $form->addText('name', 'Meno:')
            ->setRequired();

        $form->addEmail('email', 'Email:');

        $form->addTextArea('content', 'Komentár:')
            ->setRequired();

        $form->addSubmit('send', 'Publikovať komentár');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

    // Submit of comment to post
    public function commentFormSucceeded(Form $form, \stdClass $values)
    {
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Komentár bol pridaný. Ďakujeme.', 'success');
        $this->redirect('this');
    }

    // Create form for Post
    protected function createComponentPostForm()
    {
        $form = new Form;
        $form->addText('title', 'Titulok:')
            ->setRequired();
        $form->addTextArea('content', 'Obsah:')
            ->setRequired();

        $form->addSubmit('send', 'Uložiť a publikovať');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }

    // Submit new post
    public function postFormSucceeded(Form $form, \stdClass $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvorenie, alebo editovanie príspevkov musíte byť prihlásený.');
        }

        $postId = $this->getParameter('postId');

        if ($postId) {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
        }

        $this->flashMessage("Príspevok bol úspešne publikovaný.", 'success');
        $this->redirect('show', $post->id);
    }

    public function actionEdit($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Príspevok nebyl nájdený');
        }
        $this['postForm']->setDefaults($post->toArray());
    }
}