<?php

namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Home presenter.
 */
class HomePresenter extends BasePresenter
{
    private $colors;

    public function __construct()
    {
        $this->colors = ["#b92c28", "#2b669a", "#3e8f3e", "#e38d13", "#269abc"];
    }


	public function renderDefault($id)
	{
        if (isset($id) && intval($id) > 0)
        {
            $quotes_db = $this->database->table('quotes')->where("id", $id)->limit(1);
        }
        else
        {
            $quotes_db = $this->database->table('quotes')->order('RAND()')->limit(1);
        }
        $quotes = [];

        foreach ($quotes_db as $quote_db)
        {
            $categories = [];
            $categories_db = $this->database->table('categories')->where('quote', $quote_db->id);

            foreach ($categories_db as $category_db)
            {
                array_push($categories, $category_db->category);
            }

            $quote = new \Quote();
            $quote->text = $this->prepareText($quote_db->text);
            $quote->comment = $quote_db->comment;
            $quote->categories = $categories;
            $quote->id = $quote_db->id;
            $quote->score = $quote_db->tscore;

            array_push($quotes, $quote);
        }

        $this->template->quotes = $quotes;
	}

    public function renderRateup($id)
    {
        $recommended = $this->database->query("
            select a.quote from (
            select quote
            from ratings
            where user in (select user from ratings where quote = $id and value > 0)
                and value > 0
                and quote <> $id
            group by quote
            order by count(quote) desc
            limit 50) as a
            order by RAND()
            limit 1")->fetch()->quote;

        $this->redirect('Home:default', $recommended);
    }

    public function renderRatedown($id)
    {
        $this->redirect('Home:default');
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
}