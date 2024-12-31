<?php
session_start();
$host = 'localhost';
$user = 'root';
$pass = 'fotbal';
$db = 'fotbal';

// Conectarea la baza de date
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Eroare la conectare: " . $conn->connect_error);
}

// Înregistrare utilizator nou
if (isset($_POST['register'])) {
    $nume = $_POST['nume'];
    $email = $_POST['email'];
    $parola = password_hash($_POST['parola'], PASSWORD_DEFAULT);

    // Verificare dacă email-ul există deja
    $stmt = $conn->prepare("SELECT * FROM utilizatori WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color:#FFD700;'>Email-ul este deja folosit!</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO utilizatori (nume, email, parola) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nume, $email, $parola);
        $stmt->execute();
        echo "<p style='color:#FFD700;'>Înregistrare cu succes!</p>";
    }
}

// Autentificare utilizator
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $parola = $_POST['parola'];

    // Verificare utilizator
    $stmt = $conn->prepare("SELECT * FROM utilizatori WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($parola, $user['parola'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nume'] = $user['nume'];
            header("Location: i2.php");
            exit();
        } else {
            echo "<p style='color:#FFD700;'>Parola incorectă!</p>";
        }
    } else {
        echo "<p style='color:#FFD700;'>Utilizatorul nu există!</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare / Autentificare</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    
        }

        body {
        
            background: linear-gradient(to right,rgb(15, 134, 41),rgb(38, 85, 138)); 
            height: fit-content;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 100vh; 
            margin: 0;
            
        }

        h1, h2 {
            color: #D3D3D3;
            
        }

        form {
            background-color: rgba(219, 219, 219, 0.8); /* Fundal semi-transparent pentru form */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 350px; 
            
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 300px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px 20px;
            border: none;
            background-color:rgb(15, 134, 41);
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 2s ease;
        }

        button:hover {
            background-color:rgb(38, 85, 138);
        }

        hr {
            margin: 20px 0;
        }
    </style>

</head>
<body>
    <h1>Înregistrare / Autentificare</h1><br>

    <!-- Formularul de înregistrare -->
    <h2>Înregistrare</h2><br>
    <form method="POST" action="register.php">
        <input type="text" name="nume" placeholder="Nume" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="parola" placeholder="Parola" required><br><br>
        <button type="submit" name="register">Înregistrează-te</button>
    </form>

   <br>

    <!-- Formularul de autentificare -->
    <h2>Autentificare</h2><br>
    <form method="POST" action="register.php">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="parola" placeholder="Parola" required><br><br>
        <button type="submit" name="login">Autentifică-te</button>
    </form>
</body>
</html>
