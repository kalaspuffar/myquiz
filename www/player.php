<?php
    require_once __DIR__ . "/../etc/config.php";

    $timestamp = mktime();
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
    </head>
    <body>
        <div class="section hero">
            <div class="container" id="main_body">
                <h2>Please wait</h2>
                <img id="waiting" src="images/waiting.svg" />
            </div>
            <div class="container" id="name_input" style="display:none;">
                <div class="row">
                    <div class="one-half columns">
                        <img id="char_img" src="">
                    </div>
                    <div class="one-half columns">
                        <label for="playerNameInput">Player name</label>
                        <input id="char_name" type="text" placeholder="Froggy" id="playerNameInput">
                        <button class="button button-answer button-primary" onclick="setname()">Start</button>
                    </div>
                </div>
            </div>
        </div>

        <script>            
            var main = document.getElementById('main_body');
            var name_input = document.getElementById('name_input');
            var char_img = document.getElementById('char_img');
            var char_name = document.getElementById('char_name');

            var suggested_char = false;

            var websocket;
            try {
                websocket = new WebSocket("wss://myquiz.app/websocket");
                websocket.onopen = function(msg) { 
                    var msg = {
                        op: 'join_game',
                        role: 'player',
                        role_to: 'gamemaster',
                    };
                    send(msg);
                };
                websocket.onmessage = function(msg) {                     
                    var res = JSON.parse(msg.data);
                    
                    main.style.display = '';
                    name_input.style.display = 'none';

                    if (res.message.op === 'game_end') {                        
                        gameover(main);
                        quit();
                    } else if (res.message.op === 'name') {
                        suggested_char = res.message.char;
                        main.style.display = 'none';
                        name_input.style.display = '';
                        char_img.src = 'images/chars/' + suggested_char.img;
                        char_name.placeholder = suggested_char.name;
                    } else if (res.message.op === 'question') {
                        var data = '';
                        data += '<h2>' + res.message.question + '</h2>';
                        for (var i = 0; i < res.message.answers.length; i++) {
                            data += '<button class="button button-answer button-primary" onclick="answer(' + i + ')">' + 
                                res.message.answers[i] + '</button>';
                        }
                        main.innerHTML = data;
                    } else if (res.message.op === 'reset') {
                        reset(main);
                    }
                };
                websocket.onclose = function(msg) {};
            } catch(ex){ 
                console.log(ex);
            }

            function setname() {
                if (char_name.value !== '') {
                    suggested_char.name = char_name.value
                }
                var msg = {
                    op: 'name',
                    answer: suggested_char,
                };
                send(msg);
            }

            function answer(answer) {
                var msg = {
                    op: 'answer',
                    answer: answer,
                };
                send(msg);
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
                    msgenvelop.hash = sha256(JSON.stringify(msgenvelop));
                    delete msgenvelop.secret;

                    websocket.send(JSON.stringify(msgenvelop)); 
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