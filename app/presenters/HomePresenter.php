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


    public function renderCategory($id)
    {
        $this->setView("default");

        $quotes_db = $this->database->query("SELECT quotes.text, quotes.id, quotes.comment, quotes.tscore From quotes join categories on quotes.id = categories.quote where category = '$id' order by rand() limit 3");
        $this->template->quotes = $this->prepareQuotesToRender($quotes_db);
    }


	public function renderDefault($id)
	{
        if (isset($id) && intval($id) > 0)
        {
            $quotes_db = $this->database->table('quotes')->where("id", $id);
        }
        else
        {
            $quotes_db = $this->database->table('quotes')->order('RAND()')->limit(1);
        }

        $this->template->quotes = $this->prepareQuotesToRender($quotes_db);
	}

    private function prepareQuotesToRender($quotesFromDB)
    {
        $quotes = [];
        foreach ($quotesFromDB as $quote_db)
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

        return $quotes;
    }

    public function renderRateup($id)
    {
        $this->rateQuote($id, 10);
        $this->redirect('Home:default', $this->getRecommendedQuote($id));
    }

    private function getRecommendedQuote($id)
    {
        $user = $this->userName;

        return $this->database->query("
            select b.quote from (
            select quote from (select quote
            from ratings
            where user in (select user from ratings where quote = $id and value > 0)
                and value > 0
                and quote <> $id
                and user <> '$user'
            group by quote
            order by count(quote) desc
            ) as b where quote not in (select quote from ratings where user = '$user')
            limit 50) as b
            order by RAND()
            limit 1")->fetch()->quote;
    }

    public function renderRatedown($id)
    {
        $this->rateQuote($id, -10);
        $this->redirect('Home:default');
    }

    private function rateQuote($quoteID, $score)
    {
        $user = $this->userName;

        $row = $this->database->table("ratings")->select("id")->where("user", $user)->where("quote", $quoteID)->fetch();

        if (isset($row->id))
        {
            return;
        }

        $this->database->table("ratings")->insert(array('user' => $user,'quote' => $quoteID,'value' => $score));

        if ($score > 0)
        {
            $this->database->query("UPDATE quotes SET infavor = infavor + $score WHERE id = $quoteID");
        }
        else
        {
            $this->database->query("UPDATE quotes SET against = against + " . abs($score) . " WHERE id = $quoteID");
        }

        $this->database->query("UPDATE quotes SET score = infavor / (infavor + against) * 100 WHERE id = $quoteID");
        $this->database->query("UPDATE quotes SET tscore = score - 10000*(1 / ( 1 + infavor + against)) WHERE id = $quoteID");
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