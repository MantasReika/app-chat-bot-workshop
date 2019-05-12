<?php
  namespace Service;
  class UserSession {

    public $fileName;
    public $userFileJson;

    public function __construct($sender){
      // $this->sender = 123456789;
      $this->fileName = $sender . ".json";
      if (!file_exists($this->fileName)){
        $fileData = '{"game_status":"not_started","question":"","answer":"","answer_nr":"","points":0,"games_played":0}';
        file_put_contents($this->fileName, $fileData);
      }
      $this->userFileJson = json_decode(file_get_contents($this->fileName), true);
    }

    public function debugUserFileJson() {
      return json_encode($this->userFileJson);

    }

    public function saveUserSession() {
      file_put_contents($this->fileName, json_encode($this->userFileJson));
      return true;
    }

    public function getQuestion() {
      return $this->userFileJson["question"];
    }

    public function setQuestion($msg) {
      $this->userFileJson["question"] = $msg;
      return true;
    }

    public function getCorrectAnswer() {
      return $this->userFileJson["answer"];
    }

    public function setCorrectAnswer($msg) {
      $this->userFileJson["answer"] = $msg;
      return true;
    }

    public function getCorrectAnswerNr() {
      return $this->userFileJson["answer_nr"];
    }

    public function setCorrectAnswerNr($msg) {
      $this->userFileJson["answer_nr"] = $msg;
      return true;
    }

    public function getPoints() {
      return $this->userFileJson["points"];
    }

    public function setPoints($num) {
      $this->userFileJson["points"] = $num;
      return true;
    }

    public function incrementPoints() {
      $this->userFileJson["points"] = $this->getPoints() + 1;
      return true;
    }

    public function getGamesPlayed() {
      return $this->userFileJson["games_played"];
    }

    public function setGamesPlayed($num) {
      $this->userFileJson["games_played"] = $num;
      return true;
    }

    public function incrementGamesPlayed() {
      $this->userFileJson["games_played"] = $this->getGamesPlayed() + 1;
      return true;
    }

    public function getGameStatus() {
      return $this->userFileJson["game_status"];
    }

    public function setGameStatus($status) {
      $this->userFileJson["game_status"] = strtolower($status);
      return true;
    }

    public function checkAnswer($userGuess) {
      if ($userGuess == $this->getCorrectAnswerNr()) {
        return true;
      }
      else if (strtolower($userGuess) == strtolower($this->getCorrectAnswer())) {
        return true;
      }
      else {
        return false;
      }
    }
  }
?>
