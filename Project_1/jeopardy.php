<?php
session_start();

// Reset game
if (isset($_POST['reset'])) {
    session_destroy();
    header("Location: jeopardy.php");
    exit;
}

// Load questions and extract unique categories
function loadQuestionsFromFile($filename) {
    $pool = [];
    $allCategories = [];

    if (!file_exists($filename)) return [[], []];

    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($category, $value, $question, $answer) = explode('|', $line);
        $category = trim($category);
        $value = intval($value);
        $question = trim($question);
        $answer = trim($answer);

        $pool[] = [
            'category' => $category,
            'value' => $value,
            'question' => $question,
            'answer' => $answer
        ];

        $allCategories[$category] = true;
    }

    shuffle($pool);
    return [$pool, array_keys($allCategories)];
}

list($questionPool, $allCategories) = loadQuestionsFromFile("questions.txt");

// Randomly select 5 categories on new game
if (!isset($_SESSION['selectedCategories'])) {
    shuffle($allCategories);
    $_SESSION['selectedCategories'] = array_slice($allCategories, 0, 5);
}
$selectedCategories = $_SESSION['selectedCategories'];
$values = [100, 200, 300, 400, 500];

// Build board
$board = [];
$answers = [];
foreach ($questionPool as $item) {
    $cat = $item['category'];
    $val = $item['value'];
    if (in_array($cat, $selectedCategories)) {
        if (!isset($board[$cat][$val])) {
            $board[$cat][$val] = $item['question'];
            $answers[$cat][$val] = $item['answer'];
        }
    }
}

// Initialize players and game state
if (!isset($_SESSION['players'])) {
    $_SESSION['players'] = [
        'Player 1' => 0,
        'Player 2' => 0
    ];
    $_SESSION['currentPlayer'] = 'Player 1';
    $_SESSION['usedQuestions'] = [];
    $_SESSION['questions'] = $board;
    $_SESSION['answers'] = $answers;
}

$selectedCategory = $_POST['category'] ?? null;
$selectedValue = $_POST['value'] ?? null;
$showQuestion = false;
$questionText = '';
$questionKey = "$selectedCategory-$selectedValue";

// Show question
if ($selectedCategory && $selectedValue && !isset($_POST['submit_answer'])) {
    $showQuestion = true;
    $questionText = $_SESSION['questions'][$selectedCategory][$selectedValue] ?? "No question found.";
}

// Handle answer submission
if (isset($_POST['submit_answer'])) {
    $category = $_POST['category'];
    $value = $_POST['value'];
    $answer = trim($_POST['answer']);
    $correct = $_SESSION['answers'][$category][$value] ?? '';

    if (strcasecmp($answer, $correct) == 0) {
        $_SESSION['players'][$_SESSION['currentPlayer']] += $value;
        $resultMessage = "Correct! +$value points.";
    } else {
        $_SESSION['players'][$_SESSION['currentPlayer']] -= $value;
        $resultMessage = "Incorrect! -$value points. Correct answer: $correct";
    }

    $_SESSION['usedQuestions'][] = "$category-$value";

    // Export leaderboard
    $html = "<!DOCTYPE html>\n<html>\n<head><title>Leaderboard</title></head>\n<body>\n<h1>Jeopardy Leaderboard</h1>\n<ul>\n";
    foreach ($_SESSION['players'] as $player => $score) {
        $html .= "<li><strong>" . htmlspecialchars($player) . ":</strong> " . intval($score) . " points</li>\n";
    }
    $html .= "</ul>\n</body>\n</html>";
    file_put_contents("leaderboard.html", $html);

    // Switch player
    $_SESSION['currentPlayer'] = $_SESSION['currentPlayer'] === 'Player 1' ? 'Player 2' : 'Player 1';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width , initial-scale=1.0">
    <meta name="description" content="This is a game of jeopardy!">
    <title>Jeopardy</title>
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
        <h2><?= htmlspecialchars($selectedCategory) ?> - $<?= $selectedValue ?></h2>
        <p><?= $questionText ?></p>
        <form method="post">
            <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
            <input type="hidden" name="value" value="<?= htmlspecialchars($selectedValue) ?>">
            <label>Your Answer:</label>
            <input type="text" name="answer" required>
            <br><br>
            <button type="submit" name="submit_answer">Submit Answer</button>
        </form>
    </div>
<?php else: ?>
    <div class="board">
        <?php foreach ($selectedCategories as $cat): ?>
            <div class="cell category"><?= htmlspecialchars($cat) ?></div>
        <?php endforeach; ?>

        <?php foreach ($values as $val): ?>
            <?php foreach ($selectedCategories as $cat): ?>
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
