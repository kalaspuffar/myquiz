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
            <div class="container">
                <div class="row">
                    <div id="log" class="one-half column" style="background-color: white; border-radius:100px; color: black; padding: 10px"></div>
                    <div class="one-half column">
                        <button onclick="question()">Question</button>
                        <button onclick="reset()">Reset</button>
                        <button onclick="setname()">Name</button>
                    </div>
                </div>
            </div>
        </div>
        <script>            

            var characters = [
                {name: 'Antony', img: 'ant.svg'},
                {name: 'Teddy', img: 'bear.svg'},
                {name: 'Feathers', img: 'bird.svg'},
                {name: 'Tally', img: 'bronto.svg'},
                {name: 'Simba', img: 'cat.svg'},
                {name: 'Crabby', img: 'crab.svg'},
                {name: 'Zippy', img: 'dog.svg'},
                {name: 'Dopey', img: 'donkey.svg'},
                {name: 'Dumbo', img: 'elephant.svg'},
                {name: 'Moosy', img: 'elk.svg'},
                {name: 'Froggy', img: 'frog.svg'},
                {name: 'Beanstalk', img: 'giraffe.svg'},
                {name: 'Butter', img: 'goat.svg'},
                {name: 'Trigger', img: 'horse.svg'},
                {name: 'Cranky', img: 'krokodile.svg'},
                {name: 'Shiva', img: 'leopard.svg'},
                {name: 'Aslan', img: 'lion.svg'},
                {name: 'King loue', img: 'monkey.svg'},
                {name: 'Basil', img: 'mouse.svg'},
                {name: 'Octy', img: 'octopus.svg'},
                {name: 'Pumpkin', img: 'panda.svg'},
                {name: 'Frosty', img: 'penguin.svg'},
                {name: 'Baconator', img: 'pig.svg'},
                {name: 'Erectopus', img: 'rex.svg'},
                {name: 'Bolt', img: 'rhino.svg'},
                {name: 'Thumper', img: 'squirrel.svg'},
                {name: 'Horney', img: 'stego.svg'},
                {name: 'Tigrrrrr', img: 'tiger.svg'},
                {name: 'Shelly', img: 'turtle.svg'},
                {name: 'Sharpy', img: 'walross.svg'},
            ]


            var websocket;
            try {
                websocket = new WebSocket("wss://myquiz.app/websocket");
                //log('WebSocket - status '+websocket.readyState);
                websocket.onopen = function(msg) { 
                    //log("Welcome - status "+this.readyState); 

                    var msg = {
                        op: 'join_game',
                        role: 'gamemaster',
                    };
                    send(msg);
                    //log('Sent: ' + JSON.stringify(msgenvelop));
                };
                websocket.onmessage = function(msg) {                     
                    var res = JSON.parse(msg.data);
                    if (res.message.op === 'name') {
                        log("Name: " + JSON.stringify(res));
                        reset();
                    } else if (res.message.op === 'answer') {
                        log("Answer: " + JSON.stringify(res));
                        reset();
                    } else {
                        log("Received: " + res.message);
                    }
                };
                websocket.onclose   = function(msg) { 
                    //log("Disconnected - status "+this.readyState); 
                };
            } catch(ex){ 
                log(ex); 
            }
            $("msg").focus();


            function question() {
                var question = {
                    op: 'question',
                    question: 'What country has the highest life expectancy?',
                    answers: ['Italia', 'Sweden', 'Hong Kong', 'Bolivia']                    
                };
                send(question);
            }

            function reset() {
                send({op: 'reset'});
            }

            function setname() {
                var char_id = Math.floor(Math.random() * characters.length);
                
                send({op: 'name', 'char': characters[char_id]});
            }

            function send(msg, user_to = -1){
                if(!msg) { 
                    alert("Message can not be empty"); 
                    return;
                }
                try { 
                    var msgenvelop = {
                        timestamp: <?= $timestamp ?>,
                        game_id: <?= $game_id ?>,
                        role_to: 'player',
                        user_to: user_to,
                        message: msg,
                        secret: '<?= $secret ?>',
                    };
                    msgenvelop.hash = sha256(JSON.stringify(msgenvelop));
                    delete msgenvelop.secret;

                    websocket.send(JSON.stringify(msgenvelop)); 
                    //log('Sent: ' + JSON.stringify(msgenvelop)); 
                } catch(ex) { 
                    log(ex); 
                }
            }
            function quit(){
                if (websocket != null) {
                    log("Goodbye!");
                    websocket.close();
                    websocket=null;
                }
            }

            function reconnect() {
                quit();
                init();
            }

            // Utilities
            function $(id){ return document.getElementById(id); }
            function log(msg){ $("log").innerHTML+="<br>"+msg; }
            function onkey(event){ if(event.keyCode==13){ send(); } }

        </script>
    </body>
</html>