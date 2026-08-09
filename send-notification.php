<?php
require_once 'includes/mailer.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $recipientName  = htmlspecialchars($_POST['name'] ?? 'User');
    $certTitle      = htmlspecialchars($_POST['cert_title'] ?? 'Certificate');

    if ($recipientEmail) {
        $subject = "Your Certificate for {$certTitle} is Ready!";
        $body    = "
            <h2>Hello, {$recipientName}!</h2>
            <p>Your certificate for <strong>{$certTitle}</strong> has been issued on CertiChain.</p>
            <p><a href='http://localhost/certichain/verify.php'>Click here to view your certificate</a>.</p>
            <br>
            <p>Best regards,<br>The CertiChain Team</p>
        ";

      
        $result = sendEmail($recipientEmail, $recipientName, $subject, $body);

        if ($result === true) {
            $message = "<div style='color: green;'>Email sent successfully to {$recipientEmail}!</div>";
        } else {
            $message = "<div style='color: red;'>Failed to send: {$result}</div>";
        }
    } else {
        $message = "<div style='color: red;'>Please provide a valid email address.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CertiChain - Send Notification</title>
</head>
<body>
    <h2>Send Certificate Notification</h2>
    <?= $message; ?>

    <form method="POST" action="">
        <div>
            <label>Recipient Name:</label><br>
            <input type="text" name="name" required>
        </div>
        <br>
        <div>
            <label>Recipient Email:</label><br>
            <input type="email" name="email" required>
        </div>
        <br>
        <div>
            <label>Certificate Title:</label><br>
            <input type="text" name="cert_title" required>
        </div>
        <br>
        <button type="submit">Send Email</button>
    </form>
</body>
</html>