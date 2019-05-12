<?php
  namespace Service;

  use GuzzleHttp\Client;

  class OpentdbApi {

    private $apiResponse;
    private $correctAnswerNr;
    private $constructedAnswers;

    public function __construct() {
      $client = new Client();
      $response = $client->get('https://opentdb.com/api.php?difficulty=easy&amount=1');
      $apiJsonResponse = $response->json();
      $code = $response->getStatusCode(); // 200
      if ($code != 200) {
        throw new Exception("API returned status code: " . $code);
      }
      $this->apiResponse = $apiJsonResponse["results"][0];
      $this->constructedAnswers = $this->constructAnswers();
    }

    public function debugInfo(){
      return $this->apiResponse;
    }

    public function getCategory(){
      return $this->apiResponse["category"];
    }

    public function getQuestion(){
      return $this->apiResponse["question"];
    }

    public function getCorrectAnswer(){
      return $this->apiResponse["correct_answer"];
    }

    public function getIncorrectAnswers(){
      return $this->apiResponse["incorrect_answers"];
      // return json_decode($this->apiResponse["incorrect_answers"]);
    }

    public function getCorrectAnswerNr(){
      return $this->correctAnswerNr;
    }

    public function setCorrectAnswerNr($nr){
      $this->correctAnswerNr = $nr;
      return true;
    }

    public function constructAnswers(){
      $answersCount = count($this->getIncorrectAnswers()) + 1;
      $correctAnswerNr = rand(1, $answersCount);
      $this->setCorrectAnswerNr($correctAnswerNr);

      $incorrectAnswers = $this->getIncorrectAnswers();
      $constructedAnswers = '';

      $count = 0;
      foreach (range(1, $answersCount) as $cnt) {
        if ($cnt == $correctAnswerNr){
          $constructedAnswers = $constructedAnswers . $correctAnswerNr . ". " . $this->getCorrectAnswer() . ".\n";
        }
        else {
          $constructedAnswers = $constructedAnswers . $cnt . ". " . $incorrectAnswers[$count] . ".\n";
          $count++;
        }

      };
      return $constructedAnswers;
    }

    public function getConstructedAnswers() {
      return $this->constructedAnswers;
    }
  }
?>
