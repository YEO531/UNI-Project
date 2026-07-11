import javax.swing.*;
import java.awt.*;
import javafx.application.Platform;
import javafx.embed.swing.JFXPanel;
import java.util.Stack;

public class AdminDashboardFrame extends JFrame {
    private DataHandling dataHandling;
    private Stack<String> UserName;

    AdminDashboardFrame() {
        dataHandling = new FileDataHandling();
        UserName = new Stack<>();

        setTitle("Admin Dashboard - User Search");
        setSize(400, 300);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new GridBagLayout());

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(5, 5, 5, 5);

        JLabel welcomeLabel = new JLabel("Please select an ID you want to search for", SwingConstants.CENTER);
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.gridwidth = 2;
        gbc.fill = GridBagConstraints.HORIZONTAL;
        add(welcomeLabel, gbc);

        dataHandling.getUsername(UserName);
        Choice c = new Choice();
        if (UserName.isEmpty()) {
            c.add("No users found");
        } else {
            while (!UserName.isEmpty()) {
                String temp = UserName.pop();
                c.add(temp);
            }
        }

        gbc.gridx = 0;
        gbc.gridy = 1;
        gbc.gridwidth = 1;
        gbc.weightx = 0.7;
        gbc.fill = GridBagConstraints.HORIZONTAL;
        add(c, gbc);

        JButton SearchButton = new JButton("Search");
        gbc.gridx = 1;
        gbc.gridy = 1;
        gbc.weightx = 0.3;
        SearchButton.addActionListener(e -> {
            String selectUsername = c.getSelectedItem();
            if (selectUsername != null && !selectUsername.equals("No users found")) {
                String userInfo = dataHandling.getUsernameInfo(selectUsername);
                JOptionPane.showMessageDialog(this, userInfo, "User Info", JOptionPane.INFORMATION_MESSAGE);
            }
        });
        add(SearchButton, gbc);

        JButton logoutButton = new JButton("Logout");
        logoutButton.addActionListener(e -> logout());
        gbc.gridx = 0;
        gbc.gridy = 2;
        gbc.gridwidth = 2;
        gbc.weightx = 1.0;
        gbc.fill = GridBagConstraints.NONE;
        gbc.anchor = GridBagConstraints.CENTER;
        add(logoutButton, gbc);

        setLocationRelativeTo(null);
        setVisible(true);
    }

    private void logout() {
        dispose();
        new LoginFrame();
    }
}