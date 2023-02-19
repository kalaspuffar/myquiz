#!/usr/bin/env php
<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../include/php_websockets/websockets.php';

class MyQuizWebSocketServer extends WebSocketServer {
  const PING_INTERVAL = 60;

  private $myUsers = [];
  private $nextPing = 0;

  protected function process($sentBy, $messageString) {
    global $SITE_SECRET;

    if ($messageString == '') {
      return;
    }

    $envelop = json_decode($messageString);

    $hashResult = $envelop->hash;
    unset($envelop->hash);
    $envelop->secret = hash("sha256", $SITE_SECRET . $envelop->timestamp . $envelop->game_id);

    if ($hashResult == hash("sha256", json_encode($envelop))) {
      unset($envelop->secret);

      if (isset($envelop->message->op) && $envelop->message->op == 'join_game') {
        $this->myUsers[$sentBy->id]->game_id = $envelop->game_id;
        $this->myUsers[$sentBy->id]->role = $envelop->message->role;
      }  
      if ($envelop->role_to == 'gamemaster') {
        $envelop->user_id = $sentBy->id;
      }

      foreach ($this->myUsers as $user) {
        if ($envelop->game_id == $user->game_id && $envelop->role_to == $user->role) {
          $this->send($user, json_encode($envelop));
        }
      }
    }
  }

  protected function tick() {
    if (time() > $this->nextPing) {
      foreach ($this->myUsers as $user) {
        $message = $this->frame('', $user, 'ping');
        @socket_write($user->socket, $message, strlen($message));
      }
      $this->nextPing = time() + self::PING_INTERVAL;
    }
  }

  protected function connected($user) {    
    if (!isset($this->myUsers[$user->id])) {
      $this->myUsers[$user->id] = $user;
    }
  }

  protected function closed($user) {
    if (isset($this->myUsers[$user->id])) {
      unset($this->myUsers[$user->id]);
    }
  }
}

$server = new MyQuizWebSocketServer('0.0.0.0', $WEBSOCKET_PORT);

try {
  $server->run();
} catch (Exception $e) {
  $server->stdout($e->getMessage());
}
