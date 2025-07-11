<?php
session_start();

$categories = ["Anime", "Popular Video Games", "Computer Science", "Marvel", "Star Wars"];
$values = [100, 200, 300, 400, 500];
$questions = [
    "Anime" => [
        100 => "What is the name of the ninja who dreams of becoming Hokage?", //Naruto Uzumaki
        200 => "In Dragon Ball Z, who is Goku's main rival and frenemy?", //Vegeta
        300 => "In Attack on Titan, what are the giant creatures that humanity fights called?", //Titans
        400 => "What Studio Ghibli film features a giant cat bus and a forest spirit?", //My Neighbor Totoro
        500 => "In Death Note, what is the full name of the high school student who finds the notebook?" //Light Yagami
    ],
    "Popular Video Games" => [
        100 => "What game features a battle royale on an island with 100 players and building mechanics?", //Fortnite
        200 => "In Minecraft, what do you need to mine diamonds?", //An Iron Pickaxe
        300 => "What 2020 game about a crewmate vs imposters became a viral hit during the pandemic?", //Among Us
        400 => "In The Legend of Zelda series, what is the name of the main playable character?", //Link
        500 => "What critically acclaimed RPG features a time-looping cycle in the underworld and is based on Greek mythology?" //Hades
    ],
    "Computer Science" => [
        100 => "What does 'CPU' stand for in computer terminology?", //Central Processing Unit
        200 => "Which number system do computers use t store data - decimal or binary?", //Binary
        300 => "What is the name of the structure where each element points to the next one in memory?", //Linked List
        400 => "What kind of loop continues running until a certain condition is false?", //While loop
        500 => "What's the term for a function that calls itself within its own definition?" //Recursion
    ],
    "Marvel" => [
        100 => "What billionaire superhero leads the Avengers and wears a metal suit?", //Iron Man (Tony Stark)
        200 => "What is the name of Thor's enchanted hammer?", //Mjolnir
        300 => "What Infinity Stone does Vision have in his forehead?", //Mind Stone
        400 =>"Who is T'Challa better known as?", //Black Panther
        500 => "What is the real name of the Scarlet Witch?" //Wanda Maximoff
    ],
    "Star Wars" => [
        100 => "Who was Anakin Skywalker's Jedi Master?", //Obi-Wan Kenobi
        200 => "What is the name of Han Solo's ship?", //Millennium Falcon
        300 => "Which small, green Jedi Master famously says, 'Do or do not. There is no try'?", //Yoda
        400 => "What is the name of the desert planet where Rey is introduced in The Force Awakens?", //Jakku
        500 => "What clone trooper order causes the Jedi Purge in Revenge of the Sith?" //Order 66
    ]
];

// Handle form submission
$selectedCategory = $_POST['category'] ?? null;
$selectedValue = $_POST['value'] ?? null;
$showQuestion = false;

if ($selectedCategory && $selectedValue) {
    $showQuestion = true;
    $questionText = $questions[$selectedCategory][$selectedValue] ?? "No question found.";
}
?>

<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width , initial-scale=1.0">
        <meta name="description" content="This is a gamepage for jeopardy!">
        <title>Jeopardy - The Game</title>
        <link rel="stylesheet" href="style.css">
    </head>
<body>

<h1>Jeopardy</h1>

<?php if ($showQuestion): ?>
    <div class="question-box">
        <h2>Category: <?= htmlspecialchars($selectedCategory) ?> - $<?= htmlspecialchars($selectedValue) ?></h2>
        <p><?= htmlspecialchars($questionText) ?></p>
        <form method="post">
            <button type="submit">Back to Board</button>
        </form>
    </div>
<?php else: ?>
    <div class="board">
        <!-- Category Headers -->
        <?php foreach ($categories as $cat): ?>
            <div class="cell category"><?= htmlspecialchars($cat) ?></div>
        <?php endforeach; ?>

        <!-- Question Cells -->
        <?php foreach ($values as $val): ?>
            <?php foreach ($categories as $cat): ?>
                <div class="cell">
                    <form method="post">
                        <input type="hidden" name="category" value="<?= htmlspecialchars($cat) ?>">
                        <input type="hidden" name="value" value="<?= htmlspecialchars($val) ?>">
                        <button type="submit">$<?= $val ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
