import javax.swing.*;
import java.awt.*;

public class HealthTipsFrame extends JFrame {
    private JTextArea textArea;

    public HealthTipsFrame() {
        initialize();
    }

    private void initialize() {
        setTitle("Health Tips System");
        setSize(600, 400);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
        setLayout(new BorderLayout(10, 10));

        // Button panel
        JPanel buttonPanel = new JPanel(new GridLayout(1, 4, 10, 0));
        String[] categories = {"Underweight", "Normal Weight", "Overweight", "Obese"};
        
        for (String category : categories) {
            JButton btn = new JButton(category);
            btn.addActionListener(e -> showTips(category));
            buttonPanel.add(btn);
        }

        // Text area with scrolling
        textArea = new JTextArea();
        textArea.setEditable(false);
        textArea.setLineWrap(true);
        textArea.setWrapStyleWord(true);
        JScrollPane scrollPane = new JScrollPane(textArea);

        // Exit button
        JButton exitButton = new JButton("Exit");
        exitButton.addActionListener(e -> dispose());

        // Add components to frame
        add(buttonPanel, BorderLayout.NORTH);
        add(scrollPane, BorderLayout.CENTER);
        add(exitButton, BorderLayout.SOUTH);

        setLocationRelativeTo(null); // Center on screen
    }

    private void showTips(String category) {
        String tips;
        switch (category) {
            case "Underweight":
                tips = "If you are underweight:\n\n" +
                       "-> Eat 5-6 smaller meals daily\n" +
                       "-> Choose nutrient-rich foods\n" +
                       "-> Strength training builds muscle";
                break;
            case "Normal Weight":
                tips = "If you have a normal weight:\n\n" +
                       "-> Maintain 7-9 hours of sleep\n" +
                       "-> Eat balanced meals\n" +
                       "-> Stay hydrated";
                break;
            case "Overweight":
                tips = "If you are overweight:\n\n" +
                       "-> 150 mins weekly exercise\n" +
                       "-> Drink water instead of soda\n" +
                       "-> Monitor portion sizes";
                break;
            case "Obese":
                tips = "If you are obese:\n\n" +
                       "-> Consult a healthcare professional\n" +
                       "-> Follow a calorie-controlled diet\n" +
                       "-> Regular physical activity";
                break;
            default:
                tips = "No tips available.";
        }

        textArea.setText(tips);
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> new HealthTipsFrame().setVisible(true));
    }
}
