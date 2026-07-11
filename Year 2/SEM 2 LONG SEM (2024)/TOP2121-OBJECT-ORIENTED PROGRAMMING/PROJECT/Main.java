import javax.swing.SwingUtilities;

/**
 * Entry point for the Health, Nutrition, and Diet Guide System.
 * Launches the login window.
 */
public class Main {
    public static void main(String[] args) {
        // Ensure GUI is created on the Event Dispatch Thread
        SwingUtilities.invokeLater(new Runnable() {
            @Override
            public void run() {
                new LoginFrame().setVisible(true);
            }
        });
    }
}
