<?php
session_start();

function genererLabyrinthe($hauteur, $largeur) { //L'objéctif est de générer un matrice 2D représentant un labyrinthe
    $labyrinthe = [];

    // On commence par créer une grille ou chaque case est un mur '#' grace à une boucle for
    for ($y = 0; $y < $hauteur; $y++) {
        $labyrinthe[$y] = array_fill(0, $largeur, '#'); // remplit une ligne complète avec des # comme murs
    }

    // Pour la création du chemin
    $x = 0;
    $y = 0;
    $labyrinthe[$y][$x] = ' '; // Position de départ de mon Harry à partir de là des # se transforme en '' pour créer un chemin vide

    while ($x < $largeur - 1 || $y < $hauteur - 1) { //boucle pour créer le chemin
        //l'objectif de cette boucle est de former aléatoirement un chemin qui part du point de départ (0,0) pour atteindre le point d'arriver (8,8) en bas à droite
        $direction = rand(0, 1); //choisi aléatoirement pour avancer soit 0 pour horizontal soit 1 pour vertical

        if ($direction === 0 && $x < $largeur - 1) {
            $x++; // Avance à droite
        } elseif ($y < $hauteur - 1) {
            $y++; // Avance vers le bas
        }

        $labyrinthe[$y][$x] = ' ';
    }

    // Place la sortie ('S')
    $labyrinthe[$hauteur - 1][$largeur - 1] = 'S'; // empeche de sortir des limites de la grilles

    return $labyrinthe;
}

// Initialisation du jeu
if (!isset($_SESSION['labyrinthe'])) {
    $labyrinthe = genererLabyrinthe(9, 9); // Génére un labyrinthe de 9x9
    $_SESSION['labyrinthe'] = $labyrinthe;
    $_SESSION['chat'] = ['x' => 0, 'y' => 0];
    $_SESSION['victoire'] = false; // aucune victoire enregistré pour le moment
}

// Bouton restart
if (!isset($_SESSION['restart-btn'])) {
    $labyrinthe = genererLabyrinthe(9,9);
    $_SESSION['chat'] = ['x' => 0, 'y' => 0];
    $_SESSION['victoire'] = false;
}

$labyrinthe = $_SESSION['labyrinthe'];
$chat = $_SESSION['chat'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restart'])) {
        $_SESSION['labyrinthe'] = genererLabyrinthe(9, 9);
        $_SESSION['chat'] = ['x' => 0, 'y' => 0];
        $_SESSION['victoire'] = false;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (!$_SESSION['victoire']) {
        if (isset($_POST['haut']) && $chat['y'] > 0 && $labyrinthe[$chat['y'] - 1][$chat['x']] !== '#') { //vérifie que la case suivante n'est pas un mur, qu'Harry ne se deplace pas hors du labyrinthe, et que si la case au dessu n'est pas un mur alors il peut y aller
            $chat['y']--;
        }
        if (isset($_POST['bas']) && $chat['y'] < count($labyrinthe) - 1 && $labyrinthe[$chat['y'] + 1][$chat['x']] !== '#') {
            $chat['y']++;
        }
        if (isset($_POST['gauche']) && $chat['x'] > 0 && $labyrinthe[$chat['y']][$chat['x'] - 1] !== '#') {
            $chat['x']--;
        }
        if (isset($_POST['droite']) && $chat['x'] < count($labyrinthe[$chat['y']]) - 1 && $labyrinthe[$chat['y']][$chat['x'] + 1] !== '#') {
            $chat['x']++;
        }
    }

    $_SESSION['chat'] = $chat;

    if ($labyrinthe[$chat['y']][$chat['x']] === 'S') {
        $_SESSION['victoire'] = true; //victoire enregistré
    }
}

function afficherLabyrinthe($labyrinthe, $chat) {
    $html = '<table style="border-collapse: collapse;">';
    for ($y = 0; $y < count($labyrinthe); $y++) { // boucle qui partcour chaque lignes du labyrinthe de haut en bas
        $html .= '<tr>';
        for ($x = 0; $x < count($labyrinthe[$y]); $x++) {
            $distance = abs($chat['x'] - $x) + abs($chat['y'] - $y); // on soustrait $chat à x ou y pour obtenir la différence vertical (x) et horizontale (y)
            
            
            if ($distance <= 1) { // rend visible les céllules à une distance de 1 case
                if ($x == $chat['x'] && $y == $chat['y']) {
                    $html .= '<td style="border: none; padding:20px; text-align: center;">
                                <img src="assets/image/pngtree-harry-potter-holding-a-book-and-wand-ready-for-magic-adventures-png-image_12524515.png" alt="Harry Potter" style="width: 80px; height: 80px;">
                              </td>';
                } elseif ($labyrinthe[$y][$x] === 'S') {
                    $html .= '<td style="border: none; padding: 20px; text-align: center;">
                                <img src="assets/image/pngtree-goblet-of-fire-clip-art-magical-cup-png-image_11497704.png" alt="Sortie" style="width: 80px; height: 80px;">
                              </td>';
                } elseif ($labyrinthe[$y][$x] === '#') {
                    $html .= '<td style="border: none;  text-align: center;">
                                <img src="assets/image/file (1).png" alt="Mur" class="mur" style="width: 80px; height: 80px;">
                              </td>';
                } else {
                    $html .= '<td style="border: none; padding: 20px; background-color: #132513;"></td>';
                }
            } else { // si loin de 1 case, brouillard
                $html .= '<td class="brouillard" style="background-color: #171014;" width: 80px; height: 80px;></td>';
            }
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Labyrinthe</title>
</head>
<body>
    <h1>THE MAZE</h1>

    <?php if ($_SESSION['victoire']): ?>
        <div class="overlay">
            <div class="overlay-content">
                <p class="win-msg">Harry Potter, the boy who lived, come to die...</p>
                <img class="win" src="assets/image/56d557bba34e23111bfa5561069cbad2.jpg" alt="">  
                <form method="post">
                    <button type="submit" name="restart">Play Again</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
        <div class="maze-container"><?= afficherLabyrinthe($labyrinthe, $chat) ?></div>
        <form method="post" action="">
            <button type="submit" name="haut"><ion-icon name="chevron-up-outline"></ion-icon></ion-icon></button>
            <div class="middle">
            <button type="submit" name="gauche"><ion-icon name="chevron-back-outline"></ion-icon></ion-icon></button>
            <button type="submit" name="droite"><ion-icon name="chevron-forward-outline"></ion-icon></button>
            </div>
            
            <button type="submit" name="bas"><ion-icon name="chevron-down-outline"></ion-icon></button>
        </form>
        </div>

    
        

    <?php endif; ?>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>

