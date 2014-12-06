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


    /**
     * @var \Nette\Http\Request
     * @inject
     */
    public $request;

    /**
     * @var \Nette\Http\Response
     * @inject
     */
    public $response;

    /**
     * @var \Nette\Http\Session
     * @inject
     */
    public $session;

    public $userName;

    public function beforeRender()
    {
        $this->ensureUserName();

        $this->template->categories = $this->database->table("categories")->select("DISTINCT category");
        $this->template->user = $this->userName;

        parent::beforeRender();
    }

    private function ensureUserName()
    {
        $this->session->start();
        $cookies = $this->request->getCookies();

        if (!isset($cookies["recommender"]))
        {
            $this->userName = sha1($this->getGUID());

            $this->response->setCookie("recommender", $this->userName, time() + (10 * 365 * 24 * 60 * 60));
        }
        else
        {
            $this->userName = trim($cookies["recommender"], "{}");
        }
    }

    private function getGUID()
    {
        if (function_exists('com_create_guid'))
        {
            return com_create_guid();
        }
        else
        {
            mt_srand((double)microtime()*10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);
            return $uuid;
        }
    }
}
