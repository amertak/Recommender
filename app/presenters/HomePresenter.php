<?php

namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Home presenter.
 */
class HomePresenter extends BasePresenter
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
        $quotes_db = $this->database->table('quotes')->order('RAND()')->limit(3);

        $quotes = [];

        foreach ($quotes_db as $quote_db)
        {
            $quote = new \Quote();
            $quote->text = $this->prepareText($quote_db->text);
            $quote->comment = $quote_db->comment;
            $quote->tags = ['Tag1', 'Tag2'];
            $quote->id = $quote_db->id;
            $quote->score = $quote_db->score;

            array_push($quotes, $quote);
        }

        $this->template->quotes = $quotes;
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
                $text = preg_replace('/&lt;(' . preg_quote($match, '/') . ')&gt;/', '&lt;<font color="' . $this->colors[$count % count($this->colors)] . '">$1</font>&gt;', $text);
                $count++;
            }
        }

        return $text;
    }

    public function renderRandom()
    {
    }
}