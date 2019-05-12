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

// $access_token = 'EAAIkcNqpIeYBAH0dOakSRUnmhcsBU8kwrOllTm1lKz8fF7vNTn5zEqX1FCvKQY9Pfu4ouw1lnodqFYP1SgL4P6y2eYoxxlLfPZBxfd9f3IKpPDBXs53AwZAwTDJZALPUaqqGVITyAIJzq8HOePvj9cVwZB6qZCbe1TauSze6iG9SBRZAq61ncL';
// $verify_token = 'TOKEN';
// $appId = '603017076875750';
// $appSecret = 'd0bea5b778ce847279cafffbaf439432';


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
        'text' => htmlspecialchars_decode($message),
    ]
];

// TODO: if person hasnt started a game then donot send button response.
// $data = [
//     'messaging_type' => 'RESPONSE',
//     'recipient' => [
//         'id' => $sender,
//     ],
//     'message' => [
//       "attachment" => [
//         "type" => "template",
//         "payload" => [
//           "template_type" => "button",
//           "text" => $message,
//           "buttons" => [
//             [
//               "type" => "web_url",
//               "url" => "https => //www.messenger.com",
//               "title" => "Visit Messenger1"
//             ],
//             [
//               "type" => "web_url",
//               "url" => "https => //www.messenger.com",
//               "title" => "Visit Messenger2"
//             ],
//             [
//               "type" => "web_url",
//               "url" => "https => //www.messenger.com",
//               "title" => "Visit Messenger3"
//             ]
//           ]
//         ]
//       ]
//     ]
//   ];

$response = $fb->post('/me/messages', $data, $access_token);
