<?php
    require_once __DIR__ . "/../etc/config.php";

    $timestamp = time();
    $game_id = $_GET['game'];
    $secret = hash("sha256", $SITE_SECRET . $timestamp . $game_id);
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>MyQuiz - Client</title>
        <meta name="description" content="Small site to play quick quizes. This is the client you play with.">
        <meta name="author" content="Daniel Persson">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <script async src="js/sha256.min.js"></script>
        
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/skeleton.css">
        <link rel="stylesheet" href="css/custom.css">
        <link rel="stylesheet" href="css/charts.min.css">
    </head>
    <body>
        <div class="section">
            <div class="container" id="main_body">
                <h2>Please wait</h2>
                <img id="waiting" src="images/waiting.svg" />
            </div>
            <div class="container" id="waiting_room" style="display:none;">
                <div class="row">
                    <div class="one-half column">
                        <img src="http://api.qrserver.com/v1/create-qr-code/?color=000000&bgcolor=FFFFFF&data=https%3A%2F%2Fmyquiz.app%2Fplayer.php%3Fgame%3D<?= $game_id ?>&qzone=1&margin=0&size=200x200&ecc=L" />
                    </div>
                    <div class="one-half column">
                        <h2>Welcome to a new game</h2>
                        <h3>To start playing visit <span style="color:yellow">https://myquiz.app</span> and enter the
                            game id <span style="color:yellow"><?= $game_id ?></span> to join the game.</h3>
                    </div>
                </div>
                <h4>Waiting for players</h4>
                <div id="player_list">
                </div>
            </div>
            <div class="container" id="answer_result" style="display:none;">
                <table id="answer_chart">
                </table>
            </div>
        </div>

        <script>            
            var main = document.getElementById('main_body');
            var waiting_room = document.getElementById('waiting_room');            
            var player_list = document.getElementById('player_list');
            var answer_result = document.getElementById('answer_result');
            var answer_chart = document.getElementById('answer_chart');

            var suggested_char = false;

            var game_has_started = false;

            var websocket;
            try {
                websocket = new WebSocket("wss://myquiz.app/websocket");
                websocket.onopen = function(msg) { 
                    var msg = {
                        op: 'join_game',
                        role: 'viewer',
                        role_to: 'gamemaster',
                    };
                    send(msg);
                };
                websocket.onmessage = function(msg) {                     
                    var res = JSON.parse(msg.data);

                    main.style.display = '';
                    waiting_room.style.display = 'none';
                    answer_result.style.display = 'none';

                    if (res.message.op === 'game_end') {                        
                        gameover(main);
                        quit();
                    } else if (res.message.op === 'userlist') {
                        if (game_has_started) return;
                        waiting_room.style.display = '';
                        main.style.display = 'none';
                        answer_result.style.display = 'none';

                        var playerList = '';
                        for (var i in res.message.users) {
                            playerList += '<img class="player_avatar_waiting" src="images/chars/' + res.message.users[i] + '">';                        
                        }
                        player_list.innerHTML = playerList;
                    } else if (res.message.op === 'question') {
                        game_has_started = true;
                        var data = '';
                        data += '<h2>' + res.message.question + '</h2>';
                        for (var i = 0; i < res.message.answers.length; i++) {
                            data += '<button class="button button-answer button-primary">' + 
                                res.message.answers[i] + '</button>';
                        }
                        main.innerHTML = data;
                    } else if (res.message.op === 'reset') {
                        game_has_started = false;
                        player_list.innerHTML = '';
                        reset(main);
                    } else if (res.message.op === 'start') {
                        if (game_has_started) return;
                        player_list.innerHTML = '';
                        waiting_room.style.display = '';
                        main.style.display = 'none';
                    } else if (res.message.op === 'show_result') {
                        main.style.display = 'none';
                        waiting_room.style.display = 'none';
                        answer_result.style.display = '';
                        answer_chart.className = 'charts-css column show-labels show-heading';
                        showResults(res.message);
                    } else if (res.message.op === 'show_scoreboard') {
                        main.style.display = 'none';
                        waiting_room.style.display = 'none';
                        answer_result.style.display = '';
                        answer_chart.className = 'charts-css bar show-labels show-heading data-spacing-10';                        
                        showScoreBoard(res.message);
                    }
                };
                websocket.onclose = function(msg) {};
            } catch(ex){ 
                console.log(ex);
            }

            function showResults(msg) {
                var largest = 0;
                for (var i in msg.answered) {
                    largest = msg.answered[i] > largest ? msg.answered[i] : largest;
                }
                               
                data = '';
                data += '<caption>' + msg.question.question + '</caption>';
                data += '<tbody>';
                
                for (var i in msg.answered) {
                    var color_string = '';
                    if (msg.question.answers[i] == msg.question.correct) {
                        color_string = '--color: #66BB6A;';
                    }
                    data += '<tr><th scope="row">' + msg.question.answers[i] + 
                            '</th><td style="' + color_string + '--size: calc( ' + msg.answered[i] + ' / ' + largest + ' )">' +
                            (msg.answered[i] > 0 ? msg.answered[i] : '') + '</td></tr>';
                }

                data += '</tbody>';

                answer_chart.innerHTML = data;
            }

            function showScoreBoard(msg) {                              
                var largest = 0;
                for (var i in msg.scoreboard) {
                    largest = msg.scoreboard[i].score > largest ? msg.scoreboard[i].score : largest;
                }

                data = '';
                //data += '<caption>Leader board</caption>';
                data += '<tbody>';
                
                for (var i in msg.scoreboard) {                    
                    var color_string = '';
                    if (msg.scoreboard[i].score == largest) {
                        color_string = '--color: #66BB6A;';
                    }
                    data += '<tr><th scope="row"><img class="player_avatar_list" src="images/chars/' + msg.scoreboard[i].img + '"/>' + msg.scoreboard[i].name +
                            '</th><td style="' + color_string + '--size: calc( ' + msg.scoreboard[i].score + ' / ' + largest + ' )">' +
                            (msg.scoreboard[i].score > 0 ? msg.scoreboard[i].score : '') + '</td></tr>';
                }

                data += '</tbody>';

                answer_chart.innerHTML = data;                
            }

            function escapeUnicode(str) {
                return [...str].map(c => /^[\x00-\x7F]$/.test(c) ? c : c.split("").map(a => "\\u" + a.charCodeAt().toString(16).padStart(4, "0")).join("")).join("");
            }
            function stringify(str) {
                return escapeUnicode(JSON.stringify(str));
            }

            function send(msg) {
                try { 
                    var msgenvelop = {
                        timestamp: <?= $timestamp ?>,
                        game_id: <?= $game_id ?>,
                        role_to: 'gamemaster',
                        message: msg,
                        secret: '<?= $secret ?>',
                    };
                    msgenvelop.hash = sha256(stringify(msgenvelop));
                    delete msgenvelop.secret;

                    websocket.send(stringify(msgenvelop)); 
                } catch(ex) { 
                    console.log(ex);
                }
            }
            
            function quit() {
                if (websocket != null) {
                    websocket.close();
                    websocket = null;
                }
            }

            function gameover(main) {
                var data = '';
                data += '<img id="gameover" src="images/gameover.png" />';
                main.innerHTML = data;
            }

            function reset(main) {
                var data = '';
                data += '<h2>Please wait</h2>';
                data += '<img id="waiting" src="images/waiting.svg" />';
                main.innerHTML = data;
            }
        </script>
    </body>
</html>