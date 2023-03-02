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

    if ($envelop->timestamp > time() + 3600) {
      return;
    }

    $hashResult = $envelop->hash;
    unset($envelop->hash);
    $envelop->secret = hash("sha256", $SITE_SECRET . $envelop->timestamp . $envelop->game_id);

    if ($hashResult == hash("sha256", json_encode($envelop))) {
      unset($envelop->secret);

      if (isset($envelop->message->op) && $envelop->message->op == 'join_game') {
        $this->myUsers[$sentBy->id]->game_id = $envelop->game_id;
        $this->myUsers[$sentBy->id]->role = $envelop->message->role;
        echo 'Joining ' . $sentBy->id . ' as ' . $envelop->message->role . "\n";
      }  
      if ($envelop->role_to == 'gamemaster') {
        $envelop->user_id = $sentBy->id;
      }

      if (isset($envelop->user_to) && $envelop->user_to != -1) {
        $this->send($this->myUsers[$envelop->user_to], json_encode($envelop));
      } else {
        foreach ($this->myUsers as $user) {
          if ($envelop->game_id == $user->game_id && $envelop->role_to == $user->role) {
            $this->send($user, json_encode($envelop));
          }
        }  
      }
    } else {
      echo "Hash issue\n";
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
    if (isset($this->myUsers[$user->id]) && $this->myUsers[$user->id]->role == 'player') {
      $envelop = new stdClass();
      $envelop->game_id = $this->myUsers[$user->id]->game_id;
      $envelop->user_id = $user->id;
      $envelop->message = new stdClass();
      $envelop->message->op = 'disconnect';
      
      foreach ($this->myUsers as $loopUser) {
        if ($envelop->game_id == $loopUser->game_id && $loopUser->role == "gamemaster") {
          $this->send($loopUser, json_encode($envelop));
        }
      }  
    }
    if (isset($this->myUsers[$user->id]) && $this->myUsers[$user->id]->role == 'gamemaster') {
      $envelop = new stdClass();
      $envelop->game_id = $this->myUsers[$user->id]->game_id;
      $envelop->message = new stdClass();
      $envelop->message->op = 'game_end';
      
      foreach ($this->myUsers as $loopUser) {
        if ($envelop->game_id == $loopUser->game_id && $loopUser->role != "gamemaster") {
          $this->send($loopUser, json_encode($envelop));
        }
      }  
    }
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
