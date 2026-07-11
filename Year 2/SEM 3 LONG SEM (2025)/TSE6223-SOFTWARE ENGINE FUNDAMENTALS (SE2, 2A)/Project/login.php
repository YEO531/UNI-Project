<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (login($_POST['email'], $_POST['password'])) {
        redirect('index.php');
    } else {
        setFlash('error','Invalid credentials');
    }
}
?>
<!DOCTYPE html><html><body>
<h1>Login</h1>
<?php if($msg=getFlash('error')) echo "<p>$msg</p>"; ?>
<form method="post">
    <input name="email" type="email" required placeholder="Email" />
    <input name="password" type="password" required placeholder="Password" />
    <button>Login</button>
</form>
<a href="register.php">Register</a>
</body></html>  