<?php
session_start();

// Initialize players and state
if (!isset($_SESSION['players'])) {
    $_SESSION['players'] = [
        'Player 1' => 0,
        'Player 2' => 0
    ];
    $_SESSION['currentPlayer'] = 'Player 1';
    $_SESSION['usedQuestions'] = [];
}

// Reset game
if (isset($_POST['reset'])) {
    session_destroy();
    header("Location: jeopardy.php");
    exit;
}

$categories = ["Anime", "Popular Video Games", "Computer Science", "Marvel", "Star Wars"];
$values = [100, 200, 300, 400, 500];

// Questions and answers
$questions = [
    "Anime" => [
        100 => "What is the name of the ninja who dreams of becoming Hokage?",
        200 => "In Dragon Ball Z, who is Goku's main rival and frenemy?",
        300 => "In Attack on Titan, what are the giant creatures that humanity fights called?",
        400 => "What Studio Ghibli film features a giant cat bus and a forest spirit?",
        500 => "In Death Note, what is the full name of the high school student who finds the notebook?"
    ],
    "Popular Video Games" => [
        100 => "What game features a battle royale on an island with 100 players and building mechanics?",
        200 => "In Minecraft, what do you need to mine diamonds?",
        300 => "What 2020 game about a crewmate vs imposters became a viral hit during the pandemic?",
        400 => "In The Legend of Zelda series, what is the name of the main playable character?",
        500 => "What critically acclaimed RPG features a time-looping cycle in the underworld and is based on Greek mythology?"
    ],
    "Computer Science" => [
        100 => "What does 'CPU' stand for in computer terminology?",
        200 => "Which number system do computers use to store data - decimal or binary?",
        300 => "What is the name of the structure where each element points to the next one in memory?",
        400 => "What kind of loop continues running until a certain condition is false?",
        500 => "What's the term for a function that calls itself within its own definition?"
    ],
    "Marvel" => [
        100 => "What billionaire superhero leads the Avengers and wears a metal suit?",
        200 => "What is the name of Thor's enchanted hammer?",
        300 => "What Infinity Stone does Vision have in his forehead?",
        400 => "Who is T'Challa better known as?",
        500 => "What is the real name of the Scarlet Witch?"
    ],
    "Star Wars" => [
        100 => "Who was Anakin Skywalker's Jedi Master?",
        200 => "What is the name of Han Solo's ship?",
        300 => "Which small, green Jedi Master famously says, 'Do or do not. There is no try'?",
        400 => "What is the name of the desert planet where Rey is introduced in The Force Awakens?",
        500 => "What clone trooper order causes the Jedi Purge in Revenge of the Sith?"
    ]
];

$correctAnswers = [
    "Anime" => [
        100 => "Naruto Uzumaki",
        200 => "Vegeta",
        300 => "Titans",
        400 => "My Neighbor Totoro",
        500 => "Light Yagami"
    ],
    "Popular Video Games" => [
        100 => "Fortnite",
        200 => "Iron Pickaxe",
        300 => "Among Us",
        400 => "Link",
        500 => "Hades"
    ],
    "Computer Science" => [
        100 => "Central Processing Unit",
        200 => "Binary",
        300 => "Linked List",
        400 => "While loop",
        500 => "Recursion"
    ],
    "Marvel" => [
        100 => "Iron Man",
        200 => "Mjolnir",
        300 => "Mind Stone",
        400 => "Black Panther",
        500 => "Wanda Maximoff"
    ],
    "Star Wars" => [
        100 => "Obi-Wan Kenobi",
        200 => "Millennium Falcon",
        300 => "Yoda",
        400 => "Jakku",
        500 => "Order 66"
    ]
];

// State variables
$selectedCategory = $_POST['category'] ?? null;
$selectedValue = $_POST['value'] ?? null;
$showQuestion = false;
$questionText = '';
$questionKey = "$selectedCategory-$selectedValue";

// Handle question click
if ($selectedCategory && $selectedValue && !isset($_POST['submit_answer'])) {
    $showQuestion = true;
    $questionText = $questions[$selectedCategory][$selectedValue] ?? "No question found.";
}

// Handle answer submission
if (isset($_POST['submit_answer'])) {
    $category = $_POST['category'];
    $value = $_POST['value'];
    $answer = trim($_POST['answer']);
    $correct = $correctAnswers[$category][$value] ?? '';

    if (strcasecmp($answer, $correct) == 0) {
        $_SESSION['players'][$_SESSION['currentPlayer']] += $value;
        $resultMessage = "Correct! +$value points.";
    } else {
        $_SESSION['players'][$_SESSION['currentPlayer']] -= $value;
        $resultMessage = "Incorrect! -$value points. Correct answer: $correct";
    }

    $_SESSION['usedQuestions'][] = "$category-$value";

    // Build HTML content
    $html = "<!DOCTYPE html>\n<html>\n<head>\n<title>Leaderboard</title>\n</head>\n<body>\n";
    $html .= "<h1>Jeopardy Leaderboard</h1>\n<ul>\n";

    foreach ($_SESSION['players'] as $player => $score) {
        $html .= "<li><strong>" . htmlspecialchars($player) . ":</strong> " . intval($score) . " points</li>\n";
    }

    $html .= "</ul>\n</body>\n</html>";

    // Write to leaderboard.html
    file_put_contents("leaderboard.html", $html);


    // Switch turn
    $_SESSION['currentPlayer'] = $_SESSION['currentPlayer'] === 'Player 1' ? 'Player 2' : 'Player 1';
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Jeopardy Multiplayer</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

    <h1>Jeopardy</h1>

    <h2><a href="index.html">Back to Homepage</a></h2>

    <h3>Current Turn: <?= $_SESSION['currentPlayer'] ?></h3>
    <h3>Scores:</h3>
    <ul style="list-style: none;">
        <?php foreach ($_SESSION['players'] as $player => $score): ?>
            <li><strong><?= $player ?>:</strong> <?= $score ?> points</li>
        <?php endforeach; ?>
    </ul>

    <form method="post">
        <button name="reset" type="submit">Reset Game</button>
    </form>

    
    <?php if (isset($resultMessage)): ?>
        <p><strong><?= $resultMessage ?></strong></p>
    <?php endif; ?>

    <?php if ($showQuestion): ?>
        <div class="question-box">
            <h2><?= $selectedCategory ?> - $<?= $selectedValue ?></h2>
            <p><?= $questionText ?></p>
            <form method="post">
                <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
                <input type="hidden" name="value" value="<?= htmlspecialchars($selectedValue) ?>">
                <label>Your Answer:</label>
                <input type="text" name="answer" required>
                <br>
                <button type="submit" name="submit_answer">Submit Answer</button>
            </form>
        </div>
    <?php else: ?>
        <div class="board">
            <?php foreach ($categories as $cat): ?>
                <div class="cell category"><?= htmlspecialchars($cat) ?></div>
            <?php endforeach; ?>

            <?php foreach ($values as $val): ?>
                <?php foreach ($categories as $cat): ?>
                    <?php $key = "$cat-$val"; ?>
                    <div class="cell">
                        <?php if (!in_array($key, $_SESSION['usedQuestions'])): ?>
                            <form method="post">
                                <input type="hidden" name="category" value="<?= htmlspecialchars($cat) ?>">
                                <input type="hidden" name="value" value="<?= htmlspecialchars($val) ?>">
                                <button type="submit">$<?= $val ?></button>
                            </form>
                        <?php else: ?>
                            <button disabled>Used</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </body>
</html>
