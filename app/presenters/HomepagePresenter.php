<?php

namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;
    private $colors;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->colors = ["#b92c28", "#2b669a", "#3e8f3e", "#e38d13", "#269abc"];
    }

	public function renderDefault()
	{
        $quotes = $this->database->table('quotes')->order('RAND()')->limit(1);

        foreach ($quotes as $quote)
        {
            $this->template->text = $this->prepareText($quote->text);
            $this->template->quote = $quote;
        }
	}

    private function prepareText($text)
    {
        $text = preg_replace('/(\d\d:\d\d:?\d?\d?)?[^&lt;]&lt;.*&gt;/', '<br />$0', $text);

        if (preg_match_all('/&lt;(.*)&gt;/', $text, $matches))
        {
            $count = 0;
            $matches = array_unique($matches[1]);

            foreach ($matches as $match)
            {
                $text = preg_replace('/&lt;(' . preg_quote($match) . ')&gt;/', '&lt;<font color="' . $this->colors[$count % count($this->colors)] . '">$1</font>&gt;', $text);
                $count++;
            }
        }

        return $text;
    }

    public function renderTest()
    {
        $this->redirect('default');
    }
}