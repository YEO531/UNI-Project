<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $ok = register(
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['password']
    );
    if ($ok) {
        setFlash('success','Registration successful, please login.');
        redirect('login.php');
    } else {
        setFlash('error','Registration failed.');
    }
}
?>
<!DOCTYPE html><html><body>
<h1>Register</h1>
<?php if($msg=getFlash('error')) echo "<p>$msg</p>"; ?>
<form method="post">
    <input name="name" required placeholder="Name" />
    <input name="email" type="email" required placeholder="Email" />
    <input name="phone" placeholder="Phone" />
    <input name="password" type="password" required placeholder="Password" />
    <button>Register</button>
</form>
<a href="login.php">Login</a>
</body></html>