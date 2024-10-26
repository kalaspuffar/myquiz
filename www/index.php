<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyQuiz</title>
    <meta name="description" content="Small site to play quick quizes.">
    <meta name="author" content="Daniel Persson">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/skeleton.css">
    <link rel="stylesheet" href="css/custom.css">   
</head>
<body>
    <div class="section hero">
        <div class="container">
            <div class="row">
                <div class="one-half column">
                    <div class="u-full-width column">
                        <h2>My quiz application</h2>
                        <p>
                            I hope this place will be a site with playful joy where you can challange your friends and 
                            collegues to a friendly quiz challange.                        
                        </p>
                        <p>
                            This service is now in beta, if you have been invited to a game, enter your 
                            <span style="color:yellow">game id</span> below:
                        </p>
                        <form action="player.php" method="GET">
                            <div class="one-half column">
                                <input type="text" name="game">
                            </div>
                            <div class="one-half column">
                                <button class="button button-primary">Get started</button>
                            </div>
                        </form>
                    </div>
                    <div class="u-full-width newgame_spot">
                        <p>
                            What to start a new game challanging your friends and collegues to a quiz match?
                        </p>
                        <a class="button" href="backend/login.php">Login to create a new game</a>
                    </div>
                </div>
                <div class="one-half column">
                    <img class="playful" src="images/playful.jpeg">
                </div>
            </div>
        </div>
    </div>
</body>
</html>