<?php

class Deck
{
  private $deck;
  private $uid;
  private $deck_num;
  
  public function __construct($uid,$deck_num)
  {
    $this->deck = [];
    $this->uid = $uid;
    $this->deck_num = $deck_num;
  }

  public function add_card($card)
  {
    if(count($this->deck)<60)
    {
      array_push($this->deck,$card);
    }
    else
    {
      echo "Deck card limit reached, cannot add card";
    }
  }

  public function save_deck()
  {
    //$db = new mysqli("127.0.0.1","root","sisibdp02","login");
    foreach($this->deck as $card)
    {
      //insert the uid, decknum, and card print_tag into database
      echo "saving card to deck".PHP_EOL;
    }
    echo "Deck Saved".PHP_EOL;
    //var_dump($this->deck);
  }
  public function get_price($type)
  {
    $price = 0;
    if($type == "avg")
    {
      foreach($this->deck as $card)
      {
        $card_price = $card["avg_price"];
        $price += $card_price;
      }
    }
    elseif($type == 'high')
    {
      foreach($this->deck as $card)
      {
        $card_price = $card["high_price"];
        $price += $card_price;
      }
    }
    elseif($type == 'low')
    {
      foreach($this->deck as $card)
      {
        $card_price = $card["low_price"];
        $price += $card_price;
      }
    }
    return $price;
  }

}

?>
