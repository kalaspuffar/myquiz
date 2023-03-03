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
    </head>
    <body>
        <div class="section gamemaster">
            <div class="container">
                <h1>Game master interface</h1>
                <div class="row">
                    <div class="one-half column">
                        Number of questions:
                        <input id="number_of_questions" type="text" value="3"/>
                    </div>
                    <div class="one-half column">
                        <button class="button green" onclick="fetchQuestions()">Fetch questions</button>
                    </div>                    
                </div>
                <div class="row u-full-width">
                    <button class="button green" onclick="sendQuestion()">Send question</button>
                    <button class="button green" onclick="showAnswer()">Show answer</button>
                    <button class="button green" onclick="showScoreBoard()">Show scoreboard</button>
                    <a class="button green" target="__blank" href="viewer.php?game=<?= $game_id ?>">Open big view</a>
                    <button class="button red" onclick="reset()">Reset</button>
                </div>
                <div class="row" id="userlist">
                </div>
                <div class="row u-full-width" id="questionlist">
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
                {name: 'King Louie', img: 'monkey.svg'},
                {name: 'Basil', img: 'mouse.svg'},
                {name: 'Octy', img: 'octopus.svg'},
                {name: 'Pumpkin', img: 'panda.svg'},
                {name: 'Frosty', img: 'penguin.svg'},
                {name: 'Baconator', img: 'pig.svg'},
                {name: 'Erectopus', img: 'rex.svg'},
                {name: 'Bolt', img: 'rhino.svg'},
                {name: 'Thumper', img: 'squirrel.svg'},
                {name: 'Poky', img: 'stego.svg'},
                {name: 'Tigrrrrr', img: 'tiger.svg'},
                {name: 'Shelly', img: 'turtle.svg'},
                {name: 'Sharpy', img: 'walross.svg'}
            ]

            var players = [];

            var answers = [];

            var questions;
            var currentQuestion = 0;
            var nextQuestion = 0;

            var lastQuestionTime = -1;

            var websocket;
            try {
                websocket = new WebSocket("wss://myquiz.app/websocket");
                websocket.onopen = function(msg) { 
                    var msg = {
                        op: 'join_game',
                        role: 'gamemaster',
                    };
                    send(msg);
                };
                websocket.onmessage = function(msg) {                     
                    var res = JSON.parse(msg.data);

                    if (res.message.op === 'name') {
                        players[res.user_id].name = res.message.answer.name;
                        players[res.user_id].img = res.message.answer.img;
                        players[res.user_id].score = 0;
                        players[res.user_id].last_question = -1;                        
                        reset(res.user_id);

                        userJoined();
                        updateUserList();
                    } else if (res.message.op === 'answer') {
                        if (!answers[currentQuestion]) {
                            answers[currentQuestion] = [];
                        }

                        var answerTime = new Date().getTime() - lastQuestionTime;

                        answers[currentQuestion].push({
                            answer: res.message.answer,
                            time: answerTime
                        });

                        var answerVal = questions[currentQuestion].answers[res.message.answer];
                        var correctVal = questions[currentQuestion].correct;
                        if (answerVal == correctVal) {
                            players[res.user_id].score += 1000 + (Math.max(1000 - Math.round(answerTime/10), 0));
                        }
                        players[res.user_id].last_question = currentQuestion;                        
                        updateUserList();

                        var has_answered = 0;
                        var all_players = 0;
                        for (var i in players) {
                            has_answered += (players[i].last_question == currentQuestion) ? 1 : 0;
                            all_players++;
                        }
                        if (has_answered == all_players) {
                            showAnswer();
                        }
                        reset(res.user_id);
                    } else if (res.message.op === 'join_game' && res.message.role == 'viewer') {
                        send({op: 'start'}, 'viewer');
                    } else if (res.message.op === 'join_game' && res.message.role == 'player') {
                        players[res.user_id] = {
                            user_id: res.user_id,
                            name: '',
                            score: 0
                        };

                        updateUserList();
                        sendNewCharToPlayer(res.user_id);
                    } else if (res.message.op === 'disconnect') {
                        delete players[res.user_id];
                        userJoined()
                        updateUserList();
                    }
                };
                websocket.onclose = function(msg) {};
            } catch(ex){ 
                console.log(ex); 
            }

            function userImageUsed(img) {
                for (const player_id in players) {
                    if (players[player_id].img == img) {
                        return true;
                    }
                }
                return false;
            }

            function updateQuestionList() {
                var questionlist = document.getElementById('questionlist');
                var table = '<TABLE class="u-full-width">';

                table += '<TR><TH>question</TH><TH>answers</TH><TH>correct</TH></TR>';

                for (const i in questions) {
                    if (nextQuestion == i) {
                        table += '<TR class="green">';
                    } else {
                        table += '<TR>';
                    }
                    table += '<TD>' + questions[i].question + '</TD>';
                    table += '<TD>' + questions[i].answers + '</TD>';
                    table += '<TD>' + questions[i].correct + '</TD>';
                    table += '</TR>';
                }

                table += '</TABLE>';
                questionlist.innerHTML = table;
            }

            function updateUserList() {
                var userlist = document.getElementById('userlist');
                var table = '<TABLE>';

                table += '<TR><TH>Char</TH><TH>Name</TH><TH>Score</TH><TH>Answered</TH></TR>';

                for (const i in players) {
                    var answered_last = players[i].last_question == currentQuestion;
                    table += '<TR>';
                    table += '<TD>' + (players[i].img ? '<img class="player_avatar_list" src="images/chars/' + players[i].img + '"/>' : '') + '</TD>';
                    table += '<TD>' + players[i].name + '</TD>';
                    table += '<TD>' + players[i].score + '</TD>';
                    table += '<TD>' + (answered_last ? '<img class="player_avatar_list" src="images/checkmark.svg"/>' : '') + '</TD>';
                    table += '</TR>';
                }

                table += '</TABLE>';
                userlist.innerHTML = table;
            }

            function fetchQuestions() {
                var num_element = document.getElementById('number_of_questions');
                fetch('fetch.php?num=' + num_element.value)
                    .then((response) => response.json())
                    .then((data) => {
                        nextQuestion = 0;
                        questions = data;
                        updateQuestionList();
                    });
            }

            function sendQuestion() {
                if (nextQuestion >= questions.length) {
                    return;
                }
                var question = {
                    op: 'question',
                    question: questions[nextQuestion].question,
                    answers: questions[nextQuestion].answers
                };
                send(question);
                send(question, 'viewer');
                lastQuestionTime = new Date().getTime();
                currentQuestion = nextQuestion;
                nextQuestion++;
                updateUserList();
                updateQuestionList();
            }

            function showAnswer() {
                var answered = [];
                for (var i in questions[currentQuestion].answers) {
                    answered[i] = 0;
                }
                for (var i in answers[currentQuestion]) {
                    answered[answers[currentQuestion][i].answer]++;
                }

                var msg = {
                    op: 'show_result',
                    question: questions[currentQuestion],
                    answered: answered
                };
                send(msg, 'viewer');
            }

            function showScoreBoard() {
                var scoreboard = [];                
                for (var i in players) {
                    scoreboard.push({
                        id: players[i].user_id,
                        name: players[i].name,
                        img: players[i].img,
                        score: players[i].score
                    });
                }

                scoreboard.sort(function (a, b) {
                    if (a.score > b.score) {
                        return -1;
                    }
                    if (a.score < b.score) {
                        return 1;
                    }
                    return 0;
                });

                var place = 1;
                for (var i in scoreboard) {
                    scoreboard[i].placement = place++;
                    send({op: 'show_score', 'score': scoreboard[i]}, 'player', scoreboard[i].id);
                }
                var msg = {
                    op: 'show_scoreboard',
                    scoreboard: scoreboard.slice(0, 5),
                };
                send(msg, 'viewer');
            }

            function reset(player_id) {
                send({op: 'reset'}, 'player', player_id);                
            }

            function userJoined() {
                var playersToSend = [];
                for (const i in players) {
                    playersToSend.push(players[i].img);
                }

                var msg = {
                    op: 'userlist',
                    users: playersToSend                
                };
                send(msg, 'viewer');
            }

            function sendNewCharToPlayer(player_id) {
                const char_id = Math.floor(Math.random() * characters.length);
                while (userImageUsed(characters[char_id].img) === true) {
                    char_id = Math.floor(Math.random() * characters.length);
                }
                send({op: 'name', 'char': characters[char_id]}, 'player', player_id);
            }

            function setname() {
                for (const player_id in players) {
                    sendNewCharToPlayer(player_id);
                }
            }

            function escapeUnicode(str) {
                return [...str].map(c => /^[\x00-\x7F]$/.test(c) ? c : c.split("").map(a => "\\u" + a.charCodeAt().toString(16).padStart(4, "0")).join("")).join("");
            }
            function stringify(str) {
                return escapeUnicode(JSON.stringify(str));
            }

            function send(msg, role = 'player', user_to = -1){
                if(!msg) { 
                    alert("Message can not be empty"); 
                    return;
                }
                try { 
                    var msgenvelop = {
                        timestamp: <?= $timestamp ?>,
                        game_id: <?= $game_id ?>,
                        role_to: role,
                        user_to: user_to,
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
                    websocket=null;
                }
            }

            function reconnect() {
                quit();
                init();
            }
        </script>
    </body>
</html>