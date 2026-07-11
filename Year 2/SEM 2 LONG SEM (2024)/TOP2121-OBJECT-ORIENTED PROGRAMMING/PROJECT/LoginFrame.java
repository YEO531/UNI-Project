import javax.swing.*;
import java.awt.*;
//fixed repeated store bug
public class LoginFrame extends JFrame {
    private JTextField usernameField;
    private JPasswordField passwordField;
    private Authenticator authenticator;
    private final String AdminUsername="IamAdmin";
    private final String AdminPassword="WorkIsLife";
    public LoginFrame() {
        authenticator = new FileAuthenticator();
        
        setTitle("Login / Register");
        setSize(300, 200);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new GridLayout(3, 2));
        
        JLabel userLabel = new JLabel("Username:");
        usernameField = new JTextField();
        JLabel passLabel = new JLabel("Password:");
        passwordField = new JPasswordField();
        JButton loginButton = new JButton("Login");
        JButton registerButton = new JButton("Register");
        
        loginButton.addActionListener(e -> loginUser());
        registerButton.addActionListener(e -> registerUser());
        
        add(userLabel);
        add(usernameField);
        add(passLabel);
        add(passwordField);
        add(loginButton);
        add(registerButton);
        
        setVisible(true);
    }
    
    private void loginUser() {
        String username = usernameField.getText().trim();
        String password = new String(passwordField.getPassword()).trim();
        
        if (username.isEmpty() || password.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Fields cannot be empty!", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if (username.equals(AdminUsername) && password.equals(AdminPassword))
        {
            new AdminDashboardFrame();
            dispose();
        }
        else if (authenticator.authenticate(username, password)) {
            JOptionPane.showMessageDialog(this, "Login Successful!", "Success", JOptionPane.INFORMATION_MESSAGE);
            new DashboardFrame(username);
            dispose();
        }
         else {
            JOptionPane.showMessageDialog(this, "Invalid username or password!", "Error", JOptionPane.ERROR_MESSAGE);
        }
    }
    
    private void registerUser() {
        String username = usernameField.getText().trim();
        String password = new String(passwordField.getPassword()).trim();
        
        if (username.isEmpty() || password.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Fields cannot be empty!", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if (username.equals(AdminUsername)) {
            JOptionPane.showMessageDialog(this, "This Username is already been taken!", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
         if (authenticator.authenticate(username, password))
         {
            JOptionPane.showMessageDialog(this, "Successfully registered!", "Success", JOptionPane.INFORMATION_MESSAGE);
            return;
         }
        authenticator.register(username, password);
    }
} 
