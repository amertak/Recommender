<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var \Nette\Database\Context
     * @inject
     */
    public $database;


    public function beforeRender()
    {
        $this->template->categories = $this->database->table("categories")->select("DISTINCT category");

        parent::beforeRender();
    }
}
