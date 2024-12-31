<?php
session_start();

// Dacă nu ești autentificat, te redirecționează la formularul de autentificare
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = 'fotbal';
$db = 'fotbal';

// Conectarea la baza de date
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Eroare la conectare: " . $conn->connect_error);
}

// Adăugarea unui jucător
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_player'])) {
    $nume = $_POST['nume'];
    $pozitie = $_POST['pozitie'];
    $varsta = $_POST['varsta'];
    $numar = $_POST['numar'];
    $cota_piata = $_POST['cota_piata'];

    // Verifică dacă numărul există deja
    $stmt = $conn->prepare("SELECT COUNT(*) FROM jucatori WHERE numar = ?");
    $stmt->bind_param("i", $numar);
    $stmt->execute();
    $stmt->bind_result($exists);
    $stmt->fetch();
    $stmt->close();

    if ($exists > 0) {
        echo "<script>alert('Numărul $numar este deja atribuit unui alt jucător!');</script>";
    }
     elseif (!empty($nume) && !empty($pozitie) && !empty($varsta) && !empty($numar) && !empty($cota_piata)) {
        $stmt = $conn->prepare("INSERT INTO jucatori (nume, pozitie, varsta, numar, cota_piata) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiid", $nume, $pozitie, $varsta, $numar, $cota_piata);
        $stmt->execute();
        $stmt->close();
        header("Location: i2.php");
        exit();
    } else {
        echo "<p style='color:red;'>Toate câmpurile sunt obligatorii!</p>";
    }
}


// Ștergerea unui jucător
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM jucatori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Reordonarea ID-urilor
    $result = $conn->query("SELECT id FROM jucatori ORDER BY id ASC");
    $index = 1;
    while ($row = $result->fetch_assoc()) {
        $id_jucator = $row['id'];
        // Actualizează ID-ul fiecărui jucător pentru a elimina gap-ul
        $stmt = $conn->prepare("UPDATE jucatori SET id = ? WHERE id = ?");
        $stmt->bind_param("ii", $index, $id_jucator);
        $stmt->execute();
        $stmt->close();
        $index++;
    }

    // Resetează autoincrementul pentru a reflecta ordonarea corectă a ID-urilor
    $conn->query("ALTER TABLE jucatori AUTO_INCREMENT = 1");

    // Redirectează înapoi la pagina principală
    header("Location: i2.php");
}



// Preluarea tuturor jucătorilor
$jucatori = $conn->query("SELECT * FROM jucatori");

// Preluarea unui jucător pentru editare
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM jucatori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jucator = $result->fetch_assoc();
    $stmt->close();
}

// Actualizarea unui jucător
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_player'])) {
    $id = $_POST['id'];
    $nume = $_POST['nume'];
    $pozitie = $_POST['pozitie'];
    $varsta = $_POST['varsta'];
    $numar = $_POST['numar'];
    $cota_piata = $_POST['cota_piata'];

    // Verifică dacă numărul există deja la alt jucător
    $stmt = $conn->prepare("SELECT COUNT(*) FROM jucatori WHERE numar = ? AND id != ?");
    $stmt->bind_param("ii", $numar, $id);
    $stmt->execute();
    $stmt->bind_result($exists);
    $stmt->fetch();
    $stmt->close();

    if ($exists > 0) {
        echo "<script>alert('Numărul $numar este deja atribuit unui alt jucător!');</script>";
    }
     else {
        $stmt = $conn->prepare("UPDATE jucatori SET nume = ?, pozitie = ?, varsta = ?, numar = ?, cota_piata = ? WHERE id = ?");
        $stmt->bind_param("ssiidi", $nume, $pozitie, $varsta, $numar, $cota_piata, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: i2.php");
        exit();
    }
}


$v_m = $conn->query("SELECT AVG(varsta) AS varsta_medie FROM jucatori");
$varsta_medie = 0;
if ($v_m) {
    $row = $v_m->fetch_assoc();
    $varsta_medie = round($row['varsta_medie'], 2); 
}
$v_l = $conn->query("SELECT SUM(cota_piata) AS valoare_lot FROM jucatori");
$valoare_lot = 0;
if ($v_l) {
    $row = $v_l->fetch_assoc();
    $valoare_lot = number_format($row['valoare_lot'], 2);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echipa de Fotbal</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid white;
        }

        th, td {
            padding: 10px;
            text-align: left;
            color: white;
        }
        h1, h2{
            color: white;
        }
        form {
            margin-bottom: 20px;
        }
        .logout-form {
            position: fixed; 
            top: 20px; 
            right: 20px; 
        }
        body {
            background-image: url('ftb.jpg');
            background-size: cover; 
            background-position: center; 
            background-attachment: fixed; 
        }
        a {
            color: lightgrey;
            text-decoration: none; 
        }

        a:hover {
            color: lightblue; 
        }

    </style>
</head>
<body >
    <h1>Bine ai venit, <?= $_SESSION['nume'] ?>!</h1>

    <!-- Buton de deconectare -->
    <form method="POST" action="logout.php" class="logout-form">
        <button type="submit">Deconectează-te</button>
    </form>

    <h1>Echipa de Fotbal</h1>

    <form method="POST" action="i2.php">
        <input type="text" name="nume" placeholder="Nume jucator" required>
        <input type="text" name="pozitie" placeholder="Pozitie" required>
        <input type="number" name="numar" placeholder="Numar (1-99)" required>
        <input type="number" name="varsta" placeholder="Varsta" required>
        <input type="number" name="cota_piata" placeholder="Cota de piață (mil. €)" required>
        <button type="submit" name="add_player">Adauga Jucator</button>
    </form>

        <!-- Formular pentru editarea unui jucător -->
    <?php if (isset($jucator)): ?>
        <h2>Modifică Jucătorul</h2>
        <form method="POST" action="i2.php">
            <input type="hidden" name="id" value="<?= $jucator['id'] ?>">
            <input type="text" name="nume" value="<?= $jucator['nume'] ?>" required>
            <input type="text" name="pozitie" value="<?= $jucator['pozitie'] ?>" required>
            <input type="number" name="numar" value="<?= $jucator['numar'] ?>" required>
            <input type="number" name="varsta" value="<?= $jucator['varsta'] ?>" required>
            <input type="number" name="cota_piata" value="<?= $jucator['cota_piata'] ?>" required>
            <button type="submit" name="update_player">Actualizează</button>
        </form>
    <?php endif; ?>


    <h2>Lista Jucătorilor</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nume</th>
            <th>Poziție</th>
            <th>Vârstă</th>
            <th>Numar</th>
            <th>Cota de piață (mil. €)</th>
            <th>Acțiuni</th>
        </tr>
        <?php while ($row = $jucatori->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nume'] ?></td>
                <td><?= $row['pozitie'] ?></td>
                <td><?= $row['varsta'] ?></td>
                <td><?= $row['numar'] ?></td>
                <td><?= number_format($row['cota_piata'], 2) ?></td>
                <td>
                    <a href="i2.php?edit=<?= $row['id'] ?>">Modifică | </a>
                    <a href="i2.php?delete=<?= $row['id'] ?>" onclick="return confirm('Esti sigur ca vrei sa stergi acest jucator?')">Sterge</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <h2>Statistici echipă</h2>
    <p style="color: white;">Vârsta medie a echipei: <strong><?= $varsta_medie ?></strong></p>
    <p style="color: white;">Valoarea totală a lotului: <strong><?= $valoare_lot ?> mil. €</strong></p>

</body>
</html>
