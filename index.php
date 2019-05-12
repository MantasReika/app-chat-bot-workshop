<?php

use Facebook\Facebook;
use Service\ConfigProvider;
use Service\OpentdbApi;
use Service\UserSession;

include (__DIR__ . '/vendor/autoload.php');


$configProvider = new ConfigProvider(__DIR__ . "/config.json");

$access_token = $configProvider->getParameter("access_token");
$verify_token = $configProvider->getParameter("verify_token");
$appId = $configProvider->getParameter("app_id");
$appSecret = $configProvider->getParameter("app_secret");

if(isset($_REQUEST['hub_challenge'])) {
    $challenge = $_REQUEST['hub_challenge'];
    if ($_REQUEST['hub_verify_token'] === $verify_token) {
        echo $challenge; die();
    }
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input === null) {
    exit;
}

$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
$sender_message = $input['entry'][0]['messaging'][0]['message']['text'];

$message = "Do you want to play a game?\n\nEnter 'Start' to play a game";
$userSession = new UserSession($sender);

file_put_contents('debug.txt', $userSession->debugUserFileJson());
$userSession->saveUserSession();

if ($userSession->getGameStatus() == 'started' && $userSession->getGamesPlayed() >= 10){
  if ($userSession->checkAnswer($sender_message)) {
    $userSession->incrementPoints();
    $message = "Correct!";
  }
  else {
    $message = "Nope!\nThe answer was: \n" . $userSession->getCorrectAnswerNr() . ". " . $userSession->getCorrectAnswer();
  }

  $message = $message . "\n\nGame over, Congratulations,\nyou got " . $userSession->getPoints() . " out of " . $userSession->getGamesPlayed() . " points!";
  $userSession->setGameStatus("not_started");
  $userSession->setGamesPlayed(0);
  $userSession->setPoints(0);
}
else if ($userSession->getGameStatus() != 'started' &&strtolower( $sender_message) == 'start') {
  $userSession->setGameStatus('started');

  $questionApi = new OpentdbApi();
  $userSession->setQuestion($questionApi->getQuestion());
  $userSession->setCorrectAnswer($questionApi->getCorrectAnswer());
  $userSession->setCorrectAnswerNr($questionApi->getCorrectAnswerNr());
  $message = $questionApi->getQuestion() . "\n" . $questionApi->getConstructedAnswers();

}
else if ($userSession->getGameStatus() == 'started') {
  $userSession->incrementGamesPlayed();
  if ($userSession->checkAnswer($sender_message)) {
    $userSession->incrementPoints();
    $message = "Correct!\n You now have " . $userSession->getPoints() . " points. Game ". $userSession->getGamesPlayed() . " out of 10" . "\n\n";
  }
  else {
    $message = "Nope!\nThe answer was: \n" . $userSession->getCorrectAnswerNr() . ". " . $userSession->getCorrectAnswer() . "\n You have " . $userSession->getPoints() . " points. Game ". $userSession->getGamesPlayed() . " out of 10" . "\n\n";
  }
  $questionApi = new OpentdbApi();
  $userSession->setQuestion($questionApi->getQuestion());
  $userSession->setCorrectAnswer($questionApi->getCorrectAnswer());
  $userSession->setCorrectAnswerNr($questionApi->getCorrectAnswerNr());
  $message = $message . $questionApi->getQuestion() . "\n" . $questionApi->getConstructedAnswers();

}
$userSession->saveUserSession();

$fb = new Facebook([
    'app_id' => $appId,
    'app_secret' => $appSecret,
]);

$data = [
    'messaging_type' => 'RESPONSE',
    'recipient' => [
        'id' => $sender,
    ],
    'message' => [
        'text' => htmlspecialchars_decode($message, ENT_QUOTES),
    ]
];

$response = $fb->post('/me/messages', $data, $access_token);
