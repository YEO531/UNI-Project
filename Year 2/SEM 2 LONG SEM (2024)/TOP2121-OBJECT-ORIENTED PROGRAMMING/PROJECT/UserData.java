import java.time.LocalDateTime;
import java.io.*;

public class UserData implements Serializable {
    private static final long serialVersionUID = 1L;
    
    private final String username;
    private double height;
    private double weight;
    private double bmi;
    private String bmiCategory;
    private String selectedDietPlan;
    private LocalDateTime lastUpdate;

    public UserData(String username) {
        this.username = username;
    }

    public void updateMeasurements(double weight, double height) {
        this.weight = weight;
        this.height = height;
        this.bmi = Math.round(weight*100 / (height * height))/100.0;
        this.bmiCategory = calculateBMICategory();
        this.lastUpdate = LocalDateTime.now();
    }

    private String calculateBMICategory() {
        if (bmi < 18.5) return "Underweight";
        if (bmi < 24.9) return "Normal";
        if (bmi < 29.9) return "Overweight";
        return "Obese";
    }

    // Getters and setters
    public String getUsername() { return username; }
    public double getHeight(){return height;}
    public double getWeight(){return weight;}
    public double getBmi() { return bmi; }
    public String getBmiCategory() { return bmiCategory; }
    public String getSelectedDietPlan() { return selectedDietPlan; }
    public void setSelectedDietPlan(String plan) { this.selectedDietPlan = plan; }
}
