import javax.swing.*;
import java.awt.*;
import javafx.application.Platform;
import javafx.embed.swing.JFXPanel;

public class DashboardFrame extends JFrame {
    private UserData userData;
    private UserData tempuserData;
    private UserData ProgressuserData;
    private DataHandling dataHandling;
    private StackForRecord stackForRecord;
    private BMIProgression BmiProgression;

    public DashboardFrame(String username) {
        userData = new UserData(username);
        tempuserData = new UserData(username);
        ProgressuserData = new UserData(username);
        dataHandling = new FileDataHandling();
        stackForRecord = new StackForRecord();

        setTitle("Dashboard - Welcome " + username);
        setSize(400, 300);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new GridLayout(5, 1));
        
        JLabel welcomeLabel = new JLabel("Welcome, " + username + "!", SwingConstants.CENTER);
        JButton bmiButton = new JButton("Calculate BMI");
        JButton dietButton = new JButton("View Diet Recommendations");
        JButton healthTipsButton = new JButton("Health Tips");
        JButton SetTargetButton = new JButton("Set Target");
        JButton UpdateTargetButton = new JButton("Update Target");
        JButton ProgressionButton = new JButton("Progression");
        JButton logoutButton = new JButton("Logout"); 
        
        bmiButton.addActionListener(e -> calculateBMI());
        dietButton.addActionListener(e -> showDietRecommendations());
        healthTipsButton.addActionListener(e -> showHealthTips());
        SetTargetButton.addActionListener(e -> SetTarget());
        UpdateTargetButton.addActionListener(e -> UpdateTarget());
        ProgressionButton.addActionListener(e -> Progression());
        logoutButton.addActionListener(e -> logout());
        
        add(welcomeLabel);
        add(bmiButton);
        add(dietButton);
        add(healthTipsButton);
        add(SetTargetButton);
        add(UpdateTargetButton);
        add(ProgressionButton);
        add(logoutButton);
        
        setLocationRelativeTo(null);
        setVisible(true);
    }

    private void calculateBMI() {
        try {
            double weight;
            double height;
            do {
                weight = Double.parseDouble(JOptionPane.showInputDialog(this, "Enter your weight (kg):"));
                height = Double.parseDouble(JOptionPane.showInputDialog(this, "Enter your height (m):"));
                if (weight <= 0) {
                    JOptionPane.showMessageDialog(this, "Invalid input! Please enter a positive weight value.", 
                        "Warning", JOptionPane.WARNING_MESSAGE);
                }
                if (height <= 0) {
                    JOptionPane.showMessageDialog(this, "Invalid input! Please enter a positive height value.", 
                        "Warning", JOptionPane.WARNING_MESSAGE);
                }
            } while (weight <= 0 || height <= 0);
            
            userData.updateMeasurements(weight, height);
            
            String message = String.format("Your BMI: %.2f\nCategory: %s", 
                userData.getBmi(), userData.getBmiCategory());
            JOptionPane.showMessageDialog(this, message, "BMI Result", JOptionPane.INFORMATION_MESSAGE);
            
            showDietRecommendations();
        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Invalid input! Please enter numeric values.", 
                "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void showDietRecommendations() {
        if (userData.getBmi() == 0) {
            JOptionPane.showMessageDialog(this, "Please calculate your BMI first!");
            return;
        }

        DietPlan[] recommendations = DietRecommendations.getRecommendationsForBMI(userData.getBmi());
        
        JPanel recommendationsPanel = new JPanel(new GridLayout(recommendations.length, 1));
        ButtonGroup group = new ButtonGroup();
        
        for (DietPlan plan : recommendations) {
            JRadioButton radio = new JRadioButton(plan.getName());
            radio.setActionCommand(plan.getName());
            group.add(radio);
            
            radio.addActionListener(e -> {
                userData.setSelectedDietPlan(plan.getName());
                StringBuilder details = new StringBuilder();
                details.append(plan.getDescription()).append("\n\nMeal Plan:\n");
                for (String meal : plan.getMeals()) {
                    details.append("- ").append(meal).append("\n");
                }
                JOptionPane.showMessageDialog(this, details.toString(), 
                    plan.getName(), JOptionPane.INFORMATION_MESSAGE);
            });
            
            recommendationsPanel.add(radio);
        }

        JOptionPane.showMessageDialog(this, recommendationsPanel, 
            "Diet Recommendations", JOptionPane.PLAIN_MESSAGE);
    }

    private void showHealthTips() {
        new HealthTipsFrame().setVisible(true);
    }

    private void SetTarget() {
        double TempWeight;
        double TempHeight;
        if (userData.getBmi() == 0) {
            JOptionPane.showMessageDialog(this, "Please calculate your BMI first!");
            return;
        }
        if (ProgressuserData.getBmi() != 0) {
            JOptionPane.showMessageDialog(this, "Please complete your progression first before setting target!");
            return;
        }
        try {
            do {
                TempWeight = Double.parseDouble(JOptionPane.showInputDialog(this, "Enter your desired weight (kg):"));
                TempHeight = Double.parseDouble(JOptionPane.showInputDialog(this, "Enter your desired height (m):"));
                if (TempWeight <= 0) {
                    JOptionPane.showMessageDialog(this, "Invalid input! Please enter a positive weight value.", 
                        "Warning", JOptionPane.WARNING_MESSAGE);
                }
                if (TempHeight <= 0) {
                    JOptionPane.showMessageDialog(this, "Invalid input! Please enter a positive height value.", 
                        "Warning", JOptionPane.WARNING_MESSAGE);
                }
            } while (TempHeight <= 0 || TempWeight <= 0);
            
            tempuserData.updateMeasurements(TempWeight, TempHeight);
            double temp_bmi = tempuserData.getBmi();
            stackForRecord.pushHere(temp_bmi);
            dataHandling.stored(userData.getUsername(), userData.getWeight(), userData.getHeight(), userData.getBmi(), temp_bmi, 0);
            String message = String.format("This is your targeted BMI: "+temp_bmi);
            JOptionPane.showMessageDialog(this, message, "BMI Result", JOptionPane.INFORMATION_MESSAGE);
        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Invalid input! Please enter numeric values.", 
                "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void UpdateTarget() {
        double TempWeight, TempHeight;
        if (userData.getBmi() == 0) {
            JOptionPane.showMessageDialog(this, "Please calculate your BMI first!");
            return;
        }
        if (tempuserData.getBmi() == 0) {
            JOptionPane.showMessageDialog(this, "Please set your target BMI first!");
            return;
        }
        try {
            do {
                TempWeight = Double.parseDouble(JOptionPane.showInputDialog(this, "Enter your updated weight (kg):"));
                TempHeight = Double.parseDouble(JOptionPane.showInputDialog(this, "Enter your updated height (m):"));
                if (TempWeight <= 0) {
                    JOptionPane.showMessageDialog(this, "Invalid input! Please enter a positive weight value.", 
                        "Warning", JOptionPane.WARNING_MESSAGE);
                }
                if (TempHeight <= 0) {
                    JOptionPane.showMessageDialog(this, "Invalid input! Please enter a positive height value.", 
                        "Warning", JOptionPane.WARNING_MESSAGE);
                }
            } while (TempHeight <= 0 || TempWeight <= 0);
            
            ProgressuserData.updateMeasurements(TempWeight, TempHeight);
            double progress_bmi = ProgressuserData.getBmi();
            stackForRecord.pushHere(progress_bmi);
            double temp_bmi = tempuserData.getBmi();
            double bmi = userData.getBmi();
            double progress = Math.round(100 * (progress_bmi - bmi) / (temp_bmi - bmi)) / 100.0;
            dataHandling.stored(userData.getUsername(), TempWeight, TempHeight, progress_bmi, temp_bmi, progress);
            String message = String.format("This is your current BMI: "+progress_bmi);
            JOptionPane.showMessageDialog(this, message, "BMI Result", JOptionPane.INFORMATION_MESSAGE);
            String message1 = String.format("This is your current Progression: %.2f%%",(100*(progress_bmi-bmi)/(temp_bmi-bmi)));
            JOptionPane.showMessageDialog(this, message1, "BMI Result", JOptionPane.INFORMATION_MESSAGE);
        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Invalid input! Please enter numeric values.", 
                "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void Progression() {
        if (stackForRecord.Amount() == 0) {
            JOptionPane.showMessageDialog(this, "No BMI progress data available!");
            return;
        }
        try {
            new JFXPanel();
            Platform.runLater(() -> {
                try {
                    BmiProgression = new BMIProgression(stackForRecord, userData.getBmi());
                    BmiProgression.displayGraph();
                } catch (Exception e) {
                    SwingUtilities.invokeLater(() -> 
                        JOptionPane.showMessageDialog(this, "Error displaying graph: " + e.getMessage())
                    );
                }
            });
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error initializing graph: " + e.getMessage());
        }
    }

    private void logout() {
        dispose();
        new LoginFrame();
    }
}